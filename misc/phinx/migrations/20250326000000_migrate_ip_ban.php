<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class MigrateIpBan extends AbstractMigration {
    public function up(): void {
        $this->table('ip_bans')
            ->changeColumn('FromIP', 'integer', ['null' => false, 'signed' => false])
            ->changeColumn('ToIP', 'integer', ['null' => false, 'signed' => false])
            ->save();
    }

    public function down(): void {
        $this->table('ip_bans')
            ->changeColumn('FromIP', 'integer', ['null' => false])
            ->changeColumn('ToIP', 'integer', ['null' => false])
            ->save();
    }
}
