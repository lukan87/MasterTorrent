@extends('layouts.app')

@section('content')

<div class="container-fluid seedbox-form-page py-3">

    <div class="seedbox-form-card">

        <div class="seedbox-form-header">
            <div class="seedbox-form-icon">
                <i class="bi bi-plus-circle-fill"></i>
            </div>
            <div>
                <h2 class="seedbox-form-title">Add New Seedbox</h2>
                <p class="seedbox-form-subtitle">Connect your rTorrent / ruTorrent seedbox to FileIplay.</p>
            </div>
        </div>

        <div class="seedbox-form-body">

            <form action="{{ route('seedboxes.store') }}" method="POST">
                @csrf

                <div class="seedbox-field">
                    <label for="name" class="seedbox-label">
                        <i class="bi bi-hdd-network"></i>
                        Name
                    </label>
                    <input
                        class="form-control seedbox-input"
                        type="text"
                        id="name"
                        name="name"
                        placeholder="E.g: My Seedbox"
                        value="{{ old('name') }}"
                        required
                    >
                </div>

                <div class="seedbox-field">
                    <label for="server" class="seedbox-label">
                        <i class="bi bi-server"></i>
                        Server
                    </label>
                    <input
                        class="form-control seedbox-input"
                        type="text"
                        id="server"
                        name="address"
                        placeholder="https://my.ultraseedbox.net/rutorrent/plugins/httprpc/action.php"
                        value="{{ old('address') }}"
                        required
                    >
                    <div class="seedbox-help">
                        This must be the URL to your rTorrent RPC2 endpoint or ruTorrent RPC plugin.
                        Example:
                        <code>https://seedbox/path/to/rutorrent/plugins/httprpc/action.php</code>
                    </div>
                </div>

                <div class="seedbox-field">
                    <label for="username" class="seedbox-label">
                        <i class="bi bi-person-circle"></i>
                        Username
                    </label>
                    <input
                        class="form-control seedbox-input"
                        type="text"
                        id="username"
                        name="username"
                        value="{{ old('username') }}"
                        required
                    >
                </div>

                <div class="seedbox-field">
                    <label for="password" class="seedbox-label">
                        <i class="bi bi-key-fill"></i>
                        Password
                    </label>
                    <input
                        class="form-control seedbox-input"
                        type="password"
                        id="password"
                        name="password"
                        required
                    >
                </div>

                <div class="seedbox-field">
                    <label for="auth" class="seedbox-label">
                        <i class="bi bi-shield-lock-fill"></i>
                        Authentication
                    </label>
                    <select
                        class="form-select seedbox-input seedbox-select"
                        id="auth"
                        name="auth_type"
                        required
                    >
                        <option value="basic" {{ old('auth_type') === 'basic' ? 'selected' : '' }}>Basic</option>
                        <option value="digest" {{ old('auth_type') === 'digest' ? 'selected' : '' }}>Digest</option>
                    </select>
                </div>

                <div class="seedbox-form-actions">
                    <button type="submit" class="seedbox-btn seedbox-btn-primary">
                        <i class="bi bi-check-circle-fill"></i>
                        Submit
                    </button>

                    <a href="{{ route('seedboxes.index') }}" class="seedbox-btn seedbox-btn-secondary">
                        <i class="bi bi-arrow-left-circle"></i>
                        Cancel
                    </a>
                </div>
            </form>

            @if($errors->any())
                <div class="seedbox-alert seedbox-alert-danger">
                    <div class="seedbox-alert-icon">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                    </div>
                    <div>
                        <strong>Please check the following:</strong>
                        <ul class="mb-0 mt-1">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            @if(session('success'))
                <div class="seedbox-alert seedbox-alert-success">
                    <div class="seedbox-alert-icon">
                        <i class="bi bi-check-circle-fill"></i>
                    </div>
                    <div>{{ session('success') }}</div>
                </div>
            @endif

            @if(session('error'))
                <div class="seedbox-alert seedbox-alert-danger">
                    <div class="seedbox-alert-icon">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                    </div>
                    <div>{{ session('error') }}</div>
                </div>
            @endif

        </div>
    </div>

</div>

