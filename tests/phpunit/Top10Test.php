<?php

namespace Gazelle;

use PHPUnit\Framework\TestCase;
use GazelleUnitTest\Helper;

class Top10Test extends TestCase {
    protected User $user;

    public function setUp(): void {
        $this->user = Helper::makeUser('topten.' . randomString(6), 'topten');
    }

    public function tearDown(): void {
        $this->user->remove();
    }

    public function testTop10TorrentBasic(): void {
        $top10 = new Top10\Torrent(FORMAT, $this->user);
        global $Cache;

        $key = "T10_all_10_" . trim(signature('', TOP10_SALT), '=');
        $Cache->delete_multi([$key, "{$key}_lock"]);
        $this->assertEquals(
            [],
            $top10->getTopTorrents([]),
            'top10-t-basic'
        );
        $this->assertEquals(
            [],
            $top10->getTopTorrents([]),
            'top10-t-cached-basic'
        );

        $key = "T10_all_10_" . trim(signature('FLAC', TOP10_SALT), '=');
        $Cache->delete_multi([$key, "{$key}_lock"]);
        $this->assertEquals(
            [],
            $top10->getTopTorrents(['format' => 'FLAC']),
            'top10-t-format'
        );
        $key = "T10_all_10_" . trim(signature('FAKE', TOP10_SALT), '=');
        $Cache->delete_multi([$key, "{$key}_lock"]);
        $this->assertEquals(
            [],
            $top10->getTopTorrents(['format' => 'FAKE']),
            'top10-t-fake-format'
        );

        $key = "T10_all_10_" . trim(signature('hide', TOP10_SALT), '=');
        $Cache->delete_multi([$key, "{$key}_lock"]);
        $this->assertEquals(
            [],
            $top10->getTopTorrents(['freeleech' => 'hide']),
            'top10-t-freeleech'
        );

        $key = "T10_all_10_" . trim(signature('phpunit.a,phpunit.b', TOP10_SALT), '=');
        $Cache->delete_multi([$key, "{$key}_lock"]);
        $this->assertEquals(
            [],
            $top10->getTopTorrents([
                'tags' => 'phpunit.a,phpunit.b',
            ]),
            'top10-t-tag-any'
        );
        $key = "T10_all_10_" . trim(signature('', TOP10_SALT), '=');
        $Cache->delete_multi([$key, "{$key}_lock"]);
        $this->assertEquals(
            [],
            $top10->getTopTorrents([
                'tags' => '',
            ]),
            'top10-t-tag-none'
        );

        $key = "T10_all_10_" . trim(signature('phpunit.a,phpunit.b|all', TOP10_SALT), '=');
        $Cache->delete_multi([$key, "{$key}_lock"]);
        $this->assertEquals(
            [],
            $top10->getTopTorrents([
                'tags'   => 'phpunit.a,phpunit.b',
                'anyall' => 'all',
            ]),
            'top10-t-tag-all'
        );

        $key = "T10_all_10_" . trim(signature('xcludedartiste', TOP10_SALT), '=');
        $Cache->delete_multi([$key, "{$key}_lock"]);
        $this->assertEquals(
            [],
            $top10->getTopTorrents(['excluded_artists' => 'xcludedartiste']),
            'top10-t-artist'
        );
        $key = "T10_all_10_" . trim(signature('', TOP10_SALT), '=');
        $Cache->delete_multi([$key, "{$key}_lock"]);
        $this->assertEquals(
            [],
            $top10->getTopTorrents(['excluded_artists' => '']),
            'top10-t-no-artist'
        );
    }

    public function testTop10TorrentOrder(): void {
        $top10 = new Top10\Torrent(FORMAT, $this->user);
        global $Cache;

        $key = "T10_snatched_10_" . trim(signature('', TOP10_SALT), '=');
        $Cache->delete_multi([$key, "{$key}_lock"]);
        $this->assertEquals(
            [],
            $top10->getTopTorrents([], 'snatched'),
            'top10-t-snatched'
        );

        $key = "T10_seeded_10_" . trim(signature('', TOP10_SALT), '=');
        $Cache->delete_multi([$key, "{$key}_lock"]);
        $this->assertEquals(
            [],
            $top10->getTopTorrents([], 'seeded'),
            'top10-t-seeded'
        );

        $key = "T10_data_10_" . trim(signature('', TOP10_SALT), '=');
        $Cache->delete_multi([$key, "{$key}_lock"]);
        $this->assertEquals(
            [],
            $top10->getTopTorrents([], 'data'),
            'top10-t-data'
        );
    }

    public function testTop10TorrentInterval(): void {
        $top10 = new Top10\Torrent(FORMAT, $this->user);
        global $Cache;

        $key = "T10_day_10_" . trim(signature('', TOP10_SALT), '=');
        $Cache->delete_multi([$key, "{$key}_lock"]);
        $this->assertEquals(
            [],
            $top10->getTopTorrents([], 'day'),
            'top10-t-day'
        );

        $key = "T10_week_10_" . trim(signature('', TOP10_SALT), '=');
        $Cache->delete_multi([$key, "{$key}_lock"]);
        $this->assertEquals(
            [],
            $top10->getTopTorrents([], 'week'),
            'top10-t-week'
        );

        $key = "T10_month_10_" . trim(signature('', TOP10_SALT), '=');
        $Cache->delete_multi([$key, "{$key}_lock"]);
        $this->assertEquals(
            [],
            $top10->getTopTorrents([], 'month'),
            'top10-t-month'
        );

        $key = "T10_year_10_" . trim(signature('', TOP10_SALT), '=');
        $Cache->delete_multi([$key, "{$key}_lock"]);
        $this->assertEquals(
            [],
            $top10->getTopTorrents([], 'year'),
            'top10-t-year'
        );
    }
}
