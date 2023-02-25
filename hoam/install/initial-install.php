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

if (isset ($_SERVER['HTTP_REFERER']) AND ($_SERVER['HTTP_REFERER'] != ("http://" . $_SERVER['SERVER_NAME'] . 'install.php'))) {
	define ('HOAM_INSTALLING', TRUE);
}

require_once ('../hoam-init.php');

session_start ();

function install_clean ($string) {
	$string = addslashes ($string);
	return ($string);
}

if (isset ($_SERVER['HTTP_REFERER']) AND ($_SERVER['HTTP_REFERER'] == ("http://" . $_SERVER['SERVER_NAME'] . '/install.php'))) {
	if (isset ($_SESSION['install_information'])) {
		unset ($_SESSION['install_information']);
	}
	// Get rid of any old errors, and start over.
	if (isset ($_SESSION['error_class_item'])) {
		unset ($_SESSION['error_class_item']);
	}

	$_SESSION['install_information'] = array ();
	$_SESSION['error_class_item'] = new HOAM_errors ('errors');

	$_SESSION['install_information']['trusted_config-hoam-db_install_type'] = install_clean ($_POST['config-hoam-db_install_type']);
	if ($_SESSION['install_information']['trusted_config-hoam-db_install_type'] == ucfirst ($_HOAM_language ['common']['automatic'])) {
		// OK, we're going to do an automatic install. Let's pick a few names
		$_SESSION['install_information']['new-db_name'] = 'HOAM_' . date ('Ymd');
		$_SESSION['install_information']['new-db_user'] = 'HOAM_' . date ('Ymd');
		$_SESSION['install_information']['new-db_password'] = substr (str_shuffle (password_hash (md5 (time ()), PASSWORD_DEFAULT)), 0, 16);
		$_SESSION['install_information']['trusted_config-hoam-db_server'] = 'localhost';
		$_SESSION['install_information']['trusted_config-hoam-db_port'] = '';

		$_SESSION['install_information']['trusted_config-hoam-db_root_user'] = install_clean ($_POST['config-hoam-db_root_user']);
		// Make sure the name isn't too short.
		if (mb_strlen ($_SESSION['install_information']['trusted_config-hoam-db_root_user']) < 1) {
			// The username is too short.
			$_SESSION ['error_class_item'] -> addError ('config-hoam-db_root_user', sprintf ($_HOAM_language ['errors']['config']['hoam']['db_user']['too-short'], 1));
		} elseif (mb_strlen ($_SESSION['install_information']['trusted_config-hoam-db_root_user']) > 16) {
			// The username is too long.
			$_SESSION ['error_class_item'] -> addError ('config-hoam-db_root_user', sprintf ($_HOAM_language ['errors']['config']['hoam']['db_user']['too-long'], 16));
		}
		$_SESSION['install_information']['trusted_config-hoam-db_root_password'] = install_clean ($_POST['config-hoam-db_root_password']);
		// It's not good practice, but it's possible someone may be using a
		// blank root password for their DB install. Just accept what we've been
		// given.
	} else {
		// OK, we're going to do a manual install. Parse the information
		// we've been provided
		$_SESSION['install_information']['trusted_config-hoam-db_user'] = install_clean ($_POST['config-hoam-db_user']);
		// Make sure the name isn't too short.
		if (mb_strlen ($_SESSION['install_information']['trusted_config-hoam-db_user']) < 1) {
			// The username is too short.
			$_SESSION ['error_class_item'] -> addError ('config-hoam-db_user', sprintf ($_HOAM_language ['errors']['config']['hoam']['db_user']['too-short'], 1));
		} elseif (mb_strlen ($_SESSION['install_information']['trusted_config-hoam-db_user']) > 16) {
			// The username is too long.
			$_SESSION ['error_class_item'] -> addError ('config-hoam-db_user', sprintf ($_HOAM_language ['errors']['config']['hoam']['db_user']['too-long'], 16));
		}

		$_SESSION['install_information']['trusted_config-hoam-db_password'] = install_clean ($_POST['config-hoam-db_password']);
		// Make sure the name isn't too short.
		if (mb_strlen ($_SESSION['install_information']['trusted_config-hoam-db_password']) < 1) {
			// The subject is too short.
			$_SESSION ['error_class_item'] -> addError ('config-hoam-db_password', sprintf ($_HOAM_language ['errors']['config']['hoam']['db_password']['too-short'], 1));
		}
	
		$_SESSION['install_information']['trusted_config-hoam-db_name'] = install_clean ($_POST['config-hoam-db_name']);
		// Make sure the name isn't too short.
		if (mb_strlen ($_SESSION['install_information']['trusted_config-hoam-db_name']) < 1) {
			// The name is too short.
			$_SESSION ['error_class_item'] -> addError ('config-hoam-db_name', sprintf ($_HOAM_language ['errors']['config']['hoam']['db_name']['too-short'], 1));
		}
		$_SESSION['install_information']['trusted_config-hoam-db_server'] = install_clean ($_POST['config-hoam-db_server']);
		// Make sure the name isn't too short.
		if (mb_strlen ($_SESSION['install_information']['trusted_config-hoam-db_server']) < 1) {
			// The name is too short.
			$_SESSION ['error_class_item'] -> addError ('config-hoam-db_server', sprintf ($_HOAM_language ['errors']['config']['hoam']['db_server']['too-short'], 1));
		}
		$_SESSION['install_information']['trusted_config-hoam-db_port'] = (int) install_clean ($_POST['config-hoam-db_port']);
	}

	// Now, try and use the values we've been given
	// Somewhere we can store a list of all the queries we ran for reporting.
	$_SESSION['install_information']['query_list'] = array ();
	if ($_SESSION['install_information']['trusted_config-hoam-db_install_type'] == ucfirst ($_HOAM_language ['common']['automatic'])) {
		$connection = mysqli_connect (NULL, $_SESSION['install_information']['trusted_config-hoam-db_root_user'], $_SESSION['install_information']['trusted_config-hoam-db_root_password']);
		if (mysqli_connect_errno()) {
			$_SESSION ['error_class_item'] -> addError ('config-hoam-db_root_password', sprintf ($_HOAM_language ['errors']['hoam']['database']['connect'], mysqli_connect_error()));
		}
		$db = mysqli_select_db ($connection, $_SESSION['install_information']['new-db_name']);
		if ($db) {
			// The database already exists, don't overwrite just in case.
			// Update the name we're using
			$i = 0;
			do {
				$i++;
				$db_name = $_SESSION['install_information']['new-db_name'] . '_' . $i;
				$db = mysqli_select_db ($connection, $db_name);
			} while ($db AND ($i < 11)); 
			if ($i < 11) {
				$_SESSION['install_information']['new-db_name'] = $db_name;
				$query = 'CREATE DATABASE ' . $_SESSION['install_information']['new-db_name'];
				array_push ($_SESSION['install_information']['query_list'], $query);
				$result = mysqli_query ($connection, $query);
				// Always check that $result is not an error
				if (!$result) {
					$_SESSION ['error_class_item'] -> addError ('config-hoam-db_root_password', $_HOAM_language ['errors']['hoam']['database']['creating']);
				} 
			} else {
				$_SESSION ['error_class_item'] -> addError ('config-hoam-db_root_password', $_HOAM_language ['errors']['hoam']['database']['creating']);
			}
		} else {
			// OK, the database we want to create does NOT exist, therefore
			// it's safe to create. Let's do that.
			$query = 'CREATE DATABASE ' . $_SESSION['install_information']['new-db_name'];
			array_push ($_SESSION['install_information']['query_list'], $query);
			$result = mysqli_query ($connection, $query);
			// Always check that $result is not an error
			if (!$result) {
				echo $_HOAM_language ['errors']['hoam']['database']['problem'];
				$_SESSION ['error_class_item'] -> addError ('config-hoam-db_root_password', mysqli_error($connection));
			}
		}
		// Assuming there have been no errors so far, let's proceed.
		if (!$_SESSION ['error_class_item'] -> showCategory ('config-hoam-db_root_password')) {
			// Due to a bug in MySQL, we're first going to drop any
			// existing user, then Flush privileges.
			$query = 'DROP USER "'. $_SESSION['install_information']['new-db_user'] . '"@"' . $_SESSION['install_information']['trusted_config-hoam-db_server'] . '"';
			array_push ($_SESSION['install_information']['query_list'], $query);
			mysqli_query ($connection, $query);
			$query = 'FLUSH PRIVILEGES';
			array_push ($_SESSION['install_information']['query_list'], $query);
			$result = mysqli_query ($connection, $query);
			if (!$result) {
				echo $_HOAM_language ['errors']['hoam']['database']['problem'];
				$_SESSION ['error_class_item'] -> addError ('config-hoam-db_root_password', mysqli_error($connection));
			} else {
				// Create the new user account
				$query = 'CREATE USER "'. $_SESSION['install_information']['new-db_user'] . '"@"' . $_SESSION['install_information']['trusted_config-hoam-db_server'] . '" IDENTIFIED BY "' . $_SESSION['install_information']['new-db_password'] . '"';
				array_push ($_SESSION['install_information']['query_list'], $query);
				$result = mysqli_query ($connection, $query);
				// Always check that $result is not an error
				if (!$result) {
					echo $_HOAM_language ['errors']['hoam']['database']['problem'];
					$_SESSION ['error_class_item'] -> addError ('config-hoam-db_root_password', mysqli_error($connection));
				} else {
					// Grant only the permissions necessary
					$query = 'GRANT create temporary tables, delete, drop, execute, insert, select, update on ' . $_SESSION['install_information']['new-db_name'] . '.* to "' . $_SESSION['install_information']['new-db_user'] . '"@"' . $_SESSION['install_information']['trusted_config-hoam-db_server'] . '"';
					array_push ($_SESSION['install_information']['query_list'], $query);
					$result = mysqli_query ($connection, $query);
					// Always check that $result is not an error
					if (!$result) {
						echo $_HOAM_language ['errors']['hoam']['database']['problem'];
						$_SESSION ['error_class_item'] -> addError ('config-hoam-db_root_password', mysqli_error($connection));
					}
				}
			}
		}
	} else {
		// Just connect to the database using the credentials and information
		// given.
		if ($_SESSION['install_information']['trusted_config-hoam-db_port']) {
			$server = $_SESSION['install_information']['trusted_config-hoam-db_server'] . ':' . $_SESSION['install_information']['trusted_config-hoam-db_port'];
		} else {
			$server = $_SESSION['install_information']['trusted_config-hoam-db_server'];
		}
		$connection = mysqli_connect ($server, $_SESSION['install_information']['trusted_config-hoam-db_user'], $_SESSION['install_information']['trusted_config-hoam-db_password']);
		if (mysqli_connect_errno()) {
			$_SESSION ['error_class_item'] -> addError ('config-hoam-db_password', sprintf ($_HOAM_language ['errors']['hoam']['database']['connect'], mysqli_connect_error()));
		}
		$db = mysqli_select_db ($connection, $_SESSION['install_information']['trusted_config-hoam-db_name']);
		if (!$db) {
			$_SESSION ['error_class_item'] -> addError ('config-hoam-db_password', sprintf ($_HOAM_language ['errors']['hoam']['database']['connect'], mysqli_connect_error()));
		}
	}		

	// Are there any errors that the user needs to fix before committing?
	if ($_SESSION['error_class_item'] -> errorCount > 0) {
		mysqli_close ($connection);
		header ('location: /install.php');
		exit ();
	} else {
		// Make sure we can connect to the database server
		// Make sure the selected database name doesn't already exist.
		// If an automatic install, make sure that the new username doesn't
		// already exist.

		// Finally! We have good data and can start creating our .ini
		if ($_SESSION['install_information']['trusted_config-hoam-db_install_type'] == ucfirst ($_HOAM_language ['common']['automatic'])) {
			$ini_data = array (
				'HOAM_DB' => array (
					'dbname' => $_SESSION['install_information']['new-db_name'],
					'password' => $_SESSION['install_information']['new-db_password'],
					'port' => '',
					'server' => $_SESSION['install_information']['trusted_config-hoam-db_server'],
					'user' => $_SESSION['install_information']['new-db_user']));
		} else {
			$ini_data = array (
				'HOAM_DB' => array (
					'dbname' => $_SESSION['install_information']['trusted_config-hoam-db_name'],
					'password' => $_SESSION['install_information']['trusted_config-hoam-db_password'],
					'port' => $_SESSION['install_information']['trusted_config-hoam-db_port'],
					'server' => $_SESSION['install_information']['trusted_config-hoam-db_server'],
					'user' => $_SESSION['install_information']['trusted_config-hoam-db_user']));
		}
		if (HOAM_writeIni ($ini_data, HOAM_PATH . 'hoam-config.ini', TRUE)) {
			// OK, everything worked out, we can continue with the install and
			// import the initial database.
			// NOTE! We're using the root user here because it's possible the
			// user account created doesn't have sufficient permissions to
			// create new tables, etc.
			define ('HOAM_DB_NAME', $ini_data['HOAM_DB']['dbname']);
			define ('HOAM_DB_USER', $_SESSION['install_information']['trusted_config-hoam-db_root_user']);
			define ('HOAM_DB_PASSWORD', $_SESSION['install_information']['trusted_config-hoam-db_root_password']);
			define ('HOAM_DB_SERVER', $ini_data['HOAM_DB']['server']);
			define ('HOAM_DB_PORT', $ini_data['HOAM_DB']['port']);
			unset ($ini_data);
		} else {
			$_SESSION ['error_class_item'] -> addError ('config-hoam-ini', $_HOAM_language ['errors']['hoam']['installation']['file-not-writeable'] . HOAM_PATH . 'hoam-config.ini');
			header ('location: /install.php');
			exit;
		}

			$db = new HOAM_db ();
			$status = $db -> import (HOAM_PATH . 'hoam/install/default_db.sql');
			if ($status == FALSE) {
				header ('Location: /error/installation');
				exit ();
			}
			
/*			$status = $db -> import (HOAM_PATH . 'hoam/install/storedProcedures.sql', '$$');
			if ($status == FALSE) {
				header ('Location: /error/installation');
				exit ();
			}
*/
			// I _CANNOT_ for the life of me get stored procedures to import
			// cleanly despite weeks of testing and googling. Giving up and
			// just creating them directly.
			$procedures = array ();
			array_push ($procedures, "CREATE PROCEDURE HOAM_attachmentList (id CHAR(32), num SMALLINT UNSIGNED)
							BEGIN

							CREATE TEMPORARY TABLE attachment_table (sequence SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT, attachment_id CHAR(32), PRIMARY KEY (sequence));

							IF id = '' THEN
								INSERT INTO attachment_table (attachment_id)
									SELECT attachments.id FROM attachments
									ORDER BY attachments.datecreated DESC LIMIT 250;
							ELSE
								INSERT INTO attachment_table (attachment_id)
									SELECT attachments.id FROM attachments
									WHERE attachments.datecreated <= (SELECT attachments.datecreated FROM attachments WHERE attachments.id = id)
									ORDER BY attachments.datecreated DESC LIMIT 250;
							END IF;

							SET @i = 0;
							SELECT sequence INTO @i FROM attachment_table WHERE attachment_id = id;

							/* Using 'LIMIT num' instead of relying on sequence <= num can't be used until
							at least MySQL 5.5.6 */
							SELECT attachment_id FROM attachment_table WHERE sequence > @i AND sequence <= (@i + num);

							DROP TABLE attachment_table;

							END;");

			if (!empty ($procedures)) {
				foreach ($procedures as $query) {
					array_push ($_SESSION['install_information']['query_list'], $query);
					$result = $db -> runQuery ($query, TRUE);
				}
			}

		// Last bit of cleanup
		$query = "FLUSH PRIVILEGES";
		array_push ($_SESSION['install_information']['query_list'], $query);
		$result = $db -> runQuery ($query);

		require_once (HOAM_PATH_CLASS . 'log.php');
		$log_class = new HOAM_log ('log');
		$i = 0;
		foreach ($_SESSION['install_information']['query_list'] as $query) {
			$i++;
			$log_class -> add (sprintf ($_HOAM_language ['log']['install']['query'], $i, $query), HOAM_MESSAGE_DEBUG);
		}
		
		// Clear the variables since everything worked OK.
		if (isset ($_SESSION['error_class_item'])) {
			unset ($_SESSION['error_class_item']);
		}
		if (isset ($_SESSION['install_information'])) {
			unset ($_SESSION['install_information']);
		}

		// Put some code in here to delete the install page (install.php) so it
		// can't be called remotely

		mysqli_close ($connection);
		header ('Location: /');
		exit ();
	}

} else {
	// We don't know what action we're being asked to perform
	header ('Location: /error/installation/');
	exit ();
}

?>
