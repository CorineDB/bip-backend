<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\DB;

class HashedExistsMultiple implements ValidationRule
{
    protected string $table;
    protected string $column;
    protected ?string $modelClass;
    protected ?Closure $whereCallback;

    /**
     * Créer une nouvelle instance de la règle
     *
     * @param string $table Table ou nom du modèle
     * @param string $column Colonne à vérifier (par défaut 'id')
     * @param Closure|null $whereCallback Callback pour ajouter des conditions supplémentaires
     */
    public function __construct(string $table, string $column = 'id', ?Closure $whereCallback = null)
    {
        // Vérifier si c'est un nom de classe de modèle
        if (class_exists($table)) {
            $this->modelClass = $table;
            $model = new $table;
            $this->table = $model->getTable();
        } else {
            $this->table = $table;
            $this->modelClass = null;
        }

        $this->column = $column;
        $this->whereCallback = $whereCallback;
    }

    /**
     * Valider l'attribut (tableau d'IDs hashés)
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!is_array($value)) {
            $fail("Le champ {$attribute} doit être un tableau.");
            return;
        }

        if (empty($value)) {
            $fail("Le champ {$attribute} ne peut pas être vide.");
            return;
        }

        // Déhasher tous les IDs
        $unhashedIds = [];

        foreach ($value as $index => $hashedId) {
            $unhashedId = null;

            if ($this->modelClass && method_exists($this->modelClass, 'unhashId')) {
                $unhashedId = $this->modelClass::unhashId($hashedId);
            } else {
                // Fallback: essayer de déhasher avec Hashids directement
                try {
                    $hashids = new \Hashids\Hashids(
                        config('app.hashids_salt', config('app.key')),
                        config('app.hashids_min_length', 8)
                    );
                    $decoded = $hashids->decode($hashedId);
                    $unhashedId = !empty($decoded) ? $decoded[0] : null;
                } catch (\Exception $e) {
                    $unhashedId = null;
                }
            }

            if ($unhashedId === null) {
                $fail("L'élément à l'index {$index} du champ {$attribute} n'est pas valide.");
                return;
            }

            $unhashedIds[] = $unhashedId;
        }

        // Vérifier que tous les IDs existent dans la table
        $query = DB::table($this->table)->whereIn($this->column, $unhashedIds);

        // Appliquer le callback where si fourni
        if ($this->whereCallback) {
            $query = call_user_func($this->whereCallback, $query);
        }

        $count = $query->count();

        if ($count !== count($unhashedIds)) {
            $fail("Certains éléments du champ {$attribute} n'existent pas.");
        }
    }

    /**
     * Ajouter une condition where supplémentaire
     */
    public function where(Closure $callback): self
    {
        $this->whereCallback = $callback;
        return $this;
    }

    /**
     * Créer une instance statique pour une utilisation fluide
     */
    public static function make(string $table, string $column = 'id'): self
    {
        return new self($table, $column);
    }
}
