<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Reflectie die door een student bij een leeruitkomst is vastgelegd.
 *
 * @property int $id
 * @property int $gebruiker_id
 * @property int|null $opdracht_id
 * @property int $leeruitkomst_id
 * @property string $onderwerp
 * @property string $situatie
 * @property string $wat_ging_goed
 * @property string $wat_kan_beter
 * @property string $volgende_stap
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read User $user
 * @property-read Assignment|null $assignment
 * @property-read LearningOutcome $learningOutcome
 */
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
