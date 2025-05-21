<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class RelayRequest extends AbstractMigration {
    public function up(): void {
        $this->table('periodic_task')
             ->insert([
                'name'        => 'Relay Database',
                'classname'   => 'RelayDatabase',
                'description' => 'Copy new/updated records to Postgres',
                'period'      => 1,
                'is_enabled'  => 0,
            ])
            ->save();
    }

    public function down(): void {
        $this->execute("
            delete from relay.periodic_task where classname = 'RelayDatabase'
        ");
    }
}
