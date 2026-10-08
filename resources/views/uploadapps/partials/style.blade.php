<style>
.ua-page { max-width: 1440px; margin: auto; color: var(--theme-text, #dce8f0); }
.ua-page .ua-hero { padding: 28px; border: 1px solid var(--theme-border, #294656); border-radius: 18px; background: radial-gradient(ellipse at top right, var(--theme-teal-soft, #155e7544), transparent 65%), var(--theme-surface, #0a141c); }
.ua-page .ua-eyebrow { color: var(--theme-teal-text, #5eead4); font-size: var(--site-font-small, 13px); letter-spacing: .13em; text-transform: uppercase; font-weight: 700; }
.ua-page h1 { font-size: clamp(1.5rem, 3vw, 2rem); font-weight: 700; margin: 8px 0; }
.ua-page h2 { font-size: 1.05rem; font-weight: 650; }
.ua-page .ua-muted { color: var(--theme-muted, #a0b6c6); font-size: var(--site-font-body, 13px); }
.ua-page .ua-card { background: var(--theme-surface, #0a131b); border: 1px solid var(--theme-border, #293c4c); border-radius: 14px; padding: 24px; height: 100%; }
.ua-page .ua-card-header { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; margin-bottom: 20px; }
.ua-page .ua-card-header h2 { margin: 0; }
.ua-page .ua-badge { display: inline-flex; align-items: center; gap: 6px; border: 1px solid currentColor; border-radius: 99px; padding: 5px 12px; font-size: var(--site-font-small, 13px); font-weight: 650; white-space: nowrap; }
.ua-page .ua-pending { color: var(--theme-amber-text, #fcd477); background: var(--theme-amber-soft, #fcd4770c); }
.ua-page .ua-discussion, .ua-page .ua-voting { color: var(--theme-blue-text, #8fd5ff); background: var(--theme-blue-soft, #8fd5ff0c); }
.ua-page .ua-accepted { color: var(--theme-teal-text, #6ee7b7); background: var(--theme-teal-soft, #6ee7b70c); }
.ua-page .ua-rejected { color: var(--theme-red-text, #fda4af); background: var(--theme-red-soft, #fda4af0c); }
.ua-page .ua-requirements { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 16px; }
.ua-page .ua-requirement { padding: 16px; background: var(--theme-surface, #060d15); border-radius: 10px; border: 1px solid var(--theme-border, #293c4c); }
.ua-page .ua-requirement strong { display: block; margin: 6px 0; }
.ua-page .ua-filters { display: flex; gap: 8px; flex-wrap: wrap; }
.ua-page .ua-filters a { color: var(--theme-text, #b9ccdb); border: 1px solid var(--theme-border, #33495d); border-radius: 9px; padding: 8px 12px; text-decoration: none; font-size: var(--site-font-body, 13px); }
.ua-page .ua-filters a[aria-current="page"], .ua-page .ua-filters a:hover { color: var(--theme-teal-text, #81f4d8); background: var(--theme-surface, #0e221f); border-color: var(--theme-teal-border, #347b68); }
.ua-page .ua-table { --bs-table-bg: transparent; --bs-table-color: var(--theme-text, #dce8f0); --bs-table-border-color: var(--theme-border, #293c4c); --bs-table-hover-bg: var(--theme-surface, #101d26); --bs-table-hover-color: var(--theme-text, #fff); }
.ua-page .ua-table th { color: var(--theme-muted, #a0b6c6); font-size: var(--site-font-small, 13px); text-transform: uppercase; letter-spacing: .04em; padding: 14px 12px; white-space: nowrap; }
.ua-page .ua-table td { padding: 16px 12px; vertical-align: middle; font-size: var(--site-font-body, 13px); }
.ua-page .ua-table a, .ua-page .ua-link { color: var(--theme-teal-text, #81d4ee); text-decoration: none; }
.ua-page .ua-table a:hover, .ua-page .ua-link:hover { text-decoration: underline; }
.ua-page .ua-empty { padding: 40px 16px; text-align: center; color: var(--theme-muted, #a0b6c6); }
.ua-page .ua-answer { white-space: pre-wrap; overflow-wrap: anywhere; line-height: 1.7; font-size: var(--site-font-body, 13px); }
.ua-page .ua-answer-section + .ua-answer-section { margin-top: 24px; padding-top: 20px; border-top: 1px solid var(--theme-border, #293c4c); }
.ua-page .ua-details { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; margin: 0; }
.ua-page .ua-details dt { color: var(--theme-muted, #a0b6c6); font-size: var(--site-font-small, 13px); font-weight: 400; }
.ua-page .ua-details dd { margin: 4px 0 0; overflow-wrap: anywhere; }
.ua-page .ua-comment { border-left: 2px solid var(--theme-teal-border, #428f81); padding: 12px 16px; background: var(--theme-surface, #070f17); border-radius: 0 8px 8px 0; margin-bottom: 12px; }
.ua-page .form-control, .ua-page .form-select { background-color: var(--theme-control, #060d15); color: var(--theme-text, #e2ecf4); border-color: var(--theme-border, #3a5062); }
.ua-page .form-control::placeholder { color: var(--theme-muted, #8298a9); }
.ua-page .form-control:focus, .ua-page .form-select:focus { border-color: var(--theme-teal-border, #5eead4); box-shadow: 0 0 0 3px var(--theme-shadow, #5eead41a); }
.ua-page .form-label { font-size: var(--site-font-body, 13px); font-weight: 600; }
.ua-page .form-text { color: var(--theme-muted, #a0b6c6); }
.ua-page .btn { border-radius: 8px; padding: 9px 16px; font-size: var(--site-font-body, 13px); }
.ua-page a:focus-visible, .ua-page button:focus-visible { outline: 2px solid var(--theme-teal-border, #5eead4); outline-offset: 3px; }
@media(max-width: 767.98px) { .ua-page .ua-hero, .ua-page .ua-card { padding: 18px; } .ua-page .ua-requirements { grid-template-columns: 1fr; } }
</style>
