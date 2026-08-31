@php
    // Allowed category IDs
    $allowedCategoryIds = [1,5,9,11,13,18,20,24,31,54,56,82];
@endphp

@if(in_array($torrent->category_id, $allowedCategoryIds))
    {{-- Upload Subtitle Button --}}
    <div class="mb-3">
        <button class="btn btn-sm btn-secondary"
                data-bs-toggle="modal"
                data-bs-target="#uploadSubtitleModal">
            <i class="bi bi-badge-cc"></i> Upload Subtitle
        </button>
    </div>

    {{-- Upload Subtitle Modal --}}
<div class="modal fade" id="uploadSubtitleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content card-blur">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-badge-cc"></i> Upload Subtitle
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form method="POST"
                  action="{{ route('subtitles.store', $torrent) }}"
                  enctype="multipart/form-data">
                @csrf

                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Subtitle file</label>
                        <input type="file"
                               name="subtitle"
                               class="form-control"
                               required>
                        <small class="text-muted">
                            Allowed: srt, sub, ass, txt, zip, rar
                        </small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Language</label>
                        <select name="language" class="form-select" required>
                            <option value="">Select language</option>
                            <option value="english">English</option>
                            <option value="romanian">Romanian</option>
                            <option value="italian">Italian</option>
                            <option value="french">French</option>
                            <option value="spanish">Spanish</option>
                        </select>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button"
                            class="btn btn-sm btn-secondary"
                            data-bs-dismiss="modal">
                        Cancel
                    </button>

                    <button class="btn btn-sm btn-primary">
                        <i class="bi bi-upload"></i> Upload
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


    {{-- Reopen modal on validation errors --}}
    @if ($errors->any())
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const modal = new bootstrap.Modal(
                    document.getElementById('uploadSubtitleModal')
                );
                modal.show();
            });
        </script>
    @endif
@endif
