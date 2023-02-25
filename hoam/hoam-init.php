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

// Several ideas in the startup checks are from MediaWiki and Wordpress.
define ('HOAM_DEBUG', FALSE);
define ('HOAM_FILENAME_LENGTH', 192);
define ('HOAM_LOG_DB', 1);
define ('HOAM_LOG_FILE', 2);
define ('HOAM_LOG_SYSLOG', 4);
define ('HOAM_MESSAGE_EMERGENCY', 1);
define ('HOAM_MESSAGE_ALERT', 2);
define ('HOAM_MESSAGE_CRITICAL', 4);
define ('HOAM_MESSAGE_ERROR', 8);
define ('HOAM_MESSAGE_WARNING', 16);
define ('HOAM_MESSAGE_NOTICE', 32);
define ('HOAM_MESSAGE_INFORMATIONAL', 64);
define ('HOAM_MESSAGE_DEBUG', 128);
if (!defined ('HOAM_PATH')) {
	define ('HOAM_PATH', dirname (dirname (__FILE__)) . '/');
}
define ('HOAM_PATH_ATTACHMENT', HOAM_PATH . 'attachments/');
define ('HOAM_PATH_ATTACHMENT_PREVIEW', HOAM_PATH_ATTACHMENT . 'preview/');
define ('HOAM_PATH_CLASS', HOAM_PATH . 'hoam/classes/');
define ('HOAM_PATH_COUNTRY', HOAM_PATH . 'hoam/country/');
define ('HOAM_PATH_FUNCTION', HOAM_PATH . 'hoam/functions/');
define ('HOAM_PATH_LANGUAGE', HOAM_PATH . 'hoam/language/');
define ('HOAM_PATH_LETTER', HOAM_PATH . 'hoam/letters/');
define ('HOAM_PATH_PLUGIN', HOAM_PATH . 'hoam/plugins/');
define ('HOAM_PATH_REPORT', HOAM_PATH . 'hoam/reports/');
define ('HOAM_PATH_SCRIPT', HOAM_PATH . 'hoam/scripts/');
define ('HOAM_PATH_SESSION', HOAM_PATH . 'sessions/');
define ('HOAM_PATH_TEMPLATE', HOAM_PATH . 'hoam/templates/');
define ('HOAM_RELEASE', '20200516');
define ('HOAM_URL', 'http://hoam.arprs.com/');
define ('HOAM_VERSION', 0.886);

// set the error reporting level for this script
if (HOAM_DEBUG) {
	error_reporting (E_ALL | E_STRICT);
	ini_set ('display_errors', 1);
} else {
	error_reporting (E_ERROR);
}

