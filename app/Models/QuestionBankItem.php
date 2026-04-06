<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuestionBankItem extends Model
{
    protected $fillable = [
        'created_by',
        'category_id',
        'subject',
        'section',
        'topic',
        'difficulty',
        'question_type',
        'interaction_type',
        'qti_identifier',
        'question_text',
        'options',
        'correct_answer',
        'advanced_metadata',
        'explanation',
        'marks',
        'negative_marking',
        'tags',
        'is_active',
    ];

    protected $casts = [
        'options' => 'array',
        'correct_answer' => 'array',
        'advanced_metadata' => 'array',
        'tags' => 'array',
        'is_active' => 'boolean',
        'marks' => 'decimal:2',
        'negative_marking' => 'decimal:2',
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
