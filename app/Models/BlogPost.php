<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class BlogPost extends Model {
    protected $fillable = ['author_id','title','slug','excerpt','content','featured_image','tags','category','meta_title','meta_description','is_ai_generated','status','published_at','view_count'];
    protected $casts = ['tags'=>'array','is_ai_generated'=>'boolean','published_at'=>'datetime'];
    public function author() { return $this->belongsTo(User::class,'author_id'); }
    public function scopePublished($q){ return $q->where('status','published'); }
}
