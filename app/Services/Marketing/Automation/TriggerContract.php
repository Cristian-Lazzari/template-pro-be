<?php

namespace App\Services\Marketing\Automation;

use Illuminate\Database\Eloquent\Builder;

interface TriggerContract
{
    /**
     * Apply the trigger condition to an existing Customer query.
     * The query already has marketing consent applied upstream.
     * Must not return a value — mutates $query in place.
     */
    public function applyToQuery(Builder $query, array $params): void;

    /**
     * Laravel validation rules for the trigger's metadata params.
     * Keys are bare param names (e.g. 'days'), NOT prefixed with 'metadata.'.
     * The caller (FormRequest) applies the prefix.
     */
    public function validationRules(): array;

    /**
     * Default metadata values for this trigger.
     * Used by the controller to pre-populate the form.
     */
    public function defaultMetadata(): array;

    /**
     * Human-readable label for admin UI.
     */
    public function label(): string;

    /**
     * Short description for admin UI tooltip / help text.
     */
    public function description(): string;

    /**
     * List of customers table columns this trigger requires.
     * Used by AutomationAudienceBuilder to check availability before querying.
     */
    public function requiredCustomerColumns(): array;

    /**
     * Return a failure reason string if the trigger cannot run, null if OK.
     *
     * @param array $params         Resolved metadata params for this trigger.
     * @param array $availableColumns  Flipped column listing of the customers table.
     */
    public function getFailureReason(array $params, array $availableColumns): ?string;
}
