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

// Appended by Xoops Language Checker -GIJOE- in 2006-11-05 06:41:40
define('_MI_PROXYSETTINGS','Proxy settings (host:port:user:pass)');

// Appended by Xoops Language Checker -GIJOE- in 2006-02-15 05:31:20
define('_MI_PICAL_ADMENU_MYTPLSADMIN','Templates');

define( 'PICAL_MI_LOADED' , 1 ) ;

// Module Info

// The name of this module
define("_MI_PICAL_NAME","piCal");

// A brief description of this module
define("_MI_PICAL_DESC","Модуль Календаря");

// Default Locale
define("_MI_PICAL_DEFAULTLOCALE","russia");

// Names of blocks for this module (Not all module has blocks)
define("_MI_PICAL_BNAME_MINICAL","МиниКалендарь");
define("_MI_PICAL_BNAME_MINICAL_DESC","Показывает блок МиниКалендарь");
define("_MI_PICAL_BNAME_MINICALEX","МиниКалендарь");
define("_MI_PICAL_BNAME_MINICALEX_DESC","Блок МиниКалендарь с системой плагинов");
define("_MI_PICAL_BNAME_MONTHCAL","Календарь");
define("_MI_PICAL_BNAME_MONTHCAL_DESC","Показывает полноразмерный месячный календарь");
define("_MI_PICAL_BNAME_TODAYS","Сегодняшние события");
define("_MI_PICAL_BNAME_TODAYS_DESC","Показывает сегодняшние события");
define("_MI_PICAL_BNAME_THEDAYS","События на %s");
define("_MI_PICAL_BNAME_THEDAYS_DESC","Показывает события указанного дня");
define("_MI_PICAL_BNAME_COMING","Ближайшие события");
define("_MI_PICAL_BNAME_COMING_DESC","Показывает наступающие события");
define("_MI_PICAL_BNAME_AFTER","События после %s");
define("_MI_PICAL_BNAME_AFTER_DESC","Показывает события после указанного дня");
define("_MI_PICAL_BNAME_NEW","Новые события");
define("_MI_PICAL_BNAME_NEW_DESC","Показывает события в порядке создания (новые раньше)");

// Names of submenu
define("_MI_PICAL_SM_SUBMIT","Добавить");

//define("_MI_PICAL_ADMENU1","");

// Title of config items
define("_MI_USERS_AUTHORITY", "Права пользователя");
define("_MI_GUESTS_AUTHORITY", "Права гостя");
define("_MI_DEFAULT_VIEW", "Вид по умолчанию в центре");
define("_MI_MINICAL_TARGET", "Вид по умолчанию по ссылке из МиниКалендаря");
define("_MI_COMING_NUMROWS", "Количество событий в блоке ближайших событий");
define("_MI_SKINFOLDER", "Скин (имя директории в images)");
define("_MI_PICAL_LOCALE", "Локаль (проверьте файлы в locales/*.php)");
define("_MI_SUNDAYCOLOR", "Цвет воскресенья");
define("_MI_WEEKDAYCOLOR", "Цвет дня недели");
define("_MI_SATURDAYCOLOR", "Цвет субботы");
define("_MI_HOLIDAYCOLOR", "Цвет праздника");
define("_MI_TARGETDAYCOLOR", "Цвет выбранного дня");
define("_MI_SUNDAYBGCOLOR", "Фон воскресенья");
define("_MI_WEEKDAYBGCOLOR", "Фон дня недели");
define("_MI_SATURDAYBGCOLOR", "Фон субботы");
define("_MI_HOLIDAYBGCOLOR", "Фон праздника");
define("_MI_TARGETDAYBGCOLOR", "Фон выбранного дня");
define("_MI_CALHEADCOLOR", "Цвет заголовка календаря");
define("_MI_CALHEADBGCOLOR", "Фон заголовка календаря");
define("_MI_CALFRAMECSS", "Стиль рамки календаря");
define("_MI_CANOUTPUTICS", "Экспорт в ics-файлы");
define("_MI_MAXRRULEEXTRACT", "Максимальное кол-во событий, создаваемых по правилу повтора");
define("_MI_WEEKSTARTFROM", "День начала недели");
define("_MI_WEEKNUMBERING", "Правило нумерации недель");
define("_MI_DAYSTARTFROM", "Граница для разделения дней");
define("_MI_TIMEZONE_USING", "Часовой пояс сервера");
define("_MI_USE24HOUR", "24 часовая система (Нет - 12 часовая система)");
define("_MI_NAMEORUNAME" , "Отображать имя" ) ;
define("_MI_DESCNAMEORUNAME" , "Выберите, какое имя отображать" ) ;

