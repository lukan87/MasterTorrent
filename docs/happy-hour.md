# Happy Hour

Automatic mode is stored independently in `happy_hour_settings` (singleton row 1). Enabling it allows the existing hourly `happyhour:check` schedule to create events after 07:00 in the application timezone. Disabling it leaves existing events intact; use Stop or Cancel to end those events. The migration initializes this setting from the latest legacy event, enabling it only when that event is both automatic and active.

Manual events can be scheduled while automatic mode is enabled. All creation paths lock the settings row and reject overlapping enabled event intervals. Automatic checks skip any window that would overlap a manual event. At most one automatic event starts per calendar day; today's automatic history cannot be deleted until tomorrow. Presets come from `config/happyhour.php`.

Rewards apply from the start time inclusive to the end time exclusive, without depending on scheduler cleanup. The tracker retains its five-second state cache and invalidates it on model saves/deletes. Upcoming starts may take up to five seconds to appear in tracker accounting after an inactive cache entry. Existing announce behavior applies the event active at processing time to the reported transfer delta; it does not split transfers across time boundaries.

Upload rewards multiply the usual double-upload reward. Happy Hour freeleech removes charged downloads, while actual traffic remains recorded. External torrents retain their existing 5% upload / free download treatment. Happy Hour does not waive seeding rules. Seed bonus calculations and the bonus shop now use the same active time window.

Scheduled events are announced as scheduled when saved. Automatic starts and expired-event cleanup announcements occur during the hourly command. No additional scheduler or worker is required. The shoutbox system author defaults to user 2 and can be configured with `happyhour.announcement_user_id`.

Validation: `vendor/bin/phpunit tests/Unit` and `php artisan view:cache`. The Happy Hour tests use mocks and an unconnected query builder; they do not mutate production tracker data.
