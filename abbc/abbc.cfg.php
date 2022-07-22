<?php

// AdvancedBBCode 1.2
// http://software.unclassified.de/abbc
// Copyright 2003 by Yves Goergen
//
// Configuration File

// target frame and derefer script for auto-links
$abbc_cfg['target'] = '_blank';
$abbc_cfg['derefer'] = '';

// activated subsets

$abbc_cfg['subsets'] = ABBC_ALL & ~ABBC_PARAGRAPH & ~ABBC_SMILEYS & ~ABBC_LIST & ~ABBC_FONT & ~ABBC_IMG & ~ABBC_CODE & ~ABBC_QUOTE & ~ABBC_URL & ~ABBC_SPECIAL;
//$abbc_cfg['subsets'] = 2;

// embed the text output in <div> tags
$abbc_cfg['output_div'] = false;

// automatically make URLs clickable
$abbc_cfg['find_urls'] = true;

// smiley images path (with trailing /)
$abbc_cfg['smilepath'] = "smile/";

// base and monospace fonts
$abbc_cfg['basefont'] = "";//11px/16px Verdana,Arial,sans-serif";
$abbc_cfg['monofont'] = "100% Andale Mono,Courier New,monospace";
$abbc_cfg['qnamefont'] = "italic 100% Verdana,Arial,sans-serif";

// some tag's parameters, see abbc_css() for details
$abbc_cfg['custom_a'] = false;
$abbc_cfg['a_color'] = "#04F";
$abbc_cfg['a_decor'] = "none";
$abbc_cfg['a_color_hover'] = "#04F";
$abbc_cfg['a_decor_hover'] = "underline";
$abbc_cfg['m_color'] = "#0900";
$abbc_cfg['code_color'] = "#900";
$abbc_cfg['code_back'] = "#EEE";
$abbc_cfg['code_padding'] = "3px 2px 3px 2px";
$abbc_cfg['code_margin'] = "3px 0px";
$abbc_cfg['quote_borderl'] = "solid 2px #666; color: #555";
$abbc_cfg['quote_borderl2'] = "solid 2px #888; color: #666";
$abbc_cfg['quote_borderl3'] = "solid 2px #aaa; color: #777";
$abbc_cfg['quote_padding'] = "0px 0px 0px 10px";
$abbc_cfg['quote_margin'] = "3px 0px";
$abbc_cfg['qname_color'] = "#A0A000";
$abbc_cfg['par_margin'] = "10px 0px";

// here are the custom colors for PHP syntax highlighting
$abbc_cfg['use_custom_php'] = true;
$abbc_cfg['php_comment'] = "#888";
$abbc_cfg['php_default'] = "#000";
$abbc_cfg['php_html'] = "#0600";
$abbc_cfg['php_keyword'] = "#00D";
$abbc_cfg['php_string'] = "#9000";

// Tag Definitions

// Following information is necessary for a BBCode tag:
//   tag          how the [tag] is named
//   htmlopen     what it's to be translated into (parameters used by $1, $2...)
//   htmlcont     new content inside the HTML tags, normally something like $1, $2...
//   htmlclose    closing HTML tag (optional)
//   htmlblock    this defines its own block, new-lines around it are removed
//   maxparam     number of parameters for BBCode tag
//   openclose    has a closing tag, $htmlclose is needed
//   nocase       case-insensitive tagname (default, recommended)
//   nested       may this tag be nested? ex. [b]...[b]...[/b]...[/b]
//   proccont     process the tag's content? if no, nested is ignored
//   subset       what subset this tag belongs to

// Maximum parameter count is currently set to 3. You might want to change this.
// Relevant code locations are marked with MAXPARAM.

$tag = '#';
$abbc_tags[$tag]['htmlopen0']  = "";
$abbc_tags[$tag]['htmlcont0']  = "\$1";
$abbc_tags[$tag]['htmlclose0'] = "";
$abbc_tags[$tag]['textcont0']  = "\$1";
$abbc_tags[$tag]['htmlblock']  = false;
$abbc_tags[$tag]['minparam']   = 0;
$abbc_tags[$tag]['maxparam']   = 0;
$abbc_tags[$tag]['openclose']  = true;
$abbc_tags[$tag]['nocase']     = true;
$abbc_tags[$tag]['nested']     = false;
$abbc_tags[$tag]['proccont']   = false;
$abbc_tags[$tag]['subset']     = ABBC_DONTINT;

