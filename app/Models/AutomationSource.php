<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AutomationSource extends Model
{
    protected $fillable = [
        'subject',
        'name',
        'source_type',
        'site_kind',
        'base_url',
        'rss_url',
        'listing_page_url',
        'answer_key_listing_url',
        'pdf_kind',
        'answer_key_mode',
        'discovery_query',
        'notes',
        'is_active',
        'last_checked_at',
        'last_item_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_checked_at' => 'datetime',
        'last_item_at' => 'datetime',
    ];
}
