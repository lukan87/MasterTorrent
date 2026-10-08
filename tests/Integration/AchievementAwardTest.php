<?php

namespace Tests\Integration;

use App\Models\User;
use App\Services\AchievementService;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Database\QueryException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\TestCase;

/** Uses connection-local MySQL temporary tables; never writes to application tables. */
class AchievementAwardTest extends TestCase
{
    private $app;

    private AchievementService $service;

    protected function setUp(): void
    {
        if (getenv('ACHIEVEMENT_MYSQL_TEST') !== '1') {
            $this->markTestSkipped('Set ACHIEVEMENT_MYSQL_TEST=1 to use isolated MySQL temporary tables.');
        }
        $this->app = require __DIR__.'/../../bootstrap/app.php';
        $this->app->make(Kernel::class)->bootstrap();
        $connection = config('database.connections.mysql');
        unset($connection['read'], $connection['write']);
        config(['database.connections.achievement_test' => $connection]);
        DB::setDefaultConnection('achievement_test');
        config(['achievements' => require __DIR__.'/../../config/achievements.php']);
        config(['achievements.awarding_enabled' => true]);
        foreach ([
            'users' => function ($t) {
                $t->softDeletes();
                $t->string('name');
                $t->unsignedBigInteger('invited_by')->nullable();
                $t->decimal('seedbonus', 14, 2);
                $t->integer('invites')->default(0);
                $t->integer('slots')->default(0);
                $t->unsignedInteger('login_streak_days')->default(0);
                $t->date('last_login_streak_date')->nullable();
                $t->timestamp('vip_until')->nullable();
                $t->integer('user_class')->default(1);
                $t->string('enabled')->default('yes');
                $t->timestamp('banned_until')->nullable();
            },
            'torrents' => function ($t) {
                $t->unsignedBigInteger('owner');
                $t->softDeletes();
            },
            'comments' => function ($t) {
                $t->unsignedBigInteger('user_id');
            },
            'comment_reactions' => function ($t) {
                $t->unsignedBigInteger('user_id');
                $t->unsignedBigInteger('comment_id');
                $t->string('reaction');
            },
            'forum_posts' => function ($t) {
                $t->unsignedBigInteger('user_id');
            },
            'forum_post_likes' => function ($t) {
                $t->unsignedBigInteger('user_id');
                $t->unsignedBigInteger('post_id');
            },
            'torrent_reactions' => function ($t) {
                $t->unsignedBigInteger('user_id');
                $t->unsignedBigInteger('torrent_id');
            },
            'peers' => function ($t) {
                $t->unsignedBigInteger('user_id');
                $t->unsignedBigInteger('torrent_id');
                $t->boolean('active');
                $t->boolean('seeder');
            },
            'history' => function ($t) {
                $t->unsignedBigInteger('user_id');
                $t->unsignedBigInteger('torrent_id');
                $t->bigInteger('actual_uploaded')->default(0);
                $t->bigInteger('actual_downloaded')->default(0);
                $t->bigInteger('seedtime')->default(0);
                $t->timestamp('completed_at')->nullable();
            },
            'user_achievements' => function ($t) {
                $t->unsignedBigInteger('user_id');
                $t->string('category');
                $t->unsignedBigInteger('threshold');
                $t->integer('tier');
                $t->decimal('balance_before', 14, 2);
                $t->decimal('bonus_awarded', 14, 2);
                $t->integer('invites_awarded');
                $t->unsignedInteger('tokens_awarded')->default(0);
                $t->unsignedTinyInteger('vip_months_awarded')->default(0);
                $t->timestamp('earned_at');
                $t->unique(['user_id', 'category', 'threshold']);
            },
        ] as $table => $columns) {
            Schema::create($table, function (Blueprint $t) use ($columns) {
                $t->temporary();
                $t->id();
                $t->timestamps();
                $columns($t);
            });
        }
        Schema::create('notifications', function (Blueprint $t) {
            $t->temporary();
            $t->uuid('id')->primary();
            $t->string('type');
            $t->morphs('notifiable');
            $t->text('data');
            $t->timestamp('read_at')->nullable();
            $t->timestamps();
        });
        // information_schema intentionally does not list temporary tables.
        $this->service = new class extends AchievementService
        {
            public function available(): bool
            {
                return true;
            }
        };
        DB::table('users')->insert(['id' => 1, 'name' => 'Test member', 'seedbonus' => 100, 'created_at' => now()]);
    }

    protected function tearDown(): void
    {
        if ($this->app) {
            DB::purge('achievement_test'); // Closing the connection drops all temporary tables.
            restore_error_handler();
            restore_exception_handler();
        }
    }

