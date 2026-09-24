@extends('layouts.app')

@section('content')
<div class="container-fluid announcements-page py-4">
    <div class="announcements-shell">

        {{-- Header --}}
        <div class="d-flex justify-content-between align-items-center announcements-header">
            <h2 class="fw-bold mb-0 page-title">📢 Announcements</h2>

            @if(auth()->user()->user_class > 8)
                <a href="{{ route('announcements.create') }}" class="btn btn-fileiplay">
                    ➕ New Announcement
                </a>
            @endif
        </div>

        {{-- List --}}
        @forelse($announcements as $announcement)
            <div class="card mb-3 announcement-card {{ $announcement->trashed() ? 'opacity-50' : '' }}"
                 id="announcement-{{ $announcement->id }}">

                <div class="card-body">

                    {{-- Title --}}
                    <h5 class="fw-semibold mb-1 announcement-heading">

                        @if($announcement->trashed())
                            <span class="text-muted announcement-title">
                                {{ $announcement->title }}
                            </span>
                        @else
                            <a href="{{ route('announcements.show', $announcement->id) }}"
                               class="text-decoration-none announcement-title">
                                {{ $announcement->title }}
                            </a>
                        @endif

                        @if($announcement->trashed())
                            <span class="badge deleted-badge ms-2">Deleted</span>
                        @endif

                    </h5>

                    {{-- Meta --}}
                    <p class="announcement-meta mb-2">
                        👤 {{ $announcement->author->name ?? 'System' }} •
                        ⏱ {{ $announcement->created_at->diffForHumans() }}
                    </p>

                    {{-- Bottom row --}}
                    <div class="d-flex justify-content-between align-items-center announcement-footer">

                        <span class="badge type-badge type-{{ $announcement->type }}">
                            {{ ucfirst($announcement->type) }}
                        </span>

                        @if(auth()->user()->user_class > 8)
                            <div class="d-flex gap-2 announcement-actions">

                                @if($announcement->trashed())

                                    {{-- Restore --}}
                                    <button type="button"
                                            class="btn btn-sm action-btn restore-btn"
                                            data-id="{{ $announcement->id }}"
                                            title="Restore">
                                        ♻️
                                    </button>

                                    {{-- Force delete --}}
                                    <form method="POST" action="{{ route('announcements.forceDelete', $announcement->id) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="btn btn-sm action-btn danger-btn"
                                                onclick="return confirm('Permanently delete?')"
                                                title="Permanently delete">
                                            💀
                                        </button>
                                    </form>

                                @else

                                    {{-- Edit --}}
                                    <button type="button"
                                            class="btn btn-sm action-btn edit-btn"
                                            data-id="{{ $announcement->id }}"
                                            data-title="{{ $announcement->title }}"
                                            data-body="{{ $announcement->body }}"
                                            data-type="{{ $announcement->type }}"
                                            data-priority="{{ $announcement->priority }}"
                                            title="Edit">
                                        ✏️
                                    </button>

                                    {{-- Delete --}}
                                    <button type="button"
                                            class="btn btn-sm action-btn delete-btn"
                                            data-id="{{ $announcement->id }}"
                                            title="Delete">
                                        🗑️
                                    </button>

                                @endif

                            </div>
                        @endif

                    </div>
                </div>
            </div>
        @empty
            <div class="text-center empty-announcements">
                <div class="empty-icon">📢</div>
                <h5>No announcements yet</h5>
                <p>Check back later 👀</p>
            </div>
        @endforelse

    </div>
</div>

{{-- EDIT MODAL --}}
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content announcement-modal">

            <div class="modal-header">
                <h5 class="modal-title">Edit Announcement</h5>
                <button type="button"
                        class="btn-close btn-close-white"
                        data-bs-dismiss="modal"
                        aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <input type="hidden" id="edit-id">

                <label for="edit-title" class="form-label">Title</label>
                <input type="text" id="edit-title" class="form-control announcement-input mb-3">

                <label for="edit-body" class="form-label">Body</label>
                <textarea id="edit-body" class="form-control announcement-input mb-3" rows="6"></textarea>

                <label for="edit-type" class="form-label">Type</label>
                <select id="edit-type" class="form-select announcement-input mb-3">
                    <option value="info">Info</option>
                    <option value="success">Success</option>
                    <option value="warning">Warning</option>
                </select>

                <label for="edit-priority" class="form-label">Priority</label>
                <input type="number" id="edit-priority" class="form-control announcement-input mb-2">
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary forum-secondary-btn" data-bs-dismiss="modal">
                    Cancel
                </button>
                <button type="button" class="btn btn-fileiplay" id="saveEdit">
                    Save
                </button>
            </div>

        </div>
    </div>
