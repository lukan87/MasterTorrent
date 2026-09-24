@extends('layouts.app')

@section('content')

<style>
/* FileIplay Contact Staff Requests — forum style */
.contact-requests-page {
    width: 100%;
    max-width: 1500px;
    margin: 0 auto;
    padding: 2rem 1rem 3rem;
}

.contact-requests-title {
    color: #f8fafc;
    font-size: 1.35rem;
    font-weight: 700;
    margin: 0 0 1.25rem;
}

.contact-card {
    background: linear-gradient(135deg, rgba(22,32,51,.96), rgba(15,23,42,.92));
    border: 1px solid rgba(148,163,184,.18);
    border-radius: .75rem;
    box-shadow: 0 12px 30px rgba(0,0,0,.22);
    padding: 1rem;
    margin-bottom: .85rem;
    transition: border-color .2s ease, box-shadow .2s ease, transform .2s ease;
}

.contact-card:hover {
    border-color: rgba(45,212,191,.25);
    box-shadow: 0 16px 35px rgba(0,0,0,.28);
    transform: translateY(-1px);
}

.contact-label {
    display: block;
    margin-bottom: .3rem;
    color: #64748b;
    font-size: .72rem;
    font-weight: 700;
    letter-spacing: .65px;
    text-transform: uppercase;
}

.contact-value {
    color: #e2e8f0;
    font-size: .9rem;
    overflow-wrap: anywhere;
}

.contact-value strong {
    color: #f8fafc;
}

.contact-value a {
    color: #2dd4bf;
    text-decoration: none;
    transition: color .18s ease;
}

.contact-value a:hover {
    color: #5eead4;
    text-decoration: underline;
}

.contact-meta {
    color: #94a3b8;
    font-size: .82rem;
}

.status-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: .32rem .62rem;
    border-radius: .45rem;
    font-size: .75rem;
    font-weight: 700;
    white-space: nowrap;
}

.badge-open {
    color: #fecaca;
    background: rgba(127,29,29,.38);
    border: 1px solid rgba(248,113,113,.25);
}

.badge-answered {
    color: #a7f3d0;
    background: rgba(6,78,59,.42);
    border: 1px solid rgba(45,212,191,.25);
}

@media (max-width: 767.98px) {
    .contact-requests-page {
        padding: 1rem .75rem 2rem;
    }

    .contact-requests-title {
        font-size: 1.2rem;
    }

    .contact-card {
        padding: .85rem;
    }

    .contact-label {
        font-size: .68rem;
    }

    .contact-value {
        font-size: .9rem;
    }
}
</style>

<div class="contact-requests-page">

    <h4 class="contact-requests-title">
        📨 Contact Staff Requests
    </h4>

    @foreach($contacts as $c)

        <div class="contact-card">

            <div class="row align-items-center gy-3">

                {{-- ID --}}
                <div class="col-6 col-md-1">
                    <span class="contact-label">Ticket</span>
                    <div class="contact-value">
                        <strong>#{{ $c->id }}</strong>
                    </div>
                </div>

                {{-- EMAIL --}}
                <div class="col-12 col-md-3">
                    <span class="contact-label">Email</span>
                    <div class="contact-value">
                        {{ $c->email }}
                    </div>
                </div>

                {{-- SUBJECT --}}
                <div class="col-12 col-md-2">
                    <span class="contact-label">Subject</span>
                    <div class="contact-value">
                        <strong>
                            <a href="{{ route('contactstaff.show',$c->id) }}">
                                {{ $c->subject }}
                            </a>
                        </strong>
                    </div>
                </div>

                {{-- CREATED --}}
                <div class="col-6 col-md-1">
                    <span class="contact-label">Created</span>
                    <div class="contact-meta">
                        {{ $c->created_at->diffForHumans() }}
                    </div>
                </div>

                {{-- IP --}}
                <div class="col-6 col-md-3">
                    <span class="contact-label">IP</span>
                    <div class="contact-meta">
                        {{ $c->ip ?? '-' }}
                    </div>
                </div>

                {{-- STATUS --}}
                <div class="col-6 col-md-2 text-md-end">
                    @if($c->resolved)
                        <span class="status-badge badge-answered">
                            ✔ Resolved
                        </span>
                    @else
                        <span class="status-badge badge-open">
                            ● Open
                        </span>
                    @endif
                </div>

            </div>

        </div>

    @endforeach

</div>

@endsection
