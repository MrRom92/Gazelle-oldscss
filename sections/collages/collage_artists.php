<?php
/** @phpstan-var \Gazelle\User $Viewer */
/** @phpstan-var \Twig\Environment $Twig */
// phpcs:disable Generic.WhiteSpace.ScopeIndent.IncorrectExact
// phpcs:disable Generic.WhiteSpace.ScopeIndent.Incorrect

declare(strict_types=1);

namespace Gazelle;

/** @var Collage $collage required from collage.php */
$collage->setViewer($Viewer);
$collageCovers   = (int)($Viewer->option('CollageCovers') ?? 25) * (1 - (int)$Viewer->option('HideCollage'));
$collagePages    = [];
$Artists         = $collage->artistList();
$NumGroups       = $collage->numArtists();
$NumGroupsByUser = 0;
$Render          = [];
$ArtistTable     = '';

foreach ($Artists as $id => $Artist) {
    $name = html_escape($Artist['name']);
    $image = $Artist['image']
        ? sprintf('<img loading="lazy" class="tooltip" src="%s" alt="%s" title="%s" width="118"  data-origin-src="%s" />',
            html_escape(image_cache_encode($Artist['image'], height: 150, width: 150)),
            $name, $name, html_escape($Artist['image']))
        : ('<span style="width: 107px; padding: 5px;">' . $name . '</span>');
    $ArtistTable .= "<tr><td><a href=\"artist.php?id=$id\">" . $name . "</a></td></tr>";
    $Render[] = "<li class=\"image_group_$id\"><a href=\"artist.php?id=$id\">$image</a></li>";
}

if ($collageCovers) {
    if ($NumGroups > $collageCovers) {
        $Render = array_merge($Render,
            array_fill(0, $collageCovers * (int)ceil($NumGroups / $collageCovers) - $NumGroups, '<li></li>')
        );
    }
    for ($i = 0; $i < $NumGroups / $collageCovers; $i++) {
        $collagePages[] = implode('', array_slice($Render, $i * $collageCovers, $collageCovers));
    }
}

echo $Twig->render('collage/header.twig', [
    'bookmarked' => new User\Bookmark($Viewer)->isCollageBookmarked($collage),
    'collage'    => $collage,
    'object'     => 'artist',
    'viewer'     => $Viewer,
]);

echo $Twig->render('collage/sidebar.twig', [
    'artists'      => 0, // only makes sense for torrent collages
    'collage'      => $collage,
    'comments'     => new Manager\Comment()->collageSummary($collage),
    'contributors' => array_slice($collage->contributors(), 0, 5, true),
    'entries'      => $collage->numArtists(),
    'object'       => 'artist',
    'object_name'  => 'artist',
    'viewer'       => $Viewer,
]);
?>
    </div>
    <div class="main_column">
<?php if ($collageCovers != 0) { ?>
        <div id="coverart" class="box">
            <div class="head" id="coverhead"><strong>Cover Art</strong></div>
            <ul class="collage_images" id="collage_page0">
<?php
    $Page1 = array_slice($Render, 0, $collageCovers);
    foreach ($Page1 as $Group) {
        echo $Group;
    }
?>
            </ul>
        </div>
<?php if ($NumGroups > $collageCovers) { ?>
        <div class="linkbox pager" style="clear: left;" id="pageslinksdiv">
            <span id="firstpage" class="invisible"><a href="#" class="pageslink" onclick="collageShow.page(0); return false;"><strong>&laquo; First</strong></a> | </span>
            <span id="prevpage" class="invisible"><a href="#" class="pageslink" onclick="collageShow.prevPage(); return false;"><strong>&lsaquo; Prev</strong></a> | </span>
<?php
        for ($i = 0; $i < $NumGroups / $collageCovers; $i++) { ?>
            <span id="pagelink<?=$i?>" class="<?=($i > 4 ? 'hidden' : '')?><?=($i == 0 ? 'selected' : '')?>"><a href="#" class="pageslink" onclick="collageShow.page(<?=$i?>, this); return false;"><strong><?=$collageCovers * $i + 1?>-<?=min($NumGroups, $collageCovers * ($i + 1))?></strong></a><?=(($i != ceil($NumGroups / $collageCovers) - 1) ? ' | ' : '')?></span>
<?php   } ?>
            <span id="nextbar" class="<?=($NumGroups / $collageCovers > 5) ? 'hidden' : ''?>"> | </span>
            <span id="nextpage"><a href="#" class="pageslink" onclick="collageShow.nextPage(); return false;"><strong>Next</strong></a> ›</span>
            <span id="lastpage" class="<?=(ceil($NumGroups / $collageCovers) == 2 ? 'invisible' : '')?>"> | <a href="#" class="pageslink" onclick="collageShow.page(<?=ceil($NumGroups / $collageCovers) - 1?>); return false;"><strong>Last &raquo;</strong></a></span>
        </div>
        <script type="text/javascript">//<![CDATA[
            collageShow.init(<?=json_encode($collagePages)?>);
        //]]></script>
<?php
    }
}
?>
        <table class="artist_table grouping cats" id="discog_table">
            <tr class="colhead_dark">
                <td><strong>Artists</strong></td>
            </tr>
<?= $ArtistTable ?>
        </table>
    </div>
</div>
