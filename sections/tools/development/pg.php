<?php
/** @phpstan-var \Gazelle\User $Viewer */
/** @phpstan-var \Twig\Environment $Twig */

declare(strict_types=1);

namespace Gazelle;

use Gazelle\Enum\PgInfoOrderBy;
use Gazelle\Enum\Direction;

if (!$Viewer->permitted('site_database_specifics')) {
    error(403);
}

$info = new DB\PgInfo(
    DB\PgInfo::lookupOrderby($_GET['order'] ?? PgInfoOrderBy::tableName->value),
    DB::lookupDirection($_GET['sort'] ?? Direction::ascending->value)
);

echo $Twig->render('admin/pg-table-summary.twig', [
    'header' => new \Gazelle\Util\SortableTableHeader(
        PgInfoOrderBy::tableName->value,
        DB\PgInfo::columnList()
    ),
    'list' => $info->info(),
]);
