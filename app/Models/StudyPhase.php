<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Studiefase waaraan gebruikers en leeruitkomsten gekoppeld zijn.
 *
 * @property int $id
 * @property string $naam
 * @property string|null $omschrijving
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class StudyPhase extends Model
{
    protected $table = 'studiefases';

    protected $fillable = ['naam', 'omschrijving'];

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'studiefase_id');
    }

    public function learningOutcomes(): HasMany
    {
        return $this->hasMany(LearningOutcome::class, 'studiefase_id');
    }
}