<style>
    .seedbox-form-page {
        max-width: 1200px;
        margin: 0 auto;
    }

    .seedbox-form-card {
        position: relative;
        overflow: hidden;
        background: linear-gradient(135deg, rgba(22, 32, 51, .95), rgba(15, 23, 42, .84));
        border: 1px solid var(--ui-border, rgba(148, 163, 184, .16));
        border-radius: .9rem;
        box-shadow: 0 12px 32px rgba(0, 0, 0, .28);
    }

    .seedbox-form-card::before {
        content: "";
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 3px;
        background: var(--ui-accent, #22d3c5);
        opacity: .9;
    }

    .seedbox-form-header {
        display: flex;
        align-items: center;
        gap: .8rem;
        padding: 1rem 1.15rem;
        border-bottom: 1px solid var(--ui-border, rgba(148, 163, 184, .16));
        background: rgba(15, 23, 42, .34);
    }

    .seedbox-form-icon {
        width: 40px;
        height: 40px;
        flex: 0 0 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: .65rem;
        color: var(--ui-accent, #22d3c5);
        background: rgba(34, 211, 197, .08);
        border: 1px solid rgba(34, 211, 197, .2);
        font-size: 18px;
    }

    .seedbox-form-title {
        margin: 0;
        color: #f1f5f9;
        font-size: 14px;
        font-weight: 700;
        line-height: 1.3;
    }

    .seedbox-form-subtitle {
        margin: .18rem 0 0;
        color: #94a3b8;
        font-size: 13px;
        line-height: 1.45;
    }

    .seedbox-form-body {
        padding: 1.15rem;
    }

    .seedbox-field {
        margin-bottom: 1rem;
    }

    .seedbox-label {
        display: flex;
        align-items: center;
        gap: .4rem;
        margin-bottom: .4rem;
        color: #cbd5e1;
        font-size: 13px;
        font-weight: 600;
    }

    .seedbox-label i {
        color: var(--ui-accent, #22d3c5);
        font-size: 13px;
    }

    .seedbox-input {
        min-height: 40px;
        color: #e2e8f0 !important;
        background: rgba(15, 23, 42, .72) !important;
        border: 1px solid rgba(148, 163, 184, .2) !important;
        border-radius: .55rem !important;
        box-shadow: none !important;
        font-size: 14px !important;
    }

    .seedbox-input::placeholder {
        color: #64748b !important;
    }

    .seedbox-input:focus {
        color: #f8fafc !important;
        background: rgba(15, 23, 42, .9) !important;
        border-color: var(--ui-accent, #22d3c5) !important;
        box-shadow: 0 0 0 2px rgba(34, 211, 197, .08) !important;
    }

    .seedbox-select {
        cursor: pointer;
    }

    .seedbox-select option {
        color: #e2e8f0;
        background: #0f172a;
    }

    .seedbox-help {
        margin-top: .4rem;
        color: #64748b;
        font-size: 12px;
        line-height: 1.5;
    }

    .seedbox-help code {
        color: #94a3b8;
        font-size: 12px;
        word-break: break-all;
    }

    .seedbox-form-actions {
        display: flex;
        gap: .55rem;
        padding-top: .2rem;
        margin-top: .25rem;
    }

    .seedbox-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: .4rem;
        min-height: 38px;
        padding: .45rem .8rem;
        border-radius: .55rem;
        font-size: 13px;
        font-weight: 600;
        text-decoration: none;
        transition: all .18s ease;
    }

    .seedbox-btn-primary {
        color: #061311;
        background: var(--ui-accent, #22d3c5);
        border: 1px solid var(--ui-accent, #22d3c5);
    }

    .seedbox-btn-primary:hover {
        color: #061311;
        filter: brightness(1.06);
        transform: translateY(-1px);
    }

    .seedbox-btn-secondary {
        color: #cbd5e1;
        background: rgba(51, 65, 85, .42);
        border: 1px solid rgba(148, 163, 184, .2);
    }

    .seedbox-btn-secondary:hover {
        color: #fff;
        background: rgba(71, 85, 105, .55);
        border-color: rgba(148, 163, 184, .3);
    }

    .seedbox-alert {
        display: flex;
        align-items: flex-start;
        gap: .65rem;
        margin-top: 1rem;
        padding: .75rem .85rem;
        border-radius: .6rem;
        font-size: 13px;
        line-height: 1.45;
    }

    .seedbox-alert-icon {
        flex: 0 0 auto;
        margin-top: 1px;
    }

    .seedbox-alert ul {
        padding-left: 1.1rem;
    }

    .seedbox-alert-danger {
        color: #fecaca;
        background: rgba(127, 29, 29, .22);
        border: 1px solid rgba(248, 113, 113, .22);
    }

    .seedbox-alert-success {
        color: #bbf7d0;
        background: rgba(20, 83, 45, .2);
        border: 1px solid rgba(74, 222, 128, .2);
    }

    @media (max-width: 576px) {
        .seedbox-form-page {
            padding-left: .5rem;
            padding-right: .5rem;
        }

        .seedbox-form-header,
        .seedbox-form-body {
            padding: .9rem;
        }

        .seedbox-form-actions {
            flex-direction: column;
        }

        .seedbox-btn {
            width: 100%;
        }
    }
</style>

@endsection
