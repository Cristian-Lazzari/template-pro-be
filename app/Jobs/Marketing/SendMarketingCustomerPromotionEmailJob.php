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
        $customerPromotion = CustomerPromotion::query()
            ->with([
                'automation.model',
                'campaign.model',
                'customer',
                'promotion',
            ])
            ->find($this->customerPromotionId);

        if (! $customerPromotion || $customerPromotion->email_sent_at !== null) {
            return;
        }

        try {
            $result = $dispatchService->sendCustomerPromotion($customerPromotion, false);

            if (! ($result['sent'] ?? false) && ! ($result['already_sent'] ?? false)) {
                Log::warning('Marketing email was not sent.', [
                    'customer_promotion_id' => $this->customerPromotionId,
                    'reason' => $result['failure_reason'] ?? null,
                ]);
            }
        } catch (Throwable $exception) {
            report($exception);
        }
    }
}