$tag = 'rem';
$abbc_tags[$tag]['htmlopen0']  = "";
$abbc_tags[$tag]['htmlcont0']  = "";
$abbc_tags[$tag]['htmlclose0'] = "";
$abbc_tags[$tag]['textcont0']  = "";
$abbc_tags[$tag]['htmlblock']  = true;
$abbc_tags[$tag]['minparam']   = 0;
$abbc_tags[$tag]['maxparam']   = 0;
$abbc_tags[$tag]['openclose']  = true;
$abbc_tags[$tag]['nocase']     = true;
$abbc_tags[$tag]['nested']     = false;
$abbc_tags[$tag]['proccont']   = false;
$abbc_tags[$tag]['subset']     = ABBC_DONTINT;

$tag = 'spoiler';
$abbc_tags[$tag]['htmlopen0']  = "<span style=\"COLOR: black; TEXT-DECORATION: none; background-color: black; font-weight: normal;\" onmouseover=\"this.style.color='#FFFFFF';\" onmouseout=\"this.style.color=this.style.backgroundColor='#000000'\">";
$abbc_tags[$tag]['htmlcont0']  = "\$1";
$abbc_tags[$tag]['htmlclose0'] = "</span>";
$abbc_tags[$tag]['textcont0']  = "\$1";
$abbc_tags[$tag]['htmlblock']  = true;
$abbc_tags[$tag]['minparam']   = 0;
$abbc_tags[$tag]['maxparam']   = 0;
$abbc_tags[$tag]['openclose']  = true;
$abbc_tags[$tag]['nocase']     = true;
$abbc_tags[$tag]['nested']     = true;
$abbc_tags[$tag]['proccont']   = true;
$abbc_tags[$tag]['subset']     = ABBC_SIMPLE;


$tag = 'code';
$abbc_tags[$tag]['htmlopen0']  = "~\"<div class=code>\".";
$abbc_tags[$tag]['htmlcont0']  = "abbc_mask_specials(str_replace('\\\"','\"',rtrim('$1')),1).";
$abbc_tags[$tag]['htmlclose0'] = "\"</div>\"";
$abbc_tags[$tag]['textcont0']  = "\$1";
$abbc_tags[$tag]['htmlopen1']  = "~\"<div class=code>\".";
$abbc_tags[$tag]['htmlcont1']  = "abbc_mask_specials(str_replace('\\\"','\"',rtrim('$2')),\"$1\").";
$abbc_tags[$tag]['htmlclose1'] = "\"</div>\"";
$abbc_tags[$tag]['textcont1']  = "\$2";
$abbc_tags[$tag]['htmlblock']  = true;
$abbc_tags[$tag]['minparam']   = 0;
$abbc_tags[$tag]['maxparam']   = 1;
$abbc_tags[$tag]['openclose']  = true;
$abbc_tags[$tag]['nocase']     = true;
$abbc_tags[$tag]['nested']     = false;
$abbc_tags[$tag]['proccont']   = false;
$abbc_tags[$tag]['subset']     = ABBC_CODE;

$tag = 'quote';
$abbc_tags[$tag]['htmlopen0']  = "~\"<div class=quote>\".";
$abbc_tags[$tag]['htmlcont0']  = "trim(str_replace('\\\"','\"','\$1')).";
$abbc_tags[$tag]['htmlclose0'] = "\"</div>\"";
$abbc_tags[$tag]['textcont0']  = "--- Quote:\n\$1\n---";
$abbc_tags[$tag]['htmlopen1']  = "~\"<div class=quote><div class=qname>Quote\".(trim(\"\$1\")==''?':':\" from \".trim(stripslashes(\"\$1\")).\":\").\"</div>\".";
$abbc_tags[$tag]['htmlcont1']  = "trim(str_replace('\\\"','\"','\$2')).";
$abbc_tags[$tag]['htmlclose1'] = "\"</div>\"";
$abbc_tags[$tag]['textcont1']  = "--- Quote from \$1:\n\$2\n---";
$abbc_tags[$tag]['htmlblock']  = true;
$abbc_tags[$tag]['minparam']   = 0;
$abbc_tags[$tag]['maxparam']   = 1;
$abbc_tags[$tag]['openclose']  = true;
$abbc_tags[$tag]['nocase']     = true;
$abbc_tags[$tag]['nested']     = true;
$abbc_tags[$tag]['proccont']   = true;
$abbc_tags[$tag]['subset']     = ABBC_QUOTE;

