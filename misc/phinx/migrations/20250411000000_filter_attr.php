<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class FilterAttr extends AbstractMigration {
    public function up(): void {
        $this->table('user_attr')
            ->insert([
                [
                    'Name'        => 'show-all-tags',
                    'Description' => 'Show recommended tags',
                ],
            ])
            ->save();
    }

    public function down(): void {
        $this->query("
            delete from user_attr where Name = 'show-all-tags'
        ");
    }
}
