<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class RatioWatch extends AbstractMigration {
    public function up(): void {
        $this->query("
            create table history_ratio_watch (
                id_history_ratio_watch int not null primary key generated always as identity,
                id_user int not null,
                ratio_watch tstzrange not null default tstzrange(now(), 'infinity'),
                upload_begin bigint not null,
                download_begin bigint not null,
                upload_end bigint,
                download_end bigint
            )
        ");
        $this->query("
            create index hrw_u_idx on history_ratio_watch (id_user)
        ");
    }

    public function down(): void {
        $this->table('history_ratio_watch')->drop()->save();
    }
}
