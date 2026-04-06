<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AutomationRunLog;
use App\Models\AutomationSource;
use App\Models\BlogPost;
use App\Models\ProfessorLead;

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
}
