<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class RemoveTaskTorrentHistory extends AbstractMigration {
    public function up(): void {
        $this->execute("
            DELETE pth
            FROM periodic_task pt
            INNER JOIN periodic_task_history pth USING (periodic_task_id)
            WHERE pt.classname = 'TorrentHistory'
        ");
        $this->execute("
            DELETE FROM periodic_task WHERE classname = 'TorrentHistory'
        ");
    }

    public function down(): void {
        $this->table('periodic_task')
            ->insert([[
                 'name'        => 'Ratio Watch - Torrent History',
                 'classname'   => 'TorrentHistory',
                 'description' => 'Calculates seeding torrent counts',
                 'period'      => 60 * 60,
             ]])
             ->save();
    }
}