</div>

{{-- STYLE --}}
<style>
html,
body {
    max-width: 100%;
    overflow-x: hidden !important;
}

.announcements-page {
    width: 100%;
    color: #e7edf5;
}

.announcements-shell {
    width: 100%;
    max-width: 1500px;
    margin: 0 auto;
    padding: 1.5rem;
    background: linear-gradient(135deg, rgba(22, 32, 51, .97), rgba(15, 23, 42, .94));
    border: 1px solid rgba(148, 163, 184, .16);
    border-radius: .75rem;
    box-shadow: 0 14px 38px rgba(0, 0, 0, .24);
    box-sizing: border-box;
}

.announcements-header {
    gap: 1rem;
    padding: .35rem 0 1.15rem;
    margin-bottom: 1.15rem;
    border-bottom: 1px solid rgba(148, 163, 184, .13);
}

.page-title {
    color: #f8fafc;
    font-size: clamp(1.6rem, 2.4vw, 2rem);
    letter-spacing: -.02em;
}

.btn-fileiplay {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: .35rem;
    min-height: 39px;
    padding: .45rem .85rem;
    color: #062a2b;
    background: #42d9d0;
    border: 1px solid #42d9d0;
    border-radius: .5rem;
    font-size: .9rem;
    font-weight: 700;
    transition: all .18s ease;
}

.btn-fileiplay:hover {
    color: #031b1c;
    background: #67e3dc;
    border-color: #67e3dc;
    transform: translateY(-1px);
}

.announcement-card {
    width: 100%;
    overflow: hidden;
    color: #e2e8f0;
    background: linear-gradient(135deg, rgba(30, 41, 59, .84), rgba(15, 23, 42, .82));
    border: 1px solid rgba(148, 163, 184, .14) !important;
    border-radius: .65rem;
    box-shadow: 0 7px 18px rgba(0, 0, 0, .16) !important;
    box-sizing: border-box;
    transition: border-color .18s ease, transform .18s ease, box-shadow .18s ease;
}

.announcement-card:hover {
    border-color: rgba(66, 217, 208, .3) !important;
    transform: translateY(-1px);
    box-shadow: 0 10px 24px rgba(0, 0, 0, .22) !important;
}

.announcement-card .card-body {
    padding: 1rem 1.1rem;
}

.announcement-heading {
    min-width: 0;
    overflow-wrap: anywhere;
}

.announcement-title {
    color: #f1f5f9;
    font-size: 1.1rem;
    line-height: 1.4;
    font-weight: 700;
    overflow-wrap: anywhere;
    word-break: break-word;
}

a.announcement-title:hover {
    color: #42d9d0;
}

.announcement-meta {
    color: #94a3b8 !important;
    font-size: .9rem;
}

.type-info {
    color: #8ee8ff;
    background: rgba(56, 189, 248, .12);
    border: 1px solid rgba(56, 189, 248, .22);
}

.type-success {
    color: #8be9b1;
    background: rgba(34, 197, 94, .12);
    border: 1px solid rgba(34, 197, 94, .22);
}

.type-warning {
    color: #ffd58a;
    background: rgba(245, 158, 11, .12);
    border: 1px solid rgba(245, 158, 11, .22);
}

.deleted-badge {
    color: #fecaca;
    background: rgba(239, 68, 68, .12);
    border: 1px solid rgba(239, 68, 68, .2);
}

.announcement-footer {
    gap: 1rem;
    padding-top: .75rem;
    margin-top: .75rem;
    border-top: 1px solid rgba(148, 163, 184, .09);
}

