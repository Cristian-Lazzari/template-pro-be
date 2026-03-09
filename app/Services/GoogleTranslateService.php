<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class GoogleTranslateService
{
    protected $apiKey;

    public function __construct()
    {
        $this->apiKey = config('configurazione.GOOGLE_TRASLATE_KEY');

    }

    public function translate($text, $target)
    {
        if (!$text) {
            return null;
        }

        $response = Http::get(
            'https://translation.googleapis.com/language/translate/v2',
            [
                'q' => $text,
                'target' => $target,
                'format' => 'text',
                'key' => $this->apiKey
            ]
        );

        if ($response->failed()) {
            logger()->error('Google Translate error', $response->json());
            return null;
        }

        return $response['data']['translations'][0]['translatedText'] ?? null;
    }
}