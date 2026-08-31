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

/* MATRIX CANVAS */
#matrix {
    position: fixed;
    top: 0;
    left: 0;
    z-index: 0;
}

/* Wrapper */
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

/* Glass Card */
.glass-card {
    background: rgba(10, 10, 10, 0.85);
    border: 1px solid rgba(0, 150, 255, 0.15);
    border-radius: 25px;
    padding: 2.5rem;
    width: 100%;
    max-width: 480px;
    backdrop-filter: blur(25px);
    box-shadow:
        0 40px 100px rgba(0,0,0,1),
        0 0 40px rgba(0, 120, 255, 0.15);
    transition: transform 0.2s ease, box-shadow 0.3s ease;
    transform-style: preserve-3d;
    margin-top: 00px;
}

.glass-card:hover {
    box-shadow:
        0 50px 120px rgba(0,0,0,1),
        0 0 60px rgba(0, 150, 255, 0.35);
}

/* Logo Area */
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
    position: relative;
    z-index: 2;
    text-shadow:
        0 0 10px rgba(69, 133, 155, 0.8),
        0 0 25px rgba(0,191,255,0.6);
}

/* Blue Lightning */
.lightning {
    position: absolute;
    top: -40px;
    left: 50%;
    width: 200px;
    height: 200px;
    transform: translateX(-50%);
    background: radial-gradient(circle, rgba(0,191,255,0.4) 0%, transparent 70%);
    animation: lightningPulse 1.8s infinite alternate;
    filter: blur(25px);
}

@keyframes lightningPulse {
    from { opacity: 0.3; transform: translateX(-50%) scale(0.9); }
    to { opacity: 0.8; transform: translateX(-50%) scale(1.2); }
}

/* Inputs */
.form-group {
    position: relative;
    margin-bottom: 1.8rem;
}

.form-control {
    width: 100%;
    padding: 1rem 1rem 0.6rem;
    border-radius: 12px;
    border: 1px solid rgba(0, 191, 255, 0.15);
    background: rgba(255,255,255,0.02);
    color: #fff;
    font-size: 0.95rem;
    transition: 0.3s ease;
}

.form-control:focus {
    border-color: #00bfff;
    box-shadow: 0 0 20px rgba(0,191,255,0.5);
    outline: none;
}

.form-label {
    position: absolute;
    top: 1rem;
    left: 1rem;
    font-size: 0.85rem;
    color: #888;
    pointer-events: none;
    transition: 0.3s ease;
}

.form-control:focus + .form-label,
.form-control:not(:placeholder-shown) + .form-label {
    top: 0.4rem;
    font-size: 0.7rem;
    color: #00bfff;
}

