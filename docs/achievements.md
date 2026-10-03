# Achievements

Achievements are configured in `config/achievements.php`. The profile accordion sits above Seeder Rank System and displays all tiers, earned dates, actual rewards, and remaining progress. Notifications link directly to the expanded accordion.

## Milestones

- Successfully invited members: 1, 5, 10, 15, 20, 50. Counts registered accounts linked through `invited_by`, including subsequently deleted accounts; unused invitation codes do not count. The inviter is checked after registration commits. This category awards the standard tier-based bonus points.

- Torrent uploads, comments/replies, forum posts, forum reactions, torrent reactions, comment reactions, simultaneous active seeds, and completed downloads: 1, 10, 50, 100, 500, 1,000.
- Actual upload/download traffic: 1 GiB, 10 GiB, 100 GiB, 1 TiB, 5 TiB, 10 TiB. Uses retained history traffic, not purchased or multiplied account credit.
- Total seed time across torrents: 1, 7, 30, 90, 180, 365 days.
- Membership: 1 month, 6 months, 1 year, 5 years, 6 years, 10 years, using calendar anniversaries.

Request comments and replies use the existing comments milestone. The Discussion supporter category counts active reactions to other members’ comments, including request discussions. Changing a reaction type does not increase progress; removing it decreases current progress, and adding it again cannot pay an already earned milestone twice.

Self-reactions do not count. Multiple clients seeding the same torrent count once. Only history with `completed_at` counts as a snatch. Removed content and cleared history cannot contribute to future thresholds; earned awards are permanent. The seeding count reflects active tracker state at evaluation time. Short-lived peaks between evaluations may not be captured.

## Rewards

Each category has its own tier order, starting at 1. Each award pays:

`round(current seedbonus × 0.25 × tier, 2)`

The existing `seedbonus.cap` limits the balance. The stored reward is the amount actually credited. Zero balance yields zero points, but the achievement and any invites are still awarded. Contribution and loyalty categories award invites at selected higher tiers; edit their `invites` maps to tune this.

Existing members receive qualifying historical milestones on evaluation. Multiple newly reached milestones are paid sequentially in configuration order, so rewards compound. For example, a member starting at 100 with the first three upload milestones receives 25, 62.50, and 140.63 points, finishing at 328.13. A large initial backlog can quickly reach the balance cap. Existing earned awards are never recalculated or paid again. Stable identity is user + category key + threshold; renaming a category key creates new achievements.

## Processing and deployment

The migration is installed and `achievements.awarding_enabled` is true. The site owner approved activating payouts, including milestones existing members already qualify for. All scheduled, queued, and manual award paths honor this switch. Set it to false to pause payouts while keeping profile progress visible; refresh cached configuration and restart queue workers after changing it.

Run only this migration if unrelated migrations are pending:

```sh
php artisan migrate --path=database/migrations/2026_09_28_150000_create_user_achievements_table.php --force
php artisan queue:restart
```

Upload, comment, post, and reaction creation dispatch a job after the database transaction commits. The normal queue worker handles it. `achievements:award` also runs every five minutes through the existing Laravel scheduler, reconciling activity, tracker statistics, and anniversaries for eligible accounts. Disabled, banned, and deleted accounts are skipped. No changes are made inside tracker announces or profile GET requests.

Manual reconciliation:

```sh
php artisan achievements:award --user=123
php artisan achievements:award
```

Set `achievements.enabled` to false to stop awarding and hide the accordion. Refresh cached configuration when changing settings. Before migration the feature safely stays inactive.

A locked user row serializes concurrent evaluations. The unique award key provides additional duplicate protection. Award record, balance, invites, and database notification commit in one transaction; failures roll back all of them.

## Checks

```sh
vendor/bin/phpunit tests/Unit/AchievementTest.php
ACHIEVEMENT_MYSQL_TEST=1 vendor/bin/phpunit tests/Integration/AchievementAwardTest.php
```

The opt-in integration suite uses a dedicated MySQL connection with temporary shadow tables, which disappear when that connection closes. Application data is not modified. It verifies repeat evaluation, compounded rewards, caps, zero balances, notification rollback, actual traffic and distinct seeding metrics, eligibility, and profile rendering.
