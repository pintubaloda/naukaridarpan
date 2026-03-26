<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProfessorLead extends Model
{
    protected $fillable = [
        'name', 'email', 'phone', 'platform', 'institution',
        'subject', 'profile_url', 'subscriber_count',
        'outreach_status', 'email_count', 'last_emailed_at',
    ];

    protected $casts = [
        'last_emailed_at' => 'datetime',
    ];

    public function scopeNew($q)       { return $q->where('outreach_status', 'new'); }
    public function scopeEmailed($q)   { return $q->where('outreach_status', 'emailed'); }
    public function scopeReplied($q)   { return $q->where('outreach_status', 'replied'); }
    public function scopeOnboarded($q) { return $q->where('outreach_status', 'onboarded'); }

    public function markEmailed(): void
    {
        $this->update([
            'outreach_status' => 'emailed',
            'email_count'     => $this->email_count + 1,
            'last_emailed_at' => now(),
        ]);
    }
}