$tag = 'b';
$abbc_tags[$tag]['htmlopen0']  = "<b>";
$abbc_tags[$tag]['htmlcont0']  = "\$1";
$abbc_tags[$tag]['htmlclose0'] = "</b>";
$abbc_tags[$tag]['textcont0']  = "\$1";
$abbc_tags[$tag]['htmlblock']  = false;
$abbc_tags[$tag]['minparam']   = 0;
$abbc_tags[$tag]['maxparam']   = 0;
$abbc_tags[$tag]['openclose']  = true;
$abbc_tags[$tag]['nocase']     = true;
$abbc_tags[$tag]['nested']     = true;
$abbc_tags[$tag]['proccont']   = true;
$abbc_tags[$tag]['subset']     = ABBC_SIMPLE;

$tag = 'i';
$abbc_tags[$tag]['htmlopen0']  = "<i>";
$abbc_tags[$tag]['htmlcont0']  = "\$1";
$abbc_tags[$tag]['htmlclose0'] = "</i>";
$abbc_tags[$tag]['textcont0']  = "\$1";
$abbc_tags[$tag]['htmlblock']  = false;
$abbc_tags[$tag]['minparam']   = 0;
$abbc_tags[$tag]['maxparam']   = 0;
$abbc_tags[$tag]['openclose']  = true;
$abbc_tags[$tag]['nocase']     = true;
$abbc_tags[$tag]['nested']     = true;
$abbc_tags[$tag]['proccont']   = true;
$abbc_tags[$tag]['subset']     = ABBC_SIMPLE;

$tag = 'u';
$abbc_tags[$tag]['htmlopen0']  = "<u>";
$abbc_tags[$tag]['htmlcont0']  = "\$1";
$abbc_tags[$tag]['htmlclose0'] = "</u>";
$abbc_tags[$tag]['textcont0']  = "\$1";
$abbc_tags[$tag]['htmlblock']  = false;
$abbc_tags[$tag]['minparam']   = 0;
$abbc_tags[$tag]['maxparam']   = 0;
$abbc_tags[$tag]['openclose']  = true;
$abbc_tags[$tag]['nocase']     = true;
$abbc_tags[$tag]['nested']     = true;
$abbc_tags[$tag]['proccont']   = true;
$abbc_tags[$tag]['subset']     = ABBC_SIMPLE;

$tag = 's';
$abbc_tags[$tag]['htmlopen0']  = "<s>";
$abbc_tags[$tag]['htmlcont0']  = "\$1";
$abbc_tags[$tag]['htmlclose0'] = "</s>";
$abbc_tags[$tag]['textcont0']  = "\$1";
$abbc_tags[$tag]['htmlblock']  = false;
$abbc_tags[$tag]['minparam']   = 0;
$abbc_tags[$tag]['maxparam']   = 0;
$abbc_tags[$tag]['openclose']  = true;
$abbc_tags[$tag]['nocase']     = true;
$abbc_tags[$tag]['nested']     = true;
$abbc_tags[$tag]['proccont']   = true;
$abbc_tags[$tag]['subset']     = ABBC_SIMPLE;

$tag = 'o';
$abbc_tags[$tag]['htmlopen0']  = "<span style=\"border-top:1px solid black;margin-top:1px;\">";
$abbc_tags[$tag]['htmlcont0']  = "\$1";
$abbc_tags[$tag]['htmlclose0'] = "</span>";
$abbc_tags[$tag]['textcont0']  = "\$1";
$abbc_tags[$tag]['htmlopen1']  = "<span style=\"border-top:1px solid \$1;margin-top:1px;\">";
$abbc_tags[$tag]['htmlcont1']  = "\$2";
$abbc_tags[$tag]['htmlclose1'] = "</span>";
$abbc_tags[$tag]['textcont1']  = "\$1";
$abbc_tags[$tag]['htmlblock']  = false;
$abbc_tags[$tag]['minparam']   = 0;
$abbc_tags[$tag]['maxparam']   = 1;
$abbc_tags[$tag]['openclose']  = true;
$abbc_tags[$tag]['nocase']     = true;
$abbc_tags[$tag]['nested']     = true;
$abbc_tags[$tag]['proccont']   = true;
$abbc_tags[$tag]['subset']     = ABBC_SIMPLE;

$tag = 'm';
$abbc_tags[$tag]['htmlopen0']  = "<tt>";
$abbc_tags[$tag]['htmlcont0']  = "\$1";
$abbc_tags[$tag]['htmlclose0'] = "</tt>";
$abbc_tags[$tag]['textcont0']  = "\$1";
$abbc_tags[$tag]['htmlblock']  = false;
$abbc_tags[$tag]['minparam']   = 0;
$abbc_tags[$tag]['maxparam']   = 0;
$abbc_tags[$tag]['openclose']  = true;
$abbc_tags[$tag]['nocase']     = true;
$abbc_tags[$tag]['nested']     = true;
$abbc_tags[$tag]['proccont']   = true;
$abbc_tags[$tag]['subset']     = ABBC_SIMPLE;

