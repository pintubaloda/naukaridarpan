<div class="card exam-card">
  <div class="exam-thumb">
    @if($exam->thumbnail)
      <img src="{{ $exam->thumbnail }}" alt="{{ $exam->title }}">
    @else
      @php $icons=['upsc'=>'🏛️','ssc'=>'📋','banking'=>'🏦','railway'=>'🚂','state-psc'=>'🗺️','defence'=>'🎖️','police'=>'👮','teaching'=>'📚','neet'=>'🩺','jee'=>'⚙️','gate'=>'🔬','law'=>'⚖️']; @endphp
      {{ $icons[$exam->category->slug ?? ''] ?? '📝' }}
    @endif
    @if($exam->is_free)
      <span class="badge badge-green free-tag">Free</span>
    @endif
  </div>
  <div class="exam-card-body">
    <div class="exam-card-meta">
      <span class="badge badge-teal" style="font-size:.65rem">{{ $exam->category->name ?? 'Exam' }}</span>
      @if($exam->exam_type)
        <span>•</span>
        <span class="badge badge-gray" style="font-size:.65rem">{{ $exam->exam_type==='previous_year' ? 'PYQ' : 'Mock' }}</span>
      @endif
      <span>•</span>
      <span class="badge badge-gray" style="font-size:.65rem">{{ ucfirst($exam->difficulty) }}</span>
    </div>
    <div class="exam-card-title">{{ $exam->title }}</div>
    @if($exam->subject)
      <div style="font-size:.82rem;color:var(--ink-l);margin-top:.25rem">{{ $exam->subject }}</div>
    @endif
    <div class="exam-card-stats">
      <span>📝 {{ $exam->total_questions }} Qs</span>
      <span>⏱ {{ $exam->duration_minutes }} min</span>
      <span>🏆 {{ $exam->max_marks }} marks</span>
    </div>
  </div>
  <div class="exam-card-footer">
    <div>
      <div class="exam-price">
        @if($exam->is_free)<span class="free">FREE</span>@else₹{{ number_format($exam->student_price,0) }}@endif
      </div>
      <div class="exam-seller">
        <span>by {{ $exam->seller->name ?? 'Naukaridarpan' }}</span>
        @if($exam->seller->sellerProfile?->is_verified)
          <svg width="12" height="12" fill="var(--teal)" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
        @endif
      </div>
    </div>
    <a href="{{ route('exams.show',$exam->slug) }}" class="btn btn-teal btn-sm">View Details</a>
  </div>
</div>
