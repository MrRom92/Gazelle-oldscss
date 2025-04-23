<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class RemoveRedundantIndexes extends AbstractMigration {
    public function up(): void {
        $this->table('api_tokens')
            ->removeIndex('user_id')
            ->save();
        $this->table('bookmarks_artists')
            ->removeIndex('ArtistID')
            ->save();
        $this->table('bookmarks_torrents')
            ->addIndex('GroupID')
            ->removeIndex(['UserID'])
            ->removeIndexByName('groups_users')
            ->save();
        $this->table('donations')
            ->removeIndex('Time')
            ->removeIndex('UserID')
            ->save();
        $this->table('pm_conversations_users')
            ->removeIndex('UserID')
            ->save();
        $this->table('site_options')
            ->removeIndex('Name')
            ->save();
        $this->table('site_options')
            ->addIndex(['Name'], ['unique' => true, 'name' => 'so_name_uidx'])
            ->removeIndex('Name')
            ->save();
        $this->table('users_history_ips')
            ->removeIndex('UserID')
            ->save();
    }

    public function down(): void {
        $this->table('api_tokens')
            ->addIndex(['user_id'])
            ->save();
        $this->table('bookmarks_artists')
            ->addIndex(['ArtistID'])
            ->save();
        $this->table('bookmarks_torrents')
            ->addIndex(['GroupID', 'UserID'], ['unique' => true, 'name' => 'groups_users'])
            ->addIndex(['UserID'])
            ->removeIndex(['GroupID'])
            ->save();
        $this->table('donations')
            ->addIndex(['Time'])
            ->addIndex(['UserID'])
            ->save();
        $this->table('pm_conversations_users')
            ->addIndex(['UserID'])
            ->save();
        $this->table('site_options')
            ->removeIndex('Name')
            ->addIndex(['Name'])
            ->addIndex(['Name'], ['unique' => 'true', 'name' => 'name_index'])
            ->save();
        $this->table('users_history_ips')
            ->addIndex(['UserID'])
            ->save();
    }
}
