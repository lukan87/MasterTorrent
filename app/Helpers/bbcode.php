<?php

use App\Models\User;

if (!function_exists('convertCustomTagsToHtml')) {

    function convertCustomTagsToHtml($content)
    {
        if (!$content) {
            return '';
        }

        /*
        |------------------------------------------------------------------
        | SECURITY: Strip raw HTML before BBCode processing.
        | This prevents XSS via <script>, <iframe>, onerror, etc.
        | BBCode tags like [b], [url] are NOT HTML and survive this.
        |------------------------------------------------------------------
        */
        $content = strip_tags($content);

    

        /*
        |--------------------------------------------------------------------------
        | BASIC FORMATTING
        |--------------------------------------------------------------------------
        */

        $content = preg_replace('/\[b\](.*?)\[\/b\]/s', '<strong>$1</strong>', $content);
        $content = preg_replace('/\[i\](.*?)\[\/i\]/s', '<em>$1</em>', $content);
        $content = preg_replace('/\[u\](.*?)\[\/u\]/s', '<u>$1</u>', $content);
        $content = preg_replace('/\[center\](.*?)\[\/center\]/s', '<div class="text-center">$1</div>', $content);

        /*
        |--------------------------------------------------------------------------
        | COLOR (restricted list)
        |--------------------------------------------------------------------------
        */

        $content = preg_replace_callback('/\[color=(.*?)\](.*?)\[\/color\]/s', function ($matches) {

            $allowed = ['red','green','blue','orange','yellow','purple','white','black'];

            $color = strtolower(trim($matches[1]));
            $text  = $matches[2];

            if (!in_array($color, $allowed)) {
                return $text;
            }

            return '<span style="color:' . e($color) . '">' . $text . '</span>';
        }, $content);

        /*
        |--------------------------------------------------------------------------
        | SIZE (10px - 40px only)
        |--------------------------------------------------------------------------
        */

        $content = preg_replace_callback('/\[size=(\d+)\](.*?)\[\/size\]/s', function ($matches) {

            $size = (int) $matches[1];
            $size = max(10, min($size, 40));

            return '<span style="font-size:' . $size . 'px">' . $matches[2] . '</span>';
        }, $content);

        /*
        |--------------------------------------------------------------------------
        | URL
        |--------------------------------------------------------------------------
        */

        $content = preg_replace_callback('/\[url=(.*?)\](.*?)\[\/url\]/s', function ($matches) {

            $url  = filter_var($matches[1], FILTER_VALIDATE_URL);
            $text = $matches[2];

            if (!$url) {
                return $text;
            }

            return '<a href="' . e($url) . '" target="_blank" rel="noopener noreferrer">' . $text . '</a>';
        }, $content);

        $content = preg_replace_callback('/\[url\](.*?)\[\/url\]/s', function ($matches) {

            $url = filter_var($matches[1], FILTER_VALIDATE_URL);

            if (!$url) {
                return $matches[1];
            }

            return '<a href="' . e($url) . '" target="_blank" rel="noopener noreferrer">' . e($url) . '</a>';
        }, $content);

        // /*
        // |--------------------------------------------------------------------------
        // | IMAGE
        // |--------------------------------------------------------------------------
        // */

$content = preg_replace_callback('/\[img\](.*?)\[\/img\]/is', function ($matches) {

    $url = trim($matches[1]);

    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        return '';
    }

    $url = e($url);

    return '<img src="'.$url.'" class="bbcode-image img-fluid rounded shadow-sm" loading="lazy" style="max-width:100%;
height:auto;
margin:8px auto;
cursor:pointer;
display:block;
border-radius:8px;">';

}, $content);


        /*
        |--------------------------------------------------------------------------
        | SPOILER
        |--------------------------------------------------------------------------
        */

        $content = preg_replace_callback('/\[spoiler\](.*?)\[\/spoiler\]/s', function ($matches) {

            $id = uniqid('spoiler_');

            return '
                <div>
                    <a class="text-decoration-underline" data-bs-toggle="collapse" href="#' . $id . '">
                        👁️ Show Spoiler
                    </a>
                    <div id="' . $id . '" class="collapse mt-2">
                        <div class="border rounded p-3">
                            ' . $matches[1] . '
                        </div>
                    </div>
                </div>
            ';
        }, $content);

        /*
        |--------------------------------------------------------------------------
        | YOUTUBE
        |--------------------------------------------------------------------------
        */

        $content = preg_replace_callback('/\[youtube\](.*?)\[\/youtube\]/', function ($matches) {

            preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/)([a-zA-Z0-9_-]+)/', $matches[1], $video);

            if (empty($video[1])) {
                return '';
            }

            $id = e($video[1]);

            return '
                <div class="videoWrapper">
                    <iframe src="https://www.youtube.com/embed/' . $id . '" 
                            frameborder="0" 
                            allowfullscreen>
                    </iframe>
                </div>
            ';
        }, $content);

        /*
        |--------------------------------------------------------------------------
        | LINE BREAKS
        |--------------------------------------------------------------------------
        */




/*
|--------------------------------------------------------------------------
| CODE BLOCK
|--------------------------------------------------------------------------
*/

