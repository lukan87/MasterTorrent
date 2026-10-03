# Community requests

Members can vote once per request created by someone else, withdraw their vote, and sort by most voted. Repeat submissions are idempotent and the database enforces one vote per member/request. Request creators cannot vote on their own requests, including staff; legacy self-votes are excluded from counts and voter summaries. Both the list and detail card show up to three voters (You first), with all usernames in a Bootstrap tooltip. Comments reuse the shared discussion system: replies, edit/delete permissions, reaction picker, comment restrictions, daily posting limit, and achievements. Request deletion removes its discussion; foreign keys remove votes and comment reactions.

Only Uploader rank or higher, or members with `uploadpos = yes`, can fill requests. Both the endpoint and UI enforce this rule. Fill links must identify an existing, non-deleted torrent on this site. Filling locks the request and commits the requester inbox message (sent from System, user ID 2, reusing the member’s System conversation) and in-app notifications for current voters atomically. Voter notifications appear in the navbar and notifications page and link to the filled request. Withdrawn votes, legacy self-votes, and deleted accounts are excluded; a voter who fills the request still receives the notification. Owners and staff can reopen requests; only staff can delete them.

## Metadata services

- `TMDBService`: TMDB lookup, search, details, and collections.
- `OMDBService`: IMDb ID and title lookups through OMDb.
- `SteamService`: Steam app details, preserving the keyed API payload used by torrent pages.
- `MediaDisplayService`: combines TMDB and OMDb into the existing movie/TV display format. Movie and series controllers now use this service.
- `RequestMetadataService`: parses recognized TMDB/Steam links and prepares safe request previews. User-supplied URLs are never fetched directly.

Provider calls for details use `MetadataHttpCache`, with bounded timeouts, stale-data fallback, and retry backoff. Requests remain usable when previews are unavailable. Posters, ratings, cast, genres, release details, developers, publishers, and platforms are shown when supplied by the provider.

## Database and checks

Apply only the new migration when deploying elsewhere:

```sh
php artisan migrate --path=database/migrations/2026_09_29_120000_create_request_votes_table.php --force
php artisan queue:restart
REQUEST_MYSQL_TEST=1 ACHIEVEMENT_MYSQL_TEST=1 vendor/bin/phpunit tests/Integration/TorrentRequestWorkflowTest.php tests/Integration/AchievementAwardTest.php
vendor/bin/phpunit tests/Unit/TorrentMetadataTest.php tests/Unit/CommentActionTest.php tests/Unit/CommentViewTest.php tests/Unit/AchievementTest.php
```

The comment-reactions and achievements tables from the existing features must be installed. The integration suites use connection-local temporary tables; they do not change application records.
