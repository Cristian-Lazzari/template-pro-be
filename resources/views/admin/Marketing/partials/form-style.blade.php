<style>
    .marketing-form-shell.creation {
        width: 100%;
        max-width: 100%;
        margin-inline: 0;
        padding-inline: 0;
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

    @media (max-width: 720px) {
        .marketing-form-shell .split,
        .marketing-form-shell .split-3 {
            grid-template-columns: 1fr;
        }
    }
</style>
