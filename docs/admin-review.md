# Admin controller and page review

Reviewed all 11 controllers in `app/Http/Controllers/Admin` and their associated routes and Blade pages.

## Changes

- Protected email endpoints and mass-message recipient previews with authentication and the existing staff middleware.
- Restricted system diagnostics and maintenance routes to the developer role, matching the existing UI restriction.
- Removed unimplemented create/store routes for users, movies, series, and torrents. These routes had neither controller methods nor creation views.
- Added shared, responsive admin navigation, keyboard focus indicators, a skip link, validation feedback, and links to previously difficult-to-find tools.
- Added searchable, paginated movie and series directories, labeled edit controls, retained form values, empty states, and contextual delete confirmations.
- Added a message details page and linked it directly from the message directory.
- Added a backup button to System Tools. Maintenance actions now check command exit codes and report errors accurately.
- Aligned email recipient counts and sending filters, bounded recipient loading, validated filters and content, and report sent/failed totals. Mail transport errors no longer mark user accounts as junk.
- Validated message bulk selections, catalog edits, torrent filters/deletion reasons, user fields and directory filters, and log filters.
- Routed torrent deletion through the existing deletion service so tracker state, warnings, notifications, and logs are handled together.
- Replaced nonexistent torrent deletion calls in user deletion workflows with the existing service. Permanent deletion and restoration now require a trashed account and enforce role hierarchy; self-deletion is blocked.
- Gave each user detail paginator its own query parameter, fixed torrent success wording and active-seeder filtering, and made the log “today” filter start at midnight.
- Used constant-time comparison and session regeneration in the dormant admin unlock controller.

## Verification

- `vendor/bin/phpunit tests/Unit/AdminWorkflowTest.php`: 8 tests, 159 assertions.
- Admin route registrations inspected, including middleware and controller method existence.
- PHP syntax checks for all admin controllers, the provider, and web routes.
- Admin Blade templates compiled and generated PHP linted using temporary files.
- No live emails, deletion operations, backups, or cache-clear commands were executed during verification.

## Remaining considerations

- Email sending remains synchronous; larger campaigns would benefit from a queue-backed campaign workflow with progress and retry tracking.
- Admin unlock is not registered in the current routes and has no configuration/view. This review did not activate that incomplete feature or introduce new access codes.
- `PROMOTE_ALLOWED_IDS` is an existing unused constant. Class changes still use the existing role hierarchy; the intended additional ID restriction needs a product decision.
- Account soft deletion still removes tracker history and may permanently remove unseeded torrents, as in the existing workflow. Restoring an account cannot restore that content.
- Browser interaction, screen-reader behavior, and database-backed destructive workflows have not been verified. These changes improve accessibility but do not constitute a full accessibility audit.
