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
    }
</style>
