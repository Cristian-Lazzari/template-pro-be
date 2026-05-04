<?php

namespace App\Jobs\Marketing;

use App\Models\CustomerPromotion;
use App\Services\Marketing\MarketingEmailDispatchService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class SendMarketingCustomerPromotionEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 120;

    public function __construct(public int $customerPromotionId)
    {
    }

    public function handle(MarketingEmailDispatchService $dispatchService): void
    {
        Log::info('Marketing email job started.', [
            'customer_promotion_id' => $this->customerPromotionId,
        ]);

        $customerPromotion = CustomerPromotion::query()
            ->with([
                'automation.model',
                'campaign.model',
                'customer',
                'promotion',
            ])
            ->find($this->customerPromotionId);

        if (! $customerPromotion) {
            Log::warning('Marketing email job skipped because customer promotion was not found.', [
                'customer_promotion_id' => $this->customerPromotionId,
            ]);

            return;
        }

        if ($customerPromotion->email_sent_at !== null) {
            Log::info('Marketing email job skipped because email was already sent.', [
                'customer_promotion_id' => $this->customerPromotionId,
                'email_sent_at' => optional($customerPromotion->email_sent_at)->toDateTimeString(),
            ]);

            return;
        }

        if ($customerPromotion->campaign && in_array($customerPromotion->campaign->status, ['paused', 'archived'], true)) {
            Log::info('Marketing email job skipped because campaign is not dispatchable.', [
                'customer_promotion_id' => $this->customerPromotionId,
                'campaign_id' => $customerPromotion->campaign_id,
                'campaign_status' => $customerPromotion->campaign->status,
            ]);

            return;
        }

        try {
            $customer = $customerPromotion->customer;

            Log::info('Marketing email job sending check.', [
                'customer_promotion_id' => $this->customerPromotionId,
                'customer_id' => $customer?->getKey(),
                'promotion_id' => $customerPromotion->promotion?->getKey(),
                'campaign_id' => $customerPromotion->campaign_id,
                'has_valid_email' => $customer ? filter_var(trim((string) $customer->email), FILTER_VALIDATE_EMAIL) !== false : false,
                'has_marketing_consent' => $customer?->marketing_consent_at !== null,
                'email_sent_at' => optional($customerPromotion->email_sent_at)->toDateTimeString(),
            ]);

            $result = $dispatchService->sendCustomerPromotion($customerPromotion, false);

            if ($result['sent'] ?? false) {
                Log::info('Marketing email sent successfully.', [
                    'customer_promotion_id' => $this->customerPromotionId,
                    'customer_id' => $customer?->getKey(),
                    'campaign_id' => $customerPromotion->campaign_id,
                ]);
            } elseif ($result['already_sent'] ?? false) {
                Log::info('Marketing email job completed without send because email was already sent.', [
                    'customer_promotion_id' => $this->customerPromotionId,
                    'customer_id' => $customer?->getKey(),
                    'campaign_id' => $customerPromotion->campaign_id,
                ]);
            } else {
                Log::warning('Marketing email was not sent.', [
                    'customer_promotion_id' => $this->customerPromotionId,
                    'customer_id' => $customer?->getKey(),
                    'campaign_id' => $customerPromotion->campaign_id,
                    'reason' => $result['failure_reason'] ?? null,
                ]);
            }
        } catch (Throwable $exception) {
            Log::error('Marketing email job failed with exception.', [
                'customer_promotion_id' => $this->customerPromotionId,
                'message' => $exception->getMessage(),
            ]);

            report($exception);
        }
    }
}
