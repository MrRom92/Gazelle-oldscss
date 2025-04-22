<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class MigrateEmailBlacklist extends AbstractMigration {
    public function up(): void {
        $this->query("
            create table email_blacklist (
                id_email_blacklist int primary key generated always as identity,
                created timestamptz not null default current_timestamp,
                id_user int not null,
                email text not null,
                comment text not null
            )
        ");

        $this->query('
            insert into email_blacklist (created, id_user, email, comment)
            select r."Time", r."UserID", r."Email", r."Comment"
            from relay.email_blacklist r
        ');
    }

    public function down(): void {
        $this->query("
            drop table email_blacklist
        ");
    }
}
