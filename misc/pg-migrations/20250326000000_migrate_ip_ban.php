<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class MigrateIpBan extends AbstractMigration {
    public function up(): void {
        $this->query("
            create table ip_ban (
                id_ip_ban int primary key generated always as identity,
                ip inet not null,
                is_active boolean not null default true,
                constraint ib_unique_ip exclude using gist (ip inet_ops with &&)
            )
        ");
        $this->query("
            create index on ip_ban using gist (ip inet_ops)
        ");
        $this->query("
            create table ip_ban_hist (
                id_ip_ban_hist int primary key generated always as identity,
                id_ip_ban int not null references ip_ban on delete cascade,
                id_user int not null default 0,
                created timestamptz not null default current_timestamp,
                note text not null
            )
        ");
        $this->query("
            create index on ip_ban_hist (created)
        ");
    }

    public function down(): void {
        $this->table('ip_ban_hist')->drop()->save();
        $this->table('ip_ban')->drop()->save();
    }
}
