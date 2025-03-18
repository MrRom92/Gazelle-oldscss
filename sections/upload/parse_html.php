<?php

declare(strict_types=1);

namespace Gazelle;

if (!$_POST['html'] || empty($_POST['html'])) {
    Error400::error();
}

// we can assume that everything sent to this endpoint is legacy gazelle html-escaped bbcode
// hence we run html_unescape() on the result
header('Content-type: text/plain');
echo html_unescape(Util\Text::parseHtml($_POST['html']));