$content = preg_replace_callback('/\[code(?:=(\w+))?\](.*?)\[\/code\]/s', function ($matches) {

    $language = $matches[1] ?? '';
    $code = e(trim($matches[2]));

    return '
        <div class="bbcode-code-wrapper">
            '.($language ? '<div class="bbcode-code-lang">'.e($language).'</div>' : '').'
            <pre class="bbcode-code"><code>'.$code.'</code></pre>
        </div>
    ';
}, $content);


/*
|--------------------------------------------------------------------------
| QUOTE
|--------------------------------------------------------------------------
|
| Supports:
|
| [quote]message[/quote]
|
| [quote="username"]message[/quote]
|
*/

// Process nested [quote] from innermost outward.
// Tempered greedy token prevents matching across nested [quote] tags.
$quoteCount = 1;
do {
    $content = preg_replace_callback(
        '/\[quote(?:="([^"]*)")?\]((?:(?!\[quote|\[\/quote\]).)*?)\[\/quote\]/is',
        function ($matches) {

            $username = !empty($matches[1])
                ? trim($matches[1])
                : null;

            $body = trim($matches[2]);

            $header = $username
                ? '<div class="bbcode-quote-header">
                        <i class="bi bi-quote me-1"></i>
                        <strong>' . e($username) . '</strong> wrote:
                        <i class="bi bi-quote me-1"></i>
                   </div>'
                : '<div class="bbcode-quote-header">
                        <i class="bi bi-quote me-1"></i>
                        Quote
                   </div>';

            return '
                <blockquote class="bbcode-quote">
                    ' . $header . '
                    <div class="bbcode-quote-body">
                        ' . $body . '
                    </div>
                </blockquote>
            ';
        },
        $content,
        -1,
        $quoteCount
    );
} while ($quoteCount > 0);

// Apply mentions first
$content = preg_replace_callback('/@([A-Za-z0-9_]+)/', function ($matches) {

    $username = $matches[1];
    $user = User::where('name', $username)->first();

    if (!$user) {
        return '@' . e($username);
    }

    $url = route('profile.show', [
        'id' => $user->id,
        'name' => $user->name
    ]);

    return '<a href="' . e($url) . '" class="mention">@' . e($username) . '</a>';
}, $content);


/*
|--------------------------------------------------------------------------
| HORIZONTAL RULE
|--------------------------------------------------------------------------
*/

$content = preg_replace('/\[hr\]/i', '<hr class="bbcode-hr">', $content);


/*
|--------------------------------------------------------------------------
| LIST
|--------------------------------------------------------------------------
*/

$content = preg_replace_callback('/\[list\](.*?)\[\/list\]/s', function ($matches) {

    $items = preg_split('/\[\*\]/', $matches[1]);

    $html = '<ul class="bbcode-list">';

    foreach ($items as $item) {
        $item = trim($item);
        if ($item !== '') {
            $html .= '<li>' . $item . '</li>';
        }
    }

    $html .= '</ul>';

    return $html;

}, $content);



/*
|--------------------------------------------------------------------------
| INFO BOX
|--------------------------------------------------------------------------
*/

$content = preg_replace_callback('/\[box(?:=(info|warning|success|danger))?\](.*?)\[\/box\]/s', function ($matches) {

    $type = $matches[1] ?? 'info';
    $allowed = ['info','warning','success','danger'];

    if (!in_array($type, $allowed)) {
        $type = 'info';
    }

    return '<div class="bbcode-box bbcode-box-'.$type.'">'.$matches[2].'</div>';

}, $content);


// 🔥 AUTO LINK AFTER ALL HTML IS GENERATED
$content = autoLinkPlainUrls($content);

// Line breaks LAST
return nl2br($content);
    }


    
if (!function_exists('autoLinkPlainUrls')) {

    function autoLinkPlainUrls(string $content): string
    {
        $protected = [];

        // 1️⃣ Protect ALL HTML tags
        $content = preg_replace_callback(
            '/<[^>]+>/i',
            function ($matches) use (&$protected) {
                $key = '__HTML_' . count($protected) . '__';
                $protected[$key] = $matches[0];
                return $key;
            },
            $content
        );

        // 2️⃣ Protect BBCode blocks
        $content = preg_replace_callback(
            '/\[(img|url|code)[^\]]*\].*?\[\/\1\]/is',
            function ($matches) use (&$protected) {
                $key = '__BBCODE_' . count($protected) . '__';
                $protected[$key] = $matches[0];
                return $key;
            },
            $content
        );

        // 3️⃣ Convert ONLY visible standalone URLs
        $content = preg_replace_callback(
            '/(?<!["\'=])\bhttps?:\/\/[^\s<]+/i',
            function ($matches) {

                $url = filter_var($matches[0], FILTER_VALIDATE_URL);
                if (!$url) {
                    return $matches[0];
                }

                return '<a href="' . e($url) . '" target="_blank" rel="noopener noreferrer nofollow">'
                        . e($url) .
                       '</a>';
            },
            $content
        );

        // 4️⃣ Restore protected content
        if (!empty($protected)) {
            $content = str_replace(
                array_keys($protected),
                array_values($protected),
                $content
            );
        }

        return $content;
    }
}
    
}