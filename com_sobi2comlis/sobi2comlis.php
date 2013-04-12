<?php

(defined('_VALID_MOS') || defined('_JEXEC') ) || (trigger_error('Restricted access', E_USER_ERROR) && exit());
defined("DS") || define("DS", DIRECTORY_SEPARATOR);

$mainframe->_head['title'] = 'Page title';
$mainframe->appendMetaTag('description', 'Page description');
$mainframe->appendMetaTag('keywords', 'Page, keywords');
$mainframe->addCustomHeadTag("<link rel=\"stylesheet\" href=\"$mosConfig_live_site/components/com_sobi2comlis/style.css\" type=\"text/css\" />");

/* Date conversion */
function date_convert ($data, $year, $time, $second){
    $res = "";
    $part = explode(" ", $data);
    $ymd = explode ("-", $part[0]);
    $hms = explode (":", $part[1]);

    if ($year == 1) {
        $res .= $ymd[2];
        $res .= ".".$ymd[1];
        $res .= ".".$ymd[0];
    }

    if ($time == 1) {
        $res .= " ".$hms[0];
        $res .= ":".$hms[1];
        if ($second == 1) $res .= ":".$hms[2];
    }

    return $res;
}

/* Pagination */
function pagerize($pgr, $total) {

    if (empty($pgr) or $pgr < 0) $pgr = 1;
    if ($pgr > $total) $pgr = $total;

    if ($pgr != 1) $pervpgr = '<a href="index.php?option=com_sobi2comlis&amp;page=1">[first page]</a>
                               <a href="index.php?option=com_sobi2comlis&amp;page='. ($pgr - 1) .'">&larr;</a>';

    if ($pgr - 2 > 0) $pgr2left = '<a href="index.php?option=com_sobi2comlis&amp;page='. ($pgr - 2) .'">'. ($pgr - 2) .'</a>|';
    if ($pgr - 1 > 0) $pgr1left = '<a href="index.php?option=com_sobi2comlis&amp;page='. ($pgr - 1) .'">'. ($pgr - 1) .'</a>|';
    if ($pgr + 1 <= $total) $pgr1right = '| <a href="index.php?option=com_sobi2comlis&amp;page='. ($pgr + 1) .'">'. ($pgr + 1) .'</a>';
    if ($pgr + 2 <= $total) $pgr2right = '| <a href="index.php?option=com_sobi2comlis&amp;page='. ($pgr + 2) .'">'. ($pgr + 2) .'</a>';

    if ($pgr != $total) $nextpgr = '<a href="index.php?option=com_sobi2comlis&amp;page='. ($pgr + 1) .'">&rarr;</a>
                                    <a href="index.php?option=com_sobi2comlis&amp;page=' .$total. '">[last page]</a>';

    return $pervpgr.$pgr2left.$pgr1left.$pgr.$pgr1right.$pgr2right.$nextpgr;
}

$pgr = intval(preg_replace("/[^0-9]+/", "", mosGetParam($_REQUEST,'page','0'))); //By default page 0
if ($pgr == '1') $pgr = '0'; //Page 1 is the same as page 0
$frm = intval(preg_replace("/[^0-9]+/", "", mosGetParam($_REQUEST,'from','20'))); //Reviews per page (20 reviews by default)

/* Working with DB */
$database->setQuery("SELECT COUNT(*) FROM #__sobi2_plugin_reviews");
$posts = $database->loadResult();
$total = intval(($posts - 1) / $frm) + 1;
$pgr = intval($pgr);
$database->setQuery("
SELECT
	#__sobi2_plugin_reviews.itemid, 
	#__sobi2_plugin_reviews.title, 
	#__sobi2_plugin_reviews.review, 
	#__sobi2_plugin_reviews.username, 
	#__sobi2_plugin_reviews.email, 
	#__sobi2_plugin_reviews.added, 
	#__sobi2_item.title AS item_name, 
	#__sobi2_fields_data.data_txt AS item_city, 
	t.name AS item_category 
FROM 
	#__sobi2_plugin_reviews  
LEFT JOIN 
	#__sobi2_item USING(itemid) 
LEFT JOIN 
	#__sobi2_fields_data USING(itemid) 
LEFT JOIN (
		SELECT 
			#__sobi2_cat_items_relations.itemid, 
			#__sobi2_categories.name 
		FROM #__sobi2_categories 
		LEFT JOIN #__sobi2_cat_items_relations USING(catid) 
) AS t USING(itemid) 
WHERE 
	#__sobi2_plugin_reviews.review!='' 
AND 
	#__sobi2_fields_data.fieldid = '3' 
ORDER BY 
	#__sobi2_plugin_reviews.added DESC 
LIMIT 
	$pgr, $frm
");
$list = $database->loadObjectList();

?>

<!-- Reviews-->
<h1>Reviews</h1>

<!-- Pagination -->
<div class="pagination"><?=pagerize($pgr, $total);?></div>
<!-- /Pagination -->

<!-- Review entries -->
<?php

foreach($list as $review) {
    if (!empty($review->item_city)) $review->item_city = '(' .$review->item_city. ')';
    if (!empty($review->item_category)) $review->item_category = ' - ' . $review->item_category;
    $review->added = date_convert($review->added, 1, 1, 0);
    if ($review->username == '') $review->username = 'Anonymous user';
    if ($review->title == '') $review->title = '<span class="bn">no topic</span>';

echo <<<HTML
    <div class="reviewentry">
        <strong>Review about <a href="index.php?option=com_sobi2&amp;sobi2Task=sobi2Details&amp;sobi2Id=$review->itemid">$review->item_name</a> $review->item_city $review->item_category</strong><br />
        <span class="reviewtitle">$review->title</span><br />
        <div class="reviewcontent">$review->review</div>
        <span class="bn">Added $review->added</span>
    </div>
HTML;
} # end foreach

?>
<!-- /Review entries -->

<!-- Pagination -->
<div class="pagination"><?=pagerize($pgr, $total);?></div>
<!-- /Pagination -->

<!-- /Reviews -->