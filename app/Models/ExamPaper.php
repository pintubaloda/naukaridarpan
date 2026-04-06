<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class ExamPaper extends Model {
    protected $fillable = ['seller_id','category_id','title','subject','exam_type','slug','description','language','source','source_url','original_file','answer_key_pdf_url','answer_key_applied_at','answer_key_parse_log','parse_status','parse_log','questions_data','tao_item_id','tao_test_id','tao_delivery_id','tao_sync_status','tao_synced_at','tao_last_error','total_questions','duration_minutes','max_marks','negative_marking','max_retakes','difficulty','question_types','section_time_rules','section_negative_rules','exam_sections','qti_metadata','interoperability_profile','seller_price','platform_markup','student_price','is_free','thumbnail','tags','status','rejection_reason','total_purchases','total_attempts','avg_score'];
    protected $casts = ['question_types'=>'array','section_time_rules'=>'array','section_negative_rules'=>'array','exam_sections'=>'array','qti_metadata'=>'array','tags'=>'array','is_free'=>'boolean','tao_synced_at'=>'datetime','answer_key_applied_at'=>'datetime'];
    public function seller()   { return $this->belongsTo(User::class,'seller_id'); }
    public function category() { return $this->belongsTo(Category::class); }
    public function purchases(){ return $this->hasMany(Purchase::class); }
    public function attempts() { return $this->hasMany(ExamAttempt::class); }
    public function taoSyncLogs() { return $this->hasMany(ExamPaperTaoSyncLog::class)->latest(); }
    public function scopeApproved($q) { return $q->where('status','approved'); }
    public function scopeUniqueLatest($q)
    {
        $sub = self::selectRaw('MAX(id) as id')
            ->where('status', 'approved')
            ->groupBy('title', 'subject', 'category_id', 'seller_id');
        return $q->whereIn('id', $sub);
    }

    public function isReadyForTaoSync(): bool
    {
        return $this->parse_status === 'done' && !empty($this->questions_data);
    }
}
