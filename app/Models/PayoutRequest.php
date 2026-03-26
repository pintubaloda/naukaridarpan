<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class PayoutRequest extends Model {
    protected $fillable = ['seller_id','amount','status','bank_name','account_number','ifsc_code','utr_number','admin_note','processed_at'];
    protected $casts = ['processed_at'=>'datetime'];
    public function seller() { return $this->belongsTo(User::class,'seller_id'); }
}
