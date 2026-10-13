<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Email triggers 4, 5 and 6 — the agent is told each time their stage changes.
 *
 * One mailable rather than three, because every one of these is the same shape: "you have
 * moved to stage X, here is what it means and what to do". Splitting them would mean three
 * near-identical templates to keep in step.
 */
class AgentStatusUpdateEmail extends Mailable
{
    use Queueable, SerializesModels;

    public string $agentName;
    public string $subjectLine;
    public string $heading;
    public string $body;
    public ?string $actionUrl;
    public ?string $actionLabel;
    public ?string $note;
    public string $brand;
    public string $supportEmail;

    public function __construct(
        string $agentName,
        string $subjectLine,
        string $heading,
        string $body,
        ?string $actionUrl = null,
        ?string $actionLabel = null,
        ?string $note = null,
        string $brand = 'Hello Transport',
        string $supportEmail = 'info@hellotransport.com'
    ) {
        $this->agentName    = $agentName;
        $this->subjectLine  = $subjectLine;
        $this->heading      = $heading;
        $this->body         = $body;
        $this->actionUrl    = $actionUrl;
        $this->actionLabel  = $actionLabel;
        $this->note         = $note;
        $this->brand        = $brand;
        $this->supportEmail = $supportEmail;
    }

    public function build()
    {
        return $this
            ->from(config('mail.from.address', 'noreply@hellotransport.com'), $this->brand . ' HR')
            ->subject($this->subjectLine)
            ->view('emails.agent_status_update');
    }
}
