<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class BonusUploadBoost extends AbstractMigration {
    public function up(): void {
        $this->table('user_ordinal')
             ->insert([[
                    'name'          => 'bonus-upload-boost',
                    'description'   => 'How many upload boosts has this user earned',
                    'default_value' => 0,
                ]])
            ->save();
    }

    public function down(): void {
        $this->query("
            delete from user_ordinal where name = 'bonus-upload-boost';
        ");
    }
}
