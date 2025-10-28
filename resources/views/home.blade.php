@extends('layouts.app')

@section('title',  'LastFiles::Welcome Home' )

@section('content')

<div class="row">


@include('partials.happyhour')

<div class="col-lg-7 col-md-6 col-sm-6">

@include('partials.news')

</div>
<div class="col-lg-5 col-md-6 col-sm-6">

<x-poll-list :polls="$polls" />

</div>

@if (Auth::check() && Auth::user()->id !=3 )

<iframe src="https://stream.clever-host.ro/cp/widgets/player/single/?p=8036" height="110" width="100%" scrolling="no" style="border:none;"></iframe>

@endif



@include('partials.toptorrents')

@if (Auth::check() && Auth::user()->user_class >= \App\Models\UserClass::VIP)



@include('partials.onlineusers')
@include('partials.stats')



@endif



<div class="card mt-4">
    <div class="card-header">
        <h5 class="card-title mb-0"><strong>Disclaimer:</strong></h5>
    </div>
    <div class="card-body" style="font-size: 0.85rem;">
        <p class="mb-0">
            Niciunul dintre fișierele indexate pe această platformă nu este găzduit pe serverele noastre. Toate link-urile și conținutul indexat sunt furnizate exclusiv de utilizatorii site-ului, iar administratorii platformei nu își asumă responsabilitatea pentru acțiunile și materialele distribuite de aceștia. Accesul și utilizarea acestui serviciu trebuie să respecte legile și reglementările în vigoare. Orice utilizare a platformei pentru scopuri ilegale este strict interzisă și poate atrage măsuri disciplinare, inclusiv dezactivarea permanentă a accesului. Recomandăm tuturor utilizatorilor să se informeze și să respecte legislația aplicabilă în domeniul drepturilor de autor și distribuției de conținut.
        </p>
    </div>
</div>
</div>



<!-- HALLOWEEN DECOR: bats, ghosts, pumpkins -->
<!-- Includes: accessible toggle, respects prefers-reduced-motion, lightweight vanilla JS -->

<!-- <style>
/* Overlay */
#halloween-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    pointer-events: none; /* doesn't block clicks */
    overflow: hidden;
    z-index: 1050; /* above most UI but under modal backdrops if needed */
}

.halloween-elem {
    position: absolute;
    will-change: transform, opacity;
    user-select: none;
    font-size: 1.4rem;
    line-height: 1;
}

/* Bat style */
.halloween-bat svg { width: 60px; height: 40px; display: block; }

/* Ghost style */
.halloween-ghost svg { width: 56px; height: 64px; display: block; }

/* Pumpkin style */
.halloween-pumpkin svg { width: 70px; height: 70px; display: block; }

@keyframes float-across {
    0% { transform: translateX(0) translateY(0) scale(1); opacity: 1; }
    50% { transform: translateX(-20vw) translateY(-10vh) scale(1.05); }
    100% { transform: translateX(-40vw) translateY(-20vh) scale(0.9); opacity: 0; }
}

@keyframes sink-down {
    0% { transform: translateY(-10vh) scale(0.9); opacity: 0; }
    20% { opacity: 1; }
    100% { transform: translateY(100vh) scale(1); opacity: 1; }
}

@keyframes bob {
    0% { transform: translateY(0); }
    50% { transform: translateY(-8px); }
    100% { transform: translateY(0); }
}

/* Toggle button */
#halloween-toggle {
    position: fixed;
    top: 1rem;
    right: 1rem;
    z-index: 1060;
    pointer-events: auto; /* allow clicking */
}

#halloween-toggle button {
    background: rgba(0,0,0,0.6);
    color: #fff;
    border: none;
    padding: 8px 10px;
    border-radius: 6px;
    font-size: 0.9rem;
}

/* Respect reduced motion */
@media (prefers-reduced-motion: reduce) {
    .halloween-elem { animation: none !important; }
}

/* Small screens: reduce count */
@media (max-width: 576px) {
    .halloween-elem { display: none; }
}
</style>

<div id="halloween-overlay" aria-hidden="true"></div>
<div id="halloween-toggle" aria-hidden="false">
    <button id="halloween-toggle-btn" aria-pressed="true" title="Toggle Halloween decorations">🎃 Halloween ON</button>
</div>

