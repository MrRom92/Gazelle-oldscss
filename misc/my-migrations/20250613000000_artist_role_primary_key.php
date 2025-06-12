<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class ArtistRolePrimaryKey extends AbstractMigration {
    public function up(): void {
        $this->query("
            alter table requests_artists
                drop primary key,
                add primary key (RequestID, AliasID, artist_role_id),
                modify Importance enum ('1','2','3','4','5','6','7','8')
        ");
        $this->query("
            alter table torrents_artists drop primary key,
                add primary key (GroupID, AliasID, artist_role_id),
                modify Importance enum ('1','2','3','4','5','6','7','8')
        ");
    }

    public function down(): void {
        $this->query("
            alter table requests_artists
                drop primary key,
                add primary key (RequestID, AliasID, Importance),
                modify Importance enum ('1','2','3','4','5','6','7','8') NOT NULL
        ");
        $this->query("
            alter table torrents_artists
                drop primary key,
                add primary key (GroupID, AliasID, Importance),
                modify Importance enum ('1','2','3','4','5','6','7','8') NOT NULL
        ");
    }
}
