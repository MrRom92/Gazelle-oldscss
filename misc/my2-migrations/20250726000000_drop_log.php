<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class DropLog extends AbstractMigration {
    public function up(): void {
        $this->table('log')->drop()->save();
    }

    public function down(): void {
        $this->query("
            CREATE TABLE `log` (
                ID int NOT NULL AUTO_INCREMENT PRIMARY KEY,
                Message varchar(400) NOT NULL,
                Time datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                created datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                KEY Time (Time)
            )
        ");
    }
}
