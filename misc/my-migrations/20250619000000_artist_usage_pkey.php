<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class ArtistUsagePkey extends AbstractMigration {
    public function up(): void {
        $this->query("
            alter table artist_usage
                drop primary key,
                add primary key (artist_id, artist_role_id),
                modify role enum ('1','2','3','4','5','6','7','8')
        ");
    }

    public function down(): void {
        $this->query("
            alter table artist_usage
                drop primary key,
                add primary key (artist_id, role),
                modify role enum ('1','2','3','4','5','6','7','8') NOT NULL
        ");
    }
}
