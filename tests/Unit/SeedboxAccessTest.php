<?php

namespace Tests\Unit;

use App\Http\Controllers\SeedboxController;
use App\Models\Seedbox;
use App\Models\UserClass;
use Illuminate\Container\Container;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\HttpException;

class SeedboxAccessTest extends TestCase
{
    public function test_owner_can_access_their_seedbox(): void
    {
        self::assertSame('allowed', $this->checkAccess(12, 1));
    }

    public function test_developer_retains_the_existing_management_access(): void
    {
        self::assertSame('allowed', $this->checkAccess(99, UserClass::WEB_DEVELOPER));
    }

    public function test_other_users_cannot_access_the_seedbox(): void
    {
        $previous = Container::getInstance();
        Container::setInstance(new Application());
        try {
            $this->checkAccess(99, 1);
            self::fail('Expected access to be denied.');
        } catch (HttpException $e) {
            self::assertSame(403, $e->getStatusCode());
        } finally {
            Container::setInstance($previous);
        }
    }

    private function checkAccess(int $userId, int $userClass): mixed
    {
        $box = new Seedbox();
        $box->user_id = 12;
        $request = Request::create('/seedboxes/1/test');
        $request->setUserResolver(fn () => (object) ['id' => $userId, 'user_class' => $userClass]);
        $request->setRouteResolver(fn () => new class($box) {
            public function __construct(private Seedbox $box) {}
            public function parameter($name, $default = null) { return $this->box; }
        });
        $middleware = (new SeedboxController())->getMiddleware();
        self::assertSame('auth', $middleware[0]['middleware']);
        return $middleware[1]['middleware']($request, fn () => 'allowed');
    }
}
