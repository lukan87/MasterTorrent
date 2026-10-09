# Anonymous publishing

Profile settings now include **Publish anonymously by default**. This preference affects future uploads only. The ordinary and external upload forms include a **Publish anonymously** switch, which can override the saved preference per upload. Uploads submitted without `anon` inherit the preference; explicit `anon=0` or `anon=1` takes precedence. Invalid values fail validation before publishing.

The real torrent owner remains stored for permissions, bonuses, accountability and moderation. Other members see **Anonymous**, without the uploader's profile link, on torrent lists, details, peer and snatch/history pages. Anonymous uploads are excluded from the owner's public upload list and count; owner and moderator views retain them. Generic torrent serialization excludes owner/uploader relations for other viewers. Owners and moderators can see the real uploader and administrators retain audit records.

This does not anonymize a user's account, torrent contents, announce traffic, client software or independently posted comments, reactions, subtitles and request activity. Do not include identifying information in descriptions or content when privacy matters. Existing uploads are not converted by changing the default preference.

The standalone release adds `users.anonymous` (boolean, default false) through `2026_10_08_100400_add_anonymous_to_users_table.php` and uses the existing `torrents.anon` column. No Upload API/Sanctum changes are required or applied by this release. No tracker, Torznab or base seedbox service changes are included.

Verification: 18 tests, 292 assertions passed in an isolated in-memory SQLite database. Browser private/external uploads, override/default choices, real browse projection, identity display/serialization, owner/moderator access, profile list filtering, preference ownership and schema rollback/default are covered. PHP syntax and Blade compilation passed. Tests use mocked metadata/image/file-storage/notification boundaries while retaining real browser controllers, torrent parsing and database persistence. No live upload was created during testing.

Affected source files are listed in the restore-point manifest. Before release, exact original file copies and SHA-256 hashes are saved under `.backups/anonymous-publishing-*`. The supplied restore script reverts only unchanged deployed files; it refuses to overwrite later edits. Code rollback leaves the additive database column and migration record intact to avoid deleting preferences. Do not roll back the database or remove migration records automatically.

Live release verification: the single additive migration is recorded, model casts and exact deployed hashes were checked, PHP syntax and Blade compilation passed, and `https://fileiplay.org/up` returned HTTP 200. The Upload API feature branch was not deployed.
