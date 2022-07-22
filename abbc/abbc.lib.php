<?

// AdvancedBBCode 1.2
// http://software.unclassified.de/abbc
// Copyright 2003 by Yves Goergen
//
// Main Parser Module
// You should not need to change anything in here,
// use abbc.cfg.php for configuration

if (!defined('ABBC_LIB')) { define('ABBC_LIB', 1);

define("ABBC_ALL", -1);
define("ABBC_NONE", 0);
define("ABBC_MINIMUM", 1);
define("ABBC_SIMPLE", 2);
define("ABBC_CODE", 4);
define("ABBC_QUOTE", 8);
define("ABBC_FONT", 16);
define("ABBC_URL", 32);
define("ABBC_IMG", 64);
define("ABBC_LIST", 128);
define("ABBC_SPECIAL", 256);
define("ABBC_DONTINT", 512);
define("ABBC_PARAGRAPH", 1024);
define("ABBC_CUSTOM", 2048);
define("ABBC_SMILEYS", 4096);

// internal version number, do not change this
$abbc_version = "1.2-20031015";

include('abbc/abbc.cfg.php');

// Initialize some variables for a faster processing at a later time
//
function abbc_init()
{
	global $abbc_scan;
	global $abbc_tags, $abbc_smileys;
	global $abbc_smiley_count;
	global $abbc_max_taglen, $abbc_max_smileylen, $abbc_smiley_starts;

	$abbc_scan = array();

	// prepare tag configuration
	$abbc_max_taglen = 0;
	foreach ($abbc_tags as $key => $value)
	{
		$abbc_tags[$key]['level'] = 0;
		$abbc_tags[$key]['start'] = array();

		// get the longest tag and stop looking for a "=" or "]" after $max_taglen characters
		// add 1 for the ending tag's preceeding "/"
		if (strlen($key) + 1 > $abbc_max_taglen) $abbc_max_taglen = strlen($key) + 1;
	}

	// prepare smileys
	$abbc_smiley_starts = "";
	$abbc_smiley_count = sizeof($abbc_smileys);
	for ($n = 0; $n < $abbc_smiley_count; $n++)
	{
		// get the first character of this smiley and store it, it it isn't already there
		$start = $abbc_smileys[$n]['code']{0};
		if (strpos($abbc_smiley_starts, $start) === false) $abbc_smiley_starts .= $start;

		$abbc_smileys[$n]['code_len'] = strlen($abbc_smileys[$n]['code']);

		if ($abbc_smileys[$n]['code_len'] > $abbc_max_smileylen) $abbc_max_smileylen = $abbc_smileys[$n]['code_len'];
	}
}

abbc_init();

// this is used for debug output purposes.
// if you don't use debug output, you can delete this function.
//
/*function t2h($text)
{
	$text = str_replace("&", "&amp;", $text);
	$text = str_replace("<", "&lt;", $text);
	$text = str_replace(">", "&gt;", $text);
	$text = str_replace("\n", "\\n", $text);
	$text = str_replace("\r", "\\r", $text);
	return $text;
}*/


function abbc_proc($text, $check = false, $totext = false)
{
	// debug messages: to activate debug output, remove the comment right below
	$dbg = false;
	#$dbg = " ";

	// word border characters, only used for SPECIAL syntax and SMILEYs
	// I haven't checked whether it causes problems when changing this
	$wb = " \t.,!\(\)?+\-\n\r";

	global $abbc_scan, $abbc_tagstack;
	global $abbc_tags, $abbc_smileys;
	global $abbc_smiley_count;
	global $abbc_max_taglen, $abbc_max_smileylen, $abbc_smiley_starts;

	// set custom colors for PHP syntax highlighting
	global $abbc_cfg;
	if ($abbc_cfg['use_custom_php'])
	{
		ini_set("highlight.comment", $abbc_cfg['php_comment']);
		ini_set("highlight.default", $abbc_cfg['php_default']);
		ini_set("highlight.html", $abbc_cfg['php_html']);
		ini_set("highlight.keyword", $abbc_cfg['php_keyword']);
		ini_set("highlight.string", $abbc_cfg['php_string']);
	}

	$minimum = ($abbc_cfg['subsets'] & ABBC_MINIMUM);

	// ABBC_SPECIAL already done?
	global $abbc_special_done;
	$abbc_special_done = false;

	if ($minimum) $text = str_replace("\r", "", $text);

	// add a new-line at the beginning and ensure there's one at the end
	// some reg-exps need this to match the first character
	$text = "\r" . $text;
	if (substr($text, strlen($text) - 1) != "\r") $text .= "\r";

	// simple formatting: italic, bold, underlined, striked
	if ($abbc_cfg['subsets'] & ABBC_SPECIAL)
	{
		$text = str_replace("//", "&#x2F;/", $text);
		$text = str_replace("**", "&#xB0;*", $text);
		$text = str_replace("__", "&#x5F;_", $text);
		$text = str_replace("~~", "&#x7E;~", $text);

		$text = preg_replace("/([$wb])\/([^*$wb]([^\/\n]|\/[^$wb])*[^*$wb])\/([$wb])/", "$1[i]$2[/i]$4", $text);
		$text = preg_replace("/([$wb])\*([^$wb]([^\*\n]|\*[^$wb])*)\*([$wb])/", "$1[b]$2[/b]$4", $text);
		$text = preg_replace("/([$wb])_([^$wb]([^_\n]|_[^$wb])*)_([$wb])/", "$1[u]$2[/u]$4", $text);
		$text = preg_replace("/([$wb])~([^$wb]([^~\n]|~[^$wb])*)~([$wb])/", "$1[s]$2[/s]$4", $text);

		$text = str_replace("&#x7E;~", "~~", $text);
		$text = str_replace("&#x5F;_", "__", $text);
		$text = str_replace("&#xB0;*", "**", $text);
		$text = str_replace("&#x2F;/", "//", $text);

//		$text = preg_replace("/\n[ \t]*----+[ \t]*\n/", "\n[line=#808080]\n", $text);

		$abbc_special_done = true;
	}

	// quoting blocks
/*\	if (($abbc_cfg['subsets'] & ABBC_SPECIAL) && ($abbc_cfg['subsets'] & ABBC_QUOTE))
	{
		#$text = str_replace("\"|\n", "\"|", $text);   // remove new-line after end of quoting
		$text = preg_replace("/^\&gt\; ([$wb])/", "[quote]$1[/quote]", $text);
	}*/

	// 1ST PASS MAIN LOOP

	$abbc_tagstack = array();
	$error_closed = 0;
	$doproc = true;   // status variable for don't-process-content tags

	// just to make them a bit shorter...
	$max_taglen = $abbc_max_taglen;
	$smiley_starts = $abbc_smiley_starts;

	$abbc_scan['len'] = strlen(preg_replace("/\[.*?\]/", "", $text));

	$length = strlen($text);
	for ($pos = 0; $pos < strlen($text); $pos++)
	{
/*		if ($text{$pos} == "&" && $minimum && !$totext)
		{
			// don't change &#...; codes
			$n = 0;
			if ($text{$pos + 1} == '#')
			{
				$n = 2;
				$amp = 0;
				while (is_numeric($text{$pos + $n}))
				{
					$amp = $amp * 10 + intval($text{$pos + $n});
					$n++;
				}
				if ($text{$pos + $n} == ';')
				{
					$pos += $n;
				}

			}
			if (!$n)
			{
				$text = substr($text, 0, $pos) . "&amp;" . substr($text, $pos + 1);
				$pos += 4;
			}
		}
		elseif ($text{$pos} == "<" && $minimum && !$totext)
		{
			$text = substr($text, 0, $pos) . "&lt;" . substr($text, $pos + 1);
			$pos += 3;
		}
		elseif ($text{$pos} == ">" && $minimum && !$totext)
		{
			$text = substr($text, 0, $pos) . "&gt;" . substr($text, $pos + 1);
			$pos += 3;
		}*/
		if ($text{$pos} == "[" && $text{$pos - 1} == "\\" && $doproc && $minimum && !$totext)
		{
			$text = substr($text, 0, $pos - 1) . "[" . substr($text, $pos + 1);
			$pos -= 1;
		}
		elseif ($text{$pos} == "[" && $text{$pos - 1} != "\\" && $minimum)
		{
			$endpos = $pos + 1;
			while ($text{$endpos} != "]" && $text{$endpos} != "=" && $endpos - $pos - 1 <= $max_taglen) $endpos++;
			$thistag = substr($text, $pos + 1, $endpos - ($pos + 1));
			if ($endpos - $pos - 1 > $max_taglen)
			{
				// we flew out of the search loop as there was no end in a reasonable distance
				// so this can't be a valid tag
				// Note: it's "-1" instead of "+1" because we have to include the "[" and ("=" or "]") in the counting
				if ($dbg) $dbg .= "no tag:$thistag<br>";

				continue;
			}
			// now we have the tagname separated

			// find tag's end to jump to it when we're finished here...
			$stored_pos = $endpos;
			while ($text{$stored_pos} != "]" && $text{$stored_pos} != "\n") $stored_pos++;
			// if there was a new-line, this can't be a serious tag... let's just ignore it!
			if ($text{$stored_pos} == "\n") continue;

			// check for closing tag
			if ($thistag{0} == "/")
			{
				$closingtag = true;
				$thistag = substr($thistag, 1);   // remove "/"
				if ($thistag == false) $thistag = "";   // we need a STRING for our tags index!
			}
			else
			{
				$closingtag = false;
			}

			// check for valid tagname
			if ($thistag == "" || abbc_valid_tagname($thistag))
			{
				// if needed subset is not enabled, skip this tag
				if ($abbc_cfg['subsets'] & $abbc_tags[$thistag]['subset'])
				{
					if (!$closingtag && $doproc)
					{
						// current tag is OPENING

						if ($dbg) $dbg .= "pos:$pos, thistag:$thistag, tagstack:" . join("|", $abbc_tagstack) . ", text:" . t2h(trim(substr($text, 0, $pos) . "*" . substr($text, $pos))) . "<br>";

						while ($text{$endpos} != "]" && $text{$endpos} != "\n") $endpos++;
						if ($text{$endpos} == "\n")
						{
							// oops, there was a new-line before the tag's end
							// so there must have been a syntax error
							// we'll ignore this "tag" for now

							continue;
						}

						// check whether it has a closing tag, too
						if ($abbc_tags[$thistag]['openclose'])
						{
							// we expect a closing tag so we just do some stack stuff this time...

							// check if it may be nested
							if ($abbc_tags[$thistag]['nested'] || $abbc_tags[$thistag]['level'] == 0)
							{
								// store current position and update tag's current nesting layer
								array_push($abbc_tags[$thistag]['start'], $pos);
								$abbc_tags[$thistag]['level']++;

								if ($dbg) $dbg .= "new level:" . $abbc_tags[$thistag]['level'] . "<br>";

								// correct nesting check: put this tag on top of the "global" stack
								array_push($abbc_tagstack, $thistag);

								// check if we should process its content
								if (!$abbc_tags[$thistag]['proccont'])
								{
									// NO, save this to the status variable
									// we first continue processing any tags when a closing tag matched the topmost tagstack element,
									// that's when we found the closing tag to this one.
									$doproc = false;
								}
							}
							else
							{
								if ($dbg) $dbg .= "<b>Warning: trying to nest a tag which isn't allowed to...</b><br>";
							}
						}
						else
						{
							// this tag has no closing tag, so we have to process it NOW:

							$diff = abbc_expand_selection($text, $pos, $endpos);

							// this is the entire tag
							$entiretag = substr($text, $pos, $endpos - $pos + 1);

							// this is the actual replacement process
							$len_before = $endpos - $pos + 1;
							if ($dbg) $dbg .= t2h($entiretag) . " [len:" . strlen($entiretag) . ",$len_before]<br>";
							$entiretag = abbc_reg_replace_tag($entiretag, $thistag, $totext);
							$len_after = strlen($entiretag) + $diff;
							if ($dbg) $dbg .= t2h($entiretag) . " [len:" . strlen($entiretag) . ",$len_after]<br>";

							// update new cursor position and total length
							$stored_pos += $len_after - $len_before;

							// replace this tag by result of reg-exp replacement
							$text = substr_replace($text, $entiretag, $pos, $len_before);
						}
					}
					elseif ($closingtag)
					{
						// current tag is CLOSING
						if ($dbg) $dbg .= "closing:$thistag, level:" . $abbc_tags[$thistag]['level'] . "<br>";

						// check if tag was opened at all
						if ($abbc_tags[$thistag]['level'] > 0)
						{
							$corr_tag = array_pop($abbc_tagstack);

							if ($dbg) $dbg .= "pos:$pos, thistag:/$thistag, tagstack:" . join("|", $abbc_tagstack) . ", text:" . t2h(trim(substr($text, 0, $pos) . "*" . substr($text, $pos))) . "<br>";

							// correct nesting check: is this tag the next one to be closed?
							if ($thistag == $corr_tag)
							{
								// process entire tag area with reg-exps
								$startpos = array_pop($abbc_tags[$thistag]['start']);
								if ($dbg) $dbg .= "startpos:$startpos<br>";
								$abbc_tags[$thistag]['level']--;

								$diff = abbc_expand_selection($text, $startpos, $endpos);

								// this is the entire tag w/ its contents
								$entiretag = substr($text, $startpos, $endpos - $startpos + 1);

								// this is the actual replacement process
								$len_before = $endpos - $startpos + 1;
								if ($dbg) $dbg .= t2h($entiretag) . " [len:" . strlen($entiretag) . ",$len_before]<br>";
								$entiretag = abbc_reg_replace_tag($entiretag, $thistag, $totext);
								$len_after = strlen($entiretag) + $diff;
								if ($dbg) $dbg .= t2h($entiretag) . " [len:" . strlen($entiretag) . ",$len_after]<br>";

								// update new cursor position and total length
								$stored_pos += $len_after - $len_before;

								// replace this tag by result of reg-exp replacement
								$text = substr_replace($text, $entiretag, $startpos, $len_before);

								// if we were in don't-process mode, switch back again
								$doproc = true;
							}
							elseif ($doproc && !$totext)
							{
								// NO, the tag that's to be closed wasn't opened on this level.
								// So we mark it as 'bad' and that's all.
								// The corresponding opening tag (if present) will remain opened and appear in
								// the remaining tagstack, where we can mark it afterwards.

								if ($dbg) $dbg .= "<b>ERROR: $thistag is closed instead of $corr_tag at position $pos -- " . join("|", $abbc_tagstack) . "</b><br>";

								// first, let's push the correct tag back onto the stack so that the correct closing tag
								// will be recognized!
								array_push($abbc_tagstack, $corr_tag);

								$error_closed++;

								// highlight incorrectly nested closing tag
								$text = substr_replace(
									$text,
									"<span class=ecl>" . substr($text, $pos, $endpos - $pos + 1) . "</span>",
									$pos,
									$endpos - $pos + 1);
								$stored_pos += 23;   // and we 'skip' some more characters now...
							}
						}
						elseif ($doproc && !$totext)
						{
							if ($dbg) $dbg .= "<b>Warning: trying to close a tag ($thistag) with level 0...</b><br>";

							// highlight incorrectly nested closing tag
							$text = substr_replace(
								$text,
								"<span class=ecl>" . substr($text, $pos, $endpos - $pos + 1) . "</span>",
								$pos,
								$endpos - $pos + 1);
							$error_closed++;

							$stored_pos += 23;   // and we 'skip' some more characters now...
						}
					}
				} // end: subset check
			}
			else
			{
				if ($dbg) $dbg .= "warning: $thistag is not a valid BBCode tag!<br>";

				// don't skip over this, there may be data to process "inside" this "tag"
				$stored_pos = $pos;
			}

			// ok, we finished this tag, let's jump to its end end continue after it
			$pos = $stored_pos;

			if ($dbg) $dbg .= "finish -- pos:$pos, thistag:" . ($closingtag ? "/" : "") . "$thistag, tagstack:" . join("|", $abbc_tagstack) . ", text:" . t2h(trim(substr($text, 0, $pos) . "*" . substr($text, $pos))) . "<br><br>";
		}
		elseif ($abbc_cfg['subsets'] & ABBC_SMILEYS && $doproc && !$totext)
		{
			// could be a smiley?
			$could_be_smiley = strpos($smiley_starts, $text{$pos});

			// not impossible -> we have to check them all
			if ($could_be_smiley !== false)
			{
				$smileytext = substr($text, $pos, $abbc_max_smileylen);
				for ($n = 0; $n < $abbc_smiley_count; $n++)
				{
					if (!strncmp($smileytext, $abbc_smileys[$n]['code'], $abbc_smileys[$n]['code_len']))
					{
						// found a smiley -> translate it into an <img> and leave the loop
						$smiley = $abbc_smileys[$n];

						if ($dbg) $dbg .= "smiley! pos:$pos, code:" . $smiley['code'] . ", text:" . t2h(trim(substr($text, 0, $pos) . "*" . substr($text, $pos))) . "<br>\n";

						$endpos = $pos + strlen($smiley['code']) - 1;

						$diff = abbc_expand_selection_smiley($text, $pos, $endpos, $wb);

						// this is the entire tag
						$entiretag = substr($text, $pos, $endpos - $pos + 1);

						// this is the actual replacement process
						$len_before = $endpos - $pos + 1;
						if ($dbg) $dbg .= t2h($entiretag) . " [len:" . strlen($entiretag) . ",$len_before]<br>\n";
						$entiretag = abbc_reg_replace_smiley($entiretag, $n, $wb);
						$len_after = strlen($entiretag);
						if ($dbg) $dbg .= t2h($entiretag) . " [len:" . strlen($entiretag) . ",$len_after]<br>\n";

						// replace this tag by result of reg-exp replacement
						$text = substr_replace($text, $entiretag, $pos, $len_before);

						// update new cursor position and total length
						$pos += $len_after - 1;

						if ($dbg) $dbg .= "smiley done -- pos:$pos, code:" . $smiley['code'] . ", text:" . t2h(trim(substr($text, 0, $pos) . "*" . substr($text, $pos))) . "<br><br>\n";

						$abbc_scan['smile']++;
						$abbc_scan['len'] -= strlen($smiley['code']);
						break;
					}
				}
			}
		}
	}

	// This should be the same - having the $length counting ACTIVATED!
	// But it isn't. Don't know why... so I'm not using it any more
	#echo $length . "|" . strlen($text) . "<br>";

	if ($check) return sizeof($abbc_tagstack) + $error_closed;

	if (sizeof($abbc_tagstack) && $minimum && !$totext)
	{
		if ($dbg) $dbg .= "<b>remaining tagstack: " . join("|", $abbc_tagstack) . "</b><br>";

		// highlight incorrectly closed, remaining opening tags
		while ($tag = array_pop($abbc_tagstack))
		{
			$abbc_tags[$tag]['level']--;
			$startpos = array_pop($abbc_tags[$tag]['start']);
			$taglen = strpos($text, "]", $startpos) - $startpos + 1;

			$text = substr_replace(
				$text,
				"<span class=eop>" . substr($text, $startpos, $taglen) . "</span>",
				$startpos,
				$taglen);

			// Since we go through the tagstack from right to left, and elements were added to be the same
			// order as their appearance in $text, we don't change anything that would affect something after
			// it. So there's no need to adjust any following start positions of other tags :-)
		}
	}

	//--------------------------

	// automatically make URLs clickable
	if ($abbc_cfg['find_urls'] && $minimum && !$totext)
	{
		$urlsym     = '!#-&()+-;=?-Z^-z~';
		$urlendsym  = '#-&+\-\/-9=@-Z^-z~';
		$nourlsym   = '\t\r\n!(),.:;?\[\]<>\' ';

		$mailsym    = '!#-&\-.0-9=A-Z_a-z';   // no '?' here! this causes trouble with '...?...' addresses.
		$mailendsym = '#-&\-A-Z_a-z';
		$nomailsym  = '\t\r\n!(),.;?\[\]<>\' ';

		$target = '';
		if ($abbc_cfg['target'] != '') $target = " target='" . $abbc_cfg['target'] . "'";

		// we have to do this TWICE because e.g. two links in one and the next line cannot share the SAME [$wb] character
		// the one for its end AND the other for its beginning... if we run two independant passes, this is no problem
		for ($n = 0; $n < 2; $n++)
		{
			if ($abbc_cfg['derefer'] != '')
			{
				$text = preg_replace("/([$nourlsym])((?:http(?:s?)|ftp):\/\/[$urlsym]+[$urlendsym])([$nourlsym])/ei",
					'"$1\x01a href=\x03$abbc_cfg[derefer]".urlencode(abbc_h2t("$2"))."\x03$target\x02$2\x01/a\x02$3"',
					$text);
				$text = preg_replace("/([$nourlsym])(www\.[$urlsym]+[$urlendsym])([$nourlsym])/ei",
					'"$1\x01a href=\x03$abbc_cfg[derefer]".urlencode(abbc_h2t("$2"))."\x03$target\x02$2\x01/a\x02$3"',
					$text);
			}
			else
			{
				$text = preg_replace("/([$nourlsym])((?:http(?:s?)|ftp):\/\/[$urlsym]+[$urlendsym])([$nourlsym])/i",
					"$1\x01a href=\x03$2\x03$target\x02$2\x01/a\x02$3",
					$text);
				$text = preg_replace("/([$nourlsym])(www\.[$urlsym]+[$urlendsym])([$nourlsym])/i",
					"$1\x01a href=\x03$2\x03$target\x02$2\x01/a\x02$3",
					$text);
			}
			$text = preg_replace("/([$nourlsym])(ftp\.[$urlsym]+[$urlendsym])([$nourlsym])/ei",
				'"$1\x01a href=\x03ftp://".abbc_h2t("$2")."\x03$target\x02$2\x01/a\x02$3"',
				$text);
			$text = preg_replace("/([$nomailsym](?:mailto:)?)([$mailsym]+?@[$mailsym]+?\.[$mailsym]+[$mailendsym])([$nomailsym])/i",
				"$1\x01a href=\x03mailto:$2\x03\x02$2\x01/a\x02$3",
				$text);
		}
		$text = str_replace("\x01", '<', $text);
		$text = str_replace("\x02", '>', $text);
		$text = str_replace("\x03", '"', $text);
	}

	if ($minimum && !$totext) $text = str_replace("\n", "<br>\n", $text);

	if (($abbc_cfg['subsets'] & ABBC_SPECIAL) && ($abbc_cfg['subsets'] & ABBC_LIST))
	{
		$arr = explode("\n", $text);
		$arr2 = array();
		$in_list = 0;   // type of list we're inside
		foreach ($arr as $line)
		{
			if (substr($line, 0, 2) == "- ")
			{
				if ($in_list != 1)
				{
					if ($in_list == 2) $arr2[] = "</ol>";
					if ($in_list == 3) $arr2[] = "</ul>";
					$arr2[] = "<ul>";
					$in_list = 1;
				}
				$arr2[] = "<li>" . substr($line, 2) . "</li>";
			}
			elseif (substr($line, 0, 2) == "# ")
			{
				if ($in_list != 2)
				{
					if ($in_list == 1) $arr2[] = "</ul>";
					if ($in_list == 3) $arr2[] = "</ul>";
					$arr2[] = "<ol>";
					$in_list = 2;
				}
				$arr2[] = "<li>" . substr($line, 2) . "</li>";
			}
			elseif (substr($line, 0, 2) == "o ")
			{
				if ($in_list != 3)
				{
					if ($in_list == 1) $arr2[] = "</ul>";
					if ($in_list == 2) $arr2[] = "</ol>";
					$arr2[] = "<ul type=circle>";
					$in_list = 3;
				}
				$arr2[] = "<li>" . substr($line, 2) . "</li>";
			}
			elseif ($in_list > 0)
			{
				if ($in_list == 1) $arr2[] = "</ul>";
				if ($in_list == 2) $arr2[] = "</ol>";
				if ($in_list == 3) $arr2[] = "</ul>";
				$in_list = 0;
				#if ($line != "<br>") $arr2[] = $line;
				$arr2[] = $line;
			}
			else
			{
				$arr2[] = $line;
			}
		}
		$text = join("\n", $arr2);
	}

	// remove the added \r's if they are still there
	if ($text{0} == "\r") $text = substr($text, 1);
	if ($text{strlen($text) - 1} == "\r") $text = substr($text, 0, strlen($text) - 1);

	if ($minimum && !$totext)
	{
		// these are the new-lines that shouldn't be changed into <br>
		$text = str_replace("\r", "\n", $text);

		// multiple spaces and tabs
		$text = str_replace("\t", "    ", $text);
		$text = str_replace("\n ", "\n&nbsp;", $text);
		$text = str_replace("  ", "&nbsp; ", $text);
		$text = str_replace("  ", "&nbsp; ", $text);

		$text = abbc_unmask_specials($text);
	}

	// paragraph translation
	if ($abbc_cfg['subsets'] & ABBC_PARAGRAPH)
	{
		while (substr($text, strlen($text) - 5) == "<br>\n") $text = substr($text, 0, strlen($text) - 5);
		$text = str_replace("\n<br>\n<br>", "\n</div>\n<div class=p><br>", $text);
		$text = str_replace("<div class=quote>", "<div class=quote><div class=p>", $text);
		$text = str_replace("\n<br>", "\n</div>\n<div class=p>", $text);
		$text = str_replace("</div><br>", "</div>\n</div>\n<div class=p>", $text);
		$text = str_replace("<br>\n</div>", "\n</div>", $text);
		$text = "<div class=p>\n" . $text . "</div>\n";
		$text = str_replace("<div class=p>\n</div>", "</div>&nbsp;<div class=p>", $text);

		// undo \n masking of [code] blocks
		$text = str_replace("&#x0A;", "<br>\n", $text);
	}

	if ($minimum && !$totext)
	{
		// multiple spaces and tabs - tidy up for img tags
		$text = str_replace(">&nbsp; <", "> &nbsp;<", $text);
	}

	if ($abbc_cfg['output_div'] && $minimum && !$totext)
	{
		$text = "<div class=abbc>" . $text . "</div>";
	}

	if ($dbg) echo "<div class=abbc>" . $dbg . "</div><br>\r\n\r\n";

	return $text;

}   // function abbc($text)


function abbc_check($text)
{
	return abbc_proc($text, true);
}


function abbc_begin_list($type)
{
	switch ($type)
	{
		case "1": return "<ol>";
		case "A": return "<ol type=A>";
		case "i": return "<ol type=i>";
		case "a": return "<ol type=a>";
		case "": return "<ul>";
		case "o": return "<ul type=circle>";
		case "+": return "<ul type=square>";
		default: return "<ul>";
	}
}

function abbc_end_list($type)
{
	switch ($type)
	{
		case "1":
		case "A":
		case "i":
		case "a": return "</ol>";
		case "":
		case "o":
		case "+": return "</ul>";
		default: return "</ul>";
	}
}

function abbc_mask_specials($text, $incode = false)
{
	global $abbc_cfg, $abbc_special_done;

	// convert "php" into code-level 2, since we can't change the "=php" BBCode parameter into "2" from
	// within the reg-exp translation
	// Note: Default of "" is needed since if we took a default of 0, the parameter "php" would be recognized as 0!!!
	if ($incode === false) $incode = 0;
	elseif (!strcasecmp($incode, "php")) $incode = 2;

	elseif (!strcasecmp($incode, "c"))
	{
		// Syntax highlighting for C languages
		// This is still under construction!
		// Simple pattern matching is by far not a usable solution for this task. But it's better than nothing anyway.

		// operators
		$text = preg_replace('/(&amp;|&lt;|&gt;|==|&lt;=|&gt;=|\+=|-=|\*=|\+|!|~|%|\^|\||=)/', '<font color=darkblue>$1</font>', $text);
		$text = preg_replace('/([^\/<*])(\*|\/)([^\/*])/', '$1<font color=darkblue>$2</font>$3', $text);
		$text = preg_replace('/([^\/])(\/=)/', '$1<font color=darkblue>$2</font>', $text);
		$text = preg_replace('/(-)(\D)/', '<font color=darkblue>$1</font>$2', $text);

		// keywords
		$text = preg_replace('/\b(and|break|bool|case|catch|char|class|const|continue|default|delete|do|double|enum|else|extends|extern|false|finally|float|for|goto|if|int|interface|long|new|operator|or|private|protected|public|register|return|short|signed|sizeof|static|struct|switch|this|throw|throws|true|try|typeof|union|unsigned|until|virtual|void|while|xor)\b/', '<font color=blue>$1</font>', $text);

		// numbers
		$text = preg_replace('/(\W)(0x[0-9A-Fa-f]+|-?([0-9]*\.)?[0-9]+)/', '$1<font color=darkcyan>$2</font>', $text);

		// brackets
		$text = preg_replace('/([()\[\]{}])/', '<font color=red>$1</font>', $text);

		// strings
		$text = preg_replace('/((["\'])(\2|.*?[^\\\]\2))/es', '"<font color=darkcyan>".strip_tags(str_replace("\\\'","\'","$1"))."</font>"', $text);

		// comments
		$text = preg_replace('/(\/\/.*?\n|\/\*.*?\*\/)/es', '"<font color=#909090>".strip_tags(str_replace("\\\'","\'","$1"))."</font>"', $text);

		// preprocessor lines
		$text = preg_replace('/((\n|^)#(define|elif|else|endif|if|ifdef|ifndef|include|undef).*?\n)/e', '"<font color=green>".strip_tags(str_replace("\\\'","\'","$1"))."</font>"', $text);

		// set default text colour to black
		$text = '<font color=black>' . $text . '</font>';

		$incode = 1;   // perform remaining transformations, as for PHP
	}

	elseif ($incode !== false) $incode = 1;   // take any unknown descriptor for generic code

	if ($incode == 2)
	{
		// If there's still a (just ago) masked "$", it's getting embedded in some HTML <font> tags and can't
		// be unmasked after the reg-exp replace (originally called from abbc_reg_replace_tag) completed!
		// So we have to unmask it here.
		$text = str_replace("&#x24;", "$", $text);

		$text = str_replace("&lt;", "<", $text);
		$text = str_replace("&gt;", ">", $text);
		$text = str_replace("&amp;", "&", $text);

		$text = abbc_unmask_specials($text);
		$text = "<?" . $text . "?>";

		ob_start();
		highlight_string($text);
		$text = ob_get_contents();
		ob_end_clean();

		// remove the PHP tags we added and some other stuff again
		$text = preg_replace("/^<code>(<font[^>]*>)[\r\n]*(<font[^>]*>)&lt;\?/i", "$1$2", $text);
		$text = preg_replace("/<font[^>]*>\?&gt;<\/font>[\r\n]*(<\/font>)[\r\n]*<\/code>$/i", "$1", $text);
		$text = preg_replace("/\?&gt;(<\/font>)[\r\n]*(<\/font>)[\r\n]*<\/code>$/i", "$1$2", $text);

		// and now, remove HTML tags again, we do this for ourselves!
		/*$text = str_replace("&lt;", "<", $text);
		$text = str_replace("&gt;", ">", $text);
		$text = str_replace("&amp;", "&", $text);*/

		// I think we don't need this
		//$text = abbc_mask_specials($text);
	}
	if (($incode >= 1) && ($abbc_cfg['subsets'] & ABBC_PARAGRAPH))
	{
		$text = str_replace("\n", "&#x0A;", $text);
	}
	if ($incode >= 1)
	{
		$text = str_replace("#", "&#x23;", $text);   // this may cause SPECIAL lists to be generated
		$text = str_replace("*", "&#x2A;", $text);   // this may cause SPECIAL lists to be generated
	}
	if (($abbc_cfg['subsets'] & ABBC_SPECIAL) && !$abbc_special_done)
	{
		$text = str_replace("*", "&#xB0;", $text);
		$text = str_replace("/", "&#x2F;", $text);
		$text = str_replace("_", "&#x5F;", $text);
		$text = str_replace("~", "&#x7E;", $text);
		$text = str_replace("|", "&#x7C;", $text);
	}
	$text = str_replace("[", "&#x5B;", $text);
	$text = str_replace("]", "&#x5D;", $text);

	return $text;
}

function abbc_unmask_specials($text)
{
	$text = str_replace("&#xB0;", "*", $text);
	$text = str_replace("&#x2F;", "/", $text);
	$text = str_replace("&#x5F;", "_", $text);
	$text = str_replace("&#x7E;", "~", $text);
	$text = str_replace("&#x7C;", "|", $text);

	$text = str_replace("&#x5B;", "[", $text);
	$text = str_replace("&#x5D;", "]", $text);

	$text = str_replace("&#x23;", "#", $text);
	$text = str_replace("&#x2A;", "*", $text);
	return $text;
}

// Does the actual reg-exp replacement of an area containing only a single level of a BBCode tag
//
function abbc_reg_replace_tag($text, $tagname, $totext = false)
{
	global $abbc_cfg, $abbc_tags, $abbc_scan;

	$openclose = $abbc_tags[$tagname]['openclose'];

	// prepare tagname for use in reg-exps
	$tagreg = str_replace("*", "\*", $tagname);
	$tagreg = str_replace(".", "\.", $tagreg);
	$tagreg = str_replace("?", "\?", $tagreg);

	// now the reg-exps for the bbcode parameters are formed
	// at the moment only 0 to 2 parameters are allowed
	// [This is relevant for the maximum parameter count. MAXPARAM]
	$params0 = "";
	$params1 = "=([^\]]*)";
	$params2 = "=([^\]:]*):([^\]]*)";
	$params3 = "=([^\]:]*):([^\]:]*):([^\]]*)";

	// case-sensitivity reg-exp modifier is set
	if ($abbc_tags[$tagname]['nocase']) $nocase = "i"; else $nocase = "";
	$nocase .= "s";   // for PCRE_DOTALL

	// reg-exps for opening and closing tags are set
	// [This is relevant for the maximum parameter count. MAXPARAM]
	$regopen[0] = "\[$tagreg$params0\]";
	$regopen[1] = "\[$tagreg$params1\]";
	$regopen[2] = "\[$tagreg$params2\]";
	$regopen[3] = "\[$tagreg$params3\]";
	$regclose = $openclose ? "\[\/$tagreg\]" : "";
	$between = $openclose ? "(.*)" : "";

	// Note: instead of .* we could do the following:
	//       ungreedy matching (.*?) and embed the entire reg-exp into ^...$ so that the entire string MUST be used.
	//       this is for the case we have an (already marked) WRONG closing tag in a deeper level. of course, we can't
	//       let this be used for premature end of our pattern!
	// But:  this won't work when using abbc_expand_selection() which adds some spaces and new-lines around our tags...

	// remove new-lines around the tags where needed
	if ($abbc_tags[$tagname]['htmlblock'])
	{
		if ($openclose)
		{
			for ($n = $abbc_tags[$tagname]['maxparam']; $n >= $abbc_tags[$tagname]['minparam']; $n--)
			{
				$text = preg_replace("/($regopen[$n])[ \t]*?\n/$nocase", "$1", $text);   // remove new-line after beginning of block
			}
			$text = preg_replace("/($regclose)[ \t]*?\n/$nocase", "$1", $text);   // remove new-line after end of block
		}
		else
		{
			for ($n = $abbc_tags[$tagname]['maxparam']; $n >= $abbc_tags[$tagname]['minparam']; $n--)
			{
				$text = preg_replace("/\n[ \t]*?($regopen[$n])[ \t]*?\n/$nocase", "$1", $text);   // remove new-line around block
			}
		}
	}

	// now we compose the actually used reg-exp and html translation
	for ($n = $abbc_tags[$tagname]['maxparam']; $n >= $abbc_tags[$tagname]['minparam']; $n--)
	{
		$mod = $nocase;

		// check for PHP code
		if (!$totext)
		{
			$htmlopen = $abbc_tags[$tagname]["htmlopen$n"];
			if ($htmlopen != "" && $htmlopen{0} == "~")
			{
				$mod .= "e";
				$htmlopen = substr($htmlopen, 1);
			}

			$html = $htmlopen . ($openclose ? $abbc_tags[$tagname]["htmlcont$n"] . $abbc_tags[$tagname]["htmlclose$n"] : "");
		}
		else  // totext
		{
			$html = $abbc_tags[$tagname]["textcont$n"];
			if ($abbc_tags[$tagname]['htmlblock']) $html = "\n" . $html . "\n";
		}

		$reg = "/^(\s*?)$regopen[$n]$between$regclose(\s*?)$/$mod";

		if ($html != "")
		{
			// since we add a capturing parenthesis, we have to change all back-reference numbers
			// BUT only, if $html wasn't empty anyway, like for the [rem] tag
			// [This is relevant for the maximum parameter count. MAXPARAM]
			$html = str_replace("$4", "$5", $html);
			$html = str_replace("$3", "$4", $html);
			$html = str_replace("$2", "$3", $html);
			$html = str_replace("$1", "$2", $html);

			$maxref = 2;
			while (strpos($html, '$' . $maxref) !== false) $maxref++;

			if (strpos($mod, "e") === false)
			{
				// no PHP code, just add the $'s
				$html = "$1" . $html . "$" . $maxref;
			}
			else
			{
				// PHP code: put them in quotes
				$html = "\"$1\"." . $html . ".\"$" . $maxref . "\"";
			}

			$html .= $abbc_tags[$tagname]['htmlblock'] ? "\r" : "";
		}

		// now it's time to actually perform the translation!
		// echo'ing a variable that contains $name would echo the contents of the php varialbe $name. that's pretty uncool
		$text = str_replace("$", "&#x24;", $text);
		$text = preg_replace($reg, $html, $text);
		$text = str_replace("&#x24;", "$", $text);
	}
	if ($tagname == 'img') $abbc_scan['img']++;

	return $text;
}

// Does the actual reg-exp replacement of an area containing a SMILEY
//
function abbc_reg_replace_smiley($text, $n, $wb = "")
{
	global $abbc_cfg, $abbc_smileys;

	$codereg = $code = $abbc_smileys[$n]['code'];

	// prepare smiley code for use in reg-exps
	$codereg = str_replace("*", "\*", $codereg);
	$codereg = str_replace(".", "\.", $codereg);
	$codereg = str_replace("?", "\?", $codereg);
	$codereg = str_replace("(", "\(", $codereg);
	$codereg = str_replace(")", "\)", $codereg);
	$codereg = str_replace("/", "\/", $codereg);

	// case-sensitivity reg-exp modifier is set
	if ($abbc_smileys[$n]['nocase']) $nocase = "i"; else $nocase = "";
	$nocase .= "s";   // for PCRE_DOTALL

	// if there is an alignment set for this smiley, then use it!
	$align = ($abbc_smileys[$n]['align'] != "") ? " align=" . $abbc_smileys[$n]['align'] : "";

	// now we compose the actually used reg-exp and html translation
	if ($wb != "")
		$reg = "/^([$wb]+?)$codereg([$wb]+?)$/$nocase";
	else
		$reg = "/^$codereg$/$nocase";

	if ($abbc_smileys[$n]['img'] == '')
		$html = "$1$2";
	elseif ($text != "")
		$html = "$1<img src=\"" . $abbc_cfg['smilepath'] . $abbc_smileys[$n]['img'] . "\" title=\"" . htmlspecialchars($abbc_smileys[$n]['code']) . "\"$align>$2";
	else
		// we can return the entire <img> tag if there's no text to process (like for the smiley tag [:])
		return "<img src=\"" . $abbc_cfg['smilepath'] . $abbc_smileys[$n]['img'] . "\" title=\"" . htmlspecialchars($abbc_smileys[$n]['code']) . "\"$align>";

	if (!($abbc_cfg['subsets'] & ABBC_MINIMUM))
	{
		// someone didn't want us to make <img> html code but rather a bb-code smiley tag
		// so why not...
		$html = "$1[img]" . $abbc_cfg['smilepath'] . $abbc_smileys[$n]['img'] . "[/img]$2";
	}

	// now it's time to actually perform the translation!
	// echo'ing a variable that contains $name would echo the contents of the php varialbe $name. that's pretty uncool
	$text = str_replace("$", "&#x24;", $text);
	$text = preg_replace($reg, $html, $text);
	$text = str_replace("&#x24;", "$", $text);

	return $text;
}

// Looks around a 'selected' [tag](...[/tag]) for (spaces and) NEW-LINES and expands the selection to include them
// Expansion stops before first non-space && non-new-line char or after first new-line-char
// For performance, strlen($text) can be given in $length, but that's optional
//
function abbc_expand_selection($text, &$startpos, &$endpos, $length = -1)
{
	// go backward from the beginning on
	while ($startpos > 0 && ($text{$startpos - 1} == " " || $text{$startpos - 1} == "\t" || $text{$startpos - 1} == "\n")) $startpos--;

	// go forward from the end on
	if ($length == -1) $length = strlen($text);
	$end_diff = 0;
	while ($endpos < $length && ($text{$endpos + 1} == " " || $text{$endpos + 1} == "\t" || $text{$endpos + 1} == "\n"))
	{
		$endpos++;
		$end_diff++;
	}

	return $end_diff;
}

// Almost the same, but used for SMILEYS that may have some other charachters around them...
//
function abbc_expand_selection_smiley($text, &$startpos, &$endpos, $wb, $length = -1)
{
	// there are some "\" that are necessary for reg-exps but mislead the strpos function!
	$wb2 = stripslashes($wb);

	// go backward from the beginning on
	while ($startpos > 0 && strpos($wb2, $text{$startpos - 1}) !== false) $startpos--;

	// go forward from the end on
	if ($length == -1) $length = strlen($text);
	$end_diff = 0;
	while ($endpos < $length - 1 && strpos($wb2, $text{$endpos + 1}) !== false)
	{
		$endpos++;
		$end_diff++;
	}

	return $end_diff;
}

function abbc_disp_smiley($smileytext)
{
	global $abbc_smileys;
	global $abbc_smiley_count;

	for ($n = 0; $n < $abbc_smiley_count; $n++)
	{
		if (!strncmp($smileytext, $abbc_smileys[$n]['code'], $abbc_smileys[$n]['code_len']))
		{
			// found a smiley -> translate it into an <img>
			return abbc_reg_replace_smiley("", $n);
		}
	}

	// this doesn't seem to be a known smiley code -> just display it as-is
	return $smileytext;
}

// checks if given tagname exists in the tags array
// takes care of 'nocase' configuration for each tag
//
function abbc_valid_tagname(&$tagname)
{
	global $abbc_tags;

	if (array_key_exists($tagname, $abbc_tags)) return true;

	reset($abbc_tags);
	while (list ($key, $value) = each ($abbc_tags))
	{
		#echo "case-checking for tagname '$tagname' against '$key'<br>";
		if ($abbc_tags[$key]['nocase'] && !strcasecmp($key, $tagname))
		{
			// change tagname in the calling context so that we have the correct key for further access on this tag's data
			$tagname = $key;
			return true;
		}
	}

	return false;
}

// checks if given tagname exists on the tagstack
// takes care of 'nocase' configuration for each tag
//
function abbc_on_tagstack($tagname)
{
	global $abbc_tags, $abbc_tagstack;

	if (in_array($tagname, $abbc_tagstack)) return true;

	reset($abbc_tagstack);
	while (list ($key, $value) = each ($abbc_tagstack))
	{
		if ($abbc_tags[$key]['nocase'] && !strcasecmp($key, $tagname))
		{
			return true;
		}
	}

	return false;
}

function abbc_h2t($text)
{
	// This function is taken from WMS/common.lib/h2t()

	$text = str_replace("&amp;", "&", $text);
	$text = str_replace("&lt;", "<", $text);
	$text = str_replace("&gt;", ">", $text);
	$text = str_replace("&quot;", "\"", $text);
	$text = str_replace("&nbsp;", " ", $text);   // TODO: this one is subject to test...

	return $text;
}

function abbc_css()
{
	global $abbc_cfg;

	$css = <<<EOF
.quote
{
	border-left: $abbc_cfg[quote_borderl];
	padding: $abbc_cfg[quote_padding];
	margin: $abbc_cfg[quote_margin];
}
.quote .quote
{
	border-left: $abbc_cfg[quote_borderl2];
}
.quote .quote .quote
{
	border-left: $abbc_cfg[quote_borderl3];
}
.qname
{
	font: $abbc_cfg[qnamefont];
	color: $abbc_cfg[qname_color];
	margin-bottom: 4px;
}
tt, .code
{
	font: $abbc_cfg[monofont];
}
tt
{
	color: $abbc_cfg[m_color];
}
.code
{
	color: $abbc_cfg[code_color];
	background: $abbc_cfg[code_back];
	padding: $abbc_cfg[code_padding];
	margin: $abbc_cfg[code_margin];
}
.eop
{
	background: #FFFF55;
}
.ecl
{
	background: #99FF99;
}
EOF;
	return $css;
}   // function abbc_css()


//  convert user data into another format
//  e.g. smileys into bb-tags
//  or the other way round (for editing a (converted) stored posting with friendly smiley presentation)
//
function abbc_convert($in, $action)
{
	global $abbc_cfg;

	switch ($action)
	{
		case 1:
			// convert smileys into their bb-code tags for faster processing later

			// backup and change subsets to allow smiley recognition.
			// only activate ABBC_SMILEYS, this tells the reg-exp-replace function
			// to make smiley tags instead of <img> html code.
			$prev_subsets = $abbc_cfg['subsets'];
			$abbc_cfg['subsets'] = ABBC_SMILEYS;
			$out = abbc_proc($in);

			// restore previous subsets
			$abbc_cfg['subsets'] = $prev_subsets;

			return $out;

		case 2:
			// convert smiley bb-code tags back into their friendly-code for re-editing by the user

			// this feature is not yet implemented
			return "";
	}

	// nothing to do here
	return false;
}


} // ABBC_LIB

?>
