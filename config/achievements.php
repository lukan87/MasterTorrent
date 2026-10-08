<?php

$count = [1, 10, 50, 100, 500, 1000];

return [
    'enabled' => true,
    // Enable automatic milestone rewards and historical reconciliation.
    'awarding_enabled' => true,
    // Fixed points per tier, independent of the member's current balance.
    'tier_rewards' => [1 => 100, 2 => 250, 3 => 500, 4 => 1000, 5 => 2000, 6 => 5000],
    'tier_tokens' => [1 => 5, 2 => 10, 3 => 15, 4 => 20, 5 => 25, 6 => 30],
    // Invite rewards are deliberately reserved for contribution and loyalty milestones.
    'categories' => [
        'torrents' => ['name' => 'Release builder', 'icon' => 'bi-cloud-arrow-up', 'description' => 'Upload torrents for the community.', 'unit' => 'torrents uploaded', 'thresholds' => $count, 'invites' => [3 => 1, 4 => 1, 5 => 2, 6 => 3], 'first_message' => 'Congratulations on your first torrent upload! Keep up the good work.'],
        'comments' => ['name' => 'Conversation starter', 'icon' => 'bi-chat-dots', 'description' => 'Leave useful comments and replies.', 'unit' => 'comments', 'thresholds' => $count],
        'comment_reactions' => ['name' => 'Discussion supporter', 'icon' => 'bi-emoji-heart-eyes', 'description' => 'React to other members’ comments and request discussions.', 'unit' => 'comment reactions', 'thresholds' => $count],
        'forum_posts' => ['name' => 'Forum regular', 'icon' => 'bi-chat-square-text', 'description' => 'Share knowledge and join forum discussions.', 'unit' => 'forum posts', 'thresholds' => $count],
        'forum_reactions' => ['name' => 'Forum supporter', 'icon' => 'bi-emoji-smile', 'description' => 'React to other members’ forum posts.', 'unit' => 'forum reactions', 'thresholds' => $count],
        'torrent_reactions' => ['name' => 'Release supporter', 'icon' => 'bi-heart', 'description' => 'React to torrents shared by other members.', 'unit' => 'torrent reactions', 'thresholds' => $count],
        'seeding' => ['name' => 'Seeder Hero', 'icon' => 'bi-hdd-network', 'description' => 'Torrents you are seeding at the same time.', 'unit' => 'active seeds', 'thresholds' => [25, 100, 250, 500, 1000, 2500], 'points' => [1 => 25, 2 => 75, 3 => 200, 4 => 500, 5 => 1000, 6 => 2500], 'tokens' => [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0, 6 => 0]],
        'snatches' => ['name' => 'Collection explorer', 'icon' => 'bi-check2-circle', 'description' => 'Complete downloads of distinct torrents.', 'unit' => 'completed downloads', 'thresholds' => $count],
        'uploaded' => ['name' => 'Bandwidth benefactor', 'icon' => 'bi-arrow-up-circle', 'description' => 'Share actual upload traffic; shop credit and multipliers do not count.', 'unit' => 'bytes', 'thresholds' => [1073741824, 10737418240, 107374182400, 1099511627776, 5497558138880, 10995116277760], 'invites' => [4 => 1, 5 => 2, 6 => 3]],
        'downloaded' => ['name' => 'Archive explorer', 'icon' => 'bi-arrow-down-circle', 'description' => 'Download actual data, including freeleech traffic.', 'unit' => 'bytes', 'thresholds' => [1073741824, 10737418240, 107374182400, 1099511627776, 5497558138880, 10995116277760]],
        'login_streak' => ['name' => 'Daily regular', 'icon' => 'bi-calendar-check', 'description' => 'Sign in or visit while signed in every day. Missing a day resets your streak. Each milestone pays once.', 'unit' => 'consecutive days', 'thresholds' => [7, 30, 90, 180, 270, 365], 'labels' => ['7 days', '30 days', '3 months (90 days)', '6 months (180 days)', '9 months (270 days)', '1 year (365 days)'], 'points' => [1 => 1000, 2 => 1500, 3 => 2500, 4 => 5000, 5 => 10000, 6 => 30000], 'tokens' => [1 => 0, 2 => 5, 3 => 15, 4 => 25, 5 => 50, 6 => 100], 'invites' => [3 => 1, 4 => 3, 5 => 5, 6 => 15], 'vip_months' => [6 => 6]],
        'torrent_seedtime' => ['name' => 'Dedicated seeder', 'icon' => 'bi-hourglass-split', 'description' => 'Accumulate seed time on a single torrent uploaded by another member. Time across different torrents is never added together. Each milestone pays once.', 'unit' => 'seconds', 'thresholds' => [604800, 1209600, 1814400, 2592000, 7776000, 15552000], 'labels' => ['1 week', '2 weeks', '3 weeks', '1 month (30 days)', '3 months (90 days)', '6 months (180 days)'], 'points' => [1 => 100, 2 => 200, 3 => 350, 4 => 500, 5 => 1500, 6 => 3000], 'tokens' => [1 => 1, 2 => 2, 3 => 3, 4 => 5, 5 => 10, 6 => 20]],
        'invited_users' => ['name' => 'Community ambassador', 'icon' => 'bi-person-plus', 'description' => 'Bring new members to the community. Only completed registrations count.', 'unit' => 'members invited', 'thresholds' => [1, 5, 10, 15, 20, 50], 'first_message' => 'Congratulations! Your first invited member has joined the community. Keep up the good work!'],
        'account_age' => ['name' => 'Community veteran', 'icon' => 'bi-calendar-heart', 'description' => 'Celebrate your time in the community.', 'unit' => 'months', 'thresholds' => [1, 6, 12, 60, 72, 120], 'invites' => [3 => 1, 4 => 2, 5 => 2, 6 => 3]],
    ],
];
