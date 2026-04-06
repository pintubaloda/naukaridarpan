<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\ExamPaper;
use App\Models\ExamTemplate;
use App\Models\QuestionBankItem;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ExamAuthoringController extends Controller
{
    public function questionBank(Request $request)
    {
        $query = QuestionBankItem::with(['category', 'creator'])->latest();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($builder) use ($search) {
                $builder->where('question_text', 'like', '%' . $search . '%')
                    ->orWhere('subject', 'like', '%' . $search . '%')
                    ->orWhere('topic', 'like', '%' . $search . '%')
                    ->orWhere('section', 'like', '%' . $search . '%');
            });
        }

        foreach (['category_id', 'question_type', 'difficulty', 'subject', 'bank_name'] as $filter) {
            if ($request->filled($filter)) {
                $query->where($filter, $request->{$filter});
            }
        }

        $items = $query->paginate(20)->withQueryString();
        $categories = Category::orderBy('name')->get(['id', 'name']);
        $subjects = QuestionBankItem::query()->whereNotNull('subject')->distinct()->orderBy('subject')->pluck('subject');
        $bankNames = QuestionBankItem::query()->whereNotNull('bank_name')->distinct()->orderBy('bank_name')->pluck('bank_name');
        $totalQuestions = QuestionBankItem::count();
        $activeQuestions = QuestionBankItem::where('is_active', true)->count();

        return view('admin.question-bank.index', compact('items', 'categories', 'subjects', 'bankNames', 'totalQuestions', 'activeQuestions'));
    }

    public function storeQuestionBankItem(Request $request)
    {
        $data = $this->validateQuestionBankItem($request);

        QuestionBankItem::create([
            'created_by' => auth()->id(),
            'category_id' => $data['category_id'],
            'bank_name' => $data['bank_name'] ?? null,
            'subject' => $data['subject'] ?? null,
            'section' => $data['section'] ?? null,
            'topic' => $data['topic'] ?? null,
            'difficulty' => $data['difficulty'],
            'question_type' => $data['question_type'],
            'interaction_type' => $data['interaction_type'] ?? null,
            'qti_identifier' => $data['qti_identifier'] ?? null,
            'question_text' => $data['question_text'],
            'options' => $this->parseOptionsText($data['options_text'] ?? null),
            'correct_answer' => $this->normalizeCorrectAnswer($data['correct_answer']),
            'advanced_metadata' => $this->parseAdvancedMetadata($data['advanced_metadata_text'] ?? ''),
            'explanation' => $data['explanation'] ?? null,
            'marks' => $data['marks'],
            'negative_marking' => $data['negative_marking'] ?? 0,
            'tags' => collect(explode(',', (string) ($data['tags_text'] ?? '')))
                ->map(fn ($tag) => trim($tag))
                ->filter()
                ->values()
                ->all(),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return back()->with('success', 'Question added to the reusable bank.');
    }

    public function updateQuestionBankItem(Request $request, QuestionBankItem $item)
    {
        $data = $this->validateQuestionBankItem($request);

        $item->update([
            'category_id' => $data['category_id'],
            'bank_name' => $data['bank_name'] ?? null,
            'subject' => $data['subject'] ?? null,
            'section' => $data['section'] ?? null,
            'topic' => $data['topic'] ?? null,
            'difficulty' => $data['difficulty'],
            'question_type' => $data['question_type'],
            'interaction_type' => $data['interaction_type'] ?? null,
            'qti_identifier' => $data['qti_identifier'] ?? null,
            'question_text' => $data['question_text'],
            'options' => $this->parseOptionsText($data['options_text'] ?? null),
            'correct_answer' => $this->normalizeCorrectAnswer($data['correct_answer']),
            'advanced_metadata' => $this->parseAdvancedMetadata($data['advanced_metadata_text'] ?? ''),
            'explanation' => $data['explanation'] ?? null,
            'marks' => $data['marks'],
            'negative_marking' => $data['negative_marking'] ?? 0,
            'tags' => collect(explode(',', (string) ($data['tags_text'] ?? '')))
                ->map(fn ($tag) => trim($tag))
                ->filter()
                ->values()
                ->all(),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return back()->with('success', 'Question bank item updated.');
    }

    public function cloneQuestionBankItem(QuestionBankItem $item)
    {
        QuestionBankItem::create([
            'created_by' => auth()->id(),
            'category_id' => $item->category_id,
            'bank_name' => $item->bank_name,
            'subject' => $item->subject,
            'section' => $item->section,
            'topic' => $item->topic,
            'difficulty' => $item->difficulty,
            'question_type' => $item->question_type,
            'interaction_type' => $item->interaction_type,
            'qti_identifier' => $item->qti_identifier,
            'question_text' => $item->question_text,
            'options' => $item->options,
            'correct_answer' => $item->correct_answer,
            'advanced_metadata' => $item->advanced_metadata,
            'explanation' => $item->explanation,
            'marks' => $item->marks,
            'negative_marking' => $item->negative_marking,
            'tags' => $item->tags,
            'is_active' => $item->is_active,
        ]);

        return back()->with('success', 'Question bank item cloned.');
    }

    public function destroyQuestionBankItem(QuestionBankItem $item)
    {
        $item->delete();

        return back()->with('success', 'Question bank item deleted.');
    }

    public function templates()
    {
        $templates = ExamTemplate::with(['category', 'creator'])->latest()->paginate(20);
        $categories = Category::orderBy('name')->get(['id', 'name']);

        return view('admin.exam-templates.index', compact('templates', 'categories'));
    }

    public function storeTemplate(Request $request)
    {
        $data = $this->validateTemplate($request);

        $sections = $this->parseSections($data['sections_text'] ?? null);

        ExamTemplate::create([
            'created_by' => auth()->id(),
            'category_id' => $data['category_id'],
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'duration_minutes' => $data['duration_minutes'],
            'default_negative_marking' => $data['default_negative_marking'] ?? 0,
            'sections' => $sections,
            'template_data' => [
                'sections_count' => count($sections),
                'section_names' => array_column($sections, 'name'),
            ],
            'is_active' => true,
        ]);

        return back()->with('success', 'Exam template saved.');
    }

    public function updateTemplate(Request $request, ExamTemplate $template)
    {
        $data = $this->validateTemplate($request);
        $sections = $this->parseSections($data['sections_text'] ?? null);

        $template->update([
            'category_id' => $data['category_id'],
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'duration_minutes' => $data['duration_minutes'],
            'default_negative_marking' => $data['default_negative_marking'] ?? 0,
            'sections' => $sections,
            'template_data' => [
                'sections_count' => count($sections),
                'section_names' => array_column($sections, 'name'),
            ],
        ]);

        return back()->with('success', 'Exam template updated.');
    }

    public function cloneTemplate(ExamTemplate $template)
    {
        $clone = ExamTemplate::create([
            'created_by' => auth()->id(),
            'category_id' => $template->category_id,
            'name' => $template->name . ' Copy',
            'description' => $template->description,
            'duration_minutes' => $template->duration_minutes,
            'default_negative_marking' => $template->default_negative_marking,
            'sections' => $template->sections,
            'template_data' => $template->template_data,
            'is_active' => $template->is_active,
        ]);

        return back()->with('success', 'Template cloned as "' . $clone->name . '".');
    }

    public function destroyTemplate(ExamTemplate $template)
    {
        $template->delete();

        return back()->with('success', 'Exam template deleted.');
    }

    public function createExamFromTemplate(ExamTemplate $template)
    {
        $paper = ExamPaper::create([
            'seller_id' => auth()->id(),
            'category_id' => $template->category_id ?: Category::query()->value('id'),
            'title' => $template->name . ' Draft',
            'subject' => null,
            'exam_type' => 'mock',
            'slug' => Str::slug($template->name . '-draft') . '-' . Str::lower(Str::random(5)),
            'description' => $template->description,
            'language' => 'English',
            'source' => 'typed',
            'parse_status' => 'done',
            'questions_data' => json_encode([]),
            'total_questions' => 0,
            'duration_minutes' => $template->duration_minutes ?: 60,
            'max_marks' => 0,
            'negative_marking' => $template->default_negative_marking ?? 0,
            'max_retakes' => 3,
            'difficulty' => 'medium',
            'question_types' => [],
            'exam_sections' => $template->sections ?? [],
            'seller_price' => 0,
            'platform_markup' => 0,
            'student_price' => 0,
            'is_free' => true,
            'tags' => ['template:' . $template->id],
            'status' => 'draft',
        ]);

        return redirect()
            ->route('admin.exams.edit', $paper)
            ->with('success', 'Draft exam created from template. You can now import question-bank items or write questions directly.');
    }

    private function parseOptionsText(?string $raw): array
    {
        return collect(preg_split('/\r\n|\r|\n/', (string) $raw))
            ->map(fn ($line) => trim($line))
            ->filter()
            ->values()
            ->all();
    }

    private function normalizeCorrectAnswer(string $raw): array
    {
        return collect(explode(',', $raw))
            ->map(fn ($value) => strtoupper(trim($value)))
            ->filter()
            ->values()
            ->all();
    }

    private function validateTemplate(Request $request): array
    {
        return $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'duration_minutes' => 'required|integer|min:10|max:600',
            'default_negative_marking' => 'nullable|numeric|min:0|max:10',
            'sections_text' => 'nullable|string',
        ]);
    }

    private function validateQuestionBankItem(Request $request): array
    {
        return $request->validate([
            'category_id' => 'required|exists:categories,id',
            'bank_name' => 'nullable|string|max:120',
            'subject' => 'nullable|string|max:255',
            'section' => 'nullable|string|max:255',
            'topic' => 'nullable|string|max:255',
            'difficulty' => 'required|in:easy,medium,hard',
            'question_type' => 'required|in:mcq,msq,true_false,short,long,numeric',
            'interaction_type' => 'nullable|string|max:100',
            'qti_identifier' => 'nullable|string|max:255',
            'question_text' => 'required|string',
            'options_text' => 'nullable|string',
            'correct_answer' => 'required|string',
            'advanced_metadata_text' => 'nullable|string',
            'explanation' => 'nullable|string',
            'marks' => 'required|numeric|min:0',
            'negative_marking' => 'nullable|numeric|min:0|max:10',
            'tags_text' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);
    }

    private function parseAdvancedMetadata(string $raw): array
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

    private function parseSections(?string $raw): array
    {
        return collect(preg_split('/\r\n|\r|\n/', (string) $raw))
            ->map(function ($line) {
                $line = trim($line);
                if ($line === '') {
                    return null;
                }

                [$name, $notes] = array_pad(explode(':', $line, 2), 2, null);

                return [
                    'name' => trim($name),
                    'notes' => $notes !== null ? trim($notes) : null,
                ];
            })
            ->filter(fn ($section) => !empty($section['name']))
            ->values()
            ->all();
    }
}
