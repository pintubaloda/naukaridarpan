<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class ExamPaper extends Model {
    protected $fillable = ['seller_id','category_id','title','subject','slug','description','language','source','original_file','parse_status','parse_log','tao_item_id','tao_test_id','total_questions','duration_minutes','max_marks','negative_marking','max_retakes','difficulty','question_types','seller_price','platform_markup','student_price','is_free','thumbnail','tags','status','rejection_reason','total_purchases','total_attempts','avg_score'];
    protected $casts = ['question_types'=>'array','tags'=>'array','is_free'=>'boolean'];
    public function seller()   { return $this->belongsTo(User::class,'seller_id'); }
    public function category() { return $this->belongsTo(Category::class); }
    public function purchases(){ return $this->hasMany(Purchase::class); }
    public function attempts() { return $this->hasMany(ExamAttempt::class); }
    public function scopeApproved($q) { return $q->where('status','approved'); }
}
