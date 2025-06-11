<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class RequestVoteSummary extends AbstractMigration {
    public function up(): void {
        $this->query("
            alter table request_vote add foreign key (id_request)
                references request on delete cascade
        ");
        $this->query("
            create table request_vote_summary (
                id_request int not null primary key
                    references request on delete cascade,
                bounty bigint not null
            )
        ");
        $this->query("
            insert into request_vote_summary (id_request, bounty)
            select r.id_request, coalesce(sum(rv.bounty)::bigint, 0::bigint)
            from request r
            left join request_vote rv using (id_request)
            group by r.id_request
        ");
        $this->query("
            create index rvs_b_idx on request_vote_summary (bounty)
        ");
    }

    public function down(): void {
        $this->query("alter table request_vote drop constraint request_vote_id_request_fkey");
        $this->table("request_vote_summary")->drop()->save();
    }
}
