<?php

namespace Gazelle\Enum;

enum UserTorrentSearch: string {
    case downloaded       = 'downloaded';
    case leeching         = 'leeching';
    case seeding          = 'seeding';
    case snatched         = 'snatched';
    case snatchedUnseeded = 'snatched-unseeded';
    case uploaded         = 'uploaded';
    case uploadedUnseeded = 'uploaded-unseeded';
}
