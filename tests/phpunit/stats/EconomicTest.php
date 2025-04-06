<?php

namespace Gazelle;

use PHPUnit\Framework\TestCase;

class EconomicTest extends TestCase {
    public function testEconomic(): void {
        $eco = new Stats\Economic();

        // It would take too much scaffolding to verify exact values,
        // but at least this shows the SQL queries are syntactically valid.
        $this->assertGreaterThanOrEqual(0, $eco->bountyAvailable(), 'eco-stats-bounty-available');
        $this->assertGreaterThanOrEqual(0, $eco->bountyTotal(), 'eco-stats-bounty-total');
        $this->assertGreaterThanOrEqual(0, $eco->downloadTotal(), 'eco-stats-download-total');
        $this->assertGreaterThanOrEqual(0, $eco->leecherTotal(), 'eco-stats-leecher-total');
        $this->assertGreaterThanOrEqual(0, $eco->peerTotal(), 'eco-stats-peer-total');
        $this->assertGreaterThanOrEqual(0, $eco->seederTotal(), 'eco-stats-seeder-total');
        $this->assertGreaterThanOrEqual(0, $eco->snatchGrandTotal(), 'eco-stats-seeder-grand-total');
        $this->assertGreaterThanOrEqual(0, $eco->snatchTotal(), 'eco-stats-snatch-total');
        $this->assertGreaterThanOrEqual(0, $eco->torrentTotal(), 'eco-stats-total-total');
        $this->assertGreaterThanOrEqual(0, $eco->uploadTotal(), 'eco-stats-upload-total');
        $this->assertGreaterThanOrEqual(0, $eco->userTotal(), 'eco-stats-user-total');
        $this->assertGreaterThanOrEqual(0, $eco->userMfaTotal(), 'eco-stats-user-mfa-total');
        $this->assertGreaterThanOrEqual(0, $eco->userPeerTotal(), 'eco-stats-user-peer-total');
    }
}
