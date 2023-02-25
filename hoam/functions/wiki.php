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

function HOAM_wikiDisplayTemplate ($article_id) {
	global $_HOAM_language, $_HOAM_setting;

	$article_class = new HOAM_article ('articles');
	$group_members_class = new HOAM_group_members ('group_members');
	
	if (HOAM_wikiUserCanAccess ($article_id)) {
		$HTML = '<h1 id="page_title">';
		// Display the Article tools
		// Only display the edit button if the user has the necessary privileges.
		if (isset ($_SESSION['current_user']['id']) AND
			($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
			$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['article_edit'], $_SESSION['current_user']['id']) OR
			($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id']))) {
			$HTML .= '<a href="/website/wiki/edit/' . $article_id . '/" class="donotprint" title="Edit This Document"><img class="article_tool" src="/hoam/images/icons/page_white_edit.png" alt="[ Edit ]" /></a>';
		}
		// Make sure we don't try to go 'up' in the tree if we're already at the root.
		if ($article_id != $_HOAM_setting ['article']['ids']['root']) {
			$HTML .= '<a href="';
			$HTML .= HOAM_wikiURL ($article_class -> returnRootId ($article_id));
			$HTML .= '" class="donotprint" title="Go Up One Level"><img class="article_tool" src="/hoam/images/icons/arrow_up.png" alt="[ Up ]" /></a>';
		}

		// Display the Article headline
		$HTML .= stripslashes ($article_class -> returnTitle ($article_id));
		$HTML .= '</h1>';

		$ad = HOAM_returnAdvertisement ($_HOAM_setting ['advertising']['flags']['position3']);
		if (!empty ($ad)) {
			$HTML .= $ad;
		}
		unset ($ad);

		// Display the Article summary (if it exists)
		$HTML .= HOAM_wikiReturnSummary ($article_id);

		// Display the Article text
		$HTML .= stripslashes (str_replace ('\r\n', "\r\n", $article_class -> returnArticle ($article_id)));
	} else {
		// User does not have the necessary permissions.
		header ('Location: /error/403/');
		exit ();
	}
	echo $HTML;
}

function HOAM_wikiReturnPageList ($url_pre, $url_post, $current_page_number, $item_count, $items_per_page, $page_count_limit = 10) {
	// Display page counters / links
	$question_position = mb_strpos ($url_pre, '?');
	if ($question_position != 0) {
		$page_get = html_encode (mb_substr ($url_pre, $question_position));
		$page_url = mb_substr ($url_pre, 0, $question_position);
	} else {
		$page_get = NULL;
	}

	$HTML = '<div style="margin-bottom: .5em; text-align: center">';

	// Display item count
	$HTML .= ' Showing items: ' . (($current_page_number * $items_per_page) + 1) . ' - ';
	if ((($current_page_number + 1) * $items_per_page) <= $item_count) {
		$HTML .= ($current_page_number + 1) * $items_per_page;
	} else {
		$HTML .= ($current_page_number * $items_per_page) + ($item_count % $items_per_page);
	}
	$HTML .= ' (of ' . $item_count . ' total)<br/>';

	// How many total pages are there?
	$total_page_count = ceil ($item_count / $items_per_page);

	// Display Previous / Next page links

	// Display quick link to the first page.
	if (($total_page_count > 10) && ($current_page_number > 1)) {
		$HTML .= '<a href="' . $url_pre . $url_post . $page_get . '">&lt;&lt; </a>';
	}
	// Don't display the 'Previous Page' link if we're on the first page.
	if ($current_page_number > 1) {
		$HTML .= '<a href="' . $url_pre . 'page/' . $current_page_number . '/' . $url_post . $page_get . '"> Previous Page</a> ';
		// Don't display a page number for the '0' page, just link to page_url.
	} elseif ($current_page_number == 1) {
		$HTML .= '<a href="' . $url_pre . $url_post . $page_get . '"> Previous Page</a> ';
	}

	// If we have enough items to display that it will take more than two pages,
	// add page numbers to the screen for faster selection.
	if ($total_page_count > 2) {
		// If the page_count_limit is greater than how many pages we have, just
		// display those.
		if ($page_count_limit >= $total_page_count) {
			$i = 0;
			$page_counter = $total_page_count;
			// If we have more than page_count_limit pages, and we are on less
			// than the 5th page, don't change the page display
		} elseif ($current_page_number < 5) {
			$i = 0;
			$page_counter = $page_count_limit;
			// If we are near the end of the list, make sure we don't display
			// more pages than available.
		} elseif (($total_page_count - $current_page_number) < 6 )  {
			$i = $total_page_count - $page_count_limit;
			$page_counter = $total_page_count;
			// We're somewhere in the page list. Keep the current page in the
			// middle.
		} else {
			$i = $current_page_number - 4;
			$page_counter = $i + $page_count_limit;
		}
		for (; $i < $page_counter; $i ++) {
			// If we're currently on the page that equals this page number,
			// don't create a link for it.
			if ($i == $current_page_number) {
				$HTML .= $i + 1 . ' ';
				// Don't display a page number for the '0' page, just link to
				// page_url.
			} elseif ($i == 0 ) {
				$HTML .= '<a href="' . $url_pre . $url_post . $page_get . '">' . ($i + 1) . '</a> ';
			} else {
				$HTML .= '<a href="' . $url_pre . 'page/' . ($i + 1) . '/' . $url_post .  $page_get . '">' . ($i + 1 ) . '</a> ';
			}
		}
	}

	// Don't display the 'Next Page' link if we're on the last page.
	if (($total_page_count - $current_page_number) != 1) {
		$HTML .= '&nbsp;';
		$HTML .= '<a href="' . $url_pre . 'page/' . ($current_page_number + 2) . '/' . $url_post . $page_get . '">Next Page </a>';
	}
	// Display quick link to the last page.
	if ( ($total_page_count > 10) && ($total_page_count != $current_page_number + 1)){
		$HTML .= '<a href="' . $url_pre . 'page/' . $total_page_count . '/' . $url_post . $page_get . '"> &gt;&gt;</a>';
	}

	$HTML .= '</div>';
	return $HTML;
}

function HOAM_wikiReturnSummary ($article_id) {
	$article_class = new HOAM_article ('articles');

	$HTML = (string) NULL;
	$article_summary = $article_class -> returnSummary ($article_id);
	if (!empty ($article_summary)) {
		$HTML .= '<div class="article_summary">';
		$HTML .= '<strong>Summary: </strong><br/>';
		ob_start ();
		// We need to eval() the article summary because it's possible to
		// include PHP code.
		eval ('?>' . stripslashes (str_replace ('\r\n', "\r\n", $article_summary)) . '<?php ');
		$HTML .= ob_get_contents ();
		ob_end_clean ();
		$HTML .= ' (' . $article_class -> returnWordCount ($article_id) . ' words)';
		$HTML .= '</div>';
	}
	return $HTML;
}

function HOAM_wikiSearchResult ($article_id, $article_score) {
	// Make sure the $article_id given exists.
	// NOTE! privilege checking must be done
	// PRIOR to sending this function an article to display!
	// This is handled by the search function in class.news.php, but
	// needs to be remembered.

	$article_class = new HOAM_article ('articles');

	if ($article_class -> idExists ($article_id)) {
		$HTML  = '<div class="searchResult">';
		$HTML .= '<h1><a href="' . HOAM_wikiURL ($article_id);
		$HTML .= '">' . stripslashes ($article_class -> returnTitle ($article_id)) . '</a></h1>';
		$maxlength = 100;
		$article_summary = strip_tags (str_replace ('\r', "\r", HOAM_wikiReturnSummary ($article_id)));
		$article_summary_lenth = mb_strlen ($article_summary);
		if ($article_summary_lenth > $maxlength) {
			$HTML .= '<p>' . mb_substr ($article_summary, 0, $maxlength) . '&hellip;</p>';
		} elseif ($article_summary_lenth > 0) {
			$HTML .= '<p>' . $article_summary . '</p>';
		} else {
			$article_text = $article_class -> returnArticle ($article_id);
			$article_text = str_replace ('\r\n', "\r\n", $article_text);
			$article_text = preg_replace ('@<style[^>]*?>.*?</style>@siU', '', $article_text);
			$HTML .= '<p>' . mb_substr (stripslashes (strip_tags ($article_text)), 0, $maxlength) . '&hellip;</p>';
		}
		$HTML .= '<span>http://' . $_SERVER['SERVER_NAME'] . HOAM_wikiURL ($article_id) . '</span>';
		$HTML .= '</div>';
		return $HTML;
	}
	return FALSE;

}
 
function HOAM_wikiTree ($article_id) {
	// Return a string containing a unsigned list (<ul></ul>) of all articles
	// that have $article_id as their root_id
	global $_HOAM_language, $_HOAM_setting;

	$article_class = new HOAM_article ('articles');

	// Need to add separate $order and $child_order options

	// Make sure the $article_id given exists.
	if ($article_class -> idExists ($article_id)) {

		// We need to get some information about the current user, to determine
		// whether we should show them that an article exists or not.
		$children_array = $article_class -> returnChildren ($article_id, ' ORDER BY title ASC');
		if ($children_array) {
			$children_count = count ($children_array);
			$iteration = 0;
			$string  = '<ul>';
			while ($iteration < $children_count) {
				// We need to first determine whether the user has the necessary
				// privileges to access the article. Also, we don't want to try
				// and recurse under an article that we don't have permission to
				// view.
				if (HOAM_wikiUserCanAccess ($children_array[$iteration])) {
					$string .= '<li><a href="' . HOAM_wikiURL ($children_array[$iteration]) . '">' . stripslashes ($article_class -> returnTitle ($children_array[$iteration])) . '</a>';
					// Check for children of this article.  BUT, don't recurse
					// if someone screwed up and has a document with itself for
					// the root_id
					if ($children_array[$iteration] != $article_id) {
						$string .= HOAM_wikiTree ($children_array[$iteration]);
					}
					$string .= '</li>';
				}
				$iteration++;
			}
			$string .= '</ul>';
			return $string;
		}
	} else {
		return FALSE;
	}
}

function HOAM_wikiTreeCallback ($matches) {
	return (HOAM_wikiTree ($matches[1]));
}

function HOAM_wikiURL ($article_id) {
	// Return a string containing the full URL of an article, given only the
	// article id#.
	//
	// Note!
	// This function has been changed to validate that each and every article_id
	// it looks for is valid. This was done because if the database is
	// manipulated manually, it's possible to end up with an article that has an
	// invalid root_id. It's now slower, but safer. Previously, we simply
	// checked to make sure that the provided article_id was valid, meaning that
	// we would end up in an infinite loop if we had an invalid root_id.
	global $_HOAM_setting;

	$article_class = new HOAM_article ('articles');
	
	if ($article_class -> idExists ($article_id)) {
		$path = '/';
		$count = 0;
		if (!empty ($_HOAM_setting ['article']['ids']['root'])) {
			while ($article_id != $_HOAM_setting ['article']['ids']['root']) {
				if ($article_class -> idExists ($article_id) AND ($article_id != FALSE)) {
					$path = '/' . $article_class -> returnURLName ($article_id) . $path;
					$article_id = $article_class -> returnRootId ($article_id);
				} else {
					return FALSE;
				}
			}
		} else {
			return FALSE;
		}
		return $path;
	} else {
		return FALSE;
	}
}
 
function HOAM_wikiURLCallback ($matches) {
	return (HOAM_wikiURL ($matches[1]));
}

function HOAM_wikiUsersOnline () {
	global $_HOAM_language;
	
	$online_count = HOAM_countOnlineUsers ();
	if ($online_count > 1) { 
		return (sprintf ($_HOAM_language ['field_names']['hoam']['online_many'], $online_count));
	} else {
		return ($_HOAM_language ['field_names']['hoam']['online_one']);
	}
	// Something went wrong :)
	return FALSE;
}

function HOAM_wikiUserCanAccess ($article_id, $user_id = NULL) {
	// Can the current user access the article requested?
	// We are setting a variable here rather than simply returning because we
	// are testing for multiple groups... I think. Actually, this is something
	// to look at later and see if it can be simplified. I know I wrote it this
	// way for a reason, but it was some time ago...
	global $_HOAM_setting;
	
	$article_class = new HOAM_article ('articles');
	$group_members_class = new HOAM_group_members ('group_members');

	$can_access = FALSE;
	if ($user_id === NULL) {
		if (isset ($_SESSION['current_user']['id'])) {
			$user_id = $_SESSION['current_user']['id'];
		} else {
			$user_id = $_HOAM_setting ['group']['ids']['anonymous'];
		}
	}
	$groups = unserialize ($article_class -> returnGroups ($article_id));
	if (!empty ($groups)) {
		// Does this user have access to any of the necessary groups?
		foreach ($groups as $group) {
			if ($group_members_class -> isMember ($group, $user_id)) {
				// Yes, the user is a valid member. Break out of the loop.
				$can_access = TRUE;
				break;
			}
		}
	}
	return $can_access;
}

?>
