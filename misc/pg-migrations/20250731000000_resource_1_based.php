<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class Resource1Based extends AbstractMigration {
    /**
     * @param array<string> $resourceList
     */
    public function value0List(array $resourceList): string {
        $list = '';
        foreach ($resourceList as $idx => $label) {
            if ($idx !== 0) {
                $list .= ', ';
            }
            $list .= "($idx, '$label')";
        }
        return $list;
    }

    /**
     * @param array<string> $resourceList
     */
    public function value1List(array $resourceList): string {
        $list = '';
        foreach ($resourceList as $idx => $label) {
            if ($idx !== 0) {
                $list .= ', ';
            }
            $list .= "(" . ((int)$idx + 1) . ", '$label')";
        }
        return $list;
    }

    public function up(): void {
        $this->query("delete from category_has_encoding");
        $this->query("delete from category_has_format");
        $this->query("delete from category_has_media");
        $this->query("delete from encoding");
        $this->query("delete from format");
        $this->query("delete from media");

        $this->query(
            "insert into encoding (id_encoding, label) values "
                . $this->value1List(ENCODING)
        );

        $this->query(
            "insert into format (id_format, label) values "
                . $this->value1List(FORMAT)
        );

        $this->query(
            "insert into media (id_media, label) values "
                . $this->value1List(MEDIA)
        );

        $this->query("
            insert into category_has_encoding
            select (select id_category from category where name = 'Music'), id_encoding
            from encoding
        ");

        $this->query("
            insert into category_has_format
            select (select id_category from category where name = 'Music'), id_format
            from format
        ");

        $this->query("
            insert into category_has_media
            select (select id_category from category where name = 'Music'), id_media
            from media
        ");
    }

    public function down(): void {
        $this->query("delete from category_has_encoding");
        $this->query("delete from category_has_format");
        $this->query("delete from category_has_media");
        $this->query("delete from encoding");
        $this->query("delete from format");
        $this->query("delete from media");

        $this->query(
            "insert into encoding (id_encoding, label) values "
                . $this->value0List(ENCODING)
        );

        $this->query(
            "insert into format (id_format, label) values "
                . $this->value0List(FORMAT)
        );

        $this->query(
            "insert into media (id_media, label) values "
                . $this->value0List(MEDIA)
        );

        $this->query("
            insert into category_has_encoding
            select (select id_category from category where name = 'Music'), id_encoding
            from encoding
        ");

        $this->query("
            insert into category_has_format
            select (select id_category from category where name = 'Music'), id_format
            from format
        ");

        $this->query("
            insert into category_has_media
            select (select id_category from category where name = 'Music'), id_media
            from media
        ");
    }
}
