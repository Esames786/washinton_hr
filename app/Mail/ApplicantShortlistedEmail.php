<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Email trigger 2 — sent when an application is shortlisted.
 *
 * Carries the HR portal link and a one-time link to set a password. No password is
 * generated or sent: the applicant chooses their own, and the link expires.
 */
class ApplicantShortlistedEmail extends Mailable
{
    use Queueable, SerializesModels;

    public string $applicantName;
    public string $portalUrl;
    public string $setPasswordUrl;
    public string $brand;
    public string $supportEmail;
    public int $expiresInDays;

    public function __construct(
        string $applicantName,
        string $portalUrl,
        string $setPasswordUrl,
        int $expiresInDays = 7,
        string $brand = 'Hello Transport',
        string $supportEmail = 'info@hellotransport.com'
    ) {
        $this->applicantName  = $applicantName;
        $this->portalUrl      = $portalUrl;
        $this->setPasswordUrl = $setPasswordUrl;
        $this->expiresInDays  = $expiresInDays;
        $this->brand          = $brand;
        $this->supportEmail   = $supportEmail;
    }

    public function build()
    {
        return $this
            ->from(config('mail.from.address', 'noreply@hellotransport.com'), $this->brand . ' HR')
            ->subject('You have been shortlisted — next steps with ' . $this->brand)
            ->view('emails.applicant_shortlisted');
    }
}
