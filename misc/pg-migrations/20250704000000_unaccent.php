<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class Unaccent extends AbstractMigration {
    public function up(): void {
        $this->query("create extension unaccent");
    }

    public function down(): void {
        $this->query("drop extension unaccent");
    }
}
