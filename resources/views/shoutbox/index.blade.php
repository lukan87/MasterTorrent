@extends('layouts.app')

@section('content')


<!-- Shoutbox Rules Dropdown -->
<div class="dropdown mb-3 mt-1">
    <button class="btn btn-secondary dropdown-toggle" type="button" id="chatRulesDropdown" data-bs-toggle="dropdown" aria-expanded="false">
        Chat Rules
    </button>
    <div class="dropdown-menu dropdown-menu-dark p-3" aria-labelledby="chatRulesDropdown" style="width: 100%; overflow-y: auto;">
        <div class="container-fluid">
            <div class="row">
                <!-- Romanian Rules Card -->
                <div class="col-md-6 mb-3 mb-md-0">
                    <div class="card bg-dark text-white h-100">
                        <div class="card-header bg-secondary">
                            <h5 class="mb-0">RO Reguli</h5>
                        </div>
                        <div class="card-body">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item bg-transparent text-white border-secondary">1. Folosiți un limbaj prietenos față de colegi</li>
                                <li class="list-group-item bg-transparent text-white border-secondary">2. Dacă aveți frustrări referitoare la ceea ce se discută pe chat, vă rugăm să comentați într-un mod cât mai amiabil, sau mai bine, nu comentați!</li>
                                <li class="list-group-item bg-transparent text-white border-secondary">3. Nu faceți request-uri pe chat. Aveți secțiunea Request. Veți primi warn timp de o săptămână!</li>
                                <li class="list-group-item bg-transparent text-white border-secondary">4. Nu folosiți mesaje repetitive sau spam</li>
                                <li class="list-group-item bg-transparent text-white border-secondary">5. Discuțiile politice și religioase sunt strict interzise</li>
                                <li class="list-group-item bg-transparent text-white border-secondary">6. Respectați deciziile moderatorilor</li>
                            </ul>
                        </div>
                        <div class="card-footer bg-transparent border-secondary">
                            <div class="alert alert-warning mb-0">
                                <strong>Atenție:</strong> Nerespectarea acestor reguli poate duce la avertizări sau suspendarea accesului la chat.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- English Rules Card -->
                <div class="col-md-6">
                    <div class="card bg-dark text-white h-100">
                        <div class="card-header bg-secondary">
                            <h5 class="mb-0">EN Rules</h5>
                        </div>
                        <div class="card-body">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item bg-transparent text-white border-secondary">1. Use friendly language with other members</li>
                                <li class="list-group-item bg-transparent text-white border-secondary">2. If you have frustrations about the chat discussion, please comment in a friendly manner, or better, don't!</li>
                                <li class="list-group-item bg-transparent text-white border-secondary">3. Do not make requests in the chat. Use the Request section. You will receive a 1-week warning!</li>
                                <li class="list-group-item bg-transparent text-white border-secondary">4. Do not use repetitive messages or spam</li>
                                <li class="list-group-item bg-transparent text-white border-secondary">5. Political and religious discussions are strictly prohibited</li>
                                <li class="list-group-item bg-transparent text-white border-secondary">6. Respect the moderators' decisions</li>
                            </ul>
                        </div>
                        <div class="card-footer bg-transparent border-secondary">
                            <div class="alert alert-warning mb-0">
                                <strong>Warning:</strong> Breaking these rules may result in warnings or suspension of chat access.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

    {{-- Shoutbox Card --}}
    <div class="card shoutbox-container mt-2">
        <div class="card-body">
          
            <div class="shoutbox-messages">
                @foreach($messages as $message)
                    <div class="d-flex align-items-start mb-3">
                      
                        <div class="avatar-container">
                            <img src="{{ $message->user->profile_image ?? asset('images/default_avatar/default-avatar.jpg') }}" alt="User Avatar">
                        </div>

                        
                        <div class="content-container w-100">
                             
                            <div class="arrow"></div>
                            
                            <div class="d-flex justify-content-between">
                            <span class="header">
                            <a href="{{ route('profile.show', ['id' => $message->user->id]) }}"
   style="color: {{ \App\Models\UserClass::getClassColor($message->user->user_class) }}">
    {{ $message->user->name }}
    @if($message->user->warned)
        <i class="bi bi-exclamation-triangle-fill text-danger" data-bs-toggle="tooltip" title="Warned"></i>
    @endif
    @if($message->user->donor == 'yes')
        <i class="bi bi-star-fill text-success" data-bs-toggle="tooltip" title="Donor"></i>
    @endif
