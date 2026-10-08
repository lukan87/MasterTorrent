<?php

namespace Tests\Feature;

use App\Http\Controllers\ForumController;
use App\Models\ForumCategory;
use App\Models\User;
use App\Models\UserClass;
use App\Services\ForumService;
use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ForumTopicPermissionsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['cache.default' => 'array', 'session.driver' => 'array']);
    }

    private function actor(int $rank, bool $blocked = false): void
    {
        auth()->setUser((new User)->forceFill([
            'id' => 10, 'user_class' => $rank, 'forumblock' => $blocked,
        ]));
    }

    private function category(bool $private = false): ForumCategory
    {
        return (new ForumCategory)->forceFill([
            'id' => 1, 'name' => 'Public', 'slug' => 'public', 'is_private' => $private,
        ]);
    }

    private function assertDenied(string $action, ForumCategory $category): void
    {
        $controller = new ForumController(new ForumService);
        try {
            $action === 'create'
                ? $controller->create($category)
                : $controller->store(Request::create('/forum/public', 'POST'), $category);
            self::fail('Topic creation should be forbidden.');
        } catch (HttpException $exception) {
            self::assertSame(403, $exception->getStatusCode());
        }
    }

    public function test_regular_users_cannot_open_or_submit_topic_creation(): void
    {
        $this->actor(UserClass::USER);
        foreach (['create', 'store'] as $action) {
            $this->assertDenied($action, $this->category());
        }
    }

    public function test_elite_and_every_higher_class_can_open_and_submit_the_form(): void
    {
        $controller = new ForumController(new ForumService);
        foreach (array_keys(UserClass::getClasses()) as $rank) {
            if ($rank < UserClass::ELITE_USER) {
                continue;
            }
            $this->actor($rank);
            self::assertSame('forum.create', $controller->create($this->category())->name());
            // Invalid content must reach validation, without writing any database records.
            try {
                $controller->store(Request::create('/forum/public', 'POST'), $this->category());
                self::fail('Empty topic content should fail validation.');
            } catch (ValidationException $exception) {
                self::assertArrayHasKey('title', $exception->errors());
                self::assertArrayHasKey('body', $exception->errors());
            }
        }
    }

    public function test_forum_blocks_and_private_categories_still_deny_higher_classes(): void
    {
        foreach ([UserClass::ELITE_USER, UserClass::ADMIN] as $rank) {
            foreach (['create', 'store'] as $action) {
                $this->actor($rank, true);
                $this->assertDenied($action, $this->category());
                $this->actor($rank);
                if ($rank === UserClass::ELITE_USER) {
                    $this->assertDenied($action, $this->category(true));
                }
            }
        }
    }
}
