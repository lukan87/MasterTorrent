@extends('layouts.app')

@section('content')

<div class="container mt-4 create-ticket-page">

    <div class="ticket-create-card">

        <div class="ticket-create-header">
            <div class="ticket-header-icon">
                <i class="bi bi-life-preserver"></i>
            </div>

            <div>
                <h4>Create Support Ticket</h4>
                <p>Submit a support request and provide as much detail as possible.</p>
            </div>
        </div>

        <div class="ticket-create-body">

            <form method="POST" action="{{ route('tickets.store') }}" enctype="multipart/form-data">
                @csrf

                <div class="mb-3">
                    <label class="form-label">Category</label>

                    <select name="category_id" class="form-select" required>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Priority</label>

                    <select name="priority" class="form-select">
                        <option value="Low">Low</option>
                        <option value="Medium">Medium</option>
                        <option value="High">High</option>
                        <option value="Critical">Critical</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Title</label>

                    <input
                        type="text"
                        name="title"
                        class="form-control"
                        required
                    >
                </div>

                @if($torrent)

                    <div class="mb-3">
                        <label class="form-label">Reporting Torrent</label>

                        <div class="linked-torrent-box">
                            <i class="bi bi-file-earmark-play"></i>

                            <div class="linked-torrent-name">
                                {{ $torrent->name }}
                            </div>
                        </div>

                        <input
                            type="hidden"
                            name="linked_torrent_id"
                            value="{{ $torrent->id }}"
                        >

                        <input
                            type="hidden"
                            name="category_id"
                            value="4"
                        >
                    </div>

                @endif

                <div class="mb-3">
                    <label class="form-label">Description</label>

                    <textarea
                        name="description"
                        rows="6"
                        class="form-control"
                        required
                    ></textarea>

                    <div class="field-help">
                        Please describe the issue clearly, including any relevant details.
                    </div>
                </div>

                <div class="mb-3">

                    <label class="form-label">Attachment</label>

                    <div
                        id="drop-area"
                        class="ticket-drop-area"
                        style="cursor:pointer;"
                    >
                        <div class="drop-icon">
                            <i class="bi bi-cloud-arrow-up"></i>
                        </div>

                        <div class="drop-title">
                            Drag &amp; drop files here
                        </div>

                        <div class="drop-subtitle">
                            or click to select files
                        </div>

                        <input
                            type="file"
                            name="attachment[]"
                            id="attachment"
                            multiple
                            class="d-none"
                        >

                        <div id="file-name" class="selected-files"></div>
                    </div>

                    <small class="field-help">
                        Optional — screenshots, logs, etc.
                    </small>

                </div>

                <div class="ticket-submit-row">
                    <button type="submit" class="ticket-submit-btn">
                        <i class="bi bi-send"></i>
                        Submit Ticket
                    </button>
                </div>

            </form>

        </div>

    </div>

</div>

<style>
/* =========================================
   FILEIPLAY CREATE SUPPORT TICKET
   Dark glass + teal forum style
========================================= */

.create-ticket-page {
    max-width: 1000px;
}

.ticket-create-card {
    position: relative;
    overflow: hidden;
    background: linear-gradient(
        135deg,
        rgba(22,32,51,.95),
        rgba(15,23,42,.84)
    );
    border: 1px solid var(--ui-border, rgba(148,163,184,.16));
    border-radius: .85rem;
    box-shadow: 0 10px 28px rgba(0,0,0,.22);
}

