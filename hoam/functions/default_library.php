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

spl_autoload_register (function ($class_name) {
	// Try to autoload the classes. Try the new path first, then the old path.
	// We remove the first 5 characters because internal classes are typically
	// named 'HOAM_' plus the classname.
	if (is_readable (HOAM_PATH_CLASS . mb_substr ($class_name, 5) . '.php')) {
		require_once (HOAM_PATH_CLASS . mb_substr ($class_name, 5) . '.php');
//	} else {
//		printf ($GLOBALS['_HOAM_language'] ['errors']['hoam']['installation']['problem'] . $GLOBALS['_HOAM_language'] ['errors']['hoam']['installation']['missing-file'] . $GLOBALS['_HOAM_language'] ['errors']['hoam']['installation']['must-complete'], HOAM_PATH_CLASS . $class_name . '.php');
//		exit ();
	}
});

/*// error handler function
function myErrorHandler($errno, $errstr, $errfile, $errline)
{
switch ($errno) {
case E_USER_ERROR:
echo "<b>My ERROR</b> [$errno] $errstr<br />\n";
echo "  Fatal error in line $errline of file $errfile";
echo ", PHP " . PHP_VERSION . " (" . PHP_OS . ")<br />\n";
echo "Aborting...<br />\n";
exit(1);
break;
case E_USER_WARNING:
echo "<b>My WARNING</b> [$errno] $errstr<br />\n";
break;
case E_USER_NOTICE:
echo "<b>My NOTICE</b> [$errno] $errstr<br />\n";
break;
default:
echo "Unknown error type: [$errno] $errstr<br />\n";
break;
}
}

// set to the user defined error handler
$old_error_handler = set_error_handler("myErrorHandler");
*/

/**
 * Encodes HTML safely for UTF-8. Use instead of html_encode.
 *
 * @param string $var
 * @return string
 */
function html_encode ($var) {
	return htmlentities ($var, ENT_QUOTES, 'UTF-8') ;
}

function in_array_nocase ($search, &$array) {
	// Copied from php.net comments
	$search = mb_strtolower ($search);
	foreach ($array as $item)
	if (mb_strtolower ($item) == $search) {
		return TRUE;
	}
	return FALSE;
}

function is_false ($value) {
	// returns TRUE or FALSE depending on $value
	// Tried doing is_string() with a switch, but kept getting wacky results.
	// May be a bug in PHP, because the same test outside the switch works fine.
	if (($value == 0) || ($value === FALSE)) {
		return TRUE;
	} elseif (is_string ($value)) {
		if (mb_strtoupper ($value) === "FALSE")
		return TRUE;
		if (mb_strtoupper ($value) === "NO")
		return TRUE;
	}
	return FALSE;
}

function is_true ($value) {
	// returns TRUE or FALSE depending on $value
	// Tried doing is_string() with a switch, but kept getting wacky results.
	// May be a bug in PHP, because the same test outside the switch works fine.
	if (($value == 1) || ($value === TRUE)) {
		return TRUE;
	} elseif (is_string ($value)) {
		if (mb_strtoupper ($value) === "TRUE")
		return TRUE;
		if (mb_strtoupper ($value) === "YES")
		return TRUE;
	}
	return FALSE;
}

function isCLI () {
	// http://www.codediesel.com/php/quick-way-to-determine-if-php-is-running-at-the-command-line/
	if (php_sapi_name () == 'cli' && empty ($_SERVER['REMOTE_ADDR'])) {
		return TRUE;
	} else {
		return FALSE;
	}
}

function checkOwnership ($path) {
	// We have different behavior to account for differences between Linux and
	// Windows.
	if (strtoupper (substr (PHP_OS, 0, 3)) === 'WIN') {
		$current_user = get_current_user ();
		// https://stackoverflow.com/questions/56445688/reading-file-and-directory-owner-using-php-on-the-windows/56458656#56458656
		$su = new COM ("ADsSecurityUtility");
		$securityInfo = $su -> GetSecurityDescriptor ($path, 1, 1);
		$directory_owner = $securityInfo -> owner;
	} else {
		$current_user = posix_getpwuid (posix_geteuid ());
		$directory_owner = posix_getpwuid (fileowner ($path));
	}
	if ($current_user == $directory_owner) {
		return TRUE;
	} else {
		return FALSE;
	}
}

