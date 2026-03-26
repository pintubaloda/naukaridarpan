<?php

namespace App\Jobs;

use App\Models\ProfessorLead;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\{InteractsWithQueue, SerializesModels};
use Illuminate\Support\Facades\{Mail, Log};

class SendOnboardingMailerJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 2;
    public int $timeout = 120;

    public function __construct(
        private array  $leadIds,
        private string $template = 'invite'
    ) {}

    public function handle(): void
    {
        $leads = ProfessorLead::whereIn('id', $this->leadIds)->get();

        foreach ($leads as $lead) {
            if (empty($lead->email)) continue;

            try {
                $subject = $this->getSubject($this->template, $lead->email_count);
                $body    = $this->getBody($this->template, $lead);

                Mail::raw($body, function ($msg) use ($lead, $subject) {
                    $msg->to($lead->email, $lead->name ?? 'Educator')
                        ->subject($subject)
                        ->from(config('mail.from.address'), 'Naukaridarpan Team');
                });

                $lead->markEmailed();
                Log::info("Onboarding mailer sent to {$lead->email} (template: {$this->template})");

                // Throttle: 1 email per 2 seconds to avoid rate limits
                sleep(2);
            } catch (\Exception $e) {
                Log::error("Mailer failed for lead #{$lead->id}: " . $e->getMessage());
            }
        }
    }

    private function getSubject(string $template, int $emailCount): string
    {
        return match ($template) {
            'followup' => 'Following up — Earn from your exam expertise on Naukaridarpan',
            'case_study' => 'How educators earn ₹50,000+/month on Naukaridarpan',
            default     => 'Invite: Monetise your knowledge on India\'s top exam platform',
        };
    }

    private function getBody(string $template, ProfessorLead $lead): string
    {
        $name = $lead->name ?? 'Professor';
        $site = config('app.url');

        return match ($template) {
            'followup' => "Dear {$name},\n\nJust following up on our earlier message. Naukaridarpan helps educators like you earn passive income by selling exam papers to lakhs of students across India.\n\n• Upload once — earn forever\n• 85% revenue share\n• 48-hour payouts\n• Zero technical skills needed\n\nJoin free today: {$site}/register/seller\n\nBest regards,\nNaukaridarpan Team",
            default    => "Dear {$name},\n\nWe found your profile and believe you'd be a great fit for Naukaridarpan — India's growing competitive exam marketplace.\n\nWhy join?\n• Upload your question papers as mock tests\n• Students from across India will purchase them\n• You keep 85% of every sale\n• Payouts directly to your bank account within 48 hours\n• Our AI automatically converts PDFs into interactive exams\n\nIt's completely free to join: {$site}/register/seller\n\nIf you have any questions, simply reply to this email.\n\nWarm regards,\nNaukaridarpan Team\n{$site}",
        };
    }
}
