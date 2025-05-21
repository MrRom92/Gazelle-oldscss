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
                select
                    \"ID\" as id_request, \"UserID\" as id_user, \"FillerID\" as id_filler,
                    \"TorrentID\" as id_torrent, \"GroupID\" as id_tgroup,
                    \"CategoryID\" as id_category, \"ReleaseType\" as id_release_type,
                    \"Year\" as year, \"LastVote\" as last_vote, \"TimeFilled\" as filled,
                    created, updated + '1 microsecond'::interval as updated,
                    \"Description\" as description, \"Title\" as title, \"Image\" as image,
                    \"CatalogueNumber\" as catalogue_number, \"RecordLabel\" as record_label,
                    (case when regexp_replace(coalesce(\"LogCue\", ''), '\D+', '', 'g') = ''
                        then '0'
                        else regexp_replace(coalesce(\"LogCue\", ''), '\D+', '', 'g')
                        end)::int as log_score,
                    coalesce(position('Log' in \"LogCue\") > 0, false) as need_log,
                    coalesce(position('Cue' in \"LogCue\") > 0, false) as need_cue,
                    case when \"Checksum\" = 0 then false else true end as need_checksum,
                    \"BitrateList\" as encoding, \"FormatList\" as format, \"MediaList\" as media
                from relay.requests
                where updated >= (select coalesce(max(modified), '2000-01-01'::timestamptz) from request)
            ) as i on r.id_request = i.id_request
                when not matched then
                    insert (
                        id_request, id_user, id_filler, id_torrent,
                        id_tgroup, id_category, id_release_type, year,
                        last_vote, filled, created, modified,
                        description, title, image, catalogue_number,
                        record_label, log_score,
                        need_log, need_cue, need_checksum,
                        encoding, format, media
                    ) values (
                        i.id_request, i.id_user, i.id_filler, i.id_torrent,
                        i.id_tgroup, i.id_category, i.id_release_type, i.year,
                        i.last_vote, i.filled, i.created, i.updated,
                        i.description, i.title, i.image, i.catalogue_number,
                        i.record_label, i.log_score,
                        i.need_log, i.need_cue, i.need_checksum,
                        string_to_array(i.encoding::text, '|'),
                        string_to_array(i.format::text, '|'),
                        string_to_array(i.media::text, '|')
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
                        encoding = string_to_array(i.encoding::text, '|'),
                        format = string_to_array(i.format::text, '|'),
                        media = string_to_array(i.media::text, '|')
        ");
    }
}