    public function test_fixed_rewards_pay_once_and_notifications_link_to_achievements(): void
    {
        config(['achievements.categories' => ['torrents' => config('achievements.categories.torrents')]]);
        for ($id = 1; $id <= 50; $id++) {
            DB::table('torrents')->insert(['id' => $id, 'owner' => 1]);
        }
        self::assertSame(3, $this->service->award(1));
        self::assertSame('950.00', User::find(1)->seedbonus);
        self::assertSame(['100.00', '250.00', '500.00'], DB::table('user_achievements')->orderBy('tier')->pluck('bonus_awarded')->all());
        self::assertSame(1, (int) User::find(1)->invites);
        self::assertSame(30, (int) User::find(1)->slots);
        self::assertSame([5, 10, 15], DB::table('user_achievements')->orderBy('tier')->pluck('tokens_awarded')->all());
        self::assertSame(3, DB::table('notifications')->count());
        self::assertSame(0, $this->service->award(1));
        self::assertSame('950.00', User::find(1)->seedbonus);
        self::assertSame(30, (int) User::find(1)->slots);
        $notice = json_decode(DB::table('notifications')->first()->data, true);
        self::assertSame('achievement_unlocked', $notice['type']);
        $tier = DB::table('user_achievements')->where('category', $notice['category'])->where('threshold', $notice['threshold'])->value('tier');
        self::assertSame($tier * 5, $notice['tokens']);
        self::assertStringEndsWith('#achievement-'.$notice['category'].'-'.$notice['threshold'], $notice['url']);
        self::assertSame(3, $this->service->progress(User::find(1))[0]['earned_count']);
    }

    public function test_metrics_ignore_self_reactions_incomplete_snatches_and_duplicate_seed_sessions(): void
    {
        DB::table('torrents')->insert([['id' => 1, 'owner' => 1], ['id' => 2, 'owner' => 2]]);
        DB::table('torrent_reactions')->insert([['user_id' => 1, 'torrent_id' => 1], ['user_id' => 1, 'torrent_id' => 2]]);
        DB::table('forum_posts')->insert([['id' => 1, 'user_id' => 1], ['id' => 2, 'user_id' => 2]]);
        DB::table('forum_post_likes')->insert([['user_id' => 1, 'post_id' => 1], ['user_id' => 1, 'post_id' => 2]]);
        DB::table('peers')->insert([
            ['user_id' => 1, 'torrent_id' => 1, 'active' => 1, 'seeder' => 1],
            ['user_id' => 1, 'torrent_id' => 1, 'active' => 1, 'seeder' => 1],
            ['user_id' => 1, 'torrent_id' => 2, 'active' => 0, 'seeder' => 1],
        ]);
        DB::table('history')->insert([
            ['user_id' => 1, 'torrent_id' => 1, 'actual_uploaded' => 1024, 'actual_downloaded' => 512, 'completed_at' => now()],
            ['user_id' => 1, 'torrent_id' => 2, 'actual_uploaded' => 2048, 'actual_downloaded' => 256, 'completed_at' => null],
        ]);
        $metrics = $this->service->metrics(User::find(1));
        self::assertSame(1, $metrics['torrent_reactions']);
        self::assertSame(1, $metrics['forum_reactions']);
        self::assertSame(1, $metrics['seeding']);
        self::assertSame(1, $metrics['snatches']);
        self::assertSame(3072, $metrics['uploaded']);
        self::assertSame(768, $metrics['downloaded']);
    }

    public function test_comment_reaction_achievements_ignore_self_reactions_and_pay_each_tier_once(): void
    {
        config(['achievements.categories' => ['comment_reactions' => config('achievements.categories.comment_reactions')]]);
        DB::table('comments')->insert([['id' => 1, 'user_id' => 1], ['id' => 2, 'user_id' => 2]]);
        DB::table('comment_reactions')->insert(['user_id' => 1, 'comment_id' => 1, 'reaction' => 'like']);
        self::assertSame(0, $this->service->metrics(User::find(1))['comment_reactions']);
        self::assertSame(0, $this->service->award(1));
        DB::table('comment_reactions')->insert(['user_id' => 1, 'comment_id' => 2, 'reaction' => 'thanks']);
        self::assertSame(1, $this->service->metrics(User::find(1))['comment_reactions']);
        self::assertSame(1, $this->service->award(1));
        self::assertSame('200.00', User::find(1)->seedbonus);
        DB::table('comment_reactions')->where('comment_id', 2)->delete();
        self::assertSame(0, $this->service->metrics(User::find(1))['comment_reactions']);
        DB::table('comment_reactions')->insert(['user_id' => 1, 'comment_id' => 2, 'reaction' => 'love']);
        self::assertSame(0, $this->service->award(1));
        self::assertSame('200.00', User::find(1)->seedbonus);
        self::assertSame(1, DB::table('user_achievements')->count());
    }

