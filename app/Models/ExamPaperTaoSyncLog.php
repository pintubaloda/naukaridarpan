<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamPaperTaoSyncLog extends Model
{
    protected $fillable = [
        'exam_paper_id',
        'user_id',
        'trigger',
        'status',
        'message',
        'request_payload',
        'response_payload',
        'tao_test_id',
        'tao_delivery_id',
    ];

    protected $casts = [
        'request_payload' => 'array',
        'response_payload' => 'array',
    ];

    public function examPaper()
    {
        return $this->belongsTo(ExamPaper::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
