<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class RequestSearchArtistTitle extends AbstractMigration {
    public function up(): void {
        $this->query("
            alter table request rename column encoding to encoding_str
        ");
        $this->query("
            alter table request rename column format to format_str
        ");
        $this->query("
            alter table request rename column media to media_str
        ");
        $this->query("
            alter table request
                add column tag int[],
                drop column title_ts,
                add column artist_title_ts tsvector
        ");
        $this->query("
            update request set
                artist_title_ts = upd.artist_title_ts
            from (
                select r.id_request,
                    to_tsvector('simple', coalesce(string_agg(aa.\"Name\", ' '), '')
                        || ' ' || r.title
                    ) as artist_title_ts
                from request r
                left join relay.requests_artists ra on    (ra.\"RequestID\" = r.id_request)
                left join relay.artists_alias    aa using (\"AliasID\")
                left join relay.artist_role      ar on    (ar.artist_role_id = ra.artist_role_id)
                where (ar.slug is null or ar.slug != 'guest')
                group by r.id_request
            ) upd
            where request.id_request = upd.id_request
        ");
        /*
        TODO: this should done after the code has reached production
        $this->query("
            alter table request alter column artist_title_ts set not null
        ");
        */
        $this->query("create index rq_at_idx ON request USING gist (artist_title_ts)");
        $this->query("create index rq_t_idx ON request USING gist (title gist_trgm_ops)");
    }

    public function down(): void {
        $this->query("drop index rq_at_idx");
        $this->query("drop index rq_t_idx");
        $this->query("
            alter table request
                drop column tag,
                drop column artist_title_ts,
                add column
                    title_ts tsvector generated always as (to_tsvector('simple', title)) stored
        ");
        $this->query("
            alter table request rename column encoding_str to encoding
        ");
        $this->query("
            alter table request rename column format_str to format
        ");
        $this->query("
            alter table request rename column media_str to media
        ");
    }
}
