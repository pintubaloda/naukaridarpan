<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AutomationRunLog extends Model
{
    protected $fillable = [
        'workflow_name',
        'run_type',
        'subject',
        'status',
        'payload_summary',
        'message',
        'processed_count',
    ];

    protected $casts = [
        'payload_summary' => 'array',
    ];
}