function mysqlTimestampToUnix ($timestamp) {
	$yr		= mb_substr ($timestamp, 0, 4);
	$mon	= mb_substr ($timestamp, 4, 2);
	$day	= mb_substr ($timestamp, 6, 2);
	$hr		= mb_substr ($timestamp, 8, 2);
	$min	= mb_substr ($timestamp, 10, 2);
	$sec	= mb_substr ($timestamp, 12, 2);

	return mktime ($hr, $min, $sec, $mon, $day, $yr);
}

function returnHistoryDescription ($string, $serialized_array) {
	if (!empty ($serialized_array)) {
		$params = unserialize ($serialized_array);
		$param_0 = $params[0];
		$param_1 = $params[1];
		$param_2 = $params[2];
		$param_3 = $params[3];
		$param_4 = $params[4];
		return (sprintf ($string, $param_0, $param_1, $param_2, $param_3, $param_4));
	} else {
		return (sprintf ($string));
	}
}

function HOAM_calcString ($mathString) {
	// From http://www.gamedev.net/community/forums/viewreply.asp?ID=2896443
	$cf_DoCalc = create_function ('', 'return (' . $mathString . ');');
	return ($cf_DoCalc());
};

function HOAM_censor ($string, $bad_words = NULL) {
	// Censors the provided $words from $string.
	// The caller may provide a list of words to censor, or rely on the default
	// list.
	global $_HOAM_language, $_HOAM_setting;

	// What character should be used to mask the censored words?
	$mask = $_HOAM_setting ['website']['mask_character'];
	
	// If we weren't given a list of words to censor, import the default list.
	if ($bad_words === NULL) {
		// Get the list of words to censor from the config file.
		$file = $_HOAM_setting ['files']['censored_words'];
		if (is_readable (HOAM_PATH . $file)) {
			$bad_words = file (HOAM_PATH . $file);
			if (!empty ($bad_words)) {
				// Because file reads in the trailing  or \r, we need to strip
				// it out before looking in the array. Also, remove any lines
				// beginning with # (comments)
				$temp_array = array();
				foreach ($bad_words as $word) {
					if (mb_substr ($word, 0, 1) != '#') {
						array_push ($temp_array, mb_strtolower (trim ($word)));
					}
				}
				$bad_words = $temp_array;
				unset ($temp_array);
			}
		}
	}
	if (!empty ($bad_words)) {
		// See http://www.codingforums.com/showpost.php?s=0d7af63ee3979a447f4a06c85a519c62&p=846174&postcount=2
		$chars_to_ignore = array (';', '-', '.', '_', '^', '/', '<', '>', "\n", "\r");
    	$ex = explode (' ', $string);
    	for ($i = 0; $i < count ($ex); $i++) {
        	$word = mb_strtolower (str_ireplace ($chars_to_ignore, '', strip_tags ($ex[$i])));
        	if (in_array ($word, $bad_words)) {
        		$replace = '';
        		for ($j = 0; $j < mb_strlen ($word); $j++) $replace .= '*';
				$string = preg_replace ('/' . $word . '/i', $replace, $string);
        	}			
    	}
	}
	return $string;
}

function HOAM_date ($format, $time = NULL) {
	global $_HOAM_language;
	// This simply wraps around the PHP date function, the only difference is
	// it will return "unknown" when $time is empty rather than 12/31/1969.
	if (isset ($time)) {
		if ($time) {
			return (date ($format, $time));
		} else {
			return ($_HOAM_language ['common']['na']);
		}
	} else {
		// Make sure we still return the current date when asked.
		return (date ($format));
	}
}

