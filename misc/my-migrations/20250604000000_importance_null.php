<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class ImportanceNull extends AbstractMigration {
    public function up(): void {
        $this->query("
            alter table artist_usage modify role enum('1','2','3','4','5','6','7','8')
        ");
        $this->query("
            alter table requests_artists modify Importance enum('1','2','3','4','5','6','7','8')
        ");
        $this->query("
            alter table torrents_artists modify Importance enum('1','2','3','4','5','6','7','8')
        ");
    }

    public function down(): void {
        $this->query("
            alter table artist_usage modify role enum('1','2','3','4','5','6','7','8') NOT NULL
        ");
        $this->query("
            alter table requests_artists modify Importance enum('1','2','3','4','5','6','7','8') NOT NULL
        ");
        $this->query("
            alter table torrents_artists modify Importance enum('1','2','3','4','5','6','7','8') NOT NULL
        ");
    }
}