</a>

</span>

                                <span class="timestamp">{{ $message->created_at->format('Y-m-d H:i') }}
                                  
                            <div class="actions">
                            @if(auth()->id() === $message->user_id || Auth::user()->user_class >= \App\Models\UserClass::ADMIN)
                                    <a href="{{ route('shoutbox.edit', $message->id) }}" class="btn btn-sm btn-outline-warning">Edit</a>
                                    <form action="{{ route('shoutbox.destroy', $message->id) }}" method="POST" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this shout?')">Delete</button>
                                    </form>
                                @endif
                                <button class="btn btn-sm btn-outline-primary" onclick="toggleReplyForm({{ $message->id }})">Reply</button>
                            </div>
                                </span>
                            </div>

                          
                            <div class="message-content"> {!! convertCustomTagsToHtml( $message->message ) !!}</div>

                         
                            <form id="reply-form-{{ $message->id }}" action="{{ route('shoutbox.reply', $message->id) }}" method="POST" style="display: none;" class="mt-2">
                                @csrf
                                <div class="form-group">
                                    <textarea name="content" class="form-control" placeholder="Type your reply here..." required></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary btn-sm mt-2">Post Reply</button>
                            </form>

                          
                           @if($message->replies && $message->replies->count() > 0)
    <div class="replies mt-3" style="background-color: #444444; padding: 10px; border-radius: 5px;">
        @foreach($message->replies->sortByDesc('created_at') as $reply)
            <div class="d-flex align-items-start mb-3">
               
                <div class="avatar-container">
                    <img src="{{ $reply->user->profile_image ?? asset('images/default_avatar/default-avatar.jpg') }}" alt="User Avatar">
                </div>

               
                <div class="content-container w-100">
                    
                     <div class="arrow"></div>
                    <div class="d-flex justify-content-between">
                        <span class="header">
                        <a href="{{ route('messages.create', ['receiver_id' => $reply->user->id]) }}"
   style="color: {{ \App\Models\UserClass::getClassColor($reply->user->user_class) }}">
    {{ $reply->user->name }}
    @if($reply->user->warned)
    <i class="bi bi-exclamation-triangle-fill text-danger" data-bs-toggle="tooltip" title="Warned"></i>
    @endif
    @if($reply->user->donor === 'yes')
    <i class="bi bi-star-fill text-success" data-bs-toggle="tooltip" title="Donor"></i>
    @endif
</a>

                        </span>
                        <span class="timestamp">{{ $reply->created_at->format('Y-m-d H:i') }}
                           
                            <div class="actions">
                                @if(auth()->id() === $reply->user_id || Auth::user()->user_class >= \App\Models\UserClass::ADMIN)
                                    <a href="{{ route('shoutbox.edit', $reply->id) }}" class="btn btn-sm btn-outline-warning">Edit</a>
                                    <form action="{{ route('shoutbox.destroy', $reply->id) }}" method="POST" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this reply?')">Delete</button>
                                    </form>
                                @endif
                            </div>
                        </span>
                    </div>
                    <div class="message-content">{{ $reply->message }}</div>
                </div>
            </div>
        @endforeach
    </div>
@endif


                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>



    @include('shoutbox.partials.emoji')
   
    <form id="shoutbox-form" action="{{ route('shoutbox.store') }}" method="POST" class="mb-4 mt-3">
    @csrf
    <div class="form-group">
        <textarea name="content" id="content" class="form-control" placeholder="Type your message here..." required onkeydown="submitOnEnter(event)"></textarea>
    </div>
    <!-- <button type="submit" class="btn btn-primary mt-2">Post Message</button> -->
</form>


