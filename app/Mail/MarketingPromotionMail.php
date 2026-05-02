<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Symfony\Component\Mime\Email;

class MarketingPromotionMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $subjectLine;
    public string $bodyHtml;
    public ?string $bodyText;
    public ?string $trackingOpenUrl;

    public function __construct(array $rendered)
    {
        $this->subjectLine = (string) ($rendered['subject'] ?? 'Promozione per te');
        $this->bodyHtml = (string) ($rendered['body_html'] ?? '');
        $this->bodyText = isset($rendered['body_text']) ? (string) $rendered['body_text'] : null;
        $this->trackingOpenUrl = isset($rendered['tracking_open_url'])
            ? (string) $rendered['tracking_open_url']
            : null;
    }

    public function build()
    {
        $mail = $this->subject($this->subjectLine)
            ->html($this->bodyHtmlWithTrackingPixel());

        if ($this->bodyText !== null && $this->bodyText !== '') {
            $mail->withSymfonyMessage(function (Email $message) {
                $message->text($this->bodyText);
            });
        }

        return $mail;
    }

    private function bodyHtmlWithTrackingPixel(): string
    {
        if ($this->trackingOpenUrl === null || $this->trackingOpenUrl === '') {
            return $this->bodyHtml;
        }

        return $this->bodyHtml
            . "\n"
            . '<img src="' . e($this->trackingOpenUrl) . '" width="1" height="1" style="display:none;" alt="">';
    }
}
