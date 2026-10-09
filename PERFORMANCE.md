# Torrent page performance

The torrent browsers retain server-rendered Blade rows and use a small Vue 3 component to update the list, filters, sorting and pagination. Opening a torrent remains a normal page navigation. No provider credentials are sent to the browser.

## Current behavior

- `/torrents` and `/torrents/adult` return the initial HTML normally. Requests with `Accept: application/json` and `X-Torrent-Browse: 1` return escaped Blade list and search fragments through the same authenticated routes. Responses are private and not stored. The shared layout, navigation queries and movie-of-the-day query are skipped for these updates.
- Searches debounce for 500 ms. Superseded requests are cancelled, URL filters and browser history are preserved, and failed updates retain the previous list with a normal-navigation fallback.
- Browse queries fetch only row fields and relationship keys, excluding descriptions, MediaInfo and other large attributes. Category and genre options have a shared five-minute cache; member history and live torrent statistics stay separate.
- Torrent detail pages use cached TMDB, OMDb, Steam and fanart data. Missing or expired data queues `RefreshTorrentMetadata` on the existing default queue. Saved data is returned immediately; failed provider refreshes retain the existing short backoff. Upload lookups retain their synchronous behavior.
- Jobs contain provider identifiers only, resolve credentials inside the worker, and use a two-minute pending marker plus the existing provider lock to reduce duplicate refreshes. With a sync development queue, refreshes run after the response is sent.
- On a cold cache, a circular loading indicator appears in place of the metadata header; the navbar also indicates loading. Torrents without linked metadata omit the default poster/title/category header. A Vue panel checks for title metadata up to five times and automatically inserts the full existing movie, TV or game layout when ready, including cast, ratings, trailers and episode information. No extra click or page reload is needed; pending provider enrichments continue refreshing within the bounded polling window. If enrichment is unavailable, downloading and the rest of the page remain usable.
- File rows and the file tree are loaded only when the files modal opens. The initial page fetches a file count. Subsequent modal openings reuse the loaded tree; failures offer a retry.
- Detail partials use `Accept: application/json` and `X-Torrent-Content: files` or `metadata` on the existing canonical detail URL. Auth, enabled/banned-user middleware, deletion visibility and slug checks run on the same route before partial data is returned.
- Torrent detail subscriptions submit on the existing authenticated POST routes and return the updated button and subscriber label in the same response. SweetAlert feedback, button/loading indicators and duplicate-submit protection keep the page, player and comment drafts intact. Normal form redirects remain available without JavaScript.
- TMDB backdrops use `w1280` instead of original resolution. OwlCarousel assets belong to the slider partial; the unused global Slick stylesheet has been removed.

## Library browsing

`/library/movies` and `/library/series` keep their initial Blade layout and hero. A Vue component updates escaped result fragments through the same routes with `Accept: application/json` and `X-Library-Browse: 1`; those responses are private and not stored. Updates skip featured-title and layout rendering, and aggregate seeder availability for the requested page only. The initial page reuses its availability map for the hero.

Search debounces for 450 ms. Year and seeded-availability filters and latest/title/rating/year sorting also work through normal GET forms when JavaScript is disabled. URLs and browser history retain filters. New input cancels previous requests, keyboard focus is restored, errors retain existing results, and the shared navbar spinner indicates loading. Hero rotation and detail links continue normally.

Isolated synthetic MySQL tests verify a populated partial requires three queries (count, page, page availability), escapes search text, excludes deleted torrents from seeded availability, and preserves pagination filters. Chromium checks cover both libraries without production member sessions.

## TV calendar

The TV calendar uses a Vue component for date navigation, view/tab/network links, filters, debounced search and quick preferences. `Accept: application/json` plus `X-Calendar-Browse: 1` requests escaped calendar fragments on the existing authenticated route, skipping the shared layout. Browser history, search focus, the advanced-filter panel and horizontal board position are preserved; the navbar indicates requests in progress.