$tag = 'url';
$abbc_tags[$tag]['htmlopen0']  = "<a href=\"$1\" target=\"_blank\">";
$abbc_tags[$tag]['htmlcont0']  = "\$1";
$abbc_tags[$tag]['htmlclose0'] = "</a>";
$abbc_tags[$tag]['textcont0']  = "\$1";
$abbc_tags[$tag]['htmlopen1']  = "<a href=\"$1\" target=\"_blank\">";
$abbc_tags[$tag]['htmlcont1']  = "\$2";
$abbc_tags[$tag]['htmlclose1'] = "</a>";
$abbc_tags[$tag]['textcont1']  = "\$2 [\$1]";
$abbc_tags[$tag]['htmlblock']  = false;
$abbc_tags[$tag]['minparam']   = 0;
$abbc_tags[$tag]['maxparam']   = 1;
$abbc_tags[$tag]['openclose']  = true;
$abbc_tags[$tag]['nocase']     = true;
$abbc_tags[$tag]['nested']     = false;
$abbc_tags[$tag]['proccont']   = true;
$abbc_tags[$tag]['subset']     = ABBC_URL;

$tag = 'mail';
$abbc_tags[$tag]['htmlopen0']  = "<a href=\"mailto:\$1\">";
$abbc_tags[$tag]['htmlcont0']  = "\$1";
$abbc_tags[$tag]['htmlclose0'] = "</a>";
$abbc_tags[$tag]['textcont0']  = "\$1";
$abbc_tags[$tag]['htmlopen1']  = "<a href=\"mailto:\$1\">";
$abbc_tags[$tag]['htmlcont1']  = "\$2";
$abbc_tags[$tag]['textcont0']  = "\$2 [\$1]";
$abbc_tags[$tag]['htmlclose1'] = "</a>";
$abbc_tags[$tag]['htmlblock']  = false;
$abbc_tags[$tag]['minparam']   = 0;
$abbc_tags[$tag]['maxparam']   = 1;
$abbc_tags[$tag]['openclose']  = true;
$abbc_tags[$tag]['nocase']     = true;
$abbc_tags[$tag]['nested']     = false;
$abbc_tags[$tag]['proccont']   = true;
$abbc_tags[$tag]['subset']     = ABBC_URL;

$tag = 'img';
$abbc_tags[$tag]['htmlopen0']  = "";
$abbc_tags[$tag]['htmlcont0']  = "<img src=\"$1\">";
$abbc_tags[$tag]['htmlclose0'] = "";
$abbc_tags[$tag]['textcont0']  = "(Bild: \$1)";
$abbc_tags[$tag]['htmlopen1']  = "";
$abbc_tags[$tag]['htmlcont1']  = "<img src=\"$2\" align=\"$1\">";
$abbc_tags[$tag]['htmlclose1'] = "";
$abbc_tags[$tag]['textcont1']  = "(Bild: \$2)";
$abbc_tags[$tag]['htmlblock']  = false;
$abbc_tags[$tag]['minparam']   = 0;
$abbc_tags[$tag]['maxparam']   = 1;
$abbc_tags[$tag]['openclose']  = true;
$abbc_tags[$tag]['nocase']     = true;
$abbc_tags[$tag]['nested']     = false;
$abbc_tags[$tag]['proccont']   = false;
$abbc_tags[$tag]['subset']     = ABBC_IMG;

$tag = 'br';
$abbc_tags[$tag]['htmlopen0']  = "<br clear=all>";
$abbc_tags[$tag]['htmlcont0']  = "";
$abbc_tags[$tag]['htmlclose0'] = "";
$abbc_tags[$tag]['textcont0']  = "\n";
$abbc_tags[$tag]['htmlblock']  = false;
$abbc_tags[$tag]['minparam']   = 0;
$abbc_tags[$tag]['maxparam']   = 0;
$abbc_tags[$tag]['openclose']  = false;
$abbc_tags[$tag]['nocase']     = true;
$abbc_tags[$tag]['nested']     = false;
$abbc_tags[$tag]['proccont']   = false;
$abbc_tags[$tag]['subset']     = ABBC_SIMPLE;

