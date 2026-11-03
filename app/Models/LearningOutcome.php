<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Leeruitkomst binnen één studiefase.
 *
 * @property int $id
 * @property int $studiefase_id
 * @property string $code
 * @property string $titel
 * @property string|null $type
 * @property string $omschrijving
 * @property int|null $normuren
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read StudyPhase $studyPhase
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Progress> $progress
 */
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
