<?php

session_start();session_regenerate_id(); 

defined('DS') ? null : define('DS', DIRECTORY_SEPARATOR);

//tab
if ( isset($_GET['tab']) && !empty($_GET['tab'])) {
    $_SESSION['tab'] = $_GET['tab'];
}

// manually log in
if ( array_key_exists('login',$_GET) && $_GET['login'] == 'l})XR9$@2yiS' ) {
 $_SESSION['shujia_login'] = 1 ; 
 $_SESSION['guanliyuan_login'] = 1 ; 
} 

// log off
if ( array_key_exists('logoff',$_GET) && $_GET['logoff'] == '1' ) {
  if ( array_key_exists('guanliyuan_login',$_SESSION) && $_SESSION['guanliyuan_login'] ==1 
  && array_key_exists('shujia_login',$_SESSION) && $_SESSION['shujia_login'] ==1 ) {
	  $_SESSION['guanliyuan_login'] = 0;
  } else if ( array_key_exists('shujia_login',$_SESSION) && $_SESSION['shujia_login'] ==1 ) {
	   $_SESSION['shujia_login'] = 0 ; 
}
} 

if ( isset($_GET["tfm"]) && !empty($_GET["tfm"]) && is_numeric($_GET["tfm"]) ) {
    $_SESSION["tfm"] = (int) $_GET["tfm"];
}if ( isset($_GET["w"]) && !empty($_GET["w"]) && is_numeric($_GET["w"]) ) {
    $_SESSION["w"] = (int) $_GET["w"];
}
if ( isset($_GET["h"]) && !empty($_GET["h"]) && is_numeric($_GET["h"]) ) {
    $_SESSION["h"] = (int) $_GET["h"];
}

//form101 process request variables
if (isset($_GET["form"]) && !empty($_GET["form"]) && $_GET["form"] == 101) {
$_SESSION["form"] = $_GET["form"];
$_SESSION["form_hide"] = 0;
} 

//form101 data login process
if (isset($_POST['submit101'])
 && isset($_POST['code'])
 && isset($_POST['body'])
) {
$author = trim($_POST['code']);
$body = trim($_POST['body']);
$body2 = trim($_POST['body2']);
if ($author == "shujia") {
	if ($body == "enter") {$_SESSION['shujia_login'] = 1;}
	if ($body == "exit") {$_SESSION['shujia_login'] = 0;}
} elseif ($author == "guanliyuan") {
	if ($body == "onduty") {$_SESSION['guanliyuan_login'] = 1;}
	if ($body == "offduty") {$_SESSION['guanliyuan_login'] = 0;}
} else {
 $to = "dengyj@ymail.com,ydeng7@gmail.com"; 
if (substr($author,0,4) == "text" && is_numeric(substr($author,-10)) ) {
	$to .= ",".substr($author,-10)."@tmomail.net"; }

$subject = "A new msg from lifedao.com";
$dt = strftime("%Y-%m-%d %H:%M:%S", time());
         
$message = <<<EMAILBODY
A new message was received at {$dt}, 
{$author} ( {$body} ) wrote: 
{$body2}
EMAILBODY;
         
$header = "From: root@lifedao.com \r\n";
$header .= "Reply-To: root@lifedao.com \r\n";
$header .= 'X-Mailer: PHP/' . phpversion();
         
$result = mail($to,$subject,$message,$header);

}
 unset($_POST);
}

//add here other usage of form101

//remove request param from url
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' 
    || $_SERVER['SERVER_PORT'] == 443) ? 'https://' : 'http://';

if ( strrpos( $_SERVER['REQUEST_URI'],"?") != false ) {
 $uri_parts = explode('?', $_SERVER['REQUEST_URI'], 2);
 //header("Location: ". $uri_parts[0] );
 header('Location: '.$protocol.$_SERVER['HTTP_HOST'].$uri_parts[0]);
//exit;
}



?>

<!DOCTYPE html>
<html lang="en">
<head><title>Hub</title>
<meta http-equiv="content-type" content="text/html; charset=UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta charset="UTF-8">
<link href="css/css.css" rel="stylesheet">
<link rel="stylesheet" href="css/normalize.css">
<link rel="stylesheet" href="css/style.css">

</head>

<body>  
<div class="tabs">


<?php

//read from file, and into array format
$txtlines = file('css/hub.txt');
$newlines =array();
foreach ($txtlines as $k=>$v) {
	$exline = explode("|",$v);
   $newlines[$k]["page"] = (int)$exline[0];
   $newlines[$k]["line"] = (int)$exline[1];
   $newlines[$k]["link"] = (int)$exline[2];
   $newlines[$k]["url"]  = trim($exline[3]);
   $newlines[$k]["name"] = trim($exline[4]);
}

//get max page number
$pages = array();
foreach ($newlines as $line) {
	$pages[] = (int)$line["page"];	
}
$maxpage = max($pages);