    public function test_failed_notification_rolls_back_award_and_balance(): void
    {
        DB::table('torrents')->insert(['owner' => 1]);
        DB::statement('DROP TEMPORARY TABLE notifications');
        // Recreate the shadow with an incompatible schema so no real notification table can be used.
        DB::statement('CREATE TEMPORARY TABLE notifications (id INT)');
        try {
            $this->service->award(1);
            self::fail('Expected notification insert to fail');
        } catch (QueryException $e) {
            self::assertSame(0, DB::table('user_achievements')->count());
            self::assertSame('100.00', User::find(1)->seedbonus);
            self::assertSame(0, (int) User::find(1)->invites);
            self::assertSame(0, (int) User::find(1)->slots);
        }
    }

    public function test_cap_zero_balance_and_profile_rendering(): void
    {
        config(['achievements.categories' => ['torrents' => config('achievements.categories.torrents')]]);
        DB::table('users')->where('id', 1)->update(['seedbonus' => 999990]);
        DB::table('torrents')->insert(['owner' => 1]);
        self::assertSame(1, $this->service->award(1));
        self::assertSame('999999.99', User::find(1)->seedbonus);
        self::assertSame('9.99', DB::table('user_achievements')->value('bonus_awarded'));
        self::assertSame(5, (int) User::find(1)->slots);
        $categories = $this->service->progress(User::find(1));
        $html = view('profile.partials.achievements', ['achievementCategories' => $categories])->render();
        self::assertStringContainsString('1 of 6 unlocked', $html);
        self::assertStringContainsString('9 torrents uploaded to go', $html);
        self::assertStringContainsString('+9.99 points', $html);
        self::assertStringContainsString('+250.00 points', $html);
        self::assertStringContainsString('+5 tokens', $html);
        self::assertStringContainsString('+10 tokens', $html);
        self::assertStringNotContainsString('% of bonus balance', $html);
        self::assertStringContainsString('aria-valuenow="10"', $html);
        DB::table('users')->insert(['id' => 2, 'name' => 'Zero balance', 'seedbonus' => 0, 'created_at' => now()]);
        DB::table('torrents')->insert(['owner' => 2]);
        self::assertSame(1, $this->service->award(2));
        self::assertSame('100.00', User::find(2)->seedbonus);
        self::assertSame(5, (int) User::find(2)->slots);
        self::assertSame(0, $this->service->award(2));
        self::assertSame(5, (int) User::find(2)->slots);
    }

    public function test_paused_payouts_leave_progress_visible_without_mutating_balances(): void
    {
        config(['achievements.awarding_enabled' => false]);
        DB::table('torrents')->insert(['owner' => 1]);
        self::assertSame(0, $this->service->award(1));
        self::assertSame('100.00', User::find(1)->seedbonus);
        self::assertSame(0, DB::table('user_achievements')->count());
        self::assertSame(0, DB::table('notifications')->count());
        $categories = $this->service->progress(User::find(1));
        self::assertTrue($categories[0]['tiers'][0]['qualified']);
    }

    public function test_invitation_milestones_count_registered_members_and_award_once(): void
    {
        config(['achievements.categories' => ['invited_users' => config('achievements.categories.invited_users')]]);
        self::assertSame(0, $this->service->award(1));
        for ($id = 2; $id <= 6; $id++) {
            DB::table('users')->insert(['id' => $id, 'name' => 'Invited member', 'seedbonus' => 0, 'invited_by' => 1, 'created_at' => now()]);
        }
        DB::table('users')->insert(['id' => 7, 'name' => 'Unrelated member', 'seedbonus' => 0, 'created_at' => now()]);
        self::assertSame(5, $this->service->metrics(User::find(1))['invited_users']);
        self::assertSame(2, $this->service->award(1));
        self::assertSame('450.00', User::find(1)->seedbonus);
        self::assertSame(0, $this->service->award(1));
        $category = $this->service->progress(User::find(1))[0];
        self::assertSame(2, $category['earned_count']);
        self::assertSame('5 members invited to go', $category['tiers'][2]['remaining']);
        // Historical successful registrations remain counted if an account is removed later.
        DB::table('users')->where('id', 2)->update(['deleted_at' => now()]);
        self::assertSame(5, $this->service->metrics(User::find(1))['invited_users']);
    }

