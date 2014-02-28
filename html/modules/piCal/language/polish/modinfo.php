<?php

if( defined( 'FOR_XOOPS_LANG_CHECKER' ) || ! defined( 'PICAL_MI_LOADED' ) ) {




// Appended by Xoops Language Checker -GIJOE- in 2014-02-28 22:13:13
define('_MI_WHATDAY_PLUGINS','whatday plugins');
define('_MI_DESCWHATDAY_PLUGINS','Enables whatday plugins separated by comma. (rokuyou,24sekki,kyureki)');
define('_MI_COM_DIRNAME','Comment integration directory');
define('_MI_COM_DIRNAMEDSC','When use D3-comment integration system. <br/>write your d3forum (html) directory <br/>If you do not use comments or use xoops comment system, leave this in empty.');
define('_MI_COM_FORUM_ID','d3forum_id');
define('_MI_COM_FORUM_IDDSC','When you set above integration diredtory, write forum_id');
define('_MI_COM_ORDER','Order of comment integration');
define('_MI_COM_ORDERDSC','When you set comment integration, select display order of comment posts');
define('_MI_COM_VIEW','View of comment-integration');
define('_MI_COM_VIEWDSC','select flat or thread');
define('_MI_COM_POSTSNUM','\'Max posts displayed in comment integration');

// Appended by Xoops Language Checker -GIJOE- in 2006-11-05 06:41:41
define('_MI_PROXYSETTINGS','Proxy settings (host:port:user:pass)');

// Appended by Xoops Language Checker -GIJOE- in 2006-04-06 04:57:58
define('_MI_PICAL_ADMENU_MYTPLSADMIN','Templates');

define( 'PICAL_MI_LOADED' , 1 ) ;

// Module Info

// The name of this module
define("_MI_PICAL_NAME"              ,"piCal");

// A brief description of this module
define("_MI_PICAL_DESC"              ,"Kalendarz z harmonogramem");

// Default Locale
define("_MI_PICAL_DEFAULTLOCALE"     ,"Poland");

// Names of blocks for this module (Not all module has blocks)
define("_MI_PICAL_BNAME_MINICAL"            , "MiniCalendarz");
define("_MI_PICAL_BNAME_MINICAL_DESC"       , "Wyswietl blok MiniCalendarza");
define("_MI_PICAL_BNAME_MINICALEX"          , "MiniCalendarEx");
define("_MI_PICAL_BNAME_MINICALEX_DESC"     , "Extensible minicalendar z pluginem systemu");
define("_MI_PICAL_BNAME_MONTHCAL"           , "Miesiêczny kalendarz");
define("_MI_PICAL_BNAME_MONTHCAL_DESC"      , "Wy¶wietl pe³ny widkok Miesiêcznego kalendarza");
define("_MI_PICAL_BNAME_TODAYS"             , "Dzisiejsze wydarzenia");
define("_MI_PICAL_BNAME_TODAYS_DESC"        , "Wy¶wietl wydarzenia na dzi¶");
define("_MI_PICAL_BNAME_THEDAYS"            , "Wydarzenia w %s");
define("_MI_PICAL_BNAME_THEDAYS_DESC"       , "Wy¶wietl wydarzenia dla wskazanego dnia");
define("_MI_PICAL_BNAME_COMING"             , "Nadchodz±ce wydarzenia");
define("_MI_PICAL_BNAME_COMING_DESC"        , "Wy¶wietl nadchodz±ce wydarzenia");
define("_MI_PICAL_BNAME_AFTER"              , "Wydarzenia po %s");
define("_MI_PICAL_BNAME_AFTER_DESC"         , "Wy¶wietl wydarzenia po wksazanym dniu");
define("_MI_PICAL_BNAME_NEW"                , "Nowe wydarzenia");
define("_MI_PICAL_BNAME_NEW_DESC"           , "Nowe wydarzenia bêd± wy¿ej ni¿ starsze");

// Names of submenu
define("_MI_PICAL_SM_SUBMIT"                ,"Dodaj");

//define("_MI_PICAL_ADMENU1","");

// Title of config items
define("_MI_USERS_AUTHORITY"                , "Prawa u¿ytkowników");
define("_MI_GUESTS_AUTHORITY"               , "Prawa go¶ci");
define("_MI_DEFAULT_VIEW"                   , "Domy¶lny widok na ¶rodku");
define("_MI_MINICAL_TARGET"                 , "Docelowy widok z MiniCalendarza");
define("_MI_COMING_NUMROWS"                 , "Liczba wydarzeñ w bloku Nadchodz±cych Wydarzeñ");
define("_MI_SKINFOLDER"                     , "Nazwa folderu ze skórk±");
define("_MI_PICAL_LOCALE"                   , "Lokacja (sprawd¼ pliki w locales/*.php)");
define("_MI_SUNDAYCOLOR"                    , "Kolor niedziel");
define("_MI_WEEKDAYCOLOR"                   , "Kolor zwyk³ego dnia");
define("_MI_SATURDAYCOLOR"                  , "Kolor soboty");
define("_MI_HOLIDAYCOLOR"                   , "Kolor wakacji");
define("_MI_TARGETDAYCOLOR"                 , "Kolor wybranego dnia");
define("_MI_SUNDAYBGCOLOR"                  , "T³o niedzieli");
define("_MI_WEEKDAYBGCOLOR"                 , "T³o zwyk³ego dnia");
define("_MI_SATURDAYBGCOLOR"                , "T³o soboty");
define("_MI_HOLIDAYBGCOLOR"                 , "T³o wakacji");
define("_MI_TARGETDAYBGCOLOR"               , "T³o wybranego dnia");
define("_MI_CALHEADCOLOR"                   , "Kolor nag³ówka");
define("_MI_CALHEADBGCOLOR"                 , "T³o nag³ówka");
define("_MI_CALFRAMECSS"                    , "Styl ramki kalendarza");
define("_MI_CANOUTPUTICS"                   , "Permission of outputting ics files");
define("_MI_MAXRRULEEXTRACT"                , "Upper limit of events extracted by Rrule.(COUNT)");
define("_MI_WEEKSTARTFROM"                  , "Dzieñ zaczyn±jcy tydzieñ");
define("_MI_WEEKNUMBERING"                  , "Numbering rule for weeks");
define("_MI_DAYSTARTFROM"                   , "Linia graniczna pomiêdzy dniami");
define("_MI_TIMEZONE_USING"                 , "Strefa czasowa serwera");
define("_MI_USE24HOUR"                      , "24-godzinny system (Lub 12-godzinny)");
define("_MI_NAMEORUNAME"                    , "Wy¶wietlaæ nick autora wydarzenia" ) ;
define("_MI_DESCNAMEORUNAME"                , "Wybierz je¿eli 'imiê' jest pokazywane" ) ;

// Description of each config items
define("_MI_EDITBYGUESTDSC"                 , "Uprawnienia dodawanie wydarzeñ przez go¶ci");

// Options of each config items
define("_MI_OPT_AUTH_NONE"                  , "nie mo¿e dodawaæ");
define("_MI_OPT_AUTH_WAIT"                  , "mo¿e ale musi to zaakceptowaæ administrator");
define("_MI_OPT_AUTH_POST"                  , "mo¿e dodawaæ bez akceptacji administratora");
define("_MI_OPT_AUTH_BYGROUP"               , "Ustawienia grup");
define("_MI_OPT_MINI_PHPSELF"               , "Obecna strona");
define("_MI_OPT_MINI_MONTHLY"               , "Miesiêczny kalendarz");
define("_MI_OPT_MINI_WEEKLY"                , "Tygodniowy kalendarz");
define("_MI_OPT_MINI_DAILY"                 , "Dzienny kalendarz");
define("_MI_OPT_MINI_LIST"                  , "Lista wydarzeñ");
define("_MI_OPT_CANOUTPUTICS"               , "mo¿e przetworzyæ");
define("_MI_OPT_CANNOTOUTPUTICS"            , "nie mo¿e przetworzyæ");
define("_MI_OPT_STARTFROMSUN"               , "Niedziela");
define("_MI_OPT_STARTFROMMON"               , "Poniedzia³ek");
define("_MI_OPT_WEEKNOEACHMONTH"            , "przez ka¿dy miesi±c");
define("_MI_OPT_WEEKNOWHOLEYEAR"            , "przez ca³ rok");
define("_MI_OPT_USENAME"                    , "Prawdziwe imiê" ) ;
define("_MI_OPT_USEUNAME"                   , "Login" ) ;
define("_MI_OPT_TZ_USEXOOPS"                , "Ustawienia Xoopsa" ) ;
define("_MI_OPT_TZ_USEWINTER"               , "warto¶æ z serwera jako czas zimowy (zalecane)" ) ;
define("_MI_OPT_TZ_USESUMMER"               , "warto¶æ z serwera jako czas letni" ) ;

// Admin Menus
define("_MI_PICAL_ADMENU0"                  , "Wydarzenia do akceptacji");
define("_MI_PICAL_ADMENU1"                  , "Zarz±dzanie wydarzeniami");
define("_MI_PICAL_ADMENU_CAT"               , "Zarz±dzanie kategoriami");
define("_MI_PICAL_ADMENU_CAT2GROUP"         , "Uprawnienia dostêpu do kategorii");
define("_MI_PICAL_ADMENU2"                  , "Globalne uprawnienia dostêpu");
define("_MI_PICAL_ADMENU_TM"                , "Tabela");
define("_MI_PICAL_ADMENU_PLUGINS"           , "Zarz±dzanie pluginami");
define("_MI_PICAL_ADMENU_ICAL"              , "Import z iCalendar");
define("_MI_PICAL_ADMENU_MYBLOCKSADMIN"     , "Bloki i grupy");

// Text for notifications
define('_MI_PICAL_GLOBAL_NOTIFY'            , 'Ogólne');
define('_MI_PICAL_GLOBAL_NOTIFYDSC'         , 'Opcje powiadomieñ.');
define('_MI_PICAL_CATEGORY_NOTIFY'          , 'Kategorie');
define('_MI_PICAL_CATEGORY_NOTIFYDSC'       , 'Opcje powiadomieñ, które odnosz± siê do aktualnej kategorii.');
define('_MI_PICAL_EVENT_NOTIFY'             , 'Wydarzenie');
define('_MI_PICAL_EVENT_NOTIFYDSC'          , 'Opcje powiadomieñ, które odnosz± siê do aktualnego wydarzenia.');

define('_MI_PICAL_GLOBAL_NEWEVENT_NOTIFY'       , 'Nowe wydarzenie');
define('_MI_PICAL_GLOBAL_NEWEVENT_NOTIFYCAP'    , 'Powiadom mnie kiedy zostanie utworzone nowe wydarzenie.');
define('_MI_PICAL_GLOBAL_NEWEVENT_NOTIFYDSC'    , 'Powiadom mnie kiedy zostanie utworzone nowe wydarzenie (+opis wydarzenia).');
define('_MI_PICAL_GLOBAL_NEWEVENT_NOTIFYSBJ'    , '[{X_SITENAME}] {X_MODULE} auto-notify : Nowe wydarzenie');

define('_MI_PICAL_CATEGORY_NEWEVENT_NOTIFY'     , 'Nowe wydarzenie w kategorii');
define('_MI_PICAL_CATEGORY_NEWEVENT_NOTIFYCAP'  , 'Powiadom mnie kiedy zostanie utworzone nowe wydarzenie w kategorii.');
define('_MI_PICAL_CATEGORY_NEWEVENT_NOTIFYDSC'  , 'Powiadom mnie kiedy zostanie utworzone nowe wydarzenie w kategorii (+opis).');
define('_MI_PICAL_CATEGORY_NEWEVENT_NOTIFYSBJ'  , '[{X_SITENAME}] {X_MODULE} auto-notify : Nowe wydarzenie w {CATEGORY_TITLE}');



}

?>