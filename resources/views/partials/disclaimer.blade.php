{{-- Disclaimer Card --}}
<div class="container mt-4">

    <div class="disclaimer-card">

        {{-- Header --}}
        <div class="disclaimer-header">

            <div class="d-flex align-items-center gap-2">

                <div class="disclaimer-icon">

                    <i class="bi bi-shield-exclamation"></i>

                </div>

                <div>

                    <div class="disclaimer-title">

                        Disclaimer

                    </div>

                    <div class="disclaimer-subtitle">

                        Legal notice & platform responsibility

                    </div>

                </div>

            </div>

        </div>

        {{-- Body --}}
        <div class="disclaimer-body">

            <div class="mb-3">

                <a href="{{ route('terms.of.service') }}"
                   class="tos-link">

                    <i class="bi bi-file-earmark-text me-1"></i>

                    Terms of Service

                </a>

            </div>

            <div class="disclaimer-text">

                <p>
                    Niciunul dintre fișierele indexate pe această platformă nu este găzduit pe serverele noastre.
                    Toate link-urile și conținutul indexat sunt furnizate exclusiv de utilizatorii site-ului,
                    iar administratorii platformei nu își asumă responsabilitatea pentru acțiunile și materialele distribuite de aceștia.
                </p>

                <p>
                    Accesul și utilizarea acestui serviciu trebuie să respecte legile și reglementările în vigoare.
                    Orice utilizare a platformei pentru scopuri ilegale este strict interzisă și poate atrage măsuri disciplinare,
                    inclusiv dezactivarea permanentă a accesului.
                </p>

                <p class="mb-0">
                    Recomandăm tuturor utilizatorilor să se informeze și să respecte legislația aplicabilă
                    în domeniul drepturilor de autor și distribuției de conținut.
                </p>

            </div>

        </div>

    </div>

</div>

<style>

/* =========================================
   CARD
========================================= */

.disclaimer-card{

    position:relative;

    overflow:hidden;

    border-radius:24px;

    background:
        linear-gradient(
            145deg,
            rgba(255,255,255,.045),
            rgba(255,255,255,.02)
        );

    border:
        1px solid rgba(255,255,255,.06);

    backdrop-filter:blur(14px);

    box-shadow:
        0 12px 32px rgba(0,0,0,.22);
}

/* =========================================
   HEADER
========================================= */

.disclaimer-header{

    padding:1.1rem 1.35rem;

    border-bottom:
        1px solid rgba(255,255,255,.05);
}

.disclaimer-icon{

    width:40px;
    height:40px;

    border-radius:14px;

    display:flex;

    align-items:center;
    justify-content:center;

    background:
        linear-gradient(
            135deg,
            #f59e0b,
            #d97706
        );

    color:white;

    font-size:1rem;

    box-shadow:
        0 8px 20px rgba(245,158,11,.25);
}

.disclaimer-title{

    color:white;

    font-size:1rem;

    font-weight:700;
}

.disclaimer-subtitle{

    color:rgba(255,255,255,.45);

    font-size:.75rem;

    margin-top:2px;
}

/* =========================================
   BODY
========================================= */

.disclaimer-body{

    padding:1.25rem 1.35rem;
}

/* =========================================
   TERMS LINK
========================================= */

.tos-link{

    display:inline-flex;

    align-items:center;

    padding:.55rem .9rem;

    border-radius:12px;

    background:
        rgba(255,255,255,.04);

    border:
        1px solid rgba(255,255,255,.05);

    color:#cbd5e1;

    text-decoration:none;

    font-size:.88rem;

    font-weight:600;

    transition:.2s ease;
}

.tos-link:hover{

    background:
        rgba(59,130,246,.12);

    border-color:
        rgba(59,130,246,.18);

    color:#93c5fd;

    transform:
        translateY(-2px);
}

/* =========================================
   TEXT
========================================= */

.disclaimer-text{

    color:#d1d5db;

    font-size:.92rem;

    line-height:1.8;
}

.disclaimer-text p{

    margin-bottom:1rem;
}

/* =========================================
   MOBILE
========================================= */

@media(max-width:768px){

    .disclaimer-header{

        padding:1rem;
    }

    .disclaimer-body{

        padding:1rem;
    }

    .disclaimer-text{

        font-size:.88rem;

        line-height:1.7;
    }

}

</style>