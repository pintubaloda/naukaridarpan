<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AssessmentIntegration;
use App\Models\ExamPaper;
use App\Models\QtiPackage;
use App\Services\Interoperability\AssessmentInteroperabilityService;
use App\Services\QTI\QtiPackageService;
use Illuminate\Http\Request;

class QtiAdminController extends Controller
{
    public function index()
    {
        $packages = QtiPackage::with(['examPaper', 'creator'])->latest()->paginate(20);
        $exams = ExamPaper::approved()->orderBy('title')->get(['id', 'title']);

        return view('admin.qti.index', compact('packages', 'exams'));
    }

    public function import(Request $request, QtiPackageService $service)
    {
        $data = $request->validate([
            'package_file' => 'required|file|mimes:zip,xml,json|max:51200',
        ]);

        $service->importPackage($data['package_file'], auth()->id());

        return back()->with('success', 'QTI package imported into the interoperability foundation.');
    }

    public function export(Request $request, QtiPackageService $service)
    {
        $data = $request->validate([
            'exam_paper_id' => 'required|exists:exam_papers,id',
        ]);

        $paper = ExamPaper::findOrFail($data['exam_paper_id']);
        $service->exportExam($paper, auth()->id());

        return back()->with('success', 'Exam exported into the QTI foundation package list.');
    }

    public function interoperability(AssessmentInteroperabilityService $service)
    {
        $integrations = AssessmentIntegration::latest()->get();
        $sampleExam = ExamPaper::approved()->latest()->first();
        $previewPayload = $sampleExam ? $service->buildPreviewPayload($sampleExam, $integrations->first()) : null;

        return view('admin.interoperability.index', compact('integrations', 'sampleExam', 'previewPayload'));
    }

    public function storeIntegration(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'integration_type' => 'required|string|max:100',
            'endpoint_url' => 'required|url|max:500',
            'auth_type' => 'required|string|max:100',
            'configuration_text' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        AssessmentIntegration::create([
            'name' => $data['name'],
            'integration_type' => $data['integration_type'],
            'endpoint_url' => $data['endpoint_url'],
            'auth_type' => $data['auth_type'],
            'configuration' => $this->parseConfiguration($data['configuration_text'] ?? ''),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return back()->with('success', 'Assessment integration added.');
    }

    public function updateIntegration(Request $request, AssessmentIntegration $integration)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'integration_type' => 'required|string|max:100',
            'endpoint_url' => 'required|url|max:500',
            'auth_type' => 'required|string|max:100',
            'configuration_text' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $integration->update([
            'name' => $data['name'],
            'integration_type' => $data['integration_type'],
            'endpoint_url' => $data['endpoint_url'],
            'auth_type' => $data['auth_type'],
            'configuration' => $this->parseConfiguration($data['configuration_text'] ?? ''),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return back()->with('success', 'Assessment integration updated.');
    }

    public function destroyIntegration(AssessmentIntegration $integration)
    {
        $integration->delete();

        return back()->with('success', 'Assessment integration deleted.');
    }

    private function parseConfiguration(string $raw): array
    {
        return collect(preg_split('/\r\n|\r|\n/', $raw))
            ->map(fn ($line) => trim($line))
            ->filter()
            ->mapWithKeys(function ($line) {
                [$key, $value] = array_pad(explode(':', $line, 2), 2, '');
                return [trim($key) => trim($value)];
            })
            ->all();
    }
}
