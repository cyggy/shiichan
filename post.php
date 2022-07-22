<?php
/* Shiichan 4000
** Add post
*/

require "include.php";

// basic security measures-- don't leave home without 'em!
if(get_magic_quotes_gpc()) $_POST = array_map("stripslashes", $_POST);
$_POST = array_map("htmlspecialquotes", $_POST);
$_COOKIE = array_map("htmlspecialquotes", $_COOKIE);

// Generate the date
$thisverysecond = time();

$glob = file("globalsettings.txt") or fancydie("Eh? Couldn't fetch the global settings file?!");
foreach ($glob as $tmp) {  $tmp = trim($tmp);   list ($name, $value) = explode("=", $tmp);  $setting[$name] = $value;  }
$_POST[bbs] ? $lol = $_POST[bbs] : $lol = $_GET[bbs];
$local = @file("$lol/localsettings.txt");
if ($local) foreach ($local as $tmp){  $tmp = trim($tmp);   list ($name, $value) = explode("=", $tmp);  $setting[$name] = $value;  }

// mrvacbob 04-2009
if ($_POST[subj]) $_POST[id] = $thisverysecond;

// If we're getting called to write a post, go for it.
if ($_GET[shiichan] == "writenew") {
	$second = time();
	if ($setting[posticons]) {
	$icons = "<input type='radio' name='icon' value='noicon.png' checked> No icon<br>"; $i = 0;
	$handle = opendir("posticons");
	while (false !== ($file = readdir($handle))) {
		if ($file != "." && $file != ".." && $file != "noicon.png") {
		if ($i == 6) { $icons .= "<br>"; $i = 0; }
		$icons .= "<input type='radio' name='icon' value='$file'><img src='posticons/$file'> ";
		$i++;
		}}
    closedir($handle);
	$icons .= "<br>The following posticons are for <b>admin use only</b>:<br>";
	$i = 0; 
	$handle = opendir("capcodes/icons");
	while (false !== ($file = readdir($handle))) {
		if ($file != "." && $file != "..") {
		if ($i == 6) { $icons .= "<br>"; $i = 0; }
		$icons .= "<input type='radio' name='icon' value='../capcodes/icons/$file'><img src='capcodes/icons/$file'> ";
		$i++;
		}}
    closedir($handle);
	} else $icons = "<input type='hidden' name='icon' value='noicon.png'>Posticons are disabled.";
	$html = file_get_contents("skin/$setting[skin]/addthread.txt");
        $html = str_replace("<%THREADNAME%>", $threadname, $html);
	if ($setting[encoding] == "sjis") $html = str_replace("<%ENCODING%>", "<META http-equiv='Content-Type' content='text/html; charset=Shift_JIS'><style>* { font-family: Mona,'MS PGothic' !important } </style>", $html);
	else $html = str_replace("<%ENCODING%>", "<META http-equiv='Content-Type' content='text/html; charset=UTF-8'>", $html);
        $html = str_replace("<%FORUMURL%>", $setting[urltoforum], $html);
        $html = str_replace("<%POSTICONS%>", $icons, $html);
        $html = str_replace("<%FORUMNAME%>", $setting[forumname], $html);
	$html = str_replace("<%BOARDNAME%>", $setting[boardname], $html);
	$html = str_replace("<%BOARDURL%>", $_GET[bbs], $html);
	if ($_COOKIE[adminname]) $html=str_replace("<%NAMECOOKIE%>", "value='$_COOKIE[adminname]'", $html); else $html=str_replace("<%NAMECOOKIE%>", "", $html);
	if ($setting[adminsonly]) $html=str_replace("<%ADMINSONLY%>", "<h2 style='background:none;color:red'>Only administrators can post threads to this forum!</h2>", $html); else $html=str_replace("<%ADMINSONLY%>", "", $html);
        $html = str_replace("<%STARTFORM%>", "<form name='post' action='post.php' method='POST'><input type='hidden' name='bbs' value='$_GET[bbs]'><input type='hidden' name='id' value='$second'><input type='hidden' name='shiichan' value='proper'>", $html);
	$html = str_replace("<%TEXTAREA%>", "<textarea rows='15' cols='75' name='mesg'></textarea><br><input type='submit' value='Create Thread'>", $html);
	echo $html;
	exit;
}

