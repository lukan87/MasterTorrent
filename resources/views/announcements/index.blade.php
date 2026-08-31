@extends('layouts.app')

@section('content')
<div class="container glass mb-3 mt-5">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mt-5 mb-4">
        <h2 class="fw-bold mb-0">📢 Announcements</h2>

        @if(auth()->user()->user_class > 8)
            <a href="{{ route('announcements.create') }}" class="btn btn-primary btn-sm px-3">
                ➕ New Announcement
            </a>
        @endif
    </div>

    {{-- List --}}
    @forelse($announcements as $announcement)
        <div class="card mb-3 shadow-sm border-0 announcement-card {{ $announcement->trashed() ? 'opacity-50' : '' }}"
             id="announcement-{{ $announcement->id }}">

            <div class="card-body">

                {{-- Title --}}
                <h5 class="fw-semibold mb-1">

    @if($announcement->trashed())
        <span class="text-muted">
            {{ $announcement->title }}
        </span>
    @else
        <a href="{{ route('announcements.show', $announcement->id) }}"
           class="text-decoration-none announcement-title">
            {{ $announcement->title }}
        </a>
    @endif

    @if($announcement->trashed())
        <span class="badge bg-danger ms-2">Deleted</span>
    @endif

</h5>

                {{-- Meta --}}
                <p class="text-muted small mb-2">
                    👤 {{ $announcement->author->name ?? 'System' }} •
                    ⏱ {{ $announcement->created_at->diffForHumans() }}
                </p>

                {{-- Bottom row --}}
                <div class="d-flex justify-content-between align-items-center mt-2">

                    <span class="badge bg-{{ $announcement->type }}">
                        {{ ucfirst($announcement->type) }}
                    </span>

                    @if(auth()->user()->user_class > 8)
                        <div class="d-flex gap-2">

                            @if($announcement->trashed())

                                {{-- Restore --}}
                                <button class="btn btn-sm btn-success restore-btn"
                                        data-id="{{ $announcement->id }}">
                                    ♻️
                                </button>

                                {{-- Force delete --}}
                                <form method="POST" action="{{ route('announcements.forceDelete', $announcement->id) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger"
                                            onclick="return confirm('Permanently delete?')">
                                        💀
                                    </button>
                                </form>

                            @else

                                {{-- Edit --}}
                                <button 
                                    class="btn btn-sm btn-warning edit-btn"
                                    data-id="{{ $announcement->id }}"
                                    data-title="{{ $announcement->title }}"
                                    data-body="{{ $announcement->body }}"
                                    data-type="{{ $announcement->type }}"
                                    data-priority="{{ $announcement->priority }}">
                                    ✏️
                                </button>

                                {{-- Delete --}}
                                <button 
                                    class="btn btn-sm btn-outline-danger delete-btn"
                                    data-id="{{ $announcement->id }}">
                                    🗑️
                                </button>

                            @endif

                        </div>
                    @endif

                </div>
            </div>
        </div>
    @empty
        <div class="text-center text-muted mt-5">
            <h5>No announcements yet</h5>
            <p>Check back later 👀</p>
        </div>
    @endforelse

</div>

{{-- EDIT MODAL --}}
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Edit Announcement</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <input type="hidden" id="edit-id">

                <input type="text" id="edit-title" class="form-control mb-2">

                <textarea id="edit-body" class="form-control mb-2" rows="6"></textarea>

                <select id="edit-type" class="form-control mb-2">
                    <option value="info">Info</option>
                    <option value="success">Success</option>
                    <option value="warning">Warning</option>
                </select>

                <input type="number" id="edit-priority" class="form-control mb-2">
            </div>

            <div class="modal-footer">
                <button class="btn btn-primary" id="saveEdit">Save</button>
            </div>

        </div>
    </div>
</div>

{{-- STYLE --}}
<style>
.announcement-card {
    border-radius: 12px;
    transition: all 0.2s ease;
}
.announcement-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.08);
}
.announcement-title:hover {
    color: #0d6efd;
}
.badge {
    font-size: 0.75rem;
    padding: 6px 10px;
    border-radius: 8px;
}
.d-flex.gap-2 form {
    display: inline;
}
</style>

{{-- SCRIPT --}}
<script>
document.addEventListener('DOMContentLoaded', function () {

    const modal = new bootstrap.Modal(document.getElementById('editModal'));

    const editId = document.getElementById('edit-id');
    const editTitle = document.getElementById('edit-title');
    const editBody = document.getElementById('edit-body');
    const editType = document.getElementById('edit-type');
    const editPriority = document.getElementById('edit-priority');

    // EDIT
    document.querySelectorAll('.edit-btn').forEach(btn => {
        btn.onclick = () => {
            editId.value = btn.dataset.id;
            editTitle.value = btn.dataset.title;
            editBody.value = btn.dataset.body;
            editType.value = btn.dataset.type;
            editPriority.value = btn.dataset.priority;
            modal.show();
        };
    });

    // SAVE EDIT
    document.getElementById('saveEdit').onclick = () => {
        let id = editId.value;

        fetch(`/announcements/${id}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                title: editTitle.value,
                body: editBody.value,
                type: editType.value,
                priority: editPriority.value
            })
        })
        .then(res => res.json())
        .then(() => location.reload());
    };

    // DELETE
document.querySelectorAll('.delete-btn').forEach(btn => {
    btn.onclick = () => {

        let id = btn.dataset.id;

        console.log('DELETE CLICKED:', id);

        if (!confirm('Delete this announcement?')) return;

        fetch(`/announcements/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            console.log('STATUS:', response.status);

            if (!response.ok) {
                throw new Error('Request failed with status ' + response.status);
            }

            return response.json();
        })
        .then(data => {
            console.log('DATA:', data);

            if (data.success) {
                let card = document.getElementById(`announcement-${id}`);

                if (!card) {
                    console.error('Card not found!');
                    return;
                }

                card.style.transition = '0.3s';
                card.style.opacity = '0';
                card.style.transform = 'scale(0.95)';

                setTimeout(() => card.remove(), 300);
            }
        })
        .catch(error => {
            console.error('DELETE ERROR:', error);
            alert('Delete failed. Check console.');
        });
    };
});

    // RESTORE
    document.querySelectorAll('.restore-btn').forEach(btn => {
        btn.onclick = () => {
            let id = btn.dataset.id;

            fetch(`/announcements/${id}/restore`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(res => res.json())
            .then(() => location.reload());
        };
    });

});
</script>

@endsection