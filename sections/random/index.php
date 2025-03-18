<?php

declare(strict_types=1);

namespace Gazelle;

$object = match ($_REQUEST['action'] ?? '') {
    'artist'  => (new Manager\Artist())->findRandom(),
    'collage' => (new Manager\Collage())->findRandom(),
    default   => (new Manager\TGroup())->findRandom(),
};
if (is_null($object)) {
    Error404::error(); /* only likely to happen on a brand new installation */
}

header("Location: " . $object->location());
