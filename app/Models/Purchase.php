<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Purchase extends Model {
    protected $fillable = ['student_id','exam_paper_id','order_id','razorpay_payment_id','amount_paid','platform_commission','seller_credit','payment_status','retakes_used','retakes_allowed','settlement_at','is_settled'];
    protected $casts = ['settlement_at'=>'datetime','is_settled'=>'boolean'];
    public function student()   { return $this->belongsTo(User::class,'student_id'); }
    public function examPaper() { return $this->belongsTo(ExamPaper::class); }
    public function attempts()  { return $this->hasMany(ExamAttempt::class); }
    public function canAttempt(){ return $this->payment_status==='paid' && $this->retakes_used < $this->retakes_allowed; }
}
