<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class SiteOptionTokenSize extends AbstractMigration {
    public function up(): void {
        $this->execute("
            INSERT IGNORE INTO site_options (Name, `Value`, Comment)
            VALUES ('fl-token-size', '512', 'Freeleech token size in megabytes (MiB)')
        ");
    }

    public function down(): void {
        $this->execute("
            DELETE FROM site_options WHERE Name = 'fl-token-size'
        ");
    }
}
