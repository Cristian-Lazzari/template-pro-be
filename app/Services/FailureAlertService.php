<?php

namespace App\Services;

use App\Mail\FailureAlertMail;
use Carbon\Carbon;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use JsonSerializable;
use Throwable;

class FailureAlertService
{
    private const RECIPIENT = 'info@future-plus.it';

    public function notify(string $flow, Request $request, array $context = [], ?Throwable $exception = null): void
    {
        $allowDuplicate = (bool) ($context['allow_duplicate'] ?? false);

        if (!$allowDuplicate && $request->attributes->get('failure_alert_sent')) {
            return;
        }

        $payload = $this->extractPayload($request, $context);
        $details = $this->normalizeValue($context['details'] ?? []);
        $resource = $this->normalizeValue($context['resource'] ?? []);

        $alert = [
            'flow' => $flow,
            'flow_label' => $this->flowLabel($flow),
            'reported_at' => Carbon::now()->format('Y-m-d H:i:s'),
            'restaurant' => [
                'name' => config('configurazione.APP_NAME') ?: config('app.name'),
                'database' => config('configurazione.db'),
                'app_url' => config('configurazione.APP_URL'),
                'domain' => config('configurazione.domain'),
                'mail_from' => config('mail.from.address'),
            ],
            'request' => [
                'method' => $request->method(),
                'url' => $request->fullUrl(),
                'path' => $request->path(),
                'route_name' => $request->route()?->getName(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ],
            'customer' => $this->buildCustomerContext($payload, $context),
            'slot' => $context['slot'] ?? Arr::get($payload, 'date_slot'),
            'resource' => $resource,
            'error' => [
                'type' => $context['error_type'] ?? ($exception ? class_basename($exception) : 'unknown'),
                'message' => $context['message'] ?? ($exception ? $exception->getMessage() : 'Unknown error'),
                'response_status' => $context['status'] ?? null,
                'details' => $details,
                'exception_class' => $exception ? get_class($exception) : null,
                'file' => $exception?->getFile(),
                'line' => $exception?->getLine(),
            ],
            'payload_json' => $this->toPrettyJson($payload),
            'details_json' => $this->toPrettyJson($details),
            'trace' => $this->exceptionTrace($exception),
        ];

        try {
            Mail::to(self::RECIPIENT)->send(new FailureAlertMail($alert));

            if (!$allowDuplicate) {
                $request->attributes->set('failure_alert_sent', true);
            }
        } catch (Throwable $mailException) {
            Log::error('(FailureAlertService) Invio mail di allerta fallito', [
                'flow' => $flow,
                'original_error' => $alert['error']['message'],
                'mail_error' => $mailException->getMessage(),
            ]);
        }
    }

    private function flowLabel(string $flow): string
    {
        return match ($flow) {
            'order' => 'Ordine',
            'reservation' => 'Prenotazione',
            'order_payment' => 'Ordine - pagamento',
            default => ucfirst(str_replace('_', ' ', $flow)),
        };
    }

    private function extractPayload(Request $request, array $context): array
    {
        if (array_key_exists('payload', $context)) {
            $payload = $this->normalizeValue($context['payload']);

            return is_array($payload) ? $payload : ['payload' => $payload];
        }

        $payload = $this->normalizeValue($request->all());

        if (is_array($payload) && $payload !== []) {
            return $payload;
        }

        $rawBody = $request->getContent();

        if ($rawBody === '') {
            return [];
        }

        $decoded = json_decode($rawBody, true);

        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return $decoded;
        }

        return ['raw_body' => $rawBody];
    }

    private function buildCustomerContext(array $payload, array $context): array
    {
        $customer = $this->normalizeValue($context['customer'] ?? []);
        $customer = is_array($customer) ? $customer : [];

        $data = array_merge([
            'name' => Arr::get($payload, 'name'),
            'surname' => Arr::get($payload, 'surname'),
            'email' => Arr::get($payload, 'email'),
            'phone' => Arr::get($payload, 'phone'),
            'n_adult' => Arr::get($payload, 'n_adult'),
            'n_child' => Arr::get($payload, 'n_child'),
            'comune' => Arr::get($payload, 'comune'),
            'address' => Arr::get($payload, 'via', Arr::get($payload, 'address')),
            'address_n' => Arr::get($payload, 'cv', Arr::get($payload, 'address_n')),
        ], $customer);

        return array_filter($data, static fn ($value) => $value !== null && $value !== '' && $value !== []);
    }

    private function exceptionTrace(?Throwable $exception): ?string
    {
        if (!$exception) {
            return null;
        }

        return $exception->getTraceAsString();
    }

    private function toPrettyJson(mixed $value): string
    {
        $json = json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PARTIAL_OUTPUT_ON_ERROR);

        if ($json === false) {
            return var_export($value, true);
        }

        return $json;
    }

    private function normalizeValue(mixed $value): mixed
    {
        if ($value === null || is_scalar($value)) {
            return $value;
        }

        if (is_array($value)) {
            $normalized = [];

            foreach ($value as $key => $item) {
                $normalized[$key] = $this->normalizeValue($item);
            }

            return $normalized;
        }

        if ($value instanceof Arrayable) {
            return $this->normalizeValue($value->toArray());
        }

        if ($value instanceof JsonSerializable) {
            return $this->normalizeValue($value->jsonSerialize());
        }

        if ($value instanceof \Stringable) {
            return (string) $value;
        }

        if (is_object($value)) {
            $json = json_encode($value, JSON_PARTIAL_OUTPUT_ON_ERROR);

            if ($json !== false) {
                $decoded = json_decode($json, true);

                if (json_last_error() === JSON_ERROR_NONE) {
                    return $this->normalizeValue($decoded);
                }
            }

            return ['object_class' => get_class($value)];
        }

        return (string) $value;
    }
}
