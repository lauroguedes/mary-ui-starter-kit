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
use Spatie\Permission\Traits\HasRoles;

/**
 * @method static updateOrCreate(array $array, array $array1)
 */
final class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, HasRoles, Notifiable;

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
