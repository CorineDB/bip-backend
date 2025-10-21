<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class HashedExistsMultiple implements Rule
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
        // Si la valeur est vide ou null, on considère que c'est valide (pour gérer 'nullable')
        if ($value === null || (is_array($value) && empty($value))) {
            return true;
        }

        if (!is_array($value)) {
            return false;
        }

        // Déhasher tous les IDs
        $unhashedIds = [];

        foreach ($value as $index => $hashedId) {
            // Si c'est déjà un entier, pas besoin de déhasher
            if (is_int($hashedId)) {
                $unhashedIds[] = $hashedId;
                continue;
            }

            $unhashedId = null;

            if ($this->modelClass && method_exists($this->modelClass, 'unhashId')) {
                $unhashedId = $this->modelClass::unhashId($hashedId);
            } else {
                // Fallback: essayer de déhasher avec Hashids directement
                try {
                    $hashids = new \Hashids\Hashids(
                        config('app.hashids_salt', config('app.key')),
                        config('app.hashids_min_length', 64)
                    );
                    $decoded = $hashids->decode($hashedId);
                    $unhashedId = !empty($decoded) ? $decoded[0] : null;
                } catch (\Exception $e) {
                    $unhashedId = null;
                }
            }

            if ($unhashedId === null) {
                return false;
            }

            $unhashedIds[] = $unhashedId;
        }

        // Modifier directement la valeur dans la Request avec gestion des attributs imbriqués
        $this->setNestedAttributeValue($attribute, $unhashedIds);

        // Vérifier que tous les IDs existent dans la table
        $query = DB::table($this->table)->whereIn($this->column, $unhashedIds);

        // Appliquer le callback where si fourni
        if ($this->whereCallback) {
            $result = call_user_func($this->whereCallback, $query);
            // Si le callback retourne une query, l'utiliser, sinon garder la query actuelle
            if ($result !== null) {
                $query = $result;
            }
        }

        $count = $query->count();

        return $count === count($unhashedIds);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return "Certains éléments du champ :attribute n'existent pas.";
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
     *
     * @param string $attribute
     * @param mixed $value
     */
    private function setNestedAttributeValue($attribute, $value)
    {
        // Split the attribute by '.' to get the individual levels
        $keys = explode('.', $attribute);

        // Get the full request data and store it in a variable
        $input = request()->all();

        // Traverse through the keys and create the nested array path
        $current = &$input; // Reference to the root of the array

        // Traverse the keys to get the nested attribute
        foreach ($keys as $key) {
            // If the key doesn't exist, create it as an empty array
            if (!isset($current[$key])) {
                $current[$key] = [];
            }
            // Traverse deeper into the array by reference
            $current = &$current[$key];
        }

        // Set the final value
        $current = $value;

        // Now put the modified data back into the request
        request()->merge($input);
    }
}
