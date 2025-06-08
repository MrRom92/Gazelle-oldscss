<?php

namespace Gazelle\Manager;

class Request extends \Gazelle\BaseManager {
    final public const ID_KEY = 'zz_r_%d';

    public function create(
        \Gazelle\User $user,
        int $bounty,
        int $categoryId,
        int $year,
        string $title,
        ?string $image,
        string $description,
        string $recordLabel,
        string $catalogueNumber,
        int $releaseType,
        string $encodingList,
        string $formatList,
        string $mediaList,
        string $logCue,
        bool $checksum,
        string $oclc,
        int|null $groupId = null,
    ): \Gazelle\Request {
        self::$db->prepared_query('
            INSERT INTO requests (
                LastVote, Visible, UserID, CategoryID, Title, Year, Image, Description, RecordLabel,
                CatalogueNumber, ReleaseType, BitrateList, FormatList, MediaList, LogCue, Checksum, OCLC, GroupID)
            VALUES (
                now(), 1, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
            $user->id, $categoryId, $title, $year, $image, $description, $recordLabel,
            $catalogueNumber, $releaseType, $encodingList, $formatList, $mediaList, $logCue,
            (int)$checksum ? 1 : 0, $oclc, $groupId
        );
        $request = new \Gazelle\Request(self::$db->inserted_id());
        $request->vote($user, $bounty);
        $request->artistFlush();
        return $request;
    }

    public function findById(int $id): ?\Gazelle\Request {
        $key = sprintf(self::ID_KEY, $id);
        $requestId = self::$cache->get_value($key);
        if ($requestId === false) {
            $requestId = (int)self::$db->scalar("
                SELECT ID FROM requests WHERE ID = ?
                ", $id
            );
            if ($requestId) {
                self::$cache->cache_value($key, $requestId, 7200);
            }
        }
        return $requestId ? new \Gazelle\Request($requestId) : null;
    }

    /**
     * Find a list of unfilled requests by a user, sorted
     * by most number of votes and then largest bounty
     *
     * @return array of \Gazelle\Request objects
     */
    public function findUnfilledByUser(\Gazelle\User $user, int $limit): array {
        self::$db->prepared_query("
            SELECT DISTINCT r.ID
            FROM requests r
            INNER JOIN requests_votes v ON (v.RequestID = r.ID)
            WHERE r.TorrentID = 0
                AND r.UserID = ?
            GROUP BY r.ID
            ORDER BY count(v.UserID) DESC, sum(v.Bounty) DESC
            LIMIT 0, ?
            ", $user->id, $limit
        );
        return array_map(
            fn ($id) => $this->findById($id),
            self::$db->collect(0)
        );
    }

    public function findByArtist(\Gazelle\Artist $artist): array {
        $key = sprintf(\Gazelle\Artist::CACHE_REQUEST_ARTIST, $artist->id);
        $requestList = self::$cache->get_value($key);
        if ($requestList === false) {
            self::$db->prepared_query("
                SELECT DISTINCT r.ID
                FROM requests AS r
                INNER JOIN requests_votes v ON (v.RequestID = r.ID)
                INNER JOIN requests_artists AS ra ON (ra.RequestID = r.ID)
                INNER JOIN artists_alias aa ON (ra.AliasID = aa.AliasID)
                WHERE r.TorrentID = 0
                    AND aa.ArtistID = ?
                GROUP BY r.ID
                ORDER BY count(v.UserID) DESC, sum(v.Bounty) DESC
                ", $artist->id
            );
            $requestList = self::$db->collect(0);
            self::$cache->cache_value($key, $requestList, 3600);
        }
        return array_map(fn($id) => $this->findById($id), $requestList);
    }

    public function findByTGroup(\Gazelle\TGroup $tgroup): array {
        $key = sprintf(\Gazelle\TGroup::CACHE_REQUEST_TGROUP, $tgroup->id);
        $requestList = self::$cache->get_value($key);
        if ($requestList === false) {
            self::$db->prepared_query("
                SELECT r.ID
                FROM requests AS r
                INNER JOIN torrents_group tg ON (tg.ID = r.GroupID)
                WHERE r.TorrentID = 0
                    AND tg.ID = ?
                ORDER BY r.TimeAdded ASC
                ", $tgroup->id
            );
            $requestList = self::$db->collect(0);
            self::$cache->cache_value($key, $requestList, 3600);
        }
        return array_map(fn($id) => $this->findById($id), $requestList);
    }

    public function findByTorrentReported(\Gazelle\TorrentAbstract $torrent): array {
        self::$db->prepared_query("
            SELECT DISTINCT req.ID
            FROM requests AS req
            INNER JOIN reportsv2 AS rep ON (rep.TorrentID = req.TorrentID)
            WHERE rep.Status != 'Resolved'
                AND req.TorrentID = ?
            ",  $torrent->id
        );
        return array_map(fn($id) => $this->findById($id), self::$db->collect(0));
    }

    public function relay(): int {
        return $this->pg()->prepared_query("
            merge into request r using (
                with a_t as (
                    select rr.\"ID\" as id_request,
                        to_tsvector('simple', coalesce(string_agg(aa.\"Name\", ' '), '')
                            || ' ' || rr.\"Title\"
                        ) as artist_title_ts
                    from relay.requests rr
                    left join relay.requests_artists ra on    (ra.\"RequestID\" = rr.\"ID\")
                    left join relay.artists_alias    aa using (\"AliasID\")
                    left join relay.artist_role      ar on    (ar.artist_role_id = ra.artist_role_id)
                    where (ar.slug is null or ar.slug != 'guest')
                        and rr.updated >= (select coalesce(max(modified), '2000-01-01'::timestamptz) from request)
                    group by rr.\"ID\", rr.\"Title\"
                ),
                tl as (
                    select rt.id_request,
                        array_agg(rt.id_tag) as tag
                    from request_tag rt
                    inner join relay.requests rr on (rr.\"ID\" = rt.id_request)
                    where rr.updated >= (select coalesce(max(modified), '2000-01-01'::timestamptz) from request)
                    group by rt.id_request
                )
                select
                    rr.\"ID\" as id_request, rr.\"UserID\" as id_user, rr.\"FillerID\" as id_filler,
                    rr.\"TorrentID\" as id_torrent, rr.\"GroupID\" as id_tgroup,
                    rr.\"CategoryID\" as id_category, rr.\"ReleaseType\" as id_release_type,
                    rr.\"Year\" as year, rr.\"LastVote\" as last_vote, rr.\"TimeFilled\" as filled,
                    rr.created, rr.updated + '1 microsecond'::interval as updated,
                    rr.\"Description\" as description, rr.\"Title\" as title, rr.\"Image\" as image,
                    rr.\"CatalogueNumber\" as catalogue_number, rr.\"RecordLabel\" as record_label,
                    (case when regexp_replace(coalesce(rr.\"LogCue\", ''), '\D+', '', 'g') = ''
                        then '0'
                        else regexp_replace(coalesce(rr.\"LogCue\", ''), '\D+', '', 'g')
                        end)::int as log_score,
                    coalesce(position('Log' in rr.\"LogCue\") > 0, false) as need_log,
                    coalesce(position('Cue' in rr.\"LogCue\") > 0, false) as need_cue,
                    case when rr.\"Checksum\" = 0 then false else true end as need_checksum,
                    rr.\"BitrateList\" as encoding, rr.\"FormatList\" as format, rr.\"MediaList\" as media,
                    a_t.artist_title_ts,
                    coalesce(tl.tag, ARRAY[]::int[]) as tag
                from relay.requests rr
                inner join a_t on (a_t.id_request = rr.\"ID\")
                left join tl on (tl.id_request = rr.\"ID\")
            ) as i on r.id_request = i.id_request
                when not matched then
                    insert (
                        id_request, id_user, id_filler, id_torrent,
                        id_tgroup, id_category, id_release_type, year,
                        last_vote, filled, created, modified,
                        description, title, image, catalogue_number,
                        record_label, log_score,
                        need_log, need_cue, need_checksum,
                        encoding_str, format_str, media_str,
                        artist_title_ts, tag
                    ) values (
                        i.id_request, i.id_user, i.id_filler, i.id_torrent,
                        i.id_tgroup, i.id_category, i.id_release_type, i.year,
                        i.last_vote, i.filled, i.created, i.updated,
                        i.description, i.title, i.image, i.catalogue_number,
                        i.record_label, i.log_score,
                        i.need_log, i.need_cue, i.need_checksum,
                        string_to_array(i.encoding::text, '|'),
                        string_to_array(i.format::text, '|'),
                        string_to_array(i.media::text, '|'),
                        i.artist_title_ts, i.tag
                    )
                when matched then
                    update set
                        id_user = i.id_user, id_filler = i.id_filler, id_torrent = i.id_torrent,
                        id_tgroup = i.id_tgroup, id_category = i.id_category,
                        id_release_type = i.id_release_type, year = i.year,
                        last_vote = i.last_vote, filled = i.filled, created = i.created,
                        modified = i.updated, description = i.description, title = i.title,
                        image = i.image, catalogue_number = i.catalogue_number,
                        record_label = i.record_label, log_score = i.log_score, need_log = i.need_log,
                        need_cue = i.need_cue, need_checksum = i.need_checksum,
                        encoding_str = string_to_array(i.encoding::text, '|'),
                        format_str = string_to_array(i.format::text, '|'),
                        media_str = string_to_array(i.media::text, '|'),
                        artist_title_ts = i.artist_title_ts,
                        tag = i.tag
        ");
    }
}
