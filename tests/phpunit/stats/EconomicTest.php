<?php

namespace Gazelle;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

// It would take too much scaffolding to verify exact values here, but this
// shows the SQL queries are syntactically valid which is just as useful.

class EconomicTest extends TestCase {
    public function testEconomicArray(): void {
        $this->assertGreaterThanOrEqual(
            0,
            count(new Stats\Economic()->biggestSeederList()),
            'eco-stats-biggest-seeder-list',
        );
    }

    public static function economicIntProvider(): array {
        return [
            // method             label
            ['bountyAvailable',  'eco-stats-bounty-available'],
            ['bountyTotal',      'eco-stats-bounty-total'],
            ['downloadTotal',    'eco-stats-download-total'],
            ['leecherTotal',     'eco-stats-leecher-total'],
            ['peerTotal',        'eco-stats-peer-total'],
            ['seederTotal',      'eco-stats-seeder-total'],
            ['snatchGrandTotal', 'eco-stats-seeder-grand-total'],
            ['snatchTotal',      'eco-stats-snatch-total'],
            ['torrentTotal',     'eco-stats-total-total'],
            ['uploadTotal',      'eco-stats-upload-total'],
            ['uploaderTotal',    'eco-stats-uploader-total'],
            ['userTotal',        'eco-stats-user-total'],
            ['userMfaTotal',     'eco-stats-user-mfa-total'],
            ['userPeerTotal',    'eco-stats-user-peer-total'],
        ];
    }

    #[DataProvider('economicIntProvider')]
    public function testEconomicInt(string $method, string $label): void {
        $this->assertGreaterThanOrEqual(
            0,
            new Stats\Economic()->$method(),
            $label
        );
    }
}
