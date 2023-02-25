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

function HOAM_errorPage ($title = NULL, $content = NULL) {
	global $_HOAM_language, $_HOAM_queue_head, $_HOAM_setting;
	if (!isset ($title)) {
		$title = $_HOAM_language ['errors']['website']['generic'];
	}
	if (!isset ($content)) {
		$content = sprintf ($_HOAM_language ['help_pages']['website']['generic'], $_HOAM_setting ['website']['email']['webmaster'], $_HOAM_setting ['website']['email']['webmaster']);
	}
	HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $title)));
	$HTML  = '<h1>' . $title . '</h1>';
	$HTML .= $content;
    return ($HTML);
}

function HOAM_error401 () {
	// For now, this is using the 403 code until it's decided whether to add the
	// separate piece allowing authentication.
	return (HOAM_error403 ());
}

function HOAM_error403 () {
	global $_HOAM_language, $_HOAM_log, $_HOAM_queue_document;
	$_HOAM_log -> add ('403 Permissions failure.', HOAM_MESSAGE_NOTICE);
	HOAM_queueInsertValue ($_HOAM_queue_document, 'headers', array ('params' => array ('status' => 'HTTP/1.1 403 Forbidden')));
    $HTML = HOAM_errorPage ($_HOAM_language ['errors']['website']['403'], $_HOAM_language ['help_pages']['website']['403']);
   	if (!isset ($_SESSION['current_user']['id'])) {
		$HTML .= $_HOAM_language ['help_pages']['website']['login_required'];
	}
	return ($HTML);
}

function HOAM_error404 () {
	global $_HOAM_language, $_HOAM_queue_document, $_HOAM_queue_head, $_HOAM_setting;
	HOAM_queueInsertValue ($_HOAM_queue_document, 'headers', array ('params' => array ('status' => 'HTTP/1.1 404 Not Found')));
	HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['errors']['website']['404'])));
	$HTML  = '<h1>' . $_HOAM_language ['errors']['website']['404'] . '</h1>';
	$HTML .= sprintf ($_HOAM_language ['help_pages']['website']['404'], $_HOAM_setting ['website']['email']['webmaster'], $_SERVER['HTTP_REFERER']);
    return ($HTML);
}

function HOAM_errorCookies () {
	global $_HOAM_language, $_HOAM_queue_head, $_HOAM_setting;
	HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['errors']['website']['cookies_required'])));
	$HTML  = '<h1>' . $_HOAM_language ['errors']['website']['cookies_required'] . '</h1>';
	$HTML .= sprintf ($_HOAM_language ['help_pages']['website']['cookies_required'], $_HOAM_setting ['website']['email']['webmaster']);
    return ($HTML);
}

function HOAM_errorInstallation () {
	global $_HOAM_language, $_HOAM_queue_head, $_HOAM_setting;
	if (isset ($_SESSION ['error_class_item'])) {
		HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['errors']['website']['installation'])));
		$HTML  = '<h1>' . $_HOAM_language ['errors']['website']['installation'] . '</h1>';
		$HTML .= sprintf ($_HOAM_language ['help_pages']['website']['installation'], $_SESSION['error_class_item'] -> showCategory ('installation'));
    	return ($HTML);
	} else {
		echo HOAM_errorPage ($_HOAM_language ['errors']['website']['installation']);
	}
}

function HOAM_errorUnknownAccount () {
	global $_HOAM_language, $_HOAM_log, $_HOAM_queue_head;
	$user_class = new HOAM_user ('users');
	
	$trusted_accountname = $user_class -> superClean ($_GET['accountname'], $user_class -> returnColumnSize ('accountname'));
	$_HOAM_log -> add ('Attempt to logon with unknown user account "' . $trusted_accountname . '".', HOAM_MESSAGE_NOTICE);
	HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['errors']['website']['unknown_account'])));
	$HTML  = '<h1>' . $_HOAM_language ['errors']['website']['unknown_account'] . '</h1>';
	$HTML .= sprintf ($_HOAM_language ['help_pages']['website']['unknown_account'], $trusted_accountname);
	return ($HTML);
}

