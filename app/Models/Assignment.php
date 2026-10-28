<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Assignment extends Model
{
    protected $table = 'opdrachten';

    protected $fillable = ['gebruiker_id', 'onderwerp', 'type', 'omschrijving', 'gevalideerde_uren', 'inleverdatum', 'status'];

    protected function casts(): array
    {
        return ['inleverdatum' => 'date'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'gebruiker_id');
    }

    public function learningOutcomes(): BelongsToMany
    {
        return $this->belongsToMany(
            LearningOutcome::class,
            'opdracht_leeruitkomst',
            'opdracht_id',
            'leeruitkomst_id',
        )->withPivot('urenpercentage');
    }

    public function reflections(): HasMany
    {
        return $this->hasMany(Reflection::class, 'opdracht_id');
    }
}
