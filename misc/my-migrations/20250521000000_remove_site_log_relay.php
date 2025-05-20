<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class RemoveSiteLogRelay extends AbstractMigration {
    public function up(): void {
        $this->execute("
            DELETE FROM periodic_task_history
            WHERE periodic_task_id = (
                SELECT periodic_task_id FROM periodic_task WHERE classname = 'RelaySiteLog'
            )
        ");
        $this->execute("
            DELETE FROM periodic_task WHERE classname = 'RelaySiteLog'
        ");
    }

    public function down(): void {
        $this->table('periodic_task')
             ->insert([
                'name'        => 'Relay Site Log',
                'classname'   => 'RelaySiteLog',
                'description' => 'Copy new site log records to Postgres',
                'period'      => 1,
                'is_enabled'  => 0,
            ])
            ->save();
    }
}
