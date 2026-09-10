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
   DISCLAIMER CARD
========================================= */

.disclaimer-card {

    position: relative;

    overflow: hidden;

    background:
        linear-gradient(
            135deg,
            rgba(22, 32, 51, .95),
            rgba(15, 23, 42, .84)
        );

    border: 1px solid var(--ui-border);

    border-radius: .9rem;

    box-shadow:
        0 10px 28px rgba(0, 0, 0, .24);
}

.disclaimer-card::before {

    content: "";

    position: absolute;

    left: 0;
    top: 0;
    bottom: 0;

    width: 3px;

    background:
        linear-gradient(
            180deg,
            var(--ui-accent),
            var(--ui-accent-strong)
        );

    opacity: .9;

    pointer-events: none;
}


/* =========================================
   HEADER
========================================= */

.disclaimer-header {

    padding: .85rem 1rem;

    border-bottom:
        1px solid var(--ui-border);

    background: transparent;
}

.disclaimer-icon {

    width: 36px;
    height: 36px;

    display: flex;

    align-items: center;
    justify-content: center;

    flex-shrink: 0;

    border-radius: .65rem;

    color: var(--ui-accent);

    background:
        rgba(45, 212, 191, .08);

    border:
        1px solid rgba(45, 212, 191, .18);

    font-size: 15px;
}

.disclaimer-title {

    color: #fff;

    font-size: 14px;

    font-weight: 700;

    line-height: 1.25;
}

.disclaimer-subtitle {

    color:
        rgba(255, 255, 255, .55);

    font-size: 13px;

    margin-top: .15rem;
}


/* =========================================
   BODY
========================================= */

.disclaimer-body {

    padding: 1rem;
}

.disclaimer-text {

    color:
        rgba(255, 255, 255, .72);

    font-size: 14px;

    line-height: 1.7;
}

.disclaimer-text p {

    margin-bottom: 1rem;
}

.disclaimer-text p:last-child {

    margin-bottom: 0;
}


/* =========================================
   SUBTLE TEXT HIGHLIGHT
========================================= */

.disclaimer-text strong {

    color: var(--ui-accent);

    font-weight: 700;
}


/* =========================================
   OPTIONAL TERMS LINK
========================================= */

.tos-link {

    display: inline-flex;

    align-items: center;

    gap: .4rem;

    padding: .4rem .7rem;

    color: var(--ui-accent);

    text-decoration: none;

    font-size: 13px;

    font-weight: 600;

    background:
        rgba(45, 212, 191, .05);

    border:
        1px solid var(--ui-border);

    border-radius: .55rem;

    transition:
        background .18s ease,
        border-color .18s ease,
        transform .18s ease,
        color .18s ease;
}

.tos-link:hover {

    color: var(--ui-accent-strong);

    background:
        rgba(45, 212, 191, .09);

    border-color:
        rgba(45, 212, 191, .25);

    transform:
        translateY(-1px);
}


/* =========================================
   MOBILE
========================================= */

@media (max-width: 768px) {

    .disclaimer-header {

        padding: .75rem;
    }

    .disclaimer-body {

        padding: .85rem;
    }

    .disclaimer-icon {

        width: 34px;
        height: 34px;

        font-size: 14px;
    }

    .disclaimer-title {

        font-size: 14px;
    }

    .disclaimer-subtitle {

        font-size: 13px;
    }

    .disclaimer-text {

        font-size: 14px;

        line-height: 1.65;
    }

}

</style>