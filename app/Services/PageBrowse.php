<?php

namespace App\Services;

class PageBrowse
{
    public static function partial(): bool
    {
        return request()->header('X-Page-Browse') === '1' && request()->expectsJson();
    }

    public static function view(string $view, array $data)
    {
        if (! self::partial()) return view($view, $data);

        return self::json(['html' => view($view.'-results', $data)->render()]);
    }

    public static function json(array $data, int $status = 200)
    {
        return response()->json($data, $status)->header('Cache-Control', 'private, no-store')
            ->header('Vary', 'Accept, X-Page-Browse, X-Messenger-Pane, X-Messenger-Thread, X-Messenger-Sidebar, X-Ticket-Content, X-Library-Detail, X-Home-Widget, X-Profile-Achievement');
    }
}
