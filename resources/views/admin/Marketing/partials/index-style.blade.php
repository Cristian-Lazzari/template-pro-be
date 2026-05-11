<style>
    .marketing-index-page,
    .marketing-index-page * {
        min-width: 0;
    }

    .marketing-index-list {
        display: grid;
        gap: 10px;
    }

    .marketing-index-row {
        display: grid;
        grid-template-columns: minmax(0, 1.1fr) minmax(280px, 1fr) minmax(150px, .5fr) minmax(150px, auto);
        gap: 12px;
        align-items: start;
        padding: 13px 14px;
        border-radius: 12px;
        border: 1px solid rgba(216, 221, 232, 0.12);
        background: rgba(216, 221, 232, 0.05);
        color: var(--c3);
        overflow: hidden;
    }

    .marketing-index-main,
    .marketing-index-block,
    .marketing-index-stats {
        display: grid;
        gap: 7px;
    }

    .marketing-index-kicker,
    .marketing-index-meta,
    .marketing-index-stat-row,
    .marketing-index-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        align-items: center;
    }

    .campaign-card__chips {
        display: flex;
        flex-wrap: wrap;
        gap: 7px;
        align-items: center;
    }

    .marketing-index-kicker {
        color: rgba(216, 221, 232, 0.72);
        font-size: var(--fs-200);
        font-weight: 800;
        text-transform: uppercase;
    }

    .marketing-index-title {
        margin: 0;
        color: var(--c3);
        font-size: var(--fs-300);
        line-height: 1.18;
        overflow-wrap: anywhere;
    }

    .marketing-index-copy,
    .marketing-index-meta span,
    .marketing-index-stat span {
        margin: 0;
        color: rgba(216, 221, 232, 0.74);
        font-size: var(--fs-200);
        line-height: 1.35;
        overflow-wrap: anywhere;
    }

    .campaign-card__meta {
        display: grid;
        gap: 5px;
    }

    .campaign-meta-row {
        display: grid;
        grid-template-columns: 92px minmax(0, 1fr);
        gap: 8px;
        align-items: baseline;
        color: rgba(216, 221, 232, 0.76);
        font-size: var(--fs-200);
        line-height: 1.35;
    }

    .campaign-meta-label {
        color: rgba(216, 221, 232, 0.56);
        font-size: var(--fs-100);
        font-weight: 900;
        text-transform: uppercase;
    }

    .campaign-meta-value {
        color: rgba(216, 221, 232, 0.86);
        overflow-wrap: anywhere;
    }

    .marketing-index-chip,
    .marketing-index-stat {
        display: inline-flex;
        width: fit-content;
        max-width: 100%;
        gap: 6px;
        align-items: center;
        padding: 6px 9px;
        border-radius: 999px;
        border: 1px solid rgba(216, 221, 232, 0.12);
        background: rgba(9, 3, 51, 0.38);
        color: var(--c3);
        font-size: var(--fs-100);
        font-weight: 700;
        overflow-wrap: anywhere;
    }

    .marketing-index-chip--accent {
        background: rgba(45, 212, 191, 0.12);
        border-color: rgba(45, 212, 191, 0.22);
    }

    .marketing-index-stat {
        display: grid;
        min-width: 82px;
        border-radius: 14px;
        align-items: start;
    }

    .marketing-index-stat strong {
        color: var(--c3);
        font-size: var(--fs-400);
        line-height: 1;
    }

    .marketing-index-actions {
        justify-content: flex-end;
        align-items: stretch;
    }

    .marketing-index-actions form {
        margin: 0;
        display: flex;
    }

    .marketing-index-actions .order-detail__contact {
        max-width: 100%;
        justify-content: center;
    }

    .campaign-actions-compact {
        display: grid;
        gap: 7px;
        justify-items: end;
    }

    .campaign-actions-compact__primary {
        display: flex;
        gap: 7px;
        justify-content: flex-end;
    }

    .campaign-actions-compact__secondary {
        position: relative;
        justify-self: end;
    }

    .campaign-actions-compact__secondary summary {
        list-style: none;
        cursor: pointer;
    }

    .campaign-actions-compact__secondary summary::-webkit-details-marker {
        display: none;
    }

    .campaign-actions-compact__panel {
        display: grid;
        gap: 7px;
        min-width: 150px;
        margin-top: 7px;
        padding: 8px;
        border-radius: 10px;
        border: 1px solid rgba(216, 221, 232, 0.12);
        background: rgba(9, 3, 51, 0.92);
        box-shadow: 0 14px 34px rgba(0, 0, 0, 0.28);
        z-index: 2;
    }

    .campaign-actions-compact__panel form,
    .campaign-actions-compact__panel .order-detail__contact {
        width: 100%;
    }

    .marketing-index-danger {
        background: rgba(206, 59, 59, 0.1);
        border-color: rgba(255, 141, 141, 0.22);
    }

    .marketing-index-muted {
        background: rgba(9, 3, 51, 0.36);
    }

    .marketing-index-progress {
        display: grid;
        gap: 6px;
    }

    .marketing-index-progress-track {
        width: 100%;
        height: 7px;
        border-radius: 999px;
        overflow: hidden;
        border: 1px solid rgba(216, 221, 232, 0.12);
        background: rgba(9, 3, 51, 0.42);
    }

    .marketing-index-progress-bar {
        height: 100%;
        border-radius: inherit;
        background: linear-gradient(90deg, rgba(74, 222, 128, 0.9), rgba(45, 212, 191, 0.86));
    }

    .marketing-index-pager {
        display: flex;
        justify-content: center;
        margin-top: 16px;
    }

    @media (max-width: 920px) {
        .marketing-index-row {
            grid-template-columns: minmax(0, 1fr);
            align-items: stretch;
        }

        .campaign-actions-compact,
        .marketing-index-actions {
            justify-items: start;
            justify-content: flex-start;
        }

        .campaign-actions-compact__primary {
            justify-content: flex-start;
        }
    }

    @media (max-width: 640px) {
        .marketing-index-row {
            padding: 14px;
            border-radius: 14px;
        }

        .marketing-index-extra {
            display: none !important;
        }

        .marketing-index-title {
            font-size: var(--fs-300);
        }

        .campaign-actions-compact,
        .campaign-actions-compact__primary {
            width: 100%;
        }

        .campaign-actions-compact__primary {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .marketing-index-actions .order-detail__contact {
            width: 100%;
        }

        .campaign-meta-row {
            grid-template-columns: 1fr;
            gap: 2px;
        }
    }
</style>
