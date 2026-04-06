<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamTemplate extends Model
{
    protected $fillable = [
        'created_by',
        'category_id',
        'name',
        'description',
        'duration_minutes',
        'default_negative_marking',
        'sections',
        'template_data',
        'is_active',
    ];

    protected $casts = [
        'sections' => 'array',
        'template_data' => 'array',
        'is_active' => 'boolean',
        'default_negative_marking' => 'decimal:2',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
