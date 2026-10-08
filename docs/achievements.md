# Achievements

Achievements are configured in `config/achievements.php`. The profile achievement modals display tiers, earned dates, actual rewards, and remaining progress. Notifications open the category modal directly, scroll to the earned threshold, and focus it. Older achievement notifications also resolve to the earned tier when marked as read.

## Milestones

- Successfully invited members: 1, 5, 10, 15, 20, 50. Counts registered accounts linked through `invited_by`, including subsequently deleted accounts; unused invitation codes do not count. The inviter is checked after registration commits. This category awards the standard tier-based bonus points.

- Torrent uploads, comments/replies, forum posts, forum reactions, torrent reactions, comment reactions, and completed downloads: 1, 10, 50, 100, 500, 1,000.
- Actual upload/download traffic: 1 GiB, 10 GiB, 100 GiB, 1 TiB, 5 TiB, 10 TiB. Uses retained history traffic, not purchased or multiplied account credit.
- Seeder Hero: 25, 100, 250, 500, 1,000, 2,500 distinct active seeds at once. Rewards: 25, 75, 200, 500, 1,000, 2,500 BON; no tokens or invites. Existing awards at unchanged thresholds remain earned and are not paid again.
- Daily regular: 7, 30, 90, 180, 270, 365 consecutive login days. A successful sign-in or deliberate authenticated HTML page visit counts once per calendar day in the application timezone, including remembered sessions. AJAX/JSON polling, assets, and prefetching do not count. A missed day resets progress; earned tiers remain permanent and pay once per account. Tracking starts when this change is installed; prior login streaks cannot be inferred from last activity.
- Dedicated seeder: 1, 2, 3 weeks, 30, 90, 180 days on one torrent. Uses the maximum retained history seedtime for torrents owned by other members, never the sum across torrents or clients. Pauses do not erase accumulated time. Each milestone pays once per account, not once per torrent. Historical qualifying single-torrent time counts. The retired cumulative `seedtime` category is no longer evaluated or displayed; its previous rewards remain untouched.
- Membership: 1 month, 6 months, 1 year, 5 years, 6 years, 10 years, using calendar anniversaries.

Request comments and replies use the existing comments milestone. The Discussion supporter category counts active reactions to other members’ comments, including request discussions. Changing a reaction type does not increase progress; removing it decreases current progress, and adding it again cannot pay an already earned milestone twice.

Self-reactions do not count. Multiple clients seeding the same torrent count once. Only history with `completed_at` counts as a snatch. Removed content and cleared history cannot contribute to future thresholds; earned awards are permanent. The seeding count reflects active tracker state at evaluation time. Short-lived peaks between evaluations may not be captured.

## Rewards

Each category has its own tier order, starting at 1. Fixed rewards are configured in `achievements.tier_rewards`:

| Tier | Bonus points | Tokens |
| --- | ---: | ---: |
| 1 | 100 | 5 |
| 2 | 250 | 10 |
| 3 | 500 | 15 |
| 4 | 1,000 | 20 |
| 5 | 2,000 | 25 |
| 6 | 5,000 | 30 |

Daily login rewards:

| Days | BON | Tokens | Invites | VIP |
| --- | ---: | ---: | ---: | --- |
| 7 | 1,000 | 0 | 0 | — |
| 30 | 1,500 | 5 | 0 | — |
| 90 | 2,500 | 15 | 1 | — |
| 180 | 5,000 | 25 | 3 | — |
| 270 | 10,000 | 50 | 5 | — |
| 365 | 30,000 | 100 | 15 | 6 months |

VIP extends an existing future expiry, or starts from the award date. Higher account classes are preserved and are excluded from automatic VIP class reversion. The granted duration is stored in `vip_months_awarded` and shown in notifications and tiers.

Single-torrent seeding rewards:

| Duration | BON | Tokens |
| --- | ---: | ---: |
| 1 week | 100 | 1 |
| 2 weeks | 200 | 2 |
| 3 weeks | 350 | 3 |
| 30 days | 500 | 5 |
| 90 days | 1,500 | 10 |
| 180 days | 3,000 | 20 |

Tokens are configured in `achievements.tier_tokens` and credited to the existing `users.slots` balance for free-download or double-upload use. All six default tiers award 105 tokens; custom categories use their own token schedules. The actual grant is stored in `user_achievements.tokens_awarded` and shown in profiles and notifications. Tokens are paid even when bonus points are capped. All rewards commit together and repeat evaluation cannot pay them twice.

Each award pays `min(round(max(0, configured tier reward), 2), remaining balance cap)`. Missing tier rewards default to zero. The existing `seedbonus.cap` limits the balance. The stored reward is the amount actually credited. Members with zero balance receive the same fixed rewards. At the balance cap, the achievement and any invites are still awarded with zero points. Contribution and loyalty categories award invites at selected higher tiers; edit their `invites` maps to tune this.

Existing members receive qualifying historical milestones on evaluation. Rewards add together without compounding: a member starting at 100 with the first three upload milestones receives 100, 250, and 500 points, finishing at 950. Categories using the default rewards total 8,850 points across six tiers before the balance cap. Category-specific points and tokens override the defaults. Existing earned awards, including earlier percentage-based payouts, are never recalculated or paid again. Stable identity is user + category key + threshold; renaming a category key creates new achievements.

## Processing and deployment

The migration is installed and `achievements.awarding_enabled` is true. The site owner approved activating payouts, including milestones existing members already qualify for. All scheduled, queued, and manual award paths honor this switch. Set it to false to pause payouts while keeping profile progress visible; refresh cached configuration and restart queue workers after changing it.

Run only this migration if unrelated migrations are pending:

```sh
php artisan migrate --path=database/migrations/2026_09_28_150000_create_user_achievements_table.php --force
php artisan migrate --path=database/migrations/2026_10_04_000000_add_tokens_awarded_to_user_achievements_table.php --force
php artisan migrate --path=database/migrations/2026_10_07_200000_add_login_streak_achievements.php --force
php artisan config:cache
php artisan queue:restart
```

Upload, comment, post, and reaction creation dispatch a job after the database transaction commits. The normal queue worker handles it. `achievements:award` also runs every five minutes through the existing Laravel scheduler, reconciling activity, tracker statistics, and anniversaries for eligible accounts. Disabled, banned, and deleted accounts are skipped. Tracker announces use their existing accounting. Authenticated page visits record the login day after a successful HTML response and dispatch an achievement check; other achievement evaluations remain queued or scheduled.

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

The opt-in integration suite uses a dedicated MySQL connection with temporary shadow tables, which disappear when that connection closes. Application data is not modified. It verifies repeat evaluation, fixed rewards, caps, zero balances, notification rollback, actual traffic and distinct seeding metrics, eligibility, and profile rendering.