function HOAM_diff ($old_string, $new_string) {
	// From https://coderwall.com/p/3j2hxq/find-and-format-difference-between-two-strings-in-php
    $from_start = strspn ($old_string ^ $new_string, "\0");
    $from_end = strspn (strrev ($old_string) ^ strrev ($new_string), "\0");

    $old_end = mb_strlen ($old_string) - $from_end;
    $new_end = mb_strlen ($new_string) - $from_end;

    $start = substr ($new_string, 0, $from_start);
    $end = substr ($new_string, $new_end);
    $new_diff = substr ($new_string, $from_start, $new_end - $from_start);  
    $old_diff = substr ($old_string, $from_start, $old_end - $from_start);

    $new_string = "$start<ins>$new_diff</ins>$end";
    $old_string = "$start<del>$old_diff</del>$end";
    return array ("old" => $old_string, "new" => $new_string);
}

function HOAM_humanFilesize ($bytes, $decimals = 1) {
	$sizes = 'BKMGTP';
	$factor = floor ((strlen ($bytes) - 1) / 3);
	return sprintf ("%.{$decimals}f", $bytes / pow (1024, $factor)) . @$sizes[$factor];
}

function HOAM_info () {
	global $_HOAM_country, $_HOAM_language, $_HOAM_setting;
	
	$colgroup = '<colgroup>';
	$colgroup .= '<col width="25%" />';
	$colgroup .= '<col width="75%" />';
	$colgroup .= '</colgroup>';
	
    // Display all constants
    $HTML = $_HOAM_language ['field_names']['hoam']['constants'];
	$thead = '<thead>';
	$thead .= '<tr>';
	$thead .= '<th>' . $_HOAM_language ['tables']['column']['generic']['constant'] . '</th>';
	$thead .= '<th>' . $_HOAM_language ['tables']['column']['generic']['value'] . '</th>';
	$thead .= '</tr>';
	$thead .= '</thead>';
	$tbody = '<tbody>';
	$tbody .= '<tr><td>HOAM_VERSION</td><td>' . HOAM_VERSION . '</td></tr>';
	$tbody .= '<tr><td>HOAM_RELEASE</td><td>' . HOAM_RELEASE . '</td></tr>';
	$tbody .= '<tr><td>HOAM_DEBUG</td><td>';
	HOAM_DEBUG ? $tbody .= 'TRUE' : $tbody .= 'FALSE';
	$tbody .= '</td></tr>';
	$tbody .= '<tr><td>HOAM_LOG_DB</td><td>' . HOAM_LOG_DB . '</td></tr>';
	$tbody .= '<tr><td>HOAM_LOG_FILE</td><td>' . HOAM_LOG_FILE . '</td></tr>';
	$tbody .= '<tr><td>HOAM_LOG_SYSLOG</td><td>' . HOAM_LOG_SYSLOG . '</td></tr>';
	$tbody .= '<tr><td>HOAM_MESSAGE_EMERGENCY</td><td>' . HOAM_MESSAGE_EMERGENCY . '</td></tr>';
	$tbody .= '<tr><td>HOAM_MESSAGE_ALERT</td><td>' . HOAM_MESSAGE_ALERT . '</td></tr>';
	$tbody .= '<tr><td>HOAM_MESSAGE_CRITICAL</td><td>' . HOAM_MESSAGE_CRITICAL . '</td></tr>';
	$tbody .= '<tr><td>HOAM_MESSAGE_ERROR</td><td>' . HOAM_MESSAGE_ERROR . '</td></tr>';
	$tbody .= '<tr><td>HOAM_MESSAGE_WARNING</td><td>' . HOAM_MESSAGE_WARNING . '</td></tr>';
	$tbody .= '<tr><td>HOAM_MESSAGE_NOTICE</td><td>' . HOAM_MESSAGE_NOTICE . '</td></tr>';
	$tbody .= '<tr><td>HOAM_MESSAGE_INFORMATIONAL</td><td>' . HOAM_MESSAGE_INFORMATIONAL . '</td></tr>';
	$tbody .= '<tr><td>HOAM_MESSAGE_DEBUG</td><td>' . HOAM_MESSAGE_DEBUG . '</td></tr>';
	$tbody .= '<tr><td>HOAM_PATH</td><td>' . HOAM_PATH . '</td></tr>';
	$tbody .= '<tr><td>HOAM_PATH_ATTACHMENT</td><td>' . HOAM_PATH_ATTACHMENT . '</td></tr>';
	$tbody .= '<tr><td>HOAM_PATH_ATTACHMENT_PREVIEW</td><td>' . HOAM_PATH_ATTACHMENT_PREVIEW . '</td></tr>';
	$tbody .= '<tr><td>HOAM_PATH_CLASS</td><td>' . HOAM_PATH_CLASS . '</td></tr>';
	$tbody .= '<tr><td>HOAM_PATH_COUNTRY</td><td>' . HOAM_PATH_COUNTRY . '</td></tr>';
	$tbody .= '<tr><td>HOAM_PATH_FUNCTION</td><td>' . HOAM_PATH_FUNCTION . '</td></tr>';
	$tbody .= '<tr><td>HOAM_PATH_LANGUAGE</td><td>' . HOAM_PATH_LANGUAGE . '</td></tr>';
	$tbody .= '<tr><td>HOAM_PATH_LETTER</td><td>' . HOAM_PATH_LETTER . '</td></tr>';
	$tbody .= '<tr><td>HOAM_PATH_PLUGIN</td><td>' . HOAM_PATH_PLUGIN . '</td></tr>';
	$tbody .= '<tr><td>HOAM_PATH_REPORT</td><td>' . HOAM_PATH_REPORT . '</td></tr>';
	$tbody .= '<tr><td>HOAM_PATH_SCRIPT</td><td>' . HOAM_PATH_SCRIPT . '</td></tr>';
	$tbody .= '<tr><td>HOAM_PATH_SESSION</td><td>' . HOAM_PATH_SESSION . '</td></tr>';
	$tbody .= '<tr><td>HOAM_PATH_TEMPLATE</td><td>' . HOAM_PATH_TEMPLATE . '</td></tr>';
	$tbody .= '<tr><td>HOAM_FILENAME_LENGTH</td><td>' . HOAM_FILENAME_LENGTH . '</td></tr>';
	$tbody .= '</tbody>';
	$HTML .= '<table class="center">' . $colgroup . $thead . preg_replace ('/thead/', 'tfoot', $thead) . $tbody . '</table>';

    // Display important settings
    $HTML .= $_HOAM_language ['field_names']['hoam']['client_preferences'];
    $thead = '<thead>';
    $thead .= '<tr>';
    $thead .= '<th>' . $_HOAM_language ['tables']['column']['generic']['setting'] . '</th>';
    $thead .= '<th>' . $_HOAM_language ['tables']['column']['generic']['value'] . '</th>';
    $thead .= '</tr>';
    $thead .= '</thead>';
    $tbody = '<tbody>';
    $tbody .= '<tr><td>' . $_HOAM_language ['field_names']['user']['preference']['language'] . '</td><td>' . $_SERVER['HTTP_ACCEPT_LANGUAGE'] . '</td></tr>';
    $tbody .= '</tbody>';
    $HTML .= '<table class="center">' . $colgroup . $thead . preg_replace ('/thead/', 'tfoot', $thead) . $tbody . '</table>';
	
	// Display important settings
	$HTML .= $_HOAM_language ['field_names']['hoam']['settings'];
	$thead = '<thead>';
	$thead .= '<tr>';
	$thead .= '<th>' . $_HOAM_language ['tables']['column']['generic']['setting'] . '</th>';
	$thead .= '<th>' . $_HOAM_language ['tables']['column']['generic']['value'] . '</th>';
	$thead .= '</tr>';
	$thead .= '</thead>';
	$tbody = '<tbody>';
	if ($_HOAM_setting ['hoam']['last_maintenance_run']) {
		$tbody .= '<tr><td>' . ucwords ($_HOAM_language ['field_names']['hoam']['last_maintenance']) . '</td><td>' . date ($_HOAM_country ['date']['format_reports'], strtotime ($_HOAM_setting ['hoam']['last_maintenance_run'])) . '</td></tr>';
	} else {
		$tbody .= '<tr><td>' . ucwords ($_HOAM_language ['field_names']['hoam']['last_maintenance']) . '</td><td>' . $_HOAM_language ['common']['na'] . '</td></tr>';
	}
	$tbody .= '</tbody>';
	$HTML .= '<table class="center">' . $colgroup . $thead . preg_replace ('/thead/', 'tfoot', $thead) . $tbody . '</table>';
	
	return ($HTML);
}

