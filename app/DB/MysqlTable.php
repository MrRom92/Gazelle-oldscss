<?php

declare(strict_types=1);

namespace Gazelle\DB;

class MysqlTable extends AbstractTable {
    protected Mysql $dbro;

    public function __construct(
        public readonly string $name,
    ) {
        $this->dbro = \Gazelle\DB::DB(readWrite: false);
    }

    public function location(): string {
        return "tools.php?action=db-mysql&table={$this->name}";
    }

    public function exists(): bool {
        return (bool)self::$db->scalar("
            SELECT 1
            FROM information_schema.tables t
            WHERE t.table_schema = ?
                AND t.table_name = ?
            ", MYSQL_DB, $this->name
        );
    }

    public function definition(): string {
        return self::$db->row("SHOW CREATE TABLE {$this->name}")[1];
    }

    public function indexRead(): array {
        self::$db->prepared_query("
            SELECT s.INDEX_NAME           AS index_name,
                coalesce(si.ROWS_READ, 0) AS rows_read,
                group_concat(
                    concat(s.column_name, ' {', s.cardinality, '}')
                    ORDER BY s.seq_in_index
                    SEPARATOR ', '
                ) AS column_list
            FROM information_schema.statistics s
            LEFT JOIN information_schema.index_statistics si
                USING (TABLE_SCHEMA, TABLE_NAME, INDEX_NAME)
            WHERE s.TABLE_SCHEMA = ?
                AND s.TABLE_NAME = ?
            GROUP BY index_name,
                rows_read
            ORDER BY s.TABLE_NAME,
                s.INDEX_NAME = 'PRIMARY' DESC,
                coalesce(si.ROWS_READ, 0) DESC,
                s.INDEX_NAME
            ", MYSQL_DB, $this->name
        );
        return self::$db->to_array(false, MYSQLI_ASSOC);
    }

    public function tableRead(): array {
        return self::$db->rowAssoc("
            SELECT ROWS_READ, ROWS_CHANGED, ROWS_CHANGED_X_INDEXES
            FROM information_schema.table_statistics
            WHERE TABLE_SCHEMA = ?
                AND TABLE_NAME = ?
            ", MYSQL_DB, $this->name
        );
    }

    public function stats(): array {
        return self::$db->rowAssoc("
            SELECT t.TABLE_ROWS,
                t.AVG_ROW_LENGTH,
                t.DATA_LENGTH,
                t.INDEX_LENGTH,
                t.DATA_FREE,
                ts.ROWS_READ,
                ts.ROWS_CHANGED,
                ts.ROWS_CHANGED_X_INDEXES
            FROM information_schema.tables t
            INNER JOIN information_schema.table_statistics ts
                USING (TABLE_SCHEMA, TABLE_NAME)
            WHERE t.TABLE_SCHEMA = ?
                AND t.TABLE_NAME = ?
            ", MYSQL_DB, $this->name
        );
    }
}
