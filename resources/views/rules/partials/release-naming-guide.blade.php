<section class="release-guide" aria-labelledby="release-guide-title">
    <header class="release-guide-header">
        <span class="release-guide-icon"><i class="bi bi-fonts" aria-hidden="true"></i></span>
        <div>
            <p class="release-guide-eyebrow">Upload standards</p>
            <h3 id="release-guide-title">Release Naming Guide</h3>
            <p>Consistent names make releases easier to find, compare, and seed.</p>
        </div>
    </header>

    <div class="release-guide-notice">
        <i class="bi bi-shield-exclamation" aria-hidden="true"></i>
        <p><strong>Every upload must follow this guide.</strong> Badly named torrents will be renamed. Repeat offenders will lose upload rights.</p>
    </div>
    <div class="release-guide-preserve">
        <i class="bi bi-files" aria-hidden="true"></i>
        <p><strong>Change the displayed torrent name only.</strong> Keep every file and folder inside the torrent exactly as released, including dots, so the release stays cross-seedable.</p>
    </div>

    <details class="release-guide-chapter" open>
        <summary><span class="release-guide-number">01</span><span>General rules <small>All categories</small></span><i class="bi bi-chevron-down" aria-hidden="true"></i></summary>
        <div class="release-guide-chapter-body">
            <h4>Spaces between words. Dots inside values.</h4>
            <p>Use spaces between title words and release tags. Do not use a dot-separated release name or replace every dot with a space: meaningful dots must stay intact.</p>
            <div class="release-guide-table-wrap">
                <table class="release-guide-table">
                    <caption>Keep these values intact</caption>
                    <thead><tr><th scope="col">Value</th><th scope="col">Keep</th><th scope="col">Avoid</th></tr></thead>
                    <tbody>
                        <tr><th scope="row">Audio channels</th><td><code>DDP5.1</code>, <code>7.1</code></td><td><code>DDP5 1</code></td></tr>
                        <tr><th scope="row">Codecs</th><td><code>H.264</code>, <code>H.265</code></td><td><code>H 264</code></td></tr>
                        <tr><th scope="row">Versions</th><td><code>v27.1.0</code></td><td><code>v27 1 0</code></td></tr>
                        <tr><th scope="row">Dates</th><td><code>2026.09.25</code></td><td><code>2026 09 25</code></td></tr>
                        <tr><th scope="row">Initials</th><td><code>J.R.R. Tolkien</code></td><td><code>J R R Tolkien</code></td></tr>
                    </tbody>
                </table>
            </div>

            <h4>Keep names clean and readable</h4>
            <ul>
                <li>Allowed characters: <strong>A–Z, a–z, 0–9, spaces, hyphens, and meaningful dots</strong> as shown above. The <code>+</code> in the verified <code>HDR10+</code> tag is the only symbol exception.</li>
                <li>No brackets <code>( ) [ ] { }</code>, commas, colons, apostrophes, quotation marks, ampersands, accents, or decorative symbols such as <code>™ ® ! ?</code>.</li>
                <li>Use single spaces. No spaces or hyphens at the beginning or end.</li>
                <li>For movies and TV, use the IMDb English title in <strong>Title Case</strong>, applying the character rules below.</li>
            </ul>
            <div class="release-guide-table-wrap">
                <table class="release-guide-table">
                    <caption>Clean up title punctuation</caption>
                    <thead><tr><th scope="col">Original</th><th scope="col">Torrent title</th><th scope="col">Rule</th></tr></thead>
                    <tbody>
                        <tr><td><code>A &amp; B</code></td><td><code>A and B</code></td><td>Replace &amp; with and</td></tr>
                        <tr><td><code>Schindler's List</code></td><td><code>Schindlers List</code></td><td>Drop apostrophes</td></tr>
                        <tr><td><code>Amélie</code></td><td><code>Amelie</code></td><td>Remove accents</td></tr>
                        <tr><td><code>Mission: Impossible</code></td><td><code>Mission Impossible</code></td><td>Drop colons</td></tr>
                    </tbody>
                </table>
            </div>

            <h4>Credit the release accurately</h4>
            <ul>
                <li>The release group goes at the very end, directly after a hyphen with no spaces: <code>DDP5.1-GROUP</code>.</li>
                <li>If there is no group, omit it. Never invent one. <code>-NOGROUP</code>, <code>-Unknown</code>, <code>-NoGrp</code>, and your own username are not allowed.</li>
                <li>Never add website names, uploader names, tracker tags, or emojis, including <code>www.</code>, <code>[TGx]</code>, or copied <code>-RARBG</code> tags. Credit only the actual release group.</li>
                <li>Only tag what the release actually contains. Check MediaInfo; do not guess.</li>
                <li>Re-tagging another group's release is forbidden.</li>
            </ul>
        </div>
    </details>

    <details class="release-guide-chapter">
        <summary><span class="release-guide-number">02</span><span>Movies <small>Single releases and collections</small></span><i class="bi bi-chevron-down" aria-hidden="true"></i></summary>
        <div class="release-guide-chapter-body">
            <div class="release-guide-format"><span>Required order</span><code>Title Year Resolution Codec Audio-GROUP</code></div>
            <p class="release-guide-note">The group suffix is optional when the release has no group. These examples show naming syntax; always verify the tags against your own release.</p>
            <div class="release-guide-examples" aria-label="Movie naming examples">
                <code>Oppenheimer 2023 1080p x264 DDP5.1-GROUP</code>
                <code>Dune Part Two 2024 2160p x265 TrueHD 7.1 Atmos-GROUP</code>
                <code>The Holdovers 2023 720p x264 AAC2.0</code>
                <code>Casablanca 1942 1080p H.264 FLAC1.0-GROUP</code>
            </div>

            <h4>Build the name in this order</h4>
            <dl class="release-guide-parts">
                <div><dt>Title</dt><dd>The IMDb English title, cleaned up using the general rules.</dd></div>
                <div><dt>Year</dt><dd>The film's first release year, rather than the year the rip was made.</dd></div>
                <div><dt>Resolution</dt><dd><code>2160p</code>, <code>1080p</code>, <code>720p</code>, <code>576p</code>, <code>480p</code>.</dd></div>
                <div><dt>Codec</dt><dd><code>x264</code>, <code>x265</code>, <code>H.264</code>, <code>H.265</code>, <code>AV1</code>, <code>XviD</code>, <code>VC-1</code>, <code>MPEG-2</code>.</dd></div>
                <div><dt>Audio</dt><dd>Codec and channels, for example <code>AAC2.0</code>, <code>DD5.1</code>, <code>DDP5.1</code>, <code>DTS5.1</code>, <code>DTS-HD MA 5.1</code>, <code>TrueHD 7.1</code>, <code>TrueHD 7.1 Atmos</code>, <code>FLAC2.0</code>, <code>LPCM2.0</code>, <code>Opus5.1</code>.</dd></div>
            </dl>

            <h4>Optional tags: include only when true</h4>
            <ul>
                <li><strong>After the year:</strong> edition tags such as <code>Extended</code>, <code>Directors Cut</code>, <code>Theatrical</code>, <code>Unrated</code>, <code>Remastered</code>, <code>IMAX</code>; non-English audio tags such as <code>FRENCH</code>, <code>GERMAN</code>, <code>MULTi</code>; and fixes such as <code>REPACK</code>, <code>PROPER</code>.</li>
                <li><strong>Immediately after the resolution:</strong> <code>HDR</code>, <code>HDR10+</code>, <code>DV</code>, or <code>DV HDR</code>.</li>
            </ul>
            <div class="release-guide-examples" aria-label="Optional movie tags">
                <code>Movie Title 2026 Directors Cut 2160p DV HDR x265 TrueHD 7.1 Atmos-GROUP</code>
                <code>Amelie 2001 FRENCH 1080p x264 DTS5.1-GROUP</code>
            </div>

            <h4>Movie packs and collections</h4>
            <p>Use the collection title and the range of original film release years.</p>
            <div class="release-guide-examples"><code>The Matrix Collection 1999-2021 1080p x264 DDP5.1-GROUP</code></div>
        </div>
    </details>

    <details class="release-guide-chapter">
        <summary><span class="release-guide-number">03</span><span>TV <small>Episodes, seasons, and complete series</small></span><i class="bi bi-chevron-down" aria-hidden="true"></i></summary>
        <div class="release-guide-chapter-body">
            <div class="release-guide-format"><span>Required order</span><code>Show Name S01E01 Resolution Codec Audio-GROUP</code></div>
            <div class="release-guide-table-wrap">
                <table class="release-guide-table">
                    <caption>Choose the correct episode or pack marker</caption>
                    <thead><tr><th scope="col">Release type</th><th scope="col">Example name</th></tr></thead>
                    <tbody>
                        <tr><th scope="row">Single episode</th><td><code>The Bear S03E02 1080p H.264 DDP5.1-GROUP</code></td></tr>
                        <tr><th scope="row">Double episode</th><td><code>The Bear S03E02E03 1080p H.264 DDP5.1-GROUP</code></td></tr>
                        <tr><th scope="row">Season pack</th><td><code>Slow Horses S04 2160p H.265 DDP5.1-GROUP</code></td></tr>
                        <tr><th scope="row">Multi-season pack</th><td><code>Friends S01-S10 1080p x265 AAC5.1-GROUP</code></td></tr>
                        <tr><th scope="row">Complete series</th><td><code>Friends Complete 1080p x265 AAC5.1-GROUP</code></td></tr>
                        <tr><th scope="row">Daily show, no group</th><td><code>The Daily Show 2026.09.25 720p H.264 AAC2.0</code></td></tr>
                    </tbody>
                </table>
            </div>
            <ul>
                <li>Always use two digits for season and episode numbers: <code>S01E05</code>, rather than <code>S1E5</code>.</li>
                <li>Add a year to the show name only when needed to distinguish shows with the same title: <code>Doctor Who 2005 S01E01</code>.</li>
                <li>The movie rules for <code>REPACK</code>, <code>PROPER</code>, language, and HDR also apply. Put fix and language tags after the episode or pack marker, and HDR tags immediately after the resolution.</li>
            </ul>

            <h4>Episode titles: single episodes only</h4>
            <p>An episode title is optional. Place it immediately after the episode number, before any optional release tags. Use Title Case and the same character rules as the rest of the name.</p>
            <p><strong>Do not add episode titles to double episodes, season packs, multi-season packs, or complete series.</strong></p>
            <div class="release-guide-examples" aria-label="Single episode title examples">
                <code>Doctor Who 2005 S01E01 Rose 1080p H.264 AAC2.0-GROUP</code>
                <code>Show Name S02E04 Dont Look Back 1080p H.264 DDP5.1-GROUP</code>
                <code>Show Name S01E07 Part One The Return 720p x264 AAC2.0</code>
            </div>
            <p class="release-guide-note">For example, <em>Don't Look Back</em> becomes <code>Dont Look Back</code>, and <em>Part One: The Return</em> becomes <code>Part One The Return</code>.</p>
        </div>
    </details>

    <div class="release-guide-checklist">
        <i class="bi bi-check2-circle" aria-hidden="true"></i>
        <p><strong>Before you upload:</strong> check the title, spacing, year or episode marker, MediaInfo tags, and original group credit. Leave the release files and folders untouched.</p>
    </div>
</section>
