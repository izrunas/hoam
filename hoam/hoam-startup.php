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

define ('HOAM_INSTALLING', FALSE);

require_once (dirname (__FILE__) . '/hoam-init.php');

// Try to connect to the database and access the configuration table
$_HOAM_config = new HOAM_config ('configuration');
$_HOAM_config -> loadConfig ();

// Start log instance
$_HOAM_log = new HOAM_log ('log');

// Attempt to detect and use the site's specified country settings
if (isset ($_HOAM_setting ['organization']['countrycode'])) {
	if (file_exists (HOAM_PATH_COUNTRY . $_HOAM_setting ['organization']['countrycode'] . '.php')) {
	} else {
		$_HOAM_setting ['organization']['countrycode'] = 'us';
	}
} else {
	$_HOAM_setting ['organization']['countrycode'] = 'us';
}
if (file_exists (HOAM_PATH_COUNTRY . $_HOAM_setting ['organization']['countrycode'] . '.php')) {
	if (is_readable (HOAM_PATH_COUNTRY . $_HOAM_setting ['organization']['countrycode'] . '.php')) {
		require_once (HOAM_PATH_COUNTRY . $_HOAM_setting ['organization']['countrycode'] . '.php');
	} else {
		$_HOAM_log -> add (sprintf ($_HOAM_language ['errors']['hoam']['installation']['problem'] . $_HOAM_language ['errors']['hoam']['installation']['file-not-readable'], HOAM_PATH_COUNTRY . $_HOAM_setting ['organization']['countrycode']), HOAM_MESSAGE_CRITICAL);
		die (printf ($_HOAM_language ['errors']['hoam']['installation']['problem'] . $_HOAM_language ['errors']['hoam']['installation']['file-not-readable'] . $_HOAM_language ['errors']['hoam']['contact-support'], HOAM_PATH_COUNTRY . $_HOAM_setting ['organization']['countrycode']));
	}
} else {
	die (printf ($_HOAM_language ['errors']['hoam']['installation']['problem'] . $_HOAM_language ['errors']['hoam']['installation']['missing-file'] . $_HOAM_language ['errors']['hoam']['installation']['must-complete'], HOAM_PATH_COUNTRY . $_HOAM_setting ['organization']['countrycode']));
}

// Set the locale to be used by PHP when doing formatting.
setlocale (LC_ALL, $_HOAM_setting ['website']['locale']);
setlocale (LC_MONETARY, $_HOAM_setting ['website']['locale']);

// Increase the session lifetime/timeout to the configured idle time
// We can't do this during init because the configuration hasn't been loaded yet.
ini_set ('session.gc_maxlifetime', $_HOAM_setting ['website']['idle_time'] * 60);

// Load the default queues
if (file_exists (HOAM_PATH . '/hoam/hoam-queues.php')) {
    if (is_readable (HOAM_PATH . '/hoam/hoam-queues.php')) {
        require_once (HOAM_PATH . '/hoam/hoam-queues.php');
    } else {
        $_HOAM_log -> add (sprintf ($_HOAM_language ['errors']['hoam']['installation']['problem'] . $_HOAM_language ['errors']['hoam']['installation']['file-not-readable'], HOAM_PATH . 'hoam-queues.php'), HOAM_MESSAGE_CRITICAL);
        die (printf ($_HOAM_language ['errors']['hoam']['installation']['problem'] . $_HOAM_language ['errors']['hoam']['installation']['file-not-readable'] . $_HOAM_language ['errors']['hoam']['contact-support'], HOAM_PATH . 'hoam-queues.php'));
    }
} else {
    die (printf ($_HOAM_language ['errors']['hoam']['installation']['problem'] . $_HOAM_language ['errors']['hoam']['installation']['missing-file'] . $_HOAM_language ['errors']['hoam']['installation']['must-complete'], HOAM_PATH . 'hoam-queues.php'));
}

/*
For plugins, I think we need to do
1) Get a list of all files in the HOAM_PATH_PLUGIN directory
2) If a directory is found, try to load $dir/plugin.php, otherwise try to load
   each .php found.
3) Check (in the database / configuration) whether the plugin is enabled or
   disabled: $config -> pluginEnabled ($plugin -> name) ?
4) If the plugin is enabled, load the configuration for it something like
   $plugin -> loadConfig() ?
*/

$_HOAM_plugin_list = array ();
$temp_plugin_list = scandir (HOAM_PATH_PLUGIN);
foreach ($temp_plugin_list as $current_plugin) {
	if (is_dir (HOAM_PATH_PLUGIN . $current_plugin) AND
		(($current_plugin != '.') AND ($current_plugin != '..'))) {
		if (is_readable (HOAM_PATH_PLUGIN . $current_plugin . '/plugin.php')) {
			require_once (HOAM_PATH_PLUGIN . $current_plugin . '/plugin.php');
			// We need to do some sanity checks here, such as verifying that
			// a priority level has been set, that a path exists for user-
			// visible plugins, etc.
			array_push ($_HOAM_plugin_list, $plugin);
			// Clear out the plugin information that we loaded, we're now going to use the
			// information stored in $_HOAM_plugin_list
			$_HOAM_log -> add ('Loaded plugin "' . $plugin['name'] . '".', HOAM_MESSAGE_DEBUG);
			unset ($plugin);
		} else {
			$_HOAM_log -> add ('Failure loading plugin "' . $current_plugin . '".', HOAM_MESSAGE_ERROR);
		}
	}
}
unset ($temp_plugin_list);

// Need to sort these plugins by priority, then name.
foreach ($_HOAM_plugin_list as $current_plugin) {
	// If the plugin is enabled, init it.
	call_user_func ($current_plugin ['init']);
}

// Set the maximum attachment upload size
// Make sure we have enough memory available to upload the attachments also.
if ($_HOAM_setting ['attachment']['maximum_file_size'] < 33554432) {
	ini_set ('memory_limit', 67108864);
} else {
	ini_set ('memory_limit', $_HOAM_setting ['attachment']['maximum_file_size'] * 1.25);
}
ini_set ('post_max_size', $_HOAM_setting ['attachment']['maximum_file_size']);
ini_set ('upload_max_filesize', $_HOAM_setting ['attachment']['maximum_file_size']);

// Set the configured time-zone for the site
date_default_timezone_set ($_HOAM_setting ['website']['timezone']);

// If we've made it this far, then we likely have a reasonably functional system
$hoam_started = TRUE;

?>
