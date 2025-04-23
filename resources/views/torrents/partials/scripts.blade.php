<script>
    function fetchIMDBInfo() {
        const imdbUrl = document.getElementById('imdb_url').value;
        const imdbIdMatch = imdbUrl.match(/(?:imdb\.com\/title\/)(tt\d+)/);
        const imdbId = imdbIdMatch ? imdbIdMatch[1] : null;

        if (imdbId) {
            const apiKey = 'd3eb5201';
            const apiUrl = `https://www.omdbapi.com/?i=${imdbId}&apikey=${apiKey}&plot=full`;

            fetch(apiUrl)
                .then(response => response.json())
                .then(data => {
                    if (data.Response === 'True') {
                        const movieInfo = `[center][img]${data.Poster}[/img]\n\n\n[b]${data.Title} (${data.Year})[/b]\n\n\n[quote]${data.Plot}[/quote]\n[font=Arial][color=grey]Genre: ${data.Genre}[/color][/font][/center]`;
                        document.getElementById('description').value += movieInfo;
                    } else {
                        alert('Movie not found or invalid IMDb ID.');
                    }
                })
                .catch(() => alert('Error fetching movie info.'));
        } else {
            alert('Please enter a valid IMDb URL.');
        }
    }

    function toggleCustomReason(elem) {
        document.getElementById('custom_reason_div').style.display = (elem.value === 'custom') ? 'block' : 'none';
    }

    function confirmDelete() {
        return confirm('Are you sure you want to delete this torrent?');
    }

    function resizeTextarea(id) {
        const el = document.getElementById(id);
        el.style.height = 'auto';
        el.style.height = el.scrollHeight + 'px';
    }

    function insertBBCode(tag, value = null) {
        const textarea = document.getElementById('description');
        const start = textarea.selectionStart;
        const end = textarea.selectionEnd;
        const selected = textarea.value.substring(start, end);
        const openTag = value ? `[${tag}=${value}]` : `[${tag}]`;
        const closeTag = `[/${tag}]`;
        const newText = textarea.value.substring(0, start) + openTag + selected + closeTag + textarea.value.substring(end);
        textarea.value = newText;
        textarea.focus();
    }

    function adjustTextareaHeight(textarea) {
        // Reset the height to auto to calculate the new height
        textarea.style.height = 'auto';
        
        // Set the height based on scrollHeight (content height) but limit it to a max of 600px
        if (textarea.scrollHeight > 600) {
            textarea.style.height = '600px';
        } else if (textarea.scrollHeight > 400) {
            textarea.style.height = textarea.scrollHeight + 'px';
        } else {
            textarea.style.height = 'auto';
        }
    }
</script>