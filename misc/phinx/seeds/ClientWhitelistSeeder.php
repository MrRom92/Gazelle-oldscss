<?php

use Phinx\Seed\AbstractSeed;

class ClientWhitelistSeeder extends AbstractSeed {
    public function run(): void {
        foreach (
            [
                ["BiglyBT 3.5",                  "-BI3500-"],
                ["BiglyBT 3.6",                  "-BI3600-"],
                ["BiglyBT 3.7",                  "-BI3700-"],
                ["Deluge 1.2.x",                 "-DE12"],
                ["Deluge 1.3.x",                 "-DE13"],
                ["Deluge 2.x",                   "-DE2"],
                ["Flud 1.4.9",                   "-FL149"],
                ["Halite 0.4.x",                 "-HL04"],
                ["KTorrent 21.x",                "-KT21"],
                ["KTorrent 23.x",                "-KT23"],
                ["KTorrent 24.x",                "-KT24"],
                ["KTorrent 5.x",                 "-KT5"],
                ["Libtorrent (Rasterbar)",       "-LT"],
                ["libtorrent (rtorrent) 0.13.x", "-lt0D"],
                ["libtorrent (rtorrent) 0.14.x", "-lt0E"],
                ["libtorrent (rtorrent) 0.15.x", "-lt0F"],
                ["qBittorrent 2.x",              "-qB2"],
                ["qBittorrent 3.x",              "-qB3"],
                ["qBittorrent 4.0.x",            "-qB40"],
                ["qBittorrent 4.1.x",            "-qB41"],
                ["qBittorrent 4.2.x",            "-qB42"],
                ["qBittorrent 4.3.x",            "-qB43"],
                ["qBittorrent 4.4.x",            "-qB44"],
                ["qBittorrent 4.5.x",            "-qB45"],
                ["qBittorrent 4.6.x",            "-qB46"],
                ["qBittorrent 5.0.x",            "-qB50"],
                ["Transmission 2.x",             "-TR2"],
                ["Transmission 3.0.0",           "-TR3000-"],
                ["Transmission 4.0.1",           "-TR4010-"],
                ["Transmission 4.0.2",           "-TR4020-"],
                ["Transmission 4.0.3",           "-TR4030-"],
                ["Transmission 4.0.4",           "-TR4040-"],
                ["Transmission 4.0.5",           "-TR4050-"],
                ["Transmission 4.0.6",           "-TR4060-"],
            ] as $client
        ) {
            $this->table('xbt_client_whitelist')->insert([
                'vstring' => $client[0],
                'peer_id' => $client[1],
            ])->save();
        }
    }
}
