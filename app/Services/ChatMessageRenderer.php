<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Collection;

class ChatMessageRenderer
{
    private array $mentions = [];

    public function prepareMentions(Collection $messages): void
    {
        $names = [];
        foreach ($messages as $message) {
            $texts = [$message->message];
            foreach ($message->replies ?? [] as $reply) {
                $texts[] = $reply->message;
            }
            foreach ($texts as $text) {
                $names = array_merge($names, (new ShoutMentionService)->names($text));
            }
        }
        if ($names === []) {
            return;
        }
        foreach (User::whereIn('name', array_unique($names))->get(['id', 'name']) as $user) {
            $this->mentions[mb_strtolower($user->name)] = route('profile.show', ['id' => $user->id, 'name' => $user->name]);
        }
    }

    public static function mediaUrl(string $value): ?string
    {
        $url = trim(html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        $parts = parse_url($url);
        if (! filter_var($url, FILTER_VALIDATE_URL) || ! is_array($parts)
            || ! in_array(strtolower($parts['scheme'] ?? ''), ['http', 'https'], true)
            || empty($parts['host']) || isset($parts['user']) || isset($parts['pass'])) {
            return null;
        }

        return $url;
    }

    public static function youtubeId(string $value): ?string
    {
        $value = trim($value);
        if (preg_match('/^[A-Za-z0-9_-]{11}$/D', $value)) {
            return $value;
        }
        $url = self::mediaUrl($value);
        if (! $url) {
            return null;
        }
        $parts = parse_url($url);
        $host = strtolower($parts['host']);
        $path = trim($parts['path'] ?? '', '/');
        $id = null;
        if (in_array($host, ['youtu.be', 'www.youtu.be'], true)) {
            $id = explode('/', $path)[0];
        } elseif (in_array($host, ['youtube.com', 'www.youtube.com', 'm.youtube.com', 'youtube-nocookie.com', 'www.youtube-nocookie.com'], true)) {
            parse_str($parts['query'] ?? '', $query);
            if ($path === 'watch') {
                $id = $query['v'] ?? null;
            } elseif (preg_match('~^(?:shorts|embed|live)/([^/]+)$~', $path, $match)) {
                $id = $match[1];
            }
        }

        return is_string($id) && preg_match('/^[A-Za-z0-9_-]{11}$/D', $id) ? $id : null;
    }

    private function video(string $id): string
    {
        return '<button type="button" class="chat-youtube" data-youtube-id="'.e($id).'" aria-label="Play YouTube video">'
            .'<span class="chat-youtube-thumbnail"><img src="https://i.ytimg.com/vi/'.e($id).'/hqdefault.jpg" width="480" height="270" loading="lazy" decoding="async" alt="YouTube video thumbnail">'
            .'<span class="chat-youtube-play"><i class="bi bi-play-fill" aria-hidden="true"></i></span></span>'
            .'<span class="chat-youtube-caption"><i class="bi bi-youtube" aria-hidden="true"></i><span>YouTube video<small>Click to play in the video player</small></span><i class="bi bi-arrows-fullscreen" aria-hidden="true"></i></span></button>';
    }

    public function render(?string $text, int $depth = 0): string
    {
        if (! $text) {
            return '';
        }
        if ($depth >= 8) {
            return nl2br(e($text), false);
        }
        // Protect generated markup and literal code from subsequent BBCode replacements.
        $protected = [];
        $prefix = 'CHAT'.bin2hex(random_bytes(12));
        $token = function (string $html) use (&$protected, $prefix): string {
            $key = $prefix.count($protected).'END';
            $protected[$key] = $html;

            return $key;
        };
        $replace = function (string $pattern, callable $callback) use (&$text, $token): void {
            $text = preg_replace_callback($pattern, fn ($match) => $token($callback($match)), $text);
        };
        $body = fn (string $value) => $this->render($value, $depth + 1);
        $replace('/\[code(?:=(\w+))?\](.*?)\[\/code\]/is', fn ($m) => '<pre class="chat-code"><code>'.e(trim($m[2])).'</code></pre>');
        $replace('/\[img\](.*?)\[\/img\]/is', function ($m) {
            $url = self::mediaUrl($m[1]);

            return $url ? '<img src="'.e($url).'" class="bbcode-image chat-embedded-image" loading="lazy" decoding="async" referrerpolicy="no-referrer" alt="Shared chat image" tabindex="0" role="button" aria-label="Open shared image in image viewer">' : e($m[0]);
        });
        $replace('/\[youtube\](.*?)\[\/youtube\]/is', function ($m) {
            $id = self::youtubeId($m[1]);

            return $id ? $this->video($id) : e($m[0]);
        });
        $link = fn ($url, $label) => '<a href="'.e($url).'" target="_blank" rel="noopener noreferrer nofollow">'.e($label).'</a>';
        $replace('/\[url=([^\]]+)\](.*?)\[\/url\]/is', fn ($m) => ($url = self::mediaUrl($m[1])) ? $link($url, $m[2]) : e($m[2]));
        $replace('/\[url\](.*?)\[\/url\]/is', fn ($m) => ($url = self::mediaUrl($m[1])) ? $link($url, $url) : e($m[1]));
        foreach (['b' => 'strong', 'i' => 'em', 'u' => 'u', 's' => 's', 'strike' => 's'] as $tag => $html) {
            $replace('~\['.$tag.'\](.*?)\[/'.$tag.'\]~is', fn ($m) => '<'.$html.'>'.$body($m[1]).'</'.$html.'>');
        }
        // Render nested quotes from the inside out, with a bounded nesting limit.
        for ($i = 0; $i < 8; $i++) {
            $previous = $text;
            $replace('/\[quote(?:="([^"]*)")?\]((?:(?!\[quote|\[\/quote\]).)*?)\[\/quote\]/is', fn ($m) => '<blockquote class="chat-quote">'.(! empty($m[1]) ? '<strong>'.e($m[1]).' wrote:</strong>' : '').$body($m[2]).'</blockquote>');
            if ($text === $previous) {
                break;
            }
        }
        $replace('/\[spoiler\](.*?)\[\/spoiler\]/is', fn ($m) => '<details class="chat-spoiler"><summary>Show spoiler</summary><div>'.$body($m[1]).'</div></details>');
        $replace('/\[list\](.*?)\[\/list\]/is', function ($m) use ($body) {
            $items = array_filter(array_map('trim', preg_split('/\[\*\]/', $m[1])), fn ($item) => $item !== '');

            return '<ul class="chat-list">'.implode('', array_map(fn ($item) => '<li>'.$body($item).'</li>', $items)).'</ul>';
        });
        $replace('/\[color=(red|green|blue|orange|yellow|purple|white|black)\](.*?)\[\/color\]/is', fn ($m) => '<span class="chat-color-'.strtolower($m[1]).'">'.$body($m[2]).'</span>');
        $replace('/\[size=(\d+)\](.*?)\[\/size\]/is', fn ($m) => '<span style="font-size:'.max(10, min(24, (int) $m[1])).'px">'.$body($m[2]).'</span>');
        $replace('/\[center\](.*?)\[\/center\]/is', fn ($m) => '<div class="text-center">'.$body($m[1]).'</div>');
        $replace('/\[box(?:=(info|warning|success|danger))?\](.*?)\[\/box\]/is', fn ($m) => '<div class="chat-info-box">'.$body($m[2]).'</div>');
        $replace('/\[hr\]/i', fn () => '<hr>');
        // A pasted YouTube URL gets the same lightweight preview as an explicit tag.
        $replace('~https?://[^\s<>\[\]"\x27]+~i', function ($m) use ($link) {
            $url = rtrim($m[0], '.,;:!?)');
            $tail = substr($m[0], strlen($url));
            if (! self::mediaUrl($url)) {
                return e($m[0]);
            }
            $id = self::youtubeId($url);

            return ($id ? $this->video($id) : $link($url, $url)).e($tail);
        });
        $replace('/(?<![\pL\pN_@])@(?:"([^"\r\n]{1,100})"|([\pL\pN_][\pL\pN_.-]{0,99}))/u', function ($m) {
            $name = $m[1] !== '' ? $m[1] : rtrim($m[2], '.');
            $url = $this->mentions[mb_strtolower($name)] ?? null;

            return $url ? '<a class="mention" href="'.e($url).'">@'.e($name).'</a>' : e($m[0]);
        });

        $html = nl2br(e($text), false);
        for ($i = 0; $i <= count($protected); $i++) {
            $expanded = strtr($html, $protected);
            if ($expanded === $html) {
                break;
            }
            $html = $expanded;
        }

        return $html;
    }
}
