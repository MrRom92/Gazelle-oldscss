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
        foreach ($this->userList as $user) {
            $user->remove();
        }
    }

    public function testBlogCreate(): void {
        $manager = new Manager\Blog();
        $initial = $manager->headlines();
        $this->blog = $manager->create(
            title  : 'phpunit blog',
            body   : 'phpunit blog body',
            notify : false,
            thread : null,
            user   : $this->userList[0],
        );
        $this->assertEquals(
            $this->blog->id,
            $manager->findById($this->blog->id)?->id,
            'blog-find'
        );
        $this->assertEquals($this->blog->id, $manager->latestId(), 'blog-latest');
        $this->assertTrue(Helper::recentDate($this->blog->created()), 'blog-created-date');
        $this->assertEquals('phpunit blog body', $this->blog->body(), 'blog-body');
        $this->assertTrue(Helper::recentDate($this->blog->created()), 'blog-created-date');
        $this->assertLessThan(2, time() - $manager->latestEpoch(), 'blog-created-epoch');
        $this->assertLessThan(2, time() - $this->blog->createdEpoch(), 'blog-created-epoch');
        $this->assertFalse($this->blog->notify(), 'blog-no-notify');
        $this->assertEquals('phpunit blog', $this->blog->title(), 'blog-title');
        $this->assertEquals(0, $this->blog->threadId(), 'blog-thread-id');
        $this->assertEquals($this->userList[0]->id, $this->blog->userId(), 'blog-userId');
        $this->assertEquals(
            "<a href=\"blog.php?id={$this->blog->id}#blog{$this->blog->id}\">phpunit blog</a>",
            $this->blog->link(),
            'blog-link'
        );

        $this->assertEquals(
            min(20, 1 + count($initial)),
            count($manager->headlines()),
            'blog-headlines'
        );
        $this->assertEquals($this->blog->id, $manager->latestId(), 'blog-id-latest');
        $this->assertEquals($this->blog->id, $manager->latest()?->id, 'blog-latest');
        $find = $manager->findById($this->blog->id);
        $this->assertEquals($this->blog->id, $find?->id, 'blog-find');
        $this->assertEquals(
            (int)strtotime((string)$find?->created()),
            $manager->latestEpoch(),
            'blog-epoch'
        );

        $this->assertInstanceOf(Blog::class, $this->blog->flush(), 'blog-flush');
        $this->assertEquals(1, $this->blog->remove(), 'blog-remove');
    }

    public function testBlogThread(): void {
        $title      = 'blog ' . randomString(10);
        $manager    = new Manager\Blog();
        $this->blog = $manager->create(
            title  : $title,
            body   : "$title body",
            notify : false,
            thread : (new Manager\ForumThread())
                ->create(
                    new Forum(ANNOUNCEMENT_FORUM_ID),
                    $this->userList[0],
                    "thread $title title",
                    "thread $title body",
                ),
            user   : $this->userList[0],
        );
        $this->assertFalse($this->blog->notify(), 'blog-no-notify');
        $this->assertEquals(
            "thread $title title",
            $this->blog->thread()?->title(),
            'blog-thread-title',
        );
        $this->assertEquals(1, $this->blog->removeThread(), 'blog-thread-detach');
        $this->assertEquals(1, $this->blog->remove(), 'blog-thread-remove');
        unset($this->blog);
    }

    public function testBlogWitness(): void {
        $manager = new Manager\Blog();
        $this->blog = $manager->create(
            title  : 'phpunit blog witness',
            body   : 'phpunit blog witness body',
            notify : false,
            thread : null,
            user   : $this->userList[0],
        );

        $witness = new WitnessTable\UserReadBlog();
        $this->assertNull($witness->lastRead($this->userList[1]), 'blog-user-not-read');
        $this->assertTrue($witness->witness($this->userList[1]));
        $this->assertEquals($this->blog->id, $witness->lastRead($this->userList[1]), 'blog-user-read');
    }

    public function testBlogNotification(): void {
        $manager    = new Manager\Blog();
        $title      = 'phpunit blog notif';
        $this->blog = $manager->create(
            title  : $title,
            body   : 'phpunit blog notif body',
            thread : null,
            notify : true,
            user   : $this->userList[0],
        );
        $this->assertTrue($this->blog->notify(), 'blog-notify');

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
