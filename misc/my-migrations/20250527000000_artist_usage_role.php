<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

final class ArtistUsageRole extends AbstractMigration {
    public function change(): void {
        $this->table('artist_usage')
            ->addColumn('artist_role_id', 'integer', ['limit' => MysqlAdapter::INT_TINY, 'default' => 1])
            ->changeColumn('role', 'enum', ['values' => ['0', '1', '2', '3', '4', '5', '6', '7', '8']])
            ->save();
    }
}
