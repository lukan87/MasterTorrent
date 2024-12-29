<?php

if (!function_exists('emoji')) {
    /**
     * Get the emoji by its code
     *
     * @param string $emojiCode
     * @return string
     */
    function emoji($emojiCode)
    {
        $emojis = [
            ':smile:' => '😊',
            ':heart:' => '❤️',
            ':thumbsup:' => '👍',
            ':wink:' => '😉',
            ':laugh:' => '😂',
            ':sad:' => '😞',
            ':love:' => '😍',
            ':cool:' => '😎',
            ':cry:' => '😭',
            ':angry:' => '😡',
            ':kiss:' => '😘',
            ':surprised:' => '😲',
            ':blush:' => '😊',
            ':grin:' => '😁',
            ":star:" => "⭐",
            ":fire:" => "🔥",
            ":trophy:" => "🏆",
            ":party:" => "🎉",
            ":clap:" => "👏",
            ":confetti:" => "🎊",
            ":praise:" => "🙌",

        ];

        return $emojis[$emojiCode] ?? '';  // Return an empty string if the emoji code doesn't exist
    }
}