$tag = 'color';
$abbc_tags[$tag]['htmlopen1']  = "<span style=\"color:\$1\">";
$abbc_tags[$tag]['htmlcont1']  = "\$2";
$abbc_tags[$tag]['htmlclose1'] = "</span>";
$abbc_tags[$tag]['textcont0']  = "\$2";
$abbc_tags[$tag]['htmlblock']  = false;
$abbc_tags[$tag]['minparam']   = 1;
$abbc_tags[$tag]['maxparam']   = 1;
$abbc_tags[$tag]['openclose']  = true;
$abbc_tags[$tag]['nocase']     = true;
$abbc_tags[$tag]['nested']     = true;
$abbc_tags[$tag]['proccont']   = true;
$abbc_tags[$tag]['subset']     = ABBC_FONT;

$tag = 'font';
$abbc_tags[$tag]['htmlopen1']  = "<span style=\"font-family:\$1\">";
$abbc_tags[$tag]['htmlcont1']  = "\$2";
$abbc_tags[$tag]['htmlclose1'] = "</span>";
$abbc_tags[$tag]['textcont1']  = "\$2";
$abbc_tags[$tag]['htmlopen2']  = "<span style=\"font-family:\$1; font-size:\$2px; line-height:120%\">";
$abbc_tags[$tag]['htmlcont2']  = "\$3";
$abbc_tags[$tag]['htmlclose2'] = "</span>";
$abbc_tags[$tag]['textcont2']  = "\$3";
$abbc_tags[$tag]['htmlopen3']  = "<span style=\"font-family:\$1; font-size:\$2px; line-height:\$3px\">";
$abbc_tags[$tag]['htmlcont3']  = "\$4";
$abbc_tags[$tag]['htmlclose3'] = "</span>";
$abbc_tags[$tag]['textcont3']  = "\$4";
$abbc_tags[$tag]['htmlblock']  = false;
$abbc_tags[$tag]['minparam']   = 1;
$abbc_tags[$tag]['maxparam']   = 3;
$abbc_tags[$tag]['openclose']  = true;
$abbc_tags[$tag]['nocase']     = true;
$abbc_tags[$tag]['nested']     = true;
$abbc_tags[$tag]['proccont']   = true;
$abbc_tags[$tag]['subset']     = ABBC_FONT;

$tag = 'size';
$abbc_tags[$tag]['htmlopen1']  = "<span style=\"font-size:\$1px; line-height:120%\">";
$abbc_tags[$tag]['htmlcont1']  = "\$2";
$abbc_tags[$tag]['htmlclose1'] = "</span>";
$abbc_tags[$tag]['textcont1']  = "\$2";
$abbc_tags[$tag]['htmlopen2']  = "<span style=\"font-size:\$1px; line-height:\$2px\">";
$abbc_tags[$tag]['htmlcont2']  = "\$3";
$abbc_tags[$tag]['htmlclose2'] = "</span>";
$abbc_tags[$tag]['textcont2']  = "\$3";
$abbc_tags[$tag]['htmlblock']  = false;
$abbc_tags[$tag]['minparam']   = 1;
$abbc_tags[$tag]['maxparam']   = 2;
$abbc_tags[$tag]['openclose']  = true;
$abbc_tags[$tag]['nocase']     = true;
$abbc_tags[$tag]['nested']     = true;
$abbc_tags[$tag]['proccont']   = true;
$abbc_tags[$tag]['subset']     = ABBC_FONT;

$tag = 'sup';
$abbc_tags[$tag]['htmlopen0']  = "<sup>";
$abbc_tags[$tag]['htmlcont0']  = "\$1";
$abbc_tags[$tag]['htmlclose0'] = "</sup>";
$abbc_tags[$tag]['textcont0']  = "\$1";
$abbc_tags[$tag]['htmlblock']  = false;
$abbc_tags[$tag]['minparam']   = 0;
$abbc_tags[$tag]['maxparam']   = 0;
$abbc_tags[$tag]['openclose']  = true;
$abbc_tags[$tag]['nocase']     = true;
$abbc_tags[$tag]['nested']     = true;
$abbc_tags[$tag]['proccont']   = true;
$abbc_tags[$tag]['subset']     = ABBC_FONT;

$tag = 'sub';
$abbc_tags[$tag]['htmlopen0']  = "<sub>";
$abbc_tags[$tag]['htmlcont0']  = "\$1";
$abbc_tags[$tag]['htmlclose0'] = "</sub>";
$abbc_tags[$tag]['textcont0']  = "\$1";
$abbc_tags[$tag]['htmlblock']  = false;
$abbc_tags[$tag]['minparam']   = 0;
$abbc_tags[$tag]['maxparam']   = 0;
$abbc_tags[$tag]['openclose']  = true;
$abbc_tags[$tag]['nocase']     = true;
$abbc_tags[$tag]['nested']     = true;
$abbc_tags[$tag]['proccont']   = true;
$abbc_tags[$tag]['subset']     = ABBC_FONT;

