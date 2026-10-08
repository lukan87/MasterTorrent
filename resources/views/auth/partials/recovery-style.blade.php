<style>
/* FileIplay password recovery */
body {
    margin: 0;
    min-height: 100vh;
    background:
        radial-gradient(circle at 50% 0%, var(--theme-teal-soft, rgba(20, 184, 166, .08)), transparent 38%),
        var(--theme-surface, #070b15);
    color:  var(--theme-text, #e5e7eb);
    font-family: Inter, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
    overflow-x: hidden;
}

.auth-wrapper {
    min-height: calc(100vh - 70px);
    width: 100%;
    padding: 2rem 1rem;
    display: flex;
    align-items: center;
    justify-content: center;
}

.glass-card {
    width: 100%;
    max-width: 620px;
    padding: 2rem;
    background: linear-gradient(
        135deg,
        var(--theme-surface, rgba(14,21,33,.96)),
        var(--theme-surface, rgba(10,15,27,.92))
    );
    border: 1px solid var(--theme-border, rgba(148, 163, 184, .18));
    border-radius: .85rem;
    box-shadow: 0 18px 45px var(--theme-shadow, rgba(0, 0, 0, .32));
}

.logo-wrapper {
    text-align: center;
    margin-bottom: 1.25rem;
}

.app-logo {
    display: inline-block;
    color: var(--theme-teal-text, #2dd4bf);
    font-size: 1.65rem;
    font-weight: 800;
    letter-spacing: 2px;
    text-shadow: 0 0 18px var(--theme-shadow, rgba(45, 212, 191, .18));
}

.recovery-title {
    color: var(--theme-text, #f8fafc);
    font-size: 1.15rem;
    font-weight: 700;
    letter-spacing: .8px;
    margin-bottom: .65rem;
}

.recovery-subtitle {
    color: var(--theme-muted, #94a3b8);
    text-align: center;
    font-size: var(--site-font-body, 13px);
    line-height: 1.55;
    margin: 0 auto 1.5rem;
    max-width: 500px;
}

/* Instructions */
.recovery-info {
    margin-bottom: 1.5rem;
    padding: 1rem 1.1rem;
    background: var(--theme-teal-soft, rgba(15, 118, 110, .10));
    border: 1px solid var(--theme-teal-border, rgba(45, 212, 191, .22));
    border-radius: .65rem;
}

.recovery-info-title {
    display: flex;
    align-items: center;
    gap: .5rem;
    color: var(--theme-teal-text, #5eead4);
    font-size: var(--site-font-body, 13px);
    font-weight: 700;
    margin-bottom: .8rem;
}

.recovery-steps {
    margin: 0;
    padding-left: 1.25rem;
    color: var(--theme-text, #cbd5e1);
    font-size: var(--site-font-body, 13px);
    line-height: 1.65;
}

.recovery-steps li {
    margin-bottom: .4rem;
}

.recovery-steps li:last-child {
    margin-bottom: 0;
}

.recovery-steps strong {
    color: var(--theme-text, #f8fafc);
}

.code-box {
    display: inline-block;
    padding: .15rem .45rem;
    margin: 0 .15rem;
    background: var(--theme-surface-alt, rgba(1,4,15,.7));
    border: 1px solid var(--theme-teal-border, rgba(45, 212, 191, .35));
    border-radius: .35rem;
    color: var(--theme-teal-text, #5eead4);
    font-family: monospace;
    font-size: var(--site-font-body, 13px);
    font-weight: 700;
    letter-spacing: .4px;
}

.security-note {
    margin-top: .85rem;
    padding-top: .8rem;
    border-top: 1px solid var(--theme-border, rgba(148, 163, 184, .12));
    color: var(--theme-muted, #94a3b8);
    font-size: var(--site-font-body, 13px);
    line-height: 1.5;
}

.form-label {
    color: var(--theme-text, #cbd5e1);
    font-size: var(--site-font-body, 13px);
    font-weight: 600;
    margin-bottom: .45rem;
}

.form-help {
    display: block;
    color: var(--theme-muted, #64748b);
    font-size: var(--site-font-body, 13px);
    margin-top: .4rem;
    line-height: 1.4;
}

.form-control {
    min-height: 44px;
    background: var(--theme-control, rgba(1,4,15,.48)) !important;
    border: 1px solid var(--theme-border, rgba(148, 163, 184, .22)) !important;
    border-radius: .55rem !important;
    color: var(--theme-text, #f8fafc) !important;
    padding: .65rem .8rem !important;
    font-size: var(--site-font-body, 13px);
    transition:
        border-color .2s ease,
        box-shadow .2s ease,
        background .2s ease;
}

.form-control::placeholder {
    color: var(--theme-muted, #64748b);
}

.form-control:focus {
    background: var(--theme-control, rgba(1,4,15,.62)) !important;
    border-color: var(--theme-teal-border, rgba(45, 212, 191, .75)) !important;
    box-shadow: 0 0 0 .18rem var(--theme-shadow, rgba(45, 212, 191, .10)) !important;
    outline: none !important;
}

.btn {
    min-height: 42px;
    border-radius: .55rem;
    font-size: var(--site-font-body, 13px);
    font-weight: 700;
    padding: .6rem 1rem;
    transition:
        transform .18s ease,
        border-color .18s ease,
        background .18s ease,
        box-shadow .18s ease;
}

.btn-recover {
    background: var(--theme-teal-action, #0f766e);
    border: 1px solid var(--theme-teal-border, #14b8a6);
    color: var(--theme-on-action, #fff);
    box-shadow: 0 5px 16px var(--theme-shadow, rgba(20, 184, 166, .16));
}

.btn-recover:hover,
.btn-recover:focus {
    background: var(--theme-teal-action, #0d9488);
    border-color: var(--theme-teal-border, #2dd4bf);
    color: var(--theme-on-action, #fff);
    transform: translateY(-1px);
    box-shadow: 0 7px 20px var(--theme-shadow, rgba(20, 184, 166, .22));
}

.btn-secondary-custom {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: var(--theme-surface, rgba(10,15,27,.75));
    border: 1px solid var(--theme-border, rgba(148, 163, 184, .22));
    color: var(--theme-text, #cbd5e1);
    text-decoration: none;
}

.btn-secondary-custom:hover,
.btn-secondary-custom:focus {
    background: var(--theme-surface, rgba(20,27,38,.95));
    border-color: var(--theme-teal-border, rgba(45, 212, 191, .55));
    color: var(--theme-text, #fff);
    transform: translateY(-1px);
}

.alert {
    border-radius: .55rem;
    border: 1px solid transparent;
    font-size: var(--site-font-body, 13px);
}

.alert-danger {
    background: var(--theme-red-soft, rgba(127, 29, 29, .35));
    border-color: var(--theme-red-border, rgba(248, 113, 113, .28));
    color: var(--theme-text, #fecaca);
}

.alert-success {
    background: var(--theme-surface, rgba(6, 78, 59, .38));
    border-color: var(--theme-teal-border, rgba(45, 212, 191, .28));
    color: var(--theme-green-text, #a7f3d0);
}

@media (max-width: 576px) {
    .auth-wrapper {
        min-height: calc(100vh - 40px);
        padding: 1rem .75rem;
        align-items: flex-start;
    }

    .glass-card {
        padding: 1.25rem;
        margin-top: 1rem;
    }

    .app-logo {
        font-size: 1.4rem;
    }

    .recovery-info {
        padding: .9rem;
    }

    .d-flex.gap-3 {
        gap: .5rem !important;
    }

    .d-flex.gap-3 .btn {
        flex: 1 1 100%;
    }
}
</style>
