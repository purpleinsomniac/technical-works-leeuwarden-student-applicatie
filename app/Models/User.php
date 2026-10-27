<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $table = 'gebruikers';

    protected $fillable = ['naam', 'email', 'password', 'rol', 'hoofdrol_id', 'studiefase_id'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function appState(): HasOne
    {
        return $this->hasOne(UserState::class, 'gebruiker_id');
    }

    public function studyPhase(): BelongsTo
    {
        return $this->belongsTo(StudyPhase::class, 'studiefase_id');
    }

    public function primaryRole(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'hoofdrol_id');
    }

    public function extraRoles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'gebruiker_rol', 'gebruiker_id', 'rol_id');
    }

    public function canUseRole(string $roleName): bool
    {
        $primaryRole = $this->primaryRole?->naam ?? $this->rol;

        if ($primaryRole === $roleName) {
            return true;
        }

        return $primaryRole === 'beheerder'
            && $this->extraRoles()->where('naam', $roleName)->exists();
    }

    public function learningGoals(): HasMany
    {
        return $this->hasMany(LearningGoal::class, 'gebruiker_id');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class, 'gebruiker_id');
    }

    public function reflections(): HasMany
    {
        return $this->hasMany(Reflection::class, 'gebruiker_id');
    }

    public function progress(): HasMany
    {
        return $this->hasMany(Progress::class, 'gebruiker_id');
    }
}
