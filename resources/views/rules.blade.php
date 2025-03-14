@extends('layouts.app')

@section('content')

<div class="container my-5">
    <div class="card shadow-lg">
        <div class="card-header bg-secondary text-white text-center">
            <h2 class="mb-0">Last-Torrents Rules</h2>
        </div>
        <div class="card-body">
            <!-- Tabs Navigation -->
            <ul class="nav nav-tabs" id="rulesTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="ro-tab" data-bs-toggle="tab" data-bs-target="#ro" type="button" role="tab" aria-controls="ro" aria-selected="true">RO</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="en-tab" data-bs-toggle="tab" data-bs-target="#en" type="button" role="tab" aria-controls="en" aria-selected="false">EN</button>
                </li>
            </ul>

            <!-- Tabs Content -->
            <div class="tab-content mt-3" id="rulesTabsContent">
                <!-- Romanian Tab -->
                <div class="tab-pane fade show active" id="ro" role="tabpanel" aria-labelledby="ro-tab">
                    <h4 class="text-primary">Respectarea celorlalți utilizatori</h4>
                    <p>Este interzisă insultarea, hărțuirea sau discriminarea pe baza etniei, religiei, genului sau a altor criterii. Respectul reciproc este obligatoriu.</p>

                    <h4 class="text-primary">Partajarea fișierelor (seeding)</h4>
                    <ul>
                        <li>Fiecare utilizator este obligat să mențină un raport de <strong>cel puțin 1.0</strong> (raportul între upload și download).</li>
                        <li>Torrentele trebuie să fie păstrate în seeding minim <strong>24 de ore</strong> sau până când se atinge un raport de 1.0.</li>
                    </ul>

                    <h4 class="text-primary">Fișiere permise</h4>
                    <ul>
                        <li>Se pot partaja doar fișiere legale și fără conținut explicit ilegal (programe piratate, conținut care încalcă drepturile de autor etc.).</li>
                        <li>Orice conținut ofensator, pornografic cu minori sau care promovează violența este strict interzis.</li>
                    </ul>

                    <h4 class="text-primary">Conturi și securitate</h4>
                    <ul>
                        <li>Fiecare utilizator are voie să dețină <strong>un singur cont</strong>. Conturile multiple vor fi șterse fără avertisment.</li>
                        <li>Este interzisă partajarea contului sau utilizarea acestuia de către alte persoane.</li>
                    </ul>

                    <h4 class="text-primary">Titluri și descrieri ale torrentelor</h4>
                    <p>Torrentele încărcate trebuie să aibă titluri clare și să includă descrieri detaliate. Torrentele incomplete sau fără informații vor fi șterse.</p>

                    <h4 class="text-primary">Comentarii și forumuri</h4>
                    <ul>
                        <li>Limbajul vulgar, spam-ul și publicitatea sunt interzise în comentarii și pe forumuri.</li>
                        <li>Discuțiile trebuie să fie civilizate și la subiect.</li>
                    </ul>

                    <h4 class="text-primary">Respectarea categoriilor</h4>
                    <p>Torrentele trebuie plasate în categoria corespunzătoare (ex.: filme HD, muzică, aplicații). Torrentele plasate greșit pot fi șterse.</p>

                    <h4 class="text-primary">Viteza de upload și seeding</h4>
                    <p>Dacă utilizați tracker-ul, vă rugăm să setați viteza de upload la un nivel rezonabil pentru a sprijini comunitatea.</p>

                    <h4 class="text-primary">Raportarea problemelor</h4>
                    <p>Utilizatorii pot raporta problemele cu torrentele sau comportamentul neadecvat al altor membri folosind funcția de raportare.</p>

                    <h4 class="text-primary">Consecințe pentru nerespectarea regulilor</h4>
                    <ul>
                        <li><strong>Prima abatere:</strong> avertisment.</li>
                        <li><strong>A doua abatere:</strong> restricționare temporară a contului.</li>
                        <li><strong>Abateri repetate:</strong> suspendarea permanentă a contului.</li>
                    </ul>
                </div>

                <!-- English Tab -->
                <div class="tab-pane fade" id="en" role="tabpanel" aria-labelledby="en-tab">
                    <h4 class="text-primary">Respect for Other Users</h4>
                    <p>Insulting, harassing, or discriminating based on ethnicity, religion, gender, or other criteria is prohibited. Mutual respect is mandatory.</p>

                    <h4 class="text-primary">File Sharing (Seeding)</h4>
                    <ul>
                        <li>Each user is required to maintain a ratio of <strong>at least 1.0</strong> (the ratio of upload to download).</li>
                        <li>Torrents must be seeded for a minimum of <strong>24 hours</strong> or until a ratio of 1.0 is achieved.</li>
                    </ul>

                    <h4 class="text-primary">Allowed Files</h4>
                    <ul>
                        <li>Only legal files and those without explicitly illegal content (pirated software, copyright-infringing content, etc.) are allowed.</li>
                        <li>Any offensive, child-pornographic, or violence-promoting content is strictly prohibited.</li>
                    </ul>

                    <h4 class="text-primary">Accounts and Security</h4>
                    <ul>
                        <li>Each user is allowed to have <strong>only one account</strong>. Multiple accounts will be deleted without warning.</li>
                        <li>Sharing accounts or allowing others to use your account is prohibited.</li>
                    </ul>

                    <h4 class="text-primary">Torrent Titles and Descriptions</h4>
                    <p>Uploaded torrents must have clear titles and detailed descriptions. Incomplete torrents or those lacking information will be deleted.</p>

                    <h4 class="text-primary">Comments and Forums</h4>
                    <ul>
                        <li>Offensive language, spam, and advertisements are prohibited in comments and on forums.</li>
                        <li>Discussions must be civilized and on-topic.</li>
                    </ul>

                    <h4 class="text-primary">Category Compliance</h4>
                    <p>Torrents must be placed in the appropriate category (e.g., HD Movies, Music, Applications). Torrents placed incorrectly may be deleted.</p>

                    <h4 class="text-primary">Upload Speed and Seeding</h4>
                    <p>If you use the tracker, please set a reasonable upload speed to support the community.</p>

                    <h4 class="text-primary">Reporting Issues</h4>
                    <p>Users can report torrent issues or inappropriate behavior of other members using the report function.</p>

                    <h4 class="text-primary">Consequences for Rule Violations</h4>
                    <ul>
                        <li><strong>First Offense:</strong> Warning.</li>
                        <li><strong>Second Offense:</strong> Temporary account restriction.</li>
                        <li><strong>Repeated Offenses:</strong> Permanent account suspension.</li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="card-footer text-center">
            <p class="mb-0">The administrators reserve the right to modify the rules and take additional measures to maintain a safe and friendly community. Follow the rules for an enjoyable experience on the tracker!</p>
        </div>
    </div>
</div>

@endsection
