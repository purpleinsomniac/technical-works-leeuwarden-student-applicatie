<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ClassGroup extends Model
{
    protected $fillable = ['naam', 'schooljaar', 'omschrijving', 'coach_id'];

    public function coach(): BelongsTo
    {
        return $this->belongsTo(User::class, 'coach_id');
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'class_group_student', 'class_group_id', 'student_id')
            ->withTimestamps()
            ->orderBy('naam');
    }
}
