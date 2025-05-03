<?php
/** @phpstan-var \Gazelle\User $Viewer */
/** @phpstan-var \Twig\Environment $Twig */

declare(strict_types=1);

namespace Gazelle;

if (!$Viewer->permitted('site_database_specifics')) {
    Error403::error();
}

// View table definition
if (preg_match('/([\w-]+)/', $_GET['table'] ?? '', $match)) {
    $table = new DB\MysqlTable($match[1]);
    if (!$table->exists()) {
        Error404::error("No such Mysql table {$match[1]}");
    }
    echo $Twig->render('admin/mysql-table.twig', [
        'table' => $table,
    ]);
    exit;
}

$info = (new DB\MysqlInfo(
    DB\MysqlInfo::lookupTableMode($_GET['mode'] ?? Enum\MysqlTableMode::all->value),
    DB\MysqlInfo::lookupOrderby($_GET['order'] ?? Enum\MysqlInfoOrderBy::tableName->value),
    DB::lookupDirection($_GET['sort'] ?? Enum\Direction::ascending->value))
);
$list = $info->info();
$column = $info->orderBy() == Enum\MysqlInfoOrderBy::tableName
    ? Enum\MysqlInfoOrderBy::tableRows->value
    : $info->orderBy()->value;
$data = [];
foreach ($list as $t) {
    $data[$t['table_name']] = $t[$column];
}

echo $Twig->render('admin/mysql-table-summary.twig', [
    'header' => new Util\SortableTableHeader(
        Enum\MysqlInfoOrderBy::tableName->value,
        DB\MysqlInfo::columnList(),
    ),
    'list'  => $list,
    'graph' => [
        'data'  => $data,
        'title' => $info->headerAlt(),
    ],
]);
