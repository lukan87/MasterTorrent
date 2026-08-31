@extends('layouts.app')

@section('content')

<style>
@import url('https://fonts.googleapis.com/css2?family=Orbitron:wght@600;800&family=Inter:wght@400;500&display=swap');

body {
    margin: 0;
    min-height: 100vh; /* allow growth */
    background: #030303;
    overflow-x: hidden;
    overflow-y: auto; /* enable vertical scroll */
    font-family: 'Inter', sans-serif;
    display: flex;
    justify-content: center;
    align-items: center;
    color: #e6edf3;
}

/* Dropping Dots */
#dots {
    position: fixed;
    top: 0;
    left: 0;
    z-index: 0;
}

.auth-wrapper {
    position: relative;
    z-index: 2;
    width: 100%;
    padding: 1rem;
    display: flex;
    justify-content: center;
    align-items: center;
    perspective: 1200px;
}

/* Mobile fix */
@media (max-width: 768px) {
    .auth-wrapper {
        align-items: flex-start; /* stack from top */
        padding-top: 2rem;
        padding-bottom: 2rem;
    }
}

.glass-card {
    background: rgba(10, 10, 10, 0.9);
    border: 1px solid rgba(0, 191, 255, 0.15);
    border-radius: 25px;
    padding: 3rem;
    width: 100%;
    max-width: 650px;
    backdrop-filter: blur(25px);
    box-shadow:
        0 40px 100px rgba(0,0,0,1),
        0 0 40px rgba(0, 120, 255, 0.15);
    transition: transform 0.2s ease, box-shadow 0.3s ease;
    transform-style: preserve-3d;
}

.glass-card {
    padding: 3rem;
}

/* Tablet */
@media (max-width: 768px) {
    .glass-card {
        padding: 2rem;
        border-radius: 20px;
    }
}

/* Mobile */
@media (max-width: 480px) {
    .glass-card {
        padding: 1.5rem;
        border-radius: 18px;
    }
}

.glass-card:hover {
    box-shadow:
        0 50px 120px rgba(0,0,0,1),
        0 0 60px rgba(0,150,255,0.35);
}

/* Logo */
.logo-wrapper {
    position: relative;
    text-align: center;
    margin-bottom: 2rem;
}

.app-logo {
    font-family: 'Orbitron', sans-serif;
    font-size: 2.3rem;
    letter-spacing: 4px;
    color: #00bfff;
    z-index: 2;
    position: relative;
    text-shadow:
        0 0 10px rgba(0,191,255,0.8),
        0 0 25px rgba(0,191,255,0.6);
}

.lightning {
    position: absolute;
    top: -40px;
    left: 50%;
    width: 220px;
    height: 220px;
    transform: translateX(-50%);
    background: radial-gradient(circle, rgba(0,191,255,0.4) 0%, transparent 70%);
    animation: lightningPulse 2s infinite alternate;
    filter: blur(30px);
}

.lightning {
    pointer-events: none;
}

@keyframes lightningPulse {
    from { opacity: 0.3; transform: translateX(-50%) scale(0.9); }
    to { opacity: 0.9; transform: translateX(-50%) scale(1.25); }
}

/* Inputs */
.form-control,
.form-select {
    background: rgba(255,255,255,0.02) !important;
    border: 1px solid rgba(0,191,255,0.15) !important;
    border-radius: 12px !important;
    color: #fff !important;
    padding: 0.9rem 1rem !important;
    transition: 0.3s ease;
}

.form-control:focus,
.form-select:focus {
    border-color: #00bfff !important;
    box-shadow: 0 0 20px rgba(0,191,255,0.5) !important;
    outline: none !important;
}

.form-label {
    color: #aaa;
}

