<?php

declare(strict_types=1);

namespace Gazelle;

\Text::$TOC = true;

if (!empty($_POST['admincomment'])) {
    echo \Text::full_format($_POST['admincomment'], cache: IMAGE_CACHE_ENABLED);
} elseif (!empty($_POST['WikiText'])) {
    echo \Text::full_format($_REQUEST['WikiText'], cache: IMAGE_CACHE_ENABLED);
} else {
    echo \Text::full_format($_REQUEST['body'], cache: IMAGE_CACHE_ENABLED);
}
