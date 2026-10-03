<style>
.member-profile { max-width: 1600px; margin: 0 auto; padding: 0 8px; }
.member-profile .profile-hero { min-height: 340px; border-radius: 22px; overflow: hidden; }
.member-profile .profile-hero-inner { min-height: 340px; padding: 100px 32px 32px; display: flex; align-items: flex-end; }
.member-profile .profile-hero-content { position: relative; inset: auto; width: 100%; grid-template-columns: 136px minmax(0, 1fr) auto; gap: 24px; align-items: center; }
.member-profile .profile-avatar-wrap, .member-profile .profile-avatar { width: 136px; height: 136px; }
.member-profile .profile-avatar { border-radius: 24px; border: 4px solid rgba(220, 240, 255, .2); box-shadow: 0 12px 30px #0006; }
.member-profile .profile-name { font-size: clamp(1.7rem, 3vw, 2.6rem); overflow-wrap: anywhere; }
.member-profile .profile-actions { max-width: 260px; flex-wrap: wrap; }
.member-profile .profile-action-btn { border-radius: 10px; min-height: 42px; }
.member-profile .profile-meta-row { flex-wrap: wrap; }
.member-profile .profile-jump-links { display: flex; flex-wrap: wrap; gap: 8px; padding-bottom: 20px; margin-bottom: 24px; border-bottom: 1px solid var(--fi-border); }
.member-profile .profile-jump-links a { color: #bdd0df; padding: 9px 16px; border: 1px solid var(--fi-border); border-radius: 9px; background: #0a151e; text-decoration: none; font-weight: 600; }
.member-profile .profile-jump-links a:hover { color: #9cf4e3; border-color: #2dd4bf; }
.member-profile a:focus-visible, .member-profile button:focus-visible { outline: 2px solid #67e8f9; outline-offset: 4px; }
.member-profile .profile-section-heading { display: flex; justify-content: space-between; align-items: center; gap: 12px; flex-wrap: wrap; margin-bottom: 16px; }
.member-profile .elite-stats-grid .profile-section-heading { display: block; margin-bottom: 0; margin-top: 28px; }
.member-profile .profile-section-heading h2 { font-size: 1.2rem; font-weight: 700; margin: 4px 0; color: #edf6fb; }
.member-profile .profile-section-heading p, .member-profile .profile-tenure { font-size: .82rem; color: #a3b8c7; margin: 0; }
.member-profile .profile-eyebrow { color: #5eead4; font-size: .65rem; text-transform: uppercase; letter-spacing: .14em; font-weight: 700; }
.member-profile .profile-highlights { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 16px; }
.member-profile .profile-highlight { display: flex; align-items: flex-start; gap: 16px; padding: 24px; border: 1px solid #2b4b5c; border-radius: 16px; background: radial-gradient(ellipse at top right, #18405266, transparent 70%), #08131c; }
.member-profile .profile-highlight-icon { display: grid; place-items: center; width: 42px; height: 42px; border-radius: 12px; color: #6ee7ce; background: #2dd4bf15; flex-shrink: 0; font-size: 1.2rem; }
.member-profile .profile-highlight h3 { font-size: .78rem; color: #b2c6d3; margin: 0 0 8px; }
.member-profile .profile-highlight strong { font-size: clamp(1.35rem, 2.3vw, 2rem); color: #f0f9ff; font-variant-numeric: tabular-nums; overflow-wrap: anywhere; }
.member-profile .profile-highlight small { color: #9bb3c3; font-size: .9rem; margin-left: 3px; }
.member-profile .profile-highlight p { font-size: .73rem; color: #9bb3c3; margin: 6px 0 0; }
.member-profile .elite-stat-compact { min-height: 114px; padding: 20px; border-radius: 14px; text-align: left; }
.member-profile .elite-stats-grid .stat-card { align-items: flex-start; }
.member-profile .stat-label { color: #a3b8c7; font-size: .7rem; letter-spacing: .07em; }
.member-profile .stat-value { font-size: 1.18rem; margin-top: 10px; overflow-wrap: anywhere; max-width: 100%; font-variant-numeric: tabular-nums; }
.member-profile .stat-value .ms-1 { margin-left: 0 !important; font-size: .95rem; }
.member-profile .profile-secondary .accordion { margin-bottom: 20px; }
.member-profile #seederRankAccordion .accordion-item + .accordion-item { margin-top: 10px; }
.member-profile #seederRankAccordion .accordion-button { padding: 18px 20px; }
.member-profile .profile-secondary > .card { background: #08131c; border: 1px solid var(--fi-border); border-radius: 14px; }
.member-profile [id^="profile-"], .member-profile #seederRankAccordion { scroll-margin-top: 90px; }
@media (max-width: 1199.98px) {
    .member-profile .profile-hero-content { grid-template-columns: 120px minmax(0, 1fr); }
    .member-profile .profile-avatar-wrap, .member-profile .profile-avatar { width: 120px; height: 120px; }
    .member-profile .profile-actions { max-width: none; }
    .member-profile .profile-highlight { padding: 18px; gap: 10px; }
}
@media (max-width: 991.98px) {
    .member-profile .profile-hero-inner { padding: 70px 24px 24px; }
    .member-profile .profile-hero-content { display: flex; }
    .member-profile .profile-actions { justify-content: center; }
    .member-profile .profile-highlights { grid-template-columns: 1fr; }
    .member-profile .profile-highlight { align-items: center; }
}
@media (max-width: 575.98px) {
    .member-profile { padding: 0; }
    .member-profile .profile-hero { border-radius: 16px; }
    .member-profile .profile-hero-inner { padding: 48px 16px 24px; }
    .member-profile .profile-jump-links { gap: 6px; }
    .member-profile .profile-jump-links a { padding: 8px 12px; font-size: .78rem; }
    .member-profile .elite-stat-compact { padding: 14px; }
    .member-profile .stat-value { font-size: 1.05rem; }
}
</style>
