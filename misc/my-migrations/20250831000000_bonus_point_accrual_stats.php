<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class BonusPointAccrualStats extends AbstractMigration {
    public function change(): void {
        $this->table('user_summary' )
             ->addColumn('bp_hourly_accrual', 'float', ['default' => 0.0])
             ->save();
    }
}
