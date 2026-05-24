@php
    $subject = (string) ($rendered['subject'] ?? __('admin.emails.marketing.promotion_for_you'));
    $heading = (string) ($rendered['heading'] ?? $subject);
    $bodyHtml = (string) ($rendered['body_html'] ?? '');
    $ending = $rendered['ending'] ?? null;
    $sender = (string) ($rendered['sender'] ?? config('configurazione.APP_NAME', config('app.name')));
    $trackingOpenUrl  = (string) ($rendered['tracking_open_url'] ?? '');
    $trackingClickUrl = (string) ($rendered['tracking_click_url'] ?? '');
    $unsubscribeUrl   = (string) ($rendered['unsubscribe_url'] ?? '');
    $unsubscribeLabel = (string) ($rendered['unsubscribe_label'] ?? __('admin.emails.marketing.unsubscribe'));
    $hasPromotion     = (bool) ($rendered['has_promotion'] ?? false);
    $ctaLabel         = (string) ($rendered['cta_label'] ?? __('admin.emails.marketing.discover_promotion'));
    $appName          = config('configurazione.APP_NAME', config('app.name'));
    $trackingRedirectUrl = null;

    if ($trackingClickUrl !== '') {
        parse_str((string) parse_url($trackingClickUrl, PHP_URL_QUERY), $trackingQuery);
        $trackingRedirectUrl = isset($trackingQuery['redirect']) && is_string($trackingQuery['redirect'])
            ? $trackingQuery['redirect']
            : null;
    }

    $absoluteUrl = function (?string $url): ?string {
        if (! is_string($url) || $url === '') {
            return null;
        }

        if (preg_match('#^https?://#i', $url)) {
            return $url;
        }

        $baseUrl = rtrim((string) config('app.url'), '/');

        if ($baseUrl === '') {
            $baseUrl = rtrim((string) config('configurazione.APP_URL'), '/');
        }

        if ($baseUrl === '') {
            return url($url);
        }

        return $baseUrl . '/' . ltrim($url, '/');
    };

    $storageImageUrl = function (?string $path) use ($absoluteUrl): ?string {
        if (! is_string($path) || $path === '') {
            return null;
        }

        if (preg_match('#^https?://#i', $path)) {
            return $path;
        }

        $path = ltrim($path, '/');

        if (str_starts_with($path, 'public/storage/') || str_starts_with($path, 'storage/')) {
            return $absoluteUrl($path);
        }

        return $absoluteUrl('public/storage/' . $path);
    };

    $logoUrl = config('configurazione.APP_URL') === 'https://db-demo3.future-plus.it'
        ? config('configurazione.APP_URL') . '/public/favicon.png'
        : rtrim((string) config('configurazione.domain'), '/') . '/img/favicon.png';

    $logoUrl = $absoluteUrl($logoUrl);
    $imageOneUrl = $storageImageUrl($rendered['img_1'] ?? null);
    $imageTwoUrl = $storageImageUrl($rendered['img_2'] ?? null);
    $hiddenUrls = array_values(array_filter([$trackingClickUrl, $trackingRedirectUrl], fn ($url) => is_string($url) && $url !== ''));
    $bodyHtml = preg_replace_callback(
        '/<a\b[^>]*href=(["\'])(.*?)\1[^>]*>.*?<\/a>/is',
        function (array $matches) use ($hiddenUrls): string {
            $href = html_entity_decode((string) ($matches[2] ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8');

            if (in_array($href, $hiddenUrls, true) || str_contains($href, '/api/marketing/click/')) {
                return '';
            }

            return $matches[0];
        },
        $bodyHtml
    );

    foreach ($hiddenUrls as $hiddenUrl) {
        $bodyHtml = str_replace($hiddenUrl, '', $bodyHtml);
        $bodyHtml = str_replace(e($hiddenUrl), '', $bodyHtml);
    }

    $bodyHtml = preg_replace('/<p\b[^>]*>\s*(?:&nbsp;|<br\s*\/?>|\s)*<\/p>/i', '', $bodyHtml);
@endphp

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ $subject }}</title>
    <style>
        span.im {
            color: #04001d !important;
        }
    </style>
