<?php

namespace Database\Factories;

use App\Actions\SyncAdminUserPermissions;
use App\Enums\AdminPermissionModule;
use App\Enums\PermissionAction;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'role' => UserRole::CompanyUser,
            'company_id' => null,
            'is_active' => true,
            'is_commercial' => false,
            'is_owner' => false,
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    public function superAdmin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => UserRole::SuperAdmin,
            'company_id' => null,
        ])->afterCreating(function (User $user): void {
            if ($user->role !== UserRole::SuperAdmin || $user->isOwner()) {
                return;
            }

            $perms = [];
            foreach (AdminPermissionModule::all() as $module) {
                foreach (PermissionAction::all() as $action) {
                    $perms[] = ['module' => $module->value, 'action' => $action->value];
                }
            }

            app(SyncAdminUserPermissions::class)->execute($user, $perms);
        });
    }

    public function companyAdmin(?int $companyId = null): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => UserRole::CompanyAdmin,
            'company_id' => $companyId,
        ]);
    }
}