function HOAM_isPowerOf2 ($number) {
	// Short function to verify that number is power of 2.
	// From http://www.velocityreviews.com/forums/t315506-test-whether-a-number-is-a-power-of-2.html
	if (($number AND !($number & ($number - 1))) OR $number === 0) {
		return TRUE;
	} else {
		return FALSE;
	}
}

function HOAM_countOnlineUsers () {
	global $_HOAM_setting;
	
	$sessionpath = session_save_path ();
	if (mb_strpos ($sessionpath, ";") !== FALSE)
  		$sessionpath = mb_substr ($sessionpath, mb_strpos ($sessionpath, ";") + 1);
	if ($handle = opendir ($sessionpath)) {
		$count = 0;
		while (FALSE !== ($file = readdir ($handle))) {
			if ($file != '.' AND $file != '..') {
				// Using filemtime instead of atime because most Linux systems
				// nowadays are mounted relatime, and windows doesn't support
				// atime.
				if ((time () - filemtime ($sessionpath . $file)) < $_HOAM_setting ['website']['online_time'] * 60) {
					$count++;
				}
			}
		}
		closedir ($handle);
		return ($count);
	} else {
		return FALSE;
	}
}
 
function HOAM_organizationReturnMailingAddress () {
	global $_HOAM_country, $_HOAM_setting;
	
	$HTML = '';
	if (!empty($_HOAM_setting ['organization']['mailing_address']['line1'])) {
		$HTML .= $_HOAM_setting ['organization']['mailing_address']['line1'] . '<br />';
	}
	if (!empty($_HOAM_setting ['organization']['mailing_address']['line2'])) {
		$HTML .= $_HOAM_setting ['organization']['mailing_address']['line2'] . '<br />';
	}
	if (!empty($_HOAM_setting ['organization']['mailing_address']['line3'])) {
		$HTML .= $_HOAM_setting ['organization']['mailing_address']['line3'] . '<br />';
	}
	$HTML .= $_HOAM_setting ['organization']['mailing_address']['city'] . ', ';
	$HTML .= $_HOAM_setting ['organization']['mailing_address']['state'] . ' ';
	if (defined ('HOAM_INSTALLING') AND !HOAM_INSTALLING) {
		$HTML .= preg_replace ($_HOAM_country ['postalcode']['match'], $_HOAM_country ['postalcode']['format'], $_HOAM_setting ['organization']['mailing_address']['postalcode'], 1);
	}
	return $HTML;
}