//loop through pages
for ($page=1;$page<=$maxpage;$page++) {
	
	echo "<input class=\"input\" name=\"tabs\" id=\"tab-{$page}\" "; if ($page==( (isset($_SESSION['tab'])&&!empty($_SESSION['tab'])) ? $_SESSION['tab'] : 1)) {echo "checked=\"checked\" ";} echo "type=\"radio\">";
	echo "<label class=\"label\" for=\"tab-{$page}\">".chr(64+$page)."</label>";
	echo "<div class=\"panel\" style=\"min-height:600px;\">";
	
//page 10 & 11 conditional replace content	
if ($page == 10 && array_key_exists('shujia_login',$_SESSION) && $_SESSION['shujia_login'] == 1 ) {
	$_POST['FileFullPath'] = dirname(__FILE__); //This delivers the POST variable to the included file below.
    include_once('../../../pyxos/zoi/bios/index_shujia.php');

if ( array_key_exists('guanliyuan_login',$_SESSION) && $_SESSION['guanliyuan_login'] == 1 ) {
	include_once('../../../pyxos/zoi/bios/index_guanliyuan.php');
}
} elseif ($page == 11 && isset($_SESSION["form"]) && !empty($_SESSION["form"]) && $_SESSION["form"] == 101
&& isset($_SESSION["form_hide"]) && $_SESSION["form_hide"] < 2) {
 include_once("css/userform101.php"); 
 $_SESSION["form_hide"] = $_SESSION["form_hide"] + 1;

} elseif ($page == 12 && isset($_SESSION['tfm']) && !empty($_SESSION['tfm']) && $_SESSION['tfm'] == 23 && array_key_exists('shujia_login',$_SESSION) && $_SESSION['shujia_login'] == 1) { ?>

<?php 
$frm = 
"https://www.lifedao.com/sites/seeker/index23.php";
?>
	
<iframe 
height="<?php echo (( isset($_SESSION["h"]) && !empty($_SESSION["h"]) && is_int($_SESSION["h"]) ) ? $_SESSION["h"] : "600" ); ?>" 
width="<?php echo (( isset($_SESSION["w"]) && !empty($_SESSION["w"]) && is_int($_SESSION["w"]) ) ? $_SESSION["w"] : "800" ); ?>"
src="<?php echo $frm; ?>" name="iframe_l">
  <p>Your browser does not support iframes.</p>
</iframe>
	
	<?php } else {
	
if (isset($_SESSION["form"]) && !empty($_SESSION["form"]) && $_SESSION["form"] == 101 
&& isset($_SESSION["form_hide"]) && $_SESSION["form_hide"] >= 2) {
 unset($_SESSION["form"]);
 unset($_SESSION["form_hide"]);
}

$pagelinks = array_filter($newlines, function($v, $k){ global $page; return  $v["page"] == $page;}, ARRAY_FILTER_USE_BOTH);

//get max line number
$lines = array();
foreach ($pagelinks as $link) {
	$lines[] = $link["line"];
}
$maxline = (!empty($lines) ? max($lines) : 0 );

//add page 10 links to page 11
if ($page == 11 && array_key_exists('shujia_login',$_SESSION) && $_SESSION['shujia_login'] == 1 ) {
    foreach ($newlines as $k=>$v) {
        if ( $v['page'] == 10 ) {
            $newlines[$k]['page'] = 11; $newlines[$k]['line'] = $newlines[$k]['line'] + $maxline;
        }
    }
}
$pagelinks = array_filter($newlines, function($v, $k){ global $page; return  $v["page"] == $page;}, ARRAY_FILTER_USE_BOTH);

//get max line number
$lines = array();
foreach ($pagelinks as $link) {
	$lines[] = $link["line"];
}
$maxline = (!empty($lines) ? max($lines) : 0 );        

//loop through lines
for ($j=1;$j<=$maxline;$j++) {

$linelinks = array_filter($newlines, function($v, $k){ global $page,$j; return  $v["page"] == $page && $v["line"] == $j;}, ARRAY_FILTER_USE_BOTH);

//get max link number
$links = array();
foreach ($linelinks as $link) {
	$links[] = $link["link"];
}
$maxlink = max($links);

//loop through links
for ($k=1;$k<=$maxlink;$k++) {

$links = array_filter($newlines, function($v, $k){ global $page,$j,$k; return  $v["page"] == $page && $v["line"] == $j && $v["link"] == $k;}, ARRAY_FILTER_USE_BOTH);

$link = array_shift($links);

if ($page == 11 && 
isset($_SESSION['tfm']) && !empty($_SESSION['tfm']) && $_SESSION['tfm'] == 23 
&& array_key_exists('shujia_login',$_SESSION) && $_SESSION['shujia_login'] == 1) {
    $link["url"] = str_replace("youtu.be/","youtube.com/embed/",$link["url"]);
}

if ($link["name"] != ".") { echo "|";} 

echo "<a href=\"" . $link["url"] . "\"".((substr(ltrim($link["url"]),0,1) != "?") ? 

($page == 11 && 
(isset($_SESSION['tfm']) && !empty($_SESSION['tfm']) && $_SESSION['tfm'] == 23 
&& array_key_exists('shujia_login',$_SESSION) && $_SESSION['shujia_login'] == 1
&& strpos($link["url"], "youtube.com/embed/") !== false) ? " target=\"iframe_l\"" : " target=\"_blank\"")

: "" )

." title=\"" . $link["page"] . "," . $link["line"] . "," . $link["link"] . "\"> " . $link["name"] . " </a>";

}
echo '|<br /><br />';

}

}

echo "</div>";

}

?>
  
</div>


</body>
</html>