    public function test_login_days_count_once_continue_and_reset_after_a_missed_day(): void
    {
        config(['queue.default' => 'sync', 'achievements.awarding_enabled' => false]);
        \Illuminate\Support\Carbon::setTestNow('2026-10-07 12:00:00');
        try {
            $this->service->recordLoginDay(1);
            $this->service->recordLoginDay(1);
            self::assertSame(1, (int) User::find(1)->login_streak_days);
            \Illuminate\Support\Carbon::setTestNow('2026-10-08 00:00:01');
            $this->service->recordLoginDay(1);
            self::assertSame(2, $this->service->metrics(User::find(1))['login_streak']);
            \Illuminate\Support\Carbon::setTestNow('2026-10-10 00:00:01');
            self::assertSame(0, $this->service->metrics(User::find(1))['login_streak']);
            $this->service->recordLoginDay(1);
            self::assertSame(1, (int) User::find(1)->login_streak_days);
        } finally {
            \Illuminate\Support\Carbon::setTestNow();
        }
    }

    public function test_login_rewards_and_vip_extension_are_paid_once(): void
    {
        config(['achievements.categories' => ['login_streak' => config('achievements.categories.login_streak')]]);
        $vipUntil = now()->addMonth()->startOfSecond();
        DB::table('users')->where('id', 1)->update(['login_streak_days' => 365, 'last_login_streak_date' => now()->toDateString(), 'vip_until' => $vipUntil]);
        self::assertSame(6, $this->service->award(1));
        $user = User::find(1);
        self::assertSame('50100.00', $user->seedbonus);
        self::assertSame(195, (int) $user->slots);
        self::assertSame(24, (int) $user->invites);
        self::assertSame(3, (int) $user->user_class);
        self::assertSame($vipUntil->copy()->addMonthsNoOverflow(6)->toDateTimeString(), $user->vip_until->toDateTimeString());
        self::assertSame(0, $this->service->award(1));
        $notice = DB::table('notifications')->get()->map(fn ($row) => json_decode($row->data, true))->first(fn ($data) => $data['tier'] === 6);
        self::assertSame(6, $notice['vip_months']);
        $html = view('profile.partials.achievements', ['achievementCategories' => $this->service->progress($user)])->render();
        self::assertStringContainsString('6 months VIP', $html);
        self::assertStringContainsString('id="achievement-login_streak-365"', $html);
    }

    public function test_single_torrent_duration_never_sums_and_excludes_owned_torrents(): void
    {
        config(['achievements.categories' => ['torrent_seedtime' => config('achievements.categories.torrent_seedtime')]]);
        DB::table('torrents')->insert([['id' => 1, 'owner' => 1], ['id' => 2, 'owner' => 2], ['id' => 3, 'owner' => 2]]);
        DB::table('history')->insert([
            ['user_id' => 1, 'torrent_id' => 1, 'seedtime' => 15552000],
            ['user_id' => 1, 'torrent_id' => 2, 'seedtime' => 604800],
            ['user_id' => 1, 'torrent_id' => 3, 'seedtime' => 604800],
        ]);
        self::assertSame(604800, $this->service->metrics(User::find(1))['torrent_seedtime']);
        self::assertSame(1, $this->service->award(1));
        self::assertSame('200.00', User::find(1)->seedbonus);
        self::assertSame(1, (int) User::find(1)->slots);
        self::assertSame(0, (int) User::find(1)->invites);
        self::assertSame(0, $this->service->award(1));
        DB::table('history')->where('torrent_id', 2)->update(['seedtime' => 15552000]);
        self::assertSame(5, $this->service->award(1));
        self::assertSame('5750.00', User::find(1)->seedbonus);
        self::assertSame(41, (int) User::find(1)->slots);
    }

    public function test_seeder_hero_thresholds_rewards_and_distinct_active_seeds(): void
    {
        config(['achievements.categories' => ['seeding' => config('achievements.categories.seeding')]]);
        for ($id = 1; $id <= 25; $id++) {
            DB::table('peers')->insert(['user_id' => 1, 'torrent_id' => $id, 'active' => 1, 'seeder' => 1]);
        }
        self::assertSame(1, $this->service->award(1));
        self::assertSame('125.00', User::find(1)->seedbonus);
        self::assertSame(0, (int) User::find(1)->slots);
        self::assertSame(0, (int) User::find(1)->invites);
        self::assertSame(0, $this->service->award(1));
    }

    public function test_banned_and_disabled_members_receive_no_rewards(): void
    {
        DB::table('torrents')->insert(['owner' => 1]);
        DB::table('users')->where('id', 1)->update(['enabled' => 'no']);
        self::assertSame(0, $this->service->award(1));
        DB::table('users')->where('id', 1)->update(['enabled' => 'yes', 'banned_until' => now()->addDay()]);
        self::assertSame(0, $this->service->award(1));
        self::assertSame(0, DB::table('notifications')->count());
    }
}
