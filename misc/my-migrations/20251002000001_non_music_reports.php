<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class NonMusicReports extends AbstractMigration {
    public function up(): void {
        $this->table('torrent_report_configuration')->insert([
                [
                    'category_id'    => 4,
                    'type'           => 'ab-content',
                    'name'           => 'Non-Music Audiobook',
                    'sequence'       => 215,
                    'tracker_reason' => 0,
                    'need_sitelink'  => 'none',
                    'resolve_delete' => true,
                    'explanation'    => 'Please explain how this content is not related to music.',
                    'pm_body'        => '[rule]1.1.1[/rule]. Only music-related Audiobooks are allowed on the site. This audiobook does not qualify due to its contents.',
                 ],[
                    'category_id'    => 3,
                    'type'           => 'eb-content',
                    'name'           => 'Non-Music E-Book',
                    'sequence'       => 260,
                    'tracker_reason' => 0,
                    'need_sitelink'  => 'none',
                    'resolve_delete' => true,
                    'explanation'    => 'Please explain how this content is not related to music.',
                    'pm_body'        => '[rule]1.1.1[/rule]. Only music-related E-Books are allowed on the site. This E-Book does not qualify due to its contents.',
                 ]
             ])
            ->save();
    }

    public function down(): void {
        $this->query("
            DELETE FROM torrent_report_configuration WHERE type IN ('ab-content', 'eb-content')
        ");
    }
}
