<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssignmentFile extends Model
{
    protected $table = 'opdracht_bestanden';

    protected $fillable = [
        'opdracht_id',
        'originele_naam',
        'opslagpad',
        'mime_type',
        'bestandsgrootte',
    ];

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(Assignment::class, 'opdracht_id');
    }
}
