<?php

if (!function_exists('convertCustomTagsToHtml')) {
    /**
     * Convert custom tags to HTML.
     *
     * @param  string  $content
     * @return string
     */
    function convertCustomTagsToHtml($content) {
        // Convert [img]...[/img] to <img src="...">
        $content = preg_replace('/\[img\](.*?)\[\/img\]/', '<img src="$1" alt="image" data-lity style="max-width: 50%;" />', $content);

        // Convert [u]...[/u] to <u>...</u> (allowing spaces in text)
        $content = preg_replace('/\[u\](.*?)\[\/u\]/s', '<u>$1</u>', $content);

        // Convert [color=...]...[/color] to <span style="color:...">...</span>
        $content = preg_replace('/\[color=(.*?)\](.*?)\[\/color\]/s', '<span style="color:$1">$2</span>', $content);

        // Convert [center]...[/center] to <div style="text-align: center;">...</div>
        $content = preg_replace('/\[center\](.*?)\[\/center\]/s', '<div style="text-align: center;">$1</div>', $content);

        // Convert [b]...[/b] to <strong>...</strong> (allowing spaces in text)
        $content = preg_replace('/\[b\](.*?)\[\/b\]/s', '<strong>$1</strong>', $content);

        // Convert [i]...[/i] to <em>...</em> (allowing spaces in text)
        $content = preg_replace('/\[i\](.*?)\[\/i\]/s', '<em>$1</em>', $content);

        // Convert [quote]...[/quote] to <blockquote>...</blockquote>
        $content = preg_replace('/\[quote\](.*?)\[\/quote\]/s', '<blockquote>$1</blockquote>', $content);

        // Convert [spoiler]...[/spoiler] to a div with show/hide functionality
        $content = preg_replace_callback('/\[spoiler\](.*?)\[\/spoiler\]/s', function ($matches) {
            $spoilerContent = htmlspecialchars($matches[1], ENT_QUOTES, 'UTF-8'); // Sanitize content
            return '
                <div class="spoiler-container">
                    <button onclick="toggleSpoiler(this)">Show</button>
                    <div class="spoiler-content" style="display: none; background-color: #f0f0f0; padding: 5px; border: 1px solid #ccc; margin-top: 5px;">
                        ' . $spoilerContent . '
                    </div>
                </div>
            ';
        }, $content);

        // Convert [youtube]...[/youtube] to an embedded YouTube iframe
        $content = preg_replace_callback(
            '/\[youtube\](.*?)\[\/youtube\]/',
            function($matches) {
                $url = $matches[1];
                preg_match('/(?:https?:\/\/)?(?:www\.)?youtube\.com\/watch\?v=([a-zA-Z0-9_-]+)|youtu\.be\/([a-zA-Z0-9_-]+)/', $url, $videoIdMatch);
                $videoId = $videoIdMatch[1] ?? $videoIdMatch[2] ?? null;
                return $videoId ? '<div class="videoWrapper"><iframe src="https://www.youtube.com/embed/' . $videoId . '" frameborder="0" allowfullscreen></iframe></div>' : $url;
            },
            $content
        );

        // Convert [font=...]...[/font] to <span style="font-family:...">...</span>
        $content = preg_replace('/\[font=(.*?)\](.*?)\[\/font\]/s', '<span style="font-family:$1">$2</span>', $content);

        // Convert [size=...]...[/size] to <span style="font-size:...px">...</span>
        $content = preg_replace('/\[size=(\d+)\](.*?)\[\/size\]/s', '<span style="font-size:${1}px">$2</span>', $content);

        // Convert [url]...[/url] to <a href="...">...</a>
        $content = preg_replace('/\[url\](.*?)\[\/url\]/s', '<a href="$1" target="_blank">$1</a>', $content);

        // Convert [url=...]...[/url] to <a href="...">...</a>
        $content = preg_replace('/\[url=(.*?)\](.*?)\[\/url\]/s', '<a href="$1" target="_blank">$2</a>', $content);

        // Ensure line breaks are added for clarity
        $content = nl2br($content);

        return $content;
    }




    if (!function_exists('formatBytes')) {
        function formatBytes($bytes, $precision = 2) {
            $units = ['B', 'KB', 'MB', 'GB', 'TB'];

            $bytes = max($bytes, 0);
            $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
            $pow = min($pow, count($units) - 1);

            $bytes /= pow(1024, $pow);

            return round($bytes, $precision) . ' ' . $units[$pow];
        }
    }
}
