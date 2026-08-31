@extends('layouts.app')

@section('content')

<div class="container mt-5">

    <div class="card shadow-sm border-0">
        <div class="card-body p-4">

            <div class="d-flex align-items-center mb-4">
                <i class="bi bi-envelope-paper-fill text-primary fs-3 me-2"></i>
                <h4 class="mb-0">Send Email</h4>
            </div>

            <form method="POST" action="{{ route('admin.emails.send') }}" onsubmit="return validateSend()">
                @csrf

                {{-- TEMPLATE --}}
                <div class="mb-4">
                    <label class="form-label fw-semibold">
                        <i class="bi bi-layout-text-window-reverse me-1 text-muted"></i>
                        Template
                    </label>

                    <select id="templateSelect" name="template_id" class="form-select">
    <option value="">Custom Email</option>

    @foreach($templates as $t)
        <option 
            value="{{ $t->id }}"
            data-subject="{{ $t->subject }}"
            data-body="{{ $t->body }}"
        >
            {{ $t->name }}
        </option>
    @endforeach
</select>
                </div>

                {{-- SUBJECT --}}
                <div class="mb-4">
                    <label class="form-label fw-semibold">
                        <i class="bi bi-type me-1 text-muted"></i>
                        Subject
                    </label>

                    <input 
                        name="subject" 
                        id="subject" 
                        class="form-control form-control-lg" 
                        placeholder="Enter email subject..."
                    >
                </div>

                {{-- BODY --}}
                <div class="mb-4">
                    <label class="form-label fw-semibold">
                        <i class="bi bi-card-text me-1 text-muted"></i>
                        Email Content
                    </label>

                    <textarea 
                        name="body" 
                        id="body" 
                        rows="8" 
                        class="form-control"
                        placeholder="Write your email here... You can use {name} and {email}"
                    ></textarea>

                    <small class="text-muted">
                        Available variables: <strong>{name}</strong>, <strong>{email}</strong>
                    </small>
                </div>

                {{-- TARGETING --}}
                <div class="row">

                    {{-- USER CLASS --}}
                    <div class="col-md-6 mb-4">
                        <label class="form-label fw-semibold">
                            <i class="bi bi-people me-1 text-muted"></i>
                            User Class
                        </label>

                        <select name="user_class[]" id="userClassSelect" class="form-select" multiple size="6">
                            @foreach($classes as $value => $label)
                                <option value="{{ $value }}">
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>

                        <small class="text-muted">
                            Leave empty to target all classes
                        </small>
                    </div>

                    {{-- TARGET TYPE --}}
                    <div class="col-md-6 mb-4">
                        <label class="form-label fw-semibold">
                            <i class="bi bi-filter-circle me-1 text-muted"></i>
                            Audience
                        </label>

                        <select name="target" id="targetSelect" class="form-select">
                            <option value="subscribed">Subscribed Users</option>
                            <option value="inactive">Inactive Users (60d)</option>
                            
                        </select>
                    </div>


                    <div class="mb-3">
    <div class="alert alert-info py-2">
        👥 Will send to: <strong id="userCount">0</strong> users
    </div>
</div>

<div id="confirmBox" class="alert alert-warning mt-3 d-none">
    <strong>⚠ Large send detected!</strong><br>

    You are about to send emails to <strong id="confirmCount">0</strong> users.<br>

    Please type <strong>SEND</strong> to confirm:

    <input type="text" id="confirmInput" class="form-control mt-2" placeholder="Type SEND to confirm">
</div>

                </div>

                {{-- CONFIRMATION HIDDEN FIELD --}}
<input type="hidden" name="confirm" id="confirmHidden">

                {{-- ACTION --}}
                <div class="d-flex justify-content-end">
                    <button class="btn btn-primary px-4">
                        <i class="bi bi-send-fill me-1"></i>
                        Send Email
                    </button>
                </div>

            </form>

        </div>
    </div>

</div>

{{-- TEMPLATE AUTO-FILL --}}
<script>
document.getElementById('templateSelect').addEventListener('change', function () {
    let option = this.options[this.selectedIndex];

    document.getElementById('subject').value = option.dataset.subject || '';
    document.getElementById('body').value = option.dataset.body || '';
});
</script>
<script>
let currentCount = 0;

function updateUserCount() {
    let classes = Array.from(document.getElementById('userClassSelect').selectedOptions)
        .map(option => option.value);

    let target = document.getElementById('targetSelect').value;

    document.getElementById('userCount').innerText = '...';

    fetch("{{ route('admin.emails.count') }}", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": "{{ csrf_token() }}"
        },
        body: JSON.stringify({
            user_class: classes,
            target: target
        })
    })
    .then(res => res.json())
    .then(data => {
        currentCount = data.count;

        document.getElementById('userCount').innerText = data.count;

        let confirmBox = document.getElementById('confirmBox');

        if (data.count > 1000) {
            confirmBox.classList.remove('d-none');
            document.getElementById('confirmCount').innerText = data.count;
        } else {
            confirmBox.classList.add('d-none');
        }
    });
}

// listeners
document.getElementById('userClassSelect').addEventListener('change', updateUserCount);
document.getElementById('targetSelect').addEventListener('change', updateUserCount);

// run once
updateUserCount();
</script>


<script>
document.addEventListener('DOMContentLoaded', function () {

    let confirmInput = document.getElementById('confirmInput');

    if (confirmInput) {
        confirmInput.addEventListener('input', function () {
            document.getElementById('confirmHidden').value = this.value;
        });
    }

});
</script>

@endsection