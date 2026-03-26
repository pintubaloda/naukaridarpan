<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class KYCVerification extends Model {
    protected $table = 'kyc_verifications';
    protected $fillable = ['user_id','pan_number','pan_document','aadhaar_number','aadhaar_document','bank_name','account_number','ifsc_code','bank_proof_document','status','rejection_reason','reviewed_by','reviewed_at'];
    protected $casts = ['reviewed_at'=>'datetime'];
    public function user()     { return $this->belongsTo(User::class); }
    public function reviewer() { return $this->belongsTo(User::class,'reviewed_by'); }
    public function isApproved(){ return $this->status==='approved'; }
}
