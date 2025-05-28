<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class DropMigratedTables extends AbstractMigration {
    public function up(): void {
        foreach (
            [
                'blog', 'donations_bitcoin', 'email_blacklist', 'error_log',
                'news', 'users_history_passkeys',
            ] as $table
        ) {
            $this->table($table)->drop()->save();
        }
    }

    public function down(): void {
        // There is no real reason to have to roll these tables back
        // so it is much less hassle to paste the current definitions
        // than convert to phinx methods.
        $this->execute("
            CREATE TABLE blog (
              ID int NOT NULL AUTO_INCREMENT,
              UserID int NOT NULL,
              Title varchar(255) NOT NULL,
              Body mediumtext NOT NULL,
              Time datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
              ThreadID int DEFAULT NULL,
              Important tinyint NOT NULL DEFAULT '0',
              PRIMARY KEY (ID),
              KEY UserID (UserID),
              KEY Time (Time)
            )
        ");
        $this->execute("
            CREATE TABLE donations_bitcoin (
              BitcoinAddress varchar(34) CHARACTER SET utf8mb4 NOT NULL,
              Amount decimal(24,8) NOT NULL,
              donations_bitcoin_id int NOT NULL AUTO_INCREMENT,
              PRIMARY KEY (donations_bitcoin_id),
              KEY BitcoinAddress (BitcoinAddress,Amount)
            )
        ");
        $this->execute("
            CREATE TABLE email_blacklist (
              ID int NOT NULL AUTO_INCREMENT,
              UserID int NOT NULL,
              Email varchar(255) NOT NULL,
              Time datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
              Comment mediumtext NOT NULL,
              PRIMARY KEY (ID)
            )
        ");
        $this->execute("
            CREATE TABLE error_log (
              error_log_id int NOT NULL AUTO_INCREMENT,
              duration float NOT NULL DEFAULT '0',
              memory bigint NOT NULL DEFAULT '0',
              nr_query int NOT NULL DEFAULT '0',
              nr_cache int NOT NULL DEFAULT '0',
              seen int NOT NULL DEFAULT '1',
              created timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
              updated timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              digest binary(16) NOT NULL,
              uri varchar(255) NOT NULL,
              trace mediumtext NOT NULL,
              request json NOT NULL,
              error_list json NOT NULL,
              logged_var json NOT NULL,
              user_id int NOT NULL DEFAULT '0',
              PRIMARY KEY (error_log_id),
              UNIQUE KEY digest_uidx (digest),
              KEY updated_idx (updated)
            )
        ");
        $this->execute("
            CREATE TABLE news (
              ID int NOT NULL AUTO_INCREMENT,
              UserID int NOT NULL,
              Title varchar(255) NOT NULL,
              Body longtext NOT NULL,
              Time datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
              PRIMARY KEY (ID),
              KEY Time (Time)
            )
        ");
        $this->execute("
            CREATE TABLE users_history_passkeys (
              UserID int NOT NULL,
              OldPassKey varchar(32) NOT NULL,
              NewPassKey varchar(32) NOT NULL,
              ChangeTime datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
              ChangerIP varchar(15) NOT NULL,
              PRIMARY KEY (UserID,OldPassKey)
            )
        ");
    }
}
