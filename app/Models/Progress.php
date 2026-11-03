<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Voortgang van één student voor één leeruitkomst.
 *
 * @property int $id
 * @property int $gebruiker_id
 * @property int $leeruitkomst_id
 * @property int $gevalideerde_uren
 * @property int|null $normuren
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read User $user
 * @property-read LearningOutcome $learningOutcome
 */
class Progress extends Model
{
    protected $table = 'voortgang';

    protected $fillable = ['gebruiker_id', 'leeruitkomst_id', 'gevalideerde_uren', 'normuren', 'status'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'gebruiker_id');
    }

    public function learningOutcome(): BelongsTo
    {
        return $this->belongsTo(LearningOutcome::class, 'leeruitkomst_id');
    }
}
