<?php

namespace App\DTO;

use Illuminate\Http\Request;

class AnnounceRequestDTO
{
    public string $infoHash;
    public string $peerId;
    public int $port;
    public float $left;
    public float $uploaded;
    public float $downloaded;
    public ?string $event;
    public ?bool $compact;
    public ?bool $noPeerId;

public function __construct(Request $request)
{
    $query = $request->server('QUERY_STRING');

    preg_match('/info_hash=([^&]+)/', $query, $infoMatch);
    preg_match('/peer_id=([^&]+)/', $query, $peerMatch);

    $infoHashRaw = isset($infoMatch[1]) ? urldecode($infoMatch[1]) : null;
    $peerIdRaw   = isset($peerMatch[1]) ? urldecode($peerMatch[1]) : null;

    $this->infoHash   = $infoHashRaw ? bin2hex($infoHashRaw) : '';
    $this->peerId     = $peerIdRaw ? $peerIdRaw : uniqid('peer_', true);

    $this->port       = (int) $request->get('port', 0);
    $this->left       = (float) $request->get('left', 0);
    $this->uploaded   = (float) $request->get('uploaded', 0);
    $this->downloaded = (float) $request->get('downloaded', 0);
    $this->event      = $request->get('event');
    $this->compact    = $request->get('compact') == 1;
    $this->noPeerId   = $request->get('no_peer_id') == 1;
}
}