Follow, unfollow and upload-alert forms use their existing CSRF-protected routes with JSON responses. They retain normal redirects when JavaScript is disabled. A successful action refreshes the current calendar, and action buttons are disabled during a pending mutation. Provider errors retain the schedule and show the server's message. Authentication, throttling, schedule caches and member-specific follow/notification rules remain in place.

```sh
CALENDAR_MYSQL_TEST=1 vendor/bin/phpunit tests/Integration/TvCalendarTest.php tests/Feature/TvmazeServiceTest.php
```

## Forum, seedboxes, notifications and activity lists

The shared `page-browser.js` Vue component requests escaped Blade fragments with `Accept: application/json` and `X-Page-Browse: 1`. Forum category/search/participation pages, request lists, notifications, seedbox torrents and all six activity lists retain their original routes and permission checks. Initial pages and ordinary forms still work without JavaScript; fragment responses are private and not stored. Search, filter and sort updates preserve URLs, history and input focus, cancel superseded requests, and retain existing results on errors.

Forum topics refresh their post section while keeping the reply editor and browser draft outside the component. Replies and inline edits use existing validated, CSRF-protected routes and the existing renderer and notification workflow. Failed submissions retain drafts. Successful replies clear a draft only if the editor still contains the submitted text. Inline edit drafts are saved separately. Quotes use delegated events across post updates; reactions and back-to-top keep their existing behavior.

Seedbox polling retains the 20-second interval, uses torrent hashes to preserve table rows, and updates changed cells, counts and stats. It pauses while the tab is hidden, controls have focus, details/modals are open, or an upload file is selected. Tracker requests run four at a time and reuse results for five minutes. Search and status filters use partial navigation; existing seedbox action forms retain their normal behavior.

Notifications provide in-place mark-read, mark-all-read and confirmed delete actions with an updated navbar count. Opening a notification retains its destination navigation. Queries remain scoped to the current user. Request filters retain their existing rules. History, seeding, downloading, outstanding-seeding, H&R and fixer lists support partial pagination; all activity lists now provide dataset-wide name search and oldest/newest sorting without changing their membership or seeding conditions.

Temporary-table checks cover forum partials and posting permissions, notification ownership, active seeding filters/sorting and request fragments. Browser checks with real synthetic Blade fragments cover draft preservation, quotes, inline editing, error recovery, keyed polling, upload-selection pauses, tracker reuse, notifications and request/activity navigation. One existing request creation test expects rank 1 to be rejected, while the unchanged `TorrentRequest::canBeCreatedBy()` permits `UserClass::USER`; that full-suite assertion currently fails independently of these browsing changes.

## Library details, messaging, support, forms, profile and home

Movie and series detail pages update subscriptions through Vue without reloading the player. Subscription fragments query only local title/subscription data, skipping provider enrichment and recommendations. Recommendations load near the viewport, and season requests cancel older requests and reuse results within the page. Available torrent filtering runs locally.

Private message conversation links update the chat pane and sidebar without a document reload; Back/Forward restores the selected conversation and its own draft. Superseded selection requests are cancelled, and stale live updates cannot replace the newly opened thread. Existing edit controls and receipts work after switching, including when starting from the empty messenger. Private messages load earlier batches and append replies while preserving the editor and scroll position. Live thread updates use an ID cursor, return only new messages, and refresh conversation previews and the global unread badge. Existing edit/delete and read-receipt controls remain; receipt requests stay within the server's 200-ID limit. Reply drafts use session storage scoped to the member and conversation and are removed only after a confirmed successful send.

Support tickets append only new replies, preserve attachments on validation/network errors, and update status/assignment/locking without navigation. Composer drafts remain available while a ticket is locked or closed, and the existing unlock/new-ticket actions remain accessible. Internal notes and attachments still use the existing owner/staff visibility checks. Hidden tabs pause live polling; authorization failures stop it.

Torrent upload and edit forms provide cached title/IMDb search, poster previews, actual upload-byte progress and server validation messages. The authenticated `/torrents/form-metadata` route is limited to 30 requests per minute and caches successful TMDB results for six hours; provider failures are not cached. Its key comes from existing server configuration, and the former hardcoded browser key was removed from the shared form template. Selecting a title preserves an existing description. Final upload/update permissions and validation remain in their existing services; native forms remain available without Vue.

