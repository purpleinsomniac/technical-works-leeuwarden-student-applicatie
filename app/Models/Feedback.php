<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Feedback die aan een student en leeruitkomst is gekoppeld.
 *
 * @property int $id
 * @property int $auteur_id
 * @property int $student_id
 * @property int|null $opdracht_id
 * @property int $leeruitkomst_id
 * @property string $type
 * @property string|null $persoon
 * @property string $onderwerp
 * @property string $wat_ging_goed
 * @property string $wat_kan_beter
 * @property string $volgende_stap
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read User $author
 * @property-read User $student
 * @property-read Assignment|null $assignment
 * @property-read LearningOutcome $learningOutcome
 */
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