<script>
(function(){
    // Do not run if user prefers reduced motion
    const reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (reduced) return;

    const overlay = document.getElementById('halloween-overlay');
    const toggleBtn = document.getElementById('halloween-toggle-btn');
    let enabled = true;
    let spawnInterval = null;

    function rand(min, max){ return Math.random() * (max - min) + min; }

    const svgs = {
        bat: `<svg viewBox="0 0 64 40" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M2 20 C10 4, 22 4, 32 18 C42 4, 54 4, 62 20 C54 14, 46 16, 32 24 C18 16, 10 14, 2 20 Z" fill="#111"/></svg>`,
        ghost: `<svg viewBox="0 0 64 80" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M32 4 C18 4 8 14 8 28 V56 C8 66 14 70 20 70 C24 70 28 66 32 66 C36 66 40 70 44 70 C50 70 56 66 56 56 V28 C56 14 46 4 32 4 Z" fill="#f8f8ff" stroke="#ddd" stroke-width="1"/><circle cx="24" cy="34" r="3" fill="#333"/><circle cx="40" cy="34" r="3" fill="#333"/></svg>`,
        pumpkin: `<svg viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M50 10 C30 10 18 26 18 40 C18 60 32 78 50 78 C68 78 82 60 82 40 C82 26 70 10 50 10 Z" fill="#ff7b00"/><path d="M50 6 L52 2 L48 2 Z" fill="#5a3c00"/><path d="M38 46 C44 56,56 56,62 46" fill="#000" opacity="0.9"/></svg>`
    };

    function createElem(type){
        const span = document.createElement('span');
        span.className = 'halloween-elem halloween-' + type;
        span.innerHTML = svgs[type];
        // random start position at right side
        const startTop = rand(5, 85); // percent
        const startRight = -10; // start off-screen from right
        span.style.top = startTop + 'vh';
        span.style.right = startRight + 'vw';
        // random scale
        const scale = rand(0.7, 1.2);
        span.style.transform = `scale(${scale})`;

        // animation variations
        if (type === 'bat'){
            const dur = rand(6, 12);
            span.style.animation = `float-across ${dur}s linear forwards`;
            span.style.opacity = '0.95';
        } else if (type === 'ghost'){
            const dur = rand(10, 18);
            span.style.animation = `float-across ${dur}s linear forwards`;
            span.style.opacity = '0.95';
            // gentle bobbing
            span.style.animation += `, bob ${rand(3,5)}s ease-in-out infinite`;
        } else if (type === 'pumpkin'){
            // pumpkins spawn from top or bottom and sink or bounce
            const fromTop = Math.random() > 0.5;
            if (fromTop) {
                span.style.top = '-10vh';
                span.style.left = rand(5, 90) + 'vw';
                span.style.animation = `sink-down ${rand(8, 12)}s linear forwards`;
            } else {
                span.style.top = rand(60, 95) + 'vh';
                span.style.left = rand(5, 90) + 'vw';
                span.style.animation = `bob ${rand(4,6)}s ease-in-out infinite`;
            }
            span.style.opacity = '1';
        }

        overlay.appendChild(span);

        // remove when animation finished to keep DOM small
        span.addEventListener('animationend', function(){
            if (span && span.parentNode === overlay) overlay.removeChild(span);
        });

        // safety: remove after max 30s
        setTimeout(()=>{ if (span && span.parentNode === overlay) overlay.removeChild(span); }, 30000);
    }

    function spawnRoutine(){
        // spawn a bat often, ghost less often, pumpkin rarely
        const p = Math.random();
        if (p < 0.6) createElem('bat');
        else if (p < 0.9) createElem('ghost');
        else createElem('pumpkin');
    }

    function start(){
        if (spawnInterval) clearInterval(spawnInterval);
        spawnInterval = setInterval(spawnRoutine, 900);
        // initial burst
        for (let i=0;i<6;i++) setTimeout(spawnRoutine, i*250);
    }

    function stop(){
        if (spawnInterval) clearInterval(spawnInterval);
        // remove existing
        overlay.querySelectorAll('.halloween-elem').forEach(e=>e.remove());
    }

    // toggle button behaviour
    toggleBtn.addEventListener('click', function(){
        enabled = !enabled;
        if (enabled){
            start();
            toggleBtn.textContent = '🎃 Halloween ON';
            toggleBtn.setAttribute('aria-pressed', 'true');
        } else {
            stop();
            toggleBtn.textContent = '👻 Halloween OFF';
            toggleBtn.setAttribute('aria-pressed', 'false');
        }
    });

    // start by default
    start();

    // optional: stop on low battery or if on data-saver (conservative)
    if (navigator.connection) {
        const c = navigator.connection;
        if (c.saveData || c.effectiveType && c.effectiveType.includes('2g')){
            stop();
            toggleBtn.textContent = '👻 Halloween OFF (data-saver)';
            toggleBtn.setAttribute('aria-pressed', 'false');
        }
    }

})();
</script> -->

@endsection
