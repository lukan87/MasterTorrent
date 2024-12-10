<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shoutbox</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>

<div class="container mt-5">

    <h3>Shoutbox</h3>
    <ul id="messages" class="list-group mb-3"></ul>
    <form id="shoutbox-form">
        <input type="text" id="message" class="form-control" placeholder="Type a message..." required>
        <button type="submit" class="btn btn-primary mt-2">Send</button>
    </form>
</div>
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script>
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // Fetch and display messages
    function loadMessages() {
        $.get('/shoutbox', function(data) {
            $('#messages').empty();
            data.forEach(message => {
                $('#messages').append(
                    `<li class="list-group-item">
                        <strong>${message.user.name}:</strong> ${message.message}
                        <button onclick="reply(${message.id})" class="btn btn-sm btn-link">Reply</button>
                        <ul class="list-group">
                            ${message.replies.map(reply => `<li class="list-group-item"><strong>${reply.user.name}:</strong> ${reply.message}</li>`).join('')}
                        </ul>
                    </li>`
                );
            });
        });
    }

    // Handle form submission
    $('#shoutbox-form').on('submit', function(e) {
        e.preventDefault();
        const message = $('#message').val();
        $.ajax({
            url: '/shoutbox',
            method: 'POST',
            data: { message },
            headers: { 'X-CSRF-TOKEN': csrfToken },
            success: function() {
                $('#message').val('');
                loadMessages();
            }
        });
    });

    function reply(parentId) {
        const replyMessage = prompt('Enter your reply:');
        if (replyMessage) {
            $.ajax({
                url: '/shoutbox',
                method: 'POST',
                data: { message: replyMessage, parent_id: parentId },
                headers: { 'X-CSRF-TOKEN': csrfToken },
                success: loadMessages
            });
        }
    }

    $(document).ready(loadMessages);
</script>
</body>
</html>
