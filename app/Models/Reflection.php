<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reflection extends Model
{
    protected $table = 'reflecties';

    protected $fillable = [
        'gebruiker_id',
        'opdracht_id',
        'leeruitkomst_id',
        'onderwerp',
        'situatie',
        'wat_ging_goed',
        'wat_kan_beter',
        'volgende_stap',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'gebruiker_id');
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(Assignment::class, 'opdracht_id');
    }

    public function learningOutcome(): BelongsTo
    {
        return $this->belongsTo(LearningOutcome::class, 'leeruitkomst_id');
    }
}
