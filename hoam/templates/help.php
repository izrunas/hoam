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

function HOAM_helpOnlinePay ($h1 = FALSE) {
	global $_HOAM_language, $_HOAM_queue_head;

	HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['help']['onlinepay'])));
	$HTML = '<h1>' . $_HOAM_language ['field_names']['help']['onlinepay'] . '</h1>';
	$HTML .= $_HOAM_language ['help_pages']['help']['onlinepay'];

	return ($HTML);
}

function HOAM_helpSearchHelp () {
	global $_HOAM_country, $_HOAM_language, $_HOAM_setting, $_HOAM_queue_head;

	HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['help']['help'])));
	$HTML = '<h1>' . $_HOAM_language ['field_names']['help']['help'] . '</h1>';
	$HTML .= $_HOAM_language ['help_pages']['help']['help'];

	return ($HTML);
}

function HOAM_helpSearch ($h1 = FALSE) {
	global $_HOAM_country, $_HOAM_language, $_HOAM_setting, $_HOAM_queue_head;
	
	$article_class = new HOAM_article ('articles');
	$news_class = new HOAM_news ('news');

	require_once (HOAM_PATH_FUNCTION . 'wiki.php');
	
	HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['help']['search'])));
	require_once (HOAM_PATH_FUNCTION . 'forms.php');
	require_once (HOAM_PATH_FUNCTION . 'news.php');
	$HTML = '<h1>' . $_HOAM_language ['field_names']['help']['search'] . '</h1>';
	$HTML .= '<form action="/help/search/" method="get">';
	if (isset ($_SESSION['error_class_item']) AND ($_SESSION ['error_class_item'] -> errorCount > 0)) {
		$HTML .= $_HOAM_language ['help_pages']['generic']['errors_exist'];
	}
	$HTML .= '<fieldset>';
	$HTML .= '<button type="submit">' . $_HOAM_language ['common']['submit'] . '</button>';
	$HTML .= '<button type="reset">' . $_HOAM_language ['form_buttons']['reset'] . '</button>';
	$HTML .= '</fieldset>';
	$HTML .= $_HOAM_language ['help_popups']['mandatory'];
	
	$HTML .= '<div class="mandatory">';
	$HTML .= '[[noter:' . $_HOAM_language ['help_pages']['help']['link'] .  ']]'; 
	
	if (isset ($_GET['help|keywords'])) { 
		$trusted_keywords = $article_class -> superClean ($_GET['help|keywords'], 128);
	} else {
		$trusted_keywords = NULL;
	}
	$HTML .= HOAM_formField ('help|keywords', $_HOAM_language ['field_names']['help']['keywords'], stripslashes ($trusted_keywords), '', 30, 1);
	$HTML .= '</div>';
	$HTML .= '<div class="optional">';
	$HTML .= '<label for="help|area">' . $_HOAM_language ['field_names']['help']['area'] . '</label>';
	$HTML .= '<fieldset>';
	// Check to see whether the area of the site to search has been set.
	// If not, set it to the articles.
	if (isset ($_GET['help|area'])) {
		$trusted_area = $article_class -> superClean ($_GET['help|area'], 16);
	} else {
		$trusted_area = 'articles';
	}
	switch ($trusted_area) {
		case 'articles' :
			$HTML .= '<input checked="checked" name="help|area" type="radio" value="articles" />' . $_HOAM_language ['common']['articles'];
			$HTML .= '<input name="help|area" type="radio" value="news" />' . $_HOAM_language ['common']['news'];
			break;
		case 'news' :
			$HTML .= '<input name="help|area" type="radio" value="articles" />' . $_HOAM_language ['common']['articles'];
			$HTML .= '<input checked="checked" name="help|area" type="radio" value="news" />' . $_HOAM_language ['common']['news'];
			break;
		default:
			$HTML .= '<input name="help|area" type="radio" value="articles" />' . $_HOAM_language ['common']['articles'];
			$HTML .= '<input name="help|area" type="radio" value="news" />' . $_HOAM_language ['common']['news'];
			break;
	}
	$HTML .= '</fieldset>';
	$HTML .= '</div>';
	$HTML .= '</form>';
	$HTML .= '<br />';

	if (isset ($trusted_keywords)) {
		// We'll store additions to the default article url
		$local_page_number = 0;
		$offset = $local_page_number * $_SESSION['current_user']['items_per_page'];
				
		// Are there any actions we need to perform?
		if (isset ($GLOBALS['global_url_array'][2])) {
			switch ($GLOBALS['global_url_array'][2]) {
				case 'page':
					$local_page_number = (int) $GLOBALS['global_url_array'][3];
					// Subtract one from the page number to account for the
					// fact that our list begins with zero
					$local_page_number--;
					// Make sure that we are dealing with a positive page
					// number.
					if ($local_page_number < 0) {
						$local_page_number = 0;
					}
					// Where should we start looking for articles?
					$offset = $local_page_number * $_SESSION['current_user']['items_per_page'];
					break;
			}
		}
		// Find out how many total articles there are
		if ($trusted_area === 'articles') {
			$article_list = $article_class -> search ($trusted_keywords);
			$temp_article_list = array ();
			if (!empty ($article_list)) {
				foreach ($article_list as $temp_article) {
					// is the user permitted to view this article?
					if (HOAM_wikiUserCanAccess ($temp_article['id'], $_SESSION['current_user']['id'])) {
						array_push ($temp_article_list, $temp_article);
					}
				}
			}
			$article_list = $temp_article_list;
			$article_count = count ($article_list);
		} elseif ($trusted_area === 'news') {
			$article_list = $news_class -> search ($trusted_keywords, $_SESSION['current_user']['items_per_page'], $offset);
			$article_count = $news_class -> returnCountSearch ($trusted_keywords);
		}

		// We should now have at least a list of articles to display and
		// a count of the total number of articles available.
		if ($article_list) {
			// Display the different page links.
			$HTML .= HOAM_wikiReturnPageList ('/help/search/', '?help|keywords=' . stripslashes ($trusted_keywords) . '&amp;area=' . stripslashes ($trusted_area), $local_page_number, $article_count, $_SESSION['current_user']['items_per_page']);
			// Begin displaying the articles themselves.
			$i = 0;
			if ($trusted_area === 'articles') {
				do {
					$HTML .= HOAM_wikiSearchResult ($article_list[$offset + $i]['id'], $article_list[$i]['score']);
					$i++;
				} while (($i < $_SESSION['current_user']['items_per_page']) AND ($i < $article_count));
			} elseif ($trusted_area === 'news') {
				do {
					$HTML .= HOAM_newsSearchResult ($article_list[$offset + $i]['id'], $article_list[$i]['score']);
					$i++;
				} while (($i < $_SESSION['current_user']['items_per_page']) AND ($i < $article_count));
			}

			// We want to record the keywords being searched for.
			// But, we don't want to artificially inflate the numbers by
			// recording keywords when viewing more than one page. Thus,
			// we only record when there aren't any page actions.
			if (empty ($GLOBALS['global_url_article_actions'])) {
				$search_tracker = new HOAM_tracker ('search_tracker');
				$search_tracker_id = $search_tracker -> findText ($trusted_keywords);

				// Do the keywords already exist in the database?
				if (empty ($search_tracker_id)) {
					$search_tracker_id = $search_tracker -> add ($trusted_keywords);
				} else {
					// We must have received something back when
					// checking to see if the domain already existed.
					$search_tracker_id = $search_tracker_id[0];
				}
				// Increment the link counter
				$search_tracker -> changeDateModified ($search_tracker_id);
				$search_tracker -> changeCount ($search_tracker_id, ($search_tracker -> returnCount ($search_tracker_id) + 1));

				// Clean up
				unset ($search_tracker, $search_tracker_id);
			}
		} else {
			// Finally, if no articles were found matching the criteria
			// given, let the user know.
			$HTML .= '<h2>' . $_HOAM_language ['errors']['help']['result'] . '</h2>';
			$HTML .= '<p>' . sprintf ($_HOAM_language ['errors']['help']['no-results'], $trusted_keywords) . '</p>';
		}
	}
	return ($HTML);
}

