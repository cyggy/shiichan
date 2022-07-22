<?php
/* Shiichan 4000
** Basic includes
*/

// ABBC BBCode processor.
include('abbc/abbc.lib.php');

// current version (int)
$shiiversion = 3960;

function icons($i, $threadicon) { global $setting; if ($setting[posticons]) return "<img src='posticons/$threadicon'>"; return $i+1; }

function PrintPost($number, $name, $trip, $date, $id, $message, $postfile, $tid, $boardname) {
	global $setting;
	if ($date == 1234) return null;
	$post = $postfile;
	$post = str_replace("<%NUMBER%>", "<a href='javascript:quote($number,\"post$tid\");'>$number</a>", $post);
	$number % 2 ? $post = str_replace ("<%CSSHELP%>", "even", $post):  $post = str_replace ("<%CSSHELP%>", "odd", $post);
	$post = str_replace("<%NAME%>", $name, $post);
	$post = str_replace("<%TRIP%>", $trip, $post);
	$post = str_replace("<%DATE%>", date("j M Y: <b>H:i</b>", $date), $post);
	$post = str_replace("<%ID%>", $id, $post);
	if ($tid != 1 && $number != 1) {
	$messy = explode("<br>", $message); $message = "";
	for ($i = 1; $i <= $setting[fplines]; $i++) if ($messy) { $message .= array_shift($messy); $message .="<br>"; }
	if ($messy) $message .= "<i>(<a href='read.php/$boardname/$tid/$number'>Post truncated.</a>)</i>";
}
	$post = str_replace("<%MESSAGE%>", $message, $post);
	return $post;
}

function PrintPages($numposts, $boardname, $threadid, $postsperpage) { 
	$moot = "<span class='pages'>Pages:";
	for ($i = 1; $i <= ($numposts / $postsperpage) + 1; $i++) {
	   $print = $postsperpage*($i-1) + 1;
	   $pc = $print."-";
	   $tmp = $postsperpage * $i;
	   if ($tmp < $numposts) $pc .= $tmp;
		$moot .= "<a href='read.php/$boardname/$threadid/$pc'>$print</a> ";
	}
	return $moot . "</span>";
}