.announcement-actions form {
    display: inline-flex;
    margin: 0;
}

.action-btn {
    width: 36px;
    height: 36px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex: 0 0 36px;
    padding: 0;
    color: #cbd5e1;
    background: rgba(15, 23, 42, .75);
    border: 1px solid rgba(148, 163, 184, .18);
    border-radius: .5rem;
    font-size: .92rem;
    transition: all .18s ease;
}

.action-btn:hover {
    color: #fff;
    background: rgba(66, 217, 208, .1);
    border-color: rgba(66, 217, 208, .42);
    transform: translateY(-1px);
}

.restore-btn:hover {
    color: #86efac;
    background: rgba(34, 197, 94, .1);
    border-color: rgba(34, 197, 94, .4);
}

.danger-btn:hover,
.delete-btn:hover {
    color: #fca5a5;
    background: rgba(239, 68, 68, .1);
    border-color: rgba(239, 68, 68, .4);
}

.edit-btn:hover {
    color: #fde68a;
    background: rgba(245, 158, 11, .1);
    border-color: rgba(245, 158, 11, .4);
}

.empty-announcements {
    padding: 3rem 1rem;
    color: #94a3b8;
    border: 1px dashed rgba(148, 163, 184, .18);
    border-radius: .65rem;
    background: rgba(15, 23, 42, .35);
}

.empty-announcements h5 {
    color: #e2e8f0;
    font-size: 1rem;
}

.empty-announcements p {
    margin-bottom: 0;
}

.empty-icon {
    margin-bottom: .5rem;
    font-size: 2rem;
}

/* Modal */
.announcement-modal {
    color: #e2e8f0;
    background: #111c2e;
    border: 1px solid rgba(148, 163, 184, .18);
    border-radius: .7rem;
    box-shadow: 0 20px 55px rgba(0, 0, 0, .45);
}

.announcement-modal .modal-header,
.announcement-modal .modal-footer {
    border-color: rgba(148, 163, 184, .13);
}

.announcement-modal .modal-title {
    color: #f8fafc;
    font-size: 1.1rem;
    font-weight: 700;
}

.announcement-modal .form-label {
    color: #cbd5e1;
    font-size: .88rem;
    font-weight: 600;
}

.announcement-input {
    color: #e2e8f0 !important;
    background: #0f172a !important;
    border: 1px solid rgba(148, 163, 184, .2) !important;
    border-radius: .5rem !important;
    font-size: .94rem !important;
}

.announcement-input:focus {
    color: #fff !important;
    border-color: rgba(66, 217, 208, .55) !important;
    box-shadow: 0 0 0 .2rem rgba(66, 217, 208, .08) !important;
}

.announcement-input option {
    color: #e2e8f0;
    background: #0f172a;
}

.forum-secondary-btn {
    border-radius: .5rem;
}

/* Responsive */
@@media (max-width: 767.98px) {
    .announcements-page {
        padding-left: .5rem;
        padding-right: .5rem;
    }

    .announcements-shell {
        padding: 1rem;
        border-radius: .65rem;
    }

    .announcements-header {
        align-items: stretch !important;
        flex-direction: column;
    }

    .btn-fileiplay {
        width: 100%;
    }

    .announcement-card .card-body {
        padding: .9rem;
    }

    .announcement-title {
        font-size: 1.03rem;
    }

    .announcement-meta {
        font-size: .86rem;
    }

    .announcement-footer {
        align-items: flex-start !important;
        flex-direction: column;
    }

    .announcement-actions {
        width: 100%;
    }
}

@@media (max-width: 479.98px) {
    .announcements-page {
        padding-left: .35rem;
        padding-right: .35rem;
    }

    .announcements-shell {
        padding: .8rem;
    }

    .page-title {
        font-size: 1.5rem;
    }

    .announcement-meta {
        line-height: 1.6;
    }

    .action-btn {
        width: 38px;
        height: 38px;
        flex-basis: 38px;
    }
}

@@media (prefers-reduced-motion: reduce) {
    .announcement-card,
    .action-btn,
    .btn-fileiplay,
    .announcement-title {
        transition: none !important;
    }
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