$tag = 'mark';
$abbc_tags[$tag]['htmlopen1']  = "<span style=\"background-color:\$1\">";
$abbc_tags[$tag]['htmlcont1']  = "\$2";
$abbc_tags[$tag]['htmlclose1'] = "</span>";
$abbc_tags[$tag]['textcont1']  = "\$2";
$abbc_tags[$tag]['htmlblock']  = false;
$abbc_tags[$tag]['minparam']   = 1;
$abbc_tags[$tag]['maxparam']   = 1;
$abbc_tags[$tag]['openclose']  = true;
$abbc_tags[$tag]['nocase']     = true;
$abbc_tags[$tag]['nested']     = true;
$abbc_tags[$tag]['proccont']   = true;
$abbc_tags[$tag]['subset']     = ABBC_FONT;

$tag = 'align';
$abbc_tags[$tag]['htmlopen1']  = "<div style=\"text-align:\$1\">";
$abbc_tags[$tag]['htmlcont1']  = "\$2";
$abbc_tags[$tag]['htmlclose1'] = "</div>";
$abbc_tags[$tag]['textcont1']  = "\$2";
$abbc_tags[$tag]['htmlblock']  = true;
$abbc_tags[$tag]['minparam']   = 1;
$abbc_tags[$tag]['maxparam']   = 1;
$abbc_tags[$tag]['openclose']  = true;
$abbc_tags[$tag]['nocase']     = true;
$abbc_tags[$tag]['nested']     = true;
$abbc_tags[$tag]['proccont']   = true;
$abbc_tags[$tag]['subset']     = ABBC_FONT;

/*$tag = 'line';
$abbc_tags[$tag]['htmlopen0']  = "<br><div style=\"border-top:1px solid #000000; margin:8 0;\"></div>";
$abbc_tags[$tag]['htmlcont0']  = "";
$abbc_tags[$tag]['htmlclose0'] = "";
$abbc_tags[$tag]['textcont0']  = "----------";
$abbc_tags[$tag]['htmlopen1']  = "<br><div style=\"border-top:1px solid \$1; margin:4 0;\"></div>";
$abbc_tags[$tag]['htmlcont1']  = "";
$abbc_tags[$tag]['htmlclose1'] = "";
$abbc_tags[$tag]['textcont1']  = "----------";
$abbc_tags[$tag]['htmlopen2']  = "<br><div style=\"border-top:\$2px solid \$1; margin:8 0;\"></div>";
$abbc_tags[$tag]['htmlcont2']  = "";
$abbc_tags[$tag]['htmlclose2'] = "";
$abbc_tags[$tag]['textcont2']  = "----------";
$abbc_tags[$tag]['htmlblock']  = true;
$abbc_tags[$tag]['minparam']   = 0;
$abbc_tags[$tag]['maxparam']   = 2;
$abbc_tags[$tag]['openclose']  = false;
$abbc_tags[$tag]['nocase']     = true;
$abbc_tags[$tag]['nested']     = false;
$abbc_tags[$tag]['proccont']   = false;
$abbc_tags[$tag]['subset']     = ABBC_SIMPLE;*/

$tag = 'list';
$abbc_tags[$tag]['htmlopen0']  = "<ul>";
$abbc_tags[$tag]['htmlcont0']  = "\$1";
$abbc_tags[$tag]['htmlclose0'] = "</ul>";
$abbc_tags[$tag]['textcont0']  = "\$1";
$abbc_tags[$tag]['htmlopen1']  = "~abbc_begin_list(\"\$1\").";
$abbc_tags[$tag]['htmlcont1']  = "\"\$2\".";
$abbc_tags[$tag]['htmlclose1'] = "abbc_end_list(\"\$1\")";
$abbc_tags[$tag]['textcont1']  = "\$2";
$abbc_tags[$tag]['htmlblock']  = true;
$abbc_tags[$tag]['minparam']   = 0;
$abbc_tags[$tag]['maxparam']   = 1;
$abbc_tags[$tag]['openclose']  = true;
$abbc_tags[$tag]['nocase']     = true;
$abbc_tags[$tag]['nested']     = true;
$abbc_tags[$tag]['proccont']   = true;
$abbc_tags[$tag]['subset']     = ABBC_LIST;

