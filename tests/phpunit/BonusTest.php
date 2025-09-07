<?php

namespace Gazelle;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use GazelleUnitTest\Helper;
use Gazelle\Enum\BonusItemPurchaseStatus;
use Gazelle\Enum\UserStatus;

class BonusTest extends TestCase {
    protected array $userList;
    protected array $tgroupList;

    public function tearDown(): void {
        if (isset($this->tgroupList)) {
            foreach ($this->tgroupList as $tgroup) {
                Helper::removeTGroup($tgroup, current($this->userList));
            }
        }
        if (isset($this->userList)) {
            foreach ($this->userList as $user) {
                $user->remove();
            }
        }
    }

    public function testBonusBasic(): void {
        $this->userList['giver']    = Helper::makeUser('bonusg.' . randomString(6), 'bonus', true);
        $this->userList['receiver'] = Helper::makeUser('bonusr.' . randomString(6), 'bonus', true);
        $startingPoints = 10000;

        $user  = $this->userList['giver'];
        $giver = new User\Bonus($user);
        $this->assertEquals(0.0, $giver->hourlyRate(), 'bonus-per-hour');
        $this->assertEquals(0, $giver->user()->bonusPointsTotal(), 'bonus-points-initial');
        $this->assertEquals(0, $giver->user()->tokenCount(), 'bonus-fltokens-intial');
        $this->assertCount(0, $giver->history(10, 0), 'bonus-history-initial');
        $this->assertEquals(['nr' => 0, 'total' => 0], $giver->summary(), 'bonus-summary-initial');
        $this->assertEquals(
            [
                'total_torrents' => 0,
                'total_size'     => 0,
                'hourly_points'  => 0.0,
                'daily_points'   => 0.0,
                'weekly_points'  => 0.0,
                'monthly_points' => 0.0,
                'yearly_points'  => 0.0,
                'points_per_gb'  => 0.0,
                ],
            $giver->userTotals(),
            'bonus-accrual-initial'
        );

        $giver->setPoints($startingPoints);
        $this->assertEquals(
            $startingPoints,
            $giver->user()->bonusPointsTotal(),
            'bonus-set-points',
        );

        $manager = new Manager\Bonus();
        $manager->flush();
        $this->assertCount(13, $manager->itemList(), 'bonus-item-list');
        $this->assertNull($manager->findBonusItemByLabel('nope'), 'bonus-item-null');

        // buy a token
        $flt = $manager->findBonusItemByLabel('token-1');
        $this->assertFalse($flt->needsPreparation(), 'bonus-token-no-prepare');
        $this->assertInstanceOf(BonusItem::class, $flt->flush(), 'bonus-item-flush');
        $this->assertEquals('', $flt->link(), 'bonus-item-link');
        $this->assertEquals('bonus.php', $flt->location(), 'bonus-item-location');
        $this->assertEquals(
            BonusItemPurchaseStatus::success,
            $flt->purchase($user, $flt->price()),
            'bonus-item-purchase-token-1',
        );
        $this->assertEquals(
            1,
            $giver->user()->tokenCount(),
            'bonus-fltokens-bought',
        );
        $this->assertEquals(
            $startingPoints - $flt->price(),
            $giver->user()->bonusPointsTotal(),
            'bonus-spent-points',
        );
        $history = $giver->history(10, 0);
        $this->assertEquals(
            '1 Freeleech Token',
            $history[0]['Title'],
            'bonus-history-title',
        );

        // buy a seedbox
        $seedbox = $manager->findBonusItemByLabel('seedbox');
        $this->assertEquals(
            'Seedbox Viewer',
            $seedbox->title(),
            'bonus-item-title',
        );
        $this->assertEquals(
            BonusItemPurchaseStatus::forbidden,
            $seedbox->purchase($user, $seedbox->price()),
            'bonus-item-purchase-seedbox-forbidden',
        );
        $user->setField('PermissionID', MEMBER)->modify();
        $this->assertEquals(
            BonusItemPurchaseStatus::success,
            $seedbox->purchase($user, $seedbox->price()),
            'bonus-item-purchase-seedbox-success',
        );
        $this->assertTrue($giver->user()->hasAttr('feature-seedbox'), 'giver-has-seedbox');
        $this->assertCount(2, $giver->history(10, 0), 'bonus-history-new');
        $this->assertFalse($seedbox->isRecurring(), 'bonus-item-recurring');
        $this->assertEquals(
            BonusItemPurchaseStatus::alreadyPurchased,
            $seedbox->purchase($user, 0),
            'bonus-item-purchase-seedbox-duplicate',
        );

        // not enough point to buy a fifty
        $flt50 = $manager->findBonusItemByLabel('token-3');
        $this->assertEquals(
            BonusItemPurchaseStatus::insufficientFunds,
            $flt50->purchase($user, $flt50->price()),
            'bonus-item-purchase-token-50',
        );

        $other1  = $manager->findBonusItemByLabel('other-1');
        $this->assertTrue($other1->needsPreparation(), 'bonus-token-other-prepare');
        $other50 = $manager->findBonusItemByLabel('other-3');
        $giver->addPoints(
            (float)($other1->price() + $other50->price())
        );
        $this->assertEquals(
            BonusItemPurchaseStatus::incomplete,
            $other50->purchase(
                $user,
                $other50->price(),
            ),
            'bonus-item-purchase-incomplete-other-50',
        );
        $this->userList['receiver']->toggleAttr('no-fl-gifts', true);
        $this->assertEquals(
            BonusItemPurchaseStatus::declined,
            $other50->purchase(
                $user,
                $other50->price(),
                [
                    'message'  => 'phpunit gift',
                    'receiver' => $this->userList['receiver'],
                ],
            ),
            'bonus-item-purchase-declined-other-50',
        );
        $this->userList['receiver']->toggleAttr('no-fl-gifts', false);
        $this->assertEquals(
            BonusItemPurchaseStatus::success,
            $other50->purchase(
                $user,
                $other50->price(),
                [
                    'message'  => 'phpunit gift',
                    'receiver' => $this->userList['receiver'],
                ],
            ),
            'bonus-item-purchase-other-50',
        );
        $offer = $manager->offerTokenOther($user);
        $this->assertEquals('other-1', $offer[0]->label(), 'bonus-item-all-I-can-give');

        // buy file count feature
        $fileCount = $manager->findBonusItemByLabel('file-count');
        $giver->addPoints((float)$fileCount->price());
        $this->assertEquals(
            BonusItemPurchaseStatus::success,
            $fileCount->purchase($user, $fileCount->price()),
            'bonus-item-purchase-file-count',
        );
        $this->assertTrue($user->hasAttr('feature-file-count'), 'giver-has-file-count');
        $this->assertEquals(
            BonusItemPurchaseStatus::alreadyPurchased,
            $fileCount->purchase($user, $fileCount->price()),
            'bonus-item-repurchase-file-count',
        );

        $this->assertEquals(
            $flt->price()
                + $other50->price()
                + $seedbox->price()
                + $fileCount->price(),
            $giver->pointsSpent(),
            'bonus-points-spent',
        );

        $latest = $giver->otherLatest($this->userList['receiver']);
        $this->assertEquals('50 Freeleech Tokens to Other', $latest['title'], 'bonus-item-given');

        $bbn = $manager->findBonusItemByLabel('title-bb-n');
        $giver->addPoints($bbn->price());
        $this->assertEquals(
            BonusItemPurchaseStatus::success,
            $bbn->purchase($user, $bbn->price(), ['title' => '[b]i got u[/b]']),
            'bonus-item-title-no-bb',
        );
        $this->assertEquals('i got u', $user->title(), 'bonus-item-user-has-title-no-bb');

        $bby = $manager->findBonusItemByLabel('title-bb-y');
        $giver->addPoints($bby->price());
        $this->assertEquals(
            BonusItemPurchaseStatus::success,
            $bby->purchase($user, $bby->price(), ['title' => '[b]i got u[/b]']),
            'bonus-item-title-yes-bb',
        );
        $this->assertEquals(
            '<strong>i got u</strong>',
            $user->title(),
            'bonus-item-user-has-title-yes-bb',
        );

        $off = $manager->findBonusItemByLabel('title-off');
        $this->assertEquals(
            BonusItemPurchaseStatus::success,
            $off->purchase($user, $off->price()),
            'bonus-item-title-off',
        );
        $this->assertEquals(
            '',
            $user->title(),
            'bonus-item-user-has-no-title',
        );

        $collage = $manager->findBonusItemByLabel('collage-1');
        $giver->addPoints($collage->price());
        $this->assertEquals(
            BonusItemPurchaseStatus::success,
            $collage->purchase($user, $collage->price()),
            'bonus-item-purchase-collage',
        );
        $this->assertEquals(
            $collage->price() * 2,
            $collage->priceForUser($giver->user()),
            'bonus-item-price-for-user'
        );
        $this->assertEquals(
            $collage->price(),
            $collage->priceForUser($this->userList['receiver']),
            'bonus-item-price-for-other-user'
        );

        $history = $giver->history(10, 0);
        $this->assertCount(8, $history, 'bonus-history-final');

        $this->assertEquals(
            [
                'nr' => 8,
                'total' => $flt->price()
                    + $other50->price()
                    + $seedbox->price()
                    + $fileCount->price()
                    + $collage->price()
                    + $bbn->price()
                    + $bby->price()
                    + $off->price()
            ],
            $giver->summary(),
            'bonus-summary-initial'
        );
        $this->assertTrue($giver->removePoints(1.125), 'bonus-taketh-away');

        $this->assertEquals(
            BonusItemPurchaseStatus::insufficientFunds,
            $bby->purchase($user, $bby->price(), ['title' => 'whatever']),
            'bonus-item-no-funds-title-yes-bb',
        );
        // higher userclasses get some items for free
        $user->setField('PermissionID', SYSOP)->modify();
        $this->assertEquals(
            BonusItemPurchaseStatus::success,
            $bby->purchase($user, $bby->price(), ['title' => 'whatever']),
            'bonus-item-free-title-yes-bb',
        );
        $this->assertEquals(
            BonusItemPurchaseStatus::incomplete,
            $bby->purchase($user, $bby->price()),
            'bonus-item-free-title-yes-bb',
        );

        $userBonus = new User\Bonus($user);
        $history = $userBonus->purchaseHistory();
        $this->assertCount(8, $history, 'bonus-history-count');
        $this->assertEquals(
            ['id', 'title', 'total', 'cost'],
            array_keys(current($history)),
            'bonus-history-shape',
        );
        $this->assertCount(0, $userBonus->seedList(5, 0), 'bonus-history-seedlist');
        $this->assertCount(0, $userBonus->poolHistory(), 'bonus-history-pool');

        // Here is as good a place as any
        $this->assertEquals(0, $manager->discount(), 'bonus-discount');
    }

