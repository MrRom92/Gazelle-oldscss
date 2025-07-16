<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class RequestArtist extends AbstractMigration {
    public function up(): void {
        $this->table('request_artist', ['id' => false, 'primary_key' => ['id_request', 'id_alias', 'id_artist_role']])
            ->addColumn('id_request', 'integer')
            ->addColumn('id_alias', 'integer')
            ->addColumn('id_artist_role', 'integer')
            ->addColumn('id_user', 'integer')
            ->addColumn('created', 'datetime', ['timezone' => true, 'default' => 'CURRENT_TIMESTAMP'])
            ->save();
    }

    public function down(): void {
        $this->table('request_artist')->drop()->save();
    }
}