$tag = '*';
$abbc_tags[$tag]['htmlopen0']  = "<li>";
$abbc_tags[$tag]['htmlcont0']  = "";
$abbc_tags[$tag]['htmlclose0'] = "";
$abbc_tags[$tag]['textcont0']  = "\n* ";
$abbc_tags[$tag]['htmlblock']  = false;
$abbc_tags[$tag]['minparam']   = 0;
$abbc_tags[$tag]['maxparam']   = 0;
$abbc_tags[$tag]['openclose']  = false;
$abbc_tags[$tag]['nocase']     = true;
$abbc_tags[$tag]['nested']     = false;
$abbc_tags[$tag]['proccont']   = false;
$abbc_tags[$tag]['subset']     = ABBC_LIST;

#$tag = ':';
#$abbc_tags[$tag]['htmlopen1']  = "~abbc_disp_smiley(\"\$1\")";
#$abbc_tags[$tag]['htmlcont1']  = "";
#$abbc_tags[$tag]['htmlclose1'] = "";
#$abbc_tags[$tag]['htmlblock']  = false;
#$abbc_tags[$tag]['minparam']   = 1;
#$abbc_tags[$tag]['maxparam']   = 1;
#$abbc_tags[$tag]['openclose']  = false;
#$abbc_tags[$tag]['nocase']     = true;
#$abbc_tags[$tag]['nested']     = false;
#$abbc_tags[$tag]['proccont']   = false;
#$abbc_tags[$tag]['subset']     = ABBC_IMG;

$tag = 'indent';
$abbc_tags[$tag]['htmlopen0']  = "<div style=\"margin-left:20px\">";
$abbc_tags[$tag]['htmlcont0']  = "\$1";
$abbc_tags[$tag]['htmlclose0'] = "</div>";
$abbc_tags[$tag]['textcont0']  = "\$1";
$abbc_tags[$tag]['htmlopen1']  = "<div style=\"margin-left:\$1px\">";
$abbc_tags[$tag]['htmlcont1']  = "\$2";
$abbc_tags[$tag]['htmlclose1'] = "</div>";
$abbc_tags[$tag]['textcont1']  = "\$2";
$abbc_tags[$tag]['htmlblock']  = true;
$abbc_tags[$tag]['minparam']   = 0;
$abbc_tags[$tag]['maxparam']   = 1;
$abbc_tags[$tag]['openclose']  = true;
$abbc_tags[$tag]['nocase']     = true;
$abbc_tags[$tag]['nested']     = true;
$abbc_tags[$tag]['proccont']   = true;
$abbc_tags[$tag]['subset']     = ABBC_FONT;

unset($tag);

// Smiley Definitions

// Following information is necessary for a smiley:
//   code    the smiley's code -- NO "< > & [ ]" here!
//   img     image to be displayed
//   nocase  case-insensitive code (default, recommended) -- this is currently ignored: smileys ARE case-sensitive!
//   align   how to align the smiley <img>

// Note: For maximum performance with smileys, you should sort the smileys
//       in order of usage. So the most used smiley is defined first, aso.

$c = 0;

$abbc_smileys[$c]['code']   = ":-)";
$abbc_smileys[$c]['img']    = "smile.gif";
$abbc_smileys[$c]['nocase'] = true;
$abbc_smileys[$c]['align']  = "absmiddle";

$c++;
$abbc_smileys[$c]['code']   = ";-)";
$abbc_smileys[$c]['img']    = "wink.gif";
$abbc_smileys[$c]['nocase'] = true;
$abbc_smileys[$c]['align']  = "absmiddle";

$c++;
$abbc_smileys[$c]['code']   = ":cheesy:";
$abbc_smileys[$c]['img']    = "cheesy.gif";
$abbc_smileys[$c]['nocase'] = true;
$abbc_smileys[$c]['align']  = "absmiddle";

$c++;
$abbc_smileys[$c]['code']   = ":-D";
$abbc_smileys[$c]['img']    = "grins.gif";
$abbc_smileys[$c]['nocase'] = true;
$abbc_smileys[$c]['align']  = "absmiddle";

$c++;
$abbc_smileys[$c]['code']   = ":-p";
$abbc_smileys[$c]['img']    = "razz.png";
$abbc_smileys[$c]['nocase'] = true;
$abbc_smileys[$c]['align']  = "absmiddle";

$c++;
$abbc_smileys[$c]['code']   = ":-/";
$abbc_smileys[$c]['img']    = "slash.gif";
$abbc_smileys[$c]['nocase'] = true;
$abbc_smileys[$c]['align']  = "absmiddle";

$c++;
$abbc_smileys[$c]['code']   = ":#:";
$abbc_smileys[$c]['img']    = "confuse.gif";
$abbc_smileys[$c]['nocase'] = true;
$abbc_smileys[$c]['align']  = "absmiddle";

