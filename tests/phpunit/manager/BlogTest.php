<?php

namespace Gazelle;

use PHPUnit\Framework\TestCase;
use GazelleUnitTest\Helper;

class BlogTest extends TestCase {
    protected array $userList;
    protected Blog $blog;

    public function setUp(): void {
        $this->userList = [
            Helper::makeUser('blog.' . randomString(10), 'blog'),
            Helper::makeUser('blog.' . randomString(10), 'blog'),
        ];
    }

    public function tearDown(): void {
        if (isset($this->blog)) {
            $this->blog->remove();
        }
        $db = DB::DB();
        foreach ($this->userList as $user) {
            $user->remove();
        }
    }

    public function testBlogCreate(): void {
        $manager = new Manager\Blog();
        $initial = $manager->headlines();
        $this->blog = $manager->create(
            title     : 'phpunit blog',
            body      : 'phpunit blog body',
            thread    : null,
            important : 1,
            user      : $this->userList[0],
        );
        $this->assertTrue(Helper::recentDate($this->blog->created()), 'blog-created');
        $this->assertEquals('phpunit blog body', $this->blog->body(), 'blog-body');
        $this->assertEquals(1, $this->blog->important(), 'blog-important');
        $this->assertEquals(0, $this->blog->threadId(), 'blog-thread-id');
        $this->assertEquals('phpunit blog', $this->blog->title(), 'blog-title');
        $this->assertEquals($this->userList[0]->id, $this->blog->userId(), 'blog-userId');

        $this->assertEquals(1 + count($initial), count($manager->headlines()), 'blog-headlines');
        $this->assertEquals($this->blog->id, $manager->latest()->id, 'blog-latest');
        $this->assertEquals($this->blog->id, $manager->latestId(), 'blog-id-latest');
        $find = $manager->findById($this->blog->id);
        $this->assertEquals($this->blog->id, $find->id, 'blog-find');
        $this->assertEquals((int)strtotime($find->created()), $manager->latestEpoch(), 'blog-epoch');

        $this->assertInstanceOf(Blog::class, $this->blog->flush(), 'blog-flush');
        $this->assertEquals(1, $this->blog->remove(), 'blog-remove');
        unset($this->blog);
    }

    public function testBlogThread(): void {
        $title      = 'blog ' . randomString(10);
        $manager    = new Manager\Blog();
        $this->blog = $manager->create(
            title  : $title,
            body   : "$title body",
            thread : (new Manager\ForumThread())
                ->create(
                    new Forum(ANNOUNCEMENT_FORUM_ID),
                    $this->userList[0],
                    "thread $title title",
                    "thread $title body",
                ),
            important : 1,
            user      : $this->userList[0],
        );
        $this->assertEquals(
            "thread $title title",
            $this->blog->thread()->title(),
            'blog-thread-title',
        );
        $this->assertEquals(3, $this->blog->remove(), 'blog-thread-remove');
        unset($this->blog);
    }

    public function testBlogWitness(): void {
        $manager = new Manager\Blog();
        $this->blog = $manager->create(
            title     : 'phpunit blog witness',
            body      : 'phpunit blog witness body',
            thread    : null,
            important : 1,
            user      : $this->userList[0],
        );

        $witness = new WitnessTable\UserReadBlog();
        $this->assertNull($witness->lastRead($this->userList[1]), 'blog-user-not-read');
        $this->assertTrue($witness->witness($this->userList[1]));
        $this->assertEquals($this->blog->id, $witness->lastRead($this->userList[1]), 'blog-user-read');
    }

    public function testBlogNotification(): void {
        $manager = new Manager\Blog();
        $title   = 'phpunit blog notif';
        $this->blog    = $manager->create(
            title     : $title,
            body      : 'phpunit blog notif body',
            thread    : null,
            important : 1,
            user      : $this->userList[0],
        );

        $notifier = new User\Notification($this->userList[1]);
        // if this fails, the CI database has drifted (or another UT has clobbered the expected value here)
        $this->assertTrue($notifier->isActive('Blog'), 'activity-notified-blog');

        $alertList = $notifier->setDocument('index.php', '')->alertList();
        $this->assertArrayHasKey('Blog', $alertList, 'alert-has-blog');

        $alertBlog = $alertList['Blog'];
        $this->assertInstanceOf(User\Notification\Blog::class, $alertBlog, 'alert-blog-instance');
        $this->assertEquals('Blog', $alertBlog->type(), 'alert-blog-type');
        $this->assertEquals("Blog: $title", $alertBlog->title(), 'alert-blog-title');
        $this->assertEquals($this->blog->id, $alertBlog->context(), 'alert-blog-context-is-blog');
        $this->assertEquals($this->blog->url(), $alertBlog->notificationUrl(), 'alert-blog-url-is-blog');
    }
}