function HOAM_organizationReturnStateName () {
	global $_HOAM_country, $_HOAM_setting;
	if ($_HOAM_setting ['organization']['physical_address']['state']) {
		foreach ($_HOAM_country ['state'] as $state) {
			if ($state['value'] == $_HOAM_setting ['organization']['physical_address']['state']) {
				return $state['description'];
			}
		}
	} else {
		return FALSE;
	}
	return FALSE;
}

function HOAM_queueAppend (&$array, $item) {
	// Append a user-specified item to the specified array.
	array_push ($array, $item);
}

function HOAM_queueInsertAfter (&$array, $after, $item) {
	// Insert a user-specified item into the specified array after the
	// $after indexed item.
	if (in_array ($after, $array)) {
		$new_array = array ();
		while ($current = array_shift ($array)) {
			if ($current == $after) {
				array_push ($new_array, $current);
				array_push ($new_array, $item);
			} else {
				array_push ($new_array, $current);
			}
		}
		$array = $new_array;
	} else {
		return FALSE;
	}
}

function HOAM_queueInsertBefore (&$array, $before, $item) {
	// Insert a user-specified item into the specified array before the
	// $before indexed item.
	if (in_array ($before, $array)) {
		$new_array = array ();
		while ($current = array_shift ($array)) {
			if ($current == $before) {
				array_push ($new_array, $item);
				array_push ($new_array, $current);
			} else {
				array_push ($new_array, $current);
			}
		}
		$array = $new_array;
	} else {
		return FALSE;
	}
}