/* Buttons */
.btn-register {
    background: linear-gradient(90deg, #003366, #00bfff);
    border: none;
    border-radius: 30px;
    padding: 0.8rem 2rem;
    font-weight: 600;
    color: #fff;
    transition: 0.3s ease;
    box-shadow: 0 0 20px rgba(0,191,255,0.4);
}

.btn-register:hover {
    transform: translateY(-3px);
    box-shadow: 0 0 35px rgba(0,191,255,0.8);
}

.btn-back {
    background: #111;
    border: 1px solid rgba(0,191,255,0.2);
    border-radius: 30px;
    padding: 0.8rem 2rem;
    color: #00bfff;
}

.btn-back:hover {
    background: rgba(0,191,255,0.1);
}

.form-select {
    background-color: rgba(255,255,255,0.02) !important;
    color: #fff !important;
    appearance: none;
    -webkit-appearance: none;
    -moz-appearance: none;
}

.form-select option {
    background-color: #111;
    color: #fff;
}

@media (max-width: 480px) {
    .d-flex.gap-3 {
        flex-direction: column;
    }

    .btn-register,
    .btn-back {
        width: 100%;
        text-align: center;
    }
}

</style>



<canvas id="dots"></canvas>

<div class="auth-wrapper">
<div class="glass-card" id="tilt-card">


@if ($errors->any())
    <div style="
        background:#2a0d0d;
        border:1px solid #ff4d4d;
        color:#ffb3b3;
        padding:12px;
        border-radius:10px;
        margin-bottom:15px;
        font-size:14px;
    ">
        <ul style="margin:0; padding-left:18px;">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="logo-wrapper">
    <div class="lightning"></div>
    <div class="app-logo">{{ config('app.name') }}</div>
</div>

<form method="POST" action="{{ route('register') }}">
@csrf

<div class="mb-3">
<label class="form-label">Name</label>
<input 
    type="text" 
    name="name" 
    class="form-control @error('name') is-invalid @enderror"
    value="{{ old('name') }}"
    required
>

@error('name')
    <div style="color:#ff6b6b; font-size:13px; margin-top:5px;">
        {{ $message }}
    </div>
@enderror
</div>

<div class="mb-3">
<label class="form-label">Email</label>
<input 
    type="email" 
    name="email" 
    class="form-control" 
    required 
    value="{{ old('email') }}"
>
</div>

<div class="mb-3">
<label class="form-label">Password</label>
<input type="password" name="password" class="form-control" required>
</div>

<div class="mb-3">
<label class="form-label">Confirm Password</label>
<input type="password" name="password_confirmation" class="form-control" required>
</div>

<div class="mb-3">
<label class="form-label">Recovery Code</label>
<input type="text" name="recovery_code" class="form-control" required>
<small class="text-muted">Write this down safely.</small>
</div>

@php $timezones = \DateTimeZone::listIdentifiers(); @endphp

<div class="mb-3">
    <label class="form-label">Timezone</label>

    <select name="timezone" id="timezone" class="form-select" required>
      <option disabled selected>Select timezone</option>
        @foreach($timezones as $timezone)
            <option value="{{ $timezone }}">{{ $timezone }}</option>
        @endforeach
    </select>

    <!-- Detected timezone display -->
    <small id="detected-timezone" class="text-info d-block mt-2" style="opacity:0.8;"></small>

    <!-- Use my timezone button -->
    <button type="button" id="use-my-timezone" 
        class="btn btn-sm btn-back mt-2">
        Use My Timezone
    </button>

    <!-- Hidden detected value -->
    <input type="hidden" name="detected_timezone" id="detected_timezone">
</div>


@if(config('app.invite_only'))
<div class="mb-3">
<label class="form-label">Invite Code</label>
<input type="text" name="invite_code" class="form-control" required>
</div>
@endif

<div class="mb-4">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">

        <div>
            <label class="form-label mb-0">Email Notifications</label>
            <div class="small text-muted">
                Receive login reminders and important updates
            </div>
        </div>

        <div class="form-check form-switch m-0">
            <input 
                class="form-check-input"
                type="checkbox"
                name="subscribed"
                value="1"
                checked
            >
        </div>

    </div>
</div>

<div class="d-flex gap-3 mt-3 flex-wrap">
<button type="submit" class="btn btn-register">REGISTER</button>
<a href="{{ route('login') }}" class="btn btn-back">Back To Login</a>
</div>

</form>

</div>
</div>

<script>

    document.addEventListener("DOMContentLoaded", function () {

    const timezoneSelect = document.getElementById("timezone");
    const detectedText = document.getElementById("detected-timezone");
    const hiddenInput = document.getElementById("detected_timezone");
    const useBtn = document.getElementById("use-my-timezone");

    let detectedTimezone = null;

    try {
        detectedTimezone = Intl.DateTimeFormat().resolvedOptions().timeZone;

        if (detectedTimezone) {
            detectedText.innerHTML = "Detected timezone: <strong>" + detectedTimezone + "</strong>";
            hiddenInput.value = detectedTimezone;
        }

    } catch (e) {
        detectedText.textContent = "Could not detect timezone.";
    }

    // Button to apply detected timezone
    useBtn.addEventListener("click", function () {
        if (!detectedTimezone) return;

        const option = timezoneSelect.querySelector(
            `option[value="${detectedTimezone}"]`
        );

        if (option) {
            timezoneSelect.value = detectedTimezone;
        }
    });

});
/* Dropping Dots */
const canvas = document.getElementById("dots");
const ctx = canvas.getContext("2d");
canvas.width = window.innerWidth;
canvas.height = window.innerHeight;

let dots = [];
for (let i = 0; i < 333; i++) {
    dots.push({
        x: Math.random() * canvas.width,
        y: Math.random() * canvas.height,
        r: Math.random() * 3,
        speed: Math.random() * 1.5 + 0.5
    });
}

function drawDots() {
    ctx.clearRect(0,0,canvas.width,canvas.height);
    ctx.fillStyle="rgba(0,191,255,0.7)";
    ctx.beginPath();
    dots.forEach(d=>{
        ctx.moveTo(d.x,d.y);
        ctx.arc(d.x,d.y,d.r,0,Math.PI*2);
        d.y+=d.speed;
        if(d.y>canvas.height){
            d.y=0;
            d.x=Math.random()*canvas.width;
        }
    });
    ctx.fill();
    requestAnimationFrame(drawDots);
}
drawDots();

/* 3D Tilt */
const card = document.getElementById("tilt-card");
let isInteracting = false;

/* Disable tilt when interacting with form fields */
card.querySelectorAll("input, select, textarea, button").forEach(el => {
    el.addEventListener("focus", () => isInteracting = true);
    el.addEventListener("blur", () => isInteracting = false);
});

/* Softer 3D tilt */
card.addEventListener("mousemove", (e) => {

    if (isInteracting) return; // stop tilt when typing

    const rect = card.getBoundingClientRect();
    const x = e.clientX - rect.left;
    const y = e.clientY - rect.top;

    const centerX = rect.width / 2;
    const centerY = rect.height / 2;

    const rotateX = (y - centerY) / 30;   // reduced intensity
    const rotateY = (centerX - x) / 30;

    card.style.transform = `
        rotateX(${rotateX}deg)
        rotateY(${rotateY}deg)
        translateZ(5px)
    `;
});

/* Smooth reset */
card.addEventListener("mouseleave", () => {
    card.style.transition = "transform 0.4s ease";
    card.style.transform = "rotateX(0) rotateY(0)";
});

/* Restore instant movement after reset */
card.addEventListener("mouseenter", () => {
    card.style.transition = "transform 0.1s ease";
});

function resizeCanvas() {
    canvas.width = window.innerWidth;
    canvas.height = window.innerHeight;
}

window.addEventListener('resize', resizeCanvas);
resizeCanvas();
</script>

<script>

const nameInput = document.querySelector('input[name="name"]');
const registerBtn = document.querySelector('.btn-register');

// Remove message while typing
nameInput.addEventListener('input', function () {
    let existing = document.getElementById('name-error-live');
    if (existing) existing.remove();
    registerBtn.disabled = false;
});

// Check on blur
nameInput.addEventListener('blur', function () {

    if (!this.value) return;

    fetch(`/check-username?name=${encodeURIComponent(this.value)}`)
        .then(res => res.json())
        .then(data => {

            let existing = document.getElementById('name-error-live');
            if (existing) existing.remove();

            const div = document.createElement('div');
            div.id = 'name-error-live';
            div.style.fontSize = '13px';
            div.style.marginTop = '5px';

            if (data.exists) {
                div.style.color = '#ff6b6b';
                div.innerText = 'Username already taken';
                registerBtn.disabled = true;
            } else {
                div.style.color = '#4dff88';
                div.innerText = 'Username available';
            }

            nameInput.parentNode.appendChild(div);
        });
});
</script>



<script>
const emailInput = document.querySelector('input[name="email"]');
const registerBtnEmail = document.querySelector('.btn-register');

// Remove message while typing
emailInput.addEventListener('input', function () {
    let existing = document.getElementById('email-error-live');
    if (existing) existing.remove();
    registerBtnEmail.disabled = false;
});

// Check on blur
emailInput.addEventListener('blur', function () {

    if (!this.value) return;

    fetch(`/check-email?email=${encodeURIComponent(this.value)}`)
        .then(res => res.json())
        .then(data => {

            let existing = document.getElementById('email-error-live');
            if (existing) existing.remove();

            const div = document.createElement('div');
            div.id = 'email-error-live';
            div.style.fontSize = '13px';
            div.style.marginTop = '5px';

            if (data.exists) {
                div.style.color = '#ff6b6b';
                div.innerText = 'Email already registered';
                registerBtnEmail.disabled = true;
            } else {
                div.style.color = '#4dff88';
                div.innerText = 'Email available';
            }

            emailInput.parentNode.appendChild(div);
        });
});
</script>

@endsection
