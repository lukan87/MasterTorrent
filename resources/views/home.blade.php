@extends('layouts.app')

@section('title',  'LastFiles::Welcome Home' )

@section('content')

<div class="row">


@include('partials.happyhour')

<div class="col-lg-8 col-md-6 col-sm-6">

@include('partials.news')

</div>
<div class="col-lg-4 col-md-6 col-sm-6">

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



<!-- 👻 Simple Halloween Floating Ghosts & Bats -->
<!-- <style>
#halloween-overlay {
  position: fixed;
  inset: 0;
  pointer-events: none;
  overflow: hidden;
  z-index: 9999;
}

/* Common style */
.halloween-elem {
  position: absolute;
  will-change: transform, opacity;
  user-select: none;
  animation-timing-function: ease-in-out;
  opacity: 0.9;
}

/* SVG sizes */
.halloween-bat svg { width: 60px; height: 40px; }
.halloween-ghost svg { width: 70px; height: 80px; filter: drop-shadow(0 0 10px rgba(255,255,255,0.7)); }

/* Animations */
@keyframes float-across {
  0% { transform: translateX(110vw) translateY(0); opacity: 1; }
  100% { transform: translateX(-20vw) translateY(-5vh); opacity: 0; }
}

@keyframes float-up {
  0% { transform: translateY(100vh); opacity: 0; }
  20% { opacity: 1; }
  100% { transform: translateY(-20vh); opacity: 0; }
}
</style>

<div id="halloween-overlay" aria-hidden="true"></div>

<script>
(() => {
  const overlay = document.getElementById('halloween-overlay');
  const svgs = {
    bat: `<svg viewBox="0 0 64 40" xmlns="http://www.w3.org/2000/svg"><path d="M2 20 C10 4,22 4,32 18 C42 4,54 4,62 20 C54 14,46 16,32 24 C18 16,10 14,2 20Z" fill="#111"/></svg>`,
    ghost: `<svg viewBox="0 0 64 80" xmlns="http://www.w3.org/2000/svg"><path d="M32 4 C18 4 8 14 8 28 V60 C8 70 16 76 20 70 C24 66 28 70 32 66 C36 70 40 66 44 70 C48 76 56 70 56 60 V28 C56 14 46 4 32 4 Z" fill="white" stroke="#ccc" stroke-width="1"/><circle cx="24" cy="34" r="3" fill="#333"/><circle cx="40" cy="34" r="3" fill="#333"/></svg>`
  };
  const rand = (min, max) => Math.random() * (max - min) + min;

  function spawn(type) {
    const el = document.createElement('span');
    el.className = `halloween-elem halloween-${type}`;
    el.innerHTML = svgs[type];
    overlay.appendChild(el);

    if (type === 'bat') {
      el.style.top = `${rand(10, 80)}vh`;
      el.style.left = '-10vw';
      el.style.animation = `float-across ${rand(8,14)}s linear forwards`;
    } else {
      el.style.left = `${rand(10, 90)}vw`;
      el.style.top = '10vh';
      el.style.animation = `float-up ${rand(10,18)}s ease-in forwards`;
    }

    el.addEventListener('animationend', () => el.remove());
  }

  function loop() {
    const p = Math.random();
    if (p < 0.5) spawn('bat');
    else spawn('ghost');
  }

  setInterval(loop, 800);
  for (let i = 0; i < 10; i++) loop();
})();
</script> -->



@endsection