function HOAM_queueInsertValue (&$array, $item, $value) {
	// Update value assigned to an array member
	if (in_array ($item, $array)) {
		$new_array = array ();
		while ($current = array_shift ($array)) {
			if ($current == $item) {
				array_push ($new_array, array ($current => $value));
			} else {
				array_push ($new_array, $current);
			}
		}
		$array = $new_array;
	} else {
		return FALSE;
	}
}

function HOAM_queueRemove (&$array, $item) {
	// Remove the specified item from the queue
	if (in_array ($item, $array)) {
		$new_array = array ();
		while ($current = array_shift ($array)) {
			if ($current == $item) {
			} else {
				array_push ($new_array, $current);
			}
		}
		$array = $new_array;
	} else {
		return FALSE;
	}
}

function HOAM_queueRun ($function_list) {
	global $_HOAM_language;

	$output = NULL;
	foreach ($function_list as $HOAM_function) {
		if (is_array ($HOAM_function)) {
            foreach ($HOAM_function as $key => $value) {
                $temp = [$key, $value];
            }
			if (is_array ($value)) {
				// Look to see if the current array contains parameters for the
				// current function, or if it's another queue of functions.
				if (array_key_exists ('params', $value)) {
					call_user_func ($key, $value['params']);
				} else {
					HOAM_queueRun ($HOAM_function);
				}
			} else {
				HOAM_queueRun ($HOAM_function);
			}
		} else {
			if (function_exists ($HOAM_function)) {
				$output .= call_user_func ($HOAM_function);
			} else {
				// There needs to be a better reaction from the system.
				die ($_HOAM_language ['errors']['hoam']['installation']['problem'] .
				$_HOAM_language ['errors']['hoam']['installation']['missing-function'] .
				'<ul><li>' . $HOAM_function . '</li></ul>' .
				$_HOAM_language ['errors']['hoam']['contact-support']);
			}
		}
	}
	echo $output;
}

function HOAM_returnAcronym ($string) {
	global $_HOAM_language;
	$acronyms = &$_HOAM_language ['acronymn'];
	// Remove any older ABBR sections
	$string = preg_replace ("/<\/?abbr(.|\s)*?>/", "", $string);

	foreach ($acronyms as $acronym => $definition) {
		$string = preg_replace ('#\b$acronym\b(?!</(ac|sp))#', '<acronym title="$definition">$acronym</acronym>', $string, 1);
	}

	return ($string);
}

function HOAM_returnAddress ($lot) {
	// Created only so I don't have to type this junk everywhere.
	global $_HOAM_language;
	$lot_class = new HOAM_lot ('lots');

	if (!is_array ($lot)) {
		$lot = $lot_class -> listAll ($lot);
		$lot = $lot[0];
	}
	$HTML = $lot['address'] . ' ' . $lot['street'];
	if (!empty ($lot['building'])) {
		$bldg = $_HOAM_language ['field_names']['lot']['building'] . ' ' . $lot['building'];
	} else {
		$bldg = '';
	}
	if (!empty ($lot['suite'])) {
		$suite = $_HOAM_language ['field_names']['lot']['suite'] . ' ' . $lot['suite'];
	} else {
		$suite = '';
	}
	if ($bldg AND $suite) {
		$HTML .= ' (' . $bldg . ' ' . $suite . ')';
	} else {
		if ($bldg || $suite) {
			$HTML .= ' (' . $bldg . $suite . ')';
		}
	}
	return ($HTML);
}

