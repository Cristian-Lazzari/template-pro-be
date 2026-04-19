<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class FailureAlertMail extends Mailable
{
    use Queueable, SerializesModels;

    public array $alert;

    public function __construct(array $alert)
    {
        $this->alert = $alert;
    }

    public function build()
    {
        $restaurantName = $this->alert['restaurant']['name'] ?? config('configurazione.APP_NAME');
        $flowLabel = $this->alert['flow_label'] ?? 'Flusso';

        return $this->subject('[ALERT] ' . $flowLabel . ' fallito - ' . $restaurantName)
            ->view('emails.failure-alert');
    }
}
