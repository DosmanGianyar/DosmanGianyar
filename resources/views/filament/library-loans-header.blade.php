<style>
    /* Force Filament Table Actions into a 2-Column Grid Layout */
    .fi-ta-cell .fi-ta-actions,
    .fi-ta-cell .fi-ta-action-group,
    .fi-ta-cell .fi-ta-actions > div,
    .fi-ta-cell .fi-ta-action-group > div,
    .fi-ta-actions-cell > div {
        display: grid !important;
        grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
        grid-auto-flow: row !important;
        gap: 4px !important;
        width: max-content !important;
        max-width: 68px !important;
        justify-items: center !important;
        align-items: center !important;
    }
    .fi-ta-cell .fi-ta-actions button,
    .fi-ta-cell .fi-ta-actions a,
    .fi-ta-cell .fi-ta-action-group button,
    .fi-ta-cell .fi-ta-action-group a,
    .fi-ta-actions-cell button,
    .fi-ta-actions-cell a {
        margin: 0 !important;
        padding: 4px !important;
        display: inline-flex !important;
        justify-content: center !important;
        align-items: center !important;
    }
</style>
