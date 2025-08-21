<?php

declare(strict_types=1);

namespace Gazelle;

class BonusUploadReward extends Base {
    public function reward(Torrent $torrent): int {
        $categoryId = $torrent->group()->categoryId();
        if ($torrent->isPerfectFlac()) {
            $column = 'high';
        } elseif ($categoryId !== CATEGORY_MUSIC || $torrent->format() === 'FLAC') {
            $column = 'standard';
        } else {
            // Music MP3
            $column = 'low';
        }
        return (int)$this->pg()->scalar("
            select $column
            from bonus_upload_reward
            where id_category = ?
                and valid @> ?::timestamptz
            ", $categoryId, $torrent->created()
        );
    }

    public function modifyCategory(int $categoryId, array $reward): int {
        $this->pg()->pdo()->beginTransaction();
        $this->pg()->prepared_query("
            update bonus_upload_reward set
                valid = tstzrange(lower(valid), now())
            where now() <@ valid
                and id_category = ?
            ", $categoryId
        );
        $id = $this->pg()->insert("
            insert into bonus_upload_reward
                    (id_category, high, standard, low, valid)
             values (?,           ?,    ?,        ?,   tstzrange(now(), 'infinity'))
            ", $categoryId, $reward['high'], $reward['standard'], $reward['low']
        );
        $this->pg()->pdo()->commit();
        return $id;
    }
}
