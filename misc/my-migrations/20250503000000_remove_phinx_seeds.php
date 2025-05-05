<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

final class RemovePhinxSeeds extends AbstractMigration {
    public function up(): void {
        $this->execute("
            INSERT IGNORE INTO xbt_client_whitelist (peer_id, vstring) VALUES 
                ('-BI3500-', 'BiglyBT 3.5'),
                ('-BI3600-', 'BiglyBT 3.6'),
                ('-BI3700-', 'BiglyBT 3.7'),
                ('-DE12',    'Deluge 1.2.x'),
                ('-DE13',    'Deluge 1.3.x'),
                ('-DE2',     'Deluge 2.x'),
                ('-FL149',   'Flud 1.4.9'),
                ('-HL04',    'Halite 0.4.x'),
                ('-KT21',    'KTorrent 21.x'),
                ('-KT23',    'KTorrent 23.x'),
                ('-KT24',    'KTorrent 24.x'),
                ('-KT5',     'KTorrent 5.x'),
                ('-LT',      'Libtorrent (Rasterbar)'),
                ('-lt0D',    'libtorrent (rtorrent) 0.13.x'),
                ('-lt0E',    'libtorrent (rtorrent) 0.14.x'),
                ('-lt0F',    'libtorrent (rtorrent) 0.15.x'),
                ('-qB2',     'qBittorrent 2.x'),
                ('-qB3',     'qBittorrent 3.x'),
                ('-qB40',    'qBittorrent 4.0.x'),
                ('-qB41',    'qBittorrent 4.1.x'),
                ('-qB42',    'qBittorrent 4.2.x'),
                ('-qB43',    'qBittorrent 4.3.x'),
                ('-qB44',    'qBittorrent 4.4.x'),
                ('-qB45',    'qBittorrent 4.5.x'),
                ('-qB46',    'qBittorrent 4.6.x'),
                ('-qB50',    'qBittorrent 5.0.x'),
                ('-TR2',     'Transmission 2.x'),
                ('-TR3000-', 'Transmission 3.0.0'),
                ('-TR4010-', 'Transmission 4.0.1'),
                ('-TR4020-', 'Transmission 4.0.2'),
                ('-TR4030-', 'Transmission 4.0.3'),
                ('-TR4040-', 'Transmission 4.0.4'),
                ('-TR4050-', 'Transmission 4.0.5'),
                ('-TR4060-', 'Transmission 4.0.6')
        ");
    }

    public function down(): void {
    }
}
