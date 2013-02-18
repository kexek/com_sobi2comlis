<?php

(defined( '_VALID_MOS' ) || defined( '_JEXEC' ) ) || ( trigger_error( 'Restricted access', E_USER_ERROR ) && exit() );
defined( "DS" ) || define( "DS",DIRECTORY_SEPARATOR);

$mainframe->_head['title'] = 'Page title';
$mainframe->appendMetaTag( 'description', 'Page description' );
$mainframe->appendMetaTag( 'keywords', 'Page, keywords' );

/* Date convertion */
function date_convert ($data, $year, $time, $second){
    $res = "";
    $part = explode(" " , $data);
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
function pagerize() {
    if ($pgr != 1) $pervpgr = '<a href="index.php?option=com_sobi2comlis&amp;page=1" style="text-decoration: none;"><font color="#cccccc">[first page]</font></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                              <a href="index.php?option=com_sobi2comlis&amp;page='. ($pgr - 1) .'" style="text-decoration: none"><font style="font-size: 14pt">&#8592;</font></a> ';
    if ($pgr != $total) $nextpgr = ' <a href="index.php?option=com_sobi2comlis&amp;page='. ($pgr + 1) .'" style="text-decoration: none"><font style="font-size: 14pt">&#8594;</font></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                   <a href="index.php?option=com_sobi2comlis&amp;page=' .$total. '" style="text-decoration: none"><font color="#cccccc">[last page]</font></a>';
    if($pgr - 2 > 0) $pgr2left = ' <a href="index.php?option=com_sobi2comlis&amp;page='. ($pgr - 2) .'" style="text-decoration: none">'. ($pgr - 2) .'</a> <font color="#cccccc">|</font> ';
    if($pgr - 1 > 0) $pgr1left = '<a href="index.php?option=com_sobi2comlis&amp;page='. ($pgr - 1) .'" style="text-decoration: none">'. ($pgr - 1) .'</a> <font color="#cccccc">|</font> ';
    if($pgr + 2 <= $total) $pgr2right = ' <font color="#cccccc">|</font> <a href="index.php?option=com_sobi2comlis&amp;page='. ($pgr + 2) .'" style="text-decoration: none">'. ($pgr + 2) .'</a>';
    if($pgr + 1 <= $total) $pgr1right = ' <font color="#cccccc">|</font> <a href="index.php?option=com_sobi2comlis&amp;page='. ($pgr + 1) .'" style="text-decoration: none">'. ($pgr + 1) .'</a>';
    /* Вывод меню */
    echo $pervpgr.$pgr2left.$pgr1left.'<b>'.$pgr.'</b>'.$pgr1right.$pgr2right.$nextpgr;
}


/**/
$pgr = (int)preg_replace("/[^0-9]+/","",mosGetParam($_REQUEST,'page','0')); //By default page 0
if ($pgr == '1') {$pgr = '0';} //Page 1 is the same that page 0
$frm = (int)preg_replace("/[^0-9]+/","",mosGetParam($_REQUEST,'from','20')); //Reviews per page (20 reviews by default)
/**/

/* Working with DB */
$database->setQuery("SELECT COUNT(*) FROM #__sobi2_plugin_reviews");
$posts = $database->loadResult();
$total = intval(($posts - 1) / $frm) + 1;
$pgr = intval($pgr);
$database->setQuery("SELECT itemid, title, review, username, email, added FROM #__sobi2_plugin_reviews WHERE review!='' ORDER BY added DESC LIMIT $pgr , $frm");
$list = $database->loadObjectList();

/* Pagination */
if(empty($pgr) or $pgr < 0) $pgr = 1;
if($pgr > $total) $pgr = $total;

?>

<h1>Reviews</h1>
<p><strong><?php pagerize(); ?></strong></p>
<p>

<?php
foreach($list as $rvws) {
    $database->setQuery("SELECT title FROM #__sobi2_item WHERE itemid = '" .$rvws->itemid. "' LIMIT 1");
    $titled = $database->loadResult();
    $database->setQuery("SELECT data_txt FROM #__sobi2_fields_data WHERE itemid = '" .$rvws->itemid. "' AND fieldid = '3' LIMIT 1");
    $city = $database->loadResult();
    if (empty($city)) {$city = '';} else {$city = '(' .$city. ')';}
    $database->setQuery("SELECT name FROM #__sobi2_categories WHERE catid = (SELECT catid FROM #__sobi2_cat_items_relations WHERE itemid = '" .$rvws->itemid. "' LIMIT 1) LIMIT 1");
    $typed = $database->loadResult();
    if (empty($typed)) {$typed = '';} else {$typed = ' - ' .$typed;}
    $rvws->added = date_convert ($rvws->added, 1, 1, 0);
    if ($rvws->username == '') {$rvws->username = 'Anonymous user';}
    if ($rvws->title == '') $rvws->title = '<font color="#999999">без темы</font>';

echo <<<HTML
    <div style="border-bottom: 1px dashed #CCCCCC">
    <strong>Review about <a href="index.php?option=com_sobi2&amp;sobi2Task=sobi2Details&amp;sobi2Id=$rvws->itemid">$titled</a> $city $typed</strong><br /><b>
    <span color="#4CA55E">$rvws->title</span></b><br />
    $rvws->review<br/>
    <span color="#999999">
    Added $rvws->added</span></div><br /><br />
HTML;
} ?>

</p>
<p><strong><?php pagerize(); ?></strong></p>