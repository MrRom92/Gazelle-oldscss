<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class TorrentClientUseragent extends AbstractMigration {
    public function up(): void {
        $this->table('history_useragent_tracker', ['id' => false, 'primary_key' => 'id_history_useragent_tracker'])
            ->addColumn('id_history_useragent_tracker', 'integer', ['identity' => true])
            ->addColumn('id_user', 'integer')
            ->addColumn('total', 'integer')
            ->addColumn('useragent', 'string', ['length' => 100])
            ->save();
        $this->execute("
            create index hut_u_idx on history_useragent_tracker (id_user)
        ");
    }

    public function down(): void {
        $this->table('history_useragent_tracker')->drop()->save();
    }
}
