<?php

$count = [1, 10, 50, 100, 500, 1000];

return [
    'enabled' => true,
    // Enable automatic milestone rewards and historical reconciliation.
    'awarding_enabled' => true,
    // Each tier pays 25% of the current balance multiplied by its one-based order.
    'reward_percent' => 25,
    // Invite rewards are deliberately reserved for contribution and loyalty milestones.
    'categories' => [
        'torrents' => ['name' => 'Release builder', 'icon' => 'bi-cloud-arrow-up', 'description' => 'Upload torrents for the community.', 'unit' => 'torrents uploaded', 'thresholds' => $count, 'invites' => [3 => 1, 4 => 1, 5 => 2, 6 => 3], 'first_message' => 'Congratulations on your first torrent upload! Keep up the good work.'],
        'comments' => ['name' => 'Conversation starter', 'icon' => 'bi-chat-dots', 'description' => 'Leave useful comments and replies.', 'unit' => 'comments', 'thresholds' => $count],
        'comment_reactions' => ['name' => 'Discussion supporter', 'icon' => 'bi-emoji-heart-eyes', 'description' => 'React to other members’ comments and request discussions.', 'unit' => 'comment reactions', 'thresholds' => $count],
        'forum_posts' => ['name' => 'Forum regular', 'icon' => 'bi-chat-square-text', 'description' => 'Share knowledge and join forum discussions.', 'unit' => 'forum posts', 'thresholds' => $count],
        'forum_reactions' => ['name' => 'Forum supporter', 'icon' => 'bi-emoji-smile', 'description' => 'React to other members’ forum posts.', 'unit' => 'forum reactions', 'thresholds' => $count],
        'torrent_reactions' => ['name' => 'Release supporter', 'icon' => 'bi-heart', 'description' => 'React to torrents shared by other members.', 'unit' => 'torrent reactions', 'thresholds' => $count],
        'seeding' => ['name' => 'Swarm guardian', 'icon' => 'bi-hdd-network', 'description' => 'Seed distinct torrents at the same time.', 'unit' => 'active seeds', 'thresholds' => $count, 'invites' => [4 => 1, 5 => 2, 6 => 3]],
        'snatches' => ['name' => 'Collection explorer', 'icon' => 'bi-check2-circle', 'description' => 'Complete downloads of distinct torrents.', 'unit' => 'completed downloads', 'thresholds' => $count],
        'uploaded' => ['name' => 'Bandwidth benefactor', 'icon' => 'bi-arrow-up-circle', 'description' => 'Share actual upload traffic; shop credit and multipliers do not count.', 'unit' => 'bytes', 'thresholds' => [1073741824, 10737418240, 107374182400, 1099511627776, 5497558138880, 10995116277760], 'invites' => [4 => 1, 5 => 2, 6 => 3]],
        'downloaded' => ['name' => 'Archive explorer', 'icon' => 'bi-arrow-down-circle', 'description' => 'Download actual data, including freeleech traffic.', 'unit' => 'bytes', 'thresholds' => [1073741824, 10737418240, 107374182400, 1099511627776, 5497558138880, 10995116277760]],
        'seedtime' => ['name' => 'Long-term keeper', 'icon' => 'bi-hourglass-split', 'description' => 'Build total seed time across your torrents.', 'unit' => 'seconds', 'thresholds' => [86400, 604800, 2592000, 7776000, 15552000, 31536000], 'invites' => [3 => 1, 4 => 1, 5 => 2, 6 => 3]],
        'invited_users' => ['name' => 'Community ambassador', 'icon' => 'bi-person-plus', 'description' => 'Bring new members to the community. Only completed registrations count.', 'unit' => 'members invited', 'thresholds' => [1, 5, 10, 15, 20, 50], 'first_message' => 'Congratulations! Your first invited member has joined the community. Keep up the good work!'],
        'account_age' => ['name' => 'Community veteran', 'icon' => 'bi-calendar-heart', 'description' => 'Celebrate your time in the community.', 'unit' => 'months', 'thresholds' => [1, 6, 12, 60, 72, 120], 'invites' => [3 => 1, 4 => 2, 5 => 2, 6 => 3]],
    ],
];
