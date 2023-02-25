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

function HOAM_newsDisplay ($id) {
	global $_HOAM_country, $_HOAM_language, $_HOAM_setting;

	$group_members_class = new HOAM_group_members ('group_members');
	$news_class = new HOAM_news ('news');
	$user_class = new HOAM_user ('users');

	$HTML = (string) NULL;
	if ($news_class -> idExists ($id)) {
		$HTML .= '<div class="news">';
		// Display the news tools
		if (isset ($_SESSION['current_user']['id'])) {
			if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['news_edit'], $_SESSION['current_user']['id'])) {
				$HTML .= '<div class="news_tool"><a href="/website/news/edit/' . $id . '/" title="Edit This Article"><img src="/hoam/images/icons/page_white_edit.png" alt="[ Edit ]" /></a></div>';
				$HTML .= '<div class="news_tool"><a href="/website/news/delete/' . $id . '/" title="Delete This Article"><img src="/hoam/images/icons/delete.png" alt="[ Delete ]" /></a></div>';
			}
		}
		$HTML .= '<h1>' . html_encode (stripslashes ($news_class -> returnTitle ($id))) . '</h1>';
		$HTML .= '<h2>' . $_HOAM_language ['common']['posted-by'] . ' <a href="/news/sort/author/' . $user_class -> returnAccountname ($news_class -> returnUserId ($id)) . '/" title="Show all News Items by This Author">' . $user_class -> returnAccountname ($news_class -> returnUserId ($id)) . '</a> on <a href="/news/sort/date/' . date ('Y-m-d', strtotime ($news_class -> returnDateCreated ($id))) . '/" title="Show All News Items on This Date">' . date('l, F j @ g:ia', strtotime ($news_class -> returnDateCreated ($id))) . '</a></h2>';

		$article_text = $news_class -> returnArticle ($id);
		$article_text = str_replace ('\r\n', "\r\n", $article_text);
		$article_text = str_replace ('\r', "\r", $article_text);
		// We need to eval() the News text because it's possible to include PHP
		// code.
		ob_start ();
		eval ('?>' . $article_text . '<?php ' );
		$HTML .= ob_get_contents();
		ob_end_clean ();
		// Had to add the str_replace because news items with $ were being
		// parsed as variables, but only under the {{NEWS_RECENT}} tag.
		$HTML = stripslashes (str_replace('$', '&#36;', $HTML));
		
		// Now, we check to see that the last time the item was modified was
		// during the same minute as it was created.  We check for the same
		// minute, because we could be delayed slightly during the various calls
		// to update the Title, Summary, etc...
		if (date ('Y-m-d H:i', strtotime ($news_class -> returnDateModified ($id))) > date ('Y-m-d H:i', strtotime ($news_class -> returnDateCreated ($id) ) )) {
			$HTML .= '<h4>' . $_HOAM_language ['common']['updated'] . ': ' . date ('l, F j @ g:ia', strtotime ($news_class -> returnDateModified ($id)) ) . '</h4>';
		}
		$HTML .= '</div>';
	}
	return $HTML;
}

function HOAM_newsRecent () {
	global $_HOAM_language;

	$news_class = new HOAM_news ('news');

	$news_article_list = $news_class -> listRecent ($_SESSION['current_user']['items_per_page']);
	if (is_array ($news_article_list)) {
		$HTML = '';
  		foreach ($news_article_list as $article) {
    		$HTML .= HOAM_newsDisplay ($article);
  		}
  		$HTML .= '<div style="margin: 1em; text-align: center"><a href="/news/">' . $_HOAM_language ['field_names']['news']['older'] . '</a></div>';
	} else {
  		$HTML = $_HOAM_language ['errors']['news']['none-found'];
	}
	return ($HTML);
}

function HOAM_newsSearchResult ($id) {
	// Make sure the $id given exists.
	// NOTE! privilege and privacy checking must be done PRIOR to sending this
	// function an article to display! This is handled by the search function in
	// class.news.php, but needs to be remembered.

	$news_class = new HOAM_news ('news');

	$HTML = (string) NULL;
	if ($news_class -> idExists ($id)) {
		$HTML .= '<div class="searchResult">';
		$HTML .= '<h1><a href="/news/view/' . $id . '/">' . $news_class -> returnTitle ($id) . '</a></h1>';

		$news_article_text = $news_class -> returnArticle ($id);
		ob_start();
		eval('?>' . $news_article_text . '<?php ');
		$output = ob_get_contents();
		ob_end_clean();
		$news_article_text = strip_tags ($output);

		$maxlength = 100;
		$HTML .= '<p>' . mb_substr (strip_tags (str_replace ('\r\n', "\r\n", $news_article_text)), 0, $maxlength) . '&hellip;</p>';
		$HTML .= '<span>http://' . $_SERVER["SERVER_NAME"] . '/news/view/' . $id . '/</span>';
		$HTML .= "</div>\n";
	}
	return $HTML;
}

?>
