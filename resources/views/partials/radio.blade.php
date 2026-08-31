<style>
.radio-wrapper {
    display: grid;
    grid-template-columns: 3fr 1fr; /* 9 / 3 */
    gap: 16px;
    align-items: center;
}

/* Player */
.radio-player {
    background: #0f172a;
    border-radius: 14px;
    padding: 10px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.3);
}

/* Links container INLINE */
.radio-links {
    display: flex;
    flex-direction: row;
    gap: 10px;
    justify-content: center;
    align-items: center;
    background: #0f172a;
    border-radius: 14px;
    padding: 12px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.3);
}

/* Link buttons */
.radio-links a {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 10px;
    border-radius: 10px;
    background: #1e293b;
    transition: all 0.25s ease;
}

.radio-links a:hover {
    background: #334155;
    transform: translateY(-2px) scale(1.05);
    box-shadow: 0 6px 15px rgba(0,0,0,0.4);
}

.radio-links img {
    height: 28px;
}

/* Mobile */
@media (max-width: 768px) {
    .radio-wrapper {
        grid-template-columns: 1fr;
    }
}
</style>

<div class="radio-wrapper">

    <!-- Player (9) -->
    <div class="radio-player glass">
        <iframe 
            src="https://radio.sonicpanel.ro/cp/widgets/player/single/?p=8022"
            height="110"
            width="100%"
            scrolling="no"
            style="border:none; border-radius:10px;">
        </iframe>
    </div>

    <!-- Links (3) INLINE -->
    <div class="radio-links glass">
        <a href="https://radio.sonicpanel.ro/cp/links.php?p=8022&m=pls" data-bs-toggle="tooltip" title="Download for Winamp">
            <img src="https://radio.sonicpanel.ro/cp/inc/images/players/winamp2.png" alt="Winamp">
        </a>

        <a href="https://radio.sonicpanel.ro/cp/links.php?p=8022&m=asx" data-bs-toggle="tooltip" title="Download for Media Player">
            <img src="https://radio.sonicpanel.ro/cp/inc/images/players/mediaplayer.png" alt="Media Player">
        </a>

        <a href="https://radio.sonicpanel.ro/cp/links.php?p=8022&m=pls" data-bs-toggle="tooltip" title="Download for VLC">
            <img src="https://radio.sonicpanel.ro/cp/inc/images/players/vlc.png" alt="VLC">
        </a>
    </div>

</div>