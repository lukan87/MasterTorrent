# Upload API: review, staging and release

## Approved live installation — 8 October 2026

Release `3dd6b33` was installed with explicit user approval. Restore point: `/var/www/fileiplay.org/.backups/upload-api-install-20261008-191526`. Only the four pending API migrations were applied. Sanctum 4.3.3 was the only dependency added; production configuration was unchanged. Schema/storage preflight passed as the web user, Blade views compiled, and workers received a graceful restart signal. Local-origin health returned 200, protected pages redirected to login, and unauthenticated API requests returned 401 JSON. Cloudflare rejected the external Python test client; no Cloudflare settings were changed. Seedbox publishing remains disabled pending provider/worker checks. The review history below describes the pre-release state; the live installation record takes precedence.

## Current delivery

Implementation is on `feature/torrent-upload-api` in the isolated checkout `/tmp/fileiplay-upload-api-750cac3`, based on working commit `750cac3`. The Upload API has not been pushed or deployed, and production configuration has not been changed. At the user's explicit request, standalone anonymous publishing was applied separately to the live site, including the `users.anonymous` migration. Its file restore point is `/var/www/fileiplay.org/.backups/anonymous-publishing-20261008-171100`; local review commit `9ac4200` is in `/tmp/fileiplay-anonymous-750cac3`. This feature branch includes that compatible anonymous behavior. The saved GitHub baseline and database backup remain available.

User-facing pages, once released:

- `/settings/api`: scoped personal tokens, one-time token display, expiry and revocation.
- `/settings/api/documentation`: integrated multipart/cURL/Python guide.
- `/settings/api/history`: personal website/API/seedbox upload history.
- `/settings/api/publishing`: explicit seedbox publishing, status and retries.
- `/admin/upload-history`: administrator audit view.

Profile settings and seedbox torrent pages link to these pages. The website and API now share upload authorization, validation, parsing, duplicate protection and processing. The existing public/external browser upload flow is retained and tested separately.

## Required staging checks before release

1. Review the branch and the complete file list in `upload-api-review.md`. Use a staging copy of the production schema and sanitized data, separate storage/cache/queue/session services, and staging credentials. Never run the test suite against production.
2. Install locked dependencies with the project's normal deployment procedure (`composer install`). Laravel Sanctum 4.3.3 is the only new package. PHP 8.4.26 and installed Laravel 12.69.3 were used locally.
3. Review pending migrations before applying anything. The branch adds only the five migrations listed below. On the current live site the anonymous users migration is already recorded, leaving four pending API migrations; a fresh staging baseline needs all five. Other existing pending migrations are outside this task; do not blindly run an unrestricted migration command.
4. Preview and then apply these five migrations in staging. Confirm schema compatibility with the site's actual MySQL version and existing `torrents.info_hash` and `torrents.slug` unique indexes. The read-only command `php artisan upload-api:preflight` checks required schema, unique indexes, private storage permissions and queue/metadata readiness; run it after staging migrations.
5. Verify the website manually: private upload, external upload, metadata/manual fields, screenshots, download, RSS download, sending existing torrents to seedboxes, moderator flags, duplicate feedback, announce and existing Prowlarr/Sonarr/Radarr searches/downloads. Render and exercise all new pages using the real site layout, including mobile, token copy/reveal/revoke, history and seedbox authorization.
6. Repeat concurrent uploads on the release staging environment if its database/cache configuration differs from the isolated MariaDB checks below. Check rate limiting with the staging cache driver, queue failure/retry behavior and upload rollback. Confirm one torrent, one bonus award and one stored file result for simultaneous equal uploads.
7. Exercise a staging ruTorrent provider with HTTPRPC/source plugins, HTTPS and a publicly routable endpoint. Verify source export, directory reuse, stopped registration, hash checking and eventual seeding. Do not use production content for this check.
8. Inspect web server/PHP body limits. Application limits are 10 MiB per torrent and 120 MiB total request, checked against both Content-Length and aggregate parsed fields/files; deployment infrastructure can impose lower limits before Laravel. API screenshot limits are 10 MiB each and 4096 x 2160 pixels. Existing browser screenshot processing is preserved.

## Migrations and commands

Only on an approved staging/release target, use the five explicit paths (add `--pretend` first to preview SQL; use `--force` only as part of an approved production release):

```sh
php artisan migrate \
  --path=database/migrations/2026_10_08_100000_create_personal_access_tokens_table.php \
  --path=database/migrations/2026_10_08_100100_add_upload_metadata_to_torrents.php \
  --path=database/migrations/2026_10_08_100200_create_seedbox_publications_table.php \
  --path=database/migrations/2026_10_08_100300_create_upload_attempts_table.php \
  --path=database/migrations/2026_10_08_100400_add_anonymous_to_users_table.php
php artisan upload-api:preflight
```

