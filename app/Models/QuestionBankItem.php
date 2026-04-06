<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuestionBankItem extends Model
{
    protected $fillable = [
        'created_by',
        'category_id',
        'bank_name',
        'source_exam_paper_id',
        'source_exam_title',
        'source_exam_year',
        'source_question_serial',
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
        'source_exam_year' => 'integer',
        'source_question_serial' => 'integer',
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

    public function sourceExamPaper()
    {
        return $this->belongsTo(ExamPaper::class, 'source_exam_paper_id');
    }
}
