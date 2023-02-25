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

function HOAM_newsList ($list, $count, $page, $url_pre = '/news/', $url_post = '') {
	require_once (HOAM_PATH_FUNCTION . 'news.php');
	require_once (HOAM_PATH_FUNCTION . 'wiki.php');

	$HTML .= HOAM_wikiReturnPageList ($url_pre, $url_post, $page, $count, $_SESSION['current_user']['items_per_page']);	
	for ($i = 0; $i < $_SESSION['current_user']['items_per_page']; $i++) {
		$HTML .= HOAM_newsDisplay ($list[$i]);
	}
	
	return ($HTML);
}

function HOAM_newsPage () {
	global $_HOAM_country, $_HOAM_language, $_HOAM_setting, $_HOAM_queue_head;
	
	$news_class = new HOAM_news ('news');
	
	$local_page_number = (int) $GLOBALS['global_url_array'][2];
	if ($local_page_number > 0) {
		// Subtract one from the page number to account for the fact that our
		// list begins with zero
		$local_page_number--;
	} else {
		// Make sure that we are dealing with a positive page number.
		$local_page_number = 0;
	}
	$offset = $local_page_number * $_SESSION['current_user']['items_per_page'];

	// Check whether the user wants to sort their results at all.
	if ($GLOBALS['global_url_array'][3] == 'sort') {
		$HTML .= HOAM_newsSort (3, $offset, $local_page_number);
	} else { 
		$news_article_list = $news_class -> listRecent ($_SESSION['current_user']['items_per_page'], $offset);
		// Find out how many total news articles there are
		$news_article_total_count = $news_class -> returnCountQuery ();

		if (is_array ($news_article_list)) {
			$HTML .= HOAM_newsList ($news_article_list, $news_article_total_count, $local_page_number, '/news/');
		}
	}
	
	return ($HTML);
}

function HOAM_newsSort ($base = 1, $offset = 0, $page = 0) {
	global $_HOAM_country, $_HOAM_language, $_HOAM_log, $_HOAM_setting, $_HOAM_queue_head;
	
	$news_class = new HOAM_news ('news');
	$user_class = new HOAM_user ('users');
	
	switch ($GLOBALS['global_url_array'][$base + 1]) {
		case 'author' :
			$_HOAM_log -> add ('Loading /news/sort/author/ page.', HOAM_MESSAGE_DEBUG);
			$trusted_author = $user_class -> superClean ($GLOBALS['global_url_array'][$base + 2], $user_class -> returnColumnSize ('accountname'));
			$news_article_list = $news_class -> listAuthor ($user_class -> returnId ($trusted_author), $_SESSION['current_user']['items_per_page'], $offset);
			// Find out how many total news articles there are
			$news_article_total_count = $news_class -> returnCountAuthor ($user_class -> returnId ($clean_author));
			if (is_array ($news_article_list)) {
				$HTML .= HOAM_newsList ($news_article_list, $news_article_total_count, $page, '/news/', 'sort/author/' . $trusted_author . '/');
			}
			break;
		case 'date' :
			$_HOAM_log -> add ('Loading /news/sort/date/ page.', HOAM_MESSAGE_DEBUG);
			$trusted_date = $news_class -> superClean ($GLOBALS['global_url_array'][$base + 2], 10);
			$newest = date ('Y-m-d H:i:s', strtotime ($trusted_date) );
			$oldest = date ('Y-m-d H:i:s', strtotime ($trusted_date) + 86400);
			$news_article_list = $news_class -> listRange ($newest, $oldest, $_SESSION['current_user']['items_per_page'], $offset);
			// Find out how many total news articles there are
			$news_article_total_count = $news_class -> returnCountRange ($newest, $oldest);
			if (is_array ($news_article_list)) {
				$HTML .= HOAM_newsList ($news_article_list, $news_article_total_count, $page, '/news/', 'sort/author/' . $trusted_date . '/');
			}
			break;
		case 'year' :
			$_HOAM_log -> add ('Loading /news/sort/year/ page.', HOAM_MESSAGE_DEBUG);
			$trusted_date = (int) $GLOBALS['global_url_array'][$base + 2];
			$newest = date ('Y-m-d H:i:s', strtotime ($trusted_date . '-01-01 00:00:00') );
			$oldest = date ('Y-m-d H:i:s', strtotime ($trusted_date . '-12-31 23:59:59') );
			$news_article_list = $news_class -> listRange ($newest, $oldest, $_SESSION['current_user']['items_per_page'], $offset);
			// Find out how many total news articles there are
			$news_article_total_count = $news_class -> returnCountRange ($newest, $oldest);
			if (is_array ($news_article_list)) {
				$HTML .= HOAM_newsList ($news_article_list, $news_article_total_count, $page, '/news/', 'sort/year/' . $trusted_date . '/');
			}
			break;
		default:
			header ('Location: /error/unknown_action/');
		break;
    }

	return ($HTML);
}

function HOAM_newsView () {
	require_once (HOAM_PATH_FUNCTION . 'news.php');

	$news_class = new HOAM_news ('news');
	
	if ($GLOBALS['global_url_array'][2]) {
		$trusted_id = $news_class -> superClean ($GLOBALS['global_url_array'][2], $news_class -> returnColumnSize ('id'));
		if ($news_class -> idExists ($trusted_id)) {
			$HTML = HOAM_newsDisplay ($trusted_id);
		} else {
			header ('Location: /error/unknown_id/');
			exit;
		}
	} else {
		header ('Location: /news/list/');
		exit;
	}
	
	return ($HTML);
}

$news_class = new HOAM_news ('news');

HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['news']['archive'])));
echo '<h1>' . $_HOAM_language ['field_names']['news']['archive'] . '</h1>';
switch ($GLOBALS['global_url_array'][1]) {
	case 'list' :
		$_HOAM_log -> add ('Loading /news/list/ page.', HOAM_MESSAGE_DEBUG);
		echo HOAM_newsList ();
		break;
	case 'page' :
		$_HOAM_log -> add ('Loading /news/page/ page.', HOAM_MESSAGE_DEBUG);
		echo HOAM_newsPage ();
		break;
	case 'sort' :
		$_HOAM_log -> add ('Loading /news/sort/ page.', HOAM_MESSAGE_DEBUG);
		echo HOAM_newsSort ();
		break;
	case 'view' :
		$_HOAM_log -> add ('Loading /news/view/ page.', HOAM_MESSAGE_DEBUG);
		echo HOAM_newsView ();
		break;
	default:
		// Just get a list of articles and display them.
		$news_article_list = $news_class -> listRecent ($_SESSION['current_user']['items_per_page']);
		// Find out how many total news articles there are
		$news_article_total_count = $news_class -> returnCountQuery ();

		// We should now have at least a list of articles to display and a count of the total number of articles available.
		if (is_array ($news_article_list)) {
			echo HOAM_newsList ($news_article_list, $news_article_total_count, 0);
		} else {
			// Finally, if no articles were found matching the criteria given, let the user know.
			echo '<h2>' . $_HOAM_language ['errors']['news']['none-found'] . '</h2>';
			echo '<p>' . $_HOAM_language ['errors']['generic']['no-entries-available'] . '</p>';
		}
		break;
}

?>
