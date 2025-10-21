<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class HashedExists implements Rule
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
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        // Si la valeur est vide, on considère que c'est valide (pour gérer 'nullable')
        if (empty($value) || $value === null || $value === '') {
            return true;
        }

        // Si c'est déjà un entier, pas besoin de déhasher
        if (is_int($value)) {
            $unhashedId = $value;
        } else {
            // Déhasher l'ID en utilisant le modèle si disponible
            $unhashedId = null;

            if ($this->modelClass && method_exists($this->modelClass, 'unhashId')) {
                $unhashedId = $this->modelClass::unhashId($value);
            } else {
                // Fallback: essayer de déhasher avec Hashids directement
                try {
                    $hashids = new \Hashids\Hashids(
                        config('app.hashids_salt', config('app.key')),
                        config('app.hashids_min_length', 64)
                    );
                    $decoded = $hashids->decode($value);
                    $unhashedId = !empty($decoded) ? $decoded[0] : null;
                } catch (\Exception $e) {
                    $unhashedId = null;
                }
            }

            if ($unhashedId === null) {
                return false;
            }

            // Modifier directement la valeur dans la Request avec gestion des attributs imbriqués
            $this->setNestedAttributeValue($attribute, $unhashedId);
        }

        // Vérifier que l'ID existe dans la table
        $query = DB::table($this->table)->where($this->column, $unhashedId);

        // Appliquer le callback where si fourni
        if ($this->whereCallback) {
            $result = call_user_func($this->whereCallback, $query);
            // Si le callback retourne une query, l'utiliser, sinon garder la query actuelle
            if ($result !== null) {
                $query = $result;
            }
        }

        return $query->exists();
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return "Le :attribute sélectionné n'existe pas.";
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

    /**
     * Set the value for a nested attribute in the request data.
     * Gère les structures imbriquées y compris les tableaux avec indices numériques.
     *
     * @param string $attribute
     * @param mixed $value
     */
    private function setNestedAttributeValue($attribute, $value)
    {
        // Split the attribute by '.' to get the individual levels
        $keys = explode('.', $attribute);

        // Obtenir le request actuel
        $request = request();

        // Get the full request data and store it in a variable
        $input = $request->all();

        // Traverse through the keys and create the nested array path
        $current = &$input; // Reference to the root of the array
        $lastKey = array_pop($keys); // Extraire la dernière clé

        // Parcourir tous les niveaux sauf le dernier
        foreach ($keys as $key) {
            // Si c'est un indice numérique ou une clé qui n'existe pas encore
            if (is_numeric($key)) {
                $key = (int) $key;
            }

            // Si la clé n'existe pas ou n'est pas un tableau, l'initialiser
            if (!isset($current[$key]) || !is_array($current[$key])) {
                $current[$key] = [];
            }

            // Descendre d'un niveau dans la structure par référence
            $current = &$current[$key];
        }

        // Gérer la dernière clé
        if (is_numeric($lastKey)) {
            $lastKey = (int) $lastKey;
        }

        // Définir la valeur finale
        $current[$lastKey] = $value;

        // Remplacer complètement les données du request
        $request->replace($input);
    }
}
