<style>
    .marketing-index-page,
    .marketing-index-page * {
        min-width: 0;
    }

    .marketing-index-list {
        display: grid;
        gap: 12px;
    }

    .marketing-index-row {
        display: grid;
        grid-template-columns: minmax(0, 1.35fr) minmax(190px, .75fr) minmax(170px, .55fr) minmax(190px, auto);
        gap: 14px;
        align-items: center;
        padding: 16px;
        border-radius: 18px;
        border: 1px solid rgba(216, 221, 232, 0.12);
        background: rgba(216, 221, 232, 0.05);
        color: var(--c3);
        overflow: hidden;
    }

    .marketing-index-main,
    .marketing-index-block,
    .marketing-index-stats {
        display: grid;
        gap: 8px;
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

    .marketing-index-kicker {
        color: rgba(216, 221, 232, 0.72);
        font-size: var(--fs-200);
        font-weight: 800;
        text-transform: uppercase;
    }

    .marketing-index-title {
        margin: 0;
        color: var(--c3);
        font-size: var(--fs-400);
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

    .marketing-index-chip,
    .marketing-index-stat {
        display: inline-flex;
        width: fit-content;
        max-width: 100%;
        gap: 6px;
        align-items: center;
        padding: 7px 10px;
        border-radius: 999px;
        border: 1px solid rgba(216, 221, 232, 0.12);
        background: rgba(9, 3, 51, 0.38);
        color: var(--c3);
        font-size: var(--fs-200);
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
    }

    .marketing-index-actions form {
        margin: 0;
        display: flex;
    }

    .marketing-index-actions .order-detail__contact {
        max-width: 100%;
        justify-content: center;
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
        gap: 7px;
    }

    .marketing-index-progress-track {
        width: 100%;
        height: 10px;
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

        .marketing-index-actions {
            justify-content: flex-start;
        }
    }

    @media (max-width: 640px) {
        .marketing-index-row {
            padding: 14px;
            border-radius: 14px;
        }

        .marketing-index-extra,
        .marketing-index-secondary {
            display: none !important;
        }

        .marketing-index-title {
            font-size: var(--fs-300);
        }

        .marketing-index-actions {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .marketing-index-actions .order-detail__contact {
            width: 100%;
        }
    }
</style>