// Attempt to detect and use the browser's preferred language
if (isset ($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
    $temp_language_list = explode (',', strtolower ($_SERVER['HTTP_ACCEPT_LANGUAGE']));
	foreach ($temp_language_list as $temp_language) {
        $l = explode (';', $temp_language);
        preg_match ('/[A-Za-z][A-Za-z0-9_\-]*/', $l[0], $l);
		if (file_exists (HOAM_PATH_LANGUAGE . $l[0] . '.php')) {
			define ('HOAM_LANGUAGE', $l[0]);
			break;
		}
	}
	unset ($temp_language_list, $temp_language, $l);
}
// If we can't find a translation for the user's preferred language, just
// use the site's preferred language.
// For now, the only available language is English, so we'll use that.
if (!defined ('HOAM_LANGUAGE')) {
	define ('HOAM_LANGUAGE', 'en');
}
if (is_readable (HOAM_PATH_LANGUAGE . HOAM_LANGUAGE . '.php')) {
	require_once (HOAM_PATH_LANGUAGE . HOAM_LANGUAGE . '.php');
} else {
	die ("<h1>Error: HOAM installation problem.</h1><p>It does not appear that HOAM has been installed completely, or there is a problem with the installation (could not load <strong>" . HOAM_PATH_LANGUAGE . HOAM_LANGUAGE . ".php</strong>).</p><p>Please complete the installation, or contact your support personnel for assistance.</p>");
}

// Verify the server is running a supported version of PHP.
if (version_compare(PHP_VERSION, '5.5', '>=')) {
} else {
	die ($_HOAM_language ['errors']['hoam']['installation']['problem'] . $_HOAM_language ['errors']['hoam']['installation']['wrong-php'] . $_HOAM_language ['errors']['hoam']['contact-support']);
}

// Make sure the server has the necessary PHP extensions installed.
$required_extension_list = array ('curl', 'date', 'exif', 'gd', 'libxml', 'mbstring', 'mysqli', 'pcre', 'session', 'xml', 'zip', 'zlib');
// There's one additional extension if we're running under Windows
// This is only needed by the checkOwnership function at this time.
if (strtoupper (substr (PHP_OS, 0, 3)) === 'WIN') {
	array_push ($required_extension_list, 'com');
}

foreach ($required_extension_list as $required_extension) {
	if (!extension_loaded ($required_extension)) {
		$required_extension_errors .= "<li>Missing extension: '$required_extension'.</li>";
	}
}
if (isset ($required_extension_errors)) {
	header ('HTTP/1.x 500 Internal Server Error');
	die ($_HOAM_language ['errors']['hoam']['installation']['problem'] . $_HOAM_language ['errors']['hoam']['installation']['missing-extensions'] .
	'<ul>' . $required_extension_errors . '</ul>' .
	$_HOAM_language ['errors']['hoam']['installation']['required-extensions'] . $_HOAM_language ['errors']['hoam']['contact-support']);
}
unset ($required_extension_list);
unset ($required_extension_errors);

// Now that we've verified mbstring is available, configure it to use UTF-8.
// From http://developer.loftdigital.com/blog/php-utf-8-cheatsheet
// Set default language to Neutral(UTF-8) (default)
ini_set ('mbstring.language', 'neutral');
// HTTP input encoding translation is enabled
ini_set ('mbstring.encoding_translation', 1);
//  Set default character encoding detection order to auto
ini_set ('mbstring.detect_order', 'auto');
//  Do not print invalid characters
ini_set ('mbstring.substitute_character', 'none');
//  Default character set for auto content type header
ini_set ('default_charset', 'UTF-8');
//  All non-multibyte-safe functions are overloaded with the mbstring alternatives
ini_set ('mbstring.func_overload', 7);

// Perform a security check on the request, make sure the request is not an
// attempt to overwrite one of the PHP superglobals. This check is stolen
// from MediaWiki.
if (ini_get ('register_globals')) {
	if (isset ($_REQUEST['GLOBALS'])) {
		die ($_HOAM_language ['errors']['hoam']['security']['problem'] . $_HOAM_language ['errors']['hoam']['security']['php-global'] . $_HOAM_language ['errors']['hoam']['contact-support']);
	}
	$verboten = array (
	'GLOBALS',
	'_SERVER',
	'HTTP_SERVER_VARS',
	'_GET',
	'HTTP_GET_VARS',
	'_POST',
	'HTTP_POST_VARS',
	'_COOKIE',
	'HTTP_COOKIE_VARS',
	'_FILES',
	'HTTP_POST_FILES',
	'_ENV',
	'HTTP_ENV_VARS',
	'_REQUEST',
	'_SESSION',
	'HTTP_SESSION_VARS'
	);
	foreach ($_REQUEST as $name => $value) {
		if (in_array ($name, $verboten)) {
			header ('HTTP/1.x 500 Internal Server Error');
			die ($_HOAM_language ['errors']['hoam']['security']['problem'] . $_HOAM_language ['errors']['hoam']['security']['php-global']);
		}
		unset ($GLOBALS[$name]);
	}
}

// If somebody enters a query string of more than 512 characters, a 414 is
// returned and the script execution is stopped.
// From RFC2068:
// "The server is refusing to service the request because the Request-URI
// is longer than the server is willing to interpret. This rare
// condition is only likely to occur when a client has improperly
// converted a POST request to a GET request with long query
// information, when the client has descended into a URL "black hole" of
// redirection (e.g., a redirected URL prefix that points to a suffix of
// itself), or when the server is under attack by a client attempting to
// exploit security holes present in some servers using fixed-length
// buffers for reading or manipulating the Request-URI."
if (mb_strlen ($_SERVER['REQUEST_URI'] ) > 512 ) {
	header ('HTTP/1.1 414 Request-URI Too Long');
	exit ();
}

// Check all of the defined paths to make sure they exist and are readable.
foreach (array (HOAM_PATH_ATTACHMENT, HOAM_PATH_CLASS, HOAM_PATH_COUNTRY, HOAM_PATH_FUNCTION, HOAM_PATH_LETTER, HOAM_PATH_PLUGIN, HOAM_PATH_REPORT, HOAM_PATH_SCRIPT, HOAM_PATH_SESSION, HOAM_PATH_TEMPLATE) as $path) {
	if (file_exists ($path)) {
		if (!is_readable ($path)) {
			die (printf ($_HOAM_language ['errors']['hoam']['installation']['problem'] . $_HOAM_language ['errors']['hoam']['installation']['file-not-readable'] . $_HOAM_language ['errors']['hoam']['contact-support'], $path));
		}
	} else {
		die (printf ($_HOAM_language ['errors']['hoam']['installation']['problem'] . $_HOAM_language ['errors']['hoam']['installation']['missing-file'] . $_HOAM_language ['errors']['hoam']['installation']['must-complete'], $path));
	}
}

// Make sure the HOAM_PATH_ATTACHMENT can be written to
if (!is_writeable (HOAM_PATH_ATTACHMENT)) {
	die (printf ($_HOAM_language ['errors']['hoam']['installation']['problem'] . $_HOAM_language ['errors']['hoam']['installation']['file-not-writeable'] . $_HOAM_language ['errors']['hoam']['contact-support'], HOAM_PATH_ATTACHMENT));
}

// Make sure the HOAM_PATH_SESSION can be written to
if (is_writeable (HOAM_PATH_SESSION)) {
	session_save_path (HOAM_PATH_SESSION);
	ini_set ('session.cache_expire', 30);
	ini_set ('session.gc_probability', 100);
	// Only store the session ID in cookies
	ini_set ('session.use_only_cookies', 1);
	// Cookies only last until the browser is closed.
	// NOTE! New browser features such as Firefox's resume last session support
	// defeat this, and will continue using a cookie that should have otherwise
	// expired.
	ini_set ('session.cookie_lifetime', 0);
	// Make cookies only available over http
	ini_set ('session.cookie_httponly', 1);
} else {
	die (printf ($_HOAM_language ['errors']['hoam']['installation']['problem'] . $_HOAM_language ['errors']['hoam']['installation']['file-not-writeable'] . $_HOAM_language ['errors']['hoam']['contact-support'], HOAM_PATH_SESSION));
}

// Load the HOAM function library.
if (file_exists (HOAM_PATH_FUNCTION . 'default_library.php')) {
	if (is_readable (HOAM_PATH_FUNCTION . 'default_library.php')) {
		require_once (HOAM_PATH_FUNCTION . 'default_library.php');
	} else {
		die (printf ($_HOAM_language ['errors']['hoam']['installation']['problem'] . $_HOAM_language ['errors']['hoam']['installation']['file-not-readable'] . $_HOAM_language ['errors']['hoam']['contact-support'], HOAM_PATH_FUNCTION . 'default_library.php'));
	}
} else {
	die (printf ($_HOAM_language ['errors']['hoam']['installation']['problem'] . $_HOAM_language ['errors']['hoam']['installation']['missing-file'] . $_HOAM_language ['errors']['hoam']['installation']['must-complete'], HOAM_PATH_FUNCTION . 'default_library.php'));
}

if (HOAM_DEBUG) {
	// Start a timer for debugging purposes.
	$timerStart = $timerEnd = $timerElapsed = 0;
	HOAM_timerStart ();
}

// Load the user's specified database settings.
if (file_exists (HOAM_PATH . 'hoam-config.ini')) {
	if (is_readable (HOAM_PATH . 'hoam-config.ini')) {
		$temp_config = parse_ini_file (HOAM_PATH . 'hoam-config.ini');
		if (!empty ($temp_config)) {
			define ('HOAM_DB_NAME', $temp_config ['dbname']);
			define ('HOAM_DB_USER', $temp_config ['user']);
			define ('HOAM_DB_PASSWORD', $temp_config ['password']);
			define ('HOAM_DB_SERVER', $temp_config ['server']);
			define ('HOAM_DB_PORT', $temp_config ['port']);
		}
		unset ($temp_config);
	} else {
		die (printf ($_HOAM_language ['errors']['hoam']['installation']['problem'] . $_HOAM_language ['errors']['hoam']['installation']['file-not-readable'] . $_HOAM_language ['errors']['hoam']['contact-support'], HOAM_PATH . 'hoam-config.ini'));
	}
} elseif (defined ('HOAM_INSTALLING') AND HOAM_INSTALLING) {
} else {
	die (printf ($_HOAM_language ['errors']['hoam']['installation']['problem'] . $_HOAM_language ['errors']['hoam']['installation']['missing-file'] . $_HOAM_language ['errors']['hoam']['installation']['must-complete'], HOAM_PATH . 'hoam-config.ini'));
}
?>
