<?php
/*
$aws_flag = if not null append AWS link
$lng_flag = if not null append language code to link
$path = a variable found in the config file that represents a partial pathway to the style sheet, not including and details about AWS, language code, or language direction)
$dir_flag = if not null append language direction to link
$ver_numb, this is very helpful when trying to update files like css and js that don't get called by serviceWorker after they are stored. (empty = do not append '?v=some_number' to the URL.)
Example: {CCMS_LIB:examples/sample.php;FUNC:example_build_css_link("","","CSS-01","1", "1")}
*/
//function example_build_css_link($aws_flag = null, $lng_flag = null, $path, $dir_flag = null, $ver_numb = null){
function example_build_css_link($aws_flag, $lng_flag, $path, $dir_flag, $ver_numb){
	global $CFG;
	// If $path is not found in the config.php file then do nothing.
	if(!isset($CFG["RES"][$path])) return;
	//$buff = 'var l=document.createElement("link");l.rel="stylesheet";l.nonce="' . csp_nounce_ret() . '";l.href="';
	$buff = 'var l=document.createElement("link");l.rel="stylesheet";l.href="';
	$url = "";
	if($aws_flag){
		if($CFG["RES"]["AWS"]){
			$url .= $CFG["RES"]["AWS"];
		}
	}
	// We do this for safety to help just incase the script calling this function requests the AWS code and the language code by accident.  We never ask for language code ones things are located on AWS.
	if($lng_flag){
		if(!$aws_flag){
			$url .= "/" . ccms_lng_ret();
		}
	}
	$url .= $CFG["RES"][$path];
	if($dir_flag){
		$url .= "-" . ccms_lng_dir_ret();
	}
	$url .= '.css';
	if($ver_numb){
		$url .= "?v=" . $ver_numb;
	}
	$buff .= $url . '";';
	/*
	if($aws_flag){
		$qry = $CFG["DBH"]->prepare("SELECT * FROM `sri` WHERE `url` = :url LIMIT 1;");
		$qry->execute(array(':url' => $url));
		$row = $qry->fetch(PDO::FETCH_ASSOC);
		if($row){
			$buff .= 'l.integrity="sha256-' . $row["sri-code"] . '";';
		}else{
			$tmp = file_get_contents($url);
			$result = base64_encode(hash("sha256", $tmp, true));
			$qry = $CFG["DBH"]->prepare("INSERT INTO `sri` (`id`, `url`, `sri-code`) VALUES (NULL, :url, :result);");
			$qry->execute(array(':url' => $url, ':result' => $result));
			$buff .= 'l.integrity="sha256-' . $result . '";';
		}
		$buff .= 'l.crossOrigin="anonymous";';
	}
	*/
	echo $buff .= 'var h=document.getElementsByTagName("head")[0];h.parentNode.insertBefore(l,h);';
}


/*
$aws_flag = if not null append AWS link.
$lng_flag = if not null append language code to link.
$path = a variable found in the config file that represents a partial pathway to the style sheet. (Not including details about AWS, language code, or language direction.)
$dir_flag = if not null append language direction to link
$ver_numb, this is very helpful when trying to update files like css and js that don't get called by serviceWorker after they are stored. (empty = do not append '?v=some_number' to the URL.)
Example: {CCMS_LIB:examples/sample.php;FUNC:example_build_js_link("","","JS-01","","1")}
*/
//function example_build_js_link($aws_flag = null, $lng_flag = null, $path, $dir_flag = null, $ver_numb = null){
function example_build_js_link($aws_flag, $lng_flag, $path, $dir_flag, $ver_numb){
	global $CFG;
	// If $path is not found in the config.php file then do nothing.
	if(!isset($CFG["RES"][$path])) return;
	$url = "";
	if($aws_flag){
		if($CFG["RES"]["AWS"]){
			$url .= $CFG["RES"]["AWS"];
		}
	}
	// We do this for safety to help just incase the script calling this function requests the AWS code and the language code by accident.  We never ask for language code ones things are located on AWS.
	if($lng_flag){
		if(!$aws_flag){
			$url .= "/" . ccms_lng_ret();
		}
	}
	$url .= $CFG["RES"][$path];
	if($dir_flag){
		$url .= "-" . ccms_lng_dir_ret();
	}
	$url .= '.js';
	if($ver_numb){
		$url .= "?v=" . $ver_numb;
	}
	echo $url;
}


