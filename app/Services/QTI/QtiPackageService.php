<?php

namespace App\Services\QTI;

use App\Models\ExamPaper;
use App\Models\QtiPackage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class QtiPackageService
{
    public function importPackage(UploadedFile $file, int $userId): QtiPackage
    {
        $storedPath = $file->store('qti/packages/imports', 'public');
        $summary = [
            'original_name' => $file->getClientOriginalName(),
            'size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'detected' => $this->detectPackageProfile($file),
        ];

        return QtiPackage::create([
            'created_by' => $userId,
            'name' => $file->getClientOriginalName(),
            'direction' => 'import',
            'status' => 'processed',
            'version' => $summary['detected']['version'] ?? 'QTI 2.2',
            'manifest_identifier' => $summary['detected']['identifier'] ?? Str::uuid()->toString(),
            'package_path' => $storedPath,
            'summary' => $summary,
        ]);
    }

    public function exportExam(ExamPaper $paper, int $userId): QtiPackage
    {
        $identifier = 'nd_exam_' . $paper->id . '_' . Str::lower(Str::random(8));
        $payload = [
            'identifier' => $identifier,
            'title' => $paper->title,
            'subject' => $paper->subject,
            'question_count' => $paper->total_questions,
            'duration_minutes' => $paper->duration_minutes,
            'sections' => $paper->exam_sections ?? [],
            'questions' => $paper->questions_data ? json_decode((string) $paper->questions_data, true) : [],
        ];

        $path = 'qti/packages/exports/' . $identifier . '.json';
        Storage::disk('public')->put($path, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        return QtiPackage::create([
            'exam_paper_id' => $paper->id,
            'created_by' => $userId,
            'name' => $paper->title . ' Export',
            'direction' => 'export',
            'status' => 'processed',
            'version' => 'QTI 2.2-compatible foundation',
            'manifest_identifier' => $identifier,
            'package_path' => $path,
            'summary' => [
                'question_count' => $paper->total_questions,
                'sections_count' => count($paper->exam_sections ?? []),
                'format' => 'json-foundation',
            ],
        ]);
    }

    private function detectPackageProfile(UploadedFile $file): array
    {
        $name = strtolower($file->getClientOriginalName());

        if (str_ends_with($name, '.zip')) {
            return ['version' => 'QTI package', 'identifier' => Str::uuid()->toString()];
        }

        if (str_ends_with($name, '.xml')) {
            return ['version' => 'QTI XML', 'identifier' => Str::uuid()->toString()];
        }

        return ['version' => 'Unknown package', 'identifier' => Str::uuid()->toString()];
    }
}