    public static function providerBonusItem(): array {
        return [
            ['collage-1'],
            ['file-count'],
            ['invite'],
            ['seedbox'],
            ['other-1'],
            ['other-2'],
            ['other-3'],
            ['token-1'],
            ['token-2'],
            ['token-3'],
        ];
    }

    #[DataProvider('providerBonusItem')]
    public function testBonusBroke(string $label): void {
        $this->userList = [Helper::makeUser('bonusg.' . randomString(6), 'bonus', true)];
        $this->userList[0]->setField('PermissionID', MEMBER)->modify();
        $item = new Manager\Bonus()->findBonusItemByLabel($label);
        if (str_starts_with($label, 'other-')) {
            $this->userList[] = Helper::makeUser('bonusg.' . randomString(6), 'bonus', true);
            $this->assertEquals(
                BonusItemPurchaseStatus::insufficientFunds,
                $item->purchase($this->userList[0], $item->price(), ['receiver' => $this->userList[1]]),
                "bonus-item-broke-$label",
            );
        } else {
            $this->assertEquals(
                BonusItemPurchaseStatus::insufficientFunds,
                $item->purchase($this->userList[0], $item->price()),
                "bonus-item-broke-$label",
            );
        }
    }

    public function testBonusPurchaseOther(): void {
        $this->userList['giver']    = Helper::makeUser('bonusg.' . randomString(6), 'bonus', true);
        $this->userList['receiver'] = Helper::makeUser('bonusr.' . randomString(6), 'bonus', true);
        $manager = new Manager\Bonus();

        $this->assertEquals(
            BonusItemPurchaseStatus::incomplete,
            $manager->purchaseTokenOther(
                $this->userList['giver'],
                $this->userList['receiver'],
                'bad-label',
                '',
            ),
            'bonus-purchase-token-other-fail',
        );
        $this->assertEquals(
            BonusItemPurchaseStatus::insufficientFunds,
            $manager->purchaseTokenOther(
                $this->userList['giver'],
                $this->userList['receiver'],
                'other-1',
                '',
            ),
            'bonus-purchase-token-other-no-money',
        );

        $this->assertCount(0,
            $manager->offerTokenOther($this->userList['giver']),
            'bonus-offer-other-none',
        );
        new User\Bonus($this->userList['giver'])->addPoints(2000000);
        $this->assertCount(
            3,
            $manager->offerTokenOther($this->userList['giver']),
            'bonus-offer-other-all',
        );
        $this->assertEquals(
            BonusItemPurchaseStatus::success,
            $manager->purchaseTokenOther(
                $this->userList['giver'],
                $this->userList['receiver'],
                'other-1',
                '',
            ),
            'bonus-purchase-token-other-success',
        );
    }

