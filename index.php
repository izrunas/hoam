<?php
/*
 * This file is part of HOAM, copyright (C) 2002-2020 ARP Realty, Inc.
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

if (!isset ($hoam_started)) {
	define ('HOAM_PATH', dirname (__FILE__) . '/');

	require_once (HOAM_PATH . '/hoam/hoam-startup.php');
}

// Because of the way URLs are searched for a valid article, we need to strip
// out any GET requests to avoid confusion.
if ($_SERVER['QUERY_STRING']) {
	$uri = mb_substr ($_SERVER['REQUEST_URI'], 0, mb_strpos ($_SERVER['REQUEST_URI'], '?'));
} else {
	$uri = $_SERVER['REQUEST_URI'];
}

// Try to remove any superfluous or harmful code from the uri, and reduce it to
// 256 chars max. If we later need to process any GET requests, that code will
// need to retrieve and clean them.
$trusted_uri = $_HOAM_log -> superClean ($uri, 256);

// First we want to see if a existing file matches the request. (This might be
// a static html file but also a php or cgi script.) If there is such a file,
// we just include it.
// One part of this procedure creates a few vulnerabilities. In step one, when
// you check for a existing file, you actually access the file system of your
// server.
// Usually, requests from the web should have very limited rights, but this
// depends on how carefully your server is set up. If someone entered ../../../
// or something like /.a_dangerous_script, this could allow them to access
// directories below your web-root or execute scripts on your server. It's
// usually not that easy, but be sure to check some of those possible
// vulnerabilities.
if (file_exists ($_SERVER['DOCUMENT_ROOT'] . $trusted_uri)
	// Very important! We check to see if the request was not for the file
	// index.php itself - if it were, and we just went ahead, it would lead to
	// an endless loop!
	AND ($_SERVER['SCRIPT_FILENAME'] != $_SERVER['DOCUMENT_ROOT'] . $trusted_uri)
	// Also check that a request isn't coming in for the system's config file.
	AND ($trusted_uri != '/hoam-config.ini')
	// or the .htaccess file
	AND ($trusted_uri != '/.htaccess')
	// Make sure this isn't a request for one of the session cookies
	AND (mb_substr_count (preg_replace ('/\/\//', '/', $_SERVER['DOCUMENT_ROOT'] . $trusted_uri), HOAM_PATH_SESSION) == 0)
	// We check for another special case: a REQUEST_URI that contains a '/'
	// only - that would also be a request for the actual index file. If you
	// don't do this check, it will lead to a PHP Error. (We will deal with
	// this case later on.)
	AND ($trusted_uri != '/')) {
	// If a request passes all these checks, we load the file using
	// include() and stop the execution of index.php using exit().
	include ($_SERVER['DOCUMENT_ROOT'] . $trusted_uri);
	exit ();
}

// Start the session
session_start();

// Record browser information if enabled
if (isset ($_HOAM_setting ['log']['browser']) AND $_HOAM_setting ['log']['browser'] AND ini_get ('BROWSCAP')) {
	$client_browser = get_browser (NULL, TRUE);
	$browser_tracker = new HOAM_tracker ('tracker_browser');
	$tracker_id = $browser_tracker -> findText ($client_browser['browser'], ' AND platform LIKE "' . $client_browser['platform'] . '" AND version = "' . $client_browser['version'] . '"', 'browser');
	// Does the browser already exist in the database?
	if (empty ($tracker_id)) {
		$tracker_id = $browser_tracker -> add ($client_browser['browser'], 'browser');
		$browser_tracker -> changeText ($tracker_id, $client_browser['platform'], 'platform');
		$browser_tracker -> changeText ($tracker_id, $client_browser['version'], 'version');
	}
	// Increment the counter
	$browser_tracker -> changeDateModified ($tracker_id);
	$browser_tracker -> changeCount ($tracker_id, ($browser_tracker -> returnCount ($tracker_id) + 1));
	// Clean up
	unset ($browser_tracker, $tracker_id);
}

// Check to see whether we have a valid session or not.
if (!isset ($_SESSION['current_user']['items_per_page'])) {
	// If we don't have a valid session, go through and initialize to default values.
	$_SESSION['current_user']['id'] = NULL;
	$_SESSION['current_user']['items_per_page'] = $_HOAM_setting ['user']['default']['items_per_page'];

	// Eventually we want to detect the user's preferred language. However, we
	// don't have translations available, so just force the default.
	$_SESSION['current_user']['language'] = 'default';
	
	// OK, for future reference, let's record the user's specified language
	if (isset ($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
		$language_tracker = new HOAM_tracker ('tracker_language');
		$language_tracker_id = $language_tracker -> findText ($language_tracker -> superClean ($_SERVER['HTTP_ACCEPT_LANGUAGE'], 64));
		// Do the keywords already exist in the database?
		if (empty ($language_tracker_id)) {
			$language_tracker_id = $language_tracker -> add ($language_tracker -> superClean ($_SERVER['HTTP_ACCEPT_LANGUAGE'], 64));
		} else {
			// We must have received something back when checking to see if the
			// text already existed
			$language_tracker_id = $language_tracker_id[0];
		}
		// Increment the link counter
		$language_tracker -> changeDateModified ($language_tracker_id, date ('Y-m-d H:i:s'));
		$language_tracker -> changeCount ($language_tracker_id, ($language_tracker -> returnCount ($language_tracker_id) + 1));
		// Clean up
		unset ($language_tracker, $language_tracker_id);
	}
}

if ($_HOAM_setting ['hoam']['updating'] == '1') {
	$group_members_class = new HOAM_group_members ('group_members');
	// Don't display the update message for site admins and the root user; they
	// may need to get into the system if something went wrong with the upgrade.
	if (!($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
		($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id']))) {
		$_HOAM_log -> add ('System maintenance in progress. Displaying notice to site visitor.', HOAM_MESSAGE_DEBUG);
		$wiki = new HOAM_wiki_parser ();
		echo $wiki -> parse ($_HOAM_language ['help_pages']['website']['maintenance']);
		exit ();
	}
}

// Since we usually display at least one image on a page, we'll load those
// functions as (at least in the default site), most of the default images are
// stored as attachments in the database.
require_once (HOAM_PATH_FUNCTION . 'attachments.php');

// We use trim() to remove the beginning and ending slashes to get a consistent
// answer when count()ing $global_url_array. If the user didn't include the
// trailing slash at the end of the request, we would end up with a different
// count than if they had included it.  Might as well just forget it ever
// existed.
$global_url = trim ($trusted_uri, '/');
$global_url_array = explode ('/', $global_url);

// All the elements of the request string are now stored in $global_url_array.
// If the request was for www.mycars.com/bmw/z8/, then $global_url_array[0]
// contains 'bmw' and $global_url_array[1] contains 'z8.'

$global_url_article_id      = 0;
$global_url_article_root    = $_HOAM_setting ['article']['ids']['root'];
$global_url_array_count     = count ($global_url_array);
$global_url_array_iteration = 0;
$global_url_article_path    = (string) NULL;

// OK. Now we're finally going to see if we can find the URI requested.

$wiki = new HOAM_wiki_parser ();
require_once (HOAM_PATH_FUNCTION . 'advertising.php');
require_once (HOAM_PATH_FUNCTION . 'render.php');
switch ($global_url_array[0]) {
	case 'admin' :
		$_HOAM_log -> add ('Loading /admin/ page.', HOAM_MESSAGE_DEBUG, $_SESSION['current_user']['id']);
		$template = file_get_contents (HOAM_PATH_TEMPLATE . 'admin.php');
		break;
	case 'apps' :
		$_HOAM_log -> add ('Loading /apps/ page.', HOAM_MESSAGE_DEBUG, $_SESSION['current_user']['id']);
		$template = file_get_contents (HOAM_PATH_TEMPLATE . 'apps.php');
		break;
	case 'error' :
		$_HOAM_log -> add ('Loading /error/ page.', HOAM_MESSAGE_DEBUG, $_SESSION['current_user']['id']);
		$template = file_get_contents (HOAM_PATH_TEMPLATE . 'error.php');
		break;
	case 'help' :
		$_HOAM_log -> add ('Loading /help/ page.', HOAM_MESSAGE_DEBUG, $_SESSION['current_user']['id']);
		$template = file_get_contents (HOAM_PATH_TEMPLATE . 'help.php');
		break;
	case 'news' :
		$_HOAM_log -> add ('Loading /news/ page.', HOAM_MESSAGE_DEBUG, $_SESSION['current_user']['id']);
		$template = file_get_contents (HOAM_PATH_TEMPLATE . 'news.php');
		break;
	case 'residence' :
		$_HOAM_log -> add ('Loading /residence/ page.', HOAM_MESSAGE_DEBUG, $_SESSION['current_user']['id']);
		$template = file_get_contents (HOAM_PATH_TEMPLATE . 'residence_detail_user.php');
		break;
	case 'user' :
		$_HOAM_log -> add ('Loading /user/ page.', HOAM_MESSAGE_DEBUG, $_SESSION['current_user']['id']);
		$template = file_get_contents (HOAM_PATH_TEMPLATE . 'user.php');
		break;
	case 'website' :
		$_HOAM_log -> add ('Loading /website/ page.', HOAM_MESSAGE_DEBUG, $_SESSION['current_user']['id']);
		$template = file_get_contents (HOAM_PATH_TEMPLATE . 'website.php');
		// Because we will have actual wiki vars in the articles, notes, and
		// configuration, we don't want to parse those unnecessarily.
		$dont_parse = TRUE;
		break;
/*	case ('wiki') :
		// Look up the wiki page in the db.
		$template = '==I\'m a little wiki== ===Short n stout===';
		break;
*/	default :
		// If we've gotten this far, we're probably going to display an article
		// out of the database. So, load up the article functions.
		$article_class = new HOAM_article ('articles');

		while ($global_url_array_iteration < $global_url_array_count) {
			// Determine whether the current $global_url_array item being
			// inspected is a valid article urlname.
			$global_url_article_id = $article_class -> findId ($global_url_array[$global_url_array_iteration], $global_url_article_root);
			// I'd like to test for FALSE which is the result when an article
			// can't be found, but for whatever reason, FALSE also == NULL.
			// Since the 'urlname' of the '/' article is a NULL string, if we
			// test for FALSE, we can't find the root article.  Something to fix
			// eventually. Changing this to '=== FALSE' does _NOT_ work.
			if ($global_url_article_id != NULL) {
				$global_url_article_path .= $global_url_array[$global_url_array_iteration];
				$global_url_article_path .= '/';
				// Are there more items in the array to check?
				if (($global_url_array_count - $global_url_array_iteration) > 1) {
					// Since there are still items to be checked in
					// $global_url_array, set the new $global_url_article_root
					// to the current $global_url_article_id, and continue
					// processing the array.
					$global_url_article_root = $global_url_article_id;
					// Break out of the current loops and go back to the while.
				} else {
					// If we've found the article and there's nothing left to
					// check (ie, we're not performing any actions on the
					// article), then just display it.
					if ($article_class -> returnRedirect ($global_url_article_id)) {
						$_HOAM_log -> add ('Redirected to "' . $article_class -> returnRedirectLocation ($global_url_article_id) . '".', HOAM_MESSAGE_DEBUG, $_SESSION['current_user']['id']);
						header ('Location: ' . $article_class -> returnRedirectLocation ($global_url_article_id));
						exit;
					} else {
						$_HOAM_log -> add ('Viewing article "' . $global_url_article_path . '".', HOAM_MESSAGE_DEBUG, $_SESSION['current_user']['id']);
						HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => strip_tags (stripslashes ($article_class -> returnTitle ($global_url_article_id))))));
						ob_start ();
						HOAM_wikiDisplayTemplate ($global_url_article_id);						
						$template_eval = ob_get_contents ();
						ob_end_clean ();
						$template_output = $wiki -> parse ($template_eval);
						// Push the contents of the article into the queue.
						HOAM_queueInsertValue ($_HOAM_queue_body, 'bodyContent', array ('params' => array ('content' => $template_output)));
						break;
					}
				}
				// Increment the counter and start searching $global_url_array
				// for more articles.
				$global_url_array_iteration ++;
			} else {
				// We need to break out of the while so that we stop
				// incrementing the array_iteration and checking for additional
				// articles.
				header ('Location: /error/404/');
				exit;
			}
	}
	break;
}
// Do the actual template eval
ob_start ();
eval ('?>' . $template . '<?php ');
$template_eval = ob_get_contents ();
ob_end_clean ();
// Parse everything except those templates marked not to.
if (isset ($dont_parse) AND $dont_parse) {
	$template_output = $template_eval;
} else {
	$template_output = $wiki -> parse ($template_eval);
}
// Push the contents of the article into the queue.
HOAM_queueInsertValue ($_HOAM_queue_body, 'bodyContent', array ('params' => array ('content' => $template_output)));

// Insert the head and body queues, then execute the document queue. This is
// done last because doing it earlier will cause some of the other queue changes
// to be lost.
//
// This really needs to be fixed to get the correct behavior globally without
// this hack -- it's holding other things back (primarily plugins).
HOAM_queueInsertAfter ($_HOAM_queue_document, 'documentOpen', $_HOAM_queue_head);
HOAM_queueInsertBefore ($_HOAM_queue_document, 'documentClose', $_HOAM_queue_body);
echo HOAM_queueRun ($_HOAM_queue_document);

exit ();

?>