The migrations add hashed personal tokens, nullable TVDB/season/episode fields, encrypted seedbox publication records, indexed upload attempts and a users `anonymous` boolean defaulting to false. The user field is cast as a boolean; the branch includes anonymous displays, per-upload choices and the default preference. Laravel will skip its migration where already recorded; do not manually alter migration records or add the column again. They do not rebuild existing torrent tables or remove existing data. Review `down()` methods before any rollback: dropping history/token/publication tables destroys their records.

Normal release cache refresh and worker restart commands must follow the site's existing deployment procedure and only run after approval. No production Upload API release commands were executed. Only the separately authorized anonymous migration and source/view refresh were applied live.

## Storage, metadata and workers

New private torrent files live on Laravel's local private disk in `storage/app/private/torrents`; API screenshots are temporarily staged under private `upload_images`. Existing public torrent files remain in place and are supported by a compatibility resolver. This change does not retroactively privatize historical files.

Ensure `TMDB_API_KEY` is supplied through the existing services configuration if automatic metadata is wanted. An insecure hardcoded fallback was removed; no secret is included in documentation or new responses. Provider responses are cached, missing metadata can fall back to valid manual values, and manual title/description/artwork are preserved.

Seedbox publishing is disabled by default. After real provider and worker checks, an administrator may enable `UPLOAD_API_SEEDBOX_PUBLISHING_ENABLED=true` through the approved configuration process. Readiness is enforced in the page, submit/retry endpoints and queued jobs; disabling it also prevents new client actions from queued work. Publishing currently supports database, Redis and Beanstalkd queues only, with a configured `retry_after` of at least 360 seconds. Sync, null, SQS and unknown drivers remain unavailable for this optional workflow because their safe redelivery settings cannot be verified here. Normal API/browser uploads remain available regardless of this flag. Publishing also requires an existing worker. Jobs have a maximum timeout of 180 seconds and use 300-second locks. Temporary lock contention releases the job for 30 seconds, with a bounded 12-attempt limit, so explicit retries can wait for a timed-out worker lock to expire. Review worker timeout and queue redelivery settings together: use a worker timeout above 180 seconds and a queue `retry_after`/visibility timeout of at least 360 seconds for this workload. Keep publishing unavailable until the queue is suitable. These production settings were not modified. API screenshot processing and upload notifications also use jobs; on the sync driver they are scheduled after the HTTP response rather than inside the upload transaction.

## Supported automation and limitations

Tokens have only `torrents:upload` and `torrents:read`, backed by actual endpoints. Token permissions never bypass user restrictions; inactive/banned users are rejected. Upload/read rates are enforced independently per user and token, with an additional pre-authentication IP limit. Authentication is bearer-only for the new API. Token values are shown only in the generation response, stored hashed, and never placed in session flash data. Publication metadata is encrypted and jobs contain record IDs instead of provider credentials.

Seedbox publishing supports explicit selection of a completed, verified torrent from an owned ruTorrent seedbox. It exports the small `.torrent` through supported plugins, rewrites tracker/private settings through the shared upload service, and optionally registers a new torrent in the source content directory for checking/seeding. Existing seeding torrents are not restarted or modified. Repeated requests and registration retries reuse the recorded publication/torrent.

There is no arbitrary remote filesystem browser, remote shell, payload download through Laravel, or creation of torrents from loose files. Providers without the required ruTorrent plugins/capabilities, private-network endpoints and insecure HTTP endpoints cannot use this workflow. If rewriting leaves the same info hash as an existing source, automatic client registration is not used; the page reports the need for manual seeding.

Anonymous publication follows the live preference and per-upload override. Other members see Anonymous; owners and moderators retain identity access. The API exposes only the anonymous boolean. Seedbox publication saves its choice at authorization time, and retries preserve it. BitTorrent v2/hybrid uploads are not supported by the current v1 tracker/parser. Automatic category selection is not inferred: users select an existing category and movie/TV metadata is checked against it. Season/episode fields are accepted and included in generated TV names; full episode-specific metadata retrieval is not implemented.

## Verification performed