    public function testBonusPool(): void {
        global $Cache;
        $Cache->delete_value("bonus_pool");
        $manager = new Manager\Bonus();
        $this->assertEquals(
            [],
            $manager->openPoolList(),
            'bonus-open-pool',
        );
    }

    public function testBonusInvite(): void {
        $user = Helper::makeUser('bonusinvite.' . randomString(6), 'bonus');
        $this->userList = [$user];

        $invite = new Manager\Bonus()->findBonusItemByLabel('invite');
        $this->assertTrue($invite->permitted($user), 'bonus-invite-yes');
        $user->toggleAttr('disable-invites', true);
        $this->assertFalse($invite->permitted($user), 'bonus-invite-no');

        $userBonus = new User\Bonus($user);
        $this->assertTrue(
            $userBonus->removePoints(1000, force: true),
            'bonus-remove-force'
        );
        $this->assertEquals(
            -1000,
            $user->bonusPointsTotal(),
            'bonus-points-negative'
        );
    }

    public function testBonusTitle(): void {
        $this->assertEquals(
            [],
            new Json\BonusItemTitle('nope', 'title')->payload(),
            'bonus-title-bad',
        );
        $this->assertEquals(
            ['phpunit'],
            new Json\BonusItemTitle('title-bb-n', '[b]phpunit[/b]')->payload(),
            'bonus-title-plain',
        );
        $this->assertEquals(
            ['<strong>phpunit</strong>'],
            new Json\BonusItemTitle('title-bb-y', '[b]phpunit[/b]')->payload(),
            'bonus-title-rich',
        );
    }

