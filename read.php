<?php
require "include.php";

// Report fatal errors.
function fancydie($m) { global $shiiversion; ?><title>Forums Fatal Error</title>
<style type="text/css">img { float:right } * { font-family: Tahoma,sans-serif }
h1 { border: 2px solid #faf; background: #fdf; font-size: medium }</style>
<h1>Fatal error! Thread could not be fetched.</h1><?=$m?>
<hr>Powered by Shiichan <?=$shiiversion?><?php exit; }

if ($_SERVER[REQUEST_METHOD] != 'GET') fancydie('I POSTed your mom in the ass last night.');

// settings file
$glob = file("globalsettings.txt") or fancydie("Eh? Couldn't fetch the global settings file?!");
foreach ($glob as $tmp){  $tmp = trim($tmp);   list ($name, $value) = explode("=", $tmp);  $setting[$name] = $value;  }


if($_SERVER[PATH_INFO]){
        $pairs = explode('/',$_SERVER[PATH_INFO]);
        $bbs = $pairs[1];
$local = @file("$bbs/localsettings.txt");
if ($local) { foreach ($local as $tmp){  $tmp = trim($tmp);   list ($name, $value) = explode("=", $tmp);  $setting[$name] = $value;  } }
        $key = $pairs[2];
if (!$pairs[3]) {$posts = array("1-"); $st = 1; $to = $setting[postsperpage]; }
	else {
	     $posts = explode(',',$pairs[3]);
		 }
}
// some errors
if (!$bbs) fancydie("You didn't specify a BBS.");
if (!$key) fancydie("You didn't specify a thread to read.");
if (!file_exists("$bbs/dat/$key.dat")) fancydie('That thread or board does not exist.');

// go for it!
echo PrintThread($bbs, $key, $posts, true);
?>