function PrintThread($boardname, $threadid, $postarray, $isitreadphp) {
	global $setting, $shiiversion;
	$postthing = 1; 
	$postfile = file_get_contents("skin/$setting[skin]/post.txt");
	$thread = file("$boardname/dat/$threadid.dat");
	$numposts = count($thread) - 1;
	if (!$isitreadphp) {
	$postthing = $threadid;
	$start = $numposts - $setting[fpposts] + 1;
	if ($start < 1) $start = 1;
	$end = $numposts;
	$postarray = array("$start-$end");
	}
	list ($threadname, $author, $threadicon) = explode("<=>", $thread[0]);

if ($isitreadphp) {
	$top = file_get_contents("skin/$setting[skin]/threadtop.txt");
	if (file_exists("option.txt")) $option = "<div class='option'>".file_get_contents("option.txt")."</div>"; else $option = "";
	$setting[posticons] ? $top = str_replace("<%THREADICON%>", "<img src='posticons/$threadicon' alt='thread icon'>", $top) : $top = str_replace("<%THREADICON%>", "", $top);
	$top = str_replace("<%FORUMNAME%>", $setting[forumname], $top);
	$top = str_replace("<%FORUMURL%>", $setting[urltoforum], $top);
	$top = str_replace("<%BOARDURL%>", $boardname, $top);
	$top = str_replace("<%BOARDNAME%>", $setting[boardname], $top);
	$top = str_replace("<%OPTION%>", $option, $top);
	if ($setting[encoding] == "sjis") $top = str_replace("<%ENCODING%>", "<META http-equiv='Content-Type' content='text/html; charset=Shift_JIS'><style>* { font-family: Mona,'MS PGothic' !important } ".abbc_css()."</style>", $top);
	else $top = str_replace("<%ENCODING%>", "<META http-equiv='Content-Type' content='text/html; charset=UTF-8'><style>".abbc_css()."</style>", $top);
	$top = str_replace("<%THREADNAME%>", $threadname, $top);
	$isitreadphp ? $top = str_replace("<%PAGES%>", PrintPages($numposts, $boardname, $threadid, $setting[postsperpage]), $top) : $top = str_replace("<%PAGES%>", "", $top);
	$top = str_replace("<%STARTFORM%>", "<form name='post$postthing' action='post.php' method='POST'><input type='hidden' name='bbs' value='$boardname'><input type='hidden' name='id' value='$threadid'><input type='hidden' name='shiichan' value='proper'>", $top);
	$return = $top;
} else { 
	$top = file_get_contents("skin/$setting[skin]/smallthreadtop.txt");
	$top = str_replace("<%THREADNAME%>", "<a name='$threadid' href='read.php/$boardname/$threadid/1-$setting[postsperpage]'>$threadname</a>", $top);
	$top = str_replace("<%STARTFORM%>", "<form name='post$postthing' action='post.php' method='POST'><input type='hidden' name='bbs' value='$boardname'><input type='hidden' name='id' value='$threadid'><input type='hidden' name='shiichan' value='proper'>", $top);
	$return = $top;
}

	# Always show the first post on the front page.
	if (!$isitreadphp && $start != 1) {
	 list($name, $trip, $date, $message, $id, $ip) = explode("<>", $thread[1]);
	 if ($trip) $trip = "#".$trip;
	 if ($isitreadphp) $return .= PrintPost(1, $name, $trip, $date, $id, $message, $postfile, 1, $boardname);
	 else $return .= PrintPost(1, $name, $trip, $date, $id, $message, $postfile, $threadid, $boardname);
	 # The latest replies are hidden... but gotta have skins!
         $hidden = file_get_contents("skin/$setting[skin]/hidden.txt");
	 $hidden = str_replace("<%FEW%>", $setting[fpposts], $hidden);
	 $hidden = str_replace("<%READ%>", "read.php/$boardname/$threadid/1-$setting[postsperpage]", $hidden);
	 $return .= $hidden; 
	}
	
	foreach ($postarray as $apost) {
	   list($start,$end) = explode('-',$apost);
	    if (strpos($start,'l') === 0) {$start = ($numposts - intval(substr($start,1)))+1; $end = $numposts;}
	   if ($start < 1) $start = 1;
	   if ($end == "") if (strstr($apost,"-")) $end = $numposts; else $end = $start;
	   if ($end > $numposts) $end = $numposts;
	   if ($start > $numposts) $start = $numposts;
	   if ($start <= $end) {
	     for ($i = $start; $i <= $end; $i++) {
	        list($name, $trip, $date, $message, $id, $ip) = explode("<>", $thread[$i]);
	        if ($trip) $trip = "#".$trip;
	        if ($isitreadphp) $return .= PrintPost($i, $name, $trip, $date, $id, $message, $postfile, 1, $boardname);
	        else $return .= PrintPost($i, $name, $trip, $date, $id, $message, $postfile, $threadid, $boardname);
	     }
	   } else {
	   	   for ($i = $start; $i >= $end; $i--) {
		   if ($end < 1) $end = 1; if ($start > $numposts) $start = $numposts;
	        list($name, $trip, $date, $message, $id, $ip) = explode("<>", $thread[$i]);
	        if ($trip) $trip = "#".$trip;
	        if ($isitreadphp) $return .= PrintPost($i, $name, $trip, $date, $id, $message, $postfile, 1, $boardname);
	        else $return .= PrintPost($i, $name, $trip, $date, $id, $message, $postfile, $threadid, $boardname);
	      }
	   }
	}

if ($isitreadphp) { # read.php takes its skin file
	$bottom = file_get_contents("skin/$setting[skin]/threadbottom.txt");
	$bottom = str_replace("<%SHIIVERSION%>", $shiiversion, $bottom);
} else $bottom = file_get_contents("skin/$setting[skin]/smallthreadbottom.txt");
	$bottom = str_replace("<%NUMPOSTS%>", $numposts + 1, $bottom);
	if (!is_writable("$boardname/dat/$threadid.dat")) $bottom = str_replace("<%TEXTAREA%>", "This thread is threadstopped. You can't reply anymore.", $bottom);
	else if ($setting[namefield]) $bottom = str_replace("<%TEXTAREA%>", "<textarea rows='5' cols='64' name='mesg'></textarea><br><input type='submit' value='Add Reply'> Name <input name='name'> <input name='sage' type='checkbox'> Sage?<br><a href='read.php/$boardname/$threadid/1-$setting[postsperpage]'>First Page</a> - <a href='read.php/$boardname/$threadid/l$setting[postsperpage]'>Last $setting[postsperpage]</a> - <a href='read.php/$boardname/$threadid/'>Entire Thread</a>", $bottom);
	else $bottom = str_replace("<%TEXTAREA%>", "<textarea rows='5' cols='64' name='mesg'></textarea><br><input type='submit' value='Add Reply'> <input name='sage' type='checkbox'> Sage?<br><a href='read.php/$boardname/$threadid/1-$setting[postsperpage]'>First Page</a> - <a href='read.php/$boardname/$threadid/l$setting[postsperpage]'>Last $setting[postsperpage]</a> - <a href='read.php/$boardname/$threadid/'>Entire Thread</a>", $bottom);

	$bottom = str_replace("<%REPLYLINK%>", "post.php?id=$threadid&amp;bbs=$boardname", $bottom);
	$bottom = str_replace("<%ADMINLINK%>", "<a href='admin.php?task=manage&amp;bbs=$boardname&amp;tid=$threadid&amp;st=$start&amp;ed=$end'>Manage</a>", $bottom);
	$return .= $bottom;

	return $return;
}