// If we're being called to write an advanced reply, write the advanced reply dammit.
if ($_GET[id]) {
        $thread = file("$_GET[bbs]/dat/$_GET[id].dat") or fancydie("Couldn't open that thread");
        list ($threadname, $author, $lastposted) = explode("<=>", $thread[0]);
	$html = file_get_contents("skin/$setting[skin]/addreply.txt");
	if ($_COOKIE[adminname]) $html=str_replace("<%NAMECOOKIE%>", "value='$_COOKIE[adminname]'", $html); else $html=str_replace("<%NAMECOOKIE%>", "", $html);
	if (!is_writable("$_GET[bbs]/dat/$_GET[id].dat")) $html=str_replace("<%THREADSTOPPED%>", "<h3>This thread is threadstopped!!</h3>", $html); else $html=str_replace("<%THREADSTOPPED%>", "", $html);
        $html = str_replace("<%THREADNAME%>", $threadname, $html);
        $html = str_replace("<%FORUMNAME%>", $setting[forumname], $html);
	$html = str_replace("<%BOARDURL%>", $_GET[bbs], $html);
	$html = str_replace("<%THREADID%>", $_GET[id], $html);
        $html = str_replace("<%FORUMURL%>", $setting[urltoforum], $html);
	$html = str_replace("<%BOARDNAME%>", $setting[boardname], $html);
        $html = str_replace("<%STARTFORM%>", "<form name='post' action='post.php' method='POST'><input type='hidden' name='bbs' value='$_GET[bbs]'><input type='hidden' name='id' value='$_GET[id]'><input type='hidden' name='shiichan' value='proper'>", $html);
	$html = str_replace("<%TEXTAREA%>", "<textarea rows='15' cols='75' name='mesg'></textarea><br><input type='submit' value='Add Reply'> <input name='sage' type='checkbox'> Sage?", $html);
	echo $html; exit;
} else if ($_GET[bbs]) {
echo "go fuck yourself"; exit;
}


###########################
// AND AWAYYYY WE GOOO!!!!


// check for ban
$file = @file("bans.cgi");
if ($file) foreach($file as $line) {
list ($ip, $reason, $unused, $unused) = explode("<>", $line);
if (strstr($_SERVER[REMOTE_ADDR], $ip)) fancydie("<b>You have been banned from this message board.</b><p>The moderation team supplied this reason: <b>$reason</b>");
}

// check for flood
$file = @file_get_contents("temp/$_SERVER[REMOTE_ADDR].flood");
if ($file) if ($file + 5 > time()) fancydie("Please wait at least 5 seconds between posts!<p>You may have recieved this message from submitting your post more than once. Don't submit it again.");

