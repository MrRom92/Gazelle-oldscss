<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;
use Phinx\Util\Literal;

final class HistoryUserEmail extends AbstractMigration {
    public function up(): void {
        $this->table('history_email', ['id' => false, 'primary_key' => 'id_history_email'])
            ->addColumn('id_history_email', 'integer', ['identity' => true])
            ->addColumn('id_user', 'integer')
            ->addColumn('ip', 'inet')
            ->addColumn('created', 'timestamp', ['timezone' => true, 'default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('email', Literal::from('citext'))
            ->addColumn('useragent', 'text')
            ->save();
        $this->query(
            <<<END_SQL
            insert into history_email
                (id_user, email, ip, created, useragent)
            select "UserID", "Email", "IP"::inet, created, useragent
            from relay.users_history_emails
END_SQL
        );
        $this->query("
            create index he_u_idx on history_email (id_user)
        ");
    }

    public function down(): void {
        $this->table('history_email')->drop()->save();
    }
}
