<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QtiPackage extends Model
{
    protected $fillable = [
        'exam_paper_id',
        'created_by',
        'name',
        'direction',
        'status',
        'version',
        'manifest_identifier',
        'package_path',
        'summary',
        'error_message',
    ];

    protected $casts = [
        'summary' => 'array',
    ];

    public function examPaper()
    {
        return $this->belongsTo(ExamPaper::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
