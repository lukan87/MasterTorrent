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

        $content = preg_replace_callback('/\[quote\](.*?)\[\/quote\]/s', function ($matches) {
            return '<blockquote class="quote">' . trim($matches[1]) . '</blockquote>';
        }, $content);



        $content = preg_replace_callback('/\[spoiler\](.*?)\[\/spoiler\]/s', function ($matches) {
            // Process the content within the spoiler tags to convert BBCode
            $spoilerContent = convertCustomTagsToHtml($matches[1]); // Reuse the function for nested BBCode

            // Generate a unique ID for each spoiler
            $uniqueId = uniqid('spoiler_');

            return '
                <span class="text-decoration-underline" style="cursor: pointer;" data-bs-toggle="collapse" data-bs-target="#' . $uniqueId . '" aria-expanded="false" aria-controls="' . $uniqueId . '">
                    Show Spoiler
                </span>
                <div id="' . $uniqueId . '" class="collapse">
                    <div class="spoiler-content p-3 border rounded">
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
                return $videoId ? '<div class="videoWrapper"><iframe src="https://www.youtube.com/embed/' . $videoId . '" frameborder="0" allowfullscreen ></iframe></div>' : $url;
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




}
