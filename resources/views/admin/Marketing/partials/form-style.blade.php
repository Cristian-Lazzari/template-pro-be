<style>
    .marketing-form-shell.creation,
    .dash_page .marketing-form-shell.creation,
    .admin-shell--wide .marketing-form-shell.creation:not(.email-m) {
        width: 100%;
        max-width: none !important;
        margin-inline: 0 !important;
        padding-inline: 0;
        justify-self: stretch;
        align-self: stretch;
    }

    .marketing-form-shell,
    .marketing-form-shell * {
        box-sizing: border-box;
    }

    .marketing-form-shell .order-detail__section,
    .marketing-form-shell section {
        width: 100%;
        min-width: 0;
    }

    .marketing-form-shell .split,
    .marketing-form-shell .split-3 {
        display: grid !important;
        grid-template-columns: repeat(auto-fit, minmax(min(100%, 280px), 1fr));
        gap: 18px;
        align-items: start;
    }

    .marketing-form-shell .split > div,
    .marketing-form-shell .split-3 > div {
        width: 100% !important;
        min-width: 0;
    }

    .marketing-form-shell p {
        width: 100%;
        min-width: 0;
        margin-bottom: 0;
    }

    .marketing-form-shell input:not([type="checkbox"]):not([type="radio"]):not([type="hidden"]),
    .marketing-form-shell select,
    .marketing-form-shell textarea {
        display: block;
        width: 100% !important;
        max-width: 100%;
        min-width: 0;
    }

    .marketing-form-shell label,
    .marketing-form-shell .label_c,
    .marketing-form-shell .menu-dashboard__copy,
    .marketing-form-shell .error {
        min-width: 0;
        overflow-wrap: anywhere;
    }

    .marketing-form-shell .menu-dashboard__hero-actions {
        justify-content: flex-start;
    }

    .marketing-form-grid {
        display: grid;
        grid-template-columns: minmax(0, 2fr) minmax(280px, 1fr);
        gap: 22px;
        align-items: start;
        width: 100%;
        min-width: 0;
    }

    .marketing-form-main,
    .marketing-form-sidebar {
        display: grid;
        gap: 16px;
        min-width: 0;
    }

    .marketing-form-sidebar {
        position: sticky;
        top: 24px;
    }

    .marketing-form-actions {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: flex-end;
        gap: 12px;
        width: 100%;
        margin-top: 22px;
        padding: 4px 0;
    }

    .marketing-form-actions .order-detail__contact {
        min-height: 46px;
        border-radius: 14px;
        border: 1px solid rgba(216, 221, 232, 0.14);
    }

    .marketing-form-action--primary {
        border-color: rgba(14, 183, 146, 0.46) !important;
        background: linear-gradient(135deg, rgba(14, 183, 146, 0.95), rgba(11, 142, 116, 0.92)) !important;
        color: #ffffff !important;
    }

    .marketing-form-action--secondary {
        border-color: rgba(98, 166, 255, 0.26) !important;
        background: rgba(98, 166, 255, 0.11) !important;
        color: rgba(216, 221, 232, 0.94) !important;
    }

    .marketing-form-action--cancel {
        background: transparent !important;
        color: rgba(216, 221, 232, 0.82) !important;
    }

    .marketing-form-preview {
        display: grid;
        gap: 16px;
        overflow: hidden;
    }

    .marketing-form-preview__panel {
        display: grid;
        gap: 14px;
        padding: 16px;
        border-radius: 18px;
        border: 1px solid rgba(216, 221, 232, 0.12);
        background:
            linear-gradient(145deg, rgba(14, 183, 146, 0.1), rgba(216, 221, 232, 0.05));
        color: var(--c3);
    }

    .marketing-form-preview__head {
        display: grid;
        grid-template-columns: auto minmax(0, 1fr);
        gap: 12px;
        align-items: start;
    }

    .marketing-form-preview__icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 42px;
        height: 42px;
        border-radius: 15px;
        border: 1px solid rgba(14, 183, 146, 0.22);
        background: rgba(14, 183, 146, 0.12);
        color: rgba(142, 246, 219, 0.95);
    }

    .marketing-form-preview__head strong,
    .marketing-form-preview__panel h4 {
        color: var(--c3);
        line-height: 1.2;
        overflow-wrap: anywhere;
    }

    .marketing-form-preview__head strong {
        display: block;
        font-size: var(--fs-400);
    }

    .marketing-form-preview__head small,
    .marketing-form-preview__panel p {
        color: rgba(216, 221, 232, 0.74);
        line-height: 1.45;
        overflow-wrap: anywhere;
    }

    .marketing-form-preview__facts {
        display: grid;
        gap: 10px;
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .marketing-form-preview__fact {
        display: grid;
        gap: 4px;
        padding: 12px;
        border-radius: 14px;
        border: 1px solid rgba(216, 221, 232, 0.1);
        background: rgba(9, 3, 51, 0.34);
        min-width: 0;
    }

    .marketing-form-preview__fact span,
    .marketing-form-preview__chips span {
        color: rgba(216, 221, 232, 0.66);
        font-size: var(--fs-100);
        font-weight: 800;
        text-transform: uppercase;
    }

    .marketing-form-preview__fact strong {
        color: var(--c3);
        font-size: var(--fs-300);
        line-height: 1.2;
        overflow-wrap: anywhere;
    }

    .marketing-form-preview__chips {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .marketing-form-preview__chips span {
        display: inline-flex;
        width: fit-content;
        max-width: 100%;
        padding: 7px 10px;
        border-radius: 999px;
        border: 1px solid rgba(216, 221, 232, 0.12);
        background: rgba(9, 3, 51, 0.42);
        color: rgba(216, 221, 232, 0.86);
        overflow-wrap: anywhere;
    }

    .marketing-form-preview__steps {
        display: grid;
        gap: 10px;
    }

    .marketing-form-preview__step {
        display: grid;
        grid-template-columns: auto minmax(0, 1fr);
        gap: 10px;
        align-items: start;
        padding: 12px;
        border-radius: 14px;
        border: 1px solid rgba(216, 221, 232, 0.1);
        background: rgba(216, 221, 232, 0.05);
    }

    .marketing-form-preview__step i {
        color: rgba(142, 246, 219, 0.92);
    }

    .marketing-form-preview__step strong {
        display: block;
        color: var(--c3);
        font-size: var(--fs-200);
        line-height: 1.25;
        overflow-wrap: anywhere;
    }

    .marketing-form-preview__step small {
        display: block;
        margin-top: 3px;
        color: rgba(216, 221, 232, 0.72);
        line-height: 1.4;
        overflow-wrap: anywhere;
    }

    .marketing-form-preview__note {
        padding: 13px;
        border-radius: 15px;
        border: 1px solid rgba(98, 166, 255, 0.18);
        background: rgba(98, 166, 255, 0.07);
        color: rgba(216, 221, 232, 0.82);
        line-height: 1.45;
    }

    .campaign-promotion-picker {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(min(100%, 230px), 1fr));
        gap: 12px;
        width: 100%;
        min-width: 0;
    }

    .campaign-promotion-option {
        position: relative;
        min-width: 0;
    }

    .campaign-promotion-option__input {
        position: absolute;
        inline-size: 1px;
        block-size: 1px;
        opacity: 0;
        pointer-events: none;
    }

    .campaign-promotion-option__card {
        display: grid;
        grid-template-columns: auto minmax(0, 1fr);
        gap: 12px;
        min-height: 100%;
        padding: 15px;
        border-radius: 18px;
        border: 1px solid rgba(216, 221, 232, 0.12);
        background: rgba(216, 221, 232, 0.05);
        color: var(--c3);
        cursor: pointer;
        transition: border-color .16s ease, background .16s ease, transform .16s ease;
    }

    .campaign-promotion-option__card:hover {
        transform: translateY(-1px);
        border-color: rgba(14, 183, 146, 0.26);
        background: rgba(14, 183, 146, 0.08);
    }

    .campaign-promotion-option__input:focus-visible + .campaign-promotion-option__card {
        outline: 2px solid rgba(142, 246, 219, 0.7);
        outline-offset: 3px;
    }

    .campaign-promotion-option__input:checked + .campaign-promotion-option__card {
        border-color: rgba(14, 183, 146, 0.42);
        background:
            linear-gradient(135deg, rgba(14, 183, 146, 0.18), rgba(216, 221, 232, 0.05));
    }

    .campaign-promotion-option__icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 38px;
        height: 38px;
        border-radius: 13px;
        border: 1px solid rgba(216, 221, 232, 0.12);
        background: rgba(9, 3, 51, 0.42);
        color: rgba(142, 246, 219, 0.92);
    }

    .campaign-promotion-option__card strong,
    .campaign-promotion-option__card small {
        display: block;
        overflow-wrap: anywhere;
    }

    .campaign-promotion-option__card strong {
        color: var(--c3);
        font-size: var(--fs-300);
        line-height: 1.22;
    }

    .campaign-promotion-option__card small {
        margin-top: 4px;
        color: rgba(216, 221, 232, 0.72);
        line-height: 1.4;
    }

    @media (max-width: 1120px) {
        .marketing-form-grid {
            grid-template-columns: 1fr;
        }

        .marketing-form-sidebar {
            position: static;
        }
    }

    @media (max-width: 720px) {
        .marketing-form-shell .split,
        .marketing-form-shell .split-3 {
            grid-template-columns: 1fr;
        }

        .marketing-form-preview__facts {
            grid-template-columns: 1fr;
        }

        .marketing-form-actions {
            align-items: stretch;
            flex-direction: column;
        }

        .marketing-form-actions .order-detail__contact {
            width: 100%;
            justify-content: center;
        }
    }

    /* ── CPV2 nav buttons ────────────────────────────────────────── */
    .cpv2-nav {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .cpv2-nav-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 14px;
        border-radius: 10px;
        border: 1px solid rgba(216, 221, 232, 0.14);
        background: rgba(216, 221, 232, 0.05);
        color: rgba(216, 221, 232, 0.82);
        font-size: var(--fs-200);
        font-weight: 700;
        cursor: pointer;
        transition: background .15s, border-color .15s;
        white-space: nowrap;
        flex-shrink: 0;
    }

    .cpv2-nav-btn:hover {
        background: rgba(216, 221, 232, 0.1);
        border-color: rgba(216, 221, 232, 0.22);
    }

    .cpv2-nav-btn[hidden] {
        display: none !important;
    }

    .cpv2-nav-btn--primary {
        flex: 1;
        justify-content: center;
        border-color: rgba(14, 183, 146, 0.38);
        background: rgba(14, 183, 146, 0.12);
        color: rgba(142, 246, 219, 0.95);
    }

    .cpv2-nav-btn--primary:hover {
        background: rgba(14, 183, 146, 0.2);
        border-color: rgba(14, 183, 146, 0.52);
    }

    .cpv2-nav-step {
        flex: 0 0 auto;
        color: rgba(216, 221, 232, 0.42);
        font-size: var(--fs-100);
        font-weight: 700;
        text-align: center;
        min-width: 36px;
        user-select: none;
    }

    /* ── CPV2 preview card ───────────────────────────────────────── */
    .cpv2-card {
        display: grid;
        gap: 12px;
        padding: 16px;
        border-radius: 14px;
        border: 1px solid rgba(216, 221, 232, 0.1);
        background: rgba(9, 3, 51, 0.22);
    }

    .cpv2-header {
        display: grid;
        grid-template-columns: auto 1fr;
        gap: 12px;
        align-items: start;
        padding-bottom: 12px;
        border-bottom: 1px solid rgba(216, 221, 232, 0.08);
    }

    .cpv2-header-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 36px;
        height: 36px;
        border-radius: 10px;
        border: 1px solid rgba(142, 246, 219, 0.2);
        background: rgba(14, 183, 146, 0.1);
        color: rgba(142, 246, 219, 0.88);
        font-size: 15px;
        flex: 0 0 auto;
    }

    .cpv2-eyebrow {
        display: block;
        color: rgba(216, 221, 232, 0.4);
        font-size: 10px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 4px;
    }

    .cpv2-name-display {
        display: block;
        color: var(--c3);
        font-size: var(--fs-300);
        font-weight: 900;
        line-height: 1.2;
        overflow-wrap: anywhere;
    }

    .cpv2-badges {
        display: flex;
        flex-wrap: wrap;
        gap: 5px;
        margin-top: 7px;
    }

    .cpv2-badge {
        display: inline-flex;
        padding: 3px 8px;
        border-radius: 999px;
        border: 1px solid rgba(14, 183, 146, 0.28);
        background: rgba(14, 183, 146, 0.1);
        color: rgba(142, 246, 219, 0.9);
        font-size: 10px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        line-height: 1.3;
    }

    .cpv2-badge--muted {
        border-color: rgba(216, 221, 232, 0.14);
        background: rgba(216, 221, 232, 0.06);
        color: rgba(216, 221, 232, 0.65);
    }

    /* ── CPV2 checklist rows ─────────────────────────────────────── */
    .cpv2-rows {
        list-style: none;
        margin: 0;
        padding: 0;
        display: grid;
        gap: 1px;
    }

    .cpv2-row {
        display: grid;
        grid-template-columns: 16px 1fr auto;
        align-items: center;
        gap: 8px;
        padding: 5px 4px;
        border-radius: 6px;
        min-width: 0;
    }

    .cpv2-row-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
        flex: 0 0 auto;
        color: rgba(216, 221, 232, 0.3);
        line-height: 1;
    }

    .cpv2-row--done .cpv2-row-icon {
        color: rgba(142, 246, 219, 0.75);
    }

    .cpv2-row--optional .cpv2-row-icon {
        color: rgba(216, 221, 232, 0.25);
    }

    .cpv2-row-label {
        color: rgba(216, 221, 232, 0.52);
        font-size: var(--fs-100);
        font-weight: 700;
        line-height: 1.3;
        min-width: 0;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .cpv2-row-val {
        color: var(--c3);
        font-size: var(--fs-100);
        font-weight: 800;
        line-height: 1.3;
        text-align: right;
        min-width: 0;
        max-width: 130px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .cpv2-row--empty .cpv2-row-val,
    .cpv2-row--optional .cpv2-row-val {
        color: rgba(216, 221, 232, 0.35);
        font-style: italic;
        font-weight: 700;
    }
</style>
