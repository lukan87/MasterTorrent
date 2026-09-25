<div class="seedbox-server-list">
    @foreach($boxes as $box)
        <article class="seedbox-server">
            <div class="seedbox-server-icon" aria-hidden="true"><i class="bi bi-hdd-network"></i></div>
            <div class="seedbox-server-details">
                <a href="{{ route('seedboxes.torrents', $box) }}" class="seedbox-server-name">{{ $box->name }}</a>
                <div class="seedbox-server-address">{{ $box->address }}</div>
                <div class="d-flex gap-2 flex-wrap mt-2 align-items-center">
                    <span class="badge bg-secondary">{{ ucfirst($box->auth_type) }} auth</span>
                    @if($box->user_id == auth()->id())
                        <span class="badge bg-info text-dark">Your server</span>
                    @else
                        <span class="small">Owner: {{ $box->user?->name ?? 'Unknown' }}</span>
                    @endif
                    <span id="status-{{ $box->id }}" class="seedbox-status small" role="status" aria-live="polite">Not checked</span>
                </div>
            </div>
            <div class="seedbox-server-actions">
                <a href="{{ route('seedboxes.torrents', $box) }}" class="btn btn-sm btn-primary">Open torrents</a>
                <button type="button" class="btn btn-sm btn-outline-info seedbox-test" data-url="{{ route('seedboxes.test', $box) }}" data-status="status-{{ $box->id }}">Test connection</button>
                <a href="{{ route('seedboxes.edit', $box) }}" class="btn btn-sm btn-warning" aria-label="Edit {{ $box->name }}"><i class="bi bi-pencil-square"></i></a>
                <form action="{{ route('seedboxes.destroy', $box) }}" method="POST" onsubmit="return confirm('Remove this saved connection? Torrents and remote files will remain on the seedbox.');">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-sm btn-danger" aria-label="Remove {{ $box->name }}"><i class="bi bi-trash"></i></button>
                </form>
            </div>
        </article>
    @endforeach
</div>
@once
<style>
.seedbox-server { display:flex; align-items:center; gap:1rem; padding:1rem; border:1px solid rgba(255,255,255,.09); border-radius:.7rem; background:rgba(255,255,255,.025); }
.seedbox-server + .seedbox-server { margin-top:.65rem; }
.seedbox-server-icon { display:grid; place-items:center; width:44px; height:44px; flex-shrink:0; border-radius:.65rem; background:rgba(34,211,238,.09); color:#67e8f9; font-size:1.35rem; }
.seedbox-server-details { flex:1; min-width:0; }
.seedbox-server-name { color:#e2e8f0; font-weight:700; text-decoration:none; overflow-wrap:anywhere; }
.seedbox-server-name:hover { color:#67e8f9; }
.seedbox-server-address { color:#94a3b8; font-size:.8rem; margin-top:.25rem; overflow-wrap:anywhere; }
.seedbox-server-actions { display:flex; align-items:center; flex-wrap:wrap; gap:.4rem; }
.seedbox-server-actions form { margin:0; }
.seedbox-server :focus-visible { outline:2px solid #67e8f9; outline-offset:3px; }
@media(max-width:767.98px) { .seedbox-server { flex-wrap:wrap; padding:.8rem; } .seedbox-server-actions { width:100%; } .seedbox-server-actions > a:first-child { flex:1; } }
</style>
<script>
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.seedbox-test').forEach(button => {
        button.addEventListener('click', async () => {
            const status = document.getElementById(button.dataset.status);
            button.disabled = true;
            status.textContent = 'Checking…';
            status.className = 'seedbox-status small text-warning';
            const controller = new AbortController();
            const timeout = setTimeout(() => controller.abort(), 35000);
            try {
                const response = await fetch(button.dataset.url, {headers: {'Accept': 'application/json'}, signal: controller.signal});
                if (!response.ok) throw new Error('Connection check failed. Please try again.');
                const data = await response.json();
                status.textContent = data.success ? 'Connected' : 'Connection failed';
                status.title = data.message || '';
                status.className = 'seedbox-status small ' + (data.success ? 'text-success' : 'text-danger');
            } catch (error) {
                status.textContent = error.name === 'AbortError' ? 'Connection timed out' : 'Unable to check connection';
                status.className = 'seedbox-status small text-danger';
            } finally {
                clearTimeout(timeout);
                button.disabled = false;
            }
        });
    });
});
</script>
@endonce
