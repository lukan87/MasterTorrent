document.addEventListener('DOMContentLoaded', function () {
    const form = document.querySelector('form');
    form.addEventListener('submit', function (event) {
        event.preventDefault(); // Previne trimiterea formularului tradițională

        const formData = new FormData(form);

        fetch(form.action, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
            },
            body: formData,
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);

                // Descărcare automată a fișierului
                const downloadLink = document.createElement('a');
                downloadLink.href = data.download_url;
                downloadLink.download = '';
                document.body.appendChild(downloadLink);
                downloadLink.click();
                document.body.removeChild(downloadLink);

                // Redirect către pagina detaliilor torrentului după descărcare
                setTimeout(() => {
                    window.location.href = `{{ url('torrents') }}/${data.id}/${data.slug}`;
                }, 2000); // Așteaptă 2 secunde pentru a finaliza descărcarea
            } else {
                alert('Error uploading torrent.');
            }
        })
        .catch(error => console.error('Error:', error));
    });
});
