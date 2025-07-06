<?php

namespace Gazelle;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class UserRankTest extends TestCase {
    public static function dataDimension(): array {
        return [
            ['ArtistsAdded'],
            ['BonusPoints'],
            ['BountySpent'],
            ['CollageContributed'],
            ['CollageCreated'],
            ['CommentTorrent'],
            ['DataDownload'],
            ['DataUpload'],
            ['ForumPosts'],
            ['ReleaseVotes'],
            ['RequestsFilled'],
            ['Uploads'],
        ];
    }

    #[DataProvider('dataDimension')]
    public function testUserRankDimension(string $classname): void {
        // just verify that the SQL runs with no errors
        $this->expectNotToPerformAssertions();
        $class = "Gazelle\\UserRank\\Dimension\\$classname";
        /** @var UserRank\AbstractUserRank $dimension */
        $dimension = new $class();
        $dimension->bucketList();
        // flush the cache do allow testUserRankWeight to achieve greater coverage
        global $Cache;
        $Cache->delete_value($dimension->cacheKey());
    }

    public function testUserRankWeight(): void {
        $userRank = new UserRank(
            new UserRank\Configuration(RANKING_WEIGHT),
            [
                'downloaded'     => 0,
                'uploaded'       => STARTING_UPLOAD,
                'uploads'        => 0,
                'requests'       => 0,
                'posts'          => 0,
                'bounty'         => 0,
                'artists'        => 0,
                'collage-add'    => 0,
                'collage-create' => 0,
                'votes'          => 0,
                'bonus'          => 0,
                'comment-t'      => 0,
            ],
        );
        $this->assertSame(0, $userRank->score(), 'userrank-score');
        $this->assertSame(0, $userRank->rank('uploaded'), 'userrank-uploaded');
        $this->assertSame(STARTING_UPLOAD, $userRank->raw('uploaded'), 'userrank-raw-uploaded');
    }
}