    public function testBonusUserOther(): void {
        $user = Helper::makeUser('bonusother.' . randomString(6), 'bonus', enable: false);
        $this->userList = [$user];

        $this->assertEquals(
            [
                'found'    => false,
                'username' => '#nope',
            ],
            new Json\BonusUserOther('#nope')->payload(),
            'bonus-user-other-404',
        );
        $this->assertEquals(
            [
                'found'    => true,
                'accept'   => true,
                'enabled'  => false,
                'id'       => $user->id,
                'username' => $user->username(),
            ],
            new Json\BonusUserOther($user->username())->payload(),
            'bonus-user-other-not-enabled',
        );
        $user->setField('Enabled', UserStatus::enabled->value)->modify();
        $this->assertEquals(
            [
                'found'    => true,
                'accept'   => true,
                'enabled'  => true,
                'id'       => $user->id,
                'username' => $user->username(),
            ],
            new Json\BonusUserOther($user->username())->payload(),
            'bonus-user-other-accept',
        );
        $user->toggleAttr('no-fl-gifts', true);
        $this->assertEquals(
            [
                'found'    => true,
                'accept'   => false,
                'enabled'  => true,
                'id'       => $user->id,
                'username' => $user->username(),
            ],
            new Json\BonusUserOther($user->username())->payload(),
            'bonus-user-other-no-fl',
        );
    }

