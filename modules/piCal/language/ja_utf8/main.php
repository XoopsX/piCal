<?php

if( defined( 'FOR_XOOPS_LANG_CHECKER' ) || ! defined( 'PICAL_MB_LOADED' ) ) {

define( 'PICAL_MB_LOADED' , 1 ) ;

// index.php
define('_MB_PICAL_ERR_NOPERMTOUPDATE',"変更する権限がありません");
define('_MB_PICAL_ERR_NOPERMTOINSERT',"登録する権限がありません");
define('_MB_PICAL_ERR_NOPERMTODELETE',"削除する権限がありません");
define('_MB_PICAL_ALT_PRINTTHISEVENT',"印刷する");

// print.php
define('_MB_PICAL_COMESFROM',"この予定は %s にて作成されました");

// It shuld be set on languages/ja_utf8/calender.php
define('_CAL_MONTH',"月");
define('_CAL_DAY',"日");

}

?>