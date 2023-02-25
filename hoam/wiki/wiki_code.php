<?php
/*
 * This file is part of HOAM, copyright (C) 2002-2020 Robert Butler
 *
 * HOAM is free software; you can redistribute it and/or modify it under the
 * terms of the GNU Affero General Public License as published by the Free
 * Software Foundation; either version 3 of the License, or (at your option)
 * any later version.
 *
 * HOAM is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE. See the GNU Affero General Public License for more
 * details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with HOAM; if not, see http://www.gnu.org/licenses or write to the
 * Free Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301
 *
 * Questions specific to HOAM should be directed to ARP Realty. Please see
 * the HOAM web site at http://hoam.arprs.com/
 *
 * Some portions of HOAM incorporate ideas and/or code from other sources, and
 * those portions are explicitly mentioned and attributed in the relevant
 * section of HOAM source code. Questions about that code should be directed to
 * the original authors.
 *
 */

/* The following also exist in the wiki class itself:

	// Process any attachments listed.
	$attachment_sub = '/\[\[attach:(.*?)\]\]/i';

	// Process any attachments listed.
	$wiki_tree_sub = '/\[\[tree:(.*?)\]\]/i';

	// Process any attachments listed.
	$wiki_url_sub = '/\[\[url:(.*?)\]\]/i';

	// Process any multicolor text
	$color_sub = "/%%%(.*?)%%%/sim";
*/
 
