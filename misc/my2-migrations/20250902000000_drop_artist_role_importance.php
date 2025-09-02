<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class DropArtistRoleImportance extends AbstractMigration {
    public function up(): void {
        $this->query("
            alter table artist_usage drop column role
        ");
        $this->query("
            alter table requests_artists drop column Importance
        ");
        $this->query("
            alter table torrents_artists drop column Importance
        ");
    }

    public function down(): void {
        $this->query("
            alter table artist_usage add column role enum ('1','2','3','4','5','6','7','8')
        ");
        $this->query("
            alter table requests_artists add column Importance enum('1','2','3','4','5','6','7','8')
        ");
        $this->query("
            alter table torrents_artists add column Importance enum('1','2','3','4','5','6','7','8')
        ");
    }
}