.ticket-create-card::before {
    content: "";
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 3px;
    background: var(--ui-accent, #22d3c5);
}

.ticket-create-header {
    display: flex;
    align-items: center;
    gap: .75rem;
    padding: .9rem 1rem;
    background: rgba(15,23,42,.4);
    border-bottom: 1px solid var(--ui-border, rgba(148,163,184,.16));
}

.ticket-header-icon {
    width: 38px;
    height: 38px;
    flex: 0 0 38px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--ui-accent, #22d3c5);
    background: rgba(34,211,197,.07);
    border: 1px solid rgba(34,211,197,.17);
    border-radius: .6rem;
    font-size: 17px;
}

.ticket-create-header h4 {
    margin: 0;
    color: #f1f5f9;
    font-size: 14px;
    font-weight: 700;
}

.ticket-create-header p {
    margin: .2rem 0 0;
    color: #64748b;
    font-size: 12px;
}

.ticket-create-body {
    padding: 1rem;
}

.ticket-create-body .form-label {
    display: block;
    margin-bottom: .35rem;
    color: #cbd5e1;
    font-size: 13px;
    font-weight: 600;
}

.ticket-create-body .form-control,
.ticket-create-body .form-select {
    min-height: 38px;
    color: #dbeafe;
    background-color: rgba(15,23,42,.72);
    border: 1px solid rgba(148,163,184,.18);
    border-radius: .5rem;
    font-size: 13px;
    box-shadow: none;
}

.ticket-create-body textarea.form-control {
    min-height: 135px;
    resize: vertical;
}

.ticket-create-body .form-control:focus,
.ticket-create-body .form-select:focus {
    color: #f1f5f9;
    background-color: rgba(15,23,42,.9);
    border-color: rgba(34,211,197,.45);
    box-shadow: 0 0 0 .15rem rgba(34,211,197,.08);
}

.ticket-create-body .form-control::placeholder {
    color: #64748b;
}

.ticket-create-body .form-select option {
    background: #111827;
    color: #e2e8f0;
}

.field-help {
    display: block;
    margin-top: .35rem;
    color: #64748b;
    font-size: 11px;
}

/* LINKED TORRENT */

.linked-torrent-box {
    display: flex;
    align-items: center;
    gap: .55rem;
    min-height: 38px;
    padding: .55rem .7rem;
    color: #cbd5e1;
    background: rgba(15,23,42,.62);
    border: 1px solid rgba(34,211,197,.16);
    border-left: 3px solid var(--ui-accent, #22d3c5);
    border-radius: .5rem;
    font-size: 13px;
}

.linked-torrent-box i {
    flex: 0 0 auto;
    color: var(--ui-accent, #22d3c5);
}

.linked-torrent-name {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

/* DROP AREA */

.ticket-drop-area {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    min-height: 135px;
    padding: 1rem;
    text-align: center;
    background: rgba(15,23,42,.48);
    border: 1px dashed rgba(148,163,184,.25);
    border-radius: .65rem;
    transition:
        background .18s ease,
        border-color .18s ease,
        transform .18s ease;
}

.ticket-drop-area:hover,
.ticket-drop-area.drag-active {
    background: rgba(34,211,197,.045);
    border-color: rgba(34,211,197,.4);
}

.ticket-drop-area.drag-active {
    transform: translateY(-1px);
}

.drop-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 38px;
    height: 38px;
    margin-bottom: .5rem;
    color: var(--ui-accent, #22d3c5);
    background: rgba(34,211,197,.07);
    border: 1px solid rgba(34,211,197,.16);
    border-radius: .6rem;
    font-size: 18px;
}

.drop-title {
    color: #cbd5e1;
    font-size: 13px;
    font-weight: 600;
}

.drop-subtitle {
    margin-top: .15rem;
    color: #64748b;
    font-size: 11px;
}

.selected-files {
    max-width: 100%;
    margin-top: .55rem;
    color: var(--ui-accent, #22d3c5);
    font-size: 11px;
    line-height: 1.5;
    overflow-wrap: anywhere;
}

/* BUTTON */

.ticket-submit-row {
    display: flex;
    justify-content: flex-end;
    padding-top: .25rem;
}

.ticket-submit-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: .4rem;
    min-height: 37px;
    padding: .45rem .85rem;
    color: #071315;
    background: var(--ui-accent, #22d3c5);
    border: 1px solid var(--ui-accent, #22d3c5);
    border-radius: .5rem;
    font-size: 13px;
    font-weight: 700;
    transition:
        background .15s ease,
        border-color .15s ease,
        transform .15s ease;
}

.ticket-submit-btn:hover {
    color: #071315;
    background: var(--ui-accent-strong, #14b8a6);
    border-color: var(--ui-accent-strong, #14b8a6);
    transform: translateY(-1px);
}

.ticket-submit-btn:focus {
    box-shadow: 0 0 0 .15rem rgba(34,211,197,.12);
}

/* MOBILE */

@media (max-width: 576px) {
    .create-ticket-page {
        margin-top: 1rem !important;
        padding-left: .5rem;
        padding-right: .5rem;
    }

    .ticket-create-header {
        padding: .75rem;
    }

    .ticket-create-body {
        padding: .75rem;
    }

    .ticket-create-header p {
        display: none;
    }

    .ticket-create-body textarea.form-control {
        min-height: 120px;
    }

    .ticket-submit-row {
        justify-content: stretch;
    }

    .ticket-submit-btn {
        width: 100%;
    }
}
</style>

<script>
const dropArea = document.getElementById('drop-area');
const fileInput = document.getElementById('attachment');
const fileName = document.getElementById('file-name');

if (dropArea && fileInput && fileName) {

    dropArea.addEventListener('click', () => {
        fileInput.click();
    });

    fileInput.addEventListener('change', () => {

        if (fileInput.files.length) {

            let names = [];

            for (let i = 0; i < fileInput.files.length; i++) {
                names.push(fileInput.files[i].name);
            }

            fileName.textContent = names.join(', ');
        } else {
            fileName.textContent = '';
        }
    });

    dropArea.addEventListener('dragover', (e) => {
        e.preventDefault();
        dropArea.classList.add('drag-active');
    });

    dropArea.addEventListener('dragleave', () => {
        dropArea.classList.remove('drag-active');
    });

    dropArea.addEventListener('drop', (e) => {

        e.preventDefault();

        dropArea.classList.remove('drag-active');

        if (e.dataTransfer.files.length) {

            fileInput.files = e.dataTransfer.files;

            let names = [];

            for (let i = 0; i < e.dataTransfer.files.length; i++) {
                names.push(e.dataTransfer.files[i].name);
            }

            fileName.textContent = names.join(', ');
        }
    });
}
</script>

@endsection
