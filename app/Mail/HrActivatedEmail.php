<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class HrActivatedEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $employeeName;
    public $employeeEmail;

    public function __construct($employeeName, $employeeEmail)
    {
        $this->employeeName  = $employeeName;
        $this->employeeEmail = $employeeEmail;
    }

    public function build()
    {
        return $this
            ->from(config('mail.from.address', 'noreply@hellotransport.com'), 'Hello Transport HR')
            ->subject('Your Hello Transport HR Account is Now Active!')
            ->view('emails.hr_activated');
    }
}
