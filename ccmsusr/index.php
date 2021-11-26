<?php
// benchmark start
//$time_start = microtime(true);

// Use the ini_set function to set value of the include_path option on your server if necessary.
// e.g.: ini_set('include_path', 'ccmslib:ccmspre:ccmstpl:ccmsusr' . ini_get('include_path'));
//ini_set('include_path', $CFG["LIBDIR"] . ':' . $CFG["PREDIR"] . ':' . $CFG["TPLDIR"] . ':' . $CFG["USRDIR"] . ':' . ini_get('include_path'));

$CFG = array();
$CLEAN = array();

// Necessary to solve a problem on GoDaddy servers when running sites found in sub folders of existing sites.
if($_SERVER["REAL_DOCUMENT_ROOT"]) {
	$_SERVER["DOCUMENT_ROOT"] = $_SERVER["REAL_DOCUMENT_ROOT"];
}

if(file_exists($_SERVER["DOCUMENT_ROOT"] . "/ccms-setup.php")) {
	require_once($_SERVER["DOCUMENT_ROOT"] . "/ccms-setup.php");
	exit;
}

require_once $_SERVER["DOCUMENT_ROOT"] . "/ccmspre/config.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/ccmspre/index.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/ccmspre/whitelist_user.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/ccmslib/_default.php";

ob_start("ob_gzhandler");

$CFG["TPLDIR"] = $CFG["USRDIR"];
$CFG["INDEX"] = $CFG["USRINDEX"];

// This creats a persistent connection to MySQL.
CCMS_DB_First_Connect();

CCMS_Filter($_SERVER + $_REQUEST, $ccms_whitelist);

CCMS_User_Filter($_SERVER + $_REQUEST, $whitelist);

CCMS_Set_SESSION();

if(!isset($_SESSION["USER_ID"]) || $CLEAN["logout"] == "1" || $CLEAN["login"] == "1") {
	$CLEAN["ccms_tpl"] = "login";
}

CCMS_Main();

// benchmark end
//echo 'Total execution time in seconds: ' . (microtime(true) - $time_start);