function HOAM_returnColoredString ($string, $options = array ()) {
	// Returns a string that contains HTML formatted output.
	// The default color array is a graduated rainbow.

	if (!isset ($options['colors'])) {
		$options['colors'] = array ('red', 'orange', 'yellow', 'lime', 'green', 'aqua', 'blue', 'navy', 'indigo', 'violet', 'pink', 'silver', 'gray', 'black');
	}
	
	$string = preg_split('//', $string, -1, PREG_SPLIT_NO_EMPTY);
	$color_count = count ($options['colors']);
	foreach ($string as $key => $character) {
		if ($character != ' ') {
			$HTML .= '<span style="color: ' . $options['colors'][mt_rand (0, $color_count - 1)] . '">' . $character . '</span>';
		} else {
			$HTML .= $character;
		}
	}
	return $HTML;
}

function HOAM_returnColoredStringCallback ($matches) {
	return (HOAM_returnColoredString ($matches[1]));
}

function HOAM_returnCurrency ($amount) {
	// This function attempts to use money_format if it's available, otherwise
	// it punts and uses it's own. Because of the (lovely) fp inaccuracies, fix
	// it if the amount is actually 0 with a bunch of exponent digits.
	if (round ($amount, 2) == -0) {
		$amount = 0;
	}
	if (function_exists ('money_format')) {
		return (money_format('%n', $amount));
	} else {
		// Just using number format for now, should cover most cases.
		return (number_format ($amount, 2));
	}
	$formatted = new NumberFormatter ($_HOAM_setting ['website']['locale'], NumberFormatter::CURRENCY);
	$formatted -> format ($amount);

}

function HOAM_returnFiles ($path, $ext = NULL) {
	// Returns a list of all files in the specified path having the requested
	// extension
	$files = scandir ($path);
	if ($files) {
		sort ($files);
		$file_list = array();
		foreach ($files as $file) {
			if (!is_dir ($file)) {
				if ($ext) {
					if (mb_strtolower (mb_substr ($file, mb_strrpos ($file, '.') + 1, mb_strlen ($file))) == $ext) {
						array_push ($file_list, $file);
					}
				} else {
					array_push ($file_list, $file);
				}
			}
		}
		return $file_list;
	}
	return FALSE;
}

function HOAM_returnLocalizedString ($message, $param_1 = NULL, $param_2 = NULL, $param_3 = NULL, $param_4 = NULL, $param_5 = NULL) {
	global $_HOAM_language, $_HOAM_log;
	// ! Need to be careful, this function is also called with not really clean
	// ! data (just addslashes) from the languageLookup.php script. Don't touch
	// ! the database or filesystem.
	
	// THIS IS A CHANGE FROM BEFORE!
	// Make sure there's NOT a /slash at the beginning OR end of the string.
	//
	// !! This regex needs to be changed to also strip any ending slash.
	//
	$message = preg_replace ('/^\//', '', $message, 1);
	$message = explode ('|', $message);
	$elem =& $GLOBALS['_HOAM_language'];
	foreach ($message as &$key) {
		if (is_array ($elem)) {
      			$elem =& $elem[$key];
		}
	}
	// I could probably work up something fancy with func_get_arg and family,
	// but this works and I don't feel like wasting a bunch of time right now.
	return (sprintf ($elem, $param_1, $param_2, $param_3, $param_4, $param_5));
}

