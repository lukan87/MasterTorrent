@extends('layouts.app')

@section('content')


<div class="my-5">
    <div class="card shadow-lg">
        <div class="card-header bg-secondary text-white text-center">
            <h2 class="mb-0">LastFiles Rules</h2>
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
                    <h4 class="text-primary">Bun venit pe LastFiles!</h4>
                    <p>Citiți cu atenție acest reguli, nerespectarea lor duce la dezactivarea contului.</p>
                    
                    <h4 class="text-primary">1. Reguli Generale - Încălcarea acestor reguli va duce la dezactivarea contului/banIP !</h4>
                    <ul>
                        <li>Nu sfidaţi moderatorii și administratorii cu dorinţele dvs ! Ei folosesc mereu cea mai bună judecată.</li>
                        <li>Conturile multiple nu sunt admise.</li>
                        <li>Sunt interzise conturile ale căror username-uri seamănă sau imită un username al unui membru din staff.</li>
                        <li>Nu uploadaţi torrentele noastre pe alte trackere.</li>
                        <li>Nu vindeţi/nu incercaţi să vindeţi invitaţiile/contul.</li>
                        <li>Este interzisă reclama la alte trackere, via PM, Forum, Shoutbox etc.</li>
                        <li>Nu sunt admise cererile de invitații, indiferent că e vorba de forum, comment-uri, PM-uri sau Shoutbox.</li>
                        <li>Nu se dă enable conturilor dezactivate decât în cazuri excepționale şi nu se dezactivează conturi la cerere.</li>
                        <li>Nu este permisa cererea sau postarea de crack-uri/keygen-uri in comentariile torrentelor si pe Forum. Pentru nelamuriri/update-uri, puteti contacta Uploaderii.</li>
                        <li>Nu este permisa utilizarea de programe de imbunatatit ratia sau care sa afecteze in vreun fel scriptul trackerului.</li>
                        <li>Accesul la acest site este un privilegiu, nu un drept, şi acesta poate fi revocat pentru orice motiv.</li>
                        <li>Este interzisă exprimarea ostilităţii rasiste/naţionaliste/religioase, propagarea terorismului, a drogurilor etc.</li>
                    </ul>
                    
                    <h4 class="text-primary">2. Reguli Generale - Activitatea pe forum și mesajele private !</h4>
                    <ul>
                        <li>Un comportament agresiv pe forum va duce la warning/disable!</li>
                        <li>Nu deschideţi topic pentru a face o cerere.</li>
                        <li>Nu vă bateti joc de topic-urile altor membri (i.e. SPAM).</li>
                        <li>Nu este permis să faceţi flame (atac verbal si/sau insultă către alt utilizator) şi off-topic (postarea mesajelor care nu sunt în aceaşi temă cu topicul respectiv).</li>
                        <li>Nu cereţi ca un topic să fie închis/mutat, totul depinde de moderatori.</li>
                        <li>Fără multi-post, folosiţi butonul Edit sau in functie de cau butonul Quote</li>
                        <li>Aveţi grijă ca întrebările dvs să fie postate în topic-ul corespunzător.</li>
                        <li>Comportamentul neadecvat va duce la închiderea contului dumneavoastră şi al celui care v-a invitat!</li>
                        <li>Fără topic-uri/postări de vânzări/cumpărări/schimburi/căutări de personal etc. Nu suntem la mica publicitate.</li>
                        <li>Fără cereri/postări de serials/cd-keys/cracks/passwords/hack tools.</li>
                        <li>Înainte de orice întrebare, consultaţi FAQ-ul şi fiţi siguri că răspunsul nu se află acolo!</li>
                        <li>Este interzisă postarea adreselor de e-mail/ID-urilor/paginilor personale/numerelor de telefon etc.</li>
                        <li>Fără cereri de subtitrări la filme/seriale pe forum.</li>
                        <li>Este interzisă exprimarea ostilităţii rasiste/naţionaliste/religioase, propagarea terorismului, a drogurilor etc.</li>
                        <li>Nu se permit PM-uri agresive sau conținând injurii.</li>
                        <li>Nu se permit PM-uri cu conținut comercial sau orice fel de invitație nesolicitată.</li>
                    </ul>
                    
                    <h4 class="text-primary">3. Reguli download - Neglijându-le, veţi fi lipsiți de dreptul la download !</h4>
                    <ul>
                        <li>După ce terminați un download, nu închideți clientul dumneavoastră de torrente; lăsați-l pornit pentru a partaja fișierele.</li>
                        <li>O rație prea mică va duce la dezactivarea contului.</li>
                        <li>Trebuie să ții torrentul la seed minim 24 ore într-un interval de 7 zile.</li>
                        <li>La 10 hit & run, nu mai poți descărca.</li>
                        <li>După ce terminați un download, nu închideți clientul dumneavoastră de torrente (µTorrent, BitTorrent, qBittorrent, Deluge etc.); lăsați-l pornit pentru a partaja fișierele cu alții. Acest lucru vă ajută, de asemenea, să vă creșteți rația.</li>
                        <li>O rație mică va duce, în cele din urmă, la dezactivarea contului.</li>
                        <li>Dacă ați descărcat fișierul torrent și l-ați adăugat în client, asigurați-vă că finalizați descărcarea. Dacă, din orice motiv, nu puteți descărca torrentul, vă rugăm să anunțați staff-ul prin deschiderea unui ticket.</li>
                        <li>Nu recomandăm descărcările parțiale! Timpul de seed începe să fie contorizat din momentul în care ați descărcat 100% torrentul</li>
                        <li>Nu practicați Hit/Run, torentele downloadate pot fi închise doar dacă au ratie 1 sau timpul de seed pe torent este de 24 de ore.</li>
                        <li>Torentele pot fi închise dupa ce se acumulează minim 3 ore de la încheierea downloadului urmând ca ulterior să reveniți la seed și timp de 1 săptămână să realizați ratia 1 sau cele 24 de ore de seed.</li>
                        <li>Dacă știți că nu veți putea ține la seed acest timp minim inainte de a reveni, nu vă mai deranjați să-l luați. Il veți downloada mai târziu când știți că veți ține PC-ul mai mult deschis.</li>
                        <li>Încălcarea acestei reguli duce la avertizare timp de 1 săptămână warn.</li>
                        <li>Încălcarea acestei reguli pe un torrent Freelech duce la avertizare timp de 2 săptămâni warn.</li>
                        <li>Încălcarea acestei reguli în mod repetat poate duce la dezactivarea contului (disable).</li>
                        <li>Clasa VIP e exclusă de la regulile Hit/Run!</li>
                    </ul>
                    
                    <h4 class="text-primary">4. Reguli Generale - Avatare</h4>
                    <ul>
                        <li>Formatul imaginii trebuie să fie *.jpg, *.gif, sau *.png.</li>
                        <li>Nu folosiţi imagini cu conţinut pornografic, religios, fascist, nazist, rasist, xenofob, cruzime faţă de oameni şi animale.</li>
                        <li>Nu folosiţi imagini care sugereaza apartenenta la Staff-ul LastFiles sau o imagine ce ofensează în mod direct un alt user.</li>
                        <li>Fără reclamă la avatar, inclusiv promovarea propriului profil (-> disable).</li>
                    </ul>
                    
                    <h4 class="text-primary">5. Reguli Generale - Profil</h4>
                    <p>Puteți pune ce vreți in profil, în afară de:</p>
                    <ul>
                        <li>Link-uri către alte trackere;</li>
                        <li>Cuvinte jignitoare aduse la adresa unor useri sau staff-ului;</li>
                        <li>Fără CT-uri ce reprezintă o clasă diferită de cea pe care o dețineți în acest moment. Induce utilizatorii în eroare.</li>
                        <li>Fără CT-uri vulgare.</li>
                        <li>Sunt interzise CT-urile care pot sugera advertise</li>
                    </ul>
                    
                    <h4 class="text-primary">6. Reguli Generale - Requests !</h4>
                    <ul>
                        <li>Asiguraţi-vă că requestul ce urmează să-l faceţi nu este deja cerut sau urcat pe tracker!</li>
                        <li>Nu faceţi request la un material deja existent pe tracker doar pentru că nu are subtitrare în limba română.</li>
                        <li>Este obligatoriu ca cererea dumneavoastră să includă, pe langa o descriere adecvată, şi un link sugestiv (ex: gamespot pentru jocuri şi imdb pentru filme).</li>
                        <li>Este interzisă cererea de materiale care nu au fost incă lansate.</li>
                        <li>Este interzisă cererea de materiale XXX.</li>
                        <li>Este interzisă cererea de materiale româneşti.</li>
                        <li>Este interzisa cererea de crack-uri, keygen-uri, trainere, hack-uri, demo-uri şi materiale freeware.</li>
                        <li>Este interzisă cererea de pack-uri, colecţii etc.(excepţie fac doar serialele TV şi discografiile).</li>
                        <li>Nerespectarea acestor reguli conduc la stergerea requestului şi atenţionarea dumneavoastră cu warn !</li>
                    </ul>
                    
                    <h4 class="text-primary">Conținut request:</h4>
                    <ul>
                        <li>Descriere. Cu o simplă căutare pe Google puteți face rost de o descriere completă pentru materialul dumneavoastră.</li>
                        <li>Link IMDB (în caz că materialul dorit este un film) - http://www.imdb.com/</li>
                        <li>Link de pe GameSpot (în caz că materialul dorit este un joc) - http://www.gamespot.com/</li>
                        <li>Sau alt link sugestiv - http://www.wikipedia.org sau altele (link-urile Youtube nu sunt considerate link-uri de informatii).</li>
                        <li>Opțional - Screen-uri și trailer (pentru filme și jocuri).</li>
                    </ul>
                    <p>Atenție! Dacă în două săptămâni request-ul dvs. nu va fi urcat, se va șterge, indiferent de numărul de voturi pe care îl are.</p>
                    
                    <h4 class="text-primary">7. Reguli Generale - Comentarii</h4>
                    <ul>
                        <li>Comentariile ce conţin cuvinte fie ele cenzurate sau insulte la adresa unui alt user, nu sunt admise.</li>
                        <li>Este interzisă postarea videoclipurilor care au ca si scop reclama la propriul video, gameplay etc.</li>
                        <li>Fără comentarii inutile de genul: "Seed!", "Stati la seed!"</li>
                        <li>Este interzisă postarea adreselor e-mail/ID-urilor/paginilor personale.</li>
                        <li>Fără spoilere! - Nu divulgaţi acea informaţie (în special la filme), care ar slăbi interesul celorlalţi să mai privească acel material.</li>
                        <li>Dacă nu vă place un torrent nu sunteţi obligat să îl downloadaţi, deci nu postaţi comentarii dacă nu vă place torrentul respectiv! Exemplu: "Ce torrent de rahat", "De ce urcaţi torrente idioate?", "Torrent inutil" etc.</li>
                        <li>Orice cerere sau comentariu făcut referitor la subtitrări va fi răsplatit(ă) dupa caz cu avertizare sau dezactivarea contului. Nu suntem site de subtitrări.</li>
                    </ul>
                    
                    <h4 class="text-primary">8. Custom Title</h4>
                    <ul>
                        <li>Fără CT-uri ce reprezintă o clasă diferită de cea pe care o dețineți la moment ce poate induce utilizatorii în eroare.</li>
                        <li>Fără CT-uri vulgare, denigratoare și lipsite de sens.</li>
                        <li>Sunt interzise CT-urile care pot sugera advertise.</li>
                    </ul>
                    
                    <h4 class="text-primary">Semnatura</h4>
                    <ul>
                        <li>Rezolutia pozelor folosite pentru semnatura nu trebuie sa depaseasca 450x150.</li>
                        <li>Este interzisa folosirea unui limbaj/continut neadecvat sau care ar putea jigni o persoana sau un grup de persoane.</li>
                        <li>Este interzisa folosirea semnaturi pentru promovarea comunitatilor externe sau pentru promovarea unui scop personal.</li>
                    </ul>
                    
                    <h4 class="text-primary">Respectarea celorlalți utilizatori</h4>
                    <p>Este interzisă insultarea, hărțuirea sau discriminarea pe baza etniei, religiei, genului sau a altor criterii. Respectul reciproc este obligatoriu.</p>
                
                    <h4 class="text-primary">Partajarea fișierelor (seeding)</h4>
                    <ul>
                        <li>Fiecare utilizator este obligat să mențină un raport de <strong>cel puțin 1.0</strong> (raportul între upload și download).</li>
                        <li>Torrentele trebuie să fie păstrate în seeding minim <strong>24 de ore în decurs de 7 zile</strong> sau până când se atinge un raport de 1.0.</li>
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
                    <h4 class="text-primary">Welcome to LastFiles!</h4>
                    <p>Please read these rules carefully, as failure to comply will result in account deactivation.</p>
                    
                    <h4 class="text-primary">1. General Rules - Violation of these rules will lead to account deactivation/IP ban!</h4>
                    <ul>
                        <li>Do not challenge the moderators and administrators with your requests! They always use their best judgment.</li>
                        <li>Multiple accounts are not allowed.</li>
                        <li>Accounts with usernames resembling or imitating staff members are prohibited.</li>
                        <li>Do not upload our torrents to other trackers.</li>
                        <li>Do not sell or attempt to sell invitations/accounts.</li>
                        <li>Advertising other trackers via PM, Forum, Shoutbox etc. is prohibited.</li>
                        <li>Invitation requests are not allowed, whether on the forum, comments, PMs or Shoutbox.</li>
                        <li>Disabled accounts will not be re-enabled except in exceptional cases, and accounts will not be disabled upon request.</li>
                        <li>Requesting or posting cracks/keygens in torrent comments or on the Forum is not allowed. For questions/updates, contact the Uploaders.</li>
                        <li>The use of ratio-boosting programs or any software that affects the tracker script is prohibited.</li>
                        <li>Access to this site is a privilege, not a right, and can be revoked for any reason.</li>
                        <li>Expressions of racist/nationalist/religious hostility, promotion of terrorism, drugs etc. are prohibited.</li>
                    </ul>
                    
                    <h4 class="text-primary">2. General Rules - Forum activity and private messages!</h4>
                    <ul>
                        <li>Aggressive behavior on the forum will result in a warning/disable!</li>
                        <li>Do not open topics to make requests.</li>
                        <li>Do not mock other members' topics (i.e. SPAM).</li>
                        <li>Flaming (verbal attacks and/or insults towards other users) and off-topic posts (messages unrelated to the topic) are not allowed.</li>
                        <li>Do not request topics to be closed/moved - this is at the moderators' discretion.</li>
                        <li>No multi-posting - use the Edit button or, where appropriate, the Quote button.</li>
                        <li>Ensure your questions are posted in the appropriate topic.</li>
                        <li>Inappropriate behavior will lead to the closure of your account and that of the member who invited you!</li>
                        <li>No topics/posts about sales/purchases/exchanges/personnel searches etc. We are not a classified ads site.</li>
                        <li>No requests/posts for serials/cd-keys/cracks/passwords/hack tools.</li>
                        <li>Before asking any question, consult the FAQ and ensure the answer isn't already there!</li>
                        <li>Posting email addresses/IDs/personal pages/phone numbers etc. is prohibited.</li>
                        <li>No requests for movie/TV show subtitles on the forum.</li>
                        <li>Expressions of racist/nationalist/religious hostility, promotion of terrorism, drugs etc. are prohibited.</li>
                        <li>Aggressive PMs or those containing insults are not allowed.</li>
                        <li>PMs with commercial content or any unsolicited invitations are not allowed.</li>
                    </ul>
                    
                    <h4 class="text-primary">3. Download rules - Neglecting these will result in loss of download privileges!</h4>
                    <ul>
                        <li>After finishing a download, do not close your torrent client; keep it running to share files.</li>
                        <li>A low ratio will lead to account deactivation.</li>
                        <li>You must seed the torrent for at least 24 hours within a 7-day period.</li>
                        <li>After 10 hit & run penalties, you will no longer be able to download.</li>
                        <li>After finishing a download, do not close your torrent client (µTorrent, BitTorrent, qBittorrent, Deluge, etc.); keep it running so that it can share the data with others. This also helps increase your ratio.</li>
                        <li>A low ratio will eventually lead to account deactivation.</li>
                        <li>If you have downloaded the torrent file and added it to your client, make sure to complete the torrent. If, for any reason, you cannot download the torrent, please notify the staff by opening a ticket.</li>
                        <li>Seed time will only be counted if the torrent is downloaded in full.</li>
                        <li>Do not practice Hit & Run - downloaded torrents can only be stopped if they have a ratio of 1 or have been seeded for 24 hours.</li>
                        <li>Torrents can be stopped after accumulating at least 3 hours after completing the download, provided you return to seed and within 1 week achieve a ratio of 1 or the full 24 hours of seeding.</li>
                        <li>If you know you won't be able to seed for this minimum time before returning, don't bother downloading it. Download it later when you know you'll keep your PC on longer.</li>
                        <li>Violation of this rule results in a 1-week warning.</li>
                        <li>Violation of this rule on a Freeleech torrent results in a 2-week warning.</li>
                        <li>Repeated violation of this rule may lead to account deactivation (disable).</li>
                        <li>VIP class is exempt from Hit & Run rules!</li>
                    </ul>
                    
                    <h4 class="text-primary">4. General Rules - Avatars</h4>
                    <ul>
                        <li>Image format must be *.jpg, *.gif, or *.png.</li>
                        <li>Do not use images with pornographic, religious, fascist, nazi, racist, xenophobic content, cruelty to people and animals.</li>
                        <li>Do not use images suggesting affiliation with LastFiles staff or images that directly offend another user.</li>
                        <li>No advertising in avatars, including promotion of your own profile (-> disable).</li>
                    </ul>
                    
                    <h4 class="text-primary">5. General Rules - Profile</h4>
                    <p>You can put whatever you want in your profile, except:</p>
                    <ul>
                        <li>Links to other trackers;</li>
                        <li>Offensive words directed at other users or staff;</li>
                        <li>No CTs representing a different class than the one you currently hold. This misleads users.</li>
                        <li>No vulgar CTs.</li>
                        <li>CTs that may suggest advertising are prohibited.</li>
                    </ul>
                    
                    <h4 class="text-primary">6. General Rules - Requests!</h4>
                    <ul>
                        <li>Make sure the request you're about to make hasn't already been requested or uploaded to the tracker!</li>
                        <li>Do not request material already on the tracker just because it doesn't have Romanian subtitles.</li>
                        <li>Your request must include, besides an adequate description, a suggestive link (e.g., gamespot for games and imdb for movies).</li>
                        <li>Requesting unreleased material is prohibited.</li>
                        <li>Requesting XXX material is prohibited.</li>
                        <li>Requesting Romanian material is prohibited.</li>
                        <li>Requesting cracks, keygens, trainers, hacks, demos and freeware material is prohibited.</li>
                        <li>Requesting packs, collections etc. is prohibited (the only exceptions being TV series and discographies).</li>
                        <li>Failure to comply with these rules will result in request deletion and a warning!</li>
                    </ul>
                    
                    <h4 class="text-primary">Request content:</h4>
                    <ul>
                        <li>Description. With a simple Google search you can get a complete description for your material.</li>
                        <li>IMDB link (if the requested material is a movie) - http://www.imdb.com/</li>
                        <li>GameSpot link (if the requested material is a game) - http://www.gamespot.com/</li>
                        <li>Or other suggestive link - http://www.wikipedia.org or others (YouTube links are not considered information links).</li>
                        <li>Optional - Screenshots and trailer (for movies and games).</li>
                    </ul>
                    <p>Attention! If within two weeks your request has not been uploaded, it will be deleted, regardless of the number of votes it has.</p>
                    
                    <h4 class="text-primary">7. General Rules - Comments</h4>
                    <ul>
                        <li>Comments containing censored words or insults directed at another user are not allowed.</li>
                        <li>Posting videos intended to advertise your own video, gameplay etc. is prohibited.</li>
                        <li>No useless comments like: "Seed!", "Stay at seed!"</li>
                        <li>Posting email addresses/IDs/personal pages is prohibited.</li>
                        <li>No spoilers! - Do not disclose information (especially about movies) that would reduce others' interest in watching that material.</li>
                        <li>If you don't like a torrent you are not obliged to download it, so don't post comments if you don't like that torrent! Example: "What a shitty torrent", "Why upload idiotic torrents?", "Useless torrent" etc.</li>
                        <li>Any request or comment regarding subtitles will be rewarded as appropriate with a warning or account deactivation. We are not a subtitle site.</li>
                    </ul>
                    
                    <h4 class="text-primary">8. Custom Title</h4>
                    <ul>
                        <li>No CTs representing a different class than the one you currently hold, as this may mislead users.</li>
                        <li>No vulgar, derogatory or meaningless CTs.</li>
                        <li>CTs that may suggest advertising are prohibited.</li>
                    </ul>
                    
                    <h4 class="text-primary">Signature</h4>
                    <ul>
                        <li>The resolution of images used for signatures must not exceed 450x150.</li>
                        <li>The use of inappropriate language/content or content that could offend a person or group of people is prohibited.</li>
                        <li>Using signatures to promote external communities or for personal promotion is prohibited.</li>
                    </ul>
                    
                    <h4 class="text-primary">Respect for Other Users</h4>
                    <p>Insulting, harassing, or discriminating based on ethnicity, religion, gender, or other criteria is prohibited. Mutual respect is mandatory.</p>

                    <h4 class="text-primary">File Sharing (Seeding)</h4>
                    <ul>
                        <li>Each user is required to maintain a ratio of <strong>at least 1.0</strong> (the ratio of upload to download).</li>
                        <li>Torrents must be seeded for a minimum of <strong>24 hours cumulated in 7 days</strong> or until a ratio of 1.0 is achieved.</li>
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