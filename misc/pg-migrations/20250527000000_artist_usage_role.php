<?php

declare(strict_types=1);

// phpcs:disable PSR1.Files.SideEffects.FoundWithSymbols
require_once __DIR__ . '/../../lib/config.php';
// phpcs:enable PSR1.Files.SideEffects.FoundWithSymbols

use Phinx\Migration\AbstractMigration;

final class ArtistUsageRole extends AbstractMigration {
    public function change(): void {
        $this->query("
            drop foreign table if exists relay.artist_usage
        ");
        $this->query("
            import foreign schema " . MYSQL_DB
                . " limit to (artist_usage) from server relayer into relay;
        ");
    }
}
