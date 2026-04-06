<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssessmentIntegration extends Model
{
    protected $fillable = [
        'name',
        'integration_type',
        'endpoint_url',
        'auth_type',
        'configuration',
        'is_active',
    ];

    protected $casts = [
        'configuration' => 'array',
        'is_active' => 'boolean',
    ];
}
