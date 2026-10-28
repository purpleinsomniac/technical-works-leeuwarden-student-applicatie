<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Feedback extends Model
{
    protected $table = 'feedback';

    protected $fillable = [
        'auteur_id',
        'student_id',
        'opdracht_id',
        'leeruitkomst_id',
        'type',
        'persoon',
        'onderwerp',
        'wat_ging_goed',
        'wat_kan_beter',
        'volgende_stap',
    ];

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'auteur_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
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
