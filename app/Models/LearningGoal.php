<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LearningGoal extends Model
{
    protected $table = 'leerdoelen';

    protected $fillable = ['gebruiker_id', 'leeruitkomst_id', 'onderwerp', 'omschrijving', 'status'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'gebruiker_id');
    }

    public function learningOutcome(): BelongsTo
    {
        return $this->belongsTo(LearningOutcome::class, 'leeruitkomst_id');
    }
}
