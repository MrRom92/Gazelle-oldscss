<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class DropSphinxRequest extends AbstractMigration {
    public function up(): void {
        $this->table('sphinx_requests')->drop()->save();
        $this->table('sphinx_requests_delta')->drop()->save();
    }

    public function down(): void {
        $this->query("
            CREATE TABLE sphinx_requests (
              ID int NOT NULL PRIMARY KEY,
              UserID int NOT NULL DEFAULT '0',
              TimeAdded int NOT NULL DEFAULT '0',
              LastVote int NOT NULL DEFAULT '0',
              CategoryID int NOT NULL,
              Title varchar(255),
              Year int,
              ArtistList mediumtext,
              ReleaseType tinyint,
              CatalogueNumber varchar(50) NOT NULL DEFAULT '',
              BitrateList varchar(255),
              FormatList varchar(255),
              MediaList varchar(255),
              LogCue varchar(20),
              FillerID int NOT NULL DEFAULT '0',
              TorrentID int NOT NULL DEFAULT '0',
              TimeFilled int,
              Visible binary(1) NOT NULL DEFAULT '1',
              Bounty bigint NOT NULL DEFAULT '0',
              Votes int NOT NULL DEFAULT '0',
              RecordLabel varchar(80)
            )
        ");
        $this->query("
            CREATE TABLE sphinx_requests_delta (
              ID int NOT NULL PRIMARY KEY,
              UserID int NOT NULL DEFAULT '0',
              TimeAdded int,
              LastVote int,
              CategoryID tinyint,
              Title varchar(255),
              TagList varchar(728) NOT NULL DEFAULT '',
              Year int,
              ArtistList mediumtext,
              ReleaseType tinyint,
              CatalogueNumber varchar(50),
              BitrateList varchar(255),
              FormatList varchar(255),
              MediaList varchar(255),
              LogCue varchar(20),
              FillerID int NOT NULL DEFAULT '0',
              TorrentID int NOT NULL DEFAULT '0',
              TimeFilled int,
              Visible binary(1) NOT NULL DEFAULT '1',
              Bounty bigint NOT NULL DEFAULT '0',
              Votes int NOT NULL DEFAULT '0',
              RecordLabel varchar(80),
              created timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
              KEY Userid (UserID),
              KEY TimeAdded (TimeAdded)
            )
        ");
    }
}
