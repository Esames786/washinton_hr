<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class HrActivatedEmail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $employeeName,
        public string $employeeEmail
    ) {}

    public function build(): static
    {
        return $this
            ->subject('Your Hello Transport HR Account is Now Active!')
            ->view('emails.hr_activated');
    }
}