</head>
<body style="font-family: Arial, sans-serif; background-color: #e9f0fb; color: #161c3e; margin: 0; padding: 10px 0 0; width: 100%;">
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
        <tr>
            <td align="center">
                <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="900" style="max-width: 900px; width: 100%;">
                    <tr>
                        <td align="center" style="padding: 0 16px;">
                            @if ($logoUrl)
                                <img style="width: 80px; margin: 25px; background-color: #090333; border-radius: 26px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.272); padding: 2px; border: solid 2px #090333;" src="{{ $logoUrl }}" alt="{{ config('configurazione.APP_NAME', config('app.name')) }}">
                            @endif

                            <h1 style="color: #04001d; font-size: 28px; line-height: 1.2; padding: 20px; margin: 0; word-break: break-word;">
                                {{ $heading }}
                            </h1>

                            @if ($imageOneUrl)
                                <center>
                                    <img style="max-width: 450px; border-radius: 10px; width: 60%; margin-top: 2rem; margin-bottom: 2rem;" src="{{ $imageOneUrl }}" alt="">
                                </center>
                            @endif

                            <div style="margin: 30px 25px; font-size: 20px; line-height: 1.55; color: #04001d; text-align: start; word-break: break-word; overflow-wrap: anywhere;">
                                {!! $bodyHtml !!}
                            </div>

                            @if ($hasPromotion && $trackingClickUrl !== '')
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" align="center" style="margin: 26px auto;">
                                    <tr>
                                        <td align="center">
                                            <a href="{{ $trackingClickUrl }}" target="_blank" style="display: inline-block; font-weight: 800; font-size: 18px; color: #e9f0fb; text-decoration: none; padding: 12px 26px; border-radius: 10px; background-color: #04001d;">
                                                {{ $ctaLabel }}
                                            </a>
                                        </td>
                                    </tr>
                                </table>
                            @endif

                            @if ($imageTwoUrl)
                                <center>
                                    <img style="max-width: 450px; border-radius: 10px; width: 70%; margin-top: 2rem; margin-bottom: 2rem;" src="{{ $imageTwoUrl }}" alt="">
                                </center>
                            @endif

                            @if ($ending)
                                <p style="color: #04001d; font-size: 22px; line-height: 1.45; text-align: center; margin: 30px; word-break: break-word;">
                                    {!! nl2br(e(str_replace('\n', ' ', (string) $ending))) !!}
                                </p>
                            @endif

                            <div class="sender" style="color: #04001d; margin: 50px 0;">
                                <p style="font-weight: 900; font-size: 18px; margin: 1rem 2rem 0;">{{ $sender }}</p>
                                <p style="font-style: italic; font-size: 15px; margin: 1rem 2rem 2rem; color: #04001db3;">{{ now()->translatedFormat('l j F Y') }}</p>
                            </div>

                            @if ($trackingOpenUrl !== '')
                                <img src="{{ $trackingOpenUrl }}" width="1" height="1" style="display:none; width:1px; height:1px; opacity:0;" alt="">
                            @endif
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <table role="presentation" width="900" cellspacing="0" cellpadding="0" border="0"
           style="max-width:900px; width:100%; background-color:#0f0b2e; border-radius:0 0 14px 14px; margin: 50px auto 0;">
        <tr>
            <td align="center" style="padding:28px 24px 24px;">
                @if ($unsubscribeUrl !== '')
                    <p style="color:rgba(255,255,255,0.65); font-size:13px; margin:0 0 10px; line-height:1.6;">
                        {{ __('admin.emails.marketing.unsubscribe_question') }}
                        <a href="{{ $unsubscribeUrl }}"
                           style="color:rgba(255,255,255,0.85); text-decoration:underline; font-weight:700;">
                            {{ $unsubscribeLabel }}
                        </a>
                    </p>
                @endif
                <p style="color:rgba(255,255,255,0.4); font-size:11px; margin:0 0 5px; line-height:1.6;">
                    {{ __('admin.end_copy', ['name' => $appName]) }}
                </p>
                <p style="color:rgba(255,255,255,0.35); font-size:11px; margin:0;">
                    Powered by
                    <a href="https://future-plus.it"
                       style="color:rgba(255,255,255,0.6); text-decoration:none; font-weight:700;">
                        Future +
                    </a>
                </p>
            </td>
        </tr>
    </table>
</body>
</html>
