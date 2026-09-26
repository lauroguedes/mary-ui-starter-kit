<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\UserStatus;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use LauroGuedes\DemoMode\Sandbox\BelongsToSandbox;
use Spatie\Permission\Traits\HasRoles;

/**
 * @method static updateOrCreate(array $array, array $array1)
 */
final class User extends Authenticatable implements MustVerifyEmail
{
    /*
     * BelongsToSandbox is what makes this demo's Users screen worth showing.
     *
     * The screen is a list everybody adds to, which is the one case where a shared
     * demo falls apart: the first visitor's test accounts are the second visitor's
     * clutter. Scoped isolation gives each of them the seeded fifty-three plus
     * their own, so deleting something is safe to try.
     *
     * Sandboxing the authentication model is deliberate and it works because the
     * accounts you sign in with are the seeder's, and those carry no sandbox id, so
     * they belong to everybody. A visitor who registers gets an account of their
     * own and stays signed in to it, because the sandbox lives in the session and
     * Laravel's login migrates the session rather than replacing it.
     *
     * Two things it does not isolate, on purpose. Roles and permissions are shared:
     * they are part of what this kit demonstrates, not something a visitor should
     * be able to fork. And 'unique:users,email' is query-builder validation, so it
     * sees every sandbox -- two visitors cannot both register the same address.
     *
     * Entirely inert unless demo.sandbox.driver is 'scoped'. The trait adds no
     * global scope and no creating hook on any other deployment of this kit.
     */

    /** @use HasFactory<\Database\Factories\UserFactory> */
    use BelongsToSandbox, HasFactory, HasRoles, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'avatar',
        'name',
        'email',
        'status',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function socialAccounts(): HasMany
    {
        return $this->hasMany(SocialAccount::class);
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->pipe(
                fn ($parts) => Str::upper(
                    Str::substr($parts->first(), 0, 1) .
                        Str::substr($parts->last(), 0, 1)
                )
            );
    }

    protected static function booted(): void
    {
        self::created(function (User $user): void {
            $user->assignRole('user');
        });
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'status' => UserStatus::class,
        ];
    }

    #[Scope]
    protected function hideSuperAdmin(Builder $query): void
    {
        $query->when(
            ! auth()->user()?->hasRole('super-admin'),
            fn ($q) => $q->whereDoesntHave('roles', fn ($r) => $r->where('name', 'super-admin'))
        );
    }
}
