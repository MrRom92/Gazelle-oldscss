<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class LoginWatchCapture extends AbstractMigration {
    public function up(): void {
        $this->query('alter table login_attempts modify capture varchar(80)');
    }

    public function down(): void {
        $this->query('alter table login_attempts modify capture varchar(20)');
    }
}
