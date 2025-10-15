<?php

namespace App\Traits;

use Hashids\Hashids;
use Illuminate\Support\Facades\Log;

trait HashableId
{
    /**
     * Obtenir l'instance Hashids
     */
    protected static function getHashids(): Hashids
    {
        // Utiliser une clé spécifique depuis le config ou générer une clé par défaut
        $salt = config('app.hashids_salt', config('app.key'));
        $minLength = config('app.hashids_min_length', 13);

        return new Hashids($salt, $minLength);
    }

    /**
     * Hasher l'ID du modèle (attribut virtuel)
     */
    public function getHashedIdAttribute(): string
    {
        return $this->hashId($this->id);
    }

    /**
     * Hasher un ID donné
     */
    public static function hashId($id): string
    {
        if (empty($id)) {
            return '';
        }

        try {
            return static::getHashids()->encode($id);
        } catch (\Exception $e) {
            Log::error('Erreur lors du hashage de l\'ID', [
                'model' => static::class,
                'id' => $id,
                'error' => $e->getMessage()
            ]);
            return '';
        }
    }

    /**
     * Déhasher un ID
     */
    public static function unhashId(?string $hashedId)
    {
        if (empty($hashedId)) {
            return null;
        }

        try {
            $decoded = static::getHashids()->decode($hashedId);

            // Hashids retourne un tableau, prendre le premier élément
            return !empty($decoded) ? $decoded[0] : null;
        } catch (\Exception $e) {
            Log::warning('Erreur lors du déhashage de l\'ID', [
                'model' => static::class,
                'hashed_id' => $hashedId,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Déhasher plusieurs IDs à la fois
     */
    public static function unhashIds(array $hashedIds): array
    {
        return array_values(array_filter(array_map(function ($hashedId) {
            return static::unhashId($hashedId);
        }, $hashedIds), function ($id) {
            return $id !== null;
        }));
    }

    /**
     * Hasher plusieurs IDs à la fois
     */
    public static function hashIds(array $ids): array
    {
        return array_map(function ($id) {
            return static::hashId($id);
        }, $ids);
    }

    /**
     * Trouver un modèle par son ID hashé
     */
    public static function findByHashedId(?string $hashedId)
    {
        $id = static::unhashId($hashedId);

        if ($id === null) {
            return null;
        }

        return static::find($id);
    }

    /**
     * Trouver un modèle par son ID hashé ou échouer
     */
    public static function findByHashedIdOrFail(string $hashedId)
    {
        $id = static::unhashId($hashedId);

        if ($id === null) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException(
                'Invalid hashed ID provided for ' . static::class
            );
        }

        return static::findOrFail($id);
    }

    /**
     * Vérifier si un ID hashé est valide
     */
    public static function isValidHashedId(?string $hashedId): bool
    {
        return static::unhashId($hashedId) !== null;
    }

    /**
     * Modifier la sérialisation JSON pour inclure l'ID hashé
     */
    public function toArray(): array
    {
        $array = parent::toArray();

        // Ajouter l'ID hashé si l'ID existe
        if (isset($array['id'])) {
            $array['hashed_id'] = $this->hashed_id;
        }

        return $array;
    }

    /**
     * Scope pour trouver par ID hashé
     */
    public function scopeWhereHashedId($query, string $hashedId)
    {
        $id = static::unhashId($hashedId);

        if ($id === null) {
            // Retourner une requête qui ne trouvera rien
            return $query->whereRaw('1 = 0');
        }

        return $query->where('id', $id);
    }

    /**
     * Scope pour trouver par plusieurs IDs hashés
     */
    public function scopeWhereHashedIdIn($query, array $hashedIds)
    {
        $ids = static::unhashIds($hashedIds);

        if (empty($ids)) {
            // Retourner une requête qui ne trouvera rien
            return $query->whereRaw('1 = 0');
        }

        return $query->whereIn('id', $ids);
    }
}
