<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class BonusDiscount extends AbstractMigration {
    public function up(): void {
        $this->execute("
            INSERT IGNORE INTO site_options (Name, `Value`, Comment)
            VALUES ('bonus-discount', '0', 'Bonus store discount (0 = no discount, 100 = everything free)')
        ");
    }

    public function down(): void {
        $this->execute("
            DELETE FROM site_options WHERE Name = 'bonus-discount'
        ");
    }
}
