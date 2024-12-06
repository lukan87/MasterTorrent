@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 class="my-5 text-center text-primary">Generate RSS Feed</h1>

        <!-- Form to select categories and enter passkey -->
        <form action="{{ url('/rss/feed') }}" method="get" id="rssForm">
            <div class="card p-4 mb-4 shadow-lg">
                <div class="form-group">


                    <!-- Buttons for Select All and Unselect All -->
                    <div class="mb-3">
                        <button type="button" id="selectAll" class="btn btn-sm btn-outline-primary me-2">Select All</button>
                        <button type="button" id="unselectAll" class="btn btn-sm btn-outline-danger">Unselect All</button>
                    </div>

                    <!-- Categories displayed in 3 columns -->
                    <div id="categories" class="row">
                        @foreach ($categories as $category)
                            <div class="col-md-4 mb-3">
                                <div class="form-check">
                                    <input
                                        type="checkbox"
                                        value="{{ $category->id }}"
                                        id="category_{{ $category->id }}"
                                        class="form-check-input category-checkbox"
                                        @if(in_array($category->id, old('cats', session('categories', [])))) checked @endif
                                    >
                                    <label for="category_{{ $category->id }}" class="form-check-label">
                                        {{ $category->name }}
                                    </label>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    @error('cats')
                        <div class="text-danger mt-2">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Display generated RSS link -->
                <div class="form-group mt-4">
                    <label for="generatedLink" class="font-weight-bold text-success">Generated RSS Link</label>
                    <div id="generatedLinkContainer" class="p-3 bg-light border rounded d-none">
                        <a id="generatedLink" href="#" class="text-break text-primary font-weight-bold" target="_blank"></a>
                        <button type="button" id="copyLink" class="btn btn-sm btn-outline-secondary mt-2">Copy Link</button>
                    </div>
                </div>

                <div class="form-group mt-3">
                    <input type="hidden" id="passkey" value="{{ old('passkey', session('passkey', $passkey)) }}" required>
                </div>

                <div class="text-center">
                    <button type="button" id="generateRss" class="btn btn-primary btn-lg w-75">Generate Feed</button>
                </div>
            </div>
        </form>
    </div>

    <!-- Script to handle Select All, Unselect All, and URL generation -->
    <script>
        document.getElementById('generateRss').addEventListener('click', function () {
            const baseUrl = "{{ url('/rss/feed') }}";
            const passkey = document.getElementById('passkey').value;

            // Get all selected categories
            const selected = Array.from(document.querySelectorAll('.category-checkbox:checked'))
                .map(checkbox => checkbox.value);

            if (selected.length === 0) {
                alert('Please select at least one category!');
                return;
            }

            // Build URL with explicit commas
            const url = `${baseUrl}?cats=${selected.join(',')}&passkey=${passkey}`;

            // Display the generated URL
            const linkContainer = document.getElementById('generatedLinkContainer');
            const linkElement = document.getElementById('generatedLink');
            linkElement.href = url;
            linkElement.textContent = url;

            // Show the container with the link
            linkContainer.classList.remove('d-none');
        });

        // Copy link to clipboard
        document.getElementById('copyLink').addEventListener('click', function () {
            const linkElement = document.getElementById('generatedLink');

            // Copy link text to clipboard
            if (navigator.clipboard) {
                navigator.clipboard.writeText(linkElement.textContent).then(() => {
                    alert('Link copied to clipboard!');
                }).catch(err => {
                    console.error('Failed to copy link: ', err);
                    alert('Failed to copy link. Please try manually.');
                });
            } else {
                // Fallback for older browsers
                const textArea = document.createElement('textarea');
                textArea.value = linkElement.textContent;
                textArea.style.position = 'fixed'; // Avoid scrolling to bottom
                textArea.style.left = '-9999px';
                document.body.appendChild(textArea);
                textArea.focus();
                textArea.select();

                try {
                    document.execCommand('copy');
                    alert('Link copied to clipboard!');
                } catch (err) {
                    console.error('Fallback: Failed to copy link: ', err);
                    alert('Failed to copy link. Please try manually.');
                }

                document.body.removeChild(textArea);
            }
        });

        // Select All functionality
        document.getElementById('selectAll').addEventListener('click', function () {
            const checkboxes = document.querySelectorAll('.category-checkbox');
            checkboxes.forEach(checkbox => checkbox.checked = true);
        });

        // Unselect All functionality
        document.getElementById('unselectAll').addEventListener('click', function () {
            const checkboxes = document.querySelectorAll('.category-checkbox');
            checkboxes.forEach(checkbox => checkbox.checked = false);
        });
    </script>
@endsection
