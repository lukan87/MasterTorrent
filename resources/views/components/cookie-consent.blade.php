@if(auth()->check() && !auth()->user()->cookie_consent)

<div id="cookie-overlay" class="cookie-overlay">
    <div class="cookie-modal">

        <div class="cookie-icon">🍪</div>

        <h4 class="mb-3 fw-bold">Your Privacy Matters</h4>

        <p class="cookie-text">
            We use essential cookies to keep your account secure and the platform running smoothly.
            With your permission, we’d also like to use optional cookies to improve performance
            and enhance your experience.
        </p>

        <div class="cookie-buttons">
            <button class="btn btn-light btn-sm reject-btn" onclick="rejectCookies()">
                Continue with Essential Only
            </button>

            <button class="btn btn-success btn-sm accept-btn" onclick="acceptCookies()">
                ✓ Accept All Cookies
            </button>
        </div>

        <div class="cookie-footer">
            <a href="{{ route('privacy.policy') }}">Privacy Policy</a>
        </div>

    </div>
</div>



<style>
.cookie-overlay {
    position: fixed;
    inset: 0;
    background: radial-gradient(circle at center, rgba(0, 0, 0, 0.335), rgba(0,0,0,0.95));
    backdrop-filter: blur(6px);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 9999;
    animation: fadeInBg 0.3s ease;
}

.cookie-modal {
    background: linear-gradient(145deg, #1f1f1f, #171717);
    color: #fff;
    max-width: 520px;
    width: 92%;
    padding: 40px;
    border-radius: 18px;
    text-align: center;
    box-shadow: 0 20px 60px rgba(0,0,0,0.6);
    animation: slideUp 0.4s ease;
}

.cookie-icon {
    font-size: 40px;
    margin-bottom: 15px;
}

.cookie-text {
    color: #cfcfcf;
    font-size: 0.95rem;
    line-height: 1.6;
    margin-bottom: 25px;
}

.cookie-buttons {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.accept-btn {
    padding: 10px;
    font-weight: 600;
    border-radius: 8px;
    transition: all 0.2s ease;
}

.accept-btn:hover {
    transform: scale(1.03);
}

.reject-btn {
    border-radius: 8px;
    opacity: 0.8;
}

.cookie-footer {
    margin-top: 20px;
    font-size: 0.8rem;
}

.cookie-footer a {
    color: #aaa;
    text-decoration: none;
}

.cookie-footer a:hover {
    color: #fff;
    text-decoration: underline;
}

@keyframes slideUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

@keyframes fadeInBg {
    from { opacity: 0; }
    to { opacity: 1; }
}
</style>

<script>
function acceptCookies(){
    sendConsent('accepted');
}

function rejectCookies(){
    sendConsent('rejected');
}

function sendConsent(value){
    fetch("{{ route('cookie.consent') }}", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": "{{ csrf_token() }}"
        },
        body: JSON.stringify({ value: value })
    })
    .then(() => location.reload());
}
</script>
@endif