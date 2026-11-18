<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Group extends Model
{
    protected $table = 'groepen';

    protected $fillable = ['naam', 'schooljaar', 'omschrijving', 'coach_id', 'studiefases_id', 'doel'];

    public function coach(): BelongsTo
    {
        return $this->belongsTo(User::class, 'coach_id');
    }

    public function studyPhase(): BelongsTo
    {
        return $this->belongsTo(StudyPhase::class, 'studiefases_id');
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'groep_student', 'groep_id', 'student_id')
            ->withTimestamps()
            ->orderBy('naam');
    }
}
