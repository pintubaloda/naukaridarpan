@extends('layouts.app')
@section('title','Exam Templates — Naukaridarpan')
@section('content')
<div class="container section">
  <div class="dash-layout">
    @include('components.admin-sidebar')

    <main>
      <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:1rem;flex-wrap:wrap;margin-bottom:1.5rem">
        <div>
          <h2 class="mb-1">Exam Templates</h2>
          <p class="text-muted" style="margin:0">Save repeatable exam structures so we can spin up new papers faster and more consistently.</p>
        </div>
      </div>

      <div class="g-grid" style="grid-template-columns:380px 1fr;gap:1rem;align-items:start">
        <section class="card card-static card-body">
          <h3 style="font-size:1rem;margin-bottom:1rem">Create Template</h3>
          <form method="POST" action="{{ route('admin.exam-templates.store') }}">
            @csrf
            <label class="label">Category</label>
            <select name="category_id" class="input" required>
              <option value="">Select category</option>
              @foreach($categories as $category)
              <option value="{{ $category->id }}">{{ $category->name }}</option>
              @endforeach
            </select>

            <div style="margin-top:.75rem">
              <label class="label">Template Name</label>
              <input name="name" class="input" placeholder="SSC Prelims Standard Template" required>
            </div>

            <div style="margin-top:.75rem">
              <label class="label">Description</label>
              <textarea name="description" class="input" rows="3" placeholder="What this template is best used for"></textarea>
            </div>

            <div class="g-grid" style="grid-template-columns:1fr 1fr;gap:.75rem;margin-top:.75rem">
              <div>
                <label class="label">Duration (Minutes)</label>
                <input type="number" min="10" max="600" name="duration_minutes" class="input" value="60" required>
              </div>
              <div>
                <label class="label">Default Negative Marking</label>
                <input type="number" step="0.25" min="0" name="default_negative_marking" class="input" value="0.25">
              </div>
            </div>

            <div style="margin-top:.75rem">
              <label class="label">Sections</label>
              <textarea name="sections_text" class="input" rows="6" placeholder="General Awareness: 25 questions\nQuantitative Aptitude: 25 questions\nReasoning: 25 questions"></textarea>
              <div class="text-muted" style="font-size:.8rem;margin-top:.35rem">One section per line. Use <code>Name: Notes</code>.</div>
            </div>

            <button class="btn btn-primary" type="submit" style="margin-top:1rem;width:100%">Save Template</button>
          </form>
        </section>

        <section>
          <div class="card card-static">
            <div style="padding:1rem 1.25rem;border-bottom:1px solid var(--border-l)">
              <h3 style="font-size:1rem">Saved Templates</h3>
            </div>
            <div style="padding:1rem 1.25rem;display:grid;gap:1rem">
              @forelse($templates as $template)
              <article style="border:1px solid var(--border-l);border-radius:16px;padding:1rem;background:#fff">
                <div style="display:flex;justify-content:space-between;gap:1rem;align-items:flex-start">
                  <div>
                    <div style="font-size:.78rem;color:var(--ink-l);margin-bottom:.35rem">{{ $template->category->name ?? 'Uncategorized' }}</div>
                    <div style="font-weight:600;font-family:var(--fu);margin-bottom:.35rem">{{ $template->name }}</div>
                    @if($template->description)
                    <div class="text-muted" style="font-size:.88rem">{{ $template->description }}</div>
                    @endif
                  </div>
                  <div style="text-align:right;font-size:.82rem">
                    <div>{{ $template->duration_minutes }} min</div>
                    <div class="text-muted">-{{ number_format((float) $template->default_negative_marking, 2) }}</div>
                  </div>
                </div>

                @if(!empty($template->sections))
                <div style="margin-top:.9rem;display:grid;gap:.5rem">
                  @foreach($template->sections as $section)
                  <div style="padding:.55rem .7rem;background:var(--paper);border-radius:12px">
                    <div style="font-weight:600">{{ $section['name'] ?? 'Section' }}</div>
                    @if(!empty($section['notes']))
                    <div class="text-muted" style="font-size:.82rem;margin-top:.15rem">{{ $section['notes'] }}</div>
                    @endif
                  </div>
                  @endforeach
                </div>
                @endif
                <div style="display:flex;justify-content:space-between;gap:.75rem;align-items:center;flex-wrap:wrap;margin-top:1rem">
                  <details>
                    <summary style="cursor:pointer;color:var(--teal);font-family:var(--fu);font-size:.88rem">Edit Template</summary>
                    <form method="POST" action="{{ route('admin.exam-templates.update', $template) }}" style="margin-top:.8rem;display:grid;gap:.65rem;min-width:min(520px, 100%)">
                      @csrf
                      @method('PUT')
                      <select name="category_id" class="input" required>
                        @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ (string) $template->category_id === (string) $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                        @endforeach
                      </select>
                      <input name="name" class="input" value="{{ $template->name }}" required>
                      <textarea name="description" class="input" rows="3">{{ $template->description }}</textarea>
                      <div class="g-grid" style="grid-template-columns:1fr 1fr;gap:.65rem">
                        <input type="number" min="10" max="600" name="duration_minutes" class="input" value="{{ $template->duration_minutes }}" required>
                        <input type="number" step="0.25" min="0" name="default_negative_marking" class="input" value="{{ $template->default_negative_marking }}">
                      </div>
                      <textarea name="sections_text" class="input" rows="5">@foreach($template->sections ?? [] as $section){{ $section['name'] ?? 'Section' }}@if(!empty($section['notes'])): {{ $section['notes'] }}@endif
@endforeach</textarea>
                      <div style="display:flex;justify-content:flex-end">
                        <button type="submit" class="btn btn-outline btn-sm">Save Changes</button>
                      </div>
                    </form>
                  </details>
                  <form method="POST" action="{{ route('admin.exam-templates.create-exam', $template) }}">
                    @csrf
                    <button type="submit" class="btn btn-outline btn-sm">Create Draft Exam From Template</button>
                  </form>
                  <div style="display:flex;gap:.5rem;align-items:center">
                    <form method="POST" action="{{ route('admin.exam-templates.clone', $template) }}">
                      @csrf
                      <button type="submit" class="btn btn-ghost btn-sm">Clone</button>
                    </form>
                    <form method="POST" action="{{ route('admin.exam-templates.destroy', $template) }}" onsubmit="return confirm('Delete this template? Existing exams will not be affected.');">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn btn-ghost btn-sm" style="color:var(--err)">Delete</button>
                    </form>
                  </div>
                </div>
              </article>
              @empty
              <div class="text-muted" style="padding:2rem;text-align:center">No templates yet. Save your first exam blueprint from the left panel.</div>
              @endforelse
            </div>
            <div style="padding:1rem 1.25rem;border-top:1px solid var(--border-l)">{{ $templates->links() }}</div>
          </div>
        </section>
      </div>
    </main>
  </div>
</div>
@endsection
