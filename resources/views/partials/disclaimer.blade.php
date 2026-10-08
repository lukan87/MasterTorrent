{{-- Disclaimer Card --}}
<div class="container-fluid px-0 mt-4">

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
                    None of the files indexed on this platform are hosted on our servers.
                    All links and indexed content are provided exclusively by users of the site,
                    and the platform administrators are not responsible for the actions or materials
                    distributed by individual users.
                </p>

                <p>
                    Access to and use of this service must comply with all applicable laws and regulations.
                    Any use of the platform for illegal purposes is strictly prohibited and may result
                    in disciplinary action, including permanent suspension of access.
                </p>

                <p class="mb-0">
                    We strongly encourage all users to familiarise themselves with and comply with
                    applicable copyright laws and regulations concerning the distribution and sharing
                    of content.
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
            var(--theme-surface, rgba(14,21,33,.95)),
            var(--theme-surface, rgba(10,15,27,.84))
        );

    border: 1px solid var(--ui-border);

    border-radius: .9rem;

    box-shadow:
        0 10px 28px var(--theme-shadow, rgba(0, 0, 0, .24));
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
            var(--theme-teal-action, var(--ui-accent)),
            var(--theme-teal-action, var(--ui-accent-strong))
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
        var(--theme-teal-soft, rgba(45, 212, 191, .08));

    border:
        1px solid var(--theme-teal-border, rgba(45, 212, 191, .18));

    font-size: 15px;
}

.disclaimer-title {

    color: var(--theme-text, #fff);

    font-size: var(--site-font-body, 13px);

    font-weight: 700;

    line-height: 1.25;
}

.disclaimer-subtitle {

    color:
        var(--theme-muted, rgba(255, 255, 255, .55));

    font-size: var(--site-font-body, 13px);

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
        var(--theme-muted, rgba(255, 255, 255, .72));

    font-size: var(--site-font-body, 13px);

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

    font-size: var(--site-font-body, 13px);

    font-weight: 600;

    background:
        var(--theme-teal-soft, rgba(45, 212, 191, .05));

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
        var(--theme-teal-soft, rgba(45, 212, 191, .09));

    border-color:
        var(--theme-teal-border, rgba(45, 212, 191, .25));

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

        font-size: var(--site-font-body, 13px);
    }

    .disclaimer-subtitle {

        font-size: var(--site-font-body, 13px);
    }

    .disclaimer-text {

        font-size: var(--site-font-body, 13px);

        line-height: 1.65;
    }

}

</style>
