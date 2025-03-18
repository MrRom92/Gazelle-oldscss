<?php
/** @phpstan-var \Gazelle\User $Viewer */
/** @phpstan-var \Twig\Environment $Twig */

declare(strict_types=1);

namespace Gazelle;

use Gazelle\Enum\Direction;
use Gazelle\Enum\MysqlInfoOrderBy;
use Gazelle\Enum\MysqlTableMode;

if (!$Viewer->permitted('site_database_specifics')) {
    Error403::error();
}

// View table definition
$db = DB::DB();
if (!empty($_GET['table']) && preg_match('/([\w-]+)/', $_GET['table'], $match)) {
    $tableName = $match[1];
    $siteInfo = new SiteInfo();
    if (!$siteInfo->tableExists($tableName)) {
        Error404::error("No such table");
    }
    echo $Twig->render('admin/mysql-table.twig', [
        'definition' => $db->row('SHOW CREATE TABLE ' . $tableName)[1],
        'table_name' => $tableName,
        'table_read' => $siteInfo->tableRowsRead($tableName),
        'index_read' => $siteInfo->indexRowsRead($tableName),
        'stats'      => $siteInfo->tableStats($tableName),
    ]);
    exit;
}

$info = (new DB\MysqlInfo(
    DB\MysqlInfo::lookupTableMode($_GET['mode'] ?? MysqlTableMode::all->value),
    DB\MysqlInfo::lookupOrderby($_GET['order'] ?? MysqlInfoOrderBy::tableName->value),
    DB::lookupDirection($_GET['sort'] ?? Direction::ascending->value))
);
$list = $info->info();
$column = $info->orderBy() == MysqlInfoOrderBy::tableName
    ? MysqlInfoOrderBy::tableRows->value
    : $info->orderBy()->value;
$data = [];
foreach ($list as $t) {
    $data[$t['table_name']] = $t[$column];
}

echo $Twig->render('admin/mysql-table-summary.twig', [
    'header' => new \Gazelle\Util\SortableTableHeader(
        MysqlInfoOrderBy::tableName->value,
        DB\MysqlInfo::columnList(),
    ),
    'list'  => $list,
    'graph' => [
        'data'  => $data,
        'title' => $info->headerAlt(),
    ],
]);
