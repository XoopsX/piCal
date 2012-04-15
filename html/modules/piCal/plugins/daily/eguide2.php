<?php

// a plugin for eguide 2.x by nobu

if( ! defined( 'XOOPS_ROOT_PATH' ) ) exit ;

/*
	$db : db instance
	$myts : MyTextSanitizer instance
	$this->year : year
	$this->month : month
	$this->date : date
	$this->week_start : sunday:0 monday:1
	$this->user_TZ : user's timezone (+1.5 etc)
	$this->server_TZ : server's timezone (-2.5 etc)
	$tzoffset_s2u : the offset from server to user
	$now : the result of time()
	$plugin = array('dirname'=>'dirname','name'=>'name','dotgif'=>'*.gif')
	
	$plugin_returns[ DATE ][]
*/

// set range (added 86400 second margin "begin" & "end")
$range_start_s = mktime(0,0,0,$this->month,$this->date-1,$this->year) ;
$range_end_s = mktime(0,0,0,$this->month,$this->date+2,$this->year) ;

// query
$cond = isset($_GET['eid'])?" AND e.eid=".intval($_GET['eid']):"";
$sql = "SELECT title,catname,e.eid,exid,IF(exdate,exdate,edate) edate,summary,"
	. "IF(x.reserved,x.reserved,o.reserved)/IF(expersons,expersons,persons)*100, closetime, style FROM ".
	$db->prefix("eguide")." e LEFT JOIN ".
	$db->prefix("eguide_category")." c ON e.topicid=c.catid LEFT JOIN ".
	$db->prefix("eguide_opt")." o ON e.eid=o.eid LEFT JOIN ".
	$db->prefix("eguide_extent")." x ON e.eid=eidref " 
	. "WHERE ((edate BETWEEN $range_start_s AND $range_end_s AND exdate IS NULL) "
	. "OR exdate BETWEEN $range_start_s AND $range_end_s) "
	. "AND IF(exdate,exdate,edate) BETWEEN $range_start_s AND $range_end_s "
	. "AND status=0 $cond ORDER BY edate";
$result = $db->query( $sql ) ;
if (!function_exists("eguide_marker")) {
	function eguide_marker($full, $dirname) {
	    static $marker;
	    if (empty($marker)) {
			$module_handler =& xoops_gethandler('module');
			$module =& $module_handler->getByDirname('eguide');
			$config_handler =& xoops_gethandler('config');
			$config =& $config_handler->getConfigsByCat(0, $module->getVar('mid'));
			$marker = preg_split('/,|[\r\n]+/',$config['maker_set']);
	    }
	    $tmp = $marker;
	    while(list($k,$v) = array_splice($tmp, 0, 2)) {
			if ($full<$k) return $v;
	    }
	    return '';
	}
}

while( list( $title , $catname, $id , $sub, $edate , $description , $full, $close, $style) = $db->fetchRow( $result ) ) {
	if (($edate-$close)<$now) $full = -1;
	$mark = eguide_marker($full, $plugin['dirname']);
	$server_time = $edate;
	$user_time = $server_time + $tzoffset_s2u ;
	if( date( 'j' , $user_time ) != $this->date ) continue ;
	$target_date = date('j',$user_time) ;
	$param ="eid=".$id;
	$br = 0;
	$html = 1;
	switch ($style) {
		case 2: $html = 0;
		case 1: $br = 1;
	}
	//echo $title;die;
	$tmp_array = array(
		'dotgif' => $plugin['dotgif'] ,
		'dirname' => $plugin['dirname'] ,
		'link' => XOOPS_URL."/modules/{$plugin['dirname']}/event.php?".$param ,
		'id' => $id ,
		'server_time' => $server_time ,
		'user_time' => $user_time ,
		'close_time' => $close ,
		'name' => 'eid' ,
		'title' => $myts->makeTboxData4Show( $catname ).$myts->makeTboxData4Show( $title ) ,
		'description' => $myts->displayTarea($description,$html,0,1,1,$br)
	) ;
	// multiple gifs allowed per a plugin & per a day
	$plugin_returns[ $target_date ][] = $tmp_array ;
	//var_dump($plugin_returns);die;
}
?>
