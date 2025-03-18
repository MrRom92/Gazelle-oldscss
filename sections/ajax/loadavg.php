<?php

declare(strict_types=1);

namespace Gazelle;

authorize(true);

print json_encode([
    'status'   => 'success',
    'response' => ['loadAverage' => sys_getloadavg()]
]);
