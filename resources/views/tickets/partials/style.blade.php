<style>
.support { --support-bg:var(--theme-surface, #0b121d); --support-line:var(--theme-border, rgba(148,163,184,.18)); --support-muted:var(--theme-muted, #a4b3c7); --support-accent:var(--ui-accent,var(--theme-teal-text, #22d3c5)); max-width:1440px; margin:0 auto; padding:24px 16px 40px; color:var(--theme-text, #e6edf7); font-size:var(--site-font-body, 13px); }
.support * { box-sizing:border-box; }
.support a { text-decoration:none; }
.support .support-muted,.support small { color:var(--support-muted); }
.support-header { display:flex; align-items:center; justify-content:space-between; gap:20px; margin-bottom:24px; }
.support-eyebrow { color:var(--support-accent); font-size:var(--site-font-small, 13px); font-weight:700; letter-spacing:.13em; text-transform:uppercase; margin-bottom:8px; }
.support h1 { font-size:clamp(24px,3vw,32px); letter-spacing:-.025em; font-weight:700; margin:0 0 8px; overflow-wrap:anywhere; }
.support h2 { font-size:17px; margin:0; font-weight:650; }
.support h3 { font-size:var(--site-font-body, 13px); font-weight:650; }
.support-header p { margin:0; color:var(--support-muted); }
.support-actions { display:flex; gap:10px; flex-wrap:wrap; align-items:center; }
.support-btn { display:inline-flex; justify-content:center; align-items:center; gap:8px; padding:10px 15px; min-height:42px; background:var(--theme-surface, #111b2a); color:var(--theme-text, #e6edf7); border:1px solid var(--support-line); border-radius:8px; font-size:var(--site-font-body, 13px); font-weight:600; cursor:pointer; }
.support-btn:hover { color:var(--theme-text, #fff); border-color:var(--support-accent); background:var(--theme-surface, #152232); }
.support-btn-primary { background:var(--theme-teal-action, var(--support-accent)); border-color:var(--support-accent); color:var(--theme-on-action, #071c20); }
.support-btn-primary:hover { background:var(--theme-teal-action, #6ee7dc); color:var(--theme-on-action, #071c20); }
.support a:focus-visible,.support button:focus-visible { outline:2px solid var(--support-accent); outline-offset:3px; }
.support-nav { display:flex; gap:6px; flex-wrap:wrap; border-bottom:1px solid var(--support-line); padding-bottom:14px; margin-bottom:22px; }
.support-nav a { padding:9px 14px; border-radius:7px; color:var(--support-muted); font-weight:600; }
.support-nav a:hover,.support-nav a[aria-current="page"] { color:var(--support-accent); background:var(--theme-teal-soft, rgba(34,211,197,.09)); }
.support-stats { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:14px; margin-bottom:22px; }
.support-stat { padding:18px 20px; background:var(--support-bg); border:1px solid var(--support-line); border-radius:10px; color:var(--theme-text, #e6edf7); }
.support-stat strong { display:block; font-size:28px; font-weight:700; margin-top:8px; line-height:1.2; }
.support-stat span { color:var(--support-muted); font-size:var(--site-font-small, 13px); }
.support-panel { background:var(--support-bg); border:1px solid var(--support-line); border-radius:12px; overflow:hidden; margin-bottom:20px; }
.support-panel-head { padding:18px 22px; border-bottom:1px solid var(--support-line); display:flex; align-items:center; justify-content:space-between; gap:14px; }
.support-panel-body { padding:22px; }
.support-filters { display:grid; grid-template-columns:2fr repeat(3,minmax(120px,1fr)); gap:14px; }
.support label { display:block; font-size:var(--site-font-small, 13px); font-weight:600; color:var(--theme-text, #cbd5e1); margin-bottom:7px; }
.support .form-control,.support .form-select { background-color:var(--theme-control, #080e18); border:1px solid var(--theme-border, #35445a); border-radius:7px; color:var(--theme-text, #e6edf7); font-size:var(--site-font-body, 13px); min-height:42px; }
.support .form-control::placeholder { color:var(--theme-muted, #8292a9); }
.support .form-control:focus,.support .form-select:focus { border-color:var(--support-accent); box-shadow:0 0 0 3px var(--theme-shadow, rgba(34,211,197,.12)); }
.support textarea.form-control { min-height:150px; line-height:1.7; resize:vertical; }
.support .form-select option { background:var(--theme-control, #0b121d); }
.support-help { font-size:var(--site-font-small, 13px); color:var(--support-muted); margin:7px 0 0; line-height:1.6; }
.support-filter-footer { display:flex; gap:12px; align-items:end; flex-wrap:wrap; margin-top:16px; }
.support-filter-footer > div { max-width:190px; }
.support-table { width:100%; border-collapse:collapse; }
.support-table th { padding:12px 20px; color:var(--support-muted); text-transform:uppercase; font-size:var(--site-font-small, 13px); letter-spacing:.07em; background:var(--theme-surface-alt, rgba(0,0,0,.12)); font-weight:600; white-space:nowrap; }
.support-table td { padding:18px 20px; border-top:1px solid var(--support-line); vertical-align:middle; }
.support-table tbody tr:hover { background:var(--theme-surface-alt, rgba(148,163,184,0.0245)); }
.support-subject { color:var(--theme-text, #edf5ff); font-weight:600; display:block; margin:5px 0; overflow-wrap:anywhere; }
.support-subject:hover { color:var(--support-accent); }
.support-ticket-id { font-size:var(--site-font-small, 13px); color:var(--support-muted); font-variant-numeric:tabular-nums; }
.support-badge { display:inline-flex; align-items:center; gap:6px; padding:5px 9px; border-radius:5px; background:var(--theme-surface-alt, rgba(148,163,184,0.07)); color:var(--theme-text, #cbd5e1); font-size:var(--site-font-small, 13px); font-weight:600; white-space:nowrap; }
.support-badge::before { content:''; width:5px; height:5px; border-radius:50%; background:currentColor; }
.support-badge[data-value="Open"],.support-badge[data-value="Waiting User"] { color:var(--theme-blue-text, #93c5fd); background:var(--theme-blue-soft, rgba(59,130,246,.12)); }
.support-badge[data-value="Waiting Staff"],.support-badge[data-value="High"] { color:var(--theme-amber-text, #fcd978); background:var(--theme-amber-soft, rgba(245,158,11,.11)); }
.support-badge[data-value="Resolved"],.support-badge[data-value="Low"] { color:var(--theme-teal-text, #79e3bc); background:var(--theme-teal-soft, rgba(16,185,129,.1)); }
.support-badge[data-value="Critical"] { color:var(--theme-red-text, #fda4af); background:var(--theme-red-soft, rgba(244,63,94,.12)); }
.support-empty { padding:56px 24px; text-align:center; }
.support-empty > i { display:block; font-size:32px; color:var(--support-accent); margin-bottom:16px; }
.support-empty p { color:var(--support-muted); }
.support-pagination { padding:16px 20px; border-top:1px solid var(--support-line); }
.support-pagination .pagination { margin-bottom:0; }
.support-grid { display:grid; grid-template-columns:minmax(0,1fr) 320px; gap:22px; align-items:start; }
.support-form-grid { display:grid; grid-template-columns:1fr 1fr; gap:18px; }
.support-field { margin-bottom:22px; }
.support-guidance { padding-left:20px; color:var(--support-muted); line-height:1.8; }
.support-guidance li { margin-bottom:12px; }
.support-upload { border:1px dashed var(--theme-border, #4a5a70); border-radius:8px; padding:16px; background:var(--theme-surface-alt, rgba(0,0,0,.1)); }
.support-notice { border:1px solid var(--theme-teal-border, rgba(34,211,197,.25)); background:var(--theme-teal-soft, rgba(34,211,197,.07)); color: var(--theme-text, #b7eee6); padding:14px 18px; border-radius:8px; margin-bottom:20px; }
.support-notice-error { color:var(--theme-text, #fecdd3); background:var(--theme-red-soft, rgba(244,63,94,.08)); border-color:var(--theme-red-border, rgba(244,63,94,.25)); }
.support-notice ul { margin-bottom:0; padding-left:20px; }
.support-details { margin:0; }
.support-details > div { display:flex; justify-content:space-between; align-items:start; gap:15px; padding:12px 0; border-bottom:1px solid var(--support-line); }
.support-details > div:first-child { padding-top:0; }
.support-details > div:last-child { border:0; padding-bottom:0; }
.support-details dt { color:var(--support-muted); font-weight:400; font-size:var(--site-font-small, 13px); }
.support-details dd { margin:0; text-align:right; overflow-wrap:anywhere; }
.support-message { padding:24px; border-bottom:1px solid var(--support-line); }
.support-message:last-child { border-bottom:0; }
.support-message.is-note { background:var(--theme-amber-soft, rgba(245,158,11,.055)); border-left:3px solid var(--theme-amber-border, #e5b45a); }
.support-message-head { display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px; margin-bottom:14px; }
.support-person { display:flex; align-items:center; gap:10px; }
.support-avatar { width:34px; height:34px; border-radius:9px; display:grid; place-items:center; color:var(--support-accent); background:var(--theme-teal-soft, rgba(34,211,197,.09)); font-weight:700; }
.support-message-body { white-space:pre-wrap; overflow-wrap:anywhere; line-height:1.8; color:var(--theme-text, #d4dfec); }
.support-attachments { display:flex; gap:8px; flex-wrap:wrap; margin-top:15px; }
.support-attachment { display:inline-flex; align-items:center; gap:7px; color:var(--theme-text, #b6c9df); border:1px solid var(--support-line); padding:7px 10px; border-radius:6px; max-width:100%; overflow-wrap:anywhere; font-size:var(--site-font-small, 13px); }
.support-attachment:hover { color:var(--support-accent); }
.support-note-toggle { display:flex!important; align-items:center; gap:9px; font-weight:400!important; }
.support-note-toggle input { accent-color:var(--theme-amber-text, #e5b45a); width:16px; height:16px; }
.support-activity { list-style:none; padding:0; margin:0; }
.support-activity li { border-left:1px solid var(--theme-border, #35445a); padding:0 0 20px 16px; line-height:1.6; font-size:var(--site-font-small, 13px); }
.support-activity li:last-child { padding-bottom:0; }
.support-activity time { display:block; color:var(--support-muted); margin-top:3px; font-size:var(--site-font-small, 13px); }
@media(max-width:1000px) { .support-grid { grid-template-columns:minmax(0,1fr); } .support-filters { grid-template-columns:repeat(2,minmax(0,1fr)); } }
@media(max-width:600px) { .support { padding:18px 8px 30px; } .support-header { align-items:flex-start; flex-direction:column; } .support-stats { grid-template-columns:repeat(2,minmax(0,1fr)); gap:10px; } .support-stat { padding:14px; } .support-filters,.support-form-grid { grid-template-columns:1fr; } .support-panel-body,.support-message { padding:18px; } .support-table th,.support-table td { padding:12px; } .support-secondary { display:none; } .support-panel-head { padding:16px; } }
</style>
