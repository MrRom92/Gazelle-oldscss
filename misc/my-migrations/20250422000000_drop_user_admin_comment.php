<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

final class DropUserAdminComment extends AbstractMigration {
    public function up(): void {
        $this->table('users_info')
            ->removeColumn('AdminComment')
            ->save();
    }

    public function down(): void {
        $this->table('users_info')
            ->addColumn('AdminComment', 'text', ['limit' => 65536, 'null' => true])
            ->save();
    }
}
