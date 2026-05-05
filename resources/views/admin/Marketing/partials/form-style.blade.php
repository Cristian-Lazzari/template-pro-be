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
        grid-template-columns: minmax(0, 1.25fr) minmax(300px, .75fr);
        gap: 18px;
        align-items: start;
        width: 100%;
        min-width: 0;
    }

    .marketing-form-main,
    .marketing-form-sidebar {
        display: grid;
        gap: 18px;
        min-width: 0;
    }

    .marketing-form-sidebar {
        position: sticky;
        top: 18px;
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
    }
</style>
