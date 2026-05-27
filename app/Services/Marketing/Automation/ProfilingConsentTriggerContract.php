<?php

namespace App\Services\Marketing\Automation;

/**
 * Contratto opzionale per i trigger di automazione che richiedono
 * il consenso esplicito alla profilazione/tracking prima di poter
 * selezionare un cliente come destinatario.
 *
 * Implementa questo contratto sui trigger che usano dati personali
 * non derivati dalla relazione contrattuale (es. data di nascita,
 * profili comportamentali incrociati).
 *
 * AutomationAudienceBuilder verifica instanceof questo contratto e
 * applica il filtro tracking_consent_at IS NOT NULL se requiresProfilingConsent()
 * restituisce true.
 *
 * Trigger che devono implementare questo contratto:
 *   - BirthdayBeforeTrigger (usa la data di nascita a fini promozionali)
 *
 * Trigger che NON richiedono questo contratto (base giuridica = esecuzione del contratto):
 *   - no_interaction_since, no_order_since, no_booking_since
 *   - first_order_completed, first_booking_completed
 *   - orders_without_bookings, bookings_without_orders
 *   - customer_reaches_value, valuable_customer_at_risk
 *   - high_average_order_value, customer_anniversary
 */
interface ProfilingConsentTriggerContract
{
    /**
     * Restituisce true se questo trigger richiede che il cliente abbia
     * dato consenso alla profilazione/tracking (tracking_consent_at IS NOT NULL).
     */
    public function requiresProfilingConsent(): bool;
}