switch ($GLOBALS['global_url_array'][1]) {
	case ('401') :
		$_HOAM_log -> add ('Loading /error/401/ page.', HOAM_MESSAGE_DEBUG);
		echo HOAM_error401 ();
		break;
	case ('403') :
		$_HOAM_log -> add ('Loading /error/403/ page.', HOAM_MESSAGE_DEBUG);
		echo HOAM_error403 ();
		break;
	case ('404') :
		$_HOAM_log -> add ('Loading /error/404/ page.', HOAM_MESSAGE_DEBUG);
		echo HOAM_error404 ();
		break;
	case ('add') :
		$_HOAM_log -> add ('Loading /error/add/ page.', HOAM_MESSAGE_DEBUG);
		echo HOAM_errorPage ($GLOBALS['_HOAM_language'] ['errors']['website']['add'], $GLOBALS['_HOAM_language'] ['help_pages']['website']['add']);
		break;
	case ('cookies') :
		$_HOAM_log -> add ('Loading /error/cookies/ page.', HOAM_MESSAGE_DEBUG);
		echo HOAM_errorCookies ();
		break;
	case ('construction') :
		$_HOAM_log -> add ('Loading /error/construction/ page.', HOAM_MESSAGE_DEBUG);
		echo HOAM_errorPage ($GLOBALS['_HOAM_language'] ['errors']['website']['under_construction'], $GLOBALS['_HOAM_language'] ['help_pages']['website']['under_construction']);
		break;
	case ('feature_disabled') :
		$_HOAM_log -> add ('Loading /error/feature_disabled/ page.', HOAM_MESSAGE_DEBUG);
		echo HOAM_errorPage ($GLOBALS['_HOAM_language'] ['errors']['website']['feature_disabled'], $GLOBALS['_HOAM_language'] ['help_pages']['website']['feature_disabled']);
		break;
	case ('homeowner') :
		$_HOAM_log -> add ('Loading /error/homeowner/ page.', HOAM_MESSAGE_DEBUG);
		echo HOAM_errorPage ($GLOBALS['_HOAM_language'] ['errors']['website']['homeowner_restricted'], $GLOBALS['_HOAM_language'] ['help_pages']['website']['homeowner_restricted']);
		break;
	case ('id_required') :
		$_HOAM_log -> add ('Loading /error/id_required/ page.', HOAM_MESSAGE_DEBUG);
		echo HOAM_errorPage ($GLOBALS['_HOAM_language'] ['errors']['website']['id_required'], $GLOBALS['_HOAM_language'] ['help_pages']['website']['id_required']);
		break;
	case ('installation') :
		$_HOAM_log -> add ('Loading /error/installation/ page.', HOAM_MESSAGE_DEBUG);
		echo HOAM_errorInstallation ();
		break;
	case ('inuse') :
		$_HOAM_log -> add ('Loading /error/inuse/ page.', HOAM_MESSAGE_DEBUG);
		echo HOAM_errorPage ($GLOBALS['_HOAM_language'] ['errors']['website']['remove_inuse'], $GLOBALS['_HOAM_language'] ['help_pages']['website']['remove_inuse']);
		break;
	case ('logged_in') :
		$_HOAM_log -> add ('Loading /error/logged_in/ page.', HOAM_MESSAGE_DEBUG);
		echo HOAM_errorPage ($GLOBALS['_HOAM_language'] ['errors']['website']['logged_in'], $GLOBALS['_HOAM_language'] ['help_pages']['website']['logged_in']);
		break;
	case ('login') :
		$_HOAM_log -> add ('Loading /error/login/ page.', HOAM_MESSAGE_DEBUG);
		echo HOAM_errorPage ($GLOBALS['_HOAM_language'] ['errors']['website']['login_first'], $GLOBALS['_HOAM_language'] ['help_pages']['website']['login_first']);
		break;
	case ('password') :
		$_HOAM_log -> add ('Loading /error/password/ page.', HOAM_MESSAGE_DEBUG);
		echo HOAM_errorPage ($GLOBALS['_HOAM_language'] ['errors']['website']['password'], $GLOBALS['_HOAM_language'] ['help_pages']['website']['password']);
		break;
	case ('remove') :
		$_HOAM_log -> add ('Loading /error/remove/ page.', HOAM_MESSAGE_DEBUG);
		echo HOAM_errorPage ($GLOBALS['_HOAM_language'] ['errors']['website']['remove'], $GLOBALS['_HOAM_language'] ['help_pages']['website']['remove']);
		break;
	case ('unknown_action') :
		$_HOAM_log -> add ('Loading /error/unknown_action/ page.', HOAM_MESSAGE_DEBUG);
		echo HOAM_errorPage ($GLOBALS['_HOAM_language'] ['errors']['website']['unknown_action'], $GLOBALS['_HOAM_language'] ['help_pages']['website']['unknown_action']);
		break;
	case ('unknown_id') :
		$_HOAM_log -> add ('Loading /error/unknown_id/ page.', HOAM_MESSAGE_DEBUG);
		echo HOAM_errorPage ($GLOBALS['_HOAM_language'] ['errors']['website']['unknown_id'], $GLOBALS['_HOAM_language'] ['help_pages']['website']['unknown_id']);
		break;
	case ('unknown_account') :
		$_HOAM_log -> add ('Loading /error/unknown_account/ page.', HOAM_MESSAGE_DEBUG);
		echo HOAM_errorUnknownAccount ();
		break;
	default:
		echo HOAM_errorPage ();
}

?>
