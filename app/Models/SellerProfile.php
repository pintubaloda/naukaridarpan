<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class SellerProfile extends Model {
    protected $fillable = ['user_id','username','bio','qualification','institution','subjects','city','state','website','youtube_channel','linkedin','rating','total_reviews','total_sales','total_earnings','wallet_balance','pending_balance','is_verified'];
    protected $casts = ['subjects'=>'array','is_verified'=>'boolean'];
    public function user()       { return $this->belongsTo(User::class); }
    public function examPapers() { return $this->hasMany(ExamPaper::class,'seller_id','user_id'); }
}
