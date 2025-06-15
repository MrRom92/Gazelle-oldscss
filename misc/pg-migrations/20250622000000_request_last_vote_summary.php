<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class RequestLastVoteSummary extends AbstractMigration {
    public function up(): void {
        $this->query("
            alter table request
                alter column last_vote drop default,
                alter column last_vote drop not null
        ");
        $this->query("
            alter table request_vote_summary
                add column last_vote timestamptz
        ");
        $this->query("
            delete from request_vote_summary
        ");
        $this->query("
            insert into request_vote_summary (id_request, user_total, bounty_total, last_vote)
            select r.id_request,
                coalesce(count(rv.id_user), 0),
                coalesce(sum(rv.bounty)::bigint, 0::bigint),
                coalesce(max(rv.created), '-infinity')
            from request r
            left join request_vote rv using (id_request)
            group by r.id_request
        ");
        $this->query("
            alter table request_vote_summary
                alter column last_vote set not null
        ");
    }

    public function down(): void {
        $this->query("
            alter table request
                alter column last_vote set default current_timestamp,
                alter column last_vote set not null
        ");
        $this->query("
            alter table request_vote_summary
                drop column last_vote
        ");
    }
}
