<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class RequestVotePeople extends AbstractMigration {
    public function up(): void {
        $this->table("request_vote_summary")->drop()->save();
        $this->query("
            create table request_vote_summary (
                id_request int not null primary key
                    references request on delete cascade,
                user_total int not null,
                bounty_total bigint not null
            )
        ");
        $this->query("
            insert into request_vote_summary (id_request, user_total, bounty_total)
            select r.id_request,
                coalesce(count(rv.id_user), 0),
                coalesce(sum(rv.bounty)::bigint, 0::bigint)
            from request r
            left join request_vote rv using (id_request)
            group by r.id_request
        ");

        $this->query("
            create index rvs_ut_idx on request_vote_summary (user_total)
        ");
        $this->query("
            create index rvs_bt_idx on request_vote_summary (bounty_total)
        ");
    }

    public function down(): void {
        $this->table("request_vote_summary")->drop()->save();
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
}
