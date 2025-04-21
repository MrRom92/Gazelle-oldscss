<?php

namespace Gazelle\WitnessTable;

use Gazelle\Enum\SourceDB;

class UserReadBlog extends AbstractWitnessTable {
    protected function sourceDb(): SourceDB {
        return SourceDB::postgres;
    }

    protected function reference(): string {
        return 'blog';
    }

    protected function refIdColumn(): string {
        return 'id_blog';
    }

    protected function tableName(): string {
        return 'user_read_blog';
    }

    protected function idColumn(): string {
        return 'user_id';
    }

    protected function valueColumn(): string {
        return 'blog_id';
    }

    public function witness(\Gazelle\User $user): bool {
        return $this->witnessValue($user);
    }
}
