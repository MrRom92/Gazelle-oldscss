<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class UserPwHistory extends AbstractMigration {
    public function up(): void {
        $this->table('history_announce', ['id' => false, 'primary_key' => 'id_history_announce'])
            ->addColumn('id_history_announce', 'integer', ['identity' => true])
            ->addColumn('id_user', 'integer')
            ->addColumn('ip', 'inet')
            ->addColumn('created', 'timestamp', ['timezone' => true, 'default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('previous', 'text')
            ->addColumn('useragent', 'text')
            ->save();
        $this->table('history_password', ['id' => false, 'primary_key' => 'id_history_password'])
            ->addColumn('id_history_password', 'integer', ['identity' => true])
            ->addColumn('id_user', 'integer')
            ->addColumn('ip', 'inet')
            ->addColumn('created', 'timestamp', ['timezone' => true, 'default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('useragent', 'text')
            ->save();

        $this->query("
            insert into history_announce
                (id_user, ip, created, previous, useragent)
            select \"UserID\", \"ChangerIP\"::inet, \"ChangeTime\", \"OldPassKey\", 'unknown'
            from relay.users_history_passkeys
        ");
        $this->query("
            insert into history_password
                (id_user, ip, created, useragent)
            select \"UserID\", \"ChangerIP\"::inet, \"ChangeTime\", useragent
            from relay.users_history_passwords
        ");

        $this->query("
            create index ha_u_idx on history_announce (id_user)
        ");
        $this->query("
            create index hp_u_idx on history_password (id_user)
        ");
    }

    public function down(): void {
        $this->table('history_announce')->drop()->save();
        $this->table('history_password')->drop()->save();
    }
}
