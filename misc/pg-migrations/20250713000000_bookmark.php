<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class Bookmark extends AbstractMigration {
    public function up(): void {
        foreach (['artist', 'collage', 'request', 'tgroup'] as $object) {
            $this->table("bookmark_$object", ['id' => false, 'primary_key' => ["id_$object", 'id_user']])
                ->addColumn("id_$object", 'integer')
                ->addColumn('id_user', 'integer')
                ->addColumn('created', 'datetime', ['timezone' => true, 'default' => 'CURRENT_TIMESTAMP'])
                ->save();
            $letter = substr($object, 0, 1);
            $this->query("
                create index b{$letter}_u_idx on bookmark_$object (id_user)
            ");
        }
    }

    public function down(): void {
        foreach (['artist', 'collage', 'request', 'tgroup'] as $object) {
            $this->table("bookmark_$object")->drop()->save();
        }
    }
}