    public function testAddPoints(): void {
        $this->userList = [
            Helper::makeUser('bonusadd.' . randomString(6), 'bonus', enable: false),
            Helper::makeUser('bonusadd.' . randomString(6), 'bonus', enable: true),
        ];
        // back to the future
        DB::DB()->prepared_query("
            INSERT INTO user_last_access (user_id, last_access) values (?, ?)
            ", $this->userList[1]->id, date('Y-m-d H:i:s', time() + 10)
        );

        $manager = new Manager\Bonus();
        $this->assertEquals(
            1,
            // but not too far
            $manager->addActivePoints(23456, date('Y-m-d H:i:s', time() + 5)),
            'bonus-add-active',
        );
        $this->assertEquals(
            0,
            $manager->addMultiPoints(789, []),
            'bonus-add-no-multi-points',
        );
        $this->assertEquals(
            2,
            $manager->addMultiPoints(
                12345,
                array_map(fn ($u) => $u->id, $this->userList),
            ),
            'bonus-add-multi-points',
        );
        $this->assertEquals(
            0,
            $manager->addUploadPoints(369,  date('Y-m-d H:i:s', time() + 1)),
            'bonus-add-upload-points',
        );
        $this->assertEquals(
            12345,
            $this->userList[0]->flush()->bonusPointsTotal(),
            'bonus-added-user-0',
        );

        $this->tgroupList[] =  Helper::makeTGroupMusic(
            name:       'bonus add ' . randomString(10),
            artistName: [[ARTIST_MAIN], ['phpunit bonus add ' . randomString(12)]],
            tagName:    ['hard.bop'],
            user:       $this->userList[1],
        );
        $torrent = Helper::makeTorrentMusic(
            tgroup: $this->tgroupList[0],
            user:  $this->userList[1],
            title: 'phpunit bonus add ' . randomString(10),
        );
        Helper::generateTorrentSeed($torrent, $this->userList[1]);
        $this->assertGreaterThan(
            0,
            $manager->addSeedPoints(24680),
            'bonus-add-seed-points',
        );
        $this->assertEquals(
            12345 + 23456 + 24680,
            $this->userList[1]->flush()->bonusPointsTotal(),
            'bonus-added-user-1',
        );

        $this->userList[1]->toggleAttr('no-fl-gifts', true);
        $this->assertGreaterThan(
            0,
            $manager->addGlobalPoints(7531),
            'bonus-add-global-points',
        );
        $this->assertEquals(
            12345 + 23456 + 24680,
            $this->userList[1]->flush()->bonusPointsTotal(),
            'bonus-added-no-global',
        );
        foreach ($this->userList as $u) {
            new User\Bonus($u)->setPoints(0.0);
        }
    }

