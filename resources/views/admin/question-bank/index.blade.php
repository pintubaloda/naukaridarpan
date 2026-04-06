@extends('layouts.app')
@section('title','Question Bank — Naukaridarpan')
@section('content')
<div class="container section">
  <div class="dash-layout">
    @include('components.admin-sidebar')

    <main>
      <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:1rem;flex-wrap:wrap;margin-bottom:1.5rem">
        <div>
          <h2 class="mb-1">Question Bank</h2>
          <p class="text-muted" style="margin:0">Build a reusable pool of questions that we can use across multiple exams later.</p>
        </div>
        <div style="display:flex;gap:.75rem;flex-wrap:wrap">
          <div class="card card-static card-body" style="padding:.8rem 1rem;min-width:150px">
            <div class="text-muted" style="font-size:.78rem">Total Questions</div>
            <div style="font-weight:700;font-size:1.2rem">{{ number_format($totalQuestions ?? $items->total()) }}</div>
          </div>
          <div class="card card-static card-body" style="padding:.8rem 1rem;min-width:150px">
            <div class="text-muted" style="font-size:.78rem">Active Pool</div>
            <div style="font-weight:700;font-size:1.2rem">{{ number_format($activeQuestions ?? 0) }}</div>
          </div>
        </div>
      </div>

      <div class="g-grid" style="grid-template-columns:380px 1fr;gap:1rem;align-items:start">
        <section class="card card-static card-body">
          <h3 style="font-size:1rem;margin-bottom:1rem">Add Question</h3>
          <form method="POST" action="{{ route('admin.question-bank.store') }}">
            @csrf
            <label class="label">Category</label>
            <select name="category_id" class="input" required>
              <option value="">Select category</option>
              @foreach($categories as $category)
              <option value="{{ $category->id }}">{{ $category->name }}</option>
              @endforeach
            </select>

            <div class="g-grid" style="grid-template-columns:1fr 1fr;gap:.75rem;margin-top:.75rem">
              <div>
                <label class="label">Subject</label>
                <input name="subject" class="input" placeholder="Reasoning">
              </div>
              <div>
                <label class="label">Section</label>
                <input name="section" class="input" placeholder="Logical Reasoning">
              </div>
            </div>

            <div class="g-grid" style="grid-template-columns:1fr 1fr;gap:.75rem;margin-top:.75rem">
              <div>
                <label class="label">Topic</label>
                <input name="topic" class="input" placeholder="Series">
              </div>
              <div>
                <label class="label">Difficulty</label>
                <select name="difficulty" class="input" required>
                  <option value="easy">Easy</option>
                  <option value="medium" selected>Medium</option>
                  <option value="hard">Hard</option>
                </select>
              </div>
            </div>

            <div style="margin-top:.75rem">
              <label class="label">Question Type</label>
              <select name="question_type" class="input" required>
                <option value="mcq">MCQ</option>
                <option value="msq">MSQ</option>
                <option value="true_false">True / False</option>
                <option value="short">Short Answer</option>
                <option value="long">Long Answer</option>
                <option value="numeric">Numeric</option>
              </select>
            </div>

            <div style="margin-top:.75rem">
              <label class="label">Interaction Type</label>
              <input name="interaction_type" class="input" placeholder="choiceInteraction, textEntryInteraction">
            </div>

            <div style="margin-top:.75rem">
              <label class="label">QTI Identifier</label>
              <input name="qti_identifier" class="input" placeholder="item-identifier-001">
            </div>

            <div style="margin-top:.75rem">
              <label class="label">Question Text</label>
              <textarea name="question_text" class="input" rows="4" required></textarea>
            </div>

            <div style="margin-top:.75rem">
              <label class="label">Options</label>
              <textarea name="options_text" class="input" rows="4" placeholder="One option per line"></textarea>
            </div>

            <div style="margin-top:.75rem">
              <label class="label">Correct Answer</label>
              <input name="correct_answer" class="input" placeholder="A or A,C for MSQ" required>
            </div>

            <div class="g-grid" style="grid-template-columns:1fr 1fr;gap:.75rem;margin-top:.75rem">
              <div>
                <label class="label">Marks</label>
                <input type="number" step="0.25" min="0" name="marks" class="input" value="1" required>
              </div>
              <div>
                <label class="label">Negative Marking</label>
                <input type="number" step="0.25" min="0" name="negative_marking" class="input" value="0">
              </div>
            </div>

            <div style="margin-top:.75rem">
              <label class="label">Explanation</label>
              <textarea name="explanation" class="input" rows="3"></textarea>
            </div>

            <div style="margin-top:.75rem">
              <label class="label">Advanced Metadata</label>
              <textarea name="advanced_metadata_text" class="input" rows="3" placeholder="responseCardinality: single&#10;shuffle: true"></textarea>
            </div>

            <div style="margin-top:.75rem">
              <label class="label">Tags</label>
              <input name="tags_text" class="input" placeholder="banking, prelims, reasoning">
            </div>

            <label style="display:flex;align-items:center;gap:.5rem;margin-top:.9rem">
              <input type="checkbox" name="is_active" value="1" checked>
              <span>Active for reuse</span>
            </label>

            <button class="btn btn-primary" type="submit" style="margin-top:1rem;width:100%">Save To Question Bank</button>
          </form>
        </section>

        <section>
          <form method="GET" class="card card-static card-body" style="margin-bottom:1rem">
            <div class="g-grid" style="grid-template-columns:2fr 1fr 1fr 1fr;gap:.75rem">
              <input name="search" value="{{ request('search') }}" class="input" placeholder="Search question text, topic, section">
              <select name="category_id" class="input">
                <option value="">All categories</option>
                @foreach($categories as $category)
                <option value="{{ $category->id }}" @selected((string) request('category_id') === (string) $category->id)>{{ $category->name }}</option>
                @endforeach
              </select>
              <select name="question_type" class="input">
                <option value="">All types</option>
                @foreach(['mcq' => 'MCQ','msq' => 'MSQ','true_false' => 'True / False','short' => 'Short','long' => 'Long','numeric' => 'Numeric'] as $value => $label)
                <option value="{{ $value }}" @selected(request('question_type') === $value)>{{ $label }}</option>
                @endforeach
              </select>
              <select name="difficulty" class="input">
                <option value="">All difficulty</option>
                @foreach(['easy' => 'Easy','medium' => 'Medium','hard' => 'Hard'] as $value => $label)
                <option value="{{ $value }}" @selected(request('difficulty') === $value)>{{ $label }}</option>
                @endforeach
              </select>
            </div>
            <div style="margin-top:.75rem;display:flex;gap:.75rem;align-items:center">
              <select name="subject" class="input" style="max-width:240px">
                <option value="">All subjects</option>
                @foreach($subjects as $subject)
                <option value="{{ $subject }}" @selected(request('subject') === $subject)>{{ $subject }}</option>
                @endforeach
              </select>
              <button class="btn btn-secondary" type="submit">Apply Filters</button>
            </div>
          </form>

          <div class="card card-static">
            <div style="padding:1rem 1.25rem;border-bottom:1px solid var(--border-l)">
              <h3 style="font-size:1rem">Reusable Questions</h3>
            </div>
            <div style="padding:1rem 1.25rem;display:grid;gap:1rem">
              @forelse($items as $item)
              <article style="border:1px solid var(--border-l);border-radius:16px;padding:1rem;background:#fff">
                <div style="display:flex;justify-content:space-between;gap:1rem;align-items:flex-start">
                  <div>
                    <div style="font-size:.78rem;color:var(--ink-l);margin-bottom:.35rem">{{ $item->category->name ?? 'Uncategorized' }} · {{ strtoupper($item->question_type) }} · {{ ucfirst($item->difficulty) }}</div>
                    <div style="font-weight:600;font-family:var(--fu);margin-bottom:.35rem">{{ $item->question_text }}</div>
                    <div class="text-muted" style="font-size:.85rem">{{ collect([$item->subject, $item->section, $item->topic])->filter()->join(' · ') }}</div>
                  </div>
                  <div style="text-align:right;font-size:.82rem">
                    <div>{{ number_format((float) $item->marks, 2) }} marks</div>
                    <div class="text-muted">-{{ number_format((float) $item->negative_marking, 2) }}</div>
                  </div>
                </div>
                @if(!empty($item->options))
                <div style="margin-top:.75rem;display:grid;gap:.35rem">
                  @foreach($item->options as $option)
                  <div style="padding:.45rem .6rem;background:var(--paper);border-radius:10px">{{ $option }}</div>
                  @endforeach
                </div>
                @endif
                <div style="margin-top:.75rem;font-size:.84rem">
                  <strong>Correct:</strong> {{ collect($item->correct_answer)->join(', ') ?: '—' }}
                </div>
              @if($item->explanation)
                <div class="text-muted" style="margin-top:.4rem;font-size:.84rem">{{ $item->explanation }}</div>
                @endif
                <div style="display:flex;justify-content:space-between;gap:.75rem;align-items:center;flex-wrap:wrap;margin-top:.9rem">
                  <details>
                    <summary style="cursor:pointer;color:var(--teal);font-family:var(--fu);font-size:.88rem">Edit Question</summary>
                    <form method="POST" action="{{ route('admin.question-bank.update', $item) }}" style="margin-top:.8rem;display:grid;gap:.65rem;min-width:min(560px, 100%)">
                      @csrf
                      @method('PUT')
                      <select name="category_id" class="input" required>
                        @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ (string) $item->category_id === (string) $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                        @endforeach
                      </select>
                      <div class="g-grid" style="grid-template-columns:1fr 1fr 1fr;gap:.65rem">
                        <input name="subject" class="input" value="{{ $item->subject }}" placeholder="Subject">
                        <input name="section" class="input" value="{{ $item->section }}" placeholder="Section">
                        <input name="topic" class="input" value="{{ $item->topic }}" placeholder="Topic">
                      </div>
                      <div class="g-grid" style="grid-template-columns:1fr 1fr;gap:.65rem">
                        <select name="difficulty" class="input" required>
                          @foreach(['easy' => 'Easy','medium' => 'Medium','hard' => 'Hard'] as $value => $label)
                          <option value="{{ $value }}" {{ $item->difficulty === $value ? 'selected' : '' }}>{{ $label }}</option>
                          @endforeach
                        </select>
                        <select name="question_type" class="input" required>
                          @foreach(['mcq' => 'MCQ','msq' => 'MSQ','true_false' => 'True / False','short' => 'Short','long' => 'Long','numeric' => 'Numeric'] as $value => $label)
                          <option value="{{ $value }}" {{ $item->question_type === $value ? 'selected' : '' }}>{{ $label }}</option>
                          @endforeach
                        </select>
                      </div>
                      <input name="interaction_type" class="input" value="{{ $item->interaction_type }}" placeholder="Interaction type">
                      <input name="qti_identifier" class="input" value="{{ $item->qti_identifier }}" placeholder="QTI identifier">
                      <textarea name="question_text" class="input" rows="4" required>{{ $item->question_text }}</textarea>
                      <textarea name="options_text" class="input" rows="4" placeholder="One option per line">{{ collect($item->options ?? [])->map(function ($option) { return is_array($option) ? ($option['text'] ?? '') : $option; })->implode("\n") }}</textarea>
                      <input name="correct_answer" class="input" value="{{ collect($item->correct_answer ?? [])->join(', ') }}" required>
                      <div class="g-grid" style="grid-template-columns:1fr 1fr;gap:.65rem">
                        <input type="number" step="0.25" min="0" name="marks" class="input" value="{{ $item->marks }}" required>
                        <input type="number" step="0.25" min="0" name="negative_marking" class="input" value="{{ $item->negative_marking }}">
                      </div>
                      <textarea name="explanation" class="input" rows="3" placeholder="Explanation">{{ $item->explanation }}</textarea>
                      <textarea name="advanced_metadata_text" class="input" rows="3" placeholder="key: value">{{ collect($item->advanced_metadata ?? [])->map(fn ($value, $key) => $key.': '.$value)->implode("\n") }}</textarea>
                      <input name="tags_text" class="input" value="{{ collect($item->tags ?? [])->join(', ') }}" placeholder="tag1, tag2">
                      <label style="display:flex;align-items:center;gap:.5rem">
                        <input type="checkbox" name="is_active" value="1" {{ $item->is_active ? 'checked' : '' }}>
                        <span>Active for reuse</span>
                      </label>
                      <div style="display:flex;justify-content:flex-end">
                        <button type="submit" class="btn btn-outline btn-sm">Save Changes</button>
                      </div>
                    </form>
                  </details>
                  <div style="display:flex;gap:.5rem;align-items:center">
                    <form method="POST" action="{{ route('admin.question-bank.clone', $item) }}">
                      @csrf
                      <button type="submit" class="btn btn-ghost btn-sm">Clone</button>
                    </form>
                    <form method="POST" action="{{ route('admin.question-bank.destroy', $item) }}" onsubmit="return confirm('Delete this question bank item?');">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn btn-ghost btn-sm" style="color:var(--err)">Delete</button>
                    </form>
                  </div>
                </div>
              </article>
              @empty
              <div class="text-muted" style="padding:2rem;text-align:center">No reusable questions yet. Add the first one from the left panel.</div>
              @endforelse
            </div>
            <div style="padding:1rem 1.25rem;border-top:1px solid var(--border-l)">{{ $items->links() }}</div>
          </div>
        </section>
      </div>
    </main>
  </div>
</div>
@endsection