- 89 automated tests, 787 assertions passed: 81 tests/714 assertions on isolated in-memory SQLite, plus 8 tests/73 assertions on a separate socket-only MariaDB 11.8.6 instance matching the live engine.
- Coverage includes authorized upload/read, missing/invalid/revoked tokens, scopes and uploader restrictions, torrent parser failures, validation, database duplicates and an interleaved uniqueness race, rate limits, metadata failure/cache/manual precedence, token ownership, pages, history, browser private/external uploads, private file/download compatibility, screenshot processing/replay, seedbox transport/source/registration failure/retry and temporary-lock handling, preservation of existing client torrents, anonymous defaults/overrides and identity masking, redaction and read-only preflight.
- PHP syntax checks, Blade compilation and whitespace checks passed. New and substantially rewritten PHP files were formatted with the installed Pint.
- Existing announce controller, Torznab controller/service and the base SeedboxService match the saved baseline byte-for-byte.
- All 76 current live base-table definitions were copied without production data into a separate temporary server. The four pending API migrations applied successfully; the already-applied anonymous migration was correctly skipped. Preflight confirmed required columns and unique indexes.
- Real simultaneous workers verified one 201 and one 409 for the same info hash, one bonus award, one database torrent/file list and one private stored file. Separate same-title/different-hash uploads both succeed, including a URL uniqueness race; 255-character duplicate titles fit the slug column.
- Full existing layouts rendered API settings, the table-formatted guide, personal history, seedbox publishing and administrator auditing. Token generation/hash storage/use/revocation passed. Browser private/external uploads and normal/RSS downloads passed; downloads preserve the info hash and use the downloading member’s passkey.
- No real seedbox operation or interactive/visual browser test was performed. Clipboard/reveal JavaScript execution was not tested because Node/browser tooling was unavailable. Real provider and worker checks are required before optional publishing enablement.

The tests explicitly force SQLite `:memory:` and assert that target before creating test tables. They build a minimal fixture schema and run the five new migrations, rather than resetting the site's database or invoking production seeders. Local invocation used a temporary SQLite extension because the host PHP driver was absent:

```sh
php -d extension=/tmp/fileiplay-test-php/usr/lib/php/20240924/pdo_sqlite.so \
  vendor/bin/phpunit tests/Feature/UploadApiTest.php tests/Feature/AnonymousPublishingTest.php tests/Unit
```

With the SQLite driver installed on a test environment, use the ordinary `vendor/bin/phpunit tests/Feature/UploadApiTest.php tests/Feature/AnonymousPublishingTest.php tests/Unit` command. The temporary extension is a local test aid and is not a deployment dependency.

## Production-shaped database test invocation

The optional integration suite refuses writes unless a marked `/tmp/fileiplay-api-mysql-*/mysql.sock` socket is supplied and the selected database is exactly `fileiplay_upload_test`. It does not reset databases or import user rows. Initialize a separate socket-only server with a schema-only copy and migration bookkeeping, then apply only the five explicit migrations above. Do not point it at the running database service. The local validation used `/tmp/fileiplay-api-mysql-20261008`; its server is stopped after verification.

```sh
FILEIPLAY_TEST_MYSQL_SOCKET=/tmp/fileiplay-api-mysql-20261008/mysql.sock \
  php vendor/bin/phpunit tests/Integration/UploadApiMySqlTest.php
```

Worker barriers force simultaneous duplicate-hash and duplicate-title checks before insertion, exercising actual database conflicts rather than only an interleaved mock. Fixtures contain test-only users/torrents. External HTTP is blocked and jobs are faked in the integration environment; tracker/metadata provider/network operations are not implied by these results.

Configured FPM base values inspected read-only were `post_max_size=100M`, `upload_max_filesize=100M`, `memory_limit=128M`, and `max_execution_time=30`. No active pool override for these values was found in the checked files. Infrastructure can therefore reject requests below the application’s 120 MiB ceiling; verify the active web SAPI, proxy/body limits and memory budget before release. No production settings were changed. Ensure web and worker users can both access the private storage directories with the intended permissions.

## Rollback considerations

Before any approved release, retain the working Git revision and a fresh database/storage backup. The separately deployed anonymous behavior is the current live source baseline. Preserve it during any API rollback; reverting anonymous displays while retaining anonymous torrents would reveal identities. After API release the original pre-API reader cannot read newly private torrent files. Keep the private-file reader compatibility changes or deploy a forward fix if new uploads already exist. Disable new publishing entry points and stop the new publishing work before changing code; coordinate this with existing queue operations.

Do not automatically drop the new tables, reset the database, move private torrent files to public storage, or restore an older database. A database restore can lose all subsequent site activity and requires a separate explicit decision. The user's backup provides a recovery option, not an automatic rollback procedure.

Live view edits made during review were preserved in this branch: the anonymous preference remains in the profile sidebar and top torrent pagination remains hidden. Recheck live file hashes before installation and merge any subsequent edits; never overwrite the live tree wholesale.
