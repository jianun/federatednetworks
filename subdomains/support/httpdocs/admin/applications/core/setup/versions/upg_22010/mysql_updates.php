<?php
/*
+--------------------------------------------------------------------------
|   IP.Board v3.1.2
|   ========================================
|   by Matthew Mecham
|   (c) 2001 - 2004 Invision Power Services
|   http://www.invisionpower.com
|   ========================================
|   Web: http://www.invisionboard.com
|   Email: matt@invisionpower.com
|   Licence Info: http://www.invisionboard.com/?license
+---------------------------------------------------------------------------
*/


# Nothing of interest!

// $SQL[] = "";

$SQL[] = "ALTER TABLE forums CHANGE last_title last_title varchar(128) NOT NULL default '';";
$SQL[] = "ALTER TABLE forums CHANGE last_id last_id int(10) NOT NULL default '0';";
$SQL[] = "UPDATE components SET com_title='AddOnChat' WHERE com_section='chatsigma';";

