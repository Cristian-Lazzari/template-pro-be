<style>
    .marketing-detail-page {
        width: 100%;
        display: grid;
        gap: 18px;
    }

    .marketing-detail-page * {
        min-width: 0;
    }

    .marketing-detail-page .order-detail__header {
        grid-template-columns: minmax(0, 1fr);
    }

    .marketing-detail-page .order-detail__contacts {
        align-items: flex-start;
    }

    .marketing-detail-page .order-detail__contact {
        max-width: 100%;
    }

    .marketing-detail-page .order-detail__contact span,
    .marketing-detail-page .order-detail__customer,
    .marketing-detail-page .order-detail__time,
    .marketing-detail-page .order-detail__date,
    .marketing-detail-page .order-detail__code {
        white-space: normal;
        overflow: visible;
        text-overflow: initial;
        overflow-wrap: anywhere;
        word-break: break-word;
    }

    .marketing-detail-page .order-detail__summary {
        grid-template-columns: minmax(0, 1fr) minmax(180px, max-content);
    }

    .marketing-detail-page .order-detail__customer {
        display: grid;
        gap: 6px;
        justify-items: end;
        line-height: 1.12;
    }

    .marketing-detail-page .order-detail__customer small {
        color: rgba(216, 221, 232, 0.72);
        font-size: var(--fs-200);
        font-weight: 600;
    }

    .marketing-detail__grid,
    .marketing-detail__metrics,
    .marketing-detail__linked-grid,
    .marketing-detail__compact-grid {
        display: grid;
        gap: 12px;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    }

    .marketing-detail__linked-grid {
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    }

    .marketing-detail__fact,
    .marketing-detail__metric,
    .marketing-detail__linked-card,
    .marketing-detail__empty,
    .marketing-detail__assignment-card {
        display: grid;
        gap: 8px;
        padding: 14px;
        border-radius: 16px;
        border: 1px solid rgba(216, 221, 232, 0.12);
        background: rgba(216, 221, 232, 0.05);
        color: var(--c3);
        overflow: hidden;
    }

    .marketing-detail__fact > span,
    .marketing-detail__metric > span,
    .marketing-detail__linked-card > span,
    .marketing-detail__assignment-card > span {
        color: rgba(216, 221, 232, 0.72);
        font-size: var(--fs-200);
        font-weight: 800;
        letter-spacing: .06em;
        text-transform: uppercase;
    }

    .marketing-detail__fact strong,
    .marketing-detail__metric strong,
    .marketing-detail__linked-card strong,
    .marketing-detail__empty strong,
    .marketing-detail__assignment-card strong {
        color: var(--c3);
        font-size: var(--fs-300);
        line-height: 1.2;
        overflow-wrap: anywhere;
        word-break: break-word;
    }

    .marketing-detail__metric strong {
        font-size: var(--fs-500);
        font-weight: 900;
    }

    .marketing-detail__fact small,
    .marketing-detail__linked-card small,
    .marketing-detail__assignment-card small {
        color: rgba(216, 221, 232, 0.76);
        line-height: 1.45;
        overflow-wrap: anywhere;
        word-break: break-word;
    }

    .marketing-detail__actions {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));
        gap: 10px;
    }

    .marketing-detail__actions form {
        margin: 0;
        min-width: 0;
        display: flex;
    }

    .marketing-detail__actions .order-detail__contact {
        width: 100%;
        height: 100%;
        justify-content: center;
    }

    .marketing-detail__contact--danger {
        background: rgba(206, 59, 59, 0.1);
        border-color: rgba(255, 141, 141, 0.22);
    }

    .marketing-detail__contact--muted {
        background: rgba(9, 3, 51, 0.36);
    }

    .marketing-detail__linked-card .order-detail__contact {
        width: fit-content;
        max-width: 100%;
        margin-top: 4px;
    }

    .marketing-detail__compact-grid + .order-detail__section-head {
        margin-top: 16px;
    }

    .marketing-detail__progress {
        display: grid;
        gap: 10px;
    }

    .marketing-detail__progress-track {
        width: 100%;
        min-height: 12px;
        overflow: hidden;
        border-radius: 999px;
        border: 1px solid rgba(216, 221, 232, 0.12);
        background: rgba(9, 3, 51, 0.42);
    }

    .marketing-detail__progress-bar {
        position: relative;
        height: 100%;
        min-height: 12px;
        border-radius: inherit;
        background: linear-gradient(90deg, rgba(74, 222, 128, 0.92), rgba(45, 212, 191, 0.88));
        transition: width .25s ease;
        overflow: hidden;
    }

    .marketing-detail__progress-bar.is-running::after {
        content: "";
        position: absolute;
        inset: 0;
        background: linear-gradient(110deg, transparent 0%, rgba(255, 255, 255, .32) 45%, transparent 72%);
        transform: translateX(-100%);
        animation: marketingProgressSweep 1.35s linear infinite;
    }

    .marketing-detail__progress-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        justify-content: space-between;
        color: rgba(216, 221, 232, 0.78);
        font-size: var(--fs-200);
        font-weight: 700;
    }

    .marketing-detail__send-panel {
        gap: 14px;
    }

    .marketing-detail__progress-live {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        width: fit-content;
        max-width: 100%;
        padding: 8px 11px;
        border-radius: 999px;
        background: rgba(9, 3, 51, 0.44);
        border: 1px solid rgba(216, 221, 232, 0.12);
        color: rgba(216, 221, 232, 0.84);
        font-size: var(--fs-200);
        font-weight: 800;
        overflow-wrap: anywhere;
    }

    .campaign-promotion-list {
        display: grid;
        gap: 14px;
    }

    .campaign-promotion-card {
        display: grid;
        grid-template-columns: minmax(0, 1.1fr) minmax(240px, .9fr);
        gap: 14px;
        align-items: start;
        padding: 16px;
        border-radius: 18px;
        border: 1px solid rgba(216, 221, 232, 0.12);
        background: rgba(216, 221, 232, 0.05);
        color: var(--c3);
        overflow: hidden;
    }

    .campaign-promotion-card__main,
    .campaign-promotion-card__heading,
    .campaign-promotion-card__stats,
    .campaign-promotion-card__targets {
        min-width: 0;
    }

    .campaign-promotion-card__main {
        display: grid;
        gap: 12px;
    }

    .campaign-promotion-card__heading {
        display: grid;
        grid-template-columns: auto minmax(0, 1fr);
        gap: 12px;
        align-items: start;
    }

    .campaign-promotion-card__icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 38px;
        height: 38px;
        border-radius: 13px;
        background: rgba(14, 183, 146, 0.12);
        color: rgba(142, 246, 219, 0.95);
    }

    .campaign-promotion-card strong {
        display: block;
        color: var(--c3);
        font-size: var(--fs-400);
        line-height: 1.2;
        overflow-wrap: anywhere;
    }

    .campaign-promotion-card small {
        display: block;
        margin-top: 4px;
        color: rgba(216, 221, 232, 0.7);
        overflow-wrap: anywhere;
    }

    .campaign-promotion-card__meta,
    .campaign-promotion-card__targets {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .campaign-promotion-card__meta > span:not(.marketing-status-pill),
    .campaign-promotion-card__targets > span {
        display: inline-flex;
        width: fit-content;
        max-width: 100%;
        padding: 7px 10px;
        border-radius: 999px;
        border: 1px solid rgba(216, 221, 232, 0.12);
        background: rgba(9, 3, 51, 0.42);
        color: rgba(216, 221, 232, 0.86);
        font-size: var(--fs-200);
        font-weight: 800;
        overflow-wrap: anywhere;
    }

    .campaign-promotion-card__stats {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 10px;
    }

    .campaign-promotion-card__stats span {
        display: grid;
        gap: 4px;
        padding: 12px;
        border-radius: 14px;
        background: rgba(9, 3, 51, 0.32);
        border: 1px solid rgba(216, 221, 232, 0.1);
    }

    .campaign-promotion-card__stats small {
        margin: 0;
        color: rgba(216, 221, 232, 0.66);
        font-size: var(--fs-100);
        font-weight: 800;
        text-transform: uppercase;
    }

    .campaign-promotion-card__stats strong {
        font-size: var(--fs-300);
    }

    .campaign-promotion-card > .order-detail__contact {
        justify-self: start;
    }

    .campaign-report-panel {
        display: grid;
        gap: 14px;
    }

    .campaign-report-panel__summary {
        display: grid;
        gap: 4px;
        padding: 15px;
        border-radius: 16px;
        border: 1px solid rgba(14, 183, 146, 0.18);
        background: rgba(14, 183, 146, 0.08);
        color: var(--c3);
    }

    .campaign-report-panel__summary strong,
    .campaign-report-panel__summary span {
        overflow-wrap: anywhere;
    }

    .campaign-report-panel__summary span {
        color: rgba(216, 221, 232, 0.78);
        line-height: 1.45;
    }

    .campaign-report-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 12px;
    }

    .campaign-report-card {
        display: grid;
        gap: 8px;
        padding: 15px;
        border-radius: 16px;
        border: 1px solid rgba(216, 221, 232, 0.12);
        background: rgba(216, 221, 232, 0.05);
        min-height: 102px;
    }

    .campaign-report-card--active {
        border-color: rgba(14, 183, 146, 0.24);
        background: rgba(14, 183, 146, 0.08);
    }

    .campaign-report-card--rate {
        border-color: rgba(98, 166, 255, 0.22);
        background: rgba(98, 166, 255, 0.07);
    }

    .campaign-report-card span {
        color: rgba(216, 221, 232, 0.7);
        font-size: var(--fs-200);
        font-weight: 800;
        text-transform: uppercase;
    }

    .campaign-report-card strong {
        color: var(--c3);
        font-size: var(--fs-500);
        line-height: 1;
        overflow-wrap: anywhere;
    }

    @keyframes marketingProgressSweep {
        to {
            transform: translateX(100%);
        }
    }

    .marketing-detail__compact-grid + .marketing-detail__assignment-list,
    .marketing-detail__compact-grid + .marketing-detail__empty {
        margin-top: 12px;
    }

    .marketing-detail__assignment-list {
        display: grid;
        gap: 12px;
    }

    .marketing-detail__assignment-head {
        display: grid;
        gap: 10px;
        grid-template-columns: minmax(0, 1fr) auto;
        align-items: start;
    }

    .marketing-detail__assignment-person {
        display: grid;
        gap: 5px;
    }

    .marketing-detail__assignment-person strong {
        font-size: var(--fs-300);
    }

    .marketing-detail__assignment-grid {
        display: grid;
        gap: 8px;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    }

    .marketing-detail__assignment-grid small,
    .marketing-detail__token {
        display: inline-flex;
        width: fit-content;
        max-width: 100%;
        padding: 7px 10px;
        border-radius: 999px;
        background: rgba(9, 3, 51, 0.52);
        border: 1px solid rgba(216, 221, 232, 0.12);
        color: var(--c3);
        font-size: var(--fs-200);
        overflow-wrap: anywhere;
        word-break: break-word;
        white-space: normal;
    }

    .marketing-detail__pager {
        display: flex;
        justify-content: center;
        margin-top: 14px;
    }

    @media (max-width: 720px) {
        .marketing-detail-page .order-detail__summary {
            grid-template-columns: 1fr;
            align-items: start;
        }

        .marketing-detail-page .order-detail__customer {
            text-align: left;
            justify-items: start;
        }

        .marketing-detail__assignment-head {
            grid-template-columns: 1fr;
        }

        .campaign-promotion-card {
            grid-template-columns: 1fr;
        }

        .campaign-promotion-card__stats {
            grid-template-columns: 1fr;
        }
    }
</style>
