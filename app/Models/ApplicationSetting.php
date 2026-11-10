<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApplicationSetting extends Model
{
    protected $table = 'applicatie_instellingen';

    protected $fillable = [
        'application_accent_color',
        'application_style',
        'max_upload_size_mb',
        'allowed_upload_extensions',
        'maintenance_mode',
    ];

    protected function casts(): array
    {
        return [
            'allowed_upload_extensions' => 'array',
            'maintenance_mode' => 'boolean',
        ];
    }

    public static function current(): self
    {
        return static::query()->findOrFail(1);
    }
}