<script>
    function insertBBCode(tag) {
        // Get the textarea element
        var textarea = document.getElementById('content');

        // Get the selected text (if any)
        var selectedText = textarea.value.substring(textarea.selectionStart, textarea.selectionEnd);

        // Determine the cursor position for inserting BBCode
        var cursorPos;
        if (selectedText.length > 0) {
            // If there is selected text, insert the BBCode around it
            cursorPos = textarea.selectionEnd + tag.length;
        } else {
            // If no text is selected, insert the BBCode with the cursor after the closing square bracket
            cursorPos = textarea.selectionStart + tag.length + 2;
        }

        // Insert the BBCode into the textarea
        var currentContent = textarea.value;
        var newContent = currentContent.substring(0, textarea.selectionStart) +
            '[' + tag + ']' + selectedText + '[/' + tag + ']' +
            currentContent.substring(textarea.selectionEnd);
        textarea.value = newContent;

        // Set the cursor position
        textarea.setSelectionRange(cursorPos, cursorPos);
        textarea.focus();
    }

    function insertEmoji(emojiCode) {
    var textarea = document.getElementById('content');
    var cursorPos;

    // Fetch the emoji from the server using AJAX
    fetch(`/get-emoji/${emojiCode}`)
        .then(response => response.json())
        .then(data => {
            var emoji = data.emoji;
            var selectedText = textarea.value.substring(textarea.selectionStart, textarea.selectionEnd);

            if (selectedText.length > 0) {
                cursorPos = textarea.selectionEnd + emoji.length;
                textarea.value = textarea.value.substring(0, textarea.selectionStart) +
                    emoji + selectedText + emoji +
                    textarea.value.substring(textarea.selectionEnd);
            } else {
                cursorPos = textarea.selectionStart + emoji.length;
                textarea.value = textarea.value.substring(0, textarea.selectionStart) +
                    emoji + textarea.value.substring(textarea.selectionEnd);
            }

            textarea.setSelectionRange(cursorPos, cursorPos);
            textarea.focus();
        })
        .catch(error => console.error('Error fetching emoji:', error));
}


    function submitOnEnter(event) {
        // Check if the key pressed is Enter
        if (event.key === 'Enter' && !event.shiftKey) { // Allow shift+enter for new line
            event.preventDefault(); // Prevent the default action (new line)
            document.getElementById('shoutbox-form').submit(); // Submit the form
        }
    }

    document.getElementById('shoutbox-form').addEventListener('submit', function(event) {
        event.preventDefault();
        const formData = new FormData(this);
        fetch(this.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            }
        })
        .then(response => response.json())
        .then(data => {
            // Handle success (e.g., update message list)
            // You can reload messages or append the new message directly
        })
        .catch(error => console.error('Error:', error));
    });

    function toggleReplyForm(id) {
        var form = document.getElementById('reply-form-' + id);
        if (form.style.display === 'none') {
            form.style.display = 'block';
        } else {
            form.style.display = 'none';
        }
    }


</script>

<style>
    .shoutbox-message {
        list-style: none;
        margin-bottom: 20px;
    }

    .avatar-container img {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        margin-right: 10px;
    }

    .content-container {
        background-color: #333333;
        color: #ffffff;
        padding: 15px;
        border-radius: 10px;
        position: relative;

    }
    .arrow {
    width: 0;
    height: 0;
    border-left: 10px solid transparent;
    border-right: 10px solid transparent;
    border-top: 10px solid #333333; /* Match the bubble color */
    position: absolute;
    left: -10px; /* Position the arrow to the left of the bubble */
    top: 10px; /* Adjust to align with the bubble */
}

    .header {
        font-weight: bold;
        font-size: 18px;
    }

    .timestamp {
        font-size: 0.9rem;
        color: #aaa;
    }

    .message-content {
        font-size: 1rem;
        font-weight: bold;
        margin-top: 5px;
    }

    .actions {
        display: flex;
        gap: 10px;
        margin-top: 10px;
    }

    .replies {
    margin-top: 15px;
    padding-left: 20px;
    border-left: 2px solid #888;
    background-color: #444; /* Change this to your desired color */
    padding: 10px; /* Optional: Add some padding for better appearance */
    border-radius: 5px; /* Optional: Rounded corners */
}


    body {
        background-image: linear-gradient(to bottom right, #333333, #000000);
        background-attachment: fixed;
    }

    .shoutbox-container {
        max-height: 750px;
        overflow-y: auto; /* Add vertical scrolling */
    }
</style>
@endsection
