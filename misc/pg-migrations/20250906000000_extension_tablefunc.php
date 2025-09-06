<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class ExtensionTablefunc extends AbstractMigration {
    public function up(): void {
        $this->query("create extension tablefunc");
    }

    public function down(): void {
        $this->query("drop extension tablefunc");
    }
}
