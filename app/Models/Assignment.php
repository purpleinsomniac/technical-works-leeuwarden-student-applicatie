<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Opdracht die aan een student en één of meer leeruitkomsten is gekoppeld.
 *
 * @property int $id
 * @property int $gebruiker_id
 * @property string $onderwerp
 * @property string $type
 * @property string|null $omschrijving
 * @property int $gevalideerde_uren
 * @property \Illuminate\Support\Carbon|null $inleverdatum
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read User $user
 * @property-read \Illuminate\Database\Eloquent\Collection<int, LearningOutcome> $learningOutcomes
 */
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
