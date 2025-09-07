<?php


return [



    'title' => 'LastFiles',

    /*
    |--------------------------------------------------------------------------
    | Freelech State
    |--------------------------------------------------------------------------
    |
    | Global Freeleech
    |
    */
    'freeleech' => false,

    /*
    |--------------------------------------------------------------------------
    | Double Upload State
    |--------------------------------------------------------------------------
    |
    | Global Double Upload
    |
    */
    'double' => false,

    /*
    |--------------------------------------------------------------------------
    | Cache TTL
    |--------------------------------------------------------------------------
    |
    | Time-to-live for torrent stats and peer list (in seconds).
    | Increasing this reduces DB load but may return slightly stale data.
    |
    */
    'cache_ttl' => 30,


    /*
    |--------------------------------------------------------------------------
    | Announce Intervals
    |--------------------------------------------------------------------------
    |
    | Announce intervals sent to BitTorrent clients (in seconds).
    | - interval: how often clients should re-announce
    | - min_interval: minimum wait time before re-announcing
    |
    */
    'announce_interval' => 60 * 30, // 30 minutes
    'min_interval'      => 60 * 10, // 10 minutes



];