Profile activity pages use the existing private Blade-fragment browser. Profile shortcuts load comments, thanks, posts and permitted personal activity only when opened, with bounded page-local caching. Individual achievement milestone modals load when opened, including achievement deep links; the initial achievement summary and reward calculations remain unchanged.

Homepage trending, polls and online-member widgets refresh automatically while the page is visible, without manual refresh buttons. Trending refreshes every five minutes, polls every thirty seconds, and online members every five seconds. Online-member data uses a shared five-second cache. Normal page reloads also load widget data. Widget requests run only that widget's queries and skip the rest of the dashboard. Background updates preserve the online-member search and focus, selected poll answers, and expanded sections. Shoutbox editors and other homepage sections keep their existing DOM.

The shoutbox automatically polls messages and typing indicators every five seconds while the page is visible. It preserves the composer draft and queues message changes while a reply/edit is open or the user is reading older messages.

All these requests use existing guarded routes and private, non-stored responses. Vue status islands leave editors, selected files, players and unrelated controls in place. Server-side first renders and ordinary form fallbacks remain.

## Validation

Run PHP regressions:

```sh
vendor/bin/phpunit tests/Unit/TorrentMetadataTest.php tests/Unit/TorrentSearchTest.php tests/Integration/TorrentBrowseRenderingTest.php
TORRENT_PARTIAL_MYSQL_TEST=1 vendor/bin/phpunit tests/Integration/TorrentPartialAccessTest.php
FORUM_MYSQL_TEST=1 vendor/bin/phpunit tests/Integration/ForumPerformanceTest.php
LIBRARY_MYSQL_TEST=1 vendor/bin/phpunit tests/Integration/LibraryBrowseTest.php
vendor/bin/phpunit tests/Feature/TorrentMetadataSearchTest.php tests/Feature/HomeWidgetsTest.php tests/Unit/TicketAccessTest.php tests/Unit/TicketViewTest.php
MESSAGING_MYSQL_TEST=1 PROFILE_MYSQL_TEST=1 ACHIEVEMENT_MYSQL_TEST=1 vendor/bin/phpunit tests/Integration/MessagingTest.php tests/Integration/ProfilePerformanceTest.php tests/Integration/AchievementAwardTest.php
```

The optional access tests create session-local temporary MySQL tables that shadow production table names. They use array caches/sessions and a fake queue and never insert persistent production records. External Cloudflare proxy discovery is stubbed; permission middleware remains enabled.

Build assets with Node 22 or another version compatible with Vite 6:

```sh
npm ci
npm run build
```

Redis and the existing default queue worker must be available for enrichment. No new queue, database migration or environment secret is required. TVmaze provider caching and refresh intervals remain unchanged.

Read-only comparison on 8 October 2026: the same 50 normal browse results loaded 629,836 bytes of torrent attributes before column selection and 24,131 bytes after it (96.2% less). This measures database attribute data, not browser transfer size or total page-load speed. Query count remained six for the browse helper, excluding member history and layout queries.

Headless Chromium checks with synthetic Blade responses covered both browsers, pagination, sorting, history, debounced searches, out-of-order responses, failed updates, lazy files and escaped asynchronous metadata. Production member sessions were not used.

The additional rollout was checked in Chromium using local mocked routes and the real message Blade view: failed replies retain drafts/files, earlier messages preserve the composer, closed/reopened tickets retain drafts, profile tabs and milestone modals load on demand, title selection keeps an existing description, successful uploads navigate to the canonical URL, and each homepage widget refreshes independently. These checks do not measure production load times.

## Further work

Measure authenticated TTFB, LCP and SQL timings under representative load before extending this approach to further pages. Review search and sort query plans before adding indexes. Full SPA navigation is intentionally a later architecture decision; the current components improve browsing without replacing the site's routing or templates.
