<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Earn from Your Knowledge — Naukaridarpan</title>
<style>
  body{margin:0;padding:0;background:#F5F0EB;font-family:'Helvetica Neue',Arial,sans-serif;font-size:16px;color:#2D3748}
  .wrapper{max-width:600px;margin:2rem auto;background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,.08)}
  .header{background:linear-gradient(135deg,#E8650A,#C75508);padding:2.5rem 2rem;text-align:center}
  .logo{color:#fff;font-size:1.4rem;font-weight:400;margin-bottom:.5rem}
  .logo span{color:#FFD98E}
  .body{padding:2rem}
  h1{font-size:1.4rem;margin:0 0 1rem;color:#2D3748}
  p{color:#4A5568;line-height:1.7;margin:0 0 1rem}
  .benefits{background:#F0FFF4;border:1px solid #C6F6D5;border-radius:10px;padding:1.25rem;margin:1.5rem 0}
  .benefit{display:flex;align-items:center;gap:.75rem;margin-bottom:.6rem;font-size:.9rem;color:#2D3748}
  .btn{display:inline-block;background:#E8650A;color:#fff;text-decoration:none;padding:.85rem 2.5rem;border-radius:8px;font-weight:700;font-size:1rem;margin:1rem 0}
  .stats{display:grid;grid-template-columns:repeat(3,1fr);gap:1rem;margin:1.5rem 0;text-align:center}
  .stat-n{font-size:1.4rem;font-weight:700;color:#0D5C63}
  .stat-l{font-size:.78rem;color:#718096}
  .footer{background:#F5F0EB;padding:1.25rem 2rem;text-align:center;font-size:.8rem;color:#718096}
</style>
</head>
<body>
<div class="wrapper">
  <div class="header">
    <div class="logo">Naukari<span>darpan</span></div>
    <div style="color:rgba(255,255,255,.85);font-size:1.1rem;margin-top:.5rem">Turn Your Knowledge into Income</div>
  </div>
  <div class="body">
    <h1>Dear {{ $name ?? 'Professor' }},</h1>
    <p>We noticed your expertise in competitive exam education and believe you'd be a great fit for <strong>Naukaridarpan</strong> — India's growing marketplace for mock tests and question papers.</p>

    <div class="stats">
      <div><div class="stat-n">50,000+</div><div class="stat-l">Students enrolled</div></div>
      <div><div class="stat-n">85%</div><div class="stat-l">Revenue share</div></div>
      <div><div class="stat-n">48hrs</div><div class="stat-l">Payout settlement</div></div>
    </div>

    <div class="benefits">
      <div class="benefit">✅ <span>Upload PDF question papers — AI converts them automatically</span></div>
      <div class="benefit">✅ <span>Set your own price — keep 85% of every sale</span></div>
      <div class="benefit">✅ <span>Students from across India buy your papers 24/7</span></div>
      <div class="benefit">✅ <span>Direct bank payout within 48 hours of each sale</span></div>
      <div class="benefit">✅ <span>Free public profile page showcasing your credentials</span></div>
      <div class="benefit">✅ <span>Zero technical knowledge required — we handle everything</span></div>
    </div>

    <p>Joining is <strong>completely free</strong>. Register in 2 minutes and upload your first paper today.</p>

    <a href="{{ url('/register/seller') }}" class="btn">Join as an Educator →</a>

    <p style="font-size:.85rem;color:#718096;margin-top:1.5rem">Have questions? Reply to this email and our team will get back to you within 24 hours.</p>
  </div>
  <div class="footer">
    &copy; {{ date('Y') }} Naukaridarpan Technologies Pvt. Ltd. · <a href="{{ url('/') }}" style="color:#E8650A">naukaridarpan.com</a><br>
    <a href="{{ url('/unsubscribe') }}" style="color:#718096">Unsubscribe</a> from educator outreach emails
  </div>
</div>
</body>
</html>
