<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LearningOutcome extends Model
{
    protected $table = 'leeruitkomsten';

    protected $fillable = ['studiefase_id', 'code', 'titel', 'type', 'omschrijving', 'normuren'];

    public function studyPhase(): BelongsTo
    {
        return $this->belongsTo(StudyPhase::class, 'studiefase_id');
    }

    public function assignments(): BelongsToMany
    {
        return $this->belongsToMany(
            Assignment::class,
            'opdracht_leeruitkomst',
            'leeruitkomst_id',
            'opdracht_id',
        )->withPivot('urenpercentage');
    }

    public function progress(): HasMany
    {
        return $this->hasMany(Progress::class, 'leeruitkomst_id');
    }
}
