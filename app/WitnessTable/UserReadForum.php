<?php

namespace Gazelle\WitnessTable;

use Gazelle\Enum\SourceDB;

class UserReadForum extends AbstractWitnessTable {
    protected function sourceDb(): SourceDB {
        return SourceDB::mysql;
    }

    protected function reference(): string {
        return '';
    }

    protected function refIdColumn(): string {
        return 'ID';
    }

    protected function tableName(): string {
        return 'user_read_forum';
    }

    protected function idColumn(): string {
        return 'user_id';
    }

    protected function valueColumn(): string {
        return 'last_read';
    }

    public function witness(\Gazelle\User $user): bool {
        return $this->witnessDate($user);
    }
}