function HOAM_helpTree ($h1 = FALSE) {
	global $_HOAM_country, $_HOAM_language, $_HOAM_setting, $_HOAM_queue_head;

	require_once (HOAM_PATH_FUNCTION . 'wiki.php');

	HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['help']['tree'])));
	$HTML = '<h1>' . $_HOAM_language ['field_names']['help']['tree'] . '</h1>';
	$HTML .= $_HOAM_language ['help_pages']['help']['tree'];
	$HTML .= HOAM_wikiTree ($_HOAM_setting ['article']['ids']['root']);

	return ($HTML);
}

if (!isset ($GLOBALS['global_url_array'][1])) {
	HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['help']['page'])));
	echo '<h1>' . $_HOAM_language ['field_names']['help']['page'] . '</h1>';
	echo '<ul>';
	// Only display information about making online payments if the paypal
	// configuration option is enabled.
	if ($_HOAM_setting ['budget']['paypal']['enable']) {
		echo '<li><a href="/help/onlinepay/">' . $_HOAM_language ['field_names']['help']['onlinepay'] . '</a></li>';
	}
	echo '<li><a href="/help/search/">' . $_HOAM_language ['field_names']['help']['search'] . '</a></li>';
	echo '<li><a href="/help/tree/">' . $_HOAM_language ['field_names']['help']['tree'] . '</a></li>';
	echo '</ul>';
} else { 
	switch ($GLOBALS['global_url_array'][1]) { 
		case ('help') :
			$_HOAM_log -> add ('Loading /help/help/ page.', HOAM_MESSAGE_DEBUG);
			echo HOAM_helpSearchHelp ();
			break;
		case ('onlinepay') :
			$_HOAM_log -> add ('Loading /help/onlinepay/ page.', HOAM_MESSAGE_DEBUG);
			echo HOAM_helpOnlinePay ();
			break;
		case ('search') :
			$_HOAM_log -> add ('Loading /help/search/ page.', HOAM_MESSAGE_DEBUG);
			echo HOAM_helpSearch ();
			break;
		case ('tree') :
			$_HOAM_log -> add ('Loading /help/tree/ page.', HOAM_MESSAGE_DEBUG);
			echo HOAM_helpTree ();
			break;
	}
}

?>
