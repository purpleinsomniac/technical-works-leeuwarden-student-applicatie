<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
