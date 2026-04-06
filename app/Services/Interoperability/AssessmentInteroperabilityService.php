<?php

namespace App\Services\Interoperability;

use App\Models\AssessmentIntegration;
use App\Models\ExamPaper;

class AssessmentInteroperabilityService
{
    public function buildPreviewPayload(ExamPaper $paper, ?AssessmentIntegration $integration = null): array
    {
        return [
            'exam' => [
                'id' => $paper->id,
                'title' => $paper->title,
                'subject' => $paper->subject,
                'duration_minutes' => $paper->duration_minutes,
                'question_count' => $paper->total_questions,
                'status' => $paper->status,
            ],
            'integration' => $integration ? [
                'name' => $integration->name,
                'type' => $integration->integration_type,
                'endpoint_url' => $integration->endpoint_url,
            ] : null,
            'capabilities' => [
                'qti_import' => true,
                'qti_export' => true,
                'advanced_item_registry' => true,
                'interop_preview' => true,
            ],
        ];
    }
}
