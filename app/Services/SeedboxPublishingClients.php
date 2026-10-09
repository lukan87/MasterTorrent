<?php

namespace App\Services;

use App\Models\Seedbox;

class SeedboxPublishingClients
{
    public function forSeedbox(Seedbox $seedbox): SeedboxPublishingClient
    {
        return new SeedboxPublishingClient($seedbox->address, $seedbox->username, $seedbox->password, $seedbox->auth_type);
    }
}
