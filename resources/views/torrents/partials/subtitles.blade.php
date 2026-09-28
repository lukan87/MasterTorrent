@if(in_array($torrent->category_id, $allowedCategoryIds))

<div class="modal fade"
     id="{{ $uploadSubtitleModalId }}"
     tabindex="-1"
     aria-labelledby="{{ $uploadSubtitleModalId }}Label"
     aria-hidden="true">

    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content subtitle-modal-content">

            <div class="modal-header">

                <h5 class="modal-title"
                    id="{{ $uploadSubtitleModalId }}Label">

                    <i class="bi bi-badge-cc-fill"></i>
                    Upload Subtitle

                </h5>

                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Close">
                </button>

            </div>

            <form method="POST"
                  action="{{ route('subtitles.store', $torrent) }}"
                  enctype="multipart/form-data">

                @csrf

                <div class="modal-body">

                    @if ($errors->any())

                        <div class="alert alert-danger py-2">

                            <ul class="mb-0 ps-3">

                                @foreach ($errors->all() as $error)

                                    <li>{{ $error }}</li>

                                @endforeach

                            </ul>

                        </div>

                    @endif


                    {{-- SUBTITLE FILE --}}
                    <div class="mb-3">

                        <label for="subtitle-{{ $torrent->id }}"
                               class="form-label">

                            Subtitle file

                        </label>

                        <input type="file"
                               id="subtitle-{{ $torrent->id }}"
                               name="subtitle"
                               class="form-control"
                               accept=".srt,.sub,.ass,.txt,.zip,.rar"
                               required>

                        <small class="subtitle-help">
                            Allowed: srt, sub, ass, txt, zip, rar
                        </small>

                    </div>


                    {{-- LANGUAGE --}}
                    <div>

                        <label for="subtitle-language-{{ $torrent->id }}"
                               class="form-label">

                            Language

                        </label>

                        <select id="subtitle-language-{{ $torrent->id }}"
                                name="language"
                                class="form-select"
                                required>

                            <option value="">
                                Select language
                            </option>

                            <option value="english"
                                {{ old('language') === 'english' ? 'selected' : '' }}>
                                English
                            </option>

                            <option value="romanian"
                                {{ old('language') === 'romanian' ? 'selected' : '' }}>
                                Romanian
                            </option>

                            <option value="italian"
                                {{ old('language') === 'italian' ? 'selected' : '' }}>
                                Italian
                            </option>

                            <option value="french"
                                {{ old('language') === 'french' ? 'selected' : '' }}>
                                French
                            </option>

                            <option value="spanish"
                                {{ old('language') === 'spanish' ? 'selected' : '' }}>
                                Spanish
                            </option>

                        </select>

                    </div>

                </div>


                <div class="modal-footer">

                    <button type="button"
                            class="btn btn-sm subtitle-cancel-btn"
                            data-bs-dismiss="modal">

                        Cancel

                    </button>

                    <button type="submit"
                            class="btn btn-sm subtitle-submit-btn">

                        <i class="bi bi-upload me-1"></i>
                        Upload

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>


@if($errors->any())

<script>
document.addEventListener('DOMContentLoaded', function () {

    const modalElement =
        document.getElementById(@json($uploadSubtitleModalId));

    if (
        modalElement &&
        typeof bootstrap !== 'undefined' &&
        bootstrap.Modal
    ) {

        const subtitleModal =
            bootstrap.Modal.getOrCreateInstance(modalElement);

        subtitleModal.show();
    }

});
</script>

@endif

@endif