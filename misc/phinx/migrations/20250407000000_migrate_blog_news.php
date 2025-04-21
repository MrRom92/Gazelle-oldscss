<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class MigrateBlogNews extends AbstractMigration {
    public function up(): void {
        $this->table('user_read_blog')->dropForeignKey('blog_id')->save();
        $this->table('user_read_news')->dropForeignKey('news_id')->save();
    }

    public function down(): void {
        $this->table('user_read_blog')
            ->addForeignKey('blog_id', 'blog', 'ID', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
            ->save();
        $this->table('user_read_news')
            ->addForeignKey('news_id', 'news', 'ID', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
            ->save();
    }
}
