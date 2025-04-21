<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class MigrateBlogNews extends AbstractMigration {
    public function up(): void {
        $this->table('blog', ['id' => false, 'primary_key' => 'id_blog'])
             ->addColumn('id_blog', 'integer', ['identity' => true])
             ->addColumn('id_user', 'integer')
             ->addColumn('id_thread', 'integer', ['null' => true])
             ->addColumn('title', 'text')
             ->addColumn('body', 'text')
             ->addColumn('created', 'timestamp', ['timezone' => true, 'default' => 'CURRENT_TIMESTAMP'])
             ->addColumn('notify', 'boolean')
             ->save();
        $this->table('news', ['id' => false, 'primary_key' => 'id_news'])
             ->addColumn('id_news', 'integer', ['identity' => true])
             ->addColumn('id_user', 'integer')
             ->addColumn('id_thread', 'integer', ['null' => true])
             ->addColumn('title', 'text')
             ->addColumn('body', 'text')
             ->addColumn('created', 'timestamp', ['timezone' => true, 'default' => 'CURRENT_TIMESTAMP'])
             ->save();
        $this->execute('
            insert into blog
                (id_blog, id_user, title, body, created, id_thread, notify)
            select "ID", "UserID", "Title", "Body", "Time", "ThreadID",
                case when "Important" = 1 then true else false end
            from relay.blog
        ');
        $this->execute('
            insert into news
                (id_news, id_user, title, body, created)
            select "ID", "UserID", "Title", "Body", "Time"
            from relay.news
        ');
    }

    public function down(): void {
        $this->table('blog')->drop()->save();
        $this->table('news')->drop()->save();
    }
}