$c++;
$abbc_smileys[$c]['code']   = ":-(";
$abbc_smileys[$c]['img']    = "sad.gif";
$abbc_smileys[$c]['nocase'] = true;
$abbc_smileys[$c]['align']  = "absmiddle";

$c++;
$abbc_smileys[$c]['code']   = ":'(";
$abbc_smileys[$c]['img']    = "cry.png";
$abbc_smileys[$c]['nocase'] = true;
$abbc_smileys[$c]['align']  = "absmiddle";

$c++;
$abbc_smileys[$c]['code']   = ":cool:";
$abbc_smileys[$c]['img']    = "cool.gif";
$abbc_smileys[$c]['nocase'] = true;
$abbc_smileys[$c]['align']  = "absmiddle";

$c++;
$abbc_smileys[$c]['code']   = ":motz:";
$abbc_smileys[$c]['img']    = "motz.gif";
$abbc_smileys[$c]['nocase'] = true;
$abbc_smileys[$c]['align']  = "absmiddle";

$c++;
$abbc_smileys[$c]['code']   = ":finger:";
$abbc_smileys[$c]['img']    = "finger.gif";
$abbc_smileys[$c]['nocase'] = true;
$abbc_smileys[$c]['align']  = "absmiddle";

$c++;
$abbc_smileys[$c]['code']   = ":red:";
$abbc_smileys[$c]['img']    = "redface.gif";
$abbc_smileys[$c]['nocase'] = true;
$abbc_smileys[$c]['align']  = "absmiddle";

$c++;
$abbc_smileys[$c]['code']   = ":*)";
$abbc_smileys[$c]['img']    = "clown.gif";
$abbc_smileys[$c]['nocase'] = true;
$abbc_smileys[$c]['align']  = "absmiddle";

$c++;
$abbc_smileys[$c]['code']   = ":zzz:";
$abbc_smileys[$c]['img']    = "sleep.gif";
$abbc_smileys[$c]['nocase'] = true;
$abbc_smileys[$c]['align']  = "absmiddle";

$c++;
$abbc_smileys[$c]['code']   = ":heart:";
$abbc_smileys[$c]['img']    = "heart.gif";
$abbc_smileys[$c]['nocase'] = true;
$abbc_smileys[$c]['align']  = "absmiddle";

$c++;
$abbc_smileys[$c]['code']   = ":moody:";
$abbc_smileys[$c]['img']    = "moody.gif";
$abbc_smileys[$c]['nocase'] = true;
$abbc_smileys[$c]['align']  = "absmiddle";

$c++;
$abbc_smileys[$c]['code']   = ":hr:";
$abbc_smileys[$c]['img']    = "hr.gif";
$abbc_smileys[$c]['nocase'] = true;
$abbc_smileys[$c]['align']  = "absmiddle";

$c++;
$abbc_smileys[$c]['code']   = ":ohr:";
$abbc_smileys[$c]['img']    = "ohren.png";
$abbc_smileys[$c]['nocase'] = true;
$abbc_smileys[$c]['align']  = "absmiddle";

$c++;
$abbc_smileys[$c]['code']   = ":-O";
$abbc_smileys[$c]['img']    = "gaehn.gif";
$abbc_smileys[$c]['nocase'] = true;
$abbc_smileys[$c]['align']  = "absmiddle";

$c++;
$abbc_smileys[$c]['code']   = ":rolleyes:";
$abbc_smileys[$c]['img']    = "rolleyes.gif";
$abbc_smileys[$c]['nocase'] = true;
$abbc_smileys[$c]['align']  = "absmiddle";

$c++;
$abbc_smileys[$c]['code']   = "8-(";
$abbc_smileys[$c]['img']    = "shocked.gif";
$abbc_smileys[$c]['nocase'] = true;
$abbc_smileys[$c]['align']  = "absmiddle";

$c++;
$abbc_smileys[$c]['code']   = ":anx:";
$abbc_smileys[$c]['img']    = "uhoh.gif";
$abbc_smileys[$c]['nocase'] = true;
$abbc_smileys[$c]['align']  = "absmiddle";

$c++;
$abbc_smileys[$c]['code']   = ":wand:";
$abbc_smileys[$c]['img']    = "wand.gif";
$abbc_smileys[$c]['nocase'] = true;
$abbc_smileys[$c]['align']  = "absmiddle";

$c++;
$abbc_smileys[$c]['code']   = ":vogel:";
$abbc_smileys[$c]['img']    = "vogel.gif";
$abbc_smileys[$c]['nocase'] = true;
$abbc_smileys[$c]['align']  = "absmiddle";

?>
