<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class BookmarkTgroupSequence extends AbstractMigration {
    public function up(): void {
        $this->table('bookmark_tgroup')
            ->addColumn('seq', 'integer', ['null' => true])
            ->save();
        /* We want tgroup bookmark sequences to be unique,
         * so we'll just use the id for the time being until
         * the table is remigrated.
         */
        $this->query("
            update bookmark_tgroup set seq = id_tgroup
        ");
        $this->table('bookmark_tgroup')
            ->changeColumn('seq', 'integer')
            ->save();
    }

    public function down(): void {
        $this->table('bookmark_tgroup')
            ->removeColumn('seq')
            ->save();
    }
}
