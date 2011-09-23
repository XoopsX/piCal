<?php

// The RFC2445 class   === piCal ===
// piCal.php
// by GIJ=CHECKMATE (PEAK Corp. http://www.peak.ne.jp/)


if( ! class_exists( 'piCal' ) ) {

define( 'PICAL_COPYRIGHT' , "<a href='http://xoops.peak.ne.jp/' target='_blank'>piCal-0.8</a>" ) ;
define( 'PICAL_EVENT_TABLE' , 'pical_event' ) ;
define( 'PICAL_CAT_TABLE' , 'pical_cat' ) ;


class piCal
{
	// SKELTON (they will be defined in language files)
	var $holidays = array() ;
	var $date_short_names = array() ;
	var $date_long_names = array() ;
	var $week_numbers = array() ;
	var $week_short_names = array() ;
	var $week_middle_names = array() ;
	var $week_long_names = array() ;
	var $month_short_names = array() ;
	var $month_middle_names = array() ;
	var $month_long_names = array() ;
	var $byday2langday_w = array() ;
	var $byday2langday_m = array() ;

	// LOCALES
	var $locale = '' ;			// locale for piCal original
	var $locale4system = '' ;	// locale for UNIX systems (deprecated)

	// COLORS/STYLES  public
	var $holiday_color = '#CC0000' ;
	var $holiday_bgcolor = '#FFEEEE' ;
	var $sunday_color = '#CC0000' ;
	var $sunday_bgcolor = '#FFEEEE' ;
	var $saturday_color = '#0000FF' ;
	var $saturday_bgcolor = '#EEF7FF' ;
	var $weekday_color = '#000099' ;
	var $weekday_bgcolor = '#FFFFFF' ;
	var $targetday_bgcolor = '#CCFF99' ;
	var $calhead_color = '#009900' ;
	var $calhead_bgcolor = '#CCFFCC' ;
	var $frame_css = '' ;

	// TIMEZONES
	var $server_TZ = 9 ;			// Server's  Timezone Offset (hour)
	var $user_TZ = 9 ;				// User's Timezone Offset (hour)
	var $use_server_TZ = false ;	// if 'caldate' is generated in Server's time

	// AUTHORITIES
	var $insertable = true ;		// can insert a new event
	var $editable = true ;			// can update an event he posted
	var $deletable = true ;			// can delete an event he posted
	var $user_id = -1 ;				// User's ID
	var $isadmin = false ;			// Is admin or not

	// ANOTHER public properties
	var $conn ;					// MySQL�Ƃ̐ڑ��n���h�� (�\��擾�����鎞�Z�b�g)
	var $table = 'pical_event' ;		// table name for events
	var $cat_table = 'pical_cat' ;		// table name for categories
	var $plugin_table = 'pical_plugin' ;	// table name for plugins
	var $base_url = '' ;
	var $base_path = '' ;
	var $images_url = '/include/piCal/images' ;	// ���̃t�H���_�� spacer.gif, arrow*.gif ����u���Ă���
	var $images_path = 'include/piCal/images' ;
	var $jscalendar = 'jscalendar' ; // DHTML Date/Time Selector
	var $jscalendar_lang_file = 'calendar-jp.js' ; // language file of the jscalh
	var $can_output_ics = true ;	// ics�t�@�C���o�͂������邩�ǂ���
	var $connection = 'http' ;		// http �� https ��
	var $max_rrule_extract = 100 ;	// rrule �̓W�J�̏����(COUNT)
	var $week_start = 0 ;			// �T�̊J�n�j�� 0�����j 1�����j
	var $week_numbering = 0 ;		// �T�̐����� 0�Ȃ猎���� 1�Ȃ�N�ԒʎZ
	var $day_start = 0 ;			// ���t�̋��E���i�b�P�ʁj
	var $use24 = true ;				// 24���Ԑ��Ȃ�true�A12���Ԑ��Ȃ�false
	var $now_cid = 0 ;				// �J�e�S���w��
	var $categories = array() ;		// �A�N�Z�X�\�ȃJ�e�S���I�u�W�F�N�g�A�z�z��
	var $groups = array() ;			// PRIVATE���ɑI���\�ȃO���[�v�̘A�z�z��
	var $nameoruname = 'name' ;		// ���e�҂̕\���i���O�C�������n���h�������j
	var $proxysettings = '' ;		// Proxy setting
	var $last_summary = '' ;		// �O�����猏�����Q�Ƃ��邽�߂̃v���p�e�B
	var $plugins_path_monthly = 'plugins/monthly' ;
	var $plugins_path_weekly = 'plugins/weekly' ;
	var $plugins_path_daily = 'plugins/daily' ;

	// private members
	var $year ;
	var $month ;
	var $date ;
	var $day ;			// 0:Sunday ... 6:Saturday
	var $daytype ;		// 0:weekdays 1:saturday 2:sunday 3:holiday
	var $caldate ;		// everytime 'Y-n-j' formatted
	var $unixtime ;
	var $long_event_legends = array() ;
	var $language = "japanese" ;

	// �����t���Q�Ɨp�����o
	var $original_id ;	// $_GET['event_id']��������������ɎQ�Ɖ\

	var $event = null ;	// event�̏o�̓f�[�^�i�[�p //naao

/*******************************************************************/
/*        CONSTRUCTOR etc.                                         */
/*******************************************************************/

// Constructor
public function __construct( $target_date = "" , $language = "japanese" , $reload = false )
{
	// ���t�̃Z�b�g
	if( $target_date ) {
		$this->set_date( $target_date ) ;
	} else if( isset( $_GET[ 'caldate' ] ) ) {
		$this->set_date( $_GET[ 'caldate' ] ) ;
	} else if( isset( $_POST[ 'pical_jumpcaldate' ] ) && isset( $_POST[ 'pical_year' ] ) ) {
		if( empty( $_POST[ 'pical_month' ] ) ) {
			// �N�݂̂�POST���ꂽ�ꍇ
			$month = 1 ;
			$date = 1 ;
		} else if( empty( $_POST[ 'pical_date' ] ) ) {
			// �N�E����POST���ꂽ�ꍇ
			$month = intval( $_POST[ 'pical_month' ] ) ;
			$date = 1 ;
		} else {
			// �N�E���E����POST���ꂽ�ꍇ
			$month = intval( $_POST[ 'pical_month' ] ) ;
			$date = intval( $_POST[ 'pical_date' ] ) ;
		}
		$year = intval( $_POST[ 'pical_year' ] ) ;
		$this->set_date( "$year-$month-$date" ) ;
		$caldate_posted = true ;
	} else {
		$this->set_date( date( 'Y-n-j' ) ) ;
		$this->use_server_TZ = true ;
	}

	// SSL�̗L�����A$_SERVER['HTTPS'] �ɂĔ��f
	if( defined( 'XOOPS_URL' ) ) {
		$this->connection = substr( XOOPS_URL , 0 , 8 ) == 'https://' ? 'https' : 'http' ;
	} else if( ! empty( $_SERVER['HTTPS'] ) ) {
		$this->connection = 'https' ;
	} else {
		$this->connection = 'http' ;
	}

	// �J�e�S���[�w��̎擾
	$this->now_cid = ! empty( $_GET['cid'] ) ? intval( $_GET['cid'] ) : 0 ;

	// POST�Ńo���o���ɓ��t�𑗐M���ꂽ�ꍇ�A�w�肪����΃����[�h���s��
	if( ! empty( $caldate_posted ) && $reload && ! headers_sent() ) {
		$reload_str = "Location: $this->connection://{$_SERVER['HTTP_HOST']}{$_SERVER['PHP_SELF']}?caldate=$this->caldate&{$_SERVER['QUERY_STRING']}" ;
		$needed_post_vars = array( 'op' , 'order' , 'cid' , 'num' , 'txt' ) ;
		foreach( $needed_post_vars as $post ) {
			if( isset( $_POST[ $post ] ) ) $reload_str .= "&$post=".urlencode( $_POST[ $post ] ) ;
		}
		$reload_str4header = strtr( $reload_str , "\r\n\0" , "   " ) ;
		header( $reload_str4header ) ;
		exit ;
	}

	// piCal.php �t�@�C���̑��݂���f�B���N�g���̈����x�[�X�Ƃ���
	$this->base_path = dirname( dirname( __FILE__ ) ) ;

	// ����t�@�C���̓ǂݍ���
	if ( file_exists( "$this->base_path/language/$language/pical_vars.phtml" ) ) {
		include "$this->base_path/language/$language/pical_vars.phtml" ;
		include_once "$this->base_path/language/$language/pical_constants.php" ;
		$this->language = $language ;
		$this->jscalendar_lang_file = _PICAL_JS_CALENDAR ;
	} else if( file_exists( "$this->base_path/language/english/pical_vars.phtml") ) {
		include "$this->base_path/language/english/pical_vars.phtml" ;
		include_once "$this->base_path/language/english/pical_constants.php" ;
		$this->language = "english" ;
		$this->jscalendar_lang_file = 'calendar-en.js' ;
	}

	// ���P�[���t�@�C���̓Ǎ�
	if( ! empty( $this->locale ) ) $this->read_locale() ;
}


// piCal��p���P�[���t�@�C����ǂݍ���
function read_locale()
{
	if( file_exists( "$this->base_path/locales/{$this->locale}.php" ) ) {
		include "$this->base_path/locales/{$this->locale}.php" ;
	}
}


// year,month,day,caldate,unixtime ���Z�b�g����
function set_date( $setdate )
{
	if( ! ( ereg( "^([0-9][0-9]+)[-./]?([0-1]?[0-9])[-./]?([0-3]?[0-9])$" , $setdate , $regs ) && checkdate( $regs[2] , $regs[3] , $regs[1] ) ) ) {
		ereg( "^([0-9]{4})-([0-9]{2})-([0-9]{2})$" , date( 'Y-m-d' ) , $regs ) ;
		$this->use_server_TZ = true ;
	}
	$this->year = $year = intval( $regs[1] ) ;
	$this->month = $month = intval( $regs[2] ) ;
	$this->date = $date = intval( $regs[3] ) ;
	$this->caldate = "$year-$month-$date" ;
	$this->unixtime = mktime(0,0,0,$month,$date,$year) ;

	// �j���Ɠ��t�^�C�v�̃Z�b�g
	// �c�F���[�̌���
	if( $month <= 2 ) {
		$year -- ;
		$month += 12 ;
	}
	$day = ( $year + floor( $year / 4 ) - floor( $year / 100 ) + floor( $year / 400 ) + floor( 2.6 * $month + 1.6 ) + $date ) % 7 ;

	$this->day = $day ;
	if( $day == 0 ) $this->daytype = 2 ;
	else if( $day == 6 ) $this->daytype = 1 ;
	else $this->daytype = 0 ;

	if( isset( $this->holidays[ $this->caldate ] ) ) $this->daytype = 3 ;
}



// �j���E�j���̎�ނ���w�i�F�E�����F�𓾂�
function daytype_to_colors( $daytype )
{
	switch( $daytype ) {
		case 3 :
			//	Holiday
			return array( $this->holiday_bgcolor , $this->holiday_color ) ;
		case 2 :
			//	Sunday
			return array( $this->sunday_bgcolor , $this->sunday_color ) ;
		case 1 :
			//	Saturday
			return array( $this->saturday_bgcolor , $this->saturday_color ) ;
		case 0 :
		default :
			// Weekday
			return array( $this->weekday_bgcolor , $this->weekday_color ) ;
	}
}



// SQL�`���̓��t����A�j���E�j���̎�ނ����߂�N���X�֐�
function get_daytype( $date )
{
	ereg( "^([0-9][0-9]+)[-./]?([0-1]?[0-9])[-./]?([0-3]?[0-9])$" , $date , $regs ) ;
	$year = intval( $regs[1] ) ;
	$month = intval( $regs[2] ) ;
	$date = intval( $regs[3] ) ;

	// �j����3
	if( isset( $this->holidays[ "$year-$month-$date" ] ) ) return 3 ;

	// �c�F���[�̌���
	if ($month <= 2) {
		$year -- ;
		$month += 12;
	}
	$day = ( $year + floor( $year / 4 ) - floor( $year / 100 ) + floor( $year / 400 )+ floor( 2.6 * $month + 1.6 ) + $date ) % 7 ;

	if( $day == 0 ) return 2 ;
	else if( $day == 6 ) return 1 ;
	else return 0 ;
}



/*******************************************************************/
/*        �u���b�N�p�\���֐�                                       */
/*******************************************************************/

// $this->caldate���̗\�� ��Ԃ�
function get_date_schedule( $get_target = '' )
{
	// if( $get_target == '' ) $get_target = $_SERVER['SCRIPT_NAME'] ;

	$ret = '' ;

	// �������v�Z���AWHERE�߂̊��ԂɊւ����������
	$tzoffset = ( $this->user_TZ - $this->server_TZ ) * 3600 ;
	if( $tzoffset == 0 ) {
		// �������Ȃ��ꍇ �iMySQL�ɕ��ׂ����������Ȃ����߁A�����ŏ����������Ƃ�)
		$whr_term = "start<'".($this->unixtime + 86400)."' AND end>'$this->unixtime'" ;
	} else {
		// ����������ꍇ�́Aallday�ɂ���ďꍇ����
		$whr_term = "( allday AND start<='$this->unixtime' AND end>'$this->unixtime') OR ( ! allday AND start<'".($this->unixtime + 86400 - $tzoffset )."' AND end>'".($this->unixtime - $tzoffset )."')" ;
	}

	// �J�e�S���[�֘A��WHERE�����擾
	$whr_categories = $this->get_where_about_categories() ;

	// CLASS�֘A��WHERE�����擾
	$whr_class = $this->get_where_about_class() ;

	// �����̃X�P�W���[���擾
	$yrs = mysql_query( "SELECT start,end,summary,id,allday FROM $this->table WHERE admission>0 AND ($whr_term) AND ($whr_categories) AND ($whr_class) ORDER BY start,end" , $this->conn ) ;
	$num_rows = mysql_num_rows( $yrs ) ;

	if( $num_rows == 0 ) $ret .= _PICAL_MB_NOEVENT."\n" ;
	else while( $event = mysql_fetch_object( $yrs ) ) {

		$summary = $this->text_sanitizer_for_show( $event->summary ) ;

		if( $event->allday ) {
			// �S���C�x���g
			$ret .= "
	       <table border='0' cellpadding='0' cellspacing='0' width='100%'>
	         <tr>
	           <td><img border='0' src='$this->images_url/dot_allday.gif' /> &nbsp; </td>
	           <td><font size='2'><a href='$get_target?cid=$this->now_cid&amp;smode=Daily&amp;action=View&amp;event_id=$event->id&amp;caldate=$this->caldate' class='calsummary_allday'>$summary</a></font></td>
	         </tr>
	       </table>\n" ;
		} else {
			// �ʏ�C�x���g
			$event->start += $tzoffset ;
			$event->end += $tzoffset ;
			$ret .= "
	       <dl>
	         <dt>
	           <font size='2'>".$this->get_todays_time_description( $event->start , $event->end , $this->caldate , false , true )."</font>
	         </dt>
	         <dd>
	           <font size='2'><a href='$get_target?cid=$this->now_cid&amp;smode=Daily&amp;action=View&amp;event_id=$event->id&amp;caldate=$this->caldate' class='calsummary'>$summary</a></font>
	         </dd>
	       </dl>\n" ;
		}
	}

	// �\��̒ǉ��i���M�A�C�R���j
	if( $this->insertable ) $ret .= "
	       <dl>
	         <dt>
	           &nbsp; <font size='2'><a href='$get_target?smode=Daily&amp;action=Edit&amp;caldate=$this->caldate'><img src='$this->images_url/addevent.gif' border='0' width='14' height='12' />"._PICAL_MB_ADDEVENT."</a></font>
	         </dt>
	       </dl>\n" ;

	return $ret ;
}



// $this->caldate�ȍ~�̗\�� ���ő� $num ���Ԃ�
function get_coming_schedule( $get_target = '' , $num = 5 )
{
	// if( $get_target == '' ) $get_target = $_SERVER['SCRIPT_NAME'] ;

	$ret = '' ;

	// �������v�Z���AWHERE�߂̊��ԂɊւ����������
	$tzoffset = ( $this->user_TZ - $this->server_TZ ) * 3600 ;
	if( $tzoffset == 0 ) {
		// �������Ȃ��ꍇ �iMySQL�ɕ��ׂ����������Ȃ����߁A�����ŏ����������Ƃ�)
		$whr_term = "end>'$this->unixtime'" ;
	} else {
		// ����������ꍇ�́Aallday�ɂ���ďꍇ����
		$whr_term = "(allday AND end>'$this->unixtime') OR ( ! allday AND end>'".($this->unixtime - $tzoffset )."')" ;
	}

	// �J�e�S���[�֘A��WHERE�����擾
	$whr_categories = $this->get_where_about_categories() ;

	// CLASS�֘A��WHERE�����擾
	$whr_class = $this->get_where_about_class() ;

	// �����ȍ~�̃X�P�W���[���擾
	$yrs = mysql_query( "SELECT start,end,summary,id,allday FROM $this->table WHERE admission>0 AND ($whr_term) AND ($whr_categories) AND ($whr_class) ORDER BY start" , $this->conn ) ;
	$num_rows = mysql_num_rows( $yrs ) ;

	if( $num_rows == 0 ) $ret .= _PICAL_MB_NOEVENT."\n" ;
	else for( $i = 0 ; $i < $num ; $i ++ ) {
		$event = mysql_fetch_object( $yrs ) ;
		if( $event == false ) break ;
		$summary = $this->text_sanitizer_for_show( $event->summary ) ;

		if( $event->allday ) {
			// �S���C�x���g
			$ret .= "
	       <dl>
	         <dt>
	           <font size='2'><img border='0' src='$this->images_url/dot_allday.gif' /> ".$this->get_middle_md( $event->start )."</font>
	         </dt>
	         <dd>
	           <font size='2'><a href='$get_target?cid=$this->now_cid&amp;smode=Daily&amp;action=View&amp;event_id=$event->id&amp;caldate=$this->caldate' class='calsummary_allday'>$summary</a></font>
	         </dd>
	       </dl>\n" ;
		} else {
			// �ʏ�C�x���g
			$event->start += $tzoffset ;
			$event->end += $tzoffset ;
			$ret .= "
	       <dl>
	         <dt>
	           <font size='2'>".$this->get_coming_time_description( $event->start , $this->unixtime )."</font>
	         </dt>
	         <dd>
	           <font size='2'><a href='$get_target?cid=$this->now_cid&amp;smode=Daily&amp;action=View&amp;event_id=$event->id&amp;caldate=$this->caldate' class='calsummary'>$summary</a></font>
	         </dd>
	       </dl>\n" ;
		}
	}

	// �c�茏���̕\��
	if( $num_rows > $num ) $ret .= "
           <table border='0' cellspacing='0' cellpadding='0' width='100%'>
            <tr>
             <td align='right'><small>"._PICAL_MB_RESTEVENT_PRE.($num_rows-$num)._PICAL_MB_RESTEVENT_SUF."</small></td>
            </tr>
           </table>\n" ;

	// �\��̒ǉ��i���M�A�C�R���j
	if( $this->insertable ) $ret .= "
	       <dl>
	         <dt>
	           &nbsp; <font size='2'><a href='$get_target?smode=Daily&amp;action=Edit&amp;caldate=$this->caldate'><img src='$this->images_url/addevent.gif' border='0' width='14' height='12' />"._PICAL_MB_ADDEVENT."</a></font>
	         </dt>
	       </dl>\n" ;

	return $ret ;
}



// �~�j�J�����_�[�p�C�x���g�擾�֐�
function get_flags_date_has_events( $range_start_s , $range_end_s )
{
	// ���炩���ߔz��𐶐����Ă���
	/* for( $time = $start ; $time < $end ; $time += 86400 ) {
		$ret[ date( 'j' , $time ) ] = 0 ;
	} */
	for( $i = 0 ; $i <= 31 ; $i ++ ) {
		$ret[ $i ] = 0 ;
	}

	// add margin -86400 and +86400 
	$range_start_s -= 86400 ;
	$range_end_s += 86400 ;

	// �����v�Z
	$tzoffset_s2u = intval( ( $this->user_TZ - $this->server_TZ ) * 3600 ) ;
	//$gmtoffset = intval( $this->server_TZ * 3600 ) ;

	// �J�e�S���[�֘A��WHERE�����擾
	$whr_categories = $this->get_where_about_categories() ;

	// CLASS�֘A��WHERE�����擾
	$whr_class = $this->get_where_about_class() ;

/*	$yrs = mysql_query( "SELECT start,end,allday FROM $this->table WHERE admission > 0 AND start < ".($end + 86400)." AND end > ".($start - 86400)." AND ($whr_categories) AND ($whr_class)" , $this->conn ) ;
	while( $event = mysql_fetch_object( $yrs ) ) {
		$time = $event->start > $start ? $event->start : $start ;
		if( ! $event->allday ) {
			$time += $tzoffset ;
			$event->end += $tzoffset ;
		}
		$time -= ( $time + $gmtoffset ) % 86400 ;
		while( $time < $end && $time < $event->end ) {
			$ret[ date( 'j' , $time ) ] = 1 ;
			$time += 86400 ;
		}
	}*/

	

	// �S���C�x���g�ȊO�̏���
	$result = mysql_query( "SELECT summary,id,start FROM $this->table WHERE admission > 0 AND start >= $range_start_s AND start < $range_end_s AND ($whr_categories) AND ($whr_class) AND allday <= 0" , $this->conn ) ;

	while( list( $title , $id , $server_time ) = mysql_fetch_row( $result ) ) {
		$user_time = $server_time + $tzoffset_s2u ;
		if( date( 'n' , $user_time ) != $this->month ) continue ;
		$ret[ date('j',$user_time) ] = 1 ;
	}

	// �S���C�x���g��p�̏���
	$result = mysql_query( "SELECT summary,id,start,end FROM $this->table WHERE admission > 0 AND start >= $range_start_s AND start < $range_end_s AND ($whr_categories) AND ($whr_class) AND allday > 0" , $this->conn ) ;

	while( list( $title , $id , $start_s , $end_s ) = mysql_fetch_row( $result ) ) {
		if( $start_s < $range_start_s ) $start_s = $range_start_s ;
		if( $end_s > $range_end_s ) $end_s = $range_end_s ;

		while( $start_s < $end_s ) {
			$user_time = $start_s + $tzoffset_s2u ;
			if( date( 'n' , $user_time ) == $this->month ) {
				$ret[ date('j',$user_time) ] = 1 ;
			}
			$start_s += 86400 ;
		}
	}

	return $ret ;
}



// �~�j�J�����_�[�\���p�������Ԃ�
function get_mini_calendar_html( $get_target = '' , $query_string = '' , $mode = '' )
{
	// ���s���Ԍv���X�^�[�g
	// list( $usec , $sec ) = explode( " " , microtime() ) ;
	// $picalstarttime = $sec + $usec ;

	// $PHP_SELF = $_SERVER['SCRIPT_NAME'] ;
	// if( $get_target == '' ) $get_target = $PHP_SELF ;

	$original_level = error_reporting( E_ALL ^ E_NOTICE ) ;
	require_once( "$this->base_path/include/patTemplate.php" ) ;
	$tmpl = new PatTemplate() ;
	$tmpl->setBasedir( "$this->images_path" ) ;

	// �\�����[�h�ɉ����āA�e���v���[�g�t�@�C����U�蕪��
	switch( $mode ) {
		case 'NO_YEAR' :
			// �N�ԕ\���p
			$tmpl->readTemplatesFromFile( "minical_for_yearly.tmpl.html" ) ;
			$target_highlight_flag = false ;
			break ;
		case 'NO_NAVIGATE' :
			// ���Ԃ̉����Q�Ɨp
			$tmpl->readTemplatesFromFile( "minical_for_monthly.tmpl.html" ) ;
			$target_highlight_flag = false ;
			break ;
		default :
			// �ʏ�̃~�j�J�����_�[�u���b�N�p
			$tmpl->readTemplatesFromFile( "minical.tmpl.html" ) ;
			$target_highlight_flag = true ;
			break ;
	}

	// �����̊e�����C�x���g�������Ă��邩�ǂ������擾
	$event_dates = $this->get_flags_date_has_events( mktime(0,0,0,$this->month,1,$this->year) , mktime(0,0,0,$this->month+1,1,$this->year) ) ;

	// �O���͌����A�����͌����Ƃ���
	$prev_month = date("Y-n-j", mktime(0,0,0,$this->month,0,$this->year));
	$next_month = date("Y-n-j", mktime(0,0,0,$this->month+1,1,$this->year));

	// $tmpl->addVar( "WholeBoard" , "PHP_SELF" , '' ) ;
	$tmpl->addVar( "WholeBoard" , "GET_TARGET" , $get_target ) ;
	$tmpl->addVar( "WholeBoard" , "QUERY_STRING" , $query_string ) ;

	$tmpl->addVar( "WholeBoard" , "MB_PREV_MONTH" , _PICAL_MB_PREV_MONTH ) ;
	$tmpl->addVar( "WholeBoard" , "MB_NEXT_MONTH" , _PICAL_MB_NEXT_MONTH ) ;
	$tmpl->addVar( "WholeBoard" , "MB_LINKTODAY" , _PICAL_MB_LINKTODAY ) ;

	$tmpl->addVar( "WholeBoard" , "SKINPATH" , $this->images_url ) ;
	$tmpl->addVar( "WholeBoard" , "FRAME_CSS" , $this->frame_css ) ;
//	$tmpl->addVar( "WholeBoard" , "YEAR" , $this->year ) ;
//	$tmpl->addVar( "WholeBoard" , "MONTH" , $this->month ) ;
	$tmpl->addVar( "WholeBoard" , "MONTH_NAME" , $this->month_middle_names[ $this->month ] ) ;
	$tmpl->addVar( "WholeBoard" , "YEAR_MONTH_TITLE" , sprintf( _PICAL_FMT_YEAR_MONTH , $this->year , $this->month_middle_names[ $this->month ] ) ) ;
	$tmpl->addVar( "WholeBoard" , "PREV_MONTH" , $prev_month ) ;
	$tmpl->addVar( "WholeBoard" , "NEXT_MONTH" , $next_month ) ;

	$tmpl->addVar( "WholeBoard" , "CALHEAD_BGCOLOR" , $this->calhead_bgcolor ) ;
	$tmpl->addVar( "WholeBoard" , "CALHEAD_COLOR" , $this->calhead_color ) ;


	$first_date = getdate(mktime(0,0,0,$this->month,1,$this->year));
	$date = ( - $first_date['wday'] + $this->week_start - 7 ) % 7 ;
	$wday_end = 7 + $this->week_start ;

	// �j�������[�v
	$rows = array() ;
	for( $wday = $this->week_start ; $wday < $wday_end ; $wday ++ ) {
		if( $wday % 7 == 0 ) { 
			//	Sunday
			$bgcolor = $this->sunday_bgcolor ;
			$color = $this->sunday_color ;
		} elseif( $wday == 6 ) { 
			//	Saturday
			$bgcolor = $this->saturday_bgcolor ;
			$color = $this->saturday_color ;
		} else { 
			// Weekday
			$bgcolor = $this->weekday_bgcolor ;
			$color = $this->weekday_color ;
		}

		// �e���v���[�g�p�z��ւ̃f�[�^�Z�b�g
		array_push( $rows , array(
			"BGCOLOR" => $bgcolor ,
			"COLOR" => $color ,
			"DAYNAME" => $this->week_short_names[ $wday % 7 ] ,
		) ) ;
	}

	// �e���v���[�g�Ƀf�[�^�𖄂ߍ���
	$tmpl->addRows( "DayNameLoop" , $rows ) ;
	$tmpl->parseTemplate( "DayNameLoop" , 'w' ) ;

	// �T (row) ���[�v
	for( $week = 0 ; $week < 6 ; $week ++ ) {

		$rows = array() ;

		// �� (col) ���[�v
		for( $wday = $this->week_start ; $wday < $wday_end ; $wday ++ ) {
			$date ++ ;
			if( ! checkdate($this->month,$date,$this->year) ) {
				// ���͈̔͊O
				array_push( $rows , array(
					"GET_TARGET" => $get_target ,
					"QUERY_STRING" => $query_string ,
					"SKINPATH" => $this->images_url ,
					"DATE" => date( 'j' , mktime( 0 , 0 , 0 , $this->month , $date , $this->year ) ) ,
					"DATE_TYPE" => 0
				) ) ;
				continue ;
			}

			$link = "$this->year-$this->month-$date" ;

			// �j���^�C�v�ɂ��`��F�U�蕪��
			if( isset( $this->holidays[$link] ) ) {
				//	Holiday
				$bgcolor = $this->holiday_bgcolor ;
				$color = $this->holiday_color ;
			} elseif( $wday % 7 == 0 ) { 
				//	Sunday
				$bgcolor = $this->sunday_bgcolor ;
				$color = $this->sunday_color ;
			} elseif( $wday == 6 ) { 
				//	Saturday
				$bgcolor = $this->saturday_bgcolor ;
				$color = $this->saturday_color ;
			} else { 
				// Weekday
				$bgcolor = $this->weekday_bgcolor ;
				$color = $this->weekday_color ;
			}

			// �I����̔w�i�F�n�C���C�g����
			if( $date == $this->date && $target_highlight_flag ) $bgcolor = $this->targetday_bgcolor ;

			// �e���v���[�g�p�z��ւ̃f�[�^�Z�b�g
			array_push( $rows , array(
				"GET_TARGET" => $get_target ,
				"QUERY_STRING" => $query_string ,

				"BGCOLOR" => $bgcolor ,
				"COLOR" => $color ,
				"LINK" => $link ,
				"DATE" => $date ,
				"DATE_TYPE" => $event_dates[ $date ] + 1
			) ) ;
		}
		// �e���v���[�g�Ƀf�[�^�𖄂ߍ���
		$tmpl->addRows( "DailyLoop" , $rows ) ;
		$tmpl->parseTemplate( "DailyLoop" , 'w' ) ;
		$tmpl->parseTemplate( "WeekLoop" , 'a' ) ;
	}

	$ret = $tmpl->getParsedTemplate() ;

	error_reporting( $original_level ) ;

	// ���s���ԋL�^
	// list( $usec , $sec ) = explode( " " , microtime() ) ;
	// error_log( "MiniCalendar " . ( $sec + $usec - $picalstarttime ) . "sec." , 0 ) ;

	return $ret ;
}



/*******************************************************************/
/*        ���C�����\���֐�                                         */
/*******************************************************************/

// �N�ԃJ�����_�[�S�̂̕\���ipatTemplate�g�p)
function get_yearly( $get_target = '' , $query_string = '' , $for_print = false )
{
	// $PHP_SELF = $_SERVER['SCRIPT_NAME'] ;
	// if( $get_target == '' ) $get_target = $PHP_SELF ;

	$original_level = error_reporting( E_ALL ^ E_NOTICE ) ;
	require_once( "$this->base_path/include/patTemplate.php" ) ;
	$tmpl = new PatTemplate() ;
	$tmpl->readTemplatesFromFile( "$this->images_path/yearly.tmpl.html" ) ;

	// setting skin folder
	$tmpl->addVar( "WholeBoard" , "SKINPATH" , $this->images_url ) ;

	// Static parameter for the request
	$tmpl->addVar( "WholeBoard" , "GET_TARGET" , $get_target ) ;
	$tmpl->addVar( "WholeBoard" , "QUERY_STRING" , $query_string ) ;
	$tmpl->addVar( "WholeBoard" , "PRINT_LINK" , "$this->base_url/print.php?cid=$this->now_cid&amp;smode=Yearly&amp;caldate=$this->caldate" ) ;
	$tmpl->addVar( "WholeBoard" , "LANG_PRINT" , _PICAL_BTN_PRINT ) ;
	if( $for_print ) $tmpl->addVar( "WholeBoard" , "PRINT_ATTRIB" , "width='0' height='0'" ) ;

	// �J�e�S���[�I���{�b�N�X
	$tmpl->addVar( "WholeBoard" , "CATEGORIES_SELFORM" , $this->get_categories_selform( $get_target ) ) ;
	$tmpl->addVar( "WholeBoard" , "CID" , $this->now_cid ) ;

	// Variables required in header part etc.
	$tmpl->addVars( "WholeBoard" , $this->get_calendar_information( 'Y' ) ) ;

	$tmpl->addVar( "WholeBoard" , "LANG_JUMP" , _PICAL_BTN_JUMP ) ;

	// �e���̃~�j�J�����_�[
	// $this->caldate �̃o�b�N�A�b�v
	$backuped_caldate = $this->caldate ;

	// 12�������̃~�j�J�����_�[�擾���[�v
	for( $m = 1 ; $m <= 12 ; $m ++ ) {
		$this->set_date( date("Y-n-j", mktime(0,0,0,$m,1,$this->year)) ) ;
		$tmpl->addVar( "WholeBoard" , "MINICAL$m" , $this->get_mini_calendar_html( $get_target , $query_string , "NO_YEAR" ) ) ;
	}

	// $this->caldate �̃��X�g�A
	$this->set_date( $backuped_caldate ) ;

	// content generated from patTemplate
	$ret = $tmpl->getParsedTemplate( "WholeBoard" ) ;

	error_reporting( $original_level ) ;

	return $ret ;
}



// ���ԃJ�����_�[�S�̂̕\���ipatTemplate�g�p)
function get_monthly( $get_target = '' , $query_string = '' , $for_print = false )
{
	// $PHP_SELF = $_SERVER['SCRIPT_NAME'] ;
	// if( $get_target == '' ) $get_target = $PHP_SELF ;

	$original_level = error_reporting( E_ALL ^ E_NOTICE ) ;
	require_once( "$this->base_path/include/patTemplate.php" ) ;
	$tmpl = new PatTemplate() ;
	$tmpl->readTemplatesFromFile( "$this->images_path/monthly.tmpl.html" ) ;

	// setting skin folder
	$tmpl->addVar( "WholeBoard" , "SKINPATH" , $this->images_url ) ;

	// Static parameter for the request
	$tmpl->addVar( "WholeBoard" , "GET_TARGET" , $get_target ) ;
	$tmpl->addVar( "WholeBoard" , "QUERY_STRING" , $query_string ) ;
	$tmpl->addVar( "WholeBoard" , "YEAR_MONTH_TITLE" , sprintf( _PICAL_FMT_YEAR_MONTH , $this->year , $this->month_middle_names[ $this->month ] ) ) ;
	$tmpl->addVar( "WholeBoard" , "PRINT_LINK" , "$this->base_url/print.php?cid=$this->now_cid&amp;smode=Monthly&amp;caldate=$this->caldate" ) ;
	$tmpl->addVar( "WholeBoard" , "LANG_PRINT" , _PICAL_BTN_PRINT ) ;
	if( $for_print ) $tmpl->addVar( "WholeBoard" , "PRINT_ATTRIB" , "width='0' height='0'" ) ;

	// �J�e�S���[�I���{�b�N�X
	$tmpl->addVar( "WholeBoard" , "CATEGORIES_SELFORM" , $this->get_categories_selform( $get_target ) ) ;
	$tmpl->addVar( "WholeBoard" , "CID" , $this->now_cid ) ;

	// Variables required in header part etc.
	$tmpl->addVars( "WholeBoard" , $this->get_calendar_information( 'M' ) ) ;

	$tmpl->addVar( "WholeBoard" , "LANG_JUMP" , _PICAL_BTN_JUMP ) ;

	// BODY of the calendar
	$tmpl->addVar( "WholeBoard" , "CALENDAR_BODY" , $this->get_monthly_html( $get_target , $query_string ) ) ;

	// legends of long events
	foreach( $this->long_event_legends as $bit => $legend ) {
		$tmpl->addVar( "LongEventLegends" , "BIT_MASK" , 1 << ( $bit - 1 ) ) ;
		$tmpl->addVar( "LongEventLegends" , "LEGEND_ALT" , _PICAL_MB_ALLDAY_EVENT . " $bit" ) ;
		$tmpl->addVar( "LongEventLegends" , "LEGEND" , $legend ) ;
		$tmpl->addVar( "LongEventLegends" , "SKINPATH" , $this->images_url ) ;
		$tmpl->parseTemplate( "LongEventLegends" , "a" ) ;
	}

	// �挎�E�����̃~�j�J�����_�[
	// $this->caldate �̃o�b�N�A�b�v
	$backuped_caldate = $this->caldate ;
	// �O�����̓��t���Z�b�g���A�O���̃~�j�J�����_�[���Z�b�g
	$this->set_date( date("Y-n-j", mktime(0,0,0,$this->month,0,$this->year)) ) ;
	$tmpl->addVar( "WholeBoard" , "PREV_MINICAL" , $this->get_mini_calendar_html( $get_target , $query_string , "NO_NAVIGATE" ) ) ;
	// �����n�̓��t���Z�b�g���A�~�j�J�����_�[��\��
	$this->set_date( date("Y-n-j", mktime(0,0,0,$this->month+2,1,$this->year)) ) ;
	$tmpl->addVar( "WholeBoard" , "NEXT_MINICAL" , $this->get_mini_calendar_html( $get_target , $query_string , "NO_NAVIGATE" ) ) ;
	// $this->caldate �̃��X�g�A
	$this->set_date( $backuped_caldate ) ;

	// content generated from patTemplate
	$ret = $tmpl->getParsedTemplate( "WholeBoard" ) ;

	error_reporting( $original_level ) ;

	return $ret ;
}



// �T�ԃJ�����_�[�S�̂̕\���ipatTemplate�g�p)
function get_weekly( $get_target = '' , $query_string = '' , $for_print = false )
{
	// $PHP_SELF = $_SERVER['SCRIPT_NAME'] ;
	// if( $get_target == '' ) $get_target = $PHP_SELF ;

	$original_level = error_reporting( E_ALL ^ E_NOTICE ) ;
	require_once( "$this->base_path/include/patTemplate.php" ) ;
	$tmpl = new PatTemplate() ;
	$tmpl->readTemplatesFromFile( "$this->images_path/weekly.tmpl.html" ) ;

	// setting skin folder
	$tmpl->addVar( "WholeBoard" , "SKINPATH" , $this->images_url ) ;

	// Static parameter for the request
	$tmpl->addVar( "WholeBoard" , "GET_TARGET" , $get_target ) ;
	$tmpl->addVar( "WholeBoard" , "QUERY_STRING" , $query_string ) ;
	$tmpl->addVar( "WholeBoard" , "PRINT_LINK" , "$this->base_url/print.php?cid=$this->now_cid&amp;smode=Weekly&amp;caldate=$this->caldate" ) ;
	$tmpl->addVar( "WholeBoard" , "LANG_PRINT" , _PICAL_BTN_PRINT ) ;
	if( $for_print ) $tmpl->addVar( "WholeBoard" , "PRINT_ATTRIB" , "width='0' height='0'" ) ;

	// �J�e�S���[�I���{�b�N�X
	$tmpl->addVar( "WholeBoard" , "CATEGORIES_SELFORM" , $this->get_categories_selform( $get_target ) ) ;
	$tmpl->addVar( "WholeBoard" , "CID" , $this->now_cid ) ;

	// Variables required in header part etc.
	$tmpl->addVars( "WholeBoard" , $this->get_calendar_information( 'W' ) ) ;

	$tmpl->addVar( "WholeBoard" , "LANG_JUMP" , _PICAL_BTN_JUMP ) ;

	// BODY of the calendar
	$tmpl->addVar( "WholeBoard" , "CALENDAR_BODY" , $this->get_weekly_html( $get_target , $query_string ) ) ;

	// content generated from patTemplate
	$ret = $tmpl->getParsedTemplate( "WholeBoard" ) ;

	error_reporting( $original_level ) ;

	return $ret ;
}



// ����J�����_�[�S�̂̕\���ipatTemplate�g�p)
function get_daily( $get_target = '' , $query_string = '' , $for_print = false )
{
	// $PHP_SELF = $_SERVER['SCRIPT_NAME'] ;
	// if( $get_target == '' ) $get_target = $PHP_SELF ;

	$original_level = error_reporting( E_ALL ^ E_NOTICE ) ;
	require_once( "$this->base_path/include/patTemplate.php" ) ;
	$tmpl = new PatTemplate() ;
	$tmpl->readTemplatesFromFile( "$this->images_path/daily.tmpl.html" ) ;

	// setting skin folder
	$tmpl->addVar( "WholeBoard" , "SKINPATH" , $this->images_url ) ;

	// Static parameter for the request
	$tmpl->addVar( "WholeBoard" , "GET_TARGET" , $get_target ) ;
	$tmpl->addVar( "WholeBoard" , "QUERY_STRING" , $query_string ) ;
	$tmpl->addVar( "WholeBoard" , "PRINT_LINK" , "$this->base_url/print.php?cid=$this->now_cid&amp;smode=Daily&amp;caldate=$this->caldate" ) ;
	$tmpl->addVar( "WholeBoard" , "LANG_PRINT" , _PICAL_BTN_PRINT ) ;
	if( $for_print ) $tmpl->addVar( "WholeBoard" , "PRINT_ATTRIB" , "width='0' height='0'" ) ;

	// �J�e�S���[�I���{�b�N�X
	$tmpl->addVar( "WholeBoard" , "CATEGORIES_SELFORM" , $this->get_categories_selform( $get_target ) ) ;
	$tmpl->addVar( "WholeBoard" , "CID" , $this->now_cid ) ;

	// Variables required in header part etc.
	$tmpl->addVars( "WholeBoard" , $this->get_calendar_information( 'D' ) ) ;

	$tmpl->addVar( "WholeBoard" , "LANG_JUMP" , _PICAL_BTN_JUMP ) ;

	// BODY of the calendar
	$tmpl->addVar( "WholeBoard" , "CALENDAR_BODY" , $this->get_daily_html( $get_target , $query_string ) ) ;

	// content generated from patTemplate
	$ret = $tmpl->getParsedTemplate( "WholeBoard" ) ;

	error_reporting( $original_level ) ;

	return $ret ;
}



// �J�����_�[�̃w�b�_�����ɕK�v�ȏ���A�z�z��ŕԂ��i���ԁE�T�ԁE�P�����ʁj
function get_calendar_information( $mode = 'M' )
{
	$ret = array() ;

	// ��{���
	$ret[ 'TODAY' ] = date( "Y-n-j" ) ;		// GIJ TODO �v�蒼���i�g��Ȃ��H�j
	$ret[ 'CALDATE' ] = $this->caldate ;
	$ret[ 'DISP_YEAR' ] = sprintf( _PICAL_FMT_YEAR , $this->year ) ;
	$ret[ 'DISP_MONTH' ] = $this->month_middle_names[ $this->month ] ;
	$ret[ 'DISP_DATE' ] = $this->date_long_names[ $this->date ] ;
	$ret[ 'DISP_DAY' ] = "({$this->week_middle_names[ $this->day ]})" ;
	list( $bgcolor , $color ) =  $this->daytype_to_colors( $this->daytype ) ;
	$ret[ 'DISP_DAY_COLOR' ] = $color ;
	$ret[ 'COPYRIGHT' ] = PICAL_COPYRIGHT ;

	// �w�b�_�[���̃J���[
	$ret[ 'CALHEAD_BGCOLOR' ]  =  $this->calhead_bgcolor ;
	$ret[ 'CALHEAD_COLOR' ] = $this->calhead_color ;

	// �A�C�R����alt(title)
	$ret[ 'ICON_LIST' ] = _PICAL_ICON_LIST ;
	$ret[ 'ICON_DAILY' ] = _PICAL_ICON_DAILY ;
	$ret[ 'ICON_WEEKLY' ] = _PICAL_ICON_WEEKLY ;
	$ret[ 'ICON_MONTHLY' ] = _PICAL_ICON_MONTHLY ;
	$ret[ 'ICON_YEARLY' ] = _PICAL_ICON_YEARLY ;

	// ���b�Z�[�W�u���b�N
	$ret[ 'MB_PREV_YEAR' ] = _PICAL_MB_PREV_YEAR ;
	$ret[ 'MB_NEXT_YEAR' ] = _PICAL_MB_NEXT_YEAR ;
	$ret[ 'MB_PREV_MONTH' ] = _PICAL_MB_PREV_MONTH ;
	$ret[ 'MB_NEXT_MONTH' ] = _PICAL_MB_NEXT_MONTH ;
	$ret[ 'MB_PREV_WEEK' ] = _PICAL_MB_PREV_WEEK ;
	$ret[ 'MB_NEXT_WEEK' ] = _PICAL_MB_NEXT_WEEK ;
	$ret[ 'MB_PREV_DATE' ] = _PICAL_MB_PREV_DATE ;
	$ret[ 'MB_NEXT_DATE' ] = _PICAL_MB_NEXT_DATE ;
	$ret[ 'MB_LINKTODAY' ] = _PICAL_MB_LINKTODAY ;

	// �O���ւ̃����N
	$ret[ 'PREV_YEAR' ] = date("Y-n-j", mktime(0,0,0,$this->month,$this->date,$this->year-1));
	$ret[ 'NEXT_YEAR' ] = date("Y-n-j", mktime(0,0,0,$this->month,$this->date,$this->year+1));
	$ret[ 'PREV_MONTH' ] = date("Y-n-j", mktime(0,0,0,$this->month,0,$this->year));
	$ret[ 'NEXT_MONTH' ] = date("Y-n-j", mktime(0,0,0,$this->month+1,1,$this->year));
	$ret[ 'PREV_WEEK' ] = date("Y-n-j", mktime(0,0,0,$this->month,$this->date-7,$this->year)) ;
	$ret[ 'NEXT_WEEK' ] = date("Y-n-j", mktime(0,0,0,$this->month,$this->date+7,$this->year)) ;
	$ret[ 'PREV_DATE' ] = date("Y-n-j", mktime(0,0,0,$this->month,$this->date-1,$this->year)) ;
	$ret[ 'NEXT_DATE' ] = date("Y-n-j", mktime(0,0,0,$this->month,$this->date+1,$this->year)) ;

	// ���t�W�����v�p�t�H�[���̊e�R���g���[��
	// �N���I�����̏����l
	if( empty( $_POST[ 'pical_year' ] ) ) $year = $this->year ;
	else  $year = intval( $_POST[ 'pical_year' ] ) ;
	if( empty( $_POST[ 'pical_month' ] ) ) $month = $this->month ;
	else $month = intval( $_POST[ 'pical_month' ] ) ;
	if( empty( $_POST[ 'pical_date' ] ) ) $date = $this->date ;
	else $date = intval( $_POST[ 'pical_date' ] ) ;

	// �N�̑I����(2001�`2020 �Ƃ���)
	$year_options = "" ;
	for( $y = 2001 ; $y <= 2020 ; $y ++ ) {
		if( $y == $year ) {
			$year_options .= "\t\t\t<option value='$y' selected='selected'>".sprintf(strip_tags(_PICAL_FMT_YEAR),$y)."</option>\n" ;
		} else {
			$year_options .= "\t\t\t<option value='$y'>".sprintf(strip_tags(_PICAL_FMT_YEAR),$y)."</option>\n" ;
		}
	}
	$ret[ 'YEAR_OPTIONS' ] = $year_options ;

	// ���̑I����
	$month_options = "" ;
	for( $m = 1 ; $m <= 12 ; $m ++ ) {
		if( $m == $month ) {
			$month_options .= "\t\t\t<option value='$m' selected='selected'>{$this->month_short_names[$m]}</option>\n" ;
		} else {
			$month_options .= "\t\t\t<option value='$m'>{$this->month_short_names[$m]}</option>\n" ;
		}
	}
	$ret[ 'MONTH_OPTIONS' ] = $month_options ;

	// ���̑I����
	if( $mode == 'W' || $mode == 'D' ) {
		$date_options = "" ;
		for( $d = 1 ; $d <= 31 ; $d ++ ) {
			if( $d == $date ) {
				$date_options .= "\t\t\t<option value='$d' selected='selected'>{$this->date_short_names[$d]}</option>\n" ;
			} else {
				$date_options .= "\t\t\t<option value='$d'>{$this->date_short_names[$d]}</option>\n" ;
			}
		}

		$ret[ 'YMD_SELECTS' ] = sprintf( _PICAL_FMT_YMD , "<select name='pical_year'>{$ret['YEAR_OPTIONS']}</select> &nbsp; " , "<select name='pical_month'>{$ret['MONTH_OPTIONS']}</select> &nbsp; " , "<select name='pical_date'>$date_options</select> &nbsp; " ) ;
		if( $this->week_numbering ) {
			if( $this->day == 0 && ! $this->week_start ) $weekno = date( 'W' , $this->unixtime + 86400 ) ;
			else $weekno = date( 'W' , $this->unixtime ) ;
			$ret[ 'YMW_TITLE' ] = sprintf( _PICAL_FMT_YW , $this->year , $weekno ) ;
		} else {
			$week_number = floor( ( $this->date - ( $this->day - $this->week_start + 7 ) % 7 + 12 ) / 7 ) ;
			$ret[ 'YMW_TITLE' ] = sprintf( _PICAL_FMT_YMW , $this->year , $this->month_middle_names[ $this->month ] , $this->week_numbers[ $week_number ] ) ;
		}
		$ret[ 'YMD_TITLE' ] = sprintf( _PICAL_FMT_YMD , $this->year , $this->month_middle_names[ $this->month ] , $this->date_long_names[$date] ) ;
	}

	return $ret ;
}



// �J�����_�[�̖{�̂�Ԃ��i�P�������j
function get_monthly_html( $get_target = '' , $query_string = '' )
{
	// $PHP_SELF = $_SERVER['SCRIPT_NAME'] ;
	// if( $get_target == '' ) $get_target = $PHP_SELF ;

	// get the result of plugins
	$plugin_returns = array() ;
	if( strtolower( get_class( $this ) ) == 'pical_xoops' ) {
		$db =& Database::getInstance() ;
		$myts =& MyTextSanitizer::getInstance() ;
		$now = time() ;
		$just1gif = 0 ;

		$tzoffset_s2u = intval( ( $this->user_TZ - $this->server_TZ ) * 3600 ) ;
		$plugins = $this->get_plugins( "monthly" ) ;
		foreach( $plugins as $plugin ) {
			$plugin_fullpath = $this->base_path . '/' . $this->plugins_path_monthly . '/' . $plugin['file'] ;
			if( file_exists( $plugin_fullpath ) ) {
				include $plugin_fullpath ;
			}
		}
	}

	// �J�n�j�������j���̂��߂̏����i�Ȃ�Ƃ��ꓖ����I�����j
	$sunday_th = "<th class='sunday'>{$this->week_middle_names[0]}</th>\n" ;
	if( $this->week_start ) {
		$week_top_th = "" ;
		$week_end_th = $sunday_th ;
	} else {
		$week_top_th = $sunday_th ;
		$week_end_th = "" ;
	}

	$ret = "
	 <table id='calbody'>
	 <!-- week names -->
	 <tr class='pical-monthly'>
	   <th class='pical-weekmark'>
	   	<img src='$this->images_url/spacer.gif' alt='' width='10' height='20' />
	   </th>
	   $week_top_th
	   <th class='calweekname'>{$this->week_middle_names[1]}</span></th>
	   <th class='calweekname'>{$this->week_middle_names[2]}</span></th>
	   <th class='calweekname'>{$this->week_middle_names[3]}</span></th>
	   <th class='calweekname'>{$this->week_middle_names[4]}</span></th>
	   <th class='calweekname'>{$this->week_middle_names[5]}</span></th>
	   <th align='center'><span class='calweek_saturday'>{$this->week_middle_names[6]}</span></th>
	   $week_end_th
	 </tr>\n";

	$mtop_unixtime = mktime(0,0,0,$this->month,1,$this->year) ;
	$mtop_weekno = date( 'W' , $mtop_unixtime ) ;
	if( $mtop_weekno >= 52 ) $mtop_weekno = 1 ;
	$first_date = getdate( $mtop_unixtime ) ;
	$date = ( - $first_date['wday'] + $this->week_start - 7 ) % 7 ;
	$wday_end = 7 + $this->week_start ;
	$last_date = date( 't' , $this->unixtime ) ;
	$mlast_unixtime = mktime(0,0,0,$this->month+1,1,$this->year) ;

	// �������v�Z���AWHERE�߂̊��ԂɊւ����������
	$tzoffset = intval( ( $this->user_TZ - $this->server_TZ ) * 3600 ) ;
	if( $tzoffset == 0 ) {
		// �������Ȃ��ꍇ �iMySQL�ɕ��ׂ����������Ȃ����߁A�����ŏ����������Ƃ�)
		$whr_term = "start<='$mlast_unixtime' AND end>'$mtop_unixtime'" ;
	} else {
		// ����������ꍇ�́Aallday�ɂ���ďꍇ����
		$whr_term = "(allday AND start<='$mlast_unixtime' AND end>'$mtop_unixtime') OR ( ! allday AND start<='".( $mlast_unixtime - $tzoffset )."' AND end>'".( $mtop_unixtime - $tzoffset )."')" ;
	}

	// �J�e�S���[�֘A��WHERE�����擾
	$whr_categories = $this->get_where_about_categories() ;

	// CLASS�֘A��WHERE�����擾
	$whr_class = $this->get_where_about_class() ;

	// �����C�x���g��Unique-ID���ő�4���A�擾���Ă���
	$rs = mysql_query( "SELECT DISTINCT unique_id FROM $this->table WHERE ($whr_term) AND ($whr_categories) AND ($whr_class) AND (allday & 2) LIMIT 4" , $this->conn ) ;
	$long_event_ids = array() ;
	$bit = 1 ;
	while( $event = mysql_fetch_object( $rs ) ) {
		$long_event_ids[ $bit ] = $event->unique_id ;
		$bit ++ ;
	}

	// �ꃖ�����̃X�P�W���[�����܂Ƃ߂Ď擾���Ă���
	$yrs = mysql_query( "SELECT start,end,summary,id,allday,admission,uid,unique_id,categories FROM $this->table WHERE ($whr_term) AND ($whr_categories) AND ($whr_class) ORDER BY start" , $this->conn ) ;
	$numrows_yrs = mysql_num_rows( $yrs ) ;

	// �J�����_�[BODY���\��
	for( $week = 0 ; $week < 6 ; $week ++ ) {

		// �T�\���̃C���f�b�N�X
		if( $date < $last_date ) {
			$alt_week = $this->week_numbering ? sprintf( _PICAL_FMT_WEEKNO , $week + $mtop_weekno ) : $this->week_numbers[$week+1] ;
			$ret .= "<tr>\n<td><a href='$get_target?cid=$this->now_cid&amp;smode=Weekly&amp;caldate=".date('Y-n-j',mktime(0,0,0,$this->month,$date+1,$this->year))."'><img src='$this->images_url/week_index.gif' width='10' height='70' border='0' alt='$alt_week' title='$alt_week' /></a></td>\n" ;
		} else {
			break ;
		}

		for( $wday = $this->week_start ; $wday < $wday_end ; $wday ++ ) {
			$date ++;

			// �Ώی��͈̔͊O�ɂ�����̏���
			if( ! checkdate($this->month,$date,$this->year) ) {
				if( $date < 28 ) 
					$ret .= "<td bgcolor='#EEEEEE' style='$this->frame_css'><span class='calbody'>
					<img src='$this->images_url/spacer.gif' alt='' height='70' /></span></td>\n" ;
				else 
					$ret .= "<td><span class='calbody'><img src='$this->images_url/spacer.gif' alt='' height='70' /></span></td>\n" ;
				continue ;
			}

			$now_unixtime = mktime(0,0,0,$this->month,$date,$this->year) ;
			$toptime_of_day = $now_unixtime + $this->day_start - $tzoffset ;
			$bottomtime_of_day = $toptime_of_day + 86400 ;
			$link = "$this->year-$this->month-$date" ;

			// �X�P�W���[���f�[�^�̕\�����[�v
			$waitings = 0 ;
			$event_str = "<p class='event'>" ;
			$long_event = 0 ;
			if( $numrows_yrs > 0 ) mysql_data_seek( $yrs , 0 ) ;
			while( $event = mysql_fetch_object( $yrs ) ) {
				// �ΏۃC�x���g�����̓��ɂ������Ă��邩�ǂ����̃`�F�b�N
				if( $event->allday ) {
					if( $event->start >= $now_unixtime + 86400 || $event->end <= $now_unixtime ) continue ;
				} else {
					if( $event->start >= $bottomtime_of_day || $event->start != $toptime_of_day && $event->end <= $toptime_of_day ) continue ;
					// ���łɊJ�n�����E�I�������̃`�F�b�N��
					// $event->is_start_date = $event->start >= $toptime_of_day ;
					// $event->is_end_date = $event->end <= $bottomtime_of_day ;
				}

				if( $event->admission ) {

					// �T�j�^�C�Y
					$event->summary = $this->text_sanitizer_for_show( $event->summary ) ;
					// categories
					$catname = $this->text_sanitizer_for_show( $this->categories[ intval( $event->categories ) ]->cat_title ) ;
					// �Ƃ肠�������p33��������Ƃ��Ă���
					$summary = mb_strcut( $event->summary , 0 , 33 ) ;
					if( $summary != $event->summary ) $summary .= ".." ;
					$event_str_tmp = "&bull;&nbsp;<a href='$get_target?smode=Monthly&amp;action=View&amp;event_id=$event->id&amp;caldate=$this->caldate' style='font-size:10px;font-weight:normal;text-decoration:none;' class='$catname'>$summary</a>" ;

					$bit = array_search( $event->unique_id , $long_event_ids ) ;
					// �{���� !== false �Ƃ��ׂ������A�ǂ���1�`4�������Ȃ��̂�
					if( $bit > 0 && $bit <= 4 ) {
						// �����C�x���g�z��ɂ���ΊY���r�b�g�𗧂āAlegends�z��ɓo�^
						$long_event |= 1 << ( $bit - 1 ) ;
						$this->long_event_legends[ $bit ] = $event_str_tmp ;
					} else if( $event->allday & 4 ) {
						// �L�O���t���O�������Ă�����A$holiday_color�ɂ��āA��ԏ�Ɏ����Ă���
						$event_str_tmp = str_replace( " style='" , " style='color:$this->holiday_color;" , $event_str_tmp ) ;
						$event_str = "$event_str_tmp<br />\n$event_str" ;
					} else {
						// �Ȃ���΁A���t�}�X���ɕ`��
						$event_str .= $event_str_tmp . "<br />\n" ;
					}
				} else {
					// �����F�X�P�W���[���̃J�E���g�A�b�v
					if( $this->isadmin || ( $this->user_id > 0 AND $this->user_id == $event->uid ) ) $waitings ++ ;
				}
			}

			// �����F�X�P�W���[���͑��������\��
			if( $waitings > 0 ) $event_str .= "<span style='color:#00FF00;font-size:10px;font-weight:normal;'>".sprintf( _PICAL_NTC_NUMBEROFNEEDADMIT , $waitings )."</span><br />\n" ;

			// drawing the result of plugins
			if( ! empty( $plugin_returns[ $date ] ) ) {
				foreach( $plugin_returns[ $date ] as $item ) {
					$event_str .= "<a href='{$item['link']}' class='event'><img src='$this->images_url/{$item['dotgif']}' alt='{$item['title']}>' />{$item['title']}</a><br />\n" ;

				}
			}
			$event_str .= "</p>";

			// �j���^�C�v�ɂ��`��F�U�蕪��
			$date_part_append = '' ;
			if( isset( $this->holidays[$link] ) ) {
				//	Holiday
				$bgcolor = $this->holiday_bgcolor ;
				$color = $this->holiday_color ;
				if( $this->holidays[ $link ] != 1 ) {
					$date_part_append = "<p class='holiday'>{$this->holidays[ $link ]}</p>\n" ;
				}
			} elseif( $wday % 7 == 0 ) { 
				//	Sunday
				$bgcolor = $this->sunday_bgcolor ;
				$color = $this->sunday_color ;
			} elseif( $wday == 6 ) { 
				//	Saturday
				$bgcolor = $this->saturday_bgcolor ;
				$color = $this->saturday_color ;
			} else { 
				// Weekday
				$bgcolor = $this->weekday_bgcolor ;
				$color = $this->weekday_color ;
			}

			// �I����̔w�i�F�n�C���C�g����
			if( $date == $this->date ) $bgcolor = $this->targetday_bgcolor ;

			// �����C�x���g�̕`��i�w�i�j
			if( $long_event ) {
				$background = "background:url($this->images_url/monthbar_0".dechex($long_event).".gif) top repeat-x $bgcolor;" ;
			} else $background = "background-color:$bgcolor;" ;

			// �\��̒ǉ��i���M�A�C�R���j
			if( $this->insertable )
				$insert_link = "<a href='$get_target?cid=$this->now_cid&amp;smode=Monthly&amp;action=Edit&amp;caldate=$link' class='stencil'>
				<img src='$this->images_url/addevent.gif' border='0' width='14' height='12' alt='"
				._PICAL_MB_ADDEVENT."' /></a>" ;
			else
				$insert_link = "<a href='$get_target?cid=$this->now_cid&amp;smode=Monthly&amp;caldate=$link' class='stencil'>
				<img src='$this->images_url/spacer.gif' alt='' border='0' width='14' height='12' /></a>" ;

			$ret .= 
			"<td>
			<a href='$get_target?cid=$this->now_cid&amp;smode=Daily&amp;caldate=$link' class='calday'>
			$date</a>
			$insert_link
			$date_part_append
			$event_str
			</td>\n" ;

		}
		$ret .= "</tr>\n";
	}

	$ret .= "</table>\n";

	return $ret ;
}



// �J�����_�[�̖{�̂�Ԃ��i�P�T�ԕ��j
function get_weekly_html( )
{
	// $PHP_SELF = $_SERVER['SCRIPT_NAME'] ;

	$ret = "
	 <table border='0' cellspacing='0' cellpadding='0' width='100%' style='border-collapse:collapse;margin:0px;'>
	 <tr>
	   <td><img src='$this->images_url/spacer.gif' alt='' width='10' height='10' /></td>
	   <td><img src='$this->images_url/spacer.gif' alt='' width='80' height='10' /></td>
	   <td><img src='$this->images_url/spacer.gif' alt='' width='80' height='10' /></td>
	   <td><img src='$this->images_url/spacer.gif' alt='' width='80' height='10' /></td>
	   <td><img src='$this->images_url/spacer.gif' alt='' width='80' height='10' /></td>
	   <td><img src='$this->images_url/spacer.gif' alt='' width='80' height='10' /></td>
	   <td><img src='$this->images_url/spacer.gif' alt='' width='80' height='10' /></td>
	   <td><img src='$this->images_url/spacer.gif' alt='' width='80' height='10' /></td>
	 </tr>\n" ;

	$wtop_date = $this->date - ( $this->day - $this->week_start + 7 ) % 7 ;
	$wtop_unixtime = mktime(0,0,0,$this->month,$wtop_date,$this->year) ;
	$wlast_unixtime = mktime(0,0,0,$this->month,$wtop_date+7,$this->year) ;

	// get the result of plugins
	$plugin_returns = array() ;
	if( strtolower( get_class( $this ) ) == 'pical_xoops' ) {
		$db =& Database::getInstance() ;
		$myts =& MyTextSanitizer::getInstance() ;
		$now = time() ;
		$just1gif = 0 ;

		$tzoffset_s2u = intval( ( $this->user_TZ - $this->server_TZ ) * 3600 ) ;
		$plugins = $this->get_plugins( "weekly" ) ;
		foreach( $plugins as $plugin ) {
			$include_ret = @include( $this->base_path . '/' . $this->plugins_path_weekly . '/' . $plugin['file'] ) ;
			if( $include_ret === false ) {
				// weekly emulator by monthly plugin
				$wtop_month = date( 'n' , $wtop_unixtime ) ;
				$wlast_month = date( 'n' , $wlast_unixtime - 86400 ) ;
				$year_backup = $this->year ;
				$month_backup = $this->month ;
				if( $wtop_month == $wlast_month ) {
					@include( $this->base_path . '/' . $this->plugins_path_monthly . '/' . $plugin['file'] ) ;
				} else {
					$plugin_returns_backup = $plugin_returns ;
					$this->year = date( 'Y' , $wtop_unixtime ) ;
					$this->month = $wtop_month ;
					@include( $this->base_path . '/' . $this->plugins_path_monthly . '/' . $plugin['file'] ) ;
					for( $d = 1 ; $d < 21 ; $d ++ ) {
						$plugin_returns[ $d ] = @$plugin_returns_backup[ $d ] ;
					}
					$plugin_returns_backup = $plugin_returns ;
					$this->year = date( 'Y' , $wlast_unixtime ) ;
					$this->month = $wlast_month ;
					@include( $this->base_path . '/' . $this->plugins_path_monthly . '/' . $plugin['file'] ) ;
					for( $d = 8 ; $d < 32 ; $d ++ ) {
						$plugin_returns[ $d ] = @$plugin_returns_backup[ $d ] ;
					}
					$this->year = $year_backup ;
					$this->month = $month_backup ;
				}
			}
		}
	}

	// �������v�Z���AWHERE�߂̊��ԂɊւ����������
	$tzoffset = intval( ( $this->user_TZ - $this->server_TZ ) * 3600 ) ;
	if( $tzoffset == 0 ) {
		// �������Ȃ��ꍇ �iMySQL�ɕ��ׂ����������Ȃ����߁A�����ŏ����������Ƃ�)
		$whr_term = "start<='$wlast_unixtime' AND end>'$wtop_unixtime'" ;
	} else {
		// ����������ꍇ�́Aallday�ɂ���ďꍇ����
		$whr_term = "(allday AND start<='$wlast_unixtime' AND end>'$wtop_unixtime') OR ( ! allday AND start<='".( $wlast_unixtime - $tzoffset )."' AND end>'".( $wtop_unixtime - $tzoffset )."')" ;
	}

	// �J�e�S���[�֘A��WHERE�����擾
	$whr_categories = $this->get_where_about_categories() ;

	// CLASS�֘A��WHERE�����擾
	$whr_class = $this->get_where_about_class() ;

	// ��T�ԕ��̃X�P�W���[�����܂Ƃ߂Ď擾���Ă���
	$ars = mysql_query( "SELECT start,end,summary,id,allday,admission,uid FROM $this->table WHERE admission>0 AND ($whr_term) AND ($whr_categories) AND ($whr_class) ORDER BY start" , $this->conn ) ;
	$numrows_ars = mysql_num_rows( $ars ) ;
	$wrs = mysql_query( "SELECT start,end,summary,id,allday,admission,uid FROM $this->table WHERE admission=0 AND ($whr_term) AND ($whr_categories) AND ($whr_class) ORDER BY start" , $this->conn ) ;
	$numrows_wrs = mysql_num_rows( $wrs ) ;

	// �J�����_�[BODY���\��
	$now_date = $wtop_date ;
	$wday_end = 7 + $this->week_start ;
	for( $wday = $this->week_start ; $wday < $wday_end ; $wday ++ , $now_date ++ ) {

		$now_unixtime = mktime( 0 , 0 , 0 , $this->month , $now_date , $this->year ) ;
		$toptime_of_day = $now_unixtime + $this->day_start - $tzoffset ;
		$bottomtime_of_day = $toptime_of_day + 86400 ;
		$link = date( "Y-n-j" , $now_unixtime ) ;
		$date = date( "j" , $now_unixtime ) ;
		$disp = $this->get_middle_md( $now_unixtime ) ;
		$disp .= "<br />({$this->week_middle_names[$wday]})" ;
		$date_part_append = '' ;
		// �X�P�W���[���\�����̃e�[�u���J�n
		$event_str = "
				<table cellpadding='0' cellspacing='2' style='margin:0px;'>
				  <tr>
				    <td><img src='$this->images_url/spacer.gif' alt='' border='0' width='120' height='4' /></td>
				    <td><img src='$this->images_url/spacer.gif' alt='' border='0' width='360' height='4' /></td>
				  </tr>
		\n" ;
/*
					} else if( $event->allday & 4 ) {
						// �L�O���t���O�������Ă�����A$holiday_color�ɂ��āA��ԏ�Ɏ����Ă���
						$event_str_tmp = str_replace( " style='" , " style='color:$this->holiday_color;" , $event_str_tmp ) ;
						$event_str = "$event_str_tmp<br />\n$event_str" ;
*/


		// ���F�ς݃X�P�W���[���f�[�^�̕\�����[�v
		if( $numrows_ars > 0 ) mysql_data_seek( $ars , 0 ) ;
		while( $event = mysql_fetch_object( $ars ) ) {

			// �ΏۃC�x���g�����̓��ɂ������Ă��邩�ǂ����̃`�F�b�N
			if( $event->allday ) {
				if( $event->start >= $now_unixtime + 86400 || $event->end <= $now_unixtime ) continue ;
			} else {
				if( $event->start >= $bottomtime_of_day || $event->start != $toptime_of_day && $event->end <= $toptime_of_day ) continue ;
				// ���łɊJ�n�����E�I�������̃`�F�b�N��
				$event->is_start_date = $event->start >= $toptime_of_day ;
				$event->is_end_date = $event->end <= $bottomtime_of_day ;
			}

			// �T�j�^�C�Y
			$summary = $this->text_sanitizer_for_show( $event->summary ) ;

			if( $event->allday ) {
				if( $event->allday & 4 ) {
					// �L�O���t���O�̗����Ă������
					$date_part_append .= "<font size='2'><a href='?cid=$this->now_cid&amp;smode=Weekly&amp;action=View&amp;event_id=$event->id&amp;caldate=$this->caldate' class='cal_summary_specialday'><font color='$this->holiday_color'>$summary</font></a></font><br />\n" ;
					continue ;
				} else {
					// �ʏ�̑S���C�x���g
					$time_part = "             <img border='0' src='$this->images_url/dot_allday.gif' />" ;
					$summary_class = "calsummary_allday" ;
				}
			} else {
				// �ʏ�C�x���g�i�����v�Z����j
				$time_part = $this->get_time_desc_for_a_day( $event , $tzoffset , $bottomtime_of_day - $this->day_start , true , true ) ;
				$summary_class = "calsummary" ;
			}

			$event_str .= "
				  <tr>
				    <td valign='top' align='center'>
				      <pre style='margin:0px;'><font size='2'>$time_part</font></pre>
				    </td>
				    <td valign='top'>
				      <font size='2'><a href='?cid=$this->now_cid&amp;smode=Weekly&amp;action=View&amp;event_id=$event->id&amp;caldate=$this->caldate' class='$summary_class'>$summary</a></font>
				    </td>
				  </tr>
			\n" ;
		}

		// �����F�X�P�W���[���̕\�����[�v�iuid����v����Q�X�g�ȊO�̃��R�[�h�̂݁j
		if( $this->isadmin || $this->user_id > 0 ) {

			if( $numrows_wrs > 0 ) mysql_data_seek( $wrs , 0 ) ;
			while( $event = mysql_fetch_object( $wrs ) ) {

				// �ΏۃC�x���g�����̓��ɂ������Ă��邩�ǂ����̃`�F�b�N
				if( $event->allday ) {
					if( $event->start >= $now_unixtime + 86400 || $event->end <= $now_unixtime ) continue ;
				} else {
					if( $event->start >= $bottomtime_of_day || $event->start != $toptime_of_day && $event->end <= $toptime_of_day ) continue ;
					// ���łɊJ�n�����E�I�������̃`�F�b�N��
					$event->is_start_date = $event->start >= $toptime_of_day ;
					$event->is_end_date = $event->end <= $bottomtime_of_day ;
				}

				// �T�j�^�C�Y
				$summary = $this->text_sanitizer_for_show( $event->summary ) ;

				if( $event->allday ) {
					// �S���C�x���g�i�S���t���O�����Ă��Ă��A�ʏ툵���j
					$time_part = "             <img border='0' src='$this->images_url/dot_notadmit.gif' />" ;
					$summary_class = "calsummary_allday" ;
				} else {
					// �ʏ�C�x���g
					$time_part = $this->get_time_desc_for_a_day( $event , $tzoffset , $bottomtime_of_day - $this->day_start , true , false ) ;
					$summary_class = "calsummary" ;
				}

				$event_str .= "
					  <tr>
					    <td valign='top' align='center'>
					      <pre style='margin:0px;'><font size='2'>$time_part</font></pre>
					    </td>
					    <td valign='top'>
					      <font size='2'><a href='?cid=$this->now_cid&amp;smode=Weekly&amp;action=View&amp;event_id=$event->id&amp;caldate=$this->caldate' class='$summary_class'><font color='#00FF00'>$summary ("._PICAL_MB_EVENT_NEEDADMIT.")</font></a></font>
					    </td>
					  </tr>
				\n" ;
			}
		}

		// drawing the result of plugins
		if( ! empty( $plugin_returns[ $date ] ) ) {
			foreach( $plugin_returns[ $date ] as $item ) {
				$event_str .= "
				  <tr>
				    <td></td>
				    <td valign='top'>
			          <font size='2'><a href='{$item['link']}' class='$summary_class'><img src='$this->images_url/{$item['dotgif']}' alt='{$item['title']}>' />{$item['title']}</a></font>
				    </td>
				  </tr>\n" ;
			}
		}

		// �\��̒ǉ��i���M�A�C�R���j
		if( $this->insertable ) $event_str .= "
				  <tr>
				    <td valign='bottom' colspan='2'>
				      &nbsp; <font size='2'><a href='?cid=$this->now_cid&amp;smode=Weekly&amp;action=Edit&amp;caldate=$link'><img src='$this->images_url/addevent.gif' border='0' width='14' height='12' />"._PICAL_MB_ADDEVENT."</a></font>
				    </td>
				  </tr>
		\n" ;

		// �X�P�W���[���\�����̃e�[�u���I��
		$event_str .= "\t\t\t\t</table>\n" ;

		// �j���^�C�v�ɂ��`��F�U�蕪��
		if( isset( $this->holidays[ $link ] ) ) {
			//	Holiday
			$bgcolor = $this->holiday_bgcolor ;
			$color = $this->holiday_color ;
			if( $this->holidays[ $link ] != 1 ) {
				$date_part_append .= "<font color='$this->holiday_color'>{$this->holidays[ $link ]}</font>\n" ;
			}
		} elseif( $wday % 7 == 0 ) { 
			//	Sunday
			$bgcolor = $this->sunday_bgcolor ;
			$color = $this->sunday_color ;
		} elseif( $wday == 6 ) { 
			//	Saturday
			$bgcolor = $this->saturday_bgcolor ;
			$color = $this->saturday_color ;
		} else { 
			// Weekday
			$bgcolor = $this->weekday_bgcolor ;
			$color = $this->weekday_color ;
		}

		// �I����̔w�i�F�n�C���C�g����
		if( $link == $this->caldate ) $body_bgcolor = $this->targetday_bgcolor ;
		else $body_bgcolor = $bgcolor ;

		$ret .= "
	 <tr>
	   <td><img src='$this->images_url/spacer.gif' alt='' width='10' height='80' /></td>
	   <td bgcolor='$bgcolor' align='center' valign='middle' style='vertical-align:middle;text-align:center;$this->frame_css;background-color:$bgcolor'>
	     <a href='?cid=$this->now_cid&amp;smode=Daily&amp;caldate=$link' class='calbody'><font size='3' color='$color'><b><span class='calbody'>$disp</span></b></font></a><br />
	     $date_part_append
	   </td>
	   <td valign='top' colspan='6' bgcolor='$body_bgcolor' style='$this->frame_css;background-color:$body_bgcolor'>
	     $event_str
	   </td>
	 </tr>\n" ;
	}

	$ret .= "\t </table>\n";

	return $ret ;
}



// �J�����_�[�̖{�̂�Ԃ��i�P�����j
function get_daily_html( )
{
	// $PHP_SELF = $_SERVER['SCRIPT_NAME'] ;

	// get the result of plugins
	$plugin_returns = array() ;
	if( strtolower( get_class( $this ) ) == 'pical_xoops' ) {
		$db =& Database::getInstance() ;
		$myts =& MyTextSanitizer::getInstance() ;
		$now = time() ;
		$just1gif = 0 ;

		$tzoffset_s2u = intval( ( $this->user_TZ - $this->server_TZ ) * 3600 ) ;
		$plugins = $this->get_plugins( "daily" ) ;
		foreach( $plugins as $plugin ) {
			$include_ret = @include( $this->base_path . '/' . $this->plugins_path_daily . '/' . $plugin['file'] ) ;
			if( $include_ret === false ) {
				// daily emulator by monthly plugin
				@include( $this->base_path . '/' . $this->plugins_path_monthly . '/' . $plugin['file'] ) ;
			}
		}
	}

	list( $bgcolor , $color ) =  $this->daytype_to_colors( $this->daytype ) ;

	$ret = "
	<table border='0' cellspacing='0' cellpadding='0'>
	 <tr>
	 <td class='calframe'>
	 <table border='0' cellspacing='0' cellpadding='0' width='100%' style='margin:0px;'>
	 <tr>
	   <td colspan='8'><img src='$this->images_url/spacer.gif' alt='' height='10' /></td>
	 </tr>
	 <tr>
	   <td><img src='$this->images_url/spacer.gif' alt='' width='10' height='350' /></td>
	   <td colospan='7' valign='top' bgcolor='$bgcolor' style='$this->frame_css;background-color:$bgcolor'>
	     <table border='0' cellpadding='0' cellspacing='0' style='margin:0px;'>
	       <tr>
	         <td><img src='$this->images_url/spacer.gif' alt='' width='120' height='10' /></td>
	         <td><img src='$this->images_url/spacer.gif' alt='' width='440' height='10' /></td>
	       </tr>
	\n" ;

	// �������v�Z���AWHERE�߂̊��ԂɊւ����������
	$tzoffset = intval( ( $this->user_TZ - $this->server_TZ ) * 3600 ) ;
	$toptime_of_day = $this->unixtime + $this->day_start - $tzoffset ;
	$bottomtime_of_day = $toptime_of_day + 86400 ;
	$whr_term = "(allday AND start<='$this->unixtime' AND end>'$this->unixtime') OR ( ! allday AND start<'$bottomtime_of_day' AND (start='$toptime_of_day' OR end>'$toptime_of_day'))" ;

	// �J�e�S���[�֘A��WHERE�����擾
	$whr_categories = $this->get_where_about_categories() ;

	// CLASS�֘A��WHERE�����擾
	$whr_class = $this->get_where_about_class() ;

	// �����̃X�P�W���[���擾�E�\��
	$yrs = mysql_query( "SELECT start,end,summary,id,allday,admission,uid,description,(start>='$toptime_of_day') AS is_start_date,(end<='$bottomtime_of_day') AS is_end_date FROM $this->table WHERE admission>0 AND ($whr_term) AND ($whr_categories) AND ($whr_class) ORDER BY start,end" , $this->conn ) ;
	$num_rows = mysql_num_rows( $yrs ) ;

	if( $num_rows == 0 ) $ret .= "<tr><td></td><td>"._PICAL_MB_NOEVENT."</td></tr>\n" ;
	else while( $event = mysql_fetch_object( $yrs ) ) {

		if( $event->allday ) {
			// �S���C�x���g�i�����v�Z�Ȃ��j
			$time_part = "             <img border='0' src='$this->images_url/dot_allday.gif' />" ;
		} else {
			// �ʏ�C�x���g�i�����v�Z����j
			$time_part = $this->get_time_desc_for_a_day( $event , $tzoffset , $bottomtime_of_day - $this->day_start , true , true ) ;
		}

		// �T�j�^�C�Y
		$description = $this->textarea_sanitizer_for_show( $event->description ) ;
		$summary = $this->text_sanitizer_for_show( $event->summary ) ;

		$summary_class = $event->allday ? "calsummary_allday" : "calsummary" ;

		$ret .= "
	       <tr>
	         <td valign='top' align='center'>
	           <pre style='margin:0px;'><font size='3'>$time_part</font></pre>
	         </td>
	         <td vlalign='top'>
	           <font size='3'><a href='?cid=$this->now_cid&amp;smode=Daily&amp;action=View&amp;event_id=$event->id&amp;caldate=$this->caldate' class='$summary_class'>$summary</a></font><br />
	           <font size='2'>$description</font><br />
	           &nbsp; 
	         </td>
	       </tr>\n" ;
	}

	// �����F�X�P�W���[���擾�E�\���iuid����v����Q�X�g�ȊO�̃��R�[�h�̂݁j
	if( $this->isadmin || $this->user_id > 0 ) {
	  $whr_uid = $this->isadmin ? "1" : "uid=$this->user_id " ;
	  $yrs = mysql_query( "SELECT start,end,summary,id,allday,admission,uid,description,(start>='$toptime_of_day') AS is_start_date,(end<='$bottomtime_of_day') AS is_end_date FROM $this->table WHERE admission=0 AND $whr_uid AND ($whr_term) AND ($whr_categories) AND ($whr_class) ORDER BY start,end" , $this->conn ) ;

	  while( $event = mysql_fetch_object( $yrs ) ) {

		if( $event->allday ) {
			// �S���C�x���g
			$time_part = "             <img border='0' src='$this->images_url/dot_notadmit.gif' />" ;
		} else {
			// �ʏ�C�x���g
			$time_part = $this->get_time_desc_for_a_day( $event , $tzoffset , $bottomtime_of_day - $this->day_start , true , false ) ;
		}

		// �T�j�^�C�Y
		$summary = $this->text_sanitizer_for_show( $event->summary ) ;

		$summary_class = $event->allday ? "calsummary_allday" : "calsummary" ;

		$ret .= "
	       <tr>
	         <td valign='top' align='center'>
	           <pre style='margin:0px;'><font size='3'>$time_part</font></pre>
	         </td>
	         <td vlalign='top'>
	           <font size='3'><a href='?cid=$this->now_cid&amp;smode=Daily&amp;action=View&amp;event_id=$event->id&amp;caldate=$this->caldate' class='$summary_class'><font color='#00FF00'>$summary ("._PICAL_MB_EVENT_NEEDADMIT.")</font></a></font>
	         </td>
	       </tr>\n" ;
	  }
	}

	// drawing the result of plugins
	if( ! empty( $plugin_returns[ $this->date ] ) ) {
		foreach( $plugin_returns[ $this->date ] as $item ) {
			$ret .= "
	       <tr>
	         <td></td>
	         <td valign='top'>
	           <font size='3'><a href='{$item['link']}' class='$summary_class'><img src='$this->images_url/{$item['dotgif']}' alt='{$item['title']}>' />{$item['title']}</a></font><br />
	           <font size='2'>{$item['description']}</font><br />
	           &nbsp; 
	         </td>
	       </tr>\n" ;
		}
	}

	// �\��̒ǉ��i���M�A�C�R���j
	if( $this->insertable ) $ret .= "
	       <tr>
	         <td valign='bottom' colspan='2'>
	           &nbsp; <font size='2'><a href='?cid=$this->now_cid&amp;smode=Daily&amp;action=Edit&amp;caldate=$this->caldate'><img src='$this->images_url/addevent.gif' border='0' width='14' height='12' />"._PICAL_MB_ADDEVENT."</a></font>
	         </td>
	       </tr>\n" ;

	$ret .= "
	     </table>
	   </td>
	 </tr>
	 </table>
	 </td>
	 </tr>
	</table>\n" ;

	return $ret ;
}



/*******************************************************************/
/*        ���C���� �i�ʃf�[�^����j                              */
/*******************************************************************/

// �X�P�W���[���ڍ׉�ʕ\���p�������Ԃ�
function get_schedule_view_html( $for_print = false )
{
	// $PHP_SELF = $_SERVER['SCRIPT_NAME'] ;
	$smode = empty( $_GET['smode'] ) ? 'Monthly' : preg_replace('/[^a-zA-Z0-9_-]/','',$_GET['smode']) ;
	$editable = $this->editable ;
	$deletable = $this->deletable ;

	// �J�e�S���[�֘A��WHERE�����擾
	$whr_categories = $this->get_where_about_categories() ;

	// CLASS�֘A��WHERE�����擾
	$whr_class = $this->get_where_about_class() ;

	// �\��f�[�^�̎擾
	if( empty( $_GET['event_id'] ) ) die( _PICAL_ERR_INVALID_EVENT_ID ) ;
	$this->original_id = $event_id = intval( $_GET['event_id'] ) ;
	$yrs = mysql_query( "SELECT *,UNIX_TIMESTAMP(dtstamp) AS udtstamp FROM $this->table WHERE id='$event_id' AND ($whr_categories) AND ($whr_class)" , $this->conn ) ;
	if( mysql_num_rows( $yrs ) < 1 ) die( _PICAL_ERR_INVALID_EVENT_ID ) ;
	$event = mysql_fetch_object( $yrs ) ;
	
	$this->event = $event ; // naao

	// rrule�ɂ���ēW�J���ꂽ�f�[�^�ł���΁A����(�e)�̃f�[�^���擾
	if( trim( $event->rrule ) != '' ) {
		if( $event->rrule_pid != $event->id ) {
			$event->id = $event->rrule_pid ;
			$yrs = mysql_query( "SELECT id,start,start_date FROM $this->table WHERE id='$event->rrule_pid' AND ($whr_categories) AND ($whr_class)" , $this->conn ) ;
			if( mysql_num_rows( $yrs ) >= 1 ) {
				$event->id = $event->rrule_pid ;
				$parent_event = mysql_fetch_object( $yrs ) ;
				$this->original_id = $parent_event->id ;
				$is_extracted_record = true ;
			} else {
				$parent_event =& $event ;
			}
		}
		$rrule = $this->rrule_to_human_language( $event->rrule ) ;
	} else {
		$rrule = '' ;
	}

	// ���Ƃ��ƕҏW�\�̐ݒ�ł��A�{������uid�ƃ��R�[�h��uid��
	// ��v�����A���AAdmin���[�h�łȂ����́A�ҏW�E�폜�s�Ƃ���
	if( $event->uid != $this->user_id && ! $this->isadmin ) {
		$editable = false ;
		$deletable = false ;
	}

	// �����F���R�[�h�́A$editable�łȂ���΁A�\�����Ȃ�
	if( ! $event->admission && ! $editable ) die( _PICAL_ERR_NOPERM_TO_SHOW ) ;

	// �ҏW�{�^��
	if( $editable && ! $for_print ) {
		$edit_button = "
			<form method='get' action='index.php' style='margin:0px;'>
				<input type='hidden' name='smode' value='$smode' />
				<input type='hidden' name='action' value='Edit' />
				<input type='hidden' name='event_id' value='$event->id' />
				<input type='hidden' name='caldate' value='$this->caldate' />
				<input type='submit' value='"._PICAL_BTN_EDITEVENT."' />
			</form>\n" ;
	} else $edit_button = "" ;

	// �폜�{�^��
	if( $deletable && ! $for_print ) {
		$delete_button = "
			<form method='post' action='index.php' name='MainForm' style='margin:0px;'>
				<input type='hidden' name='smode' value='$smode' />
				<input type='hidden' name='last_smode' value='$smode' />
				<input type='hidden' name='event_id' value='$event->id' />
				<input type='hidden' name='subevent_id' value='$event_id' />
				<input type='hidden' name='caldate' value='$this->caldate' />
				<input type='hidden' name='last_caldate' value='$this->caldate' />
				<input type='submit' name='delete' value='"._PICAL_BTN_DELETE."' onclick='return confirm(\""._PICAL_CNFM_DELETE_YN."\")' />
				".( ! empty( $is_extracted_record ) ? "<input type='submit' name='delete_one' value='"._PICAL_BTN_DELETE_ONE."' onclick='return confirm(\""._PICAL_CNFM_DELETE_YN."\")' />" : "" )."
				".$GLOBALS['xoopsGTicket']->getTicketHtml( __LINE__ )."
			</form>\n" ;
	} else $delete_button = "" ;

	// iCalendar �o�̓{�^��
	if( $this->can_output_ics && ! $for_print ) {
		$php_self4disp = strtr( @$_SERVER['PHP_SELF'] , '<>\'"' , '    ' ) ;
		$ics_output_button = "
			<a href='?fmt=single&amp;event_id=$event->id&amp;output_ics=1' target='_blank'><img border='0' src='$this->images_url/output_ics_win.gif' alt='"._PICAL_BTN_OUTPUTICS_WIN."' title='"._PICAL_BTN_OUTPUTICS_WIN."' /></a>
			<a href='webcal://{$_SERVER['HTTP_HOST']}$php_self4disp?fmt=single&amp;event_id=$event->id&amp;output_ics=1' target='_blank'><img border='0' src='$this->images_url/output_ics_mac.gif' alt='"._PICAL_BTN_OUTPUTICS_MAC."' title='"._PICAL_BTN_OUTPUTICS_MAC."' /></a>\n" ;
	} else $ics_output_button = "" ;

	// ���t�E���ԕ\���̏���
	if( $event->allday ) {
		// �S���C�x���g�i�����v�Z�Ȃ��j
		$tzoffset = 0 ;
		$event->end -= 300 ;
		$start_time_str = "("._PICAL_MB_ALLDAY_EVENT.")" ;
		$end_time_str = "" ;
	} else {
		// �ʏ�C�x���g�i���[�U���Ԃւ̎����v�Z����j
		$tzoffset = intval( ( $this->user_TZ - $this->server_TZ ) * 3600 ) ;
		$disp_user_tz = $this->get_tz_for_display( $this->user_TZ ) ;
		$start_time_str = $this->get_middle_hi( $event->start + $tzoffset ) . " $disp_user_tz" ;
		$end_time_str = $this->get_middle_hi( $event->end + $tzoffset ) . " $disp_user_tz" ;
		if( $this->user_TZ != $event->event_tz ) {
			$tzoffset_s2e = intval( ( $event->event_tz - $this->server_TZ ) * 3600 ) ;
			$disp_event_tz = $this->get_tz_for_display( $event->event_tz ) ;
			$start_time_str .= " &nbsp; &nbsp; <small>" . $this->get_middle_dhi( $event->start + $tzoffset_s2e ) . " $disp_event_tz</small>" ;
			$end_time_str .= " &nbsp; &nbsp; <small>" . $this->get_middle_dhi( $event->end + $tzoffset_s2e ) . " $disp_event_tz</small>" ;
		}
	}

	if( isset( $event->start_date ) ) {
		// out of unixtimestamp
		$start_date_str = $event->start_date ; // GIJ TODO
	} else {
		// inside unixtimestamp
		$start_date_str = $this->get_long_ymdn( $event->start + $tzoffset ) ;
	}
	if( isset( $event->end_date ) ) {
		// out of unixtimestamp
		$end_date_str = $event->end_date ; // GIJ TODO
	} else {
		// inside unixtimestamp
		$end_date_str = $this->get_long_ymdn( $event->end + $tzoffset ) ;
	}

	$start_datetime_str = "$start_date_str &nbsp; $start_time_str" ;
	$end_datetime_str = "$end_date_str &nbsp; $end_time_str" ;

	// �J��Ԃ��ŁA���A����(�e)�łȂ��f�[�^�́A�e�ւ̃����N�����
	if( trim( $event->rrule ) != '' ) {
		if( isset( $parent_event ) && $parent_event != $event ) {
			if( isset( $parent_event->start_date ) ) {
				$parent_date_str = $parent_event->start_date ; // GIJ TODO
			} else {
				$parent_date_str = $this->get_long_ymdn( $parent_event->start + $tzoffset ) ;
			}
			$rrule .= "<br /><a href='?action=View&amp;event_id=$parent_event->id' target='_blank'>"._PICAL_MB_LINK_TO_RRULE1ST. " $parent_date_str</a>" ;
		} else {
			$rrule .= '<br /> '._PICAL_MB_RRULE1ST ;
		}
	}

	// �J�e�S���[�̕\��
	$cat_titles4show = '' ;
	$cids = explode( "," , $event->categories ) ;
	foreach( $cids as $cid ) {
		$cid = intval( $cid ) ;
		if( isset( $this->categories[ $cid ] ) ) $cat_titles4show .= $this->text_sanitizer_for_show( $this->categories[ $cid ]->cat_title ) . "," ;
	}
	if( $cat_titles4show != '' ) $cat_titles4show = substr( $cat_titles4show , 0 , -1 ) ;

	// ���e�҂̕\��
	$submitter_info = $this->get_submitter_info( $event->uid ) ;

	// ���J�E����J����т��̑Ώۂ̑O����
	if( $event->class == 'PRIVATE' ) {
		$groupid = intval( $event->groupid ) ;
		if( $groupid == 0 ) $group = _PICAL_OPT_PRIVATEMYSELF ;
		else if( isset( $this->groups[ $groupid ] ) ) $group = sprintf( _PICAL_OPT_PRIVATEGROUP , $this->groups[ $groupid ] ) ;
		else $group = _PICAL_OPT_PRIVATEINVALID ;
		$class_status = _PICAL_MB_PRIVATE . sprintf( _PICAL_MB_PRIVATETARGET , $group ) ;
	} else {
		$class_status = _PICAL_MB_PUBLIC ;
	}

	// ���̑��A�\���p�O����
	$admission_status = $event->admission ? _PICAL_MB_EVENT_ADMITTED : _PICAL_MB_EVENT_NEEDADMIT ;
	$last_modified = $this->get_long_ymdn( $event->udtstamp - intval( ( $this->user_TZ - $this->server_TZ ) * 3600 ) ) ;
	$description = $this->textarea_sanitizer_for_show( $event->description ) ;
	$summary = $this->text_sanitizer_for_show( $event->summary ) ;
	$location = $this->text_sanitizer_for_show( $event->location ) ;
	$contact = $this->text_sanitizer_for_show( $event->contact ) ;

	// �ė��p�p
	$this->last_summary = $summary ;

	// �\����
	$ret = "
<h2>"._PICAL_MB_TITLE_EVENTINFO." <small>-"._PICAL_MB_SUBTITLE_EVENTDETAIL."-</small></h2>
	<table border='0' cellpadding='0' cellspacing='2'>
	<tr>
		<td class='head'>"._PICAL_TH_SUMMARY."</td>
		<td class='even'>$summary</td>
	</tr>
	<tr>
		<td class='head'>"._PICAL_TH_STARTDATETIME."</td>
		<td class='even'>$start_datetime_str</td>
	</tr>
	<tr>
		<td class='head'>"._PICAL_TH_ENDDATETIME."</td>
		<td class='even'>$end_datetime_str</td>
	</tr>
	<tr>
		<td class='head'>"._PICAL_TH_LOCATION."</td>
		<td class='even'>$location</td>
	</tr>
	<tr>
		<td class='head'>"._PICAL_TH_CONTACT."</td>
		<td class='even'>$contact</td>
	</tr>
	<tr>
		<td class='head'>"._PICAL_TH_DESCRIPTION."</td>
		<td class='even'>$description</td>
	</tr>
	<tr>
		<td class='head'>"._PICAL_TH_CATEGORIES."</td>
		<td class='even'>$cat_titles4show</td>
	</tr>
	<tr>
		<td class='head'>"._PICAL_TH_SUBMITTER."</td>
		<td class='even'>$submitter_info</td>
	</tr>
	<tr>
		<td class='head'>"._PICAL_TH_CLASS."</td>
		<td class='even'>$class_status</td>
	</tr>
	<tr>
		<td class='head'>"._PICAL_TH_RRULE."</td>
		<td class='even'>$rrule</td>
	</tr>
	".($this->isadmin?"<tr>
		<td class='head'>"._PICAL_TH_ADMISSIONSTATUS."</td>
		<td class='even'>$admission_status</td>
	</tr>":"")."
	<tr>
		<td class='head'>"._PICAL_TH_LASTMODIFIED."</td>
		<td class='even'>$last_modified</td>
	</tr>
	<tr>
		<td></td>
		<td align='center'>
			<div style='float:left; margin: 2px;'>$edit_button</div>
			<div style='float:left; margin: 2px;'>$delete_button</div>
			<div style='float:left; margin: 2px;'>$ics_output_button</div>
		</td>
	</tr>
	<tr>
		<td><img src='$this->images_url/spacer.gif' alt='' width='150' height='4' /></td>		<td width='100%'></td>
	</tr>
	<tr>
		<td width='100%' align='right' colspan='2'>".PICAL_COPYRIGHT."</td>
	</tr>
	</table>\n" ;

	// for meta discription // naao
	$this->event->start_datetime_str = $start_datetime_str ;
	$this->event->end_datetime_str = $end_datetime_str ;

	return $ret ;
}



// �X�P�W���[���ҏW�p��ʕ\���p�������Ԃ�
function get_schedule_edit_html( )
{
	// $PHP_SELF = $_SERVER['SCRIPT_NAME'] ;
	$editable = $this->editable ;
	$deletable = $this->deletable ;
	$smode = empty( $_GET['smode'] ) ? 'Monthly' : preg_replace('/[^a-zA-Z0-9_-]/','',$_GET['smode']) ;

	// �ύX�̏ꍇ�A�o�^�σX�P�W���[�����擾
	if( ! empty( $_GET[ 'event_id' ] ) ) {

		if( ! $this->editable ) die( "Not allowed" ) ;

		$event_id = intval( $_GET[ 'event_id' ] ) ;
		$yrs = mysql_query( "SELECT * FROM $this->table WHERE id='$event_id'" , $this->conn ) ;
		if( mysql_num_rows( $yrs ) < 1 ) die( _PICAL_ERR_INVALID_EVENT_ID ) ;
		$event = mysql_fetch_object( $yrs ) ;

		// ���Ƃ��ƕҏW�E�폜�\�̐ݒ�ł��A�{������uid�ƃ��R�[�h��uid��
		// ��v�����A���AAdmin���[�h�łȂ����́A�ҏW�E�폜�s�Ƃ���
		if( $event->uid != $this->user_id && ! $this->isadmin ) {
			$editable = false ;
			$deletable = false ;
		}

		$description = $this->textarea_sanitizer_for_edit( $event->description ) ;
		$summary = $this->text_sanitizer_for_edit( $event->summary ) ;
		$location = $this->text_sanitizer_for_edit( $event->location ) ;
		$contact = $this->text_sanitizer_for_edit( $event->contact ) ;
		$categories = $event->categories ;
		if( $event->class == 'PRIVATE' ) {
			$class_private = "checked='checked'" ;
			$class_public = '' ;
			$select_private_disabled = '' ;
		} else {
			$class_private = '' ;
			$class_public = "checked='checked'" ;
			$select_private_disabled = "disabled='disabled'" ;
		}
		$groupid = $event->groupid ;
		$rrule = $event->rrule ;
		$admission_status = $event->admission ? _PICAL_MB_EVENT_ADMITTED : _PICAL_MB_EVENT_NEEDADMIT ;
		$update_button = $editable ? "<input name='update' type='submit' value='"._PICAL_BTN_SUBMITCHANGES."' />" : "" ;
		$insert_button = "<input name='saveas' type='submit' value='"._PICAL_BTN_SAVEAS."' onclick='return confirm(\""._PICAL_CNFM_SAVEAS_YN."\")' />" ;
		$delete_button = $deletable ? "<input name='delete' type='submit' value='"._PICAL_BTN_DELETE."' onclick='return confirm(\""._PICAL_CNFM_DELETE_YN."\")' />" : "" ;
		$tz_options = $this->get_tz_options( $event->event_tz ) ;
		$poster_tz = $event->poster_tz ;

		// ���t�E���ԕ\���̏���
		if( $event->allday ) {
			// �S���C�x���g�i�����v�Z�Ȃ��j
			$select_timezone_disabled = "disabled='disabled'" ;
			$allday_checkbox = "checked='checked'" ;
			$allday_select = "disabled='disabled'" ;
			$allday_bit1 = ( $event->allday & 2 ) ? "checked='checked'" : "" ;
			$allday_bit2 = ( $event->allday & 4 ) ? "checked='checked'" : "" ;
			$allday_bit3 = ( $event->allday & 8 ) ? "checked='checked'" : "" ;
			$allday_bit4 = ( $event->allday & 16 ) ? "checked='checked'" : "" ;
			if( isset( $event->start_date ) ) {
				$start_ymd = $start_long_ymdn = $event->start_date ;
			} else {
				$start_ymd = date( "Y-m-d" , $event->start ) ;
				$start_long_ymdn = $this->get_long_ymdn( $event->start ) ;
			}
			$start_hour = 0 ;
			$start_min = 0 ;
			if( isset( $event->end_date ) ) {
				$end_ymd = $end_long_ymdn = $event->end_date ;
			} else {
				$end_ymd = date( "Y-m-d" , $event->end - 300 ) ;
				$end_long_ymdn = $this->get_long_ymdn( $event->end - 300 ) ;
			}
			$end_hour = 23 ;
			$end_min = 55 ;
		} else {
			// �ʏ�C�x���g�ievent_tz �ł̎��ԕ\���j
			$select_timezone_disabled = "" ;
			$tzoffset_s2e = intval( ( $event->event_tz - $this->server_TZ ) * 3600 ) ;
			$event->start += $tzoffset_s2e ;
			$event->end += $tzoffset_s2e ;
			$allday_checkbox = "" ;
			$allday_select = "" ;
			$allday_bit1 = $allday_bit2 = $allday_bit3 = $allday_bit4 = "" ;
			$start_ymd = date( "Y-m-d" , $event->start ) ;
			$start_long_ymdn = $this->get_long_ymdn( $event->start ) ;
			$start_hour = date( "H" , $event->start ) ;
			$start_min = date( "i" , $event->start ) ;
			$end_ymd = date( "Y-m-d" , $event->end ) ;
			$end_long_ymdn = $this->get_long_ymdn( $event->end ) ;
			$end_hour = date( "H" , $event->end ) ;
			$end_min = date( "i" , $event->end ) ;
		}

	// �V�K�o�^�̏ꍇ
	} else {

		if( ! $this->insertable ) die( "Not allowed" ) ;

		$event_id = 0 ;

		$editable = true ;
		$summary = '' ;
		$select_timezone_disabled = "" ;
		$location = '' ;
		$contact = '' ;
		$class_private = '' ;
		$class_public = "checked='checked'" ;
		$select_private_disabled = "disabled='disabled'" ;
		$groupid = 0 ;
		$rrule = '' ;
		$description = '' ;
		$categories = $this->now_cid > 0 ? sprintf( "%05d," , $this->now_cid ) : '' ;
		$start_ymd = $end_ymd = $this->caldate ;
		$start_long_ymdn = $end_long_ymdn = $this->get_long_ymdn( $this->unixtime ) ;
		$start_hour = 9 ;
		$start_min = 0 ;
		$end_hour = 17 ;
		$end_min = 0 ;
		$admission_status = _PICAL_MB_EVENT_NOTREGISTER ;
		$update_button = '' ;
		$insert_button = "<input name='insert' type='submit' value='"._PICAL_BTN_NEWINSERTED."' />" ;
		$delete_button = '' ;
		$allday_checkbox = $allday_select = "" ;
		$allday_bit1 = $allday_bit2 = $allday_bit3 = $allday_bit4 = "" ;
		$tz_options = $this->get_tz_options( $this->user_TZ ) ;
		$poster_tz = $this->user_TZ ;
	}

	// Start Date
	$textbox_start_date = $this->get_formtextdateselect( 'StartDate' , $start_ymd , $start_long_ymdn ) ;

	// Start Hour
	$select_start_hour = "<select name='StartHour' $allday_select>\n" ;
	$select_start_hour .= $this->get_options_for_hour( $start_hour ) ;
	$select_start_hour .= "</select>" ;

	// Start Minutes
	$select_start_min = "<select name='StartMin' $allday_select>\n" ;
	for( $m = 0 ; $m < 60 ; $m += 5 ) {
		if( $m == $start_min ) $select_start_min .= "<option value='$m' selected='selected'>" . sprintf( "%02d" , $m ) . "</option>\n" ;
		else $select_start_min .= "<option value='$m'>" . sprintf( "%02d" , $m ) . "</option>\n" ;
	}
	$select_start_min .= "</select>" ;

	// End Date
	$textbox_end_date = $this->get_formtextdateselect( 'EndDate' , $end_ymd , $end_long_ymdn ) ;

	// End Hour
	$select_end_hour = "<select name='EndHour' $allday_select>\n" ;
	$select_end_hour .= $this->get_options_for_hour( $end_hour ) ;
	$select_end_hour .= "</select>" ;

	// End Minutes
	$select_end_min = "<select name='EndMin' $allday_select>\n" ;
	for( $m = 0 ; $m < 60 ; $m += 5 ) {
		if( $m == $end_min ) $select_end_min .= "<option value='$m' selected='selected'>" . sprintf( "%02d" , $m ) . "</option>\n" ;
		else $select_end_min .= "<option value='$m'>" . sprintf( "%02d" , $m ) . "</option>\n" ;
	}
	$select_end_min .= "</select>" ;

	// Checkbox for selecting Categories
	$category_checkboxes = '' ;
	foreach( $this->categories as $cid => $cat ) {
		$cid4sql = sprintf( "%05d," , $cid ) ;
		$cat_title4show = $this->text_sanitizer_for_show( $cat->cat_title ) ;
		if( $cat->cat_depth < 2 ) {
			$category_checkboxes .= "<div style='float:left; margin:2px;'>\n" ;
		}
		$category_checkboxes .= str_repeat( '-' , $cat->cat_depth - 1 ) . "<input type='checkbox' name='cids[]' value='$cid' ".(strstr($categories,$cid4sql)?"checked='checked'":"")." />$cat_title4show<br />\n" ;
	}
	$category_checkboxes = substr( str_replace( '<div' , '</div><div' , $category_checkboxes ) , 6 ) . "</div>\n" ;

	// target for "class = PRIVATE"
	$select_private = "<select name='groupid' $select_private_disabled>\n<option value='0'>"._PICAL_OPT_PRIVATEMYSELF."</option>\n" ;
	foreach( $this->groups as $sys_gid => $gname ) {
		$option_desc = sprintf( _PICAL_OPT_PRIVATEGROUP , $gname ) ;
		if( $sys_gid == $groupid ) $select_private .= "<option value='$sys_gid' selected='selected'>$option_desc</option>\n" ;
		else $select_private .= "<option value='$sys_gid'>$option_desc</option>\n" ;
	}
	$select_private .= "</select>" ;

	// XOOPS�p���ǂ����ł̏�������
	if( defined( 'XOOPS_ROOT_PATH' ) ) {

		// DHTML�e�L�X�g�G���A�̏���
		include_once( XOOPS_ROOT_PATH . "/include/xoopscodes.php" ) ;
		ob_start();
		$GLOBALS["description_text"] = $description;
		xoopsCodeTarea("description_text",50,6);
		$description_textarea = ob_get_contents();
		ob_end_clean();

	} else {
		// XOOPS�ȊO�ł́A�P�Ȃ�v���[��textare
		$description_textarea = "<textarea name='description' cols='50' rows='6' wrap='soft'>$description</textarea>" ;
	}

	// FORM DISPLAY
	$ret = "
<h2>"._PICAL_MB_TITLE_EVENTINFO." <small>-"._PICAL_MB_SUBTITLE_EVENTEDIT."-</small></h2>
<form action='index.php' method='post' name='MainForm'>
	".$GLOBALS['xoopsGTicket']->getTicketHtml( __LINE__ )."
	<input type='hidden' name='caldate' value='$this->caldate' />
	<input type='hidden' name='event_id' value='$event_id' />
	<input type='hidden' name='last_smode' value='$smode' />
	<input type='hidden' name='last_caldate' value='$this->caldate' />
	<input type='hidden' name='poster_tz' value='$poster_tz' />
	<table border='0' cellpadding='0' cellspacing='2'>
	<tr>
		<td class='head'>"._PICAL_TH_SUMMARY."</td>
		<td class='even'><input type='text' name='summary' size='60' maxlength='250' value='$summary' /></td>
	</tr>
	<tr>
		<td class='head'>"._PICAL_TH_TIMEZONE."</td>
		<td class='even'><select name='event_tz' $select_timezone_disabled>$tz_options</select></td>
	</tr>
	<tr>
		<td class='head'>"._PICAL_TH_STARTDATETIME."</td>
		<td class='even'>
			$textbox_start_date &nbsp;
			{$select_start_hour} {$select_start_min}"._PICAL_MB_MINUTE_SUF."
</select>
		</td>
	</tr>
	<tr>
		<td class='head'>"._PICAL_TH_ENDDATETIME."</td>
		<td class='even'>
			$textbox_end_date &nbsp; 
			{$select_end_hour} {$select_end_min}"._PICAL_MB_MINUTE_SUF."
		</td>
	</tr>
	<tr>
		<td class='head'>"._PICAL_TH_ALLDAYOPTIONS."</td>
		<td class='even'>
			<fieldset>
				<legend class='blockTitle'><input type='checkbox' name='allday' value='1' $allday_checkbox onClick='document.MainForm.event_tz.disabled=document.MainForm.StartHour.disabled=document.MainForm.StartMin.disabled=document.MainForm.EndHour.disabled=document.MainForm.EndMin.disabled=this.checked' />"._PICAL_MB_ALLDAY_EVENT."</legend>
				<input type='checkbox' name='allday_bits[]' value='1' {$allday_bit1} />"._PICAL_MB_LONG_EVENT." &nbsp;  <input type='checkbox' name='allday_bits[]' value='2' {$allday_bit2} />"._PICAL_MB_LONG_SPECIALDAY." &nbsp;  <!-- <input type='checkbox' name='allday_bits[]' value='3' {$allday_bit3} />rsv3 &nbsp;  <input type='checkbox' name='allday_bits[]' value='4' {$allday_bit4} />rsv4 -->
			</fieldset>
		</td>
	</tr>
	<tr>
		<td class='head'>"._PICAL_TH_LOCATION."</td>
		<td class='even'><input type='text' name='location' size='40' maxlength='250' value='$location' /></td>
	</tr>
	<tr>
		<td class='head'>"._PICAL_TH_CONTACT."</td>
		<td class='even'><input type='text' name='contact' size='50' maxlength='250' value='$contact' /></td>
	</tr>
	<tr>
		<td class='head'>"._PICAL_TH_DESCRIPTION."</td>
		<td class='even'>$description_textarea</td>
	</tr>
	<tr>
		<td class='head'>"._PICAL_TH_CATEGORIES."</td>
		<td class='even'>$category_checkboxes</td>
	</tr>
	<tr>
		<td class='head'>"._PICAL_TH_CLASS."</td>
		<td class='even'><input type='radio' name='class' value='PUBLIC' $class_public onClick='document.MainForm.groupid.disabled=true' />"._PICAL_MB_PUBLIC." &nbsp;  &nbsp; <input type='radio' name='class' value='PRIVATE' $class_private onClick='document.MainForm.groupid.disabled=false' />"._PICAL_MB_PRIVATE.sprintf( _PICAL_MB_PRIVATETARGET , $select_private )."</td>
	</tr>
	<tr>
		<td class='head'>"._PICAL_TH_RRULE."</td>
		<td class='even'>" . $this->rrule_to_form( $rrule , $end_ymd ) . "</td>
	</tr>
	<tr>
		<td class='head'>"._PICAL_TH_ADMISSIONSTATUS."</td>
		<td class='even'>$admission_status</td>
	</tr>\n" ;

	if( $editable ) {
	$ret .= "
	<tr>
		<td style='text-align:center' colspan='2'>
			<input name='reset' type='reset' value='"._PICAL_BTN_RESET."' />
			$update_button
			$insert_button
			$delete_button
		</td>
	</tr>\n" ;
	}

	$ret .= "
	<tr>
		<td><img src='$this->images_url/spacer.gif' alt='' width='150' height='4' /></td>		<td width='100%'></td>
	</tr>
	<tr>
		<td width='100%' align='right' colspan='2'>".PICAL_COPYRIGHT."</td>
	</tr>
	</table>
</form>
\n" ;

	return $ret ;
}




// �X�P�W���[���̍X�V����ѐV�K�o�^
function update_schedule( $set_sql_append = '' , $whr_sql_append = '' , $notify_callback = null )
{
	// debug���[�h�� Location �������Ȃ��Ȃ�̂�h��
//	error_reporting( 0 ) ;

	// $_SERVER �ϐ��̎擾
	// $PHP_SELF = $_SERVER['SCRIPT_NAME'] ;

	// summary�̃`�F�b�N�i���L���Ȃ炻�̎|��ǉ��j
	if( $_POST[ 'summary' ] == "" ) $_POST[ 'summary' ] = _PICAL_MB_NOSUBJECT ;

	// ���t�̑O�����i�����ȓ��t�Ȃ�caldate�ɃZ�b�g�j
	list( $start , $start_date , $use_default ) = $this->parse_posted_date( $this->mb_convert_kana( $_POST[ 'StartDate' ] , "a" ) , $this->unixtime ) ;
	list( $end , $end_date , $use_default ) = $this->parse_posted_date( $this->mb_convert_kana( $_POST[ 'EndDate' ] , "a" ) , $this->unixtime ) ;

	// allday �����̃r�b�g�𗧂Ă�
	$allday = 1 ;
	if( isset( $_POST[ 'allday_bits' ] ) ) {
		$bits = $_POST[ 'allday_bits' ] ;
		if( is_array( $bits ) ) foreach( $bits as $bit ) {
			if( $bit > 0 && $bit < 8 ) {
				$allday += pow( 2 , intval( $bit ) ) ;
			}
		}
	}

	if( $start_date || $end_date ) {
		// 1970�ȑO�A2038�N�ȍ~�̓��t������񂾓���ȑS���C�x���g
		if( $start_date ) $date_append = ", start_date='$start_date'" ;
		else $date_append = ", start_date=null" ;
		if( $end_date ) $date_append .= ", end_date='$end_date'" ;
		else {
			$date_append .= ", end_date=null" ;
			$end += 86400 ;
		}
		$set_sql_date = "start='$start', end='$end', allday='$allday' $date_append" ;
		$allday_flag = true ;
	} else if( ! empty( $_POST[ 'allday' ] ) ) {
		// �S���C�x���g�i�����v�Z�Ȃ��j
		if( $start > $end ) list( $start , $end ) = array( $end , $start ) ;
		$end += 86400 ;		// �I�����Ԃ́A�I����������0:00���w��
		$set_sql_date = "start='$start', end='$end', allday='$allday', start_date=null, end_date=null" ;
		$allday_flag = true ;
	} else {
		// �ʏ�C�x���g�i�����v�Z����j

		// Timezone �̏����i�����̂݁A�C�x���g���Ԃ���T�[�o���Ԃւ̕ϊ��j
		if( ! isset( $_POST['event_tz'] ) ) $_POST['event_tz'] = $this->user_TZ ;
		$tzoffset_e2s = intval( ( $this->server_TZ - $_POST['event_tz'] ) * 3600 ) ;
		//$tzoffset_e2s = intval( date( 'Z' , $start ) - $_POST['event_tz'] * 3600 ) ;

		$start += $_POST[ 'StartHour' ] * 3600 + $_POST[ 'StartMin' ] * 60 + $tzoffset_e2s ;
		$end += $_POST[ 'EndHour' ] * 3600 + $_POST[ 'EndMin' ] * 60 + $tzoffset_e2s ;
		if( $start > $end ) list( $start , $end ) = array( $end , $start ) ;
		$set_sql_date = "start='$start', end='$end', allday=0, start_date=null, end_date=null" ;
		$allday_flag = false ;
	}

	// �T�[�oTZ���L�^
	$set_sql_date .= ",server_tz='$this->server_TZ'" ;

	// description ��XOOPS�p�O���� (�I���ȃc�M�n�M�ŁA���܂�i�D�ǂ��Ȃ����ǁc�c)
	if( ! isset( $_POST[ 'description' ] ) && isset( $_POST[ 'description_text' ] ) ) {
		$_POST[ 'description' ] = $_POST[ 'description_text' ] ;
	}

	// �J�e�S���[�̏���
	$_POST[ 'categories' ] = '' ;
	$cids = is_array( @$_POST['cids'] ) ? $_POST['cids'] : array() ;
	foreach( $cids as $cid ) {
		$cid = intval( $cid ) ;
		while( isset( $this->categories[ $cid ] ) ) {
			$cid4sql = sprintf( "%05d," , $cid ) ;
			if( stristr( $_POST[ 'categories' ] , $cid4sql ) === false ) {
				$_POST[ 'categories' ] .= sprintf( "%05d," , $cid ) ;
			}
			$cid = intval( $this->categories[ $cid ]->pid ) ;
		}
	}

	// RRULE�̎擾
	$rrule = $this->rrule_from_post( $start , $allday_flag ) ;

	// �X�V�ΏۃJ�����ݒ�
	$cols = array( "summary" => "255:J:1" , "location" => "255:J:0" , "contact" => "255:J:0" , "description" => "A:J:0" , "categories" => "255:E:0" , "class" => "255:E:0" , "groupid" => "I:N:0" , "poster_tz" => "F:N:0" , "event_tz" => "F:N:0" ) ;

	$set_str = $this->get_sql_set( $cols ) . ", $set_sql_date $set_sql_append" ;

	// event_id��POST����擾���āA�L�������Ȃ�UPDATE�A�����Ȃ�INSERT�����݂�
	$event_id = intval( $_POST[ 'event_id' ] ) ;
	if( $event_id > 0 ) {
		// �X�V����

		// �܂��́Arrule_pid���擾���A�L����id�Ȃ�A����rrule_pid������
		// �����R�[�h���폜
		$rs = mysql_query( "SELECT rrule_pid FROM $this->table WHERE id='$event_id' $whr_sql_append" , $this->conn ) ;
		if( ! ( $event = mysql_fetch_object( $rs ) ) ) die( "Record Not Exists." ) ;
		if( $event->rrule_pid > 0 ) {
			if( ! mysql_query( "DELETE FROM $this->table WHERE rrule_pid='$event->rrule_pid' AND id<>'$event_id'" , $this->conn ) ) echo mysql_error() ;
		}

		// �Ώۃ��R�[�h��UPDATE����
		if( $rrule != '' ) $set_str .= ", rrule_pid=id" ;
		$sql = "UPDATE $this->table SET $set_str , rrule='$rrule' , sequence=sequence+1 WHERE id='$event_id' $whr_sql_append" ;
		if( ! mysql_query( $sql , $this->conn ) ) echo mysql_error() ;

		// RRULE����A�q���R�[�h��W�J
		if( $rrule != '' ) {
			$this->rrule_extract( $event_id ) ;
		}

		// ���ׂĂ��X�V��A�V�������t�̃J�����_�[�������[�h
		$last_smode = preg_replace( '/[^a-zA-Z0-9_-]/' , '' , @$_POST['last_smode'] ) ;
		//$last_caldate = preg_replace( '/[^a-zA-Z0-9_-]/' , '' , @$_POST['last_caldate'] ) ;
		$new_caldate = $start_date ? $start_date : date( 'Y-n-j' , $start ) ;
		$this->redirect( "smode=$last_smode&caldate=$new_caldate" ) ;

	} else {
		// �V�K�o�^����

		// ����(�e)���R�[�h��INSERT����
		$sql = "INSERT INTO $this->table SET $set_str , rrule='$rrule' , sequence=0" ;
		if( ! mysql_query( $sql , $this->conn ) ) echo mysql_error() ;
		// �e���R�[�h�� unique_id,rrule_pid �̌v�Z�Ɠo�^
		$event_id = mysql_insert_id( $this->conn ) ;
		$unique_id = 'pical060-' . md5( "{$_SERVER['HTTP_HOST']}{$_SERVER['PHP_SELF']}$event_id") ;
		$rrule_pid = $rrule ? $event_id : 0 ;
		mysql_query( "UPDATE $this->table SET unique_id='$unique_id',rrule_pid='$rrule_pid' WHERE id='$event_id'" , $this->conn ) ;

		// RRULE����A�q���R�[�h��W�J
		if( $rrule != '' ) {
			$this->rrule_extract( $event_id ) ;
		}

		if( isset( $notify_callback ) ) $this->$notify_callback( $event_id ) ;

		// ���ׂĂ�o�^��Astart�� �̃J�����_�[�������[�h
		$last_smode = preg_replace( '/[^a-zA-Z0-9_-]/' , '' , @$_POST['last_smode'] ) ;
		$last_caldate = preg_replace( '/[^a-zA-Z0-9_-]/' , '' , @$_POST['last_caldate'] ) ;
		$this->redirect( "smode=$last_smode&caldate=$last_caldate" ) ;

	}
}



// �X�P�W���[���̍폜�iRRULE�t���Ȃ�e���炷�ׂāj
function delete_schedule( $whr_sql_append = '' , $eval_after = null )
{
	// debug���[�h�� Location �������Ȃ��Ȃ�̂�h��
	// error_reporting( 0 ) ;

	if( ! empty( $_POST[ 'event_id' ] ) ) {

		$event_id = intval( $_POST[ 'event_id' ] ) ;

		// �܂��́Arrule_pid���擾���A�L����id�Ȃ�A����rrule_pid������
		// �S���R�[�h���폜
		$rs = mysql_query( "SELECT rrule_pid FROM $this->table WHERE id='$event_id' $whr_sql_append" , $this->conn ) ;
		if( ! ( $event = mysql_fetch_object( $rs ) ) ) die( "Record Not Exists." ) ;
		if( $event->rrule_pid > 0 ) {
			if( ! mysql_query( "DELETE FROM $this->table WHERE rrule_pid='$event->rrule_pid' $whr_sql_append" , $this->conn ) ) echo mysql_error() ;
			// �폜������̒ǉ�������eval�Ŏ󂯂� (XOOPS�ł́A�R�����g�̍폜�j
			if( mysql_affected_rows() > 0 && isset( $eval_after ) ) {
				$id = $event->rrule_pid ;
				eval( $eval_after ) ;
			}
		} else {
			if( ! mysql_query( "DELETE FROM $this->table WHERE id='$event_id' $whr_sql_append" , $this->conn ) ) echo mysql_error() ;
			// �폜������̒ǉ�������eval�Ŏ󂯂� (XOOPS�ł́A�R�����g�̍폜�j
			if( mysql_affected_rows() == 1 && isset( $eval_after ) ) {
				$id = $event_id ;
				eval( $eval_after ) ;
			}
		}

	}
	$last_smode = preg_replace( '/[^a-zA-Z0-9_-]/' , '' , @$_POST['last_smode'] ) ;
	$last_caldate = preg_replace( '/[^a-zA-Z0-9_-]/' , '' , @$_POST['last_caldate'] ) ;
	$this->redirect( "smode=$last_smode&caldate=$last_caldate" ) ;
}



// �X�P�W���[���̈ꌏ�폜�iRRULE�̎q�����R�[�h�j
function delete_schedule_one( $whr_sql_append = '' )
{
	if( ! empty( $_POST[ 'subevent_id' ] ) ) {

		$event_id = intval( $_POST[ 'subevent_id' ] ) ;

		if( ! mysql_query( "DELETE FROM $this->table WHERE id='$event_id' AND rrule_pid <> id $whr_sql_append" , $this->conn ) ) echo mysql_error() ;

	}
	$last_smode = preg_replace( '/[^a-zA-Z0-9_-]/' , '' , @$_POST['last_smode'] ) ;
	$last_caldate = preg_replace( '/[^a-zA-Z0-9_-]/' , '' , @$_POST['last_caldate'] ) ;
	$this->redirect( "smode=$last_smode&caldate=$last_caldate" ) ;
}



/*******************************************************************/
/*        �����֐�                                                 */
/*******************************************************************/

// ���_�C���N�g����
function redirect( $query )
{
	// character white list and black list against 'javascript'
	if( ! preg_match( '/^[a-z0-9=&_-]*$/i' , $query )  || stristr( $query , 'javascript' ) ) {
		header( strtr( "Location: $this->connection://{$_SERVER['HTTP_HOST']}{$_SERVER['PHP_SELF']}" , "\r\n\0" , "   " ) ) ;
		exit ;
	}

	if( headers_sent() ) {
		echo "
			<html>
			<head>
			<title>redirection</title>
			<meta http-equiv='Refresh' content='0; url=?$query' />
			</head>
			<body>
			<p>
				<a href='?$query'>push here if not redirected</a>
			</p>
			</body>
			</html>";
	} else {
		header( strtr( "Location: $this->connection://{$_SERVER['HTTP_HOST']}{$_SERVER['PHP_SELF']}?$query" , "\r\n\0" , "   " ) ) ;
	}
	exit ;
}


// -12.0�`12.0�܂ł̒l���󂯂āA(GMT+HH:MM) �Ƃ����������Ԃ�
function get_tz_for_display( $offset )
{
	return "(GMT" . ( $offset >= 0 ? "+" : "-" ) . sprintf( "%02d:%02d" , abs( $offset ) , abs( $offset ) * 60 % 60 ) . ")" ;
}


// -12.0�`12.0�܂ł�Timzone SELECT�{�b�N�X�pOption�������Ԃ�
function get_tz_options( $selected = 0 )
{
	$tzs = array( '-12','-11','-10','-9','-8','-7','-6',
		'-5','-4','-3.5','-3','-2','-1',
		'0','1','2','3','3.5','4','4.5','5','5.5',
		'6','7','8','9','9.5','10','11','12') ;

	$ret = '' ;
	foreach( $tzs as $tz ) {
		if( $tz == $selected ) $ret .= "\t<option value='$tz' selected='selected'>".$this->get_tz_for_display( $tz )."</option>\n" ;
		else $ret .= "\t<option value='$tz'>".$this->get_tz_for_display( $tz )."</option>\n" ;
	}

	return $ret ;
}


// -12.0�`12.0�܂ł̒l���󂯂āAarray(TZOFFSET,TZID)��Ԃ�
function get_timezone_desc( $tz )
{
	if( $tz == 0 ) {
		$tzoffset = "+0000" ;
		$tzid = "GMT" ;
	} else if( $tz > 0 ) {
		$tzoffset = sprintf( "+%02d%02d" , $tz , $tz * 60 % 60 ) ;
		$tzid = "Etc/GMT-" . sprintf( "%d" , $tz ) ;
	} else {
		$tz = abs( $tz ) ;
		$tzoffset = sprintf( "-%02d%02d" , $tz , $tz * 60 % 60 ) ;
		$tzid = "Etc/GMT+" . sprintf( "%d" , $tz ) ;
	}

	return array( $tzoffset , $tzid ) ;
}


// �J�e�S���[�I�𕶎��{�b�N�X���t�H�[�����ƍ쐬����
function get_categories_selform( $get_target = '' , $smode = null )
{
	if( empty( $this->categories ) ) return '' ;

	if( empty( $smode ) ) $smode = isset( $_GET['smode'] ) ? $_GET['smode'] : '' ;
	$smode = preg_replace('/[^a-zA-Z0-9_-]/','',$smode) ;

	$op = empty( $_GET['op'] ) ? '' : preg_replace('/[^a-zA-Z0-9_-]/','',$_GET['op']) ;

	$ret = "<form action='$get_target' method='GET' style='margin:0px;'>\n" ;
	$ret .= "<input type='hidden' name='caldate' value='$this->caldate' />\n" ;
	$ret .= "<input type='hidden' name='smode' value='$smode' />\n" ;
	$ret .= "<input type='hidden' name='op' value='$op' />\n" ;
	$ret .= "<select name='cid' onchange='submit();'>\n" ;
	$ret .= "\t<option value='0'>"._PICAL_MB_SHOWALLCAT."</option>\n" ;
	foreach( $this->categories as $cid => $cat ) {
		$selected = $this->now_cid == $cid ? "selected='selected'" : "" ;
		$depth_desc = str_repeat( '-' , intval( $cat->cat_depth ) ) ;
		$cat_title4show = $this->text_sanitizer_for_show( $cat->cat_title ) ;
		$ret .= "\t<option value='$cid' $selected>$depth_desc $cat_title4show</option>\n" ;
	}
	$ret .= "</select>\n</form>\n" ;

	return $ret ;
}


// �N�����̃e�L�X�g�{�b�N�X���͂��󂯂āAUnixTimestamp��Ԃ�
function parse_posted_date( $date_desc , $default_unixtime )
{
	if( ! ereg( "^([0-9][0-9]+)[-./]?([0-1]?[0-9])[-./]?([0-3]?[0-9])$" , $date_desc , $regs ) ) {
		$unixtime = $default_unixtime ;
		$use_default = true ;
		$iso_date = '' ;
	} else if( $regs[1] >= 2038 ) {
		// 2038�N�ȍ~�̏ꍇ 2038/1/1 �ɃZ�b�g
		$unixtime = mktime( 0 , 0 , 0 , 1 , 1 , 2038 ) ;
		$use_default = false ;
		$iso_date = "{$regs[1]}-{$regs[2]}-{$regs[3]}" ;
	} else if( $regs[1] <= 1970 ) {
		// 1970�N�ȑO�̏ꍇ 1970/12/31�ɃZ�b�g
		$unixtime = mktime( 0 , 0 , 0 , 12 , 31 , 1970 ) ;
		$use_default = false ;
		$iso_date = "{$regs[1]}-{$regs[2]}-{$regs[3]}" ;
	} else if( ! checkdate( $regs[2] , $regs[3] , $regs[1] ) ) {
		$unixtime = $default_unixtime ;
		$use_default = true ;
		$iso_date = '' ;
	} else {
		$unixtime = mktime( 0 , 0 , 0 , $regs[2] , $regs[3] , $regs[1] ) ;
		$use_default = false ;
		$iso_date = '' ;
	}

	return array( $unixtime , $iso_date , $use_default ) ;
}


// timezone�z����󂯂āARFC2445��VTIMEZONE�p�������Ԃ�
function get_vtimezones_str( $timezones )
{
	if( empty( $timezones ) ) {

		return 
"BEGIN:VTIMEZONE\r
TZID:GMT\r
BEGIN:STANDARD\r
DTSTART:19390101T000000\r
TZOFFSETFROM:+0000\r
TZOFFSETTO:+0000\r
TZNAME:GMT\r
END:STANDARD\r
END:VTIMEZONE\r\n" ;

	} else {

		$ret = "" ;
		foreach( $timezones as $tz => $dummy ) {

			list( $for_tzoffset , $for_tzid ) = $this->get_timezone_desc( $tz ) ;

			$ret .= 
"BEGIN:VTIMEZONE\r
TZID:$for_tzid\r
BEGIN:STANDARD\r
DTSTART:19390101T000000\r
TZOFFSETFROM:$for_tzoffset\r
TZOFFSETTO:$for_tzoffset\r
TZNAME:$for_tzid\r
END:STANDARD\r
END:VTIMEZONE\r\n" ;

		}
		return $ret ;
	}
}


// �A�z�z��������Ɏ��A$_POST����INSERT,UPDATE�p��SET���𐶐�����N���X�֐�
function get_sql_set( $cols )
{
	$ret = "" ;

	foreach( $cols as $col => $types ) {

		list( $field , $lang , $essential ) = explode( ':' , $types ) ;

		// ����`�Ȃ�''�ƌ��Ȃ�
		if( ! isset( $_POST[ $col ] ) ) $data = '' ;
		else if( get_magic_quotes_gpc() ) $data = stripslashes( $_POST[ $col ] ) ;
		else $data = $_POST[ $col ] ;

		// �K�{�t�B�[���h�̃`�F�b�N
		if( $essential && $data === '' ) {
			die( sprintf( _PICAL_ERR_LACKINDISPITEM , $col ) ) ;
		}

		// ����E�����Ȃǂ̕ʂɂ�鏈��
		switch( $lang ) {
			case 'N' :	// ���l (������ , �����)
				$data = intval( str_replace( "," , "" , $data ) ) ;
				break ;
			case 'J' :	// ���{��e�L�X�g (���p�J�i���S�p����)
				$data = $this->mb_convert_kana( $data , "KV" ) ;
				break ;
			case 'E' :	// ���p�p�����̂�
				$data = $this->mb_convert_kana( $data , "as" ) ;
				break ;
		}

		// �t�B�[���h�̌^�ɂ�鏈��
		switch( $field ) {
			case 'A' :	// textarea
				$ret .= "$col='".addslashes($data)."'," ;
				break ;
			case 'I' :	// integer
				$data = intval( $data ) ;
				$ret .= "$col='$data'," ;
				break ;
			case 'F' :	// float
				$data = doubleval( $data ) ;
				$ret .= "$col='$data'," ;
				break ;
			default :	// varchar(�f�t�H���g)�͐��l�ɂ�镶�����w��
				if( $field < 1 ) $field = 255 ;
				$data = mb_strcut( $data , 0 , $field ) ;
				$ret .= "$col='".addslashes($data)."'," ;
		}
	}

	// �Ō�� , ���폜
	$ret = substr( $ret , 0 , -1 ) ;

	return $ret ;
}



// unixtimestamp����A���݂̌���ŕ\�����ꂽ�����\�L�� YMDN �𓾂�
function get_long_ymdn( $time )
{
	return sprintf(
		_PICAL_FMT_YMDN , // format
		date( 'Y' , $time ) , // Y
		$this->month_long_names[ date( 'n' , $time ) ] , // M
		$this->date_long_names[ date( 'j' , $time ) ] , // D
		$this->week_long_names[ date( 'w' , $time ) ] // N
	) ;
}



// unixtimestamp����A���݂̌���ŕ\�����ꂽ�W�����\�L�� MD �𓾂�
function get_middle_md( $time )
{
	return sprintf(
		_PICAL_FMT_MD , // format
		$this->month_middle_names[ date( 'n' , $time ) ] , // M
		$this->date_short_names[ date( 'j' , $time ) ] // D
	) ;
}



// unixtimestamp����A���݂̌���ŕ\�����ꂽ DHI �𓾂�
function get_middle_dhi( $time , $is_over24 = false )
{
	$hour_offset = $is_over24 ? 24 : 0 ;

	$hour4disp = $this->use24 ? $this->hour_names_24[ date( 'G' , $time ) + $hour_offset ] : $this->hour_names_12[ date( 'G' , $time ) + $hour_offset ] ;

	return sprintf(
		_PICAL_FMT_DHI ,
		$this->date_short_names[ date( 'j' , $time ) ] , // D
		$hour4disp , // H
		date( _PICAL_DTFMT_MINUTE , $time ) // I
	) ;
}



// unixtimestamp����A���݂̌���ŕ\�����ꂽ HI �𓾂�
function get_middle_hi( $time , $is_over24 = false )
{
	$hour_offset = $is_over24 ? 24 : 0 ;

	$hour4disp = $this->use24 ? $this->hour_names_24[ date( 'G' , $time ) + $hour_offset ] : $this->hour_names_12[ date( 'G' , $time ) + $hour_offset ] ;

	return sprintf(
		_PICAL_FMT_HI ,
		$hour4disp , // H
		date( _PICAL_DTFMT_MINUTE , $time ) // I
	) ;
}



// Make <option>s for selecting "HOUR" (default_hour must be 0-23)
function get_options_for_hour( $default_hour = 0 )
{
	$ret = '' ;
	for( $h = 0 ; $h < 24 ; $h ++ ) {
		$ret .= $h == $default_hour ? "<option value='$h' selected='selected'>" : "<option value='$h'>" ;
		$ret .= $this->use24 ? $this->hour_names_24[ $h ] : $this->hour_names_12[ $h ] ;
		$ret .= "</option>\n" ;
	}
	return $ret ;
}



// unixtimestamp����A���鎞��(timestamp�`��)�ȍ~�̗\������̕�����𓾂�
function get_coming_time_description( $start , $now , $admission = true )
{
	// ���F�̗L���ɂ���ăh�b�gGIF��ւ���
	if( $admission ) $dot = "" ;
	else $dot = "<img border='0' src='$this->images_url/dot_notadmit.gif' />" ;

	if( $start >= $now && $start - $now < 86400 ) {
		// 24���Ԉȓ��̃C�x���g
		if( ! $dot ) $dot = "<img border='0' src='$this->images_url/dot_today.gif' />" ;
		$ret = "$dot <b>" . $this->get_middle_hi( $start ) . "</b>"._PICAL_MB_TIMESEPARATOR ;
	} else if( $start < $now ) {
		// ���łɊJ�n���ꂽ�C�x���g
		if( ! $dot ) $dot = "<img border='0' src='$this->images_url/dot_started.gif' />" ;
		$ret = "$dot "._PICAL_MB_CONTINUING ;
	} else {
		// �����ȍ~�ɊJ�n�ɂȂ�C�x���g
		if( ! $dot ) $dot = "<img border='0' src='$this->images_url/dot_future.gif' />" ;
//		$ret = "$dot " . date( "n/j H:i" , $start ) . _PICAL_MB_TIMESEPARATOR ;
		$ret = "$dot " . $this->get_middle_md( $start ) . " " . $this->get_middle_hi( $start ) . _PICAL_MB_TIMESEPARATOR ;
	}

	return $ret ;
}



// �Q��unixtimestamp����A�����(Y-n-j�`��)�̗\�莞�Ԃ̕�����𓾂�i���ɃS�~�j
function get_todays_time_description( $start , $end , $ynj , $justify = true , $admission = true , $is_start_date = null , $is_end_date = null , $border_for_2400 = null )
{
	if( ! isset( $is_start_date ) ) $is_start_date = ( date( "Y-n-j" , $start ) == $ynj ) ;
	if( ! isset( $is_end_date ) ) $is_end_date = ( date( "Y-n-j" , $end ) == $ynj ) ;
	if( ! isset( $border_for_2400 ) ) $this->unixtime - intval( ( $this->user_TZ - $this->server_TZ ) * 3600 ) + 86400 ;

	// $day_start �w�肪���鎞�́A24:00�ȍ~�̏���
	if( $is_start_date && $start > $border_for_2400 ) {
		$start_desc = $this->get_middle_hi( $start , true ) ;
	} else $start_desc = $this->get_middle_hi( $start ) ;

	if( $is_end_date && $end > $border_for_2400 ) {
		$end_desc = $this->get_middle_hi( $end , true ) ;
	} else $end_desc = $this->get_middle_hi( $end ) ;

	$stuffing = $justify ? '     ' : '' ;

	// �\�莞�Ԏw��̗L���E���F�̗L���ɂ���ăh�b�gGIF��ւ���
	if( $admission ) {
		if( $is_start_date ) $dot = "<img border='0' src='$this->images_url/dot_startday.gif' />" ;
		else if( $is_end_date ) $dot = "<img border='0' src='$this->images_url/dot_endday.gif' />" ;
		else $dot = "<img border='0' src='$this->images_url/dot_interimday.gif' />" ;
	} else $dot = "<img border='0' src='$this->images_url/dot_notadmit.gif' />" ;

	if( $is_start_date ) {
		if( $is_end_date ) $ret = "$dot {$start_desc}"._PICAL_MB_TIMESEPARATOR."{$end_desc}" ;
		else $ret = "$dot {$start_desc}"._PICAL_MB_TIMESEPARATOR."{$stuffing}" ;
	} else {
		if( $is_end_date ) $ret = "$dot {$stuffing}"._PICAL_MB_TIMESEPARATOR."{$end_desc}" ;
		else $ret = "$dot "._PICAL_MB_CONTINUING ;
	}

	return $ret ;
}


// $event�N�G�����ʂ���A������̗\�莞�Ԃ̕�����𓾂�i�ʏ�C�x���g�̂݁j
function get_time_desc_for_a_day( $event , $tzoffset , $border_for_2400 , $justify = true , $admission = true )
{
	$start = $event->start + $tzoffset ;
	$end = $event->end + $tzoffset ;

	// $day_start �w�肪���鎞�́A24:00�ȍ~�̏���
	if( $event->is_start_date && $event->start >= $border_for_2400 ) {
		$start_desc = $this->get_middle_hi( $start , true ) ;
	} else $start_desc = $this->get_middle_hi( $start ) ;

	if( $event->is_end_date && $event->end >= $border_for_2400 ) {
		$end_desc = $this->get_middle_hi( $end , true ) ;
	} else $end_desc = $this->get_middle_hi( $end ) ;

	$stuffing = $justify ? '     ' : '' ;

	// �\�莞�Ԏw��̗L���E���F�̗L���ɂ���ăh�b�gGIF��ւ���
	if( $admission ) {
		if( $event->is_start_date ) $dot = "<img border='0' src='$this->images_url/dot_startday.gif' />" ;
		else if( $event->is_end_date ) $dot = "<img border='0' src='$this->images_url/dot_endday.gif' />" ;
		else $dot = "<img border='0' src='$this->images_url/dot_interimday.gif' />" ;
	} else $dot = "<img border='0' src='$this->images_url/dot_notadmit.gif' />" ;

	if( $event->is_start_date ) {
		if( $event->is_end_date ) $ret = "$dot {$start_desc}"._PICAL_MB_TIMESEPARATOR."{$end_desc}" ;
		else $ret = "$dot {$start_desc}"._PICAL_MB_TIMESEPARATOR."{$stuffing}" ;
	} else {
		if( $event->is_end_date ) $ret = "$dot {$stuffing}"._PICAL_MB_TIMESEPARATOR."{$end_desc}" ;
		else $ret = "$dot "._PICAL_MB_CONTINUING ;
	}

	return $ret ;
}


// ���t���̓{�b�N�X�̊֐� (JavaScript�œ��͂���ۂ�Override�Ώ�)

function get_formtextdateselect( $name , $value )
{
	return "<input type='text' name='$name' size='12' value='$value' style='ime-mode:disabled' />" ;
}



// $this->images_url���ɂ���style.css��ǂݍ��݁A�T�j�^�C�Y���Ĉ����n��
function get_embed_css( )
{
	$css_filename = "$this->images_path/style.css" ;
	if( ! is_readable( $css_filename ) ) return "" ;
	else return strip_tags( join( "" , file( $css_filename ) ) ) ;
}



// ���e�҂̕\���������Ԃ� (Override�Ώ�)
function get_submitter_info( $uid )
{
	return '' ;
}



// �J�e�S���֌W��WHERE�p�����𐶐�
function get_where_about_categories()
{
	if( $this->isadmin ) {
		if( empty( $this->now_cid ) ) {
			// �{���҂��Ǘ��҂�$cid�w�肪�Ȃ���Ώ��True
			return "1" ;
		} else {
			// �{���҂��Ǘ��҂�$cid�w�肪����΁A��������LIKE�w��
			return "categories LIKE '%".sprintf("%05d,",$this->now_cid)."%'" ;
		}
	} else {
		if( empty( $this->now_cid ) ) {
			// �{���҂��Ǘ��҈ȊO��$cid�w�肪�Ȃ���΁ACAT2GROUP�ɂ�鐧��
			$limit_from_perm = "categories='' OR " ;
			foreach( $this->categories as $cid => $cat ) {
				$limit_from_perm .= "categories LIKE '%".sprintf("%05d,",$cid)."%' OR " ;
			}
			$limit_from_perm = substr( $limit_from_perm , 0 , -3 ) ;
			return $limit_from_perm ;
		} else {
			// �{���҂��Ǘ��҈ȊO��$cid�w�肪����΁A�����`�F�b�N����$cid�w��
			if( isset( $this->categories[ $this->now_cid ] ) ) {
				return "categories LIKE '%".sprintf("%05d,",$this->now_cid)."%'" ;
			} else {
				// �w�肳�ꂽcid�������ɂȂ�
				return '0' ;
			}
		}
	}
}



// CLASS(���J�E����J)�֌W��WHERE�p�����𐶐�
function get_where_about_class()
{
	if( $this->isadmin ) {
		// �{���҂��Ǘ��҂Ȃ���True
		return "1" ;
	} else if( $this->user_id <= 0 ) {
		// �{���҂��Q�X�g�Ȃ���J(PUBLIC)���R�[�h�̂�
		return "class='PUBLIC'" ;
	} else {
		// �ʏ탆�[�U�Ȃ�APUBLIC���R�[�h���A���[�UID����v���郌�R�[�h�A�܂��́A�������Ă���O���[�vID�̂����̈�����R�[�h�̃O���[�vID�ƈ�v���郌�R�[�h
		$ids = ' ' ;
		foreach( $this->groups as $id => $name ) {
			$ids .= "$id," ;
		}
		$ids = substr( $ids , 0 , -1 ) ;
		if( intval( $ids ) == 0 ) $group_section = '' ;
		else $group_section = "OR groupid IN ($ids)" ;
		return "(class='PUBLIC' OR uid=$this->user_id $group_section)" ;
	}
}



// mb_convert_kana�̏���
function mb_convert_kana( $str , $option )
{
	// convert_kana �̏����́A���{��ł̂ݍs��
	if( $this->language != 'japanese' || ! function_exists( 'mb_convert_kana' ) ) {
		return $str ;
	} else {
		return mb_convert_kana( $str , $option ) ;
	}
}



/*******************************************************************/
/*   �T�j�^�C�Y�֘A�̊֐� (�T�u�N���X���쐬���鎞��Override�Ώ�)   */
/*******************************************************************/

function textarea_sanitizer_for_show( $data )
{
	return nl2br( htmlspecialchars( $data , ENT_QUOTES ) ) ;
}

function text_sanitizer_for_show( $data )
{
	return htmlspecialchars( $data , ENT_QUOTES ) ;
}

function textarea_sanitizer_for_edit( $data )
{
	return htmlspecialchars( $data , ENT_QUOTES ) ;
}

function text_sanitizer_for_edit( $data )
{
	return htmlspecialchars( $data , ENT_QUOTES ) ;
}

function textarea_sanitizer_for_export_ics( $data )
{
	return $data ;
}


/*******************************************************************/
/*        iCalendar �����֐�                                       */
/*******************************************************************/

// iCalendar�`���ł̃o�b�`�o�̓v���b�g�t�H�[���I��p�t�H�[����Ԃ�
// $_POST['ids']�Ŏw��
function output_ics_confirm( $post_target , $target = '_self' )
{
	// POST�Ŏ󂯎����id�z����Aevent_ids�z��Ƃ���POST����
	$hiddens = "" ;
	foreach( $_POST[ 'ids' ] as $id ) {
		$id = intval( $id ) ;
		$hiddens .= "<input type='hidden' name='event_ids[]' value='$id' />\n" ;
	}
	// webcal�����N����
	$webcal_url = str_replace( 'http://' , 'webcal://' , $post_target ) ;
	// �m�F�t�H�[����Ԃ�
	return "
	<div style='text-align:center;width:100%;'>&nbsp;<br /><b>"._PICAL_MB_ICALSELECTPLATFORM."</b><br />&nbsp;</div>
	<table border='0' cellpadding='5' cellspacing='2' width='100%'>
	<tr>
	<td align='right' width='50%'>
	<form action='$post_target?output_ics=1' method='post' target='$target'>
		$hiddens
		<input type='submit' name='do_output' value='"._PICAL_BTN_OUTPUTICS_WIN."' />
	</form>
	</td>
	<td align='left' width='50%'>
	<form action='$webcal_url?output_ics=1' method='post' target='$target'>
		$hiddens
		<input type='submit' name='do_output' value='"._PICAL_BTN_OUTPUTICS_MAC."' />
	</form>
	</td>
	</tr>
	</table><br /><br />\n" ;
}


// iCalendar�`���ł̏o�� (mbstring�K�{)
// �o�͂��ꌏ�݂̂̏ꍇ��$_GET['event_id']�A�z��̏ꍇ��$_POST['event_ids']
function output_ics( )
{
	// $event_id ���w�肳��Ă��Ȃ���ΏI��
	if( empty( $_GET[ 'event_id' ] ) && empty( $_POST[ 'event_ids' ] ) ) die( _PICAL_ERR_INVALID_EVENT_ID ) ;

	// iCalendar�o�͋����Ȃ���ΏI��
	if( ! $this->can_output_ics ) die( _PICAL_ERR_NOPERM_TO_OUTPUTICS ) ;
	if( isset( $_GET[ 'event_id' ] ) ) {
		// $_GET[ 'event_id' ] �ɂ��ꌏ�����̎w��̏ꍇ
		$event_id = intval( $_GET['event_id'] ) ;
		$event_ids = array( $event_id ) ;
		$rs = mysql_query( "SELECT summary AS udtstmp FROM $this->table WHERE id='$event_id'" , $this->conn ) ;
		if( mysql_num_rows( $rs ) < 1 ) die( _PICAL_ERR_INVALID_EVENT_ID ) ;
		$summary = mysql_result( $rs , 0 , 0 ) ;
		// ���� �� X-WR-CALNAME �Ƃ���
		$x_wr_calname = $summary ;
		// �t�@�C�����Ɏg���Ȃ������ȕ����͍��
		if( function_exists( "mb_ereg_replace" ) ) {
			$summary = mb_ereg_replace( '[<>|"?*,:;\\/]' , '' , $summary ) ;
		} else {
			$summary = ereg_replace( '[<>|"?*,:;\\/]' , '' , $summary ) ;
		}
		// �֎~���������������.ics ���t�@�C�����Ƃ��� (�vSJIS�ϊ�)
		$output_filename = mb_convert_encoding( $summary , "SJIS" ) . '.ics' ;
	} else if( is_array( $_POST[ 'event_ids' ] ) ) {
		// $_POST[ 'event_ids' ] �ɂ��z��ɂ��w��̏ꍇ
		$event_ids = array_unique( $_POST[ 'event_ids' ] ) ;
		// events-��������(GMT) �� X-WR-CALNAME �Ƃ���
		$x_wr_calname = 'events-' . gmdate( 'Ymd\THis\Z' ) ;
		// events-��������.ics ���t�@�C�����Ƃ���
		$output_filename = $x_wr_calname . '.ics' ;
	} else die( _PICAL_ERR_INVALID_EVENT_ID ) ;

	// HTTP�w�b�_�o��
	header("Content-type: text/calendar");
	header("Content-Disposition: attachment; filename=$output_filename" );
	header("Expires: 0");
	header("Cache-Control: must-revalidate, post-check=0,pre-check=0");
	header("Pragma: public");

	// iCalendar�w�b�_
	$ical_header = "BEGIN:VCALENDAR\r
CALSCALE:GREGORIAN\r
X-WR-TIMEZONE;VALUE=TEXT:GMT\r
PRODID:PEAK Corporation - piCal -\r
X-WR-CALNAME;VALUE=TEXT:$x_wr_calname\r
VERSION:2.0\r
METHOD:PUBLISH\r\n" ;

	// �J�e�S���[�֘A��WHERE�����擾
	$whr_categories = $this->get_where_about_categories() ;

	// CLASS�֘A��WHERE�����擾
	$whr_class = $this->get_where_about_class() ;

	// �C�x���g���̃��[�v
	$vevents_str = "" ;
	$timezones = array() ;
	foreach( $event_ids as $event_id ) {

		$event_id = intval( $event_id ) ;
		$sql = "SELECT *,UNIX_TIMESTAMP(dtstamp) AS udtstmp,DATE_ADD(end_date,INTERVAL 1 DAY) AS end_date_offseted FROM $this->table WHERE id='$event_id' AND ($whr_categories) AND ($whr_class)" ;
		if( ! $rs = mysql_query( $sql , $this->conn ) ) echo mysql_error() ;
		$event = mysql_fetch_object( $rs ) ;
		if( ! $event ) continue ;

		if( isset( $event->start_date ) ) {
			// 1970�ȑO�A2038�N�ȍ~�̓��t������񂾓���ȑS���C�x���g
			$dtstart = str_replace( '-' , '' , $event->start_date ) ;
			if( isset( $event->end_date_offseted ) ) {
				$dtend = str_replace( '-' , '' , $event->end_date_offseted ) ;
			} else {
				$dtend = date( 'Ymd' , $event->end ) ;
			}
			$dtstart_opt = $dtend_opt = ";VALUE=DATE" ;
		} else if( $event->allday ) {
			// �S���C�x���g�i���������Ȃ��j
			$dtstart = date( 'Ymd' , $event->start ) ;
			if( isset( $event->end_date_offseted ) ) {
				$dtend = str_replace( '-' , '' , $event->end_date_offseted ) ;
			} else {
				$dtend = date( 'Ymd' , $event->end ) ;
			}
			// �J�n�ƏI���������̏ꍇ�́A�I�����P�����ɂ��炷
			if( $dtstart == $dtend ) $dtend = date( 'Ymd' , $event->end + 86400 ) ;
			$dtstart_opt = $dtend_opt = ";VALUE=DATE" ;
		} else {
			if( $event->rrule ) {
				// �ʏ�C�x���g��RRULE������΁A�C�x���gTZ�ŏo��
				$tzoffset = intval( ( $this->server_TZ - $event->event_tz ) * 3600 ) ;
				list( , $tzid ) = $this->get_timezone_desc( $event->event_tz ) ;
				$dtstart = date( 'Ymd\THis' , $event->start - $tzoffset ) ;
				$dtend = date( 'Ymd\THis' , $event->end - $tzoffset ) ;
				$dtstart_opt = $dtend_opt = ";TZID=$tzid" ;
				// ����ɁA����VTIMEZONE���o��
				$timezones[$event->event_tz] = 1 ;
			} else {
				// �ʏ�C�x���g��RRULE��������΁A�T�[�o�̎���������������GMT�\��
				$tzoffset = $this->server_TZ * 3600 ;
				$dtstart = date( 'Ymd\THis\Z' , $event->start - $tzoffset ) ;
				$dtend = date( 'Ymd\THis\Z' , $event->end - $tzoffset ) ;
				$dtstart_opt = $dtend_opt = "" ;
			}
		}

		// DTSTAMP�͏��GMT
		$dtstamp = date( 'Ymd\THis\Z' , $event->udtstmp - $this->server_TZ * 3600 ) ;

		// DESCRIPTION�� folding , \r�폜 ����� \n -> \\n �ϊ�, �T�j�^�C�Y
		// (folding ������) TODO
		$description = str_replace( "\r" , '' , $event->description ) ;
		$description = str_replace( "\n" , '\n' , $description ) ;
		$description = $this->textarea_sanitizer_for_export_ics( $description ) ;

		// �J�e�S���[�̕\��
		$categories = '' ;
		$cids = explode( "," , $event->categories ) ;
		foreach( $cids as $cid ) {
			$cid = intval( $cid ) ;
			if( isset( $this->categories[ $cid ] ) ) $categories .= $this->categories[ $cid ]->cat_title . "," ;
		}
		if( $categories != '' ) $categories = substr( $categories , 0 , -1 ) ;

		// RRULE�s�́ARRULE�̒��g�����鎞�����o��
		$rrule_line = $event->rrule ? "RRULE:{$event->rrule}\r\n" : "" ;

		// �C�x���g�f�[�^�̏o��
		$vevents_str .= "BEGIN:VEVENT\r
DTSTART{$dtstart_opt}:{$dtstart}\r
DTEND{$dtend_opt}:{$dtend}\r
LOCATION:{$event->location}\r
TRANSP:OPAQUE\r
SEQUENCE:{$event->sequence}\r
UID:{$event->unique_id}\r
DTSTAMP:{$dtstamp}\r
CATEGORIES:{$categories}\r
DESCRIPTION:{$description}\r
SUMMARY:{$event->summary}\r
{$rrule_line}PRIORITY:{$event->priority}\r
CLASS:{$event->class}\r
END:VEVENT\r\n" ;

	}

	// VTIMEZONE
	$vtimezones_str = $this->get_vtimezones_str( $timezones ) ;

	// iCalendar�t�b�^
	$ical_footer = "END:VCALENDAR\r\n" ;

	$ical_data = "$ical_header$vtimezones_str$vevents_str$ical_footer" ;

	// mbstring ������ꍇ�̂݁AUTF-8 �ւ̕ϊ�
	if( extension_loaded( 'mbstring' ) ) {
		mb_http_output( "pass" ) ;
		$ical_data = mb_convert_encoding( $ical_data , "UTF-8" ) ;
	}

	echo $ical_data ;

	exit ;
}



function import_ics_via_fopen( $uri , $force_http = true , $user_uri = '' )
{
	if( strlen( $uri ) < 5 ) return "-1:" ;
	$user_uri = empty( $user_uri ) ? '' : $uri ;
	// webcal://* �� connection���w����A���ׂ� http://* �ɓ���
	$uri = str_replace( "webcal://" , "http://" , $uri ) ;

	if( $force_http ) {
		if( substr( $uri , 0 , 7 ) != 'http://' ) $uri = "http://" . $uri ;
	}

	// iCal parser �ɂ�鏈��
	include_once "$this->base_path/class/iCal_parser.php" ;
	$ical = new iCal_parser() ;
	$ical->language = $this->language ;
	$ical->timezone = ( $this->server_TZ >= 0 ? "+" : "-" ) . sprintf( "%02d%02d" , abs( $this->server_TZ ) , abs( $this->server_TZ ) * 60 % 60 ) ;
	list( $ret_code , $message , $filename ) = explode( ":" , $ical->parse( $uri , $user_uri ) , 3 ) ;
	if( $ret_code != 0 ) {
		// �p�[�X���s�Ȃ�-1�ƃG���[���b�Z�[�W��Ԃ�
		return "-1: $message : $filename" ;
	}
	$setsqls = $ical->output_setsqls() ;

	$count = 0 ;
	foreach( $setsqls as $setsql ) {
		$sql = "INSERT INTO $this->table SET $setsql,admission=1,uid=$this->user_id,poster_tz='$this->user_TZ',server_tz='$this->server_TZ'" ;

		if( ! mysql_query( $sql , $this->conn ) ) die( mysql_error() ) ;
		$this->update_record_after_import( mysql_insert_id( $this->conn ) ) ;

		$count ++ ;
	}

	return "$count: $message:" ;
}



function import_ics_via_upload( $userfile )
{
	// ics�t�@�C�����N���C�A���g�}�V������A�b�v���[�h���ēǍ���
	include_once "$this->base_path/class/iCal_parser.php" ;
	$ical = new iCal_parser() ;
	$ical->language = $this->language ;
	$ical->timezone = ( $this->server_TZ >= 0 ? "+" : "-" ) . sprintf( "%02d%02d" , abs( $this->server_TZ ) , abs( $this->server_TZ ) * 60 % 60 ) ;
	list( $ret_code , $message , $filename ) = explode( ":" , $ical->parse( $_FILES[ $userfile ][ 'tmp_name' ] , $_FILES[ $userfile ][ 'name' ] ) , 3 ) ;
	if( $ret_code != 0 ) {
		// �p�[�X���s�Ȃ�-1�ƃG���[���b�Z�[�W��Ԃ�
		return "-1: $message : $filename" ;
	}
	$setsqls = $ical->output_setsqls() ;

	$count = 0 ;
	foreach( $setsqls as $setsql ) {
		$sql = "INSERT INTO $this->table SET $setsql,admission=1,uid=$this->user_id,poster_tz='$this->user_TZ',server_tz='$this->server_TZ'" ;

		if( ! mysql_query( $sql , $this->conn ) ) die( mysql_error() ) ;
		$this->update_record_after_import( mysql_insert_id( $this->conn ) ) ;

		$count ++ ;
	}

	return "$count: $message :" ;
}



// �P���R�[�h��ǂݍ��݌�ɍs������ �irrule�̓W�J�Acategories��cid���Ȃǁj
function update_record_after_import( $event_id )
{
	$rs = mysql_query( "SELECT categories,rrule FROM $this->table WHERE id='$event_id'" , $this->conn ) ;
	$event = mysql_fetch_object( $rs ) ;

	// categories �� cid�� ( '\,' -> ',' ��Outlook�΍�)
	$event->categories = str_replace( '\,' , ',' , $event->categories ) ;
	$cat_names = explode( ',' , $event->categories ) ;
	for( $i = 0 ; $i < sizeof( $cat_names ) ; $i ++ ) {
		$cat_names[ $i ] = trim( $cat_names[ $i ] ) ;
	}
	$categories = '' ;
	foreach( $this->categories as $cid => $cat ) {
		if( in_array( $cat->cat_title , $cat_names ) ) {
			$categories .= sprintf( "%05d," , $cid ) ;
		}
	}

	// rrule_pid �̏���
	$rrule_pid = $event->rrule ? $event_id : 0 ;

	// ���R�[�h�X�V
	mysql_query( "UPDATE $this->table SET categories='$categories',rrule_pid='$rrule_pid' WHERE id='$event_id'" , $this->conn ) ;

	// RRULE����A�q���R�[�h��W�J
	if( $event->rrule != '' ) {
		$this->rrule_extract( $event_id ) ;
	}

	// GIJ TODO category �̎����o�^ class,groupid �̏���
}


/*******************************************************************/
/*        RRULE �����֐�                                           */
/*******************************************************************/

// rrule�����R����ɖ|�󂷂�N���X�֐�
function rrule_to_human_language( $rrule )
{
	$rrule = trim( $rrule ) ;
	if( $rrule == '' ) return '' ;

	// rrule �̊e�v�f��ϐ��ɓW�J
	$rrule = strtoupper( $rrule ) ;
	$rules = split( ';' , $rrule ) ;
	foreach( $rules as $rule ) {
		list( $key , $val ) = explode( '=' , $rule , 2 ) ;
		$key = trim( $key ) ;
		$$key = trim( $val ) ;
	}

	if( empty( $FREQ ) ) $FREQ = 'DAILY' ;
	if( empty( $INTERVAL ) || $INTERVAL <= 0 ) $INTERVAL = 1 ;

	// �p�x�������
	$ret_freq = '' ;
	$ret_day = '' ;
	switch( $FREQ ) {
		case 'DAILY' :
			if( $INTERVAL == 1 ) $ret_freq = _PICAL_RR_EVERYDAY ;
			else $ret_freq = sprintf( _PICAL_RR_PERDAY , $INTERVAL ) ;
			break ;
		case 'WEEKLY' :
			if( empty( $BYDAY ) ) break ;	// BYDAY �K�{
			$ret_day = strtr( $BYDAY , $this->byday2langday_w ) ;
			if( $INTERVAL == 1 ) $ret_freq = _PICAL_RR_EVERYWEEK ;
			else $ret_freq = sprintf( _PICAL_RR_PERWEEK , $INTERVAL ) ;
			break ;
		case 'MONTHLY' :
			if( isset( $BYMONTHDAY ) ) {
				$ret_day = "" ;
				$monthdays = explode( ',' , $BYMONTHDAY ) ;
				foreach( $monthdays as $monthday ) {
					$ret_day .= $this->date_long_names[ $monthday ] . "," ;
				}
				$ret_day = substr( $ret_day , 0 , -1 ) ;
			} else if( isset( $BYDAY ) ) {
				$ret_day = strtr( $BYDAY , $this->byday2langday_m ) ;
			} else {
				break ;		// BYDAY �܂��� BYMONTHDAY �K�{
			}
			if( $INTERVAL == 1 ) $ret_freq = _PICAL_RR_EVERYMONTH ;
			else $ret_freq = sprintf( _PICAL_RR_PERMONTH , $INTERVAL ) ;
			break ;
		case 'YEARLY' :
			$ret_day = "" ;
			if( ! empty( $BYMONTH ) ) {
				$months = explode( ',' , $BYMONTH ) ;
				foreach( $months as $month ) {
					$ret_day .= $this->month_long_names[ $month ] . "," ;
				}
				$ret_day = substr( $ret_day , 0 , -1 ) ;
			}
			if( isset( $BYDAY ) ) {
				$ret_day .= ' ' . strtr( $BYDAY , $this->byday2langday_m ) ;
			}
			if( $INTERVAL == 1 ) $ret_freq = _PICAL_RR_EVERYYEAR ;
			else $ret_freq = sprintf( _PICAL_RR_PERYEAR , $INTERVAL ) ;
			break ;
	}

	// �I���������
	$ret_terminator = '' ;
	// UNTIL �� COUNT �̗��������鎞�� COUNT �D��
	if( isset( $COUNT ) && $COUNT > 0 ) {
		$ret_terminator = sprintf( _PICAL_RR_COUNT , $COUNT ) ;
	} else if( isset( $UNTIL ) ) {
		// UNTIL �́A�S�������ł���Ɩ������Ō��Ȃ�
		$year = substr( $UNTIL , 0 , 4 ) ;
		$month = substr( $UNTIL , 4 , 2 ) ;
		$date = substr( $UNTIL , 6 , 2 ) ;
		$ret_terminator = sprintf( _PICAL_RR_UNTIL , "$year-$month-$date" ) ;
	}

	return "$ret_freq $ret_day $ret_terminator" ;
}



// rrule��ҏW�p�t�H�[���ɓW�J����N���X�֐�
function rrule_to_form( $rrule , $until_init )
{
	// �e�����l�̐ݒ�
	$norrule_checked = '' ;
	$daily_checked = '' ;
	$weekly_checked = '' ;
	$monthly_checked = '' ;
	$yearly_checked = '' ;
	$norrule_checked = '' ;
	$noterm_checked = '' ;
	$count_checked = '' ;
	$until_checked = '' ;
	$daily_interval_init = 1 ;
	$weekly_interval_init = 1 ;
	$monthly_interval_init = 1 ;
	$yearly_interval_init = 1 ;
	$count_init = 1 ;
	$wdays_checked = array( 'SU'=>'' , 'MO'=>'' , 'TU'=>'' , 'WE'=>'' , 'TH'=>'' , 'FR'=>'' , 'SA'=>'' ) ;
	$byday_m_init = '' ;
	$bymonthday_init = '' ;
	$bymonths_checked = array( 1=>'' , '' , '' , '' , '' , '' , '' , '' , '' , '' , '' , '' ) ;

	if( trim( $rrule ) == '' ) {
		$norrule_checked = "checked='checked'" ;
	} else {

		// rrule �̊e�v�f��ϐ��ɓW�J
		$rrule = strtoupper( $rrule ) ;
		$rules = split( ';' , $rrule ) ;
		foreach( $rules as $rule ) {
			list( $key , $val ) = explode( '=' , $rule , 2 ) ;
			$key = trim( $key ) ;
			$$key = trim( $val ) ;
		}

		if( empty( $FREQ ) ) $FREQ = 'DAILY' ;
		if( empty( $INTERVAL ) || $INTERVAL <= 0 ) $INTERVAL = 1 ;

		// �p�x�������
		switch( $FREQ ) {
			case 'DAILY' :
				$daily_interval_init = $INTERVAL ;
				$daily_checked = "checked='checked'" ;
				break ;
			case 'WEEKLY' :
				if( empty( $BYDAY ) ) break ;	// BYDAY �K�{
				$weekly_interval_init = $INTERVAL ;
				$weekly_checked = "checked='checked'" ;
				$wdays = explode( ',' , $BYDAY , 7 ) ;
				foreach( $wdays as $wday ) {
					if( isset( $wdays_checked[ $wday ] ) ) $wdays_checked[ $wday ] = "checked='checked'" ;
				}
				break ;
			case 'MONTHLY' :
				if( isset( $BYDAY ) ) {
					$byday_m_init = $BYDAY ;
				} else if( isset( $BYMONTHDAY ) ) {
					$bymonthday_init = $BYMONTHDAY ;
				} else {
					break ;	// BYDAY �܂��� BYMONTHDAY �K�{
				}
				$monthly_interval_init = $INTERVAL ;
				$monthly_checked = "checked='checked'" ;
				break ;
			case 'YEARLY' :
				if( empty( $BYMONTH ) ) $BYMONTH = '' ;
				if( isset( $BYDAY ) ) $byday_m_init = $BYDAY ;
				$yearly_interval_init = $INTERVAL ;
				$yearly_checked = "checked='checked'" ;
				$months = explode( ',' , $BYMONTH , 12 ) ;
				foreach( $months as $month ) {
					$month = intval( $month ) ;
					if( $month > 0 && $month <= 12 ) $bymonths_checked[ $month ] = "checked='checked'" ;
				}
				break ;
		}

		// �I���������
		// UNTIL �� COUNT �̗��������鎞�� COUNT �D��
		if( isset( $COUNT ) && $COUNT > 0 ) {
			$count_init = $COUNT ;
			$count_checked = "checked='checked'" ;
		} else if( isset( $UNTIL ) ) {
			// UNTIL �́A�S���f�[�^�ł���Ɩ������Ō��Ȃ�
			$year = substr( $UNTIL , 0 , 4 ) ;
			$month = substr( $UNTIL , 4 , 2 ) ;
			$date = substr( $UNTIL , 6 , 2 ) ;
			$until_init = "$year-$month-$date" ;
			$until_checked = "checked='checked'" ;
		} else {
			// ���҂Ƃ��w�肪�Ȃ���΁A�I�������Ȃ�
			$noterm_checked = "checked='checked'" ;
		}

	}

	// UNTIL ���w�肷�邽�߂̃{�b�N�X
	$textbox_until = $this->get_formtextdateselect( 'rrule_until' , $until_init ) ;

	// �j���I���`�F�b�N�{�b�N�X�̓W�J
	$wdays_checkbox = '' ;
	foreach( $this->byday2langday_w as $key => $val ) {
		$wdays_checkbox .= "<input type='checkbox' name='rrule_weekly_bydays[]' value='$key' {$wdays_checked[$key]} />$val &nbsp; \n" ;
	}

	// ���I���`�F�b�N�{�b�N�X�̓W�J
	$bymonth_checkbox = "<table border='0' cellpadding='2'><tr>\n" ;
	foreach( $bymonths_checked as $key => $val ) {
		$bymonth_checkbox .= "<td><input type='checkbox' name='rrule_bymonths[]' value='$key' $val />{$this->month_short_names[$key]}</td>\n" ;
		if( $key == 6 ) $bymonth_checkbox .= "</tr>\n<tr>\n" ;
	}
	$bymonth_checkbox .= "</tr></table>\n" ;

	// ��N�j���I��OPTION�̓W�J
	$byday_m_options = '' ;
	foreach( $this->byday2langday_m as $key => $val ) {
		if( $byday_m_init == $key ) {
			$byday_m_options .= "<option value='$key' selected='selected'>$val</option>\n" ;
		} else {
			$byday_m_options .= "<option value='$key'>$val</option>\n" ;
		}
	}

	return "
			<input type='radio' name='rrule_freq' value='none' $norrule_checked />"._PICAL_RR_R_NORRULE."<br />
			<br />
			<fieldset>
				<legend class='blockTitle'>"._PICAL_RR_R_YESRRULE."</legend>
				<fieldset>
					<legend class='blockTitle'><input type='radio' name='rrule_freq' value='daily' $daily_checked />"._PICAL_RR_FREQDAILY."</legend>
					"._PICAL_RR_FREQDAILY_PRE." <input type='text' size='2' name='rrule_daily_interval' value='$daily_interval_init' /> "._PICAL_RR_FREQDAILY_SUF."
				</fieldset>
				<br />
				<fieldset>
					<legend class='blockTitle'><input type='radio' name='rrule_freq' value='weekly' $weekly_checked />"._PICAL_RR_FREQWEEKLY."</legend>
					"._PICAL_RR_FREQWEEKLY_PRE."<input type='text' size='2' name='rrule_weekly_interval' value='$weekly_interval_init' /> "._PICAL_RR_FREQWEEKLY_SUF." <br />
					$wdays_checkbox
				</fieldset>
				<br />
				<fieldset>
					<legend class='blockTitle'><input type='radio' name='rrule_freq' value='monthly' $monthly_checked />"._PICAL_RR_FREQMONTHLY."</legend>
					"._PICAL_RR_FREQMONTHLY_PRE."<input type='text' size='2' name='rrule_monthly_interval' value='$monthly_interval_init' /> "._PICAL_RR_FREQMONTHLY_SUF." &nbsp; 
					<select name='rrule_monthly_byday'>
						<option value=''>"._PICAL_RR_S_NOTSELECTED."</option>
						$byday_m_options
					</select> &nbsp; "._PICAL_RR_OR." &nbsp; 
					<input type='text' size='10' name='rrule_bymonthday' value='$bymonthday_init' />"._PICAL_NTC_MONTHLYBYMONTHDAY."
				</fieldset>
				<br />
				<fieldset>
					<legend class='blockTitle'><input type='radio' name='rrule_freq' value='yearly' $yearly_checked />"._PICAL_RR_FREQYEARLY."</legend>
					"._PICAL_RR_FREQYEARLY_PRE."<input type='text' size='2' name='rrule_yearly_interval' value='$yearly_interval_init' /> "._PICAL_RR_FREQYEARLY_SUF." <br />
					$bymonth_checkbox <br />
					<select name='rrule_yearly_byday'>
						<option value=''>"._PICAL_RR_S_SAMEASBDATE."</option>
						$byday_m_options
					</select>
				</fieldset>
				<br />
				<input type='radio' name='rrule_terminator' value='noterm' $noterm_checked onClick='document.MainForm.rrule_until.disabled=true;document.MainForm.rrule_count.disabled=true;' />"._PICAL_RR_R_NOCOUNTUNTIL." &nbsp; ".sprintf( _PICAL_NTC_EXTRACTLIMIT , $this->max_rrule_extract )."  <br />
				<input type='radio' name='rrule_terminator' value='count' $count_checked onClick='document.MainForm.rrule_until.disabled=true;document.MainForm.rrule_count.disabled=false;' />"._PICAL_RR_R_USECOUNT_PRE." <input type='text' size='3' name='rrule_count' value='$count_init' /> "._PICAL_RR_R_USECOUNT_SUF."<br />
				<input type='radio' name='rrule_terminator' value='until' $until_checked onClick='document.MainForm.rrule_until.disabled=false;document.MainForm.rrule_count.disabled=true;' />"._PICAL_RR_R_USEUNTIL." $textbox_until
			</fieldset>
  \n" ;
}



// POST���ꂽrrule�֘A�̐ݒ�l���ARRULE������ɑg�ݏグ��N���X�֐�
function rrule_from_post( $start , $allday_flag )
{
	// �J��Ԃ������Ȃ�A�������ŋ󕶎����Ԃ�
	if( $_POST['rrule_freq'] == 'none' ) return '' ;

	// �p�x����
	switch( strtoupper( $_POST['rrule_freq'] ) ) {
		case 'DAILY' :
			$ret_freq = "FREQ=DAILY;INTERVAL=" . abs( intval( $_POST['rrule_daily_interval'] ) ) ;
			break ;
		case 'WEEKLY' :
			$ret_freq = "FREQ=WEEKLY;INTERVAL=" . abs( intval( $_POST['rrule_weekly_interval'] ) ) ;
			if( empty( $_POST['rrule_weekly_bydays'] ) ) {
				// �j���̎w�肪����Ȃ���΁A�J�n���Ɠ����j���ɂ���
				$bydays = array_keys( $this->byday2langday_w ) ;
				$byday = $bydays[ date( 'w' , $start ) ] ;
			} else {
				$byday = '' ;
				foreach( $_POST['rrule_weekly_bydays'] as $wday ) {
					if( preg_match( '/[^\w]+/' , $wday ) ) die( "Some injection was tried" ) ;
					$byday .= substr( $wday , 0 , 2 ) . ',' ;
				}
				$byday = substr( $byday , 0 , -1 ) ;
			}
			$ret_freq .= ";BYDAY=$byday" ;
			break ;
		case 'MONTHLY' :
			$ret_freq = "FREQ=MONTHLY;INTERVAL=" . abs( intval( $_POST['rrule_monthly_interval'] ) ) ;
			if( $_POST['rrule_monthly_byday'] != '' ) {
				// ��N�j���ɂ��w��
				$byday = substr( trim( $_POST['rrule_monthly_byday'] ) , 0 , 4 ) ;				if( preg_match( '/[^\w-]+/' , $byday ) ) die( "Some injection was tried" ) ;
				$ret_freq .= ";BYDAY=$byday" ;
			} else if( $_POST['rrule_bymonthday'] != '' ) {
				// ���t�ɂ��w��
				$bymonthday = preg_replace( '/[^0-9,]+/' , '' , $_POST['rrule_bymonthday'] ) ;
				$ret_freq .= ";BYMONTHDAY=$bymonthday" ;
			} else {
				// ��N�j������t�̎w�肪�Ȃ���΁A�J�n���Ɠ������t�Ƃ���
				$ret_freq .= ";BYMONTHDAY=" . date( 'j' , $start ) ;
			}
			break ;
		case 'YEARLY' :
			$ret_freq = "FREQ=YEARLY;INTERVAL=" . abs( intval( $_POST['rrule_yearly_interval'] ) ) ;
			if( empty( $_POST['rrule_bymonths'] ) ) {
				// ���̎w�肪����Ȃ���΁A�J�n���Ɠ������ɂ���
				$bymonth = date( 'n' , $start ) ;
			} else {
				$bymonth = '' ;
				foreach( $_POST['rrule_bymonths'] as $month ) {
					$bymonth .= intval( $month ) . ',' ;
				}
				$bymonth = substr( $bymonth , 0 , -1 ) ;
			}
			if( $_POST['rrule_yearly_byday'] != '' ) {
				// ��N�j���ɂ��w��
				$byday = substr( trim( $_POST['rrule_yearly_byday'] ) , 0 , 4 ) ;
				if( preg_match( '/[^\w-]+/' , $byday ) ) die( "Some injection was tried" ) ;
				$ret_freq .= ";BYDAY=$byday" ;
			}
			$ret_freq .= ";BYMONTH=$bymonth" ;
			break ;
		default :
			return '' ;
	}

	// �I������
	if( empty( $_POST['rrule_terminator'] ) ) $_POST['rrule_terminator'] = '' ;
	switch( strtoupper( $_POST['rrule_terminator'] ) ) {
		case 'COUNT' :
			$ret_term = ';COUNT=' . abs( intval( $_POST['rrule_count'] ) ) ;
			break ;
		case 'UNTIL' :
			// UNTIL��Unixtime��
			list( $until , $until_date , $use_default ) = $this->parse_posted_date( $this->mb_convert_kana( $_POST[ 'rrule_until' ] , "a" ) , $this->unixtime ) ;
			// 1970�ȑO�E2038�N�ȍ~�Ȃ�AUNTIL����
			if( $until_date ) {
				$ret_term = '' ;
			} else {
				if( ! $allday_flag ) {
					// �S���C�x���g�łȂ���Γ�����23:59:59���I�������ƌ��Ȃ��āA UTC �֎����v�Z����
					$event_tz = isset( $_POST['event_tz'] ) ? $_POST['event_tz'] : $this->user_TZ ;
					$until = $until - intval( $event_tz * 3600 ) + 86400 - 1 ;
				}
				$ret_term = ';UNTIL=' . date( 'Ymd\THis\Z' , $until ) ;
			}
			break ;
		case 'NOTERM' :
		default :
			$ret_term = '' ;
			break ;
	}

	// WKST�́A�����œ����
	$ret_wkst = $this->week_start ? ';WKST=MO' : ';WKST=SU' ;

	return $ret_freq . $ret_term . $ret_wkst ;
}


// �n���ꂽevent_id������(�e)�Ƃ��āARRULE��W�J���ăf�[�^�x�[�X�ɔ��f
function rrule_extract( $event_id )
{
	$yrs = mysql_query( "SELECT *,TO_DAYS(end_date)-TO_DAYS(start_date) AS date_diff FROM $this->table WHERE id='$event_id'" , $this->conn ) ;
	if( mysql_num_rows( $yrs ) < 1 ) return ;
	$event = mysql_fetch_object( $yrs ) ;

	if( $event->rrule == '' ) return ;

	// rrule �̊e�v�f��ϐ��ɓW�J
	$rrule = strtoupper( $event->rrule ) ;
	$rules = split( ';' , $rrule ) ;
	foreach( $rules as $rule ) {
		list( $key , $val ) = explode( '=' , $rule , 2 ) ;
		$key = trim( $key ) ;
		$$key = trim( $val ) ;
	}

	// �����ɂ���āARRULE�̓��t�w�肪�ǂ��u������邩�̌v�Z 
	if( $event->allday ) {
		$tzoffset_date = 0 ;
	} else {
		// �C�x���g���g��TZ�œW�J����
		$tzoffset_s2e = intval( ( $event->event_tz - $this->server_TZ ) * 3600 ) ;
		$tzoffset_date = date( 'z' , $event->start + $tzoffset_s2e ) - date( 'z' , $event->start ) ;
		if( $tzoffset_date > 1 ) $tzoffset_date = -1 ;
		else if( $tzoffset_date < -1 ) $tzoffset_date = 1 ;
	}

	if( empty( $FREQ ) ) $FREQ = 'DAILY' ;
	if( empty( $INTERVAL ) || $INTERVAL <= 0 ) $INTERVAL = 1 ;

	// �x�[�X�ƂȂ�SQL��
	$base_sql = "INSERT INTO $this->table SET uid='$event->uid',groupid='$event->groupid',summary='".addslashes($event->summary)."',location='".addslashes($event->location)."',organizer='".addslashes($event->organizer)."',sequence='$event->sequence',contact='".addslashes($event->contact)."',tzid='$event->tzid',description='".addslashes($event->description)."',dtstamp='$event->dtstamp',categories='".addslashes($event->categories)."',transp='$event->transp',priority='$event->priority',admission='$event->admission',class='$event->class',rrule='".addslashes($event->rrule)."',unique_id='$event->unique_id',allday='$event->allday',start_date=null,end_date=null,cid='$event->cid',event_tz='$event->event_tz',server_tz='$event->server_tz',poster_tz='$event->poster_tz',extkey0='$event->extkey0',extkey1='$event->extkey1',rrule_pid='$event_id'" ;

	// �I���������
	// �J�E���g
	$count = $this->max_rrule_extract ;
	if( isset( $COUNT ) && $COUNT > 0 && $COUNT < $count ) {
		$count = $COUNT ;
	}
	// �W�J�I����
	if( isset( $UNTIL ) ) {
		// UNTIL �́A�S�������ł���Ɩ������Ō��Ȃ�
		$year = substr( $UNTIL , 0 , 4 ) ;
		$month = substr( $UNTIL , 4 , 2 ) ;
		$date = substr( $UNTIL , 6 , 2 ) ;
		if( ! checkdate( $month , $date , $year ) ) $until = 0x7FFFFFFF ;
		else {
			$until = gmmktime( 23 , 59 , 59 , $month , $date , $year , 0 ) ;
			if( ! $event->allday ) {
				// �T�[�o���ԂƃC�x���g���Ԃœ��t���قȂ�ꍇ�ɂ�UNTIL�����炷
				$until -= intval( $tzoffset_date * 86400 ) ;
				// UTC -> server_TZ �̎����v�Z�͍s��Ȃ�
				// $until -= intval( $this->server_TZ * 3600 ) ;
			}
		}
	} else $until = 0x7FFFFFFF ;

	// WKST
	if( empty( $WKST ) ) $WKST = 'MO' ;

	// UnixTimestamp�͈͊O�̏���
	if( isset( $event->start_date ) ) {
		// �J�n��I����2038�N�ȍ~�Ȃ�W�J���Ȃ�
		if( date( 'Y' , $event->start ) >= 2038 ) return ;
		if( date( 'Y' , $event->end ) >= 2038 ) return ;

		// 1971�N�̓���������W�J�x�[�X��start�Ƃ���
		$event->start = mktime( 0 , 0 , 0 , substr( $event->start_date , 5 , 2 ) , substr( $event->start_date , 8 , 2 ) , 1970 + 1 ) ;

		// end��1970�ȑO�Ȃ�A�����Ƃ��Ĕ��f�B�����łȂ��ꍇ�͂Ƃ肠�������u TODO
		if( isset( $event->end_date ) ) {
			$event->end = $event->start + ( $event->date_diff + 1 ) * 86400 ;
		}
	}

	// �p�x�������
	$sqls = array() ;
	switch( $FREQ ) {
		case 'DAILY' :
			$gmstart = $event->start + date( "Z" , $event->start ) ;
			$gmend = $event->end + date( "Z" , $event->end ) ;
			for( $c = 1 ; $c < $count ; $c ++ ) {
				$gmstart += $INTERVAL * 86400 ;
				$gmend += $INTERVAL * 86400 ;
				if( $gmstart > $until ) break ;
				$sqls[] = $base_sql . ",start=UNIX_TIMESTAMP('".gmdate("Y-m-d H:i:s", $gmstart)."'),end=UNIX_TIMESTAMP('".gmdate("Y-m-d H:i:s", $gmend)."')";
			}
			break ;
			
		case 'WEEKLY' :
			$gmstart = $event->start + date( "Z" , $event->start ) ;
			$gmstartbase = $gmstart ;
			$gmend = $event->end + date( "Z" , $event->end ) ;
			$duration = $gmend - $gmstart ;
			$wtop_date = gmdate( 'j' , $gmstart ) - gmdate( 'w' , $gmstart ) ;
			if( $WKST != 'SU' ) $wtop_date = $wtop_date == 7 ? 1 : $wtop_date + 1 ;
			$secondofday = $gmstart % 86400 ;
			$month = gmdate( 'm' , $gmstart ) ;
			$year = gmdate( 'Y' , $gmstart ) ;
			$week_top = gmmktime( 0 , 0 , 0 , $month , $wtop_date , $year ) ;
			$c = 1 ;
			// ���l���j���z��̍쐬
			$temp_dates = explode( ',' , $BYDAY ) ;
			$wdays = array_keys( $this->byday2langday_w ) ;
			if( $WKST != 'SU' ) {
				// rotate wdays for creating array starting with Monday
				$sun_date = array_shift( $wdays ) ;
				array_push( $wdays , $sun_date ) ;
			}
			$dates = array() ;
			foreach( $temp_dates as $date ) {
				// measure for bug of PHP<4.2.0
				if( in_array( $date , $wdays ) ) {
					$dates[] = array_search( $date , $wdays ) ;
				}
			}
			sort( $dates ) ;
			$dates = array_unique( $dates ) ;
			if( ! count( $dates ) ) return ;
			while( 1 ) {
				foreach( $dates as $date ) {
					// �T�[�o���ԂƃC�x���g���Ԃŗj�����قȂ�ꍇ�̏����ǉ�
					$gmstart = $week_top + ( $date - $tzoffset_date ) * 86400 + $secondofday ;
					if( $gmstart <= $gmstartbase ) continue ;
					$gmend = $gmstart + $duration ;
					if( $gmstart > $until ) break 2 ;
					if( ++ $c > $count ) break 2 ;
					$sqls[] = $base_sql . ",start=UNIX_TIMESTAMP('".gmdate("Y-m-d H:i:s", $gmstart)."'),end=UNIX_TIMESTAMP('".gmdate("Y-m-d H:i:s", $gmend)."')";
				}
				$week_top += $INTERVAL * 86400 * 7 ;
			}
			break ;

		case 'MONTHLY' :
			$gmstart = $event->start + date( "Z" , $event->start ) ;
			$gmstartbase = $gmstart ;
			$gmend = $event->end + date( "Z" , $event->end ) ;
			$duration = $gmend - $gmstart ;
			$secondofday = $gmstart % 86400 ;
			$month = gmdate( 'm' , $gmstart ) ;
			$year = gmdate( 'Y' , $gmstart ) ;
			$c = 1 ;
			if( isset( $BYDAY ) && ereg( '^(-1|[1-4])(SU|MO|TU|WE|TH|FR|SA)' , $BYDAY , $regs ) ) {
				// ��N�j���w��(BYDAY)�̏ꍇ�i�����s�j
				// �ړI�̗j���ԍ����擾
				$wdays = array_keys( $this->byday2langday_w ) ;
				$wday = array_search( $regs[2] , $wdays ) ;
				$first_ymw = gmdate( 'Ym' , $gmstart ) . intval( ( gmdate( 'j' , $gmstart ) - 1 ) / 7 ) ;
				if( $regs[1] == -1 ) {
					// �ŏI�T�w��̏ꍇ�̃��[�v
					$monthday_bottom = gmmktime( 0 , 0 , 0 , $month , 0 , $year ) ;
					while( 1 ) {
						for( $i = 0 ; $i < $INTERVAL ; $i ++ ) {
							$monthday_bottom += gmdate( 't' , $monthday_bottom + 86400 ) * 86400 ;
						}
						// �ŏI���̗j���𒲂ׂ�
						$last_monthdays_wday = gmdate( 'w' , $monthday_bottom ) ;
						$date_back = $wday - $last_monthdays_wday ;
						if( $date_back > 0 ) $date_back -= 7 ;
						// �T�[�o���ԂƃC�x���g���Ԃŗj�����قȂ�ꍇ�̏����ǉ�
						$gmstart = $monthday_bottom + ( $date_back - $tzoffset_date ) * 86400 + $secondofday ;
						if( $gmstart <= $gmstartbase ) continue ;
						$gmend = $gmstart + $duration ;
						if( $gmstart > $until ) break ;
						if( ++ $c > $count ) break ;
						$sqls[] = $base_sql . ",start=UNIX_TIMESTAMP('".gmdate("Y-m-d H:i:s", $gmstart)."'),end=UNIX_TIMESTAMP('".gmdate("Y-m-d H:i:s", $gmend)."')";
					}
				} else {
					// ��N�T�w��̏ꍇ�̃��[�v
					$monthday_top = gmmktime( 0 , 0 , 0 , $month , 1 , $year ) ;
					$week_number_offset = ( $regs[1] - 1 ) * 7 * 86400 ;
					while( 1 ) {
						for( $i = 0 ; $i < $INTERVAL ; $i ++ ) {
							$monthday_top += gmdate( 't' , $monthday_top ) * 86400 ;
						}
						// ��N�T�����̗j���𒲂ׂ�
						$week_numbers_top_wday = gmdate( 'w' , $monthday_top + $week_number_offset ) ;
						$date_ahead = $wday - $week_numbers_top_wday ;
						if( $date_ahead < 0 ) $date_ahead += 7 ;
						// �T�[�o���ԂƃC�x���g���Ԃŗj�����قȂ�ꍇ�̏����ǉ�
						$gmstart = $monthday_top + $week_number_offset + ( $date_ahead - $tzoffset_date ) * 86400 + $secondofday ;
						if( $gmstart <= $gmstartbase ) continue ;
						$gmend = $gmstart + $duration ;
						if( $gmstart > $until ) break ;
						if( ++ $c > $count ) break ;
						$sqls[] = $base_sql . ",start=UNIX_TIMESTAMP('".gmdate("Y-m-d H:i:s", $gmstart)."'),end=UNIX_TIMESTAMP('".gmdate("Y-m-d H:i:s", $gmend)."')";
					}
				}
			} else if( isset( $BYMONTHDAY ) ) {
				// ���t�w��(BYMONTHDAY)�̏ꍇ�i�����j
				$monthday_top = gmmktime( 0 , 0 , 0 , $month , 1 , $year ) ;
				// BYMONTHDAY ��O�������āA$dates�z��ɂ���
				$temp_dates = explode( ',' , $BYMONTHDAY ) ;
				$dates = array() ;
				foreach( $temp_dates as $date ) {
					if( $date > 0 && $date <= 31 ) $dates[] = intval( $date ) ;
				}
				sort( $dates ) ;
				$dates = array_unique( $dates ) ;
				if( ! count( $dates ) ) return ;
				while( 1 ) {
					$months_day = gmdate( 't' , $monthday_top ) ;
					foreach( $dates as $date ) {
						// ���̍ŏI���t���[�`�F�b�N
						if( $date > $months_day ) $date = $months_day ;
						// �T�[�o���ԂƃC�x���g���Ԃœ��t���قȂ�ꍇ�̏����ǉ�
						$gmstart = $monthday_top + ( $date - 1 - $tzoffset_date ) * 86400 + $secondofday ;
						if( $gmstart <= $gmstartbase ) continue ;
						$gmend = $gmstart + $duration ;
						if( $gmstart > $until ) break 2 ;
						if( ++ $c > $count ) break 2 ;
						$sqls[] = $base_sql . ",start=UNIX_TIMESTAMP('".gmdate("Y-m-d H:i:s", $gmstart)."'),end=UNIX_TIMESTAMP('".gmdate("Y-m-d H:i:s", $gmend)."')";
					}
					for( $i = 0 ; $i < $INTERVAL ; $i ++ ) {
						$monthday_top += gmdate( 't' , $monthday_top ) * 86400 ;
					}
				}
			} else {
				// �L����$BYDAY��$BYMONTHDAY��������΁A�J��Ԃ��������Ȃ�
				return ;
			}
			break ;
			
		case 'YEARLY' :
			$gmstart = $event->start + date( "Z" , $event->start ) ;
			$gmstartbase = $gmstart ;
			$gmend = $event->end + date( "Z" , $event->end ) ;
			$duration = $gmend - $gmstart ;
			$secondofday = $gmstart % 86400 ;
			$gmmonth = gmdate( 'n' , $gmstart ) ;

			// empty BYMONTH
			if( empty( $BYMONTH ) ) $BYMONTH = $gmmonth ;

			// BYMONTH ��O�������āA$months�z��ɂ���iBYMONTH�͕����j
			$temp_months = explode( ',' , $BYMONTH ) ;
			$months = array() ;
			foreach( $temp_months as $month ) {
				if( $month > 0 && $month <= 12 ) $months[] = intval( $month ) ;
			}
			sort( $months ) ;
			$months = array_unique( $months ) ;
			if( ! count( $months ) ) return ;

			if( isset( $BYDAY ) && ereg( '^(-1|[1-4])(SU|MO|TU|WE|TH|FR|SA)' , $BYDAY , $regs ) ) {
				// ��N�j���w��̏ꍇ�i�����s�j
				// �ړI�̗j���ԍ����擾
				$wdays = array_keys( $this->byday2langday_w ) ;
				$wday = array_search( $regs[2] , $wdays ) ;
				$first_ym = gmdate( 'Ym' , $gmstart ) ;
				$year = gmdate( 'Y' , $gmstart ) ;
				$c = 1 ;
				if( $regs[1] == -1 ) {
					// �ŏI�T�w��̏ꍇ�̃��[�v
					while( 1 ) {
						foreach( $months as $month ) {
							// �ŏI���̗j���𒲂ׂ�
							$last_monthdays_wday = gmdate( 'w' , gmmktime( 0 , 0 , 0 , $month + 1 , 0 , $year ) ) ;
							$date_back = $wday - $last_monthdays_wday ;
							if( $date_back > 0 ) $date_back -= 7 ;
							$gmstart = gmmktime( 0 , 0 , 0 , $month + 1 , $date_back - $tzoffset_date , $year ) + $secondofday ;
							// ����Ɠ������ȑO���ǂ����`�F�b�N
							if( gmdate( 'Ym' , $gmstart ) <= $first_ym ) continue ;
							$gmend = $gmstart + $duration ;
							if( $gmstart > $until ) break 2 ;
							if( ++ $c > $count ) break 2 ;
							$sqls[] = $base_sql . ",start=UNIX_TIMESTAMP('".gmdate("Y-m-d H:i:s", $gmstart)."'),end=UNIX_TIMESTAMP('".gmdate("Y-m-d H:i:s", $gmend)."')";
						}
						$year += $INTERVAL ;
						if( $year >= 2038 ) break ;
					}
				} else {
					// ��N�T�w��̏ꍇ�̃��[�v
					$week_numbers_top_date = 1 + ( $regs[1] - 1 ) * 7 ;
					while( 1 ) {
						foreach( $months as $month ) {
							// ��N�T�����̗j���𒲂ׂ�
							$week_numbers_top_wday = gmdate( 'w' , gmmktime( 0 , 0 , 0 , $month , $week_numbers_top_date , $year ) ) ;
							$date_ahead = $wday - $week_numbers_top_wday ;
							if( $date_ahead < 0 ) $date_ahead += 7 ;
							$gmstart = gmmktime( 0 , 0 , 0 , $month , $week_numbers_top_date + $date_ahead - $tzoffset_date , $year ) + $secondofday ;
							// ����Ɠ������ȑO���ǂ����`�F�b�N
							if( gmdate( 'Ym' , $gmstart ) <= $first_ym ) continue ;
							$gmend = $gmstart + $duration ;
							if( $gmstart > $until ) break 2 ;
							if( ++ $c > $count ) break 2 ;
							$sqls[] = $base_sql . ",start=UNIX_TIMESTAMP('".gmdate("Y-m-d H:i:s", $gmstart)."'),end=UNIX_TIMESTAMP('".gmdate("Y-m-d H:i:s", $gmend)."')";
						}
						$year += $INTERVAL ;
						if( $year >= 2038 ) break ;
					}
				}
			} else {
				// ���t�w��̏ꍇ�̃��[�v�i�����s�j
				$first_date = gmdate( 'j' , $gmstart ) ;
				$year = gmdate( 'Y' , $gmstart ) ;
				$c = 1 ;
				while( 1 ) {
					foreach( $months as $month ) {
						$date = $first_date ;
						// ���̍ŏI���t���[�`�F�b�N
						while( ! checkdate( $month , $date , $year ) && $date > 0 ) $date -- ;
						// $date �� gmdate('j') ���瓾�Ă��邽�߁A$tzoffset_date �̏����͕s�v
						$gmstart = gmmktime( 0 , 0 , 0 , $month , $date , $year ) + $secondofday ;
						if( $gmstart <= $gmstartbase ) continue ;
						$gmend = $gmstart + $duration ;
						if( $gmstart > $until ) break 2 ;
						if( ++ $c > $count ) break 2 ;
						$sqls[] = $base_sql . ",start=UNIX_TIMESTAMP('".gmdate("Y-m-d H:i:s", $gmstart)."'),end=UNIX_TIMESTAMP('".gmdate("Y-m-d H:i:s", $gmend)."')";
					}
					$year += $INTERVAL ;
					if( $year >= 2038 ) break ;
				}
			}
			break ;
			
		default :
			return ;
	}

	// echo "<pre>" ; var_dump( $sqls ) ; echo "</pre>" ; exit ;
	foreach( $sqls as $sql ) {
		mysql_query( $sql , $this->conn ) ;
	}
}


// The End of Class
}

}
?>