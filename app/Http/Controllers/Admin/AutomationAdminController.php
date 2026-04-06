<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AutomationRunLog;
use App\Models\AutomationSource;
use App\Models\BlogPost;
use App\Models\ProfessorLead;
use Illuminate\Http\Request;

class AutomationAdminController extends Controller
{
    public function index()
    {
        $sources = AutomationSource::orderBy('subject')->orderBy('name')->paginate(30);
        $recentRuns = AutomationRunLog::latest()->take(15)->get();
        $stats = [
            'sources' => AutomationSource::count(),
            'active_sources' => AutomationSource::where('is_active', true)->count(),
            'imported_posts' => BlogPost::whereNotNull('import_hash')->count(),
            'imported_leads' => ProfessorLead::whereNotNull('lead_hash')->count(),
        ];

        return view('admin.automation-sources.index', compact('sources', 'recentRuns', 'stats'));
    }

    public function store(Request $request)
    {
        $source = AutomationSource::create($this->validatedData($request));

        return redirect()->route('admin.automation-sources.index')
            ->with('success', "Source '{$source->name}' created.");
    }

    public function update(Request $request, AutomationSource $source)
    {
        $source->update($this->validatedData($request));

        return redirect()->route('admin.automation-sources.index')
            ->with('success', "Source '{$source->name}' updated.");
    }

    protected function validatedData(Request $request): array
    {
        return $request->validate([
            'subject' => 'nullable|string|max:255',
            'name' => 'required|string|max:255',
            'source_type' => 'required|string|max:50',
            'site_kind' => 'nullable|string|max:100',
            'base_url' => 'nullable|url|max:500',
            'rss_url' => 'nullable|url|max:500',
            'listing_page_url' => 'nullable|url|max:500',
            'answer_key_listing_url' => 'nullable|url|max:500',
            'pdf_kind' => 'required|in:text,scanned',
            'answer_key_mode' => 'required|in:same_pdf,separate_pdf,none',
            'discovery_query' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
        ]) + [
            'is_active' => $request->boolean('is_active'),
        ];
    }
}
