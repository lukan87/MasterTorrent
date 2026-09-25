<?php

namespace Tests\Unit;

use App\Models\Invite;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\TestCase;

class InviteExpiryTest extends TestCase
{
    protected function tearDown(): void
    {
        Carbon::setTestNow();
    }

    public function test_expiration_is_enforced_at_exactly_fourteen_days_without_mutating_creation_time(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-25 12:00:00'));
        $invite = new Invite;
        $invite->setDateFormat('Y-m-d H:i:s');
        $invite->setRawAttributes(['created_at' => '2026-09-11 12:00:00', 'is_expired' => false]);
        self::assertTrue($invite->expired);
        self::assertSame('2026-09-11 12:00:00', $invite->created_at->format('Y-m-d H:i:s'));
        Carbon::setTestNow(Carbon::parse('2026-09-25 11:59:59'));
        self::assertFalse($invite->expired);
    }

    public function test_explicit_expiration_flag_is_respected(): void
    {
        $invite = new Invite(['is_expired' => true]);
        self::assertTrue($invite->expired);
    }
}
