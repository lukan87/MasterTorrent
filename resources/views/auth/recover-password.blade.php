@extends('layouts.app')

@section('content')

<style>
@import url('https://fonts.googleapis.com/css2?family=Orbitron:wght@600;800&family=Inter:wght@400;500&display=swap');

body {
    margin: 0;
    height: 100vh;
    background: #030303;
    overflow: hidden;
    font-family: 'Inter', sans-serif;
    display: flex;
    justify-content: center;
    align-items: center;
    color: #e6edf3;
}

/* Dropping dots background */
#dots {
    position: fixed;
    top: 0;
    left: 0;
    z-index: 0;
    pointer-events: none;
}

.auth-wrapper {
    position: relative;
    z-index: 2;
    width: 100%;
    padding: 1rem;
    display: flex;
    justify-content: center;
    align-items: center;
}

.glass-card {
    background: rgba(10, 10, 10, 0.9);
    border: 1px solid rgba(0, 191, 255, 0.15);
    border-radius: 25px;
    padding: 3rem;
    width: 100%;
    max-width: 600px;
    backdrop-filter: blur(25px);
    box-shadow:
        0 40px 100px rgba(0,0,0,1),
        0 0 40px rgba(0,120,255,0.15);
    transition: box-shadow 0.3s ease;
}

.glass-card:hover {
    box-shadow:
        0 50px 120px rgba(0,0,0,1),
        0 0 60px rgba(0,150,255,0.35);
}

/* Logo area */
.logo-wrapper {
    position: relative;
    text-align: center;
    margin-bottom: 2rem;
}

.app-logo {
    font-family: 'Orbitron', sans-serif;
    font-size: 2.2rem;
    letter-spacing: 4px;
    color: #00bfff;
    position: relative;
    z-index: 2;
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
    pointer-events: none;
}

@keyframes lightningPulse {
    from { opacity: 0.3; transform: translateX(-50%) scale(0.9); }
    to { opacity: 0.9; transform: translateX(-50%) scale(1.25); }
}

/* Inputs */
.form-control {
    background: rgba(255,255,255,0.02) !important;
    border: 1px solid rgba(0,191,255,0.15) !important;
    border-radius: 12px !important;
    color: #fff !important;
    padding: 0.9rem 1rem !important;
    transition: 0.3s ease;
}

.form-control:focus {
    border-color: #00bfff !important;
    box-shadow: 0 0 20px rgba(0,191,255,0.5) !important;
    outline: none !important;
}

/* Buttons */
.btn-recover {
    background: linear-gradient(90deg, #003366, #00bfff);
    border: none;
    border-radius: 30px;
    padding: 0.8rem 2rem;
    font-weight: 600;
    color: #fff;
    transition: 0.3s ease;
    box-shadow: 0 0 20px rgba(0,191,255,0.4);
}

.btn-recover:hover {
    transform: translateY(-2px);
    box-shadow: 0 0 35px rgba(0,191,255,0.8);
}

.btn-secondary-custom {
    background: #111;
    border: 1px solid rgba(0,191,255,0.2);
    border-radius: 30px;
    padding: 0.8rem 2rem;
    color: #00bfff;
}

.btn-secondary-custom:hover {
    background: rgba(0,191,255,0.1);
}

/* Alerts */
.alert-danger {
    background: rgba(200,50,50,0.9);
    color: white;
}

.alert-success {
    background: rgba(50,160,120,0.9);
    color: white;
}
</style>

<canvas id="dots"></canvas>

<div class="auth-wrapper">
<div class="glass-card">

<div class="logo-wrapper">
    <div class="lightning"></div>
    <div class="app-logo">{{ config('app.name') }}</div>
</div>

<h4 class="text-center mb-4">RECOVER PASSWORD</h4>

@if(session('status'))
<div class="alert alert-success">{{ session('status') }}</div>
@endif

@if($errors->any())
<div class="alert alert-danger">
    <ul class="mb-0">
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<form method="POST" action="{{ route('custom.password.update') }}">
@csrf

<div class="mb-3">
<label>Email</label>
<input type="email" name="email" class="form-control" required>
</div>

<div class="mb-3">
<label>Recovery Code</label>
<input type="text" name="recovery_code" class="form-control" required>
</div>

<div class="mb-3">
<label>New Password</label>
<input type="password" name="password" class="form-control" required>
</div>

<div class="mb-3">
<label>Confirm Password</label>
<input type="password" name="password_confirmation" class="form-control" required>
</div>

<div class="d-flex gap-3 flex-wrap mt-3">
<button type="submit" class="btn btn-recover">RESET PASSWORD</button>
<a href="{{ route('login') }}" class="btn btn-secondary-custom">Login</a>
<a href="{{ route('register') }}" class="btn btn-secondary-custom">Register</a>
</div>

</form>

</div>
</div>

<script>
/* Dropping Dots */
const canvas = document.getElementById("dots");
const ctx = canvas.getContext("2d");

canvas.width = window.innerWidth;
canvas.height = window.innerHeight;

let dots = [];

for (let i = 0; i < 100; i++) {
    dots.push({
        x: Math.random() * canvas.width,
        y: Math.random() * canvas.height,
        r: Math.random() * 2,
        speed: Math.random() * 1.5 + 0.5
    });
}

function drawDots() {
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    ctx.fillStyle = "rgba(0,191,255,0.7)";
    ctx.beginPath();

    dots.forEach(dot => {
        ctx.moveTo(dot.x, dot.y);
        ctx.arc(dot.x, dot.y, dot.r, 0, Math.PI * 2);
        dot.y += dot.speed;

        if (dot.y > canvas.height) {
            dot.y = 0;
            dot.x = Math.random() * canvas.width;
        }
    });

    ctx.fill();
    requestAnimationFrame(drawDots);
}

drawDots();
</script>

@endsection
