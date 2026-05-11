<style>
    .marketing-index-page,
    .marketing-index-page * {
        min-width: 0;
    }

    .campaign-index-hero {
        display: grid;
        gap: 10px;
        padding: 18px 20px;
        border-radius: 10px;
        border: 1px solid rgba(216, 221, 232, 0.11);
        background: rgba(216, 221, 232, 0.045);
    }

    .campaign-index-hero__top {
        display: flex;
        gap: 16px;
        align-items: flex-start;
        justify-content: space-between;
    }

    .campaign-index-hero__title {
        display: grid;
        gap: 3px;
    }

    .campaign-index-hero__title > span {
        color: rgba(216, 221, 232, 0.6);
        font-size: var(--fs-100);
        font-weight: 900;
        letter-spacing: 0;
        text-transform: uppercase;
    }

    .campaign-index-hero__title h1 {
        margin: 0;
        color: var(--c3);
        font-size: var(--fs-600);
        line-height: 1.05;
    }

    .campaign-index-hero__title p {
        max-width: 680px;
        margin: 0;
        color: rgba(216, 221, 232, 0.72);
        font-size: var(--fs-200);
        line-height: 1.35;
    }

    .campaign-index-hero__primary,
    .campaign-index-quicklinks a {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        width: fit-content;
        max-width: 100%;
        border-radius: 7px;
        text-decoration: none;
        line-height: 1.15;
        white-space: nowrap;
    }

    .campaign-index-hero__primary {
        flex: 0 0 auto;
        padding: 10px 13px;
        border: 1px solid rgba(14, 183, 146, 0.32);
        background: rgba(14, 183, 146, 0.14);
        color: rgba(228, 255, 248, 0.98);
        font-size: var(--fs-200);
        font-weight: 900;
    }

    .campaign-index-quicklinks {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        padding-top: 9px;
        border-top: 1px solid rgba(216, 221, 232, 0.08);
    }

    .campaign-index-quicklinks a,
    .campaign-index-quicklinks .order-detail__contact {
        min-height: 0;
        padding: 6px 8px;
        border: 1px solid transparent;
        background: transparent;
        color: rgba(216, 221, 232, 0.68);
        font-size: var(--fs-100);
        font-weight: 800;
        box-shadow: none;
    }

    .campaign-index-quicklinks a:hover,
    .campaign-index-quicklinks .order-detail__contact:hover {
        border-color: rgba(216, 221, 232, 0.12);
        background: rgba(216, 221, 232, 0.06);
        color: var(--c3);
    }

    .marketing-index-page .order-detail__section.mt-4 {
        margin-top: 14px !important;
    }

    .marketing-index-list {
        display: grid;
        gap: 8px;
    }

    .campaign-row-card {
        position: relative;
        display: grid;
        grid-template-columns: minmax(220px, 1.35fr) minmax(150px, .65fr) minmax(160px, .7fr) minmax(150px, .7fr) minmax(132px, auto);
        gap: 14px;
        align-items: center;
        padding: 11px 12px;
        border-radius: 8px;
        border: 1px solid rgba(216, 221, 232, 0.11);
        background: rgba(216, 221, 232, 0.04);
        color: var(--c3);
        overflow: visible;
    }

    .campaign-row-card:hover {
        border-color: rgba(216, 221, 232, 0.18);
        background: rgba(216, 221, 232, 0.055);
    }

    .campaign-row-card__identity,
    .campaign-row-card__status,
    .campaign-row-card__details,
    .campaign-row-card__progress {
        min-width: 0;
    }

    .campaign-row-card__identity {
        display: grid;
        gap: 6px;
    }

    .campaign-row-card__title {
        margin: 0;
        color: var(--c3);
        font-size: var(--fs-400);
        font-weight: 900;
        line-height: 1.16;
        overflow-wrap: anywhere;
    }

    .campaign-row-card__meta-line {
        display: flex;
        flex-wrap: wrap;
        gap: 6px 8px;
        align-items: center;
        color: rgba(216, 221, 232, 0.68);
        font-size: var(--fs-100);
        line-height: 1.25;
    }

    .campaign-row-card__basis {
        display: inline-flex;
        max-width: 100%;
        align-items: center;
        padding: 3px 7px;
        border-radius: 999px;
        border: 1px solid rgba(45, 212, 191, 0.18);
        background: rgba(45, 212, 191, 0.08);
        color: rgba(201, 255, 245, 0.9);
        font-weight: 850;
        overflow-wrap: anywhere;
    }

    .campaign-row-card__status {
        display: grid;
        justify-items: start;
        gap: 6px;
        color: rgba(216, 221, 232, 0.66);
        font-size: var(--fs-100);
        line-height: 1.25;
    }

    .campaign-row-card__status .settings-state {
        padding: 4px 8px;
        font-size: var(--fs-100);
        line-height: 1.1;
    }

    .campaign-row-card__details {
        display: grid;
        gap: 5px;
    }

    .campaign-row-card__details div {
        display: grid;
        grid-template-columns: 58px minmax(0, 1fr);
        gap: 7px;
        align-items: baseline;
        font-size: var(--fs-100);
        line-height: 1.25;
    }

    .campaign-row-card__details span {
        color: rgba(216, 221, 232, 0.52);
        font-weight: 900;
        text-transform: uppercase;
    }

    .campaign-row-card__details strong {
        color: rgba(216, 221, 232, 0.86);
        font-size: var(--fs-100);
        font-weight: 800;
        overflow-wrap: anywhere;
    }

    .campaign-progress-compact {
        display: grid;
        gap: 6px;
    }

    .campaign-progress-compact__head {
        display: flex;
        gap: 8px;
        align-items: baseline;
        justify-content: space-between;
        color: rgba(216, 221, 232, 0.82);
        font-size: var(--fs-100);
        font-weight: 850;
        line-height: 1.2;
    }

    .campaign-progress-compact__head small {
        color: rgba(216, 221, 232, 0.52);
        font-size: var(--fs-100);
        font-weight: 800;
        white-space: nowrap;
    }

    .campaign-progress-compact__track {
        width: 100%;
        height: 5px;
        overflow: hidden;
        border-radius: 999px;
        background: rgba(9, 3, 51, 0.52);
    }

    .campaign-progress-compact__bar {
        height: 100%;
        border-radius: inherit;
        background: linear-gradient(90deg, rgba(74, 222, 128, 0.88), rgba(45, 212, 191, 0.78));
    }

    .campaign-row-card__actions {
        justify-self: end;
    }

    .campaign-row-card__actions.campaign-actions-compact {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        align-items: center;
        justify-content: flex-end;
    }

    .campaign-row-card__actions form {
        display: flex;
        margin: 0;
    }

    .campaign-row-card__actions .order-detail__contact {
        min-height: 0;
        padding: 7px 9px;
        border-radius: 7px;
        font-size: var(--fs-100);
        line-height: 1.1;
    }

    .campaign-row-card__actions .order-detail__contact i,
    .campaign-row-card__actions .order-detail__contact svg {
        font-size: var(--fs-200);
        width: 1em;
        height: 1em;
    }

    .campaign-row-card__icon-action {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        padding: 0;
        border-radius: 7px;
        border: 1px solid rgba(216, 221, 232, 0.13);
        background: rgba(9, 3, 51, 0.34);
        color: rgba(216, 221, 232, 0.78);
        cursor: pointer;
    }

    .campaign-row-card__icon-action:hover {
        border-color: rgba(216, 221, 232, 0.22);
        background: rgba(216, 221, 232, 0.07);
        color: var(--c3);
    }

    .campaign-row-card__icon-action--danger {
        border-color: rgba(255, 141, 141, 0.2);
        background: rgba(206, 59, 59, 0.08);
        color: rgba(255, 180, 180, 0.9);
    }

    .campaign-row-card__icon-action--danger:hover {
        border-color: rgba(255, 141, 141, 0.32);
        background: rgba(206, 59, 59, 0.14);
        color: rgba(255, 220, 220, 0.98);
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
        gap: 6px;
        justify-items: end;
    }

    .campaign-actions-compact__primary {
        display: flex;
        gap: 6px;
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
        position: absolute;
        top: calc(100% + 5px);
        right: 0;
        display: grid;
        gap: 5px;
        min-width: 176px;
        padding: 8px;
        border-radius: 8px;
        border: 1px solid rgba(216, 221, 232, 0.12);
        background: rgba(9, 3, 51, 0.96);
        box-shadow: 0 14px 34px rgba(0, 0, 0, 0.28);
        z-index: 10;
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
        .campaign-index-hero__top {
            align-items: stretch;
            flex-direction: column;
        }

        .campaign-index-hero__primary {
            justify-content: center;
        }

        .campaign-row-card {
            grid-template-columns: minmax(0, 1fr) minmax(140px, .45fr);
            align-items: start;
        }

        .campaign-row-card__identity {
            grid-column: 1 / -1;
        }

        .campaign-row-card__actions {
            justify-self: start;
        }

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
        .campaign-index-hero {
            padding: 15px;
        }

        .campaign-index-quicklinks {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .campaign-index-quicklinks a,
        .campaign-index-quicklinks .order-detail__contact {
            width: 100%;
            justify-content: center;
            white-space: normal;
            text-align: center;
        }

        .campaign-row-card {
            grid-template-columns: minmax(0, 1fr);
            gap: 10px;
            padding: 11px;
        }

        .campaign-row-card__details {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .campaign-row-card__details div {
            grid-template-columns: 1fr;
            gap: 2px;
        }

        .campaign-row-card__actions,
        .campaign-actions-compact {
            justify-self: stretch;
            justify-items: stretch;
        }

        .campaign-actions-compact__primary {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .campaign-actions-compact__secondary {
            justify-self: stretch;
        }

        .campaign-actions-compact__panel {
            position: static;
            margin-top: 6px;
        }

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
