<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\DB;

class HashedExists implements ValidationRule
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
     * Valider l'attribut
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (empty($value)) {
            $fail("Le champ {$attribute} est requis.");
            return;
        }

        // Déhasher l'ID en utilisant le modèle si disponible
        $unhashedId = null;

        if ($this->modelClass && method_exists($this->modelClass, 'unhashId')) {
            $unhashedId = $this->modelClass::unhashId($value);
        } else {
            // Fallback: essayer de déhasher avec Hashids directement
            try {
                $hashids = new \Hashids\Hashids(
                    config('app.hashids_salt', config('app.key')),
                    config('app.hashids_min_length', 8)
                );
                $decoded = $hashids->decode($value);
                $unhashedId = !empty($decoded) ? $decoded[0] : null;
            } catch (\Exception $e) {
                $unhashedId = null;
            }
        }

        if ($unhashedId === null) {
            $fail("Le {$attribute} fourni n'est pas valide.");
            return;
        }

        // Vérifier que l'ID existe dans la table
        $query = DB::table($this->table)->where($this->column, $unhashedId);

        // Appliquer le callback where si fourni
        if ($this->whereCallback) {
            $query = call_user_func($this->whereCallback, $query);
        }

        if (!$query->exists()) {
            $fail("Le {$attribute} sélectionné n'existe pas.");
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
