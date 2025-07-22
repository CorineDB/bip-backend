<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Role;
use Illuminate\Console\Command;

class CreateKeycloakUser extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'keycloak:create-user 
                            {email : User email address}
                            {--username= : Username (optional)}
                            {--keycloak-id= : Keycloak ID (optional)}
                            {--role= : Role slug (optional, defaults to first available role)}';

    /**
     * The console command description.
     */
    protected $description = 'Create a user for Keycloak authentication';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        $username = $this->option('username') ?? $email;
        $keycloakId = $this->option('keycloak-id');
        $roleSlug = $this->option('role');

        // Check if user already exists
        if (User::where('email', $email)->exists()) {
            $this->error("User with email {$email} already exists!");
            return 1;
        }

        // Get role
        $role = null;
        if ($roleSlug) {
            $role = Role::where('slug', $roleSlug)->first();
            if (!$role) {
                $this->error("Role '{$roleSlug}' not found!");
                return 1;
            }
        } else {
            $role = Role::first();
            if (!$role) {
                $this->error("No roles found! Please create a role first.");
                return 1;
            }
        }

        // Create user
        try {
            $user = User::create([
                'provider' => 'keycloak',
                'provider_user_id' => $keycloakId,
                'username' => $username,
                'email' => $email,
                'status' => 'actif',
                'is_email_verified' => true,
                'email_verified_at' => now(),
                'password' => null,
                'roleId' => $role->id,
                'keycloak_id' => $keycloakId,
            ]);

            $this->info("✅ User created successfully!");
            $this->table(
                ['Field', 'Value'],
                [
                    ['ID', $user->id],
                    ['Email', $user->email],
                    ['Username', $user->username],
                    ['Role', $role->name ?? $role->slug],
                    ['Status', $user->status],
                    ['Keycloak ID', $user->keycloak_id ?? 'Not set'],
                ]
            );

            $this->warn("💡 The user can now authenticate via Keycloak with email: {$email}");
            
            return 0;
        } catch (\Exception $e) {
            $this->error("Failed to create user: " . $e->getMessage());
            return 1;
        }
    }
}