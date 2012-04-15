<?php
if (!defined('XOOPS_ROOT_PATH')) exit();

//require_once XOOPS_MODULE_PATH.'/profile/class/FieldType.class.php';

class piCal_multiMenuFlow extends XCube_ActionFilter{
	var $snoopData = array(
		'minutes'=>0
	);
	/**
	 * @public
	 */
	function postFilter(){
		$mydirname = basename( dirname( dirname( __FILE__ ) ) ) ;
		$module_handler = xoops_gethandler( 'module' );
		$module = $module_handler->getByDirname($mydirname);
		$myMid = $module->mid();
		if(isset($GLOBALS['xoopsModule']) && $GLOBALS['xoopsModule']->getVar('mid') != $module->mid()) return null;
		if ( isset($_POST['insert']) ){
			$module = $module_handler->getByDirname("formmakex");
			$mid = $module->mid();
			$_SESSION['multiMenuFlow'][$myMid] = $this->getSubmit($_SESSION['multiMenuFlow'][$mid]);
		}
	}
	function getSubmit($var) {
		//if (!isset($_GET['StartHour']) || !isset($_GET['StartMinutes'])) return null;
		// get bridge data
		$addmin = 0;
		foreach($var as $key => $myrow ) {
			if (isset($myrow['minutes'])) $addmin = intval($myrow['minutes']) * 60;
		}
		$start = intval($_POST['StartHour']) * 3600 + intval($_POST['StartMin']) * 60;
		$ret['bridgeData'] = array(
			'caldate' => $_POST['caldate'],
			'StartHour' => intval($_POST['StartHour']),
			'StartMin' => intval($_POST['StartMin']),
			'EndHour' => intval( (  $start + $addmin ) / 3600 ),
			'EndMin' => (  $start + $addmin ) % 3600 / 60
		);
		return $ret;
	}	
}
?>