// Report fatal errors.
function fancydie($m) { global $shiiversion; ?><title>Forums Fatal Error</title>
<style type="text/css">img { float:right } * { font-family: Tahoma,sans-serif }
h1 { border: 2px solid #faf; background: #fdf; font-size: medium }</style>
<h1>Fatal error! Message could not be posted.</h1><?=$m?><hr><?=$_POST[mesg]?>
<hr>Powered by Shiichan <?=$shiiversion?><?php exit; }

// ENT_QUOTES thingy
function htmlspecialquotes($st) { 
return str_replace("&amp;#", "&#", htmlspecialchars("$st", ENT_QUOTES)); }

/* link shorten

function shorten($str){
  if(strlen($str) > 50) {
    $divide = round(strlen($str) / 3);
    if ($divide*2 > 50) {
      $divide = round(strlen($str) / 5);
     $second_string = substr($str,$divide*4,200);
    } else {
        $second_string = substr($str,$divide*2,200);
    }
   $first_string = substr($str,0,$divide);
   $short_string = $first_string . "..." . $second_string;
   $short_string = htmlspecialchars($short_string, ENT_NOQUOTES);
    } else {
 $short_string = $str;
 }
 return $short_string;
}*/

// Check for POST and no in-forums spoofing
if($_SERVER[REQUEST_METHOD]!="POST"){fancydie("Trying to GET post.php?<meta http-equiv='refresh' content='0;url=.'>");}

// for capcode functions
$threadstopwhendone = false;
$loggedin = false;

###################
// capcode post
if ($_POST[pass]) {
$admin = file("shadow.cgi"); 
foreach ($admin as $line) { list($name, $pass, $level) = explode("<>", $line);
if (strtolower($_POST[name]) == $name) { if (md5($_POST[pass]) != $pass) fancydie("The password you supplied for that username is incorrect.");
$loggedin = true; break; }} if ($loggedin == false) fancydie("You gave a password but your name doesn't match any registered user.");
$fdc = @file_get_contents("capcodes/$name.txt");
if ($fdc) $_POST[name]= "<b>".$fdc."</b>"; else
$_POST[name] = "<b style='color:#f00'>$name</b>";
$idcrypt = "<b>(capped)</b> ";

if ($level < 7500 && $setting[adminsonly] && $_POST[subj]) fancydie("You need a userlevel of 7500 to start a thread."); // admins-only threads...
if (!$_POST[subj] && !is_writable("$_POST[bbs]/dat/$_POST[id].dat")) {
if ($level < 6500) { fancydie("You need a userlevel of 6500 to reply to this thread."); }
chmod ("$_POST[bbs]/dat/$_POST[id].dat", 0666);
$threadstopwhendone = true;
}
} else {
#############################################
//////////////////// non-capcodes area
// str_replaces
$_POST[mesg] = str_replace("shiichan=proper", " lol what ", $_POST[mesg]);
$_POST[name] = str_replace(array("﹟", "＃", "♯"), "#", $_POST[name]);  //  Unicode spoofs for tripcodes and capcodes

// ID hash 
$idcrypt = " ";
if ($setting[haship]) { $idcrypt .= "ID: " . substr(base64_encode(pack("H*",sha1($_SERVER[REMOTE_ADDR].date("d").SecureSalt()))), 1,8) . " "; }

#### funky tripcode time ###########
# no blank tripcodes plz
if(preg_match("/\#$/", $_POST[name], $match)){
    $_POST[name] = preg_replace("/\#$/", "", $_POST[name]);
}
## ## ## Secure tripcodes courtesy of MrVacBob ## ## ##
# tripcode hashing, 2ch-style and modified Wakaba-style
if (preg_match("/\#/", $_POST[name])) {    
    $_POST[name] = str_replace("&#","&%%%%%%",$_POST[name]); # otherwise HTML numeric entities screw up explode()!
    list ($name,$trip,$sectrip) = str_replace("&%%%%%%", "&#", explode("#",$_POST[name]));
    $_POST[name] = $name;
    
    if ($trip != "") {
        $salt = strtr(preg_replace("/[^\.-z]/",".",substr($trip."H.",1,2)),":;<=>?@[\\]^_`","ABCDEFGabcdef");
        $trip = substr(crypt($trip, $salt),-10);
    }
    
    if ($sectrip != "") {
        $sha = base64_encode(pack("H*",sha1($sectrip.SecureSalt())));
        $sha = substr($sha,0,15);
        $trip .= "#".$sha;
    }
}
# End of tripcode section #############################

if (strlen($_POST[name]) > 30) fancydie("Your name is too damn long!");
// Certain things can only be done by admins.
if (strstr($_POST[icon], "..")) fancydie("When I say 'for admins only' I mean 'for admins only'!"); // admins-only icons...
if ($setting[adminsonly] && $_POST[subj]) fancydie("When I say 'for admins only' I mean 'for admins only'!"); // admins-only threads...
if (!$_POST[subj] && !is_writable("$_POST[bbs]/dat/$_POST[id].dat")) fancydie("You're not allowed to reply to this thread.<br>If you're making a new thread, <b>try entering a subject for it</b> dum-dum."); // threadstops
} // End of non-capcodes-only section ####
##########################################

//// anchor >>1 links
//$_POST[mesg] = preg_replace("/&gt;&gt;([0-9,\-]+)/", "<a href=\"read.php/$_POST[bbs]/$_POST[id]/$1\">&gt;&gt;$1</a>", $_POST[mesg]);
$_POST[mesg] = preg_replace("/&gt;&gt;([\d,lqr-]+),/", "<a href=\"read.php/$_POST[bbs]/$_POST[id]/$1\">>>$1</a>,", $_POST[mesg]);
$_POST[mesg] = preg_replace("/&gt;&gt;([\d,lqr-]+)/", "<a href=\"read.php/$_POST[bbs]/$_POST[id]/$1\">&gt;&gt;$1</a>", $_POST[mesg]);



// linebreaks
$_POST[name] = str_replace (array("\r\n","\r","\n"), " ", $_POST[name]);
$_POST[subj] = str_replace (array("\r\n","\r","\n"), " ", $_POST[subj]);
$_POST[icon] = str_replace (array("\r\n","\r","\n"), " ", $_POST[icon]);

// URL replace
function auto_url($txt){

  # (1) catch those with url larger than 71 characters
    $pat = '/(http|ftp)+(?:s)?:(\\/\\/)'
           .'((\\w|\\.)+)(\\/)?(\\S){71,}/i';
	     $txt = preg_replace($pat, "<a href=\"\\0\" target=\"_blank\">$1$2$3/...</a>",
	     $txt);

	       # (2) replace the other short urls provided that they are not contained inside an html tag already.
	         $pat = '/(?<!href=\")(http|ftp)+(s)?:' .
		      '(\\/\\/)((\\w|\\.)+) (\\/)?(\\S)/i';
		        $txt = preg_replace($pat,"<a href=\"$0\" target=\"_blank\">$0</a> ",
			  $txt);

			    return $txt;
} 

$_POST[mesg] = auto_url($_POST[mesg]);
# # # Here be the quote parsing options. # # # 

// quote matching ... three times! BWAHAHAHAHAHAHAHAH
$_POST[mesg] = preg_replace("/\n&gt; (.+)/i", "\n<span class='quote'>$1</span>", $_POST[mesg]);
$_POST[mesg] = preg_replace("/^&gt; (.+)/i", "<span class='quote'>$1</span>", $_POST[mesg]);
$_POST[mesg] = preg_replace("/<span class='quote'>&gt; (.+)/i", "<span class='quote'><span class='quote'>$1</span>", $_POST[mesg]);
$_POST[mesg] = preg_replace("/<span class='quote'>&gt; (.+)/i", "<span class='quote'><span class='quote'>$1</span>", $_POST[mesg]);

// ABBC
// abbc changes \x01\x02 to <>
// (i guess all other php using it is exploitable)
//			mrvacbob 04-2009
$_POST[mesg] = str_replace(array("\x01","\x02"),"", $_POST[mesg]);
$_POST[mesg] = abbc_proc($_POST[mesg]);
$_POST[mesg] = str_replace (array("\r\n","\r","\n"), "", $_POST[mesg]);



// shiichan check
if ($_POST[shiichan] != "proper") fancydie("Whoever told you to click here is a mean person. Please tell them off.");

if ($_POST[subj]) { $_POST[sage] = ""; }
if ($_POST[sage]) $idcrypt .= "(sage)";

// Length checks
if (strlen($_POST[mesg]) == 0) fancydie("You didn't write a post?!");
if (strlen($_POST[mesg]) > 10000) fancydie("Thanks for your contribution, but it was too large.");
if (strlen($_POST[subj]) > 45) fancydie("Subject is too long!");
if (count(explode("<br>", $_POST[mesg])) > 100) fancydie("Your post has far too many lines in it!");

// check for ID and board
if (!$_POST[bbs]) fancydie("No board specified to post to!");
if (!$_POST[id]) fancydie("No thread ID specified to post to!");
if (!is_dir($_POST[bbs])) fancydie("Board specified does not exist.");
if (!$_POST[subj] && !is_file("$_POST[bbs]/dat/$_POST[id].dat")) fancydie("Thread ID specified does not exist.");
if ($_POST[subj] && is_file("$_POST[bbs]/dat/$_POST[id].dat")) fancydie ("Thread has already been created.");

// Tripcode mohel
if ($_POST[name]) { $censorme = false;
if (file_exists("mohel.cgi")) {
$mohel = file("mohel.cgi") or fancydie("Couldn't open mohel.cgi :(");
foreach ($mohel as $line) {
	$line = trim($line);
	if ($line{0} == '#') {
		if ($line == '#'.$trip) $censorme = true;
	} else {
		if ($line == $_POST[name].'#'.$trip) $censorme = true;
	}
}
if ($censorme == true) { echo "<b>Message from Mohel:</b> Your nickname was censored, for your own good.<p>"; $_POST[name]=""; $trip=''; }
}}

// anonymous, we love you!
if ($_POST[name] == "" && !$trip) $_POST[name] = $setting[nameless];

// It's time to actually write the post.
$handle = fopen("$_POST[bbs]/dat/$_POST[id].dat", "a") or fancydie("Couldn't open the thread .dat file for writing!");
$tobewritten = "$_POST[name]<>$trip<>$posttime<>$_POST[mesg]<>$idcrypt";
$tobewritten = str_replace (array("\r\n","\r","\n"), "", $tobewritten); // do NOT allow linebreaks under penalty of fucking up the post!
if ($_POST[subj]) { 
$_POST[name] ? $namae = $_POST[name] : $namae = '#'.$trip;
fwrite($handle, "$_POST[subj]<=>$namae<=>$_POST[icon]\n"); }
fwrite($handle, "$_POST[name]<>$trip<>$thisverysecond<>$_POST[mesg]<>$idcrypt<>$_SERVER[REMOTE_ADDR]\n");
if (count(file("$_POST[bbs]/dat/$_POST[id].dat")) > 999) { // Match anything with 1000 or greater replies.
fwrite($handle, "Over 1000 Thread<><>$thisverysecond<>This thread has over 1000 replies.<br>You can't reply anymore.<>Over 1000<>1.1.1.1\n");
$threadstopwhendone = true;
}
fclose($handle);

if (!is_dir("temp")) { mkdir("temp") or die("can't make temp dir"); chmod("temp", 0700); }
$handle = fopen("temp/$_SERVER[REMOTE_ADDR].flood", "w") or die ("can't write temp file");
fwrite($handle, $thisverysecond);
fclose($handle);

if ($_POST[subj]) {
	$handle = fopen("$_POST[bbs]/subject.txt", "a") or fancydie("Couldn't open subject.txt for writing!");
	$_POST[name] ? $namae = $_POST[name] : $namae = '#'.$trip;
	fwrite($handle, "$_POST[subj]<>$namae<>$_POST[icon]<>$_POST[id]<>0<>$namae<>$_POST[id]\n");
	fclose($handle);
}

if ($threadstopwhendone) chmod ("$_POST[bbs]/dat/$_POST[id].dat", 0440);
RebuildThreadList($_POST[bbs], $_POST[id], $_POST[sage], false);
?>
<html><title>Success</title><meta http-equiv='refresh' content='1;url=<?=$setting[urltoforum]?><?=$_POST[bbs]?>/'>
<? readfile("skin/$setting[skin]/success.txt"); ?>
<br><small><a href='<?=$setting[urltoforum]?><?=$_POST[bbs]?>/'>Click here to be forwarded manually</a></small>
<hr>
Powered by Shiichan v.<?=$shiiversion?>
