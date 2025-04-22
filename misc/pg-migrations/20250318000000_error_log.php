<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class ErrorLog extends AbstractMigration {
    public function up(): void {
        $this->query("
            create table error_log (
                id_error_log int primary key generated always as identity,
                duration float   not null default 0.0,
                memory   bigint  not null default 0,
                nr_query integer not null default 0,
                nr_cache integer not null default 0,
                seen     integer not null default 1,
                id_user  integer not null default 0,
                created  timestamptz not null default current_timestamp,
                updated  timestamptz not null default current_timestamp,
                uri varchar(255) not null,
                digest     bytea not null,
                trace      text not null,
                request    jsonb not null default '[]',
                error_list jsonb not null default '[]',
                logged_var jsonb not null default '[]',
                unique (digest)
            );

        ");
        $this->query("
            create index on error_log (updated);
        ");
    }

    public function down(): void {
        $this->table('error_log')->drop()->save();
    }
}
