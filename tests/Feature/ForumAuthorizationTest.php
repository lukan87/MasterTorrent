<?php

namespace Tests\Feature;

use App\Http\Controllers\ForumCategoryController;
use App\Http\Controllers\ForumController;
use App\Http\Controllers\ForumPostLikeController;
use App\Http\Controllers\TopicSubscriptionController;
use App\Models\ForumCategory;
use App\Models\ForumPost;
use App\Models\ForumTopic;
use App\Models\User;
use App\Models\UserClass;
use App\Services\ForumAccess;
use App\Services\ForumService;
use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ForumAuthorizationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['cache.default' => 'array', 'session.driver' => 'array']);
    }

    public function test_all_category_routes_require_authentication(): void
    {
        foreach ($this->app['router']->getRoutes() as $route) {
            if (str_starts_with($route->getName() ?? '', 'forum.category.')) {
                self::assertContains('auth', $route->gatherMiddleware());
            }
        }
    }

    public function test_admin_can_open_category_creation_and_guest_is_forbidden(): void
    {
        auth()->setUser((new User)->forceFill(['id' => 10, 'user_class' => UserClass::ADMIN]));
        self::assertSame('forum.categories.create', (new ForumCategoryController)->create()->name());
        auth()->forgetGuards();
        $this->expectException(HttpException::class);
        (new ForumCategoryController)->create();
    }

    public function test_moderators_can_manage_topics_and_view_private_categories_but_not_delete(): void
    {
        $moderator = (new User)->forceFill(['id' => 10, 'user_class' => UserClass::MODERATOR]);
        $regular = (new User)->forceFill(['user_class' => UserClass::USER]);
        self::assertTrue(ForumAccess::allows($moderator, 'manage_topics'));
        self::assertTrue(ForumAccess::allows($moderator, 'edit_posts'));
        self::assertFalse(ForumAccess::allows($moderator, 'delete_topics'));
        self::assertTrue(ForumAccess::canView($moderator, new ForumCategory(['is_private' => true])));
        self::assertFalse(ForumAccess::canView($regular, new ForumCategory(['is_private' => true])));
    }

    public function test_blocked_users_cannot_edit_react_or_follow(): void
    {
        auth()->setUser((new User)->forceFill(['id' => 10, 'user_class' => UserClass::ADMIN, 'forumblock' => true]));
        $category = (new ForumCategory)->forceFill(['id' => 1, 'is_private' => false]);
        $topic = (new ForumTopic)->forceFill(['id' => 1, 'category_id' => 1]);
        $post = (new ForumPost)->forceFill(['id' => 1, 'topic_id' => 1, 'user_id' => 10]);
        foreach (['edit', 'update', 'react', 'follow', 'unfollow'] as $action) {
            try {
                match ($action) {
                    'edit' => (new ForumController(new ForumService))->editPost($category, $topic, $post),
                    'update' => (new ForumController(new ForumService))->updatePost(Request::create('/'), $category, $topic, $post),
                    'react' => (new ForumPostLikeController)->toggle(Request::create('/'), $category, $topic, $post),
                    'follow' => (new TopicSubscriptionController)->store($category, $topic),
                    'unfollow' => (new TopicSubscriptionController)->destroy($category, $topic),
                };
                self::fail('Blocked participation should fail.');
            } catch (HttpException $exception) {
                self::assertSame(403, $exception->getStatusCode());
            }
        }
    }

    public function test_preview_uses_the_safe_renderer_and_rejects_oversized_content(): void
    {
        auth()->setUser((new User)->forceFill(['id' => 10, 'user_class' => UserClass::USER]));
        $controller = new ForumController(new ForumService);
        $response = $controller->preview(Request::create('/forum/preview', 'POST', ['body' => '[code]<div>[b]literal[/b]</div>[/code]']));
        $html = $response->getData(true)['html'];
        self::assertStringContainsString('&lt;div&gt;[b]literal[/b]&lt;/div&gt;', $html);
        $this->expectException(ValidationException::class);
        $controller->preview(Request::create('/forum/preview', 'POST', ['body' => str_repeat('x', 10001)]));
    }
}