#### shall we rewrite index.html?
function RebuildThreadList($bbs, $thisid, $sage, $rmthread) {
	global $setting, $shiiversion;
	
	$subject = file("$bbs/subject.txt");

	if ($thisid!=1) {
		global $_POST, $thisverysecond;
		while (list($value, $line) = each($subject)) {
			list ($threadname, $author, $threadicon, $id, $replies, $last, $lasttime) = explode("<>", $line);
			if ($id == $thisid) {
			$slice1 = array_slice($subject, 0, $value);
			$slice2 =array_slice($subject,$value+1,count($subject));
			$replies = count(file("$bbs/dat/$thisid.dat")) - 1;
			if (!$sage or !$slice1) {
			$subject = array_merge($slice1, $slice2);
			if (!$rmthread) array_unshift($subject,"$threadname<>$author<>$threadicon<>$id<>$replies<>$_POST[name]<>$thisverysecond\n");
			break;
			} else {
			if (!$rmthread) array_push($slice1, "$threadname<>$author<>$threadicon<>$id<>$replies<>$_POST[name]<>$thisverysecond\n");
			$subject = array_merge($slice1, $slice2);
			break;
			}
		}
	}
	
	$f=fopen("$bbs/subject.txt", "w") or die ("couldn't write to subject.txt");
	foreach ($subject as $line) fwrite($f, $line);
	fclose($f);
	}
	
	$f=fopen("$bbs/index.html", "w") or die("couldn't write to index.html");
	if (file_exists("option.txt")) $option = "<div class='option'>".file_get_contents("option.txt")."</div>"; else $option = "";
	$top = file_get_contents("skin/$setting[skin]/boardtop.txt");
	$top = str_replace("<%POST%>","<form action='post.php'><input type='hidden' name='shiichan' value='writenew'><input type='hidden' name='bbs' value='$bbs'><input type='submit' value='New Thread'></form>", $top);
	$top = str_replace("<%FORUMURL%>", $setting[urltoforum], $top);
	$top = str_replace("<%BOARDURL%>", $bbs, $top);
	$top = str_replace("<%FORUMNAME%>", $setting[forumname], $top);
	$top = str_replace("<%BOARDNAME%>", $setting[boardname], $top);
	$top = str_replace("<%OPTION%>", $option, $top);
	if ($setting[encoding] == "sjis") $top = str_replace("<%ENCODING%>", "<META http-equiv='Content-Type' content='text/html; charset=Shift_JIS'><style>* { font-family: Mona,'MS PGothic' !important }".abbc_css()."</style>", $top);
	else $top = str_replace("<%ENCODING%>", "<META http-equiv='Content-Type' content='text/html; charset=UTF-8'><style>".abbc_css()."</style>", $top);
	fputs($f, $top);
	if (!$subject) fputs($f, "<tr><td colspan='5'><p style='text-align:center; padding: 1em'>This forum has no threads in it.</p></td></tr>");
	
	
	else { for ($i = 0; $i < $setting[fpthreads]; $i++) {
	if (!$subject[$i]) break;
	list ($threadname, $author, $threadicon, $id, $replies, $last, $lasttime) = explode("<>", $subject[$i]);	
	$time = date("j M Y H:i", $lasttime);
	$icon = icons($i, $threadicon);
	$pages = ceil($replies / $setting[postsperpage]);
	 $last = ($pages - 1) * $setting[postsperpage];
	fputs($f, "<tr><td><a href='read.php/$bbs/$id/'>$icon</a> </td><td><a href='$bbs/#$id'>$threadname</a>");
	 if ($pages > 1) { fputs($f, " ( ");
	 for ($j = 0; $j < $pages && $j < 7; $j++) {
	$jam = $j * $setting[postsperpage] + 1; $jelly = $jam - 1 + $setting[postsperpage];
	 fputs($f, "<a href='read.php/$bbs/$id/$jam-$jelly'>$jam</a> "); }
	 if ($pages > 6) { 
	 fputs($f, "... <a href='read.php/$bbs/$id/$last-'>Last page</a> ");
	 }
	  fputs($f, ")");
	 }
	  fputs($f, "</td><td>$author</td><td>$replies</td><td nowrap><small><a href='read.php/$bbs/$id/$last-'>$time</a></small></td></tr>");
	}
	
	for ($i = $setting[fpthreads]; $i < $setting[fpthreads] + $setting[additionalthreads]; $i++) {
	if (!$subject[$i]) break;
	list ($threadname, $author, $threadicon, $id, $replies, $last, $lasttime) = explode("<>", $subject[$i]);	
	$time = date("j M Y H:i", $lasttime);
	$icon = icons($i, $threadicon);
	fputs($f, "<tr><td><a href='read.php/$bbs/$id/1-$setting[postsperpage]'>$icon</a></td><td><a href='read.php/$bbs/$id/l$setting[postsperpage]'>$threadname</a></td><td>$author</td><td>$replies</td><td nowrap><small>$time</small></td></tr>");
	} }

	$middle = file_get_contents("skin/$setting[skin]/boardmiddle.txt");
	$middle = str_replace("<%BOARDURL%>", $bbs, $middle);
	$middle = str_replace("<%HEADTXT%>", file_get_contents("$bbs/head.txt"), $middle);
	fputs($f, $middle);
	
	for ($i = 0; $i < $setting[fpthreads]; $i++) {
	if (!$subject[$i]) break;
	list ($threadname, $author, $threadicon, $id, $replies, $last, $lasttime) = explode("<>", $subject[$i]);
	fputs ($f, PrintThread($bbs, $id, array("0"), false));
	}

	$bottom = file_get_contents("skin/$setting[skin]/boardbottom.txt");
	$bottom = str_replace("<%SHIIVERSION%>", $shiiversion, $bottom);
	fputs($f, $bottom);
	fclose($f);
}

function SecureSalt()
{
    $salt = "LOLLOLOLOLOLOLOLOLOLOLOLOLOLOLOL"; #this is ONLY used if the host doesn't have openssl
                                                #I don't know a better way to get random data
    if (file_exists("salt.cgi")) { #already generated a key
        $fd = fopen("salt.cgi","r");
        $fstats = fstat($fd);
        $salt = fread($fd,$fstats[size]);
        fclose($fd);
    } else { 
        system("openssl rand 448 > salt.cgi",$err);
        if ($err === 0) {
            chmod("salt.cgi",0400);
            $fd = fopen("salt.cgi","r");
            $fstats = fstat($fp);
            $salt = fread($fd,$fstats[size]);
            fclose($fd);
        }
    }
    return $salt;
}
?>