/*
$aws_flag = if not null append AWS link.
$path = a variable found in the config file that represents a partial pathway to the style sheet. (Not including details about AWS, language code, or language direction.)
*/
//function example_build_js_sri($aws_flag = null, $path){
function example_build_js_sri($aws_flag, $path){
	/*
	global $CFG;

	if(!isset($CFG["RES"][$path])) return;

	$buff = ",'";
	$url = "";

	if(isset($aws_flag)){
		if($CFG["RES"]["AWS"]){
			$url .= $CFG["RES"]["AWS"] . $CFG["RES"][$path];
		} else {
			$url .= "." . $CFG["RES"][$path];
		}
	} else {
		$url .= "." . $CFG["RES"][$path];
	}
	$qry = $CFG["DBH"]->prepare("SELECT * FROM `sri` WHERE `url` = :url LIMIT 1;");
	$qry->execute(array(':url' => $url));

	$row = $qry->fetch(PDO::FETCH_ASSOC);
	if($row) {
		echo $buff .= "sha256-" . $row["sri-code"] . "','anonymous'";
	}else{
		$tmp = file_get_contents($url);
		$result = base64_encode(hash("sha256", $tmp, true));
		$qry = $CFG["DBH"]->prepare("INSERT INTO `sri` (`id`, `url`, `sri-code`) VALUES (NULL, :url, :result);");
		$qry->execute(array(':url' => $url, ':result' => $result));
		echo $buff .= "sha256-" . $result . "','anonymous'";
	}
	*/
}


function example_lngList_1() {
	global $CFG, $CLEAN;
	$tpl = htmlspecialchars(preg_replace('/^\/([\pL\pN-]*)\/?(.*)\z/i', '${2}', $_SERVER['REQUEST_URI']));
	$qry = $CFG["DBH"]->prepare("SELECT * FROM `ccms_lng_charset` WHERE `status` = 1 ORDER BY lngDesc ASC;");
	if($qry->execute()) {
		while($row = $qry->fetch()) {
			if($row["ptrLng"]) {
				echo "<li id=\"lng-" . $row["lng"] . "\"><a href=\"/" . $row["ptrLng"] . "/#a" . $tpl . "\">" . $row["lngDesc"] . "</a></li>\n";
			} else {
				echo "<li id=\"lng-" . $row["lng"] . "\"><a href=\"/" . $row["lng"] . "/#a" . $tpl . "\">" . $row["lngDesc"] . "</a></li>\n";
			}
		}
	}
}


function example_lngList_2() {
	global $CFG, $CLEAN;
	$tpl = htmlspecialchars(preg_replace('/^\/([\pL\pN-]*)\/?(.*)\z/i', '${2}', $_SERVER['REQUEST_URI']));
	$qry = $CFG["DBH"]->prepare("SELECT * FROM `ccms_lng_charset` WHERE `status` = 1 ORDER BY lngDesc ASC;");
	if($qry->execute()) {
		while($row = $qry->fetch()) {
			if($row["ptrLng"]) {
				echo "<li id=\"lng-" . $row["lng"] . "\"><a href=\"/" . $row["ptrLng"] . "/" . $tpl . "\">" . $row["lngDesc"] . "</a></li>\n";
			} else {
				echo "<li id=\"lng-" . $row["lng"] . "\"><a href=\"/" . $row["lng"] . "/" . $tpl . "\">" . $row["lngDesc"] . "</a></li>\n";
			}
		}
	}
}
