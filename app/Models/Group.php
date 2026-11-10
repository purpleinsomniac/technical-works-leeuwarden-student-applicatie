<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Group extends Model
{
    protected $table = 'groepen';

    protected $fillable = ['naam', 'schooljaar', 'omschrijving', 'coach_id'];

    public function coach(): BelongsTo
    {
        return $this->belongsTo(User::class, 'coach_id');
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'groep_student', 'groep_id', 'student_id')
            ->withTimestamps()
            ->orderBy('naam');
    }
}
