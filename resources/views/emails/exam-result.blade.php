<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Your Result is Ready — Naukaridarpan</title>
<style>
  body{margin:0;padding:0;background:#F5F0EB;font-family:'Helvetica Neue',Arial,sans-serif;font-size:16px;color:#2D3748}
  .wrapper{max-width:600px;margin:2rem auto;background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,.08)}
  .header{background:linear-gradient(135deg,#0D5C63,#0A4950);padding:2rem;text-align:center}
  .logo{color:#fff;font-size:1.4rem;margin-bottom:.4rem}
  .logo span{color:#E8650A}
  .score-ring{width:120px;height:120px;border-radius:50%;border:8px solid {{ $attempt->percentage >= 60 ? '#276749' : ($attempt->percentage >= 40 ? '#D4A017' : '#C53030') }};display:flex;flex-direction:column;align-items:center;justify-content:center;margin:1.5rem auto}
  .body{padding:2rem}
  h1{font-size:1.4rem;margin:0 0 1rem}
  .stats-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:.75rem;margin:1.5rem 0;text-align:center}
  .stat-n{font-size:1.3rem;font-weight:700}
  .stat-l{font-size:.76rem;color:#718096}
  .btn{display:inline-block;background:#E8650A;color:#fff;text-decoration:none;padding:.75rem 2rem;border-radius:8px;font-weight:600;margin:1rem 0}
  .footer{background:#F5F0EB;padding:1.25rem 2rem;text-align:center;font-size:.8rem;color:#718096}
</style>
</head>
<body>
<div class="wrapper">
  <div class="header">
    <div class="logo">Naukari<span>darpan</span></div>
    <div style="color:rgba(255,255,255,.7);font-size:.9rem">Your result is ready</div>
    <div style="width:110px;height:110px;border-radius:50%;border:8px solid {{ $attempt->percentage >= 60 ? '#4ADE80' : ($attempt->percentage >= 40 ? '#FCD34D' : '#FC8181') }};display:flex;flex-direction:column;align-items:center;justify-content:center;margin:1.5rem auto">
      <span style="font-size:1.8rem;font-weight:700;color:#fff">{{ round($attempt->percentage) }}%</span>
      <span style="font-size:.75rem;color:rgba(255,255,255,.7)">{{ $attempt->score }}/{{ $attempt->examPaper->max_marks }}</span>
    </div>
  </div>
  <div class="body">
    <h1>Hi {{ $attempt->student->name }}! 👋</h1>
    <p>Here's your result for <strong>{{ $attempt->examPaper->title }}</strong></p>

    <div class="stats-grid">
      <div><div class="stat-n" style="color:#276749">{{ $attempt->correct_answers }}</div><div class="stat-l">Correct</div></div>
      <div><div class="stat-n" style="color:#C53030">{{ $attempt->wrong_answers }}</div><div class="stat-l">Wrong</div></div>
      <div><div class="stat-n" style="color:#718096">{{ $attempt->unattempted }}</div><div class="stat-l">Skipped</div></div>
      <div><div class="stat-n" style="color:#0D5C63">{{ gmdate('i:s', $attempt->time_taken_seconds ?? 0) }}</div><div class="stat-l">Time taken</div></div>
    </div>

    @if($attempt->percentage >= 60)
    <p style="background:#F0FFF4;border:1px solid #C6F6D5;border-radius:8px;padding:1rem;color:#276749">🎉 Excellent! You scored above 60%. Keep this momentum going!</p>
    @elseif($attempt->percentage >= 40)
    <p style="background:#FBF4DC;border:1px solid #F6D858;border-radius:8px;padding:1rem;color:#7A5C10">📈 Good effort! Review your weak areas and attempt again to improve.</p>
    @else
    <p style="background:#FFF5F5;border:1px solid #FED7D7;border-radius:8px;padding:1rem;color:#C53030">💪 Keep practising! Review all answers carefully and attempt again.</p>
    @endif

    <a href="{{ url('/student/exam-attempt/'.$attempt->id.'/result') }}" class="btn">View Detailed Analysis →</a>
  </div>
  <div class="footer">
    &copy; {{ date('Y') }} Naukaridarpan · <a href="{{ url('/') }}" style="color:#E8650A">naukaridarpan.com</a>
  </div>
</div>
</body>
</html>
