<?php

namespace App\Services;

class SeedboxPublishingReadiness
{
    public function ready(): bool
    {
        if (! config('upload-api.seedbox_publishing_enabled', false)) {
            return false;
        }
        $connection = config('queue.connections.'.config('queue.default'), []);

        return in_array($connection['driver'] ?? null, ['database', 'redis', 'beanstalkd'], true)
            && (int) ($connection['retry_after'] ?? 0) >= 360;
    }
}
