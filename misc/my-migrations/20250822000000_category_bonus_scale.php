<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CategoryBonusScale extends AbstractMigration {
    public function up(): void {
        $this->table('category')
            ->addColumn('bonus_scale', 'float', ['default' => '1.0'])
            ->save();
        $this->execute('
            CREATE FUNCTION IF NOT EXISTS category_bonus_accrual(size bigint, seedtime float, seeders integer, scale float)
            RETURNS float DETERMINISTIC NO SQL
            RETURN (size / scale) / pow(1024, 3) * (0.0433 + (0.07 * ln(1 + seedtime/24)) / pow(greatest(seeders, 1), 0.35))
        ');
        // 0.0433 * 24 = 1.0392, 0.07 * 24 = 1.68
        $this->execute('
            CREATE FUNCTION IF NOT EXISTS future_bonus_accrual(size bigint, seedtime float, seeders integer, scale float, days float)
            RETURNS float DETERMINISTIC NO SQL
            RETURN (size / scale) / (1024*1024*1024)
                * (
                    1.0392 * days
                    + 1.68 * (
                        (seedtime / 24 + days + 1) * (ln(seedtime / 24 + days + 1) - 1)
                        - (seedtime / 24 + 1) * (ln(seedtime / 24 + 1) - 1)
                    )
                    / pow(greatest(seeders, 1), 0.35)
                );
        ');
    }

    public function down(): void {
        $this->table('category')
            ->removeColumn('bonus_scale')
            ->save();
        $this->execute('
            DROP FUNCTION IF EXISTS category_bonus_accrual
        ');
        $this->execute('
            DROP FUNCTION IF EXISTS future_bonus_accrual
        ');
    }
}
