<?php

declare(strict_types=1);

// phpcs:disable PSR1.Files.SideEffects.FoundWithSymbols
require_once __DIR__ . '/../../lib/config.php';
// phpcs:enable PSR1.Files.SideEffects.FoundWithSymbols

use Phinx\Migration\AbstractMigration;

final class CategoryBonusScale extends AbstractMigration {
    public function up(): void {
        $this->query("
            drop foreign table if exists relay.category
        ");
        $this->query("
            import foreign schema " . MYSQL_DB
                . " limit to (category) from server relayer into relay;
        ");
        $this->table('category')
            ->addColumn('bonus_scale', 'float', ['default' => '1.0'])
            ->save();
    }

    public function down(): void {
        $this->query("
            drop foreign table if exists relay.category
        ");
        $this->query("
            import foreign schema " . MYSQL_DB
                . " limit to (category) from server relayer into relay;
        ");
        $this->table('category')
            ->removeColumn('bonus_scale')
            ->save();
    }
}
