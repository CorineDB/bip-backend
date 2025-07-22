<?php

namespace App\Traits;

trait GenerateTemporaryPassword
{
    public function generateTemporaryPassword(int $length = 12): string
    {
        $characters = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789!@#$%&*';
        $password = '';
        $charactersLength = strlen($characters);

        for ($i = 0; $i < $length; $i++) {
            $password .= $characters[random_int(0, $charactersLength - 1)];
        }

        return $password;
    }

    public function generateSimpleTemporaryPassword(int $length = 8): string
    {
        $characters = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789';
        $password = '';
        $charactersLength = strlen($characters);

        for ($i = 0; $i < $length; $i++) {
            $password .= $characters[random_int(0, $charactersLength - 1)];
        }

        return $password;
    }

    public function generateNumericTemporaryPassword(int $length = 6): string
    {
        $password = '';

        for ($i = 0; $i < $length; $i++) {
            $password .= random_int(0, 9);
        }

        return $password;
    }
}