/* Button */
.btn-elite {
    width: 100%;
    background: linear-gradient(90deg, #003366, #0c5b75);
    border: none;
    border-radius: 30px;
    padding: 0.8rem;
    font-weight: 600;
    color: #fff;
    transition: 0.3s ease;
    box-shadow: 0 0 20px rgba(0,191,255,0.4);
}

.btn-elite:hover {
    transform: translateY(-3px);
    box-shadow: 0 0 35px rgba(0, 255, 238, 0.8);
}

.links {
    margin-top: 1rem;
    text-align: center;
    font-size: 0.85rem;
}

.links a {
    color: #00bfff;
    text-decoration: none;
    margin: 0 8px;
    transition: 0.3s;
}

.links a:hover {
    color: #fff;
}
</style>

<canvas id="matrix"></canvas>

<div class="auth-wrapper">
    <div class="glass-card" id="tilt-card">

        <div class="logo-wrapper">
            <div class="lightning"></div>
            <div class="app-logo">
                {{ config('app.name') }}
            </div>
        </div>

        @if(session('status')) <div class="alert alert-success">{{ session('status') }}</div> @endif @if($errors->any()) <div class="alert alert-danger"> <ul class="mb-0"> @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach </ul> </div> @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="form-group">
                <input type="text" name="name" required placeholder=" " class="form-control">
                <label class="form-label">Username</label>
            </div>

            <div class="form-group">
                <input type="password" name="password" required placeholder=" " class="form-control">
                <label class="form-label">Password</label>
            </div>

            <button type="submit" class="btn btn-elite">
                ACCESS LastFiles
            </button>

        </form>

    </div>

</div>

<!-- ACTION LINKS CARD -->

<div class="glass-card help-card text-center">

    <h6 class="help-title">
        Need Help?
    </h6>

    <div class="help-links">

        <a href="{{ route('custom.password.recover') }}" class="help-btn">
            🔑 Forgot Password
        </a>

        <a href="{{ route('register') }}" class="help-btn">
            🧾 Create Account
        </a>

        <a href="{{ route('contact.create') }}" class="help-btn help-btn-danger">
            💬 Contact Staff
        </a>

    </div>

</div>

   <style>

/* HELP CARD */

.help-card{
    padding:2rem;
}

/* Title */

.help-title{
    letter-spacing:3px;
    color:#00bfff;
    font-family:'Orbitron', sans-serif;
    margin-bottom:1.5rem;
}

/* Links container */

.help-links{
    display:flex;
    flex-direction:column;
    gap:12px;
}

/* Buttons */

.help-btn{

    display:block;

    padding:12px;

    border-radius:12px;

    text-decoration:none;

    font-weight:500;

    background:rgba(255,255,255,0.03);

    border:1px solid rgba(0,191,255,0.2);

    color:#e6edf3;

    transition:0.25s ease;

}

/* Hover effect */

.help-btn:hover{

    transform:translateY(-3px);

    background:rgba(0,191,255,0.08);

    box-shadow:0 0 15px rgba(0,191,255,0.4);

    color:#fff;

}

/* Contact staff highlight */

.help-btn-danger{

    border:1px solid rgba(0,255,180,0.3);

}

.help-btn-danger:hover{

    background:rgba(0,255,180,0.08);

    box-shadow:0 0 18px rgba(0,255,180,0.5);

}

   </style>


<script>
/* ================= MATRIX EFFECT ================= */
const canvas = document.getElementById("matrix");
const ctx = canvas.getContext("2d");

canvas.height = window.innerHeight;
canvas.width = window.innerWidth;

const letters = "01アァカサタナハマヤャラワ0123456789";
const fontSize = 14;
const columns = canvas.width / fontSize;
const drops = [];

for (let x = 0; x < columns; x++)
    drops[x] = 1;

function drawMatrix() {
    ctx.fillStyle = "rgba(0, 0, 0, 0.05)";
    ctx.fillRect(0, 0, canvas.width, canvas.height);

    ctx.fillStyle = "#00ff66";
    ctx.font = fontSize + "px monospace";

    for (let i = 0; i < drops.length; i++) {
        const text = letters.charAt(Math.floor(Math.random() * letters.length));
        ctx.fillText(text, i * fontSize, drops[i] * fontSize);

        if (drops[i] * fontSize > canvas.height && Math.random() > 0.975)
            drops[i] = 0;

        drops[i]++;
    }
}

setInterval(drawMatrix, 33);

/* ================= 3D TILT ================= */
const card = document.getElementById("tilt-card");

card.addEventListener("mousemove", (e) => {
    const rect = card.getBoundingClientRect();
    const x = e.clientX - rect.left;
    const y = e.clientY - rect.top;

    const centerX = rect.width / 2;
    const centerY = rect.height / 2;

    const rotateX = ((y - centerY) / 25);
    const rotateY = ((centerX - x) / 25);

    card.style.transform = `rotateX(${rotateX}deg) rotateY(${rotateY}deg)`;
});

card.addEventListener("mouseleave", () => {
    card.style.transform = "rotateX(0) rotateY(0)";
});
</script>

@endsection
