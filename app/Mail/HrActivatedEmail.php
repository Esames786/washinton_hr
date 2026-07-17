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
    public $brand;
    public $supportEmail;

    // #17: brand the activation email by the subcontractor's origin (CrazyRays vs Hello Transport).
    public function __construct($employeeName, $employeeEmail, $brand = 'Hello Transport', $supportEmail = 'info@hellotransport.com')
    {
        $this->employeeName  = $employeeName;
        $this->employeeEmail = $employeeEmail;
        $this->brand         = $brand;
        $this->supportEmail  = $supportEmail;
    }

    public function build()
    {
        return $this
            ->from(config('mail.from.address', 'noreply@hellotransport.com'), $this->brand . ' HR')
            ->subject('Your ' . $this->brand . ' HR Account is Now Active!')
            ->view('emails.hr_activated');
    }
}