function HOAM_returnTruncatedString ($string, $length) {
	if ((int) $length AND (mb_strlen ($string) > (int) $length)) {
		$string = mb_substr ($string, 0, (int) $length);
		$string = preg_replace ('/\w+$/', '', $string);
		$string .= "&hellip;";
	}
	return $string;
}

function HOAM_returnStringMultiLine ($string, $length) {
	if ($length AND (mb_strlen ($string) > $length)) {
		$newstr = '';
		while (mb_strlen ($string)) {
			$newstr .= mb_substr ($string, 0, $length);
			$newstr .= '<br />';
			$string = mb_substr ($string, $length);
		}
		return $newstr;
	}
	return $string;
}

function HOAM_returnWebsiteTitle () {
	// Simply consolidated a couple uses of this to one location.
	if (empty ($GLOBALS['_HOAM_setting'] ['website']['title'])) {
		if (!empty ($GLOBALS['_HOAM_setting'] ['organization']['name'])) {
			return ($GLOBALS['_HOAM_setting'] ['organization']['name']);
		} else {
			return ('HOAM');
		}
	} else {
		return ($GLOBALS['_HOAM_setting'] ['website']['title']);
	}
}

function HOAM_timerStart () {
	if (HOAM_DEBUG) {
		global $timerStart;
		$mtime = explode (' ', microtime ());
		$mtime = $mtime[1] + $mtime[0];
		$timerStart = $mtime;
		return TRUE;
	} else {
		return FALSE;
	}
}

function HOAM_timerStop ($precision = 3) {
	if (HOAM_DEBUG) {
		global $timerStart, $timerEnd, $timerElapsed;
		$mtime = explode (' ', microtime ());
		$mtime = $mtime[1] + $mtime[0];
		$timerEnd = $mtime;
		$timerElapsed = number_format ($timerEnd - $timerStart, $precision);
		return $timerElapsed;
	} else {
		return FALSE;
	}
}

function HOAM_timerLog ($message = NULL) {
	if (HOAM_DEBUG) {
		global $_HOAM_log, $timerStart, $timerEnd, $timerElapsed;
		HOAM_timerStop ();
		$_HOAM_log -> add ($message . 'Timer start: ' . $timerStart . ' timer end: ' . $timerEnd . ' time elapsed: ' . $timerElapsed, HOAM_MESSAGE_DEBUG);
	} else {
		return FALSE;
	}		
}

function HOAM_userReturnSecurityQuestion ($user_question) {
	global $_HOAM_language;
	foreach ($_HOAM_language ['password_hint_phrases'] as $question) {
		if ($question['value'] == $user_question) {
			return $question['description'];
		}
	}
	return FALSE;
}

function HOAM_writeIni ($assoc_arr, $file, $has_sections = FALSE) {
	// From https://stackoverflow.com/questions/1268378/create-ini-file-write-values-in-php
	$content = '';

    if ($has_sections) {
        foreach ($assoc_arr as $key => $elem) {
            $content .= "[" . $key . "]\r\n";
            foreach ($elem as $key2 => $elem2) {
                if (is_array ($elem2)) {
                    for ($i = 0; $i < count ($elem2); $i++) {
                        $content .= $key2 . "[] = \"" . $elem2[$i] . "\"\r\n";
                    }
                }
                else if ($elem2 == "") $content .= $key2 . " = \r\n";
                else $content .= $key2 . " = \"" . $elem2 . "\"\r\n";
            }
        }
    } else { 
        foreach ($assoc_arr as $key => $elem) {
            if (is_array ($elem)) {
                for ($i = 0; $i < count ($elem); $i++) {
                    $content .= $key . "[] = \"" . $elem[$i] . "\"\r\n";
                }
            }
            else if ($elem == "") $content .= $key . " = \r\n";
            else $content .= $key . " = \"" . $elem . "\"\r\n";
        }
    }

    if (!$handle = fopen ($file, 'w')) {
        return FALSE;
    }

    $success = fwrite ($handle, $content);
    fclose ($handle);

    return ($success); 
}

?>
