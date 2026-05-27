<?php

namespace App\Services\Marketing\Automation;

use App\Services\Marketing\Automation\Triggers\BookingsWithoutOrdersTrigger;
use App\Services\Marketing\Automation\Triggers\BirthdayBeforeTrigger;
use App\Services\Marketing\Automation\Triggers\CustomerAnniversaryTrigger;
use App\Services\Marketing\Automation\Triggers\CustomerReachesValueTrigger;
use App\Services\Marketing\Automation\Triggers\FirstBookingCompletedTrigger;
use App\Services\Marketing\Automation\Triggers\FirstOrderCompletedTrigger;
use App\Services\Marketing\Automation\Triggers\HighAverageOrderValueTrigger;
use App\Services\Marketing\Automation\Triggers\NoBookingSinceTrigger;
use App\Services\Marketing\Automation\Triggers\NoInteractionSinceTrigger;
use App\Services\Marketing\Automation\Triggers\NoOrderSinceTrigger;
use App\Services\Marketing\Automation\Triggers\OrdersWithoutBookingsTrigger;
use App\Services\Marketing\Automation\Triggers\ValuableCustomerAtRiskTrigger;
use Illuminate\Support\Facades\Schema;

class AutomationTriggerEvaluator
{
    /** @var array<string, TriggerContract> */
    private array $registry;

    private ?array $customerColumns = null;

    public function __construct()
    {
        $this->registry = [
            'no_interaction_since'      => new NoInteractionSinceTrigger(),
            'no_order_since'            => new NoOrderSinceTrigger(),
            'no_booking_since'          => new NoBookingSinceTrigger(),
            'birthday_before'           => new BirthdayBeforeTrigger(),
            'first_order_completed'     => new FirstOrderCompletedTrigger(),
            'first_booking_completed'   => new FirstBookingCompletedTrigger(),
            'orders_without_bookings'   => new OrdersWithoutBookingsTrigger(),
            'bookings_without_orders'   => new BookingsWithoutOrdersTrigger(),
            'customer_reaches_value'    => new CustomerReachesValueTrigger(),
            'valuable_customer_at_risk' => new ValuableCustomerAtRiskTrigger(),
            'customer_anniversary'      => new CustomerAnniversaryTrigger(),
            'high_average_order_value'  => new HighAverageOrderValueTrigger(),
        ];
    }

    public function supports(string $trigger): bool
    {
        return array_key_exists($trigger, $this->registry);
    }

    public function get(string $trigger): ?TriggerContract
    {
        return $this->registry[$trigger] ?? null;
    }

    /** @return string[] */
    public function supportedKeys(): array
    {
        return array_keys($this->registry);
    }

    /**
     * Full definitions for all triggers — passed to views and API responses.
     * Each entry gives the frontend everything it needs to render a form.
     *
     * @return array<string, array{
     *   key: string,
     *   label: string,
     *   description: string,
     *   default_metadata: array,
     *   validation_rules: array,
     *   required_columns: string[]
     * }>
     */
    public function definitions(): array
    {
        $definitions = [];

        foreach ($this->registry as $key => $trigger) {
            $definitions[$key] = [
                'key'              => $key,
                'label'            => $trigger->label(),
                'description'      => $trigger->description(),
                'default_metadata' => $trigger->defaultMetadata(),
                'validation_rules' => $trigger->validationRules(),
                'required_columns' => $trigger->requiredCustomerColumns(),
            ];
        }

        return $definitions;
    }

    /**
     * Return null if the trigger can run, or a human-readable reason why it cannot.
     */
    public function getFailureReason(string $trigger, array $params): ?string
    {
        $triggerObj = $this->get($trigger);

        if ($triggerObj === null) {
            return "Trigger '{$trigger}' non supportato.";
        }

        return $triggerObj->getFailureReason($params, $this->customerColumns());
    }

    private function customerColumns(): array
    {
        if ($this->customerColumns !== null) {
            return $this->customerColumns;
        }

        if (! Schema::hasTable('customers')) {
            return $this->customerColumns = [];
        }

        return $this->customerColumns = array_flip(Schema::getColumnListing('customers'));
    }
}
