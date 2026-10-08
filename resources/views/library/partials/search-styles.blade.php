/* Shared movie and series search card. */
.lib-page .lib-search-card {
    display: flex;
    align-items: flex-end;
    gap: 1rem;
    padding: 1rem;
    border-radius: .85rem;
    box-shadow: 0 8px 24px var(--theme-shadow, rgba(0, 0, 0, .12));
}

.lib-search-field {
    flex: 1 1 280px;
    min-width: 0;
}

.lib-filter-label {
    display: block;
    margin-bottom: .45rem;
    color: var(--ui-text-muted);
    font-size: var(--site-font-small, 13px);
    font-weight: 600;
}

.lib-search-query {
    display: flex;
    align-items: center;
    gap: .65rem;
    min-height: 42px;
    padding: .45rem .75rem;
    background: var(--theme-surface-alt, #03080e);
    border: 1px solid var(--ui-border);
    border-radius: .5rem;
}

.lib-page .lib-search-card .lib-search-input {
    width: 100%;
    min-width: 0;
    padding: 0;
}

.lib-browse-options {
    display: grid;
    grid-template-columns: 110px minmax(140px, 1fr) minmax(160px, 1fr);
    gap: .75rem;
}

.lib-filter-field {
    min-width: 0;
}

.lib-search-card .lib-filter-field .form-control,
.lib-search-card .lib-filter-field .form-select {
    min-height: 42px;
    border-color: var(--ui-border);
    border-radius: .5rem;
    background-color: var(--theme-surface-alt, #03080e);
    color: var(--theme-text, #e2e8f0);
    font-size: var(--site-font-body, 13px);
}

.lib-search-card .lib-filter-field .form-control::placeholder {
    color: var(--ui-text-muted);
}

.lib-search-query:focus-within,
.lib-search-card .lib-filter-field .form-control:focus,
.lib-search-card .lib-filter-field .form-select:focus {
    border-color: var(--ui-accent);
    box-shadow: 0 0 0 2px var(--theme-teal-soft, rgba(99, 210, 198, .15));
}

.lib-page .lib-search-card .lib-search-btn {
    flex: 0 0 auto;
    min-height: 42px;
    justify-content: center;
    padding: .55rem 1rem;
}

@media (max-width: 991.98px) {
    .lib-page .lib-search-card {
        flex-wrap: wrap;
    }

    .lib-search-field {
        flex-basis: 100%;
    }

    .lib-browse-options {
        flex: 1 1 auto;
    }
}

@media (max-width: 575.98px) {
    .lib-page .lib-search-card {
        gap: .85rem;
        padding: .85rem;
    }

    .lib-browse-options {
        width: 100%;
        grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
    }

    .lib-filter-field:last-child {
        grid-column: 1 / -1;
    }

    .lib-page .lib-search-card .lib-search-btn {
        width: 100%;
    }
}
