<?php

if( defined( 'FOR_XOOPS_LANG_CHECKER' ) || ! defined( 'PICAL_MB_LOADED' ) ) {

define( 'PICAL_MB_LOADED' , 1 ) ;
include_once 'pical_constants.php';

// index.php
define('_MB_PICAL_ERR_NOPERMTOUPDATE',"変更する権限がありません");
define('_MB_PICAL_ERR_NOPERMTOINSERT',"登録する権限がありません");
define('_MB_PICAL_ERR_NOPERMTODELETE',"削除する権限がありません");
define('_MB_PICAL_ALT_PRINTTHISEVENT',"印刷する");

// print.php
define('_MB_PICAL_COMESFROM',"この予定は %s にて作成されました");

define('_MB_PICAL_EDIT_TITLE',"予約日時の確定");
define('_MB_PICAL_EDIT_DATE',"予約日時");
define('_MB_PICAL_EDIT_DESC',"ご予約の内容");
define('_MB_PICAL_EDIT_SUBMIT',"クリックして確定します");
define('_MB_PICAL_RESERVATION',"予約");

define('_MB_PICAL_DUNIT',"日");
define('_MB_PICAL_DUNIT_MON',"月曜日");
define('_MB_PICAL_DUNIT_TUE',"火曜日");
define('_MB_PICAL_DUNIT_WED',"水曜日");
define('_MB_PICAL_DUNIT_THU',"木曜日");
define('_MB_PICAL_DUNIT_FRI',"金曜日");
define('_MB_PICAL_DUNIT_SAT',"土曜日");
define('_MB_PICAL_DUNIT_SUN',"日曜日");

}

?>