<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/** Email trigger 3 — sent when an application is rejected, with the reason if one was given. */
class ApplicantRejectedEmail extends Mailable
{
    use Queueable, SerializesModels;

    public string $applicantName;
    public ?string $reason;
    public string $brand;
    public string $supportEmail;

    public function __construct(
        string $applicantName,
        ?string $reason = null,
        string $brand = 'Hello Transport',
        string $supportEmail = 'info@hellotransport.com'
    ) {
        $this->applicantName = $applicantName;
        $this->reason        = $reason;
        $this->brand         = $brand;
        $this->supportEmail  = $supportEmail;
    }

    public function build()
    {
        return $this
            ->from(config('mail.from.address', 'noreply@hellotransport.com'), $this->brand . ' HR')
            ->subject('Your application to ' . $this->brand)
            ->view('emails.applicant_rejected');
    }
}
