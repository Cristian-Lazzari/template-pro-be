<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Symfony\Component\Mime\Email;

class MarketingPromotionMail extends Mailable
{
    use Queueable, SerializesModels;

    public array $rendered;
    public string $subjectLine;
    public ?string $bodyText;

    public function __construct(array $rendered)
    {
        $this->rendered = $rendered;
        $this->subjectLine = (string) ($rendered['subject'] ?? 'Promozione per te');
        $this->bodyText = isset($rendered['body_text']) ? (string) $rendered['body_text'] : null;
    }

    public function build()
    {
        $mail = $this->subject($this->subjectLine)
            ->view('emails.marketing-promotion', [
                'rendered' => $this->rendered,
            ]);

        if ($this->bodyText !== null && $this->bodyText !== '') {
            $mail->withSymfonyMessage(function (Email $message) {
                $message->text($this->bodyText);
            });
        }

        return $mail;
    }
}
