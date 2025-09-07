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
        $this->infoHash   = bin2hex($request->get('info_hash'));
        $this->peerId     = $request->get('peer_id');
        $this->port       = (int)$request->get('port');
        $this->left       = (float)$request->get('left');
        $this->uploaded   = (float)$request->get('uploaded');
        $this->downloaded = (float)$request->get('downloaded');
        $this->event      = $request->get('event');
        $this->compact    = $request->get('compact') == 1;
        $this->noPeerId   = $request->get('no_peer_id') == 1;
    }
}
