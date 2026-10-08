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

## Validation

Run PHP regressions:

```sh
vendor/bin/phpunit tests/Unit/TorrentMetadataTest.php tests/Unit/TorrentSearchTest.php tests/Integration/TorrentBrowseRenderingTest.php
TORRENT_PARTIAL_MYSQL_TEST=1 vendor/bin/phpunit tests/Integration/TorrentPartialAccessTest.php
LIBRARY_MYSQL_TEST=1 vendor/bin/phpunit tests/Integration/LibraryBrowseTest.php
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

## Further work

Measure authenticated TTFB, LCP and SQL timings under representative load before extending this approach to further pages. Review search and sort query plans before adding indexes. Full SPA navigation is intentionally a later architecture decision; the current components improve browsing without replacing the site's routing or templates.
