<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExamPaperTaoSyncLog;
use App\Models\{User, ExamAttempt, ExamPaper, Purchase, PayoutRequest, PlatformSetting, ExamTemplate, QuestionBankItem};
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function dashboard()
    {
        $stats = [
            'total_users'    => User::count(),
            'total_sellers'  => User::where('role', 'seller')->count(),
            'total_students' => User::where('role', 'student')->count(),
            'total_papers'   => ExamPaper::count(),
            'pending_review' => ExamPaper::where('status', 'pending_review')->count(),
            'total_sales'    => Purchase::where('payment_status', 'paid')->count(),
            'total_revenue'  => Purchase::where('payment_status', 'paid')->sum('platform_commission'),
            'pending_kyc'    => \App\Models\KYCVerification::where('status', 'under_review')->count(),
            'pending_payout' => PayoutRequest::where('status', 'pending')->count(),
            'high_risk_attempts' => ExamAttempt::whereJsonContains('anti_cheat_review->risk_level', 'high')->count(),
            'submitted_attempts' => ExamAttempt::where('status', 'submitted')->count(),
        ];

        $recentSales = Purchase::where('payment_status', 'paid')
            ->with(['student', 'examPaper.seller'])
            ->orderByDesc('created_at')->take(10)->get();

        $recentPapers = ExamPaper::where('status', 'pending_review')
            ->with(['seller', 'category'])->orderByDesc('created_at')->take(5)->get();

        $highRiskAttempts = ExamAttempt::where('status', 'submitted')
            ->whereJsonContains('anti_cheat_review->risk_level', 'high')
            ->with(['student', 'examPaper'])
            ->latest('submitted_at')
            ->take(8)
            ->get();

        $topAttempts = ExamAttempt::where('status', 'submitted')
            ->whereNotNull('percentage')
            ->with(['student', 'examPaper'])
            ->orderByDesc('percentage')
            ->orderBy('time_taken_seconds')
            ->take(8)
            ->get();

        return view('admin.dashboard', compact('stats', 'recentSales', 'recentPapers', 'highRiskAttempts', 'topAttempts'));
    }

    public function reports()
    {
        $submittedAttempts = ExamAttempt::where('status', 'submitted')
            ->with(['student', 'examPaper'])
            ->latest('submitted_at')
            ->get();

        $riskBreakdown = [
            'high' => $submittedAttempts->filter(fn ($attempt) => ($attempt->anti_cheat_review['risk_level'] ?? 'low') === 'high')->count(),
            'medium' => $submittedAttempts->filter(fn ($attempt) => ($attempt->anti_cheat_review['risk_level'] ?? 'low') === 'medium')->count(),
            'low' => $submittedAttempts->filter(fn ($attempt) => ($attempt->anti_cheat_review['risk_level'] ?? 'low') === 'low')->count(),
        ];

        $weakAreas = $submittedAttempts
            ->flatMap(function ($attempt) {
                return collect($attempt->performance_breakdown ?? [])->map(function ($bucket, $label) {
                    $total = max(1, (int) ($bucket['total'] ?? 0));
                    return [
                        'label' => $label,
                        'accuracy' => round(((int) ($bucket['correct'] ?? 0) / $total) * 100, 2),
                        'total' => $total,
                    ];
                });
            })
            ->groupBy('label')
            ->map(function ($rows, $label) {
                return [
                    'label' => $label,
                    'avg_accuracy' => round($rows->avg('accuracy'), 2),
                    'attempts' => $rows->count(),
                ];
            })
            ->sortBy('avg_accuracy')
            ->take(8)
            ->values();

        $topExams = ExamPaper::query()
            ->withCount(['attempts as submitted_attempts_count' => fn ($query) => $query->where('status', 'submitted')])
            ->orderByDesc('submitted_attempts_count')
            ->take(8)
            ->get();

        $topStudents = ExamAttempt::query()
            ->where('status', 'submitted')
            ->whereNotNull('percentage')
            ->with('student')
            ->orderByDesc('percentage')
            ->orderBy('time_taken_seconds')
            ->take(10)
            ->get();

        return view('admin.reports', compact('submittedAttempts', 'riskBreakdown', 'weakAreas', 'topExams', 'topStudents'));
    }

    public function users(Request $r)
    {
        $query = User::query();
        if ($r->role)   $query->where('role', $r->role);
        if ($r->search) $query->where(fn($q) => $q->where('name', 'like', '%'.$r->search.'%')->orWhere('email', 'like', '%'.$r->search.'%'));
        $users = $query->orderByDesc('created_at')->paginate(20)->withQueryString();
        return view('admin.users', compact('users'));
    }

    public function exams(Request $r)
    {
        $query = ExamPaper::with(['seller', 'category'])->withCount([
            'attempts as submitted_attempts_count' => fn ($attempts) => $attempts->where('status', 'submitted'),
            'attempts as high_risk_attempts_count' => fn ($attempts) => $attempts->where('status', 'submitted')->whereJsonContains('anti_cheat_review->risk_level', 'high'),
        ])->orderByDesc('created_at');
        if ($r->status) {
            $query->where('status', $r->status);
        }
        if ($r->search) {
            $query->where(function ($q) use ($r) {
                $q->where('title', 'like', '%'.$r->search.'%')
                    ->orWhere('subject', 'like', '%'.$r->search.'%')
                    ->orWhere('slug', 'like', '%'.$r->search.'%');
            });
        }
        $papers = $query->paginate(20)->withQueryString();
        return view('admin.exams-index', compact('papers'));
    }

    public function toggleUser(User $user)
    {
        $user->update(['is_active' => ! $user->is_active]);
        return back()->with('success', 'User status updated.');
    }

    public function settings()
    {
        $settings = PlatformSetting::all()->keyBy('key');
        return view('admin.settings', compact('settings'));
    }

    public function updateSettings(Request $r)
    {
        foreach ($r->except('_token') as $key => $value) {
            PlatformSetting::set($key, $value);
        }
        return back()->with('success', 'Settings saved.');
    }

    public function pendingPayouts()
    {
        $payouts = PayoutRequest::where('status', 'pending')
            ->with(['seller.sellerProfile', 'seller.kyc'])->orderByDesc('created_at')->paginate(20);
        return view('admin.payouts', compact('payouts'));
    }

    public function processPayout(Request $r, PayoutRequest $payout)
    {
        $r->validate(['action' => 'required|in:paid,rejected', 'utr_number' => 'required_if:action,paid|nullable|string', 'admin_note' => 'nullable|string']);
        $payout->update([
            'status'       => $r->action,
            'utr_number'   => $r->utr_number,
            'admin_note'   => $r->admin_note,
            'processed_at' => now(),
        ]);
        if ($r->action === 'rejected') {
            // Refund to wallet
            $payout->seller->sellerProfile->increment('wallet_balance', $payout->amount);
        }
        return back()->with('success', 'Payout ' . $r->action . '.');
    }

    public function scrapedPapers()
    {
        $papers = ExamPaper::where('source', 'scraped')->where('status', 'draft')
            ->with('category')->orderByDesc('created_at')->paginate(20);
        return view('admin.scraped', compact('papers'));
    }

    public function publishScraped(ExamPaper $paper)
    {
        $paper->update(['status' => 'approved', 'is_free' => true, 'student_price' => 0]);
        return back()->with('success', 'Scraped paper published as free exam.');
    }

    public function professorLeads(Request $r)
    {
        $leads = \App\Models\ProfessorLead::orderByDesc('created_at')->paginate(30);
        return view('admin.professor-leads', compact('leads'));
    }

    public function sendOnboardingMailer(Request $r)
    {
        $r->validate(['lead_ids' => 'required|array', 'template' => 'required|string']);
        // dispatch mailer job
        \App\Jobs\SendOnboardingMailerJob::dispatch($r->lead_ids, $r->template);
        return back()->with('success', count($r->lead_ids) . ' mailers queued.');
    }

    public function createPaper()
    {
        $categories = \App\Models\Category::where('is_active', true)->orderBy('sort_order')->get();
        $templates = ExamTemplate::with('category')->where('is_active', true)->latest()->get();
        $selectedTemplate = request('template_id')
            ? ExamTemplate::with('category')->find(request('template_id'))
            : null;

        return view('admin.papers.create', compact('categories', 'templates', 'selectedTemplate'));
    }

    public function editExam(\App\Models\ExamPaper $paper)
    {
        $paper->load([
            'taoSyncLogs.user',
            'attempts' => fn ($query) => $query->with('student')->latest()->take(20),
        ]);
        $categories = \App\Models\Category::where('is_active', true)->orderBy('sort_order')->get();
        $questionBankItems = QuestionBankItem::query()
            ->with('category')
            ->where('is_active', true)
            ->when($paper->category_id, fn ($query) => $query->where('category_id', $paper->category_id))
            ->when($paper->subject, fn ($query) => $query->where(function ($inner) use ($paper) {
                $inner->whereNull('subject')->orWhere('subject', $paper->subject);
            }))
            ->latest()
            ->take(12)
            ->get();
        $publishReadiness = $this->buildPublishReadiness($paper, $paper->questions_data ? json_decode((string) $paper->questions_data, true) : []);

        return view('admin.exams-edit', compact('paper', 'categories', 'questionBankItems', 'publishReadiness'));
    }

    public function updateExam(Request $r, \App\Models\ExamPaper $paper)
    {
        $r->validate([
            'title'            => 'required|string|max:255',
            'subject'          => 'nullable|string|max:255',
            'exam_type'        => 'required|in:mock,previous_year',
            'category_id'      => 'required|exists:categories,id',
            'description'      => 'nullable|string|max:2000',
            'language'         => 'required|in:English,Hindi,Both',
            'duration_minutes' => 'required|integer|min:10|max:360',
            'max_marks'        => 'required|integer|min:10',
            'negative_marking' => 'nullable|numeric|min:0|max:1',
            'max_retakes'      => 'required|integer|min:1|max:10',
            'difficulty'       => 'required|in:easy,medium,hard',
            'seller_price'     => 'required|numeric|min:0',
            'is_free'          => 'boolean',
            'tags'             => 'nullable|string',
            'status'           => 'required|in:draft,pending_review,approved,rejected',
            'exam_sections_text' => 'nullable|string',
            'section_time_rules_text' => 'nullable|string',
            'section_negative_rules_text' => 'nullable|string',
            'interoperability_profile' => 'nullable|string|max:100',
            'qti_metadata_text' => 'nullable|string',
            'questions'        => 'nullable|array',
            'questions.*.serial' => 'nullable|integer|min:1',
            'questions.*.text' => 'nullable|string',
            'questions.*.type' => 'nullable|string',
            'questions.*.marks' => 'nullable|numeric|min:0',
            'questions.*.correct_answer' => 'nullable',
            'questions.*.explanation' => 'nullable|string',
            'questions.*.topic' => 'nullable|string|max:255',
            'questions.*.section' => 'nullable|string|max:255',
            'questions.*.subject' => 'nullable|string|max:255',
            'questions.*.interaction_type' => 'nullable|string|max:100',
            'questions.*.qti_identifier' => 'nullable|string|max:255',
            'questions.*.advanced_metadata_text' => 'nullable|string',
            'questions.*.options' => 'nullable|array',
            'questions.*.options.*.label' => 'nullable|string|max:10',
            'questions.*.options.*.text' => 'nullable|string',
        ]);

        $markupPct    = (float) \App\Models\PlatformSetting::get('default_commission', 15);
        $sellerPrice  = (float) $r->seller_price;
        $markup       = round($sellerPrice * $markupPct / 100, 2);

        $questionsData = $paper->questions_data ? json_decode((string) $paper->questions_data, true) : [];
        if ($r->has('questions')) {
            $questionsData = $this->sanitizeQuestions($r->input('questions', []));
        }
        $examSections = $this->parseExamSections($r->input('exam_sections_text', ''));
        $sectionTimeRules = $this->parseSectionTimeRules($r->input('section_time_rules_text', ''));
        $sectionNegativeRules = $this->parseSectionNegativeRules($r->input('section_negative_rules_text', ''));
        $previewPaper = clone $paper;
        $previewPaper->title = $r->title;
        $previewPaper->duration_minutes = $r->duration_minutes;
        $previewPaper->exam_sections = $examSections;
        $previewPaper->status = $r->status;
        $publishReadiness = $this->buildPublishReadiness($previewPaper, $questionsData);

        if ($r->status === 'approved' && !($publishReadiness['ready'] ?? false)) {
            return back()->withErrors([
                'status' => 'This exam is not ready for approval yet: ' . implode(' ', $publishReadiness['issues']),
            ])->withInput();
        }

        $paper->update([
            'title'            => $r->title,
            'subject'          => $r->subject,
            'exam_type'        => $r->exam_type,
            'category_id'      => $r->category_id,
            'description'      => $r->description,
            'language'         => $r->language,
            'duration_minutes' => $r->duration_minutes,
            'max_marks'        => $r->max_marks,
            'negative_marking' => $r->negative_marking ?? 0,
            'max_retakes'      => $r->max_retakes,
            'difficulty'       => $r->difficulty,
            'seller_price'     => $sellerPrice,
            'platform_markup'  => $markup,
            'student_price'    => $r->boolean('is_free') ? 0 : $sellerPrice + $markup,
            'is_free'          => $r->boolean('is_free'),
            'tags'             => $r->tags ? array_map('trim', explode(',', $r->tags)) : [],
            'status'           => $r->status,
            'exam_sections' => $examSections,
            'section_time_rules' => $sectionTimeRules,
            'section_negative_rules' => $sectionNegativeRules,
            'interoperability_profile' => $r->input('interoperability_profile') ?: null,
            'qti_metadata' => $this->parseKeyValueText($r->input('qti_metadata_text', '')),
            'questions_data'   => !empty($questionsData) ? json_encode($questionsData) : $paper->questions_data,
            'total_questions'  => count($questionsData) ?: $paper->total_questions,
            'question_types'   => !empty($questionsData) ? $this->summarizeQuestionTypes($questionsData) : $paper->question_types,
            'max_marks'        => !empty($questionsData)
                ? collect($questionsData)->sum(fn ($question) => (float) ($question['marks'] ?? 1))
                : $r->max_marks,
        ]);

        return back()->with('success', 'Exam updated.');
    }

    public function importQuestionBank(Request $request, ExamPaper $paper)
    {
        $data = $request->validate([
            'item_ids' => 'required|array|min:1',
            'item_ids.*' => 'exists:question_bank_items,id',
        ]);

        $existingQuestions = $paper->questions_data
            ? json_decode((string) $paper->questions_data, true)
            : [];

        $items = QuestionBankItem::whereIn('id', $data['item_ids'])->get();

        foreach ($items as $item) {
            $existingQuestions[] = $this->mapQuestionBankItemToQuestion($item, count($existingQuestions) + 1, $paper);
        }

        $paper->update([
            'questions_data' => json_encode($existingQuestions),
            'total_questions' => count($existingQuestions),
            'question_types' => $this->summarizeQuestionTypes($existingQuestions),
            'max_marks' => collect($existingQuestions)->sum(fn ($question) => (float) ($question['marks'] ?? 1)),
            'parse_status' => !empty($existingQuestions) ? 'done' : $paper->parse_status,
        ]);

        return back()->with('success', count($items) . ' question bank item(s) imported into this exam.');
    }

    private function sanitizeQuestions(array $questions): array
    {
        return collect($questions)
            ->map(function ($question, $index) {
                $options = collect($question['options'] ?? [])
                    ->map(fn ($option) => [
                        'label' => strtoupper(trim((string) ($option['label'] ?? ''))),
                        'text' => trim((string) ($option['text'] ?? '')),
                    ])
                    ->filter(fn ($option) => $option['label'] !== '' || $option['text'] !== '')
                    ->values()
                    ->all();

                $type = trim((string) ($question['type'] ?? 'mcq')) ?: 'mcq';
                $correctAnswer = $question['correct_answer'] ?? null;
                if (is_array($correctAnswer)) {
                    $correctAnswer = array_values(array_filter(array_map(
                        fn ($item) => strtoupper(trim((string) $item)),
                        $correctAnswer
                    )));
                } elseif (is_string($correctAnswer) && ($type === 'msq' || str_contains($correctAnswer, ','))) {
                    $correctAnswer = array_values(array_filter(array_map(
                        fn ($item) => strtoupper(trim((string) $item)),
                        explode(',', $correctAnswer)
                    )));
                } elseif ($correctAnswer !== null) {
                    $correctAnswer = trim((string) $correctAnswer);
                }

                return [
                    'serial' => (int) ($question['serial'] ?? ($index + 1)),
                    'text' => trim((string) ($question['text'] ?? '')),
                    'type' => $type,
                    'marks' => (float) ($question['marks'] ?? 1),
                    'interaction_type' => trim((string) ($question['interaction_type'] ?? '')) ?: null,
                    'qti_identifier' => trim((string) ($question['qti_identifier'] ?? '')) ?: null,
                    'advanced_metadata' => $this->parseKeyValueText($question['advanced_metadata_text'] ?? ''),
                    'options' => $options ?: null,
                    'correct_answer' => $correctAnswer,
                    'explanation' => trim((string) ($question['explanation'] ?? '')) ?: null,
                    'topic' => trim((string) ($question['topic'] ?? '')) ?: null,
                    'section' => trim((string) ($question['section'] ?? '')) ?: null,
                    'subject' => trim((string) ($question['subject'] ?? '')) ?: null,
                ];
            })
            ->filter(fn ($question) => $question['text'] !== '')
            ->values()
            ->all();
    }

    private function mapQuestionBankItemToQuestion(QuestionBankItem $item, int $serial, ExamPaper $paper): array
    {
        $correct = $item->correct_answer ?? [];
        $correct = is_array($correct) ? $correct : [$correct];

        return [
            'serial' => $serial,
            'type' => $item->question_type ?: 'mcq',
            'text' => $item->question_text,
            'marks' => (float) ($item->marks ?? 1),
            'subject' => $item->subject ?: ($paper->subject ?? null),
            'section' => $item->section,
            'topic' => $item->topic,
            'interaction_type' => $item->interaction_type,
            'qti_identifier' => $item->qti_identifier,
            'advanced_metadata' => $item->advanced_metadata,
            'correct_answer' => count($correct) <= 1 ? ($correct[0] ?? null) : array_values($correct),
            'explanation' => $item->explanation,
            'options' => collect($item->options ?? [])
                ->values()
                ->map(function ($option, $index) {
                    if (is_array($option)) {
                        return [
                            'label' => strtoupper(trim((string) ($option['label'] ?? chr(65 + $index)))),
                            'text' => trim((string) ($option['text'] ?? '')),
                        ];
                    }

                    return [
                        'label' => chr(65 + $index),
                        'text' => trim((string) $option),
                    ];
                })
                ->all(),
        ];
    }

    private function buildPublishReadiness(ExamPaper $paper, array $questions): array
    {
        $issues = [];

        if (empty(trim((string) $paper->title))) {
            $issues[] = 'Title is missing.';
        }

        if ((int) ($paper->duration_minutes ?? 0) <= 0) {
            $issues[] = 'Duration must be greater than zero.';
        }

        if (count($questions) === 0) {
            $issues[] = 'No questions are attached to this exam.';
        }

        $invalidQuestions = collect($questions)->filter(function ($question) {
            $type = $question['type'] ?? 'mcq';
            $text = trim((string) ($question['text'] ?? ''));
            $correct = $question['correct_answer'] ?? null;
            $options = collect($question['options'] ?? [])->filter(fn ($option) => trim((string) ($option['text'] ?? '')) !== '');

            if ($text === '') {
                return true;
            }

            if (in_array($type, ['mcq', 'msq', 'true_false'], true) && $options->count() < 2) {
                return true;
            }

            if ($correct === null || $correct === '' || (is_array($correct) && empty($correct))) {
                return true;
            }

            return false;
        })->count();

        if ($invalidQuestions > 0) {
            $issues[] = $invalidQuestions . ' question(s) are incomplete.';
        }

        return [
            'ready' => empty($issues),
            'issues' => $issues,
            'question_count' => count($questions),
            'invalid_questions' => $invalidQuestions,
        ];
    }

    private function summarizeQuestionTypes(array $questions): array
    {
        return collect($questions)
            ->groupBy(fn ($question) => $question['type'] ?? 'unknown')
            ->map(fn ($items) => $items->count())
            ->all();
    }

    private function parseKeyValueText($raw): array
    {
        if (is_array($raw)) {
            return $raw;
        }

        $raw = trim((string) $raw);
        if ($raw === '') {
            return [];
        }

        return collect(preg_split('/\r\n|\r|\n/', $raw))
            ->map(fn ($line) => trim((string) $line))
            ->filter()
            ->mapWithKeys(function ($line) {
                [$key, $value] = array_pad(explode(':', $line, 2), 2, '');
                $key = trim((string) $key);

                if ($key === '') {
                    return [];
                }

                return [$key => trim((string) $value)];
            })
            ->all();
    }

    private function parseSectionTimeRules(?string $raw): array
    {
        $raw = trim((string) $raw);
        if ($raw === '') {
            return [];
        }

        return collect(preg_split('/\r\n|\r|\n/', $raw))
            ->map(function ($line) {
                [$section, $minutes] = array_pad(explode(':', $line, 2), 2, null);
                $section = trim((string) $section);
                $minutes = (int) trim((string) $minutes);

                if ($section === '' || $minutes <= 0) {
                    return null;
                }

                return ['section' => $section, 'minutes' => $minutes];
            })
            ->filter()
            ->values()
            ->all();
    }

    private function parseSectionNegativeRules(?string $raw): array
    {
        $raw = trim((string) $raw);
        if ($raw === '') {
            return [];
        }

        return collect(preg_split('/\r\n|\r|\n/', $raw))
            ->map(function ($line) {
                [$section, $ratio] = array_pad(explode(':', $line, 2), 2, null);
                $section = trim((string) $section);
                $ratio = (float) trim((string) $ratio);

                if ($section === '' || $ratio < 0) {
                    return null;
                }

                return ['section' => $section, 'negative_marking' => $ratio];
            })
            ->filter()
            ->values()
            ->all();
    }

    private function parseExamSections(?string $raw): array
    {
        $raw = trim((string) $raw);
        if ($raw === '') {
            return [];
        }

        return collect(preg_split('/\r\n|\r|\n/', $raw))
            ->map(function ($line) {
                [$name, $description] = array_pad(explode(':', $line, 2), 2, null);
                $name = trim((string) $name);
                $description = trim((string) $description);

                if ($name === '') {
                    return null;
                }

                return [
                    'name' => $name,
                    'description' => $description !== '' ? $description : null,
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    public function syncExamToTao(\App\Models\ExamPaper $paper)
    {
        $tao = app(\App\Services\TAO\TaoService::class);
        if (! $tao->isConfigured()) {
            $this->logTaoSyncAttempt($paper, 'manual', false, 'TAO is not configured.');
            return back()->with('error', 'TAO is not configured.');
        }

        if (! $paper->isReadyForTaoSync()) {
            $this->logTaoSyncAttempt($paper, 'manual', false, 'Parse the exam paper successfully before syncing to TAO.');
            return back()->with('error', 'Parse the exam paper successfully before syncing to TAO.');
        }

        $result = $tao->syncExamPaper($paper);
        $paper->update([
            'tao_test_id' => $result['test_id'] ?? $paper->tao_test_id,
            'tao_delivery_id' => $result['delivery_id'] ?? $paper->tao_delivery_id,
            'tao_sync_status' => !empty($result['success']) ? 'synced' : 'failed',
            'tao_synced_at' => !empty($result['success']) ? now() : $paper->tao_synced_at,
            'tao_last_error' => !empty($result['success']) ? null : ($result['message'] ?? 'TAO sync failed.'),
        ]);

        $this->logTaoSyncAttempt(
            $paper->fresh(),
            'manual',
            !empty($result['success']),
            $result['message'] ?? 'TAO sync completed.',
            [
                'exam_paper_id' => $paper->id,
                'title' => $paper->title,
                'total_questions' => $paper->total_questions,
                'duration_minutes' => $paper->duration_minutes,
            ],
            $result
        );

        return back()->with(!empty($result['success']) ? 'success' : 'error', $result['message'] ?? 'TAO sync completed.');
    }

    protected function logTaoSyncAttempt(
        ExamPaper $paper,
        string $trigger,
        bool $success,
        string $message,
        ?array $requestPayload = null,
        ?array $responsePayload = null
    ): void {
        ExamPaperTaoSyncLog::create([
            'exam_paper_id' => $paper->id,
            'user_id' => auth()->id(),
            'trigger' => $trigger,
            'status' => $success ? 'success' : 'failed',
            'message' => $message,
            'request_payload' => $requestPayload,
            'response_payload' => $responsePayload,
            'tao_test_id' => $paper->tao_test_id,
            'tao_delivery_id' => $paper->tao_delivery_id,
        ]);
    }

    public function storePaper(Request $r)
    {
        $r->validate([
            'title'           => 'required|string|max:255',
            'subject'         => 'nullable|string|max:255',
            'category_id'     => 'required|exists:categories,id',
            'description'     => 'nullable|string|max:2000',
            'language'        => 'required|in:English,Hindi,Both',
            'duration_minutes'=> 'required|integer|min:10|max:360',
            'max_marks'       => 'required|integer|min:10',
            'negative_marking'=> 'nullable|numeric|min:0|max:1',
            'max_retakes'     => 'required|integer|min:1|max:10',
            'difficulty'      => 'required|in:easy,medium,hard',
            'seller_price'    => 'required|numeric|min:0',
            'is_free'         => 'boolean',
            'tags'            => 'nullable|string',
            'exam_sections_text' => 'nullable|string',
            'interoperability_profile' => 'nullable|string|max:100',
            'qti_metadata_text' => 'nullable|string',
            'input_type'      => 'required|in:pdf,url,typed',
            'pdf_file'        => 'required_if:input_type,pdf|file|mimes:pdf|max:51200',
            'pdf_url'         => 'required_if:input_type,url|nullable|url',
            'typed_content'   => 'required_if:input_type,typed|nullable|string',
            'publish_now'     => 'nullable|boolean',
        ]);

        $markupPct    = (float) \App\Models\PlatformSetting::get('default_commission', 15);
        $sellerPrice  = (float) $r->seller_price;
        $markup       = round($sellerPrice * $markupPct / 100, 2);
        $studentPrice = $sellerPrice + $markup;

        $paper = \App\Models\ExamPaper::create([
            'seller_id'        => auth()->id(),
            'category_id'      => $r->category_id,
            'title'            => $r->title,
            'subject'          => $r->subject,
            'exam_type'        => $r->exam_type ?? 'mock',
            'slug'             => \Illuminate\Support\Str::slug($r->title) . '-' . \Illuminate\Support\Str::random(5),
            'description'      => $r->description,
            'language'         => $r->language,
            'source'           => $r->input_type === 'typed' ? 'typed' : ($r->input_type === 'url' ? 'upload' : 'upload'),
            'duration_minutes' => $r->duration_minutes,
            'max_marks'        => $r->max_marks,
            'negative_marking' => $r->negative_marking ?? 0,
            'max_retakes'      => $r->max_retakes,
            'difficulty'       => $r->difficulty,
            'seller_price'     => $sellerPrice,
            'platform_markup'  => $markup,
            'student_price'    => $r->boolean('is_free') ? 0 : $studentPrice,
            'is_free'          => $r->boolean('is_free'),
            'tags'             => $r->tags ? array_map('trim', explode(',', $r->tags)) : [],
            'exam_sections'    => $this->parseExamSections($r->input('exam_sections_text', '')),
            'interoperability_profile' => $r->input('interoperability_profile') ?: null,
            'qti_metadata'     => $this->parseKeyValueText($r->input('qti_metadata_text', '')),
            'status'           => $r->boolean('publish_now') ? 'approved' : 'draft',
            'parse_status'     => 'pending',
        ]);

        $disk = 'public';
        if ($r->input_type === 'pdf' && $r->hasFile('pdf_file')) {
            $path = $r->file('pdf_file')->store("papers/{$paper->id}", $disk);
            $paper->update(['original_file' => $path]);
            \App\Jobs\ParseExamPaperJob::dispatch($paper, 'pdf');
        } elseif ($r->input_type === 'url' && $r->pdf_url) {
            $resp = \Illuminate\Support\Facades\Http::timeout(60)->get($r->pdf_url);
            if (! $resp->successful()) {
                return back()->withErrors(['pdf_url' => 'Failed to download PDF from URL.'])->withInput();
            }
            $path = "papers/{$paper->id}/" . \Illuminate\Support\Str::uuid() . '.pdf';
            \Illuminate\Support\Facades\Storage::disk($disk)->put($path, $resp->body());
            $paper->update(['original_file' => $path]);
            \App\Jobs\ParseExamPaperJob::dispatch($paper, 'pdf');
        } elseif ($r->input_type === 'typed' && $r->typed_content) {
            \App\Jobs\ParseExamPaperJob::dispatch($paper, 'typed', $r->typed_content);
        }

        return redirect()->route('admin.papers.create', ['paper_id' => $paper->id])->with('success', 'Paper created. Parsing started.');
    }

    public function parseStatus(\App\Models\ExamPaper $paper)
    {
        return response()->json([
            'status'          => $paper->parse_status,
            'total_questions' => $paper->total_questions,
            'question_types'  => $paper->question_types,
            'log'             => $paper->parse_log,
        ]);
    }
}
