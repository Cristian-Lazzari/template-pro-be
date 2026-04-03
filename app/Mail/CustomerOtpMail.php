<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Lang;

class CustomerOtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $code;
    public int $expiresInMinutes;
    public string $purpose;

    public function __construct(string $code, int $expiresInMinutes, string $purpose)
    {
        $this->code = $code;
        $this->expiresInMinutes = $expiresInMinutes;
        $this->purpose = $purpose;
    }

    public function build()
    {
        return $this->subject(Lang::get('customer.mail.' . $this->purpose . '.subject'))
            ->view('emails.customer-otp');
    }
}
