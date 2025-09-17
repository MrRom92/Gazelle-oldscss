<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class TagReject extends AbstractMigration {
    public function up(): void {
        $this->table('tag_reject', ['id' => false, 'primary_key' => 'id_tag_reject'])
            ->addColumn('id_tag_reject', 'integer', ['identity' => true])
            ->addColumn('id_user', 'integer')
            ->addColumn('created', 'timestamp', ['timezone' => true, 'default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('name', 'string', ['length' => 40])
            ->save();
        $this->query("
            create unique index tr_name_uidx on tag_reject (name)
        ");
    }

    public function down(): void {
        $this->table('tag_reject')->drop()->save();
    }
}
