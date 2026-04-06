<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class ExamAttempt extends Model {
    protected $fillable = ['purchase_id','student_id','exam_paper_id','status','started_at','submitted_at','time_taken_seconds','score','percentage','rank_position','percentile','correct_answers','wrong_answers','unattempted','answers','question_order','performance_breakdown','question_timings','bookmarked_questions','anti_cheat_review','tab_switches','tab_switch_count','security_log','tao_delivery_uri','tao_launch_url','tao_result'];
    protected $casts = ['started_at'=>'datetime','submitted_at'=>'datetime','answers'=>'array','question_order'=>'array','performance_breakdown'=>'array','question_timings'=>'array','bookmarked_questions'=>'array','anti_cheat_review'=>'array','security_log'=>'array','tab_switches'=>'boolean','tao_result'=>'array'];
    public function purchase()  { return $this->belongsTo(Purchase::class); }
    public function student()   { return $this->belongsTo(User::class,'student_id'); }
    public function examPaper() { return $this->belongsTo(ExamPaper::class); }
}