    public function testUploadReward(): void {
        $this->userList[] = Helper::makeUser('bonusup.' . randomString(6), 'bonus');
        $this->tgroupList[] =  Helper::makeTGroupMusic(
            name:       'bonus ' . randomString(10),
            artistName: [[ARTIST_MAIN], ['phpunit bonus ' . randomString(12)]],
            tagName:    ['bop'],
            user:       $this->userList[0],
        );
        $reward = new BonusUploadReward();

        $torrent = Helper::makeTorrentMusic(
            tgroup: $this->tgroupList[0],
            user:  $this->userList[0],
            title: 'phpunit bonus ' . randomString(10),
            media: 'Vinyl',
            format: 'FLAC',
            encoding: '24bit Lossless',
        );
        $this->assertEquals(400, $reward->reward($torrent), 'bonus-reward-perfect-flac');

        $torrent = Helper::makeTorrentMusic(
            tgroup: $this->tgroupList[0],
            user:  $this->userList[0],
            title: 'phpunit bonus ' . randomString(10),
            media: 'CD',
            format: 'FLAC',
            encoding: 'Lossless',
        );
        $this->assertEquals(30, $reward->reward($torrent), 'bonus-reward-flac');

        $torrent = Helper::makeTorrentMusic(
            tgroup: $this->tgroupList[0],
            user:  $this->userList[0],
            title: 'phpunit bonus ' . randomString(10),
            media: 'Vinyl',
            format: 'MP3',
            encoding: '320',
        );
        $this->assertEquals(30, $reward->reward($torrent), 'bonus-reward-mp3');

        $this->tgroupList[] =  Helper::makeTGroupEBook('phpunit ebook title');
        $torrent = Helper::makeTorrentEBook(
            tgroup: $this->tgroupList[1],
            user:  $this->userList[0],
            description: 'phpunit bonus ebook ' . randomString(10),
        );
        // If the following test fails, look in bonus_upload_reward with:
        // select * from bonus_upload_reward where id_category = 3 order by lower(valid);
        // You may have to close out the current valid range with something like
        // update bonus_upload_reward set valid = tstzrange(lower(valid), now()) where now() <@ valid and id_category = 3;
        // insert into bonus_upload_reward (id_category, high, standard, low, valid) values (3, 10, 10, 10, tstzrange(now(), 'infinity'));
        $this->assertEquals(10, $reward->reward($torrent), 'bonus-reward-other');

        $this->assertGreaterThan(
            0,
            $reward->modifyCategory(
                CATEGORY_EBOOK,
                ['high' => 330, 'standard' => 220, 'low' => 110],
            ),
            'bonus-reward-modify'
        );
        $torrent->setField('created', date('Y-m-d H:i:s', time() + 1))->modify();
        $this->assertEquals(220, $reward->reward($torrent->flush()), 'bonus-reward-new-other');
        $reward->modifyCategory(
            CATEGORY_EBOOK,
            ['high' => 10, 'standard' => 10, 'low' => 10],
        );
    }

    public function testStats(): void {
        $eco = new Stats\Economic();
        $eco->flush();

        $total    = $eco->bonusTotal();
        $stranded = $eco->bonusStrandedTotal();

        $this->userList['bonus'] = Helper::makeUser('bonusstat.' . randomString(6), 'bonus', true);
        $bonus = new User\Bonus($this->userList['bonus']);
        $bonus->addPoints(98765);

        $eco->flush();
        $this->assertEquals(98765 + $total, $eco->bonusTotal(), 'bonus-total-points');
        $this->assertEquals($stranded, $eco->bonusStrandedTotal(), 'bonus-total-stranded-points');

        $this->userList['bonus']->setField('Enabled', UserStatus::disabled->value)->modify();
        $eco->flush();
        $this->assertEquals(98765 + $stranded, $eco->bonusStrandedTotal(), 'bonus-total-disabled-stranded-points');
    }
}