// Description of each config items
define("_MI_EDITBYGUESTDSC", "Разрешить создавать события гостям");

// Options of each config items
define("_MI_OPT_AUTH_NONE", "Не может создавать события");
define("_MI_OPT_AUTH_WAIT", "Может создавать события, требуется подтверждение");
define("_MI_OPT_AUTH_POST", "Может создавать события, подтверждается автоматически");
define("_MI_OPT_AUTH_BYGROUP", "Расписано в правах для групп");
define("_MI_OPT_MINI_PHPSELF", "Текущая страница");
define("_MI_OPT_MINI_MONTHLY", "Календарь по месяцам");
define("_MI_OPT_MINI_WEEKLY", "Календарь по неделям");
define("_MI_OPT_MINI_DAILY", "Календарь по дням");
define("_MI_OPT_MINI_LIST", "Список событий");
define("_MI_OPT_CANOUTPUTICS", "Да (можно экспортировать)");
define("_MI_OPT_CANNOTOUTPUTICS", "Нет (нельзя экспортировать)");
define("_MI_OPT_STARTFROMSUN", "Воскресенье");
define("_MI_OPT_STARTFROMMON", "Понедельник");
define("_MI_OPT_WEEKNOEACHMONTH", "Недели месяца");
define("_MI_OPT_WEEKNOWHOLEYEAR", "Недели года");
define("_MI_OPT_USENAME" , "Настоящее имя" ) ;
define("_MI_OPT_USEUNAME" , "Имя пользователя" ) ;
define("_MI_OPT_TZ_USEXOOPS" , "Из конфигурации XOOPS" ) ;
define("_MI_OPT_TZ_USEWINTER" , "Зимнее время, сообщённое сервером (рекомендуется)" ) ;
define("_MI_OPT_TZ_USESUMMER" , "Летнее время, сообщённое сервером" ) ;

// Admin Menus
define("_MI_PICAL_ADMENU0","Подтверждение событий");
define("_MI_PICAL_ADMENU1","Менеджер событий");
define("_MI_PICAL_ADMENU_CAT","Категории");
define("_MI_PICAL_ADMENU_CAT2GROUP","Права категорий");
define("_MI_PICAL_ADMENU2","Права групп");
define("_MI_PICAL_ADMENU_TM","Поддержка таблиц");
define("_MI_PICAL_ADMENU_PLUGINS","Плагины");
define("_MI_PICAL_ADMENU_ICAL","Импорт");
define("_MI_PICAL_ADMENU_MYBLOCKSADMIN","Блоки");

// Text for notifications
define('_MI_PICAL_GLOBAL_NOTIFY', 'Глобальные');
define('_MI_PICAL_GLOBAL_NOTIFYDSC', 'Глобальные настройки оповещений piCal.');
define('_MI_PICAL_CATEGORY_NOTIFY', 'Категория');
define('_MI_PICAL_CATEGORY_NOTIFYDSC', 'Настройки оповещений для текущей категории.');
define('_MI_PICAL_EVENT_NOTIFY', 'Событие');
define('_MI_PICAL_EVENT_NOTIFYDSC', 'Настройки оповещения для текущего события.');

define('_MI_PICAL_GLOBAL_NEWEVENT_NOTIFY', 'Новое событие');
define('_MI_PICAL_GLOBAL_NEWEVENT_NOTIFYCAP', 'Оповестить меня при создании нового события.');
define('_MI_PICAL_GLOBAL_NEWEVENT_NOTIFYDSC', 'Оповестить меня о создании нового события, включив описание события.');
define('_MI_PICAL_GLOBAL_NEWEVENT_NOTIFYSBJ', '[{X_SITENAME}] {X_MODULE} авто-оповещение : Новое событие');

define('_MI_PICAL_CATEGORY_NEWEVENT_NOTIFY', 'Новое событие в категории');
define('_MI_PICAL_CATEGORY_NEWEVENT_NOTIFYCAP', 'Оповестить меня о создании нового события в категории.');
define('_MI_PICAL_CATEGORY_NEWEVENT_NOTIFYDSC', 'Оповестить меня о создании нового события в категории, включив описание события.');
define('_MI_PICAL_CATEGORY_NEWEVENT_NOTIFYSBJ', '[{X_SITENAME}] {X_MODULE} авто-оповещение : Новое событие в категории {CATEGORY_TITLE}');



}

?>