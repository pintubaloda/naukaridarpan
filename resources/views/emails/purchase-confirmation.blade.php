<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Purchase Confirmed — Naukaridarpan</title>
<style>
  body{margin:0;padding:0;background:#F5F0EB;font-family:'Helvetica Neue',Arial,sans-serif;font-size:16px;color:#2D3748}
  .wrapper{max-width:600px;margin:2rem auto;background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,.08)}
  .header{background:linear-gradient(135deg,#0D5C63,#0A4950);padding:2.5rem 2rem;text-align:center}
  .logo{color:#fff;font-size:1.4rem;font-weight:400;letter-spacing:.02em;margin-bottom:.5rem}
  .logo span{color:#E8650A}
  .body{padding:2rem}
  h1{font-size:1.5rem;margin:0 0 1rem;color:#2D3748}
  p{color:#4A5568;line-height:1.65;margin:0 0 1rem}
  .exam-box{background:#E8F4F5;border:1px solid #B2DFDB;border-radius:10px;padding:1.25rem;margin:1.5rem 0}
  .exam-title{font-size:1.1rem;font-weight:600;color:#0D5C63;margin-bottom:.5rem}
  .meta{font-size:.85rem;color:#718096}
  .btn{display:inline-block;background:#E8650A;color:#fff;text-decoration:none;padding:.75rem 2rem;border-radius:8px;font-weight:600;font-size:.95rem;margin:1rem 0}
  .divider{height:1px;background:#E8E0D5;margin:1.5rem 0}
  .footer{background:#F5F0EB;padding:1.25rem 2rem;text-align:center;font-size:.8rem;color:#718096}
</style>
</head>
<body>
<div class="wrapper">
  <div class="header">
    <div class="logo">Naukari<span>darpan</span></div>
    <div style="color:rgba(255,255,255,.7);font-size:.9rem">India's Exam Marketplace</div>
  </div>
  <div class="body">
    <h1>🎉 Purchase Confirmed!</h1>
    <p>Hello <strong>{{ $purchase->student->name }}</strong>,</p>
    <p>Your payment was successful. Your exam is now unlocked and ready to attempt.</p>

    <div class="exam-box">
      <div class="exam-title">{{ $purchase->examPaper->title }}</div>
      <div class="meta">
        📝 {{ $purchase->examPaper->total_questions }} Questions &nbsp;·&nbsp;
        ⏱ {{ $purchase->examPaper->duration_minutes }} minutes &nbsp;·&nbsp;
        🔄 {{ $purchase->retakes_allowed }} retake(s)
      </div>
    </div>

    <table style="width:100%;font-size:.88rem;color:#718096;border-collapse:collapse;margin:1rem 0">
      <tr><td style="padding:.3rem 0">Order ID</td><td style="text-align:right;font-weight:600;color:#2D3748">{{ $purchase->order_id }}</td></tr>
      <tr><td style="padding:.3rem 0">Amount Paid</td><td style="text-align:right;font-weight:600;color:#0D5C63">₹{{ number_format($purchase->amount_paid, 0) }}</td></tr>
      <tr><td style="padding:.3rem 0">Payment Date</td><td style="text-align:right">{{ $purchase->created_at->format('d M Y, g:i A') }}</td></tr>
    </table>

    <a href="{{ url('/student/exam/'.$purchase->id.'/start') }}" class="btn">Start Exam Now →</a>

    <div class="divider"></div>
    <p style="font-size:.85rem">If you face any issues, reply to this email or contact us at <a href="mailto:support@naukaridarpan.com" style="color:#E8650A">support@naukaridarpan.com</a></p>
    <p style="font-size:.85rem;color:#718096">Refund Policy: Full refund available within 7 days if the exam cannot be accessed.</p>
  </div>
  <div class="footer">
    &copy; {{ date('Y') }} Naukaridarpan Technologies Pvt. Ltd. · New Delhi, India<br>
    <a href="{{ url('/') }}" style="color:#E8650A">naukaridarpan.com</a>
  </div>
</div>
</body>
</html>