$_HOAM_wiki_code = array (
	"/======= (.+?) =======/" => "<h6>\\1</h6>", // h6
	"/====== (.+?) ======/" => "<h5>\\1</h5>", // h5
	"/===== (.+?) =====/" => "<h4>\\1</h4>", // h4
	"/==== (.+?) ====/" => "<h3>\\1</h3>", // h3
	"/=== (.+?) ===/" => "<h2>\\1</h2>", // h2
	"/== (.+?) ==/" => "<h1>\\1</h1>", // h1
	
	"/\'\'\'\'\'(.*?)\'\'\'\'\'/sim" => "<span style=\"font-style:italic;font-weight:bold\">\\1</span>", // bold & italic text
	"/\'\'\'(.*?)\'\'\'/sim" => "<span style=\"font-weight:bold\">\\1</span>", // bold text
	"/\'\'(.*?)\'\'/sim" => "<span style=\"font-style:italic\">\\1</span>", // italic text
	"/__(.*?)__/sim" => "<span style=\"text-decoration:underline\">\\1</span>", // underlined text
	"/--(.*?)--/sim" => "<span style=\"text-decoration:line-through\">\\1</span>", // strikethrough text

	"/----/" => "<hr />", // horizontal line
	"/\[nl\]/i" => "<br />", // force new line
	"/>>/" => "&nbsp;&nbsp;", // indented text

	"/\[\[email:(.*?)\|(.*?)\]\]/i" => "<a href=\"mailto:\\1\" title=\"\\1\">\\2&nbsp;<img class=\"noprint\" src=\"/hoam/images/icons/email_link.png\" alt=\"Send an Email\"/></a>", // email link with alternative text
	"/\[\[email:(.*?)\]\]/i" => "<a href=\"mailto:\\1\" title=\"\\1\">\\1&nbsp;<img class=\"noprint\" src=\"/hoam/images/icons/email_link.png\" alt=\"Send an Email\"/></a>", // email link
	"/\[\[email:(.*?)@(.*?)\|(.*?)\]\]/i" => "<a href=\"mailto:\\1@\\2\" title=\"\\1@\\2\">\\3&nbsp;<img class=\"noprint\" src=\"/hoam/images/icons/email_link.png\" alt=\"Send an Email\"/></a>", // email link with alternative text
	"/\[\[email:(.*?)@(.*?)\]\]/i" => "<a href=\"mailto:\\1@\\2\" title=\"\\1@\\2\">\\1@\\2&nbsp;<img class=\"noprint\" src=\"/hoam/images/icons/email_link.png\" alt=\"Send an Email\"/></a>", // email link

	// The external.png file is copied from MediaWiki
	"/\[\[((ftp|http|https):\/\/(.*?))\|(.*?)\]\]/i" => "<a href=\"\\1\" title=\"\\4\">\\4&nbsp;<img class=\"noprint\" src=\"/hoam/images/external.png\" alt=\"Link to External Site\" /></a>", // external http:link with alternative text
	"/\[\[((ftp|http|https):\/\/(.*?))\]\]/i" => "<a href=\"\\1\" title=\"\\1\">\\1&nbsp;<img class=\"noprint\" src=\"/hoam/images/external.png\" alt=\"Link to External Site\" /></a>", // external http:link

	"/\[\[note:(.*?)\]\]/i" => "<div class=\"article_note\">\\1</div>", // article note
	"/\[\[noter:(.*?)\]\]/i" => "<div class=\"article_note RHS\">\\1</div>", // article note right side of screen
	
	// Internal links only use one bracket
	"/\[((ftp|http|https):(.*?))\|(.*?)\]/i" => "<a href=\"\\3\" title=\"\\4\">\\4</a>", // internal http:link with alternative text
	"/\[((ftp|http|https):(.*?))\]/i" => "<a href=\"\\3\" title=\"\\3\">\\3</a>", // internal http:link

//	"/\[\[img:(.*?)\]\]/i" => "<img src=\"" . $UploadsDir . "/\\1\" alt=\"\" />", // image
//	"/\[\[img:(.*?) \| (.*?)\]\]/i" => "<img src=\"" . $UploadsDir . "/\\1\" alt=\"\\2\" /><br /><small>\\2</small>", // image with caption

	"/\[\[wiki:(.*?)\|(.*?)\]\]/i" => "<a href=\"\\1\" title=\"\\2\">\\2</a>", // internal wiki link with alternative text
	"/\[\[wiki:(.*?)\]\]/i" => "<a href=\"\\1\">\\1</a>", // internal wiki link

	// From Wilky (https://github.com/lahdekorpi/Wiky.php/blob/3bbc84b48de20997795cef4ca534f54dc8d50d1e/wiky.inc.php)
	// Ordered list
	"/[\n\r]#.+([\n|\r]#.+)+/" => "<ol>$0</ol>", // First pass, finding all blocks
	"/[\n\r]#(?!#) *(.+)(([\n\r]#{2,}.+)+)/" => "<li>$1<ol>$2</ol></li>", // List item with sub items of 2 or more
	"/[\n\r]#{2}(?!#) *(.+)(([\n\r]#{3,}.+)+)/" => "<li>$1<ol>$2</ol></li>", // List item with sub items of 3 or more
	"/[\n\r]#{3}(?!#) *(.+)(([\n\r]#{4,}.+)+)/" => "<li>$1<ol>$2</ol></li>", // List item with sub items of 4 or more

	// From Wilky (https://github.com/lahdekorpi/Wiky.php/blob/3bbc84b48de20997795cef4ca534f54dc8d50d1e/wiky.inc.php)
	// Unordered list
	"/[\n\r]\*.+([\n|\r]\*.+)+/" => "<ul>$0</ul>", // First pass, finding all blocks
	"/[\n\r]\*(?!\*) *(.+)(([\n\r]\*{2,}.+)+)/" => "<li>$1<ul>$2</ul></li>", // List item with sub items of 2 or more
	"/[\n\r]\*{2}(?!\*) *(.+)(([\n\r]\*{3,}.+)+)/" => "<li>$1<ul>$2</ul></li>", // List item with sub items of 3 or more
	"/[\n\r]\*{3}(?!\*) *(.+)(([\n\r]\*{4,}.+)+)/" => "<li>$1<ul>$2</ul></li>", // List item with sub items of 4 or more

	// From Wilky (https://github.com/lahdekorpi/Wiky.php/blob/3bbc84b48de20997795cef4ca534f54dc8d50d1e/wiky.inc.php)
	// List items
	"/^[#\*]+ *(.+)$/m" => "<li>$1</li>", // Wraps all list items to <li/>

//	"/[\n\r]: *.+([\n\r]:+.+)*/" => "<dl>$0</dl>", // Indentation first pass
//	"/^:(?!:) *(.+)$/m" => "<dd>$1</dd>", // Indentation second pass
//	"/([\n\r]:: *.+)+/" => "<dd><dl>$0</dl></dd>", // Subindentation first pass
//	"/^:: *(.+)$/m" => "<dd>$1</dd>", // Subindentation second pass

	// The pre might still need some tweaking.
	"/^\{\{\{(.*?)^\}\}\}/sm" => "<pre>\\1</spre>", // preformatted text

	// Testing paragraphs
//	"/^(.+?)[\n\r]{2,}/im" => "<p>\\1</p>",

    );
 
?>
