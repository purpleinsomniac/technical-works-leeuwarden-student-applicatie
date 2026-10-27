<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserState extends Model
{
    protected $table = 'gebruiker_statussen';

    protected $fillable = ['gebruiker_id', 'gegevens'];

    protected function casts(): array
    {
        return ['gegevens' => 'array'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'gebruiker_id');
    }
}
