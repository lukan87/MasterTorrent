@extends('layouts.app')

@section('content')
<div data-page-browser data-browse-url="{{ url()->current() }}">
@include('profile.torrents-results')
</div>
<script>
function initializeTorrentModeration() {
    const modalElement = document.getElementById('deleteTorrentModal');
    const form = document.getElementById('deleteTorrentForm');
    const nameField = document.getElementById('torrentName');
    const reasonSelect = document.getElementById('reasonSelect');
    const customReasonBox = document.getElementById('customReasonBox');

    if (!modalElement || !form || modalElement.dataset.initialized) {
        return;
    }

    modalElement.dataset.initialized = '1';
    modalElement.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;

        if (!button) {
            return;
        }

        const torrentId = button.getAttribute('data-id');
        const torrentName = button.getAttribute('data-name');

        if (nameField) {
            nameField.textContent = torrentName || 'this torrent';
        }

        if (torrentId) {
            form.action = '/admin/torrents/' + encodeURIComponent(torrentId) + '/destroy';
        }
    });

    if (reasonSelect && customReasonBox) {
        reasonSelect.addEventListener('change', function () {
            customReasonBox.classList.toggle('d-none', this.value !== 'custom');
        });
    }
}
document.addEventListener('DOMContentLoaded', initializeTorrentModeration);
document.addEventListener('page:updated', initializeTorrentModeration);
</script>


@vite('resources/js/page-browser.js')
@endsection
