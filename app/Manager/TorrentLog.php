<?php

declare(strict_types=1);

namespace Gazelle\Manager;

use Gazelle\Logfile         as Logfile;
use Gazelle\File\RipLog     as RipLog;
use Gazelle\File\RipLogHTML as RipLogHTML;
use Gazelle\Torrent         as Torrent;

class TorrentLog extends \Gazelle\Base {
    public function create(Torrent $torrent, Logfile $logfile, string $checkerVersion): \Gazelle\TorrentLog {
        self::$db->prepared_query('
            INSERT INTO torrents_logs
                   (TorrentID, Score, `Checksum`, FileName, Ripper, RipperVersion, `Language`, ChecksumState, Details, LogcheckerVersion)
            VALUES (?,         ?,      ?,         ?,        ?,      ?,             ?,          ?,             ?,       ?)
            ', $torrent->id, $logfile->score(), $logfile->checksumStatus(),
                $logfile->filename(), $logfile->ripper(), $logfile->ripperVersion(),
                $logfile->language(), $logfile->checksumState(),
                $logfile->detailsAsString(), $checkerVersion
        );
        $logId = self::$db->inserted_id();
        new RipLog($torrent->id, $logId)->put($logfile->filepath());
        new RipLogHTML($torrent->id, $logId)->put($logfile->text());
        return new \Gazelle\TorrentLog($torrent, $logId);
    }

    public function findById(Torrent $torrent, int $id): ?\Gazelle\TorrentLog {
        $logId = (int)self::$db->scalar("
            SELECT LogID FROM torrents_logs WHERE TorrentID = ? AND LogID = ?
            ", $torrent->id, $id
        );
        return $logId ? new \Gazelle\TorrentLog($torrent, $logId) : null;
    }
}
