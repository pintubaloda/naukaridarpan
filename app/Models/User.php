<?php
namespace App\Models;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable {
    use Notifiable;
    protected $fillable = ['name','email','phone','password','role','avatar','is_active'];
    protected $hidden   = ['password','remember_token'];
    protected $casts    = ['email_verified_at'=>'datetime','is_active'=>'boolean'];
    public function sellerProfile()  { return $this->hasOne(SellerProfile::class); }
    public function purchases()      { return $this->hasMany(Purchase::class,'student_id'); }
    public function examAttempts()   { return $this->hasMany(ExamAttempt::class,'student_id'); }
    public function kyc()            { return $this->hasOne(KYCVerification::class); }
    public function payoutRequests() { return $this->hasMany(PayoutRequest::class,'seller_id'); }
    public function examPapers()     { return $this->hasMany(ExamPaper::class,'seller_id'); }
    public function isAdmin()  { return $this->role==='admin'; }
    public function isSeller() { return $this->role==='seller'; }
    public function isStudent(){ return $this->role==='student'; }
}
