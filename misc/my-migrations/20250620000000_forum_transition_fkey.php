<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class ForumTransitionFkey extends AbstractMigration {
    public function up(): void {
        $this->table('forums_transitions')
            ->dropForeignKey('source')
            ->dropForeignKey('destination')
            ->addForeignKey('source',      'forums', 'ID', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
            ->addForeignKey('destination', 'forums', 'ID', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
            ->save();
    }

    public function down(): void {
        $this->table('forums_transitions')
            ->dropForeignKey('source')
            ->dropForeignKey('destination')
            ->addForeignKey('source',      'forums', 'ID')
            ->addForeignKey('destination', 'forums', 'ID')
            ->save();
    }
}
