<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    protected $table = 'rollen';

    protected $fillable = ['naam'];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'gebruiker_rol', 'rol_id', 'gebruiker_id');
    }

    public function primaryUsers(): HasMany
    {
        return $this->hasMany(User::class, 'hoofdrol_id');
    }
}
