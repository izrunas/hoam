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

function HOAM_upgradeCopyFiles ($file_list) {
	global $_HOAM_language, $_HOAM_log, $_HOAM_setting;
	if (is_array ($file_list)) {
		foreach ($file_list as $file) {
			if (file_exists (HOAM_PATH . 'upgrade/' . $file['source'])) {
				if (!file_exists (dirname (HOAM_PATH . $file['destination']))) {
					$success = mkdir (dirname (HOAM_PATH . $file['destination']), 0750, TRUE);
					if ($success) {
						$_HOAM_log -> add ('Created directory "' . dirname (HOAM_PATH . $file['destination']) . '".', HOAM_MESSAGE_NOTICE, array ('flags' => $_HOAM_setting ['log']['flags']['upgrade']));
					} else {
						$_HOAM_log -> add ('Could not create directory "' . dirname (HOAM_PATH . $file['destination']) . '".', HOAM_MESSAGE_ERROR, array ('flags' => $_HOAM_setting ['log']['flags']['upgrade']));
						$_HOAM_log -> add ('Error message: "' . error_get_last () . '".', HOAM_MESSAGE_ERROR, array ('flags' => $_HOAM_setting ['log']['flags']['upgrade']));
						return FALSE;
					}
				}
				if (md5_file (HOAM_PATH . 'upgrade/' . $file['source']) == $file['md5']) {
					$_HOAM_log -> add ('MD5 signature matched for "' . $file['source'] . '".', HOAM_MESSAGE_DEBUG, array ('flags' => $_HOAM_setting ['log']['flags']['upgrade']));
					if (is_writeable (HOAM_PATH . dirname ($file['destination']))) {
						$success = copy (HOAM_PATH . 'upgrade/' . $file['source'], HOAM_PATH . $file['destination']);
						if ($success) {
							$_HOAM_log -> add ('Copied file "' . $file['source'] . '" to "' . $file['destination'] . '".', HOAM_MESSAGE_INFORMATIONAL, array ('flags' => $_HOAM_setting ['log']['flags']['upgrade']));
						} else {
							$_HOAM_log -> add ('Failed copying file "' . $file['source'] . '" to "' . $file['destination'] . '".', HOAM_MESSAGE_ERROR, array ('flags' => $_HOAM_setting ['log']['flags']['upgrade']));
							$error = error_get_last ();
							$_HOAM_log -> add ('Error message: "' . $error['message'] . '".', HOAM_MESSAGE_ERROR, array ('flags' => $_HOAM_setting ['log']['flags']['upgrade']));
							return FALSE;
						}
					} else {
						$_HOAM_log -> add ('Destination "' . HOAM_PATH . dirname ($file['destination']) . '" for file "' . basename ($file['0']['source']) . '" is not writeable,', HOAM_MESSAGE_ERROR, array ('flags' => $_HOAM_setting ['log']['flags']['upgrade']));
						return FALSE;
					}
				} else {
					$_HOAM_log -> add ('MD% signature check failed for "' . $file['source'] . '".', HOAM_MESSAGE_ERROR, array ('flags' => $_HOAM_setting ['log']['flags']['upgrade']));
					return FALSE;
				}
			} else {
				$_HOAM_log -> add ('File "' . $file['source'] . '" is missing from the upgrade.', HOAM_MESSAGE_ERROR, array ('flags' => $_HOAM_setting ['log']['flags']['upgrade']));
				return FALSE;
			}
		}
		return TRUE;
	} else {
		$_HOAM_log -> add ('No files to copy during upgrade.', HOAM_MESSAGE_INFORMATIONAL, array ('flags' => $_HOAM_setting ['log']['flags']['upgrade']));
		return TRUE;
	}
}

function HOAM_upgradeDeleteFiles ($file_list) {
	// It's not as critical that files are deleted, so we'll just warn if we
	// can't delete them instead of generating an error..
	global $_HOAM_language, $_HOAM_log, $_HOAM_setting;
	if (is_array ($file_list)) {
		foreach ($file_list as $file) {
			$_HOAM_log -> add ('Attempting to delete File "'. HOAM_PATH . $file['name'] . '" during upgrade.', HOAM_MESSAGE_DEBUG, array ('flags' => $_HOAM_setting ['log']['flags']['upgrade']));
			if (file_exists (HOAM_PATH . $file['name'])) {
				if (is_file (HOAM_PATH . $file['name'])) {
					$success = unlink (HOAM_PATH . $file['name']);
					if ($success) {
						$_HOAM_log -> add ('File "'. HOAM_PATH . $file['name'] . '" successfully deleted during upgrade.', HOAM_MESSAGE_INFORMATIONAL, array ('flags' => $_HOAM_setting ['log']['flags']['upgrade']));
					} else {
						$_HOAM_log -> add ('File "'. HOAM_PATH . $file['name'] . '" could not be deleted during upgrade.', HOAM_MESSAGE_WARNING, array ('flags' => $_HOAM_setting ['log']['flags']['upgrade']));
					}
				} else {
					$success = rmdir (HOAM_PATH . $file['name']);
					if ($success) {
						$_HOAM_log -> add ('Directory "'. HOAM_PATH . $file['name'] . '" successfully deleted during upgrade.', HOAM_MESSAGE_INFORMATIONAL, array ('flags' => $_HOAM_setting ['log']['flags']['upgrade']));
					} else {
						$_HOAM_log -> add ('Directory "'. HOAM_PATH . $file['name'] . '" could not be deleted during upgrade.', HOAM_MESSAGE_WARNING, array ('flags' => $_HOAM_setting ['log']['flags']['upgrade']));
					}
				}
			} else {
				$_HOAM_log -> add ('File "'. HOAM_PATH . $file['name'] . '" was not found, could not be deleted during upgrade.', HOAM_MESSAGE_WARNING, array ('flags' => $_HOAM_setting ['log']['flags']['upgrade']));
			}
		}
	} else {
		$_HOAM_log -> add ('No files to delete during upgrade.', HOAM_MESSAGE_INFORMATIONAL, array ('flags' => $_HOAM_setting ['log']['flags']['upgrade']));
		return TRUE;
	}
	$_HOAM_log -> add ('Completed deleting specified files and directories during upgrade.', HOAM_MESSAGE_INFORMATIONAL, array ('flags' => $_HOAM_setting ['log']['flags']['upgrade']));
	return TRUE;
}

function HOAM_upgradeDownload ($url = '') {
	global $_HOAM_language, $_HOAM_log, $_HOAM_setting;

	if (empty ($url)) {
		$success = file_get_contents ("https://hoam.arprs.com/installs/version.php?upgrade=" . HOAM_VERSION);
		$xml = simplexml_load_string ($success);
		if (gettype ($xml) == 'object') {
			$url = $xml -> url;
			$_HOAM_log -> add ('Recommended upgrade package for HOAM ' . HOAM_VERSION . ' is "' . $url . '"', HOAM_MESSAGE_INFORMATIONAL, array ('flags' => $_HOAM_setting ['log']['flags']['upgrade']));
		} else {
			return FALSE;
		}
	} else {
		$_HOAM_log -> add ('Downloading custom upgrade package "' . $url . '".', HOAM_MESSAGE_WARNING, array ('flags' => $_HOAM_setting ['log']['flags']['upgrade']));
	}
	// Inintialize directory name where file will be saved
	$dir = HOAM_PATH . 'upgrade/';
	if (!file_exists ($dir)) {
		$success = mkdir ($dir, 0750);
		if ($success) {
			$_HOAM_log -> add ('Created directory "' . $dir . '".', HOAM_MESSAGE_NOTICE, array ('flags' => $_HOAM_setting ['log']['flags']['upgrade']));
		} else {
			$_HOAM_log -> add ('Could not create directory "' . $dir . '"', HOAM_MESSAGE_ERROR, array ('flags' => $_HOAM_setting ['log']['flags']['upgrade']));
			return FALSE;
		}
	} else {
		$_HOAM_log -> add ('Directory "' . $dir . '" already exists.', HOAM_MESSAGE_INFORMATIONAL, array ('flags' => $_HOAM_setting ['log']['flags']['upgrade']));
	}
	if (!is_writeable ($dir)) {
		$success = chmod ($dir, 0750);
		if (!$success) {
			$_HOAM_log -> add ('Could not write to directory "' . $dir . '"', HOAM_MESSAGE_ERROR, array ('flags' => $_HOAM_setting ['log']['flags']['upgrade']));
			return FALSE;
		}
	}
	// Get just the filename
	$filename = basename ($url);
	// Save file into file location
	$save_file_loc = $dir . $filename;

	// Open file
	$fp = fopen ($save_file_loc, 'wb');
	if (!$fp) {
		$_HOAM_log -> add ('Could not create file "' . $dir . $filename . '"', HOAM_MESSAGE_ERROR, array ('flags' => $_HOAM_setting ['log']['flags']['upgrade']));
		return FALSE;
	}
	// Initialize and set options for the cURL session
	$ch = curl_init ($url);
	curl_setopt ($ch, CURLOPT_FILE, $fp);
	curl_setopt ($ch, CURLOPT_HEADER, 0);

	// Perform a cURL session and close
	curl_exec ($ch);
	curl_close ($ch);

	// Close file
	$success = fclose ($fp);
	if ($success) {
		$_HOAM_log -> add ('Successfully downloaded upgrade package "' . $filename . '"', HOAM_MESSAGE_INFORMATIONAL, array ('flags' => $_HOAM_setting ['log']['flags']['upgrade']));
		return $filename;
	} else {
		$_HOAM_log -> add ('Failed to downloaded upgrade package "' . $filename . '"', HOAM_MESSAGE_ERROR, array ('flags' => $_HOAM_setting ['log']['flags']['upgrade']));
		return FALSE;
	}
	
}

function HOAM_upgradeEval ($code) {
	global $_HOAM_log, $_HOAM_setting;
	// So that we're not actually using the eval() command, we're going to dump
	// the provided code into a temporary file then include it.
	if (!empty ($code)) {
		// Protect against us accidentally including two sections of code with
		// the same name. 
		if (gettype ($code) != 'array') {
	    	$tmp = tmpfile ();
    		$tmpf = stream_get_meta_data ($tmp);
    		$tmpf = $tmpf ['uri'];
    		fwrite ($tmp, $code);
    		$ret = include ($tmpf);
    		fclose ($tmp);
			$_HOAM_log -> add ('Executed provided PHP code.', HOAM_MESSAGE_DEBUG, array ('flags' => $_HOAM_setting ['log']['flags']['upgrade']));
	    	return ($ret);
		} else {
			$_HOAM_log -> add ('Duplicate PHP code sections sent, not sure which to execute.', HOAM_MESSAGE_ERROR, array ('flags' => $_HOAM_setting ['log']['flags']['upgrade']));
			return FALSE;
		}
	} else {
		$_HOAM_log -> add ('No PHP code provided to execute.', HOAM_MESSAGE_DEBUG, array ('flags' => $_HOAM_setting ['log']['flags']['upgrade']));
		return TRUE;
	}
}

function HOAM_upgradeExport ($include_attachments = FALSE) {
	global $_HOAM_country, $_HOAM_language, $_HOAM_log, $_HOAM_setting;
	
	$dir = HOAM_PATH . 'backup/';
	if (!file_exists ($dir)) {
		mkdir ($dir);
	}
	if (!is_writeable ($dir)) {
		$_HOAM_log -> add ('Could not write to directory "'. $dir . '".', HOAM_MESSAGE_ERROR, array ('flags' => $_HOAM_setting ['log']['flags']['upgrade']));
		return FALSE;
	}
	$filename = 'HOAM_BACKUP_' . date ($_HOAM_country ['date']['format_mysql']);
	$output = array ();
	$return_var = 0;
	if (HOAM_DB_PORT) {
		exec ('mysqldump --opt --user="' . HOAM_DB_USER . '" --password="' . HOAM_DB_PASSWORD . '" --host="' . HOAM_DB_SERVER . '" -P ' . HOAM_DB_PORT . ' ' . HOAM_DB_NAME . ' > ' . $dir . $filename . '.sql', $output, $return_var);
	} else {
		exec ('mysqldump --opt --user="' . HOAM_DB_USER . '" --password="' . HOAM_DB_PASSWORD . '" ' . HOAM_DB_NAME . ' > ' . $dir . $filename . '.sql', $output, $return_var);
	}
	// mysqldump returns 0 on success, 1 for warning, 2 for not found
	if (!$return_var) {
		$_HOAM_log -> add ('Successfully dumped HOAM MySQL database backup.', HOAM_MESSAGE_INFORMATIONAL, array ('flags' => $_HOAM_setting ['log']['flags']['upgrade']));
		$zip = new ZipArchive ();
		if ($zip -> open ($dir . $filename . '.zip', ZipArchive::CREATE + ZipArchive::OVERWRITE) !== TRUE) {
			$_HOAM_log -> add ('Could not create ZIP archive "' . $dir . $filename . '.zip"', HOAM_MESSAGE_ERROR, array ('flags' => $_HOAM_setting ['log']['flags']['upgrade']));
    		return FALSE;
		}
		$success = $zip -> addFile ($dir . $filename . '.sql', 'backup/' . $filename . '.sql');
		if ($success) {
			$_HOAM_log -> add ('Added SQL backup "' . $dir . $filename . '.sql" to ZIP archive.', HOAM_MESSAGE_ERROR, array ('flags' => $_HOAM_setting ['log']['flags']['upgrade']));
		} else {
			$_HOAM_log -> add ('Failed to add SQL backup to ZIP archive.', HOAM_MESSAGE_ERROR, array ('flags' => $_HOAM_setting ['log']['flags']['upgrade']));
			return FALSE;
		}
		// Do we want to include attachments in the backup archive?
		if ($include_attachments === TRUE) {
			$_HOAM_log -> add ('Including attachments in backup.', HOAM_MESSAGE_INFORMATIONAL, array ('flags' => $_HOAM_setting ['log']['flags']['upgrade']));
			$objects = new RecursiveIteratorIterator (new RecursiveDirectoryIterator (HOAM_PATH_ATTACHMENT, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::SELF_FIRST);
			foreach ($objects as $name => $object){
				$attachment_path = $object -> getPath ();
				if (mb_strpos ($attachment_path, HOAM_PATH_ATTACHMENT_PREVIEW) === FALSE) {
					$_HOAM_log -> add ('Adding "' . $object -> getPathName () . '" to backup.', HOAM_MESSAGE_INFORMATIONAL, array ('flags' => $_HOAM_setting ['log']['flags']['upgrade']));
					if (is_file ($object -> getPathName ())) {
						// Keep only the part of the path under the directory HOAM is installed in, not the real path of the file
						$zip -> addFile ($object -> getPathName (), mb_substr ($object -> getPathName (), mb_strlen (HOAM_PATH)));
					} else {
						// Checking for directories that may be empty
						$attachment_dir = new FilesystemIterator ($object -> getPathName (), FilesystemIterator::SKIP_DOTS);
						if (iterator_count ($attachment_dir)) { 
							$zip -> addEmptyDir (mb_substr ($object -> getPathName (), mb_strlen (HOAM_PATH)));
						}
					}
				}
			}
		}
		$success = $zip -> close();
		if ($success) {
			// Go ahead and delete the uncompressed backup.
			$_HOAM_log -> add ('ZIP archive "' . $dir . $filename . '.zip" successfully created.', HOAM_MESSAGE_INFORMATIONAL, array ('flags' => $_HOAM_setting ['log']['flags']['upgrade']));
			unlink ($dir . $filename . '.sql');
			return ($filename . '.zip');
		}
	} else {
		$_HOAM_log -> add ('Failed to dump MySQL database for backup to create backup.', HOAM_MESSAGE_ERROR, array ('flags' => $_HOAM_setting ['log']['flags']['upgrade']));
		return FALSE;
	}
}

function HOAM_upgradeLoadScript ($file = '') {
	global $_HOAM_log, $_HOAM_setting;
	if (empty ($file)) {
		$file = HOAM_PATH . 'upgrade/upgrade.xml';
	}
	if (file_exists ($file)) {
		if (is_readable ($file)) {
			$xml = simplexml_load_file ($file, 'SimpleXMLElement', LIBXML_NOCDATA);
			if (gettype ($xml) == 'object') {
				$json = json_encode ($xml);
				$_HOAM_upgrade = json_decode ($json, TRUE);
				return ($_HOAM_upgrade);
			} else {
				$_HOAM_log -> add ('Could not import XML file "' . $file . '".', HOAM_MESSAGE_ERROR, array ('flags' => $_HOAM_setting ['log']['flags']['upgrade']));
				return FALSE;
			}
		} else {
			$_HOAM_log -> add ('Could not read upgrade file "' . $file . '".', HOAM_MESSAGE_ERROR, array ('flags' => $_HOAM_setting ['log']['flags']['upgrade']));
			return FALSE;
		} 
	} else {
		$_HOAM_log -> add ('File "' . $file . '" cannot be found.', HOAM_MESSAGE_ERROR, array ('flags' => $_HOAM_setting ['log']['flags']['upgrade']));
		return FALSE;
	}
}

function HOAM_upgradereturnChanges () {
	global $_HOAM_language;
	$changes = HOAM_upgradeLoadScript ();
	if ($changes) {
		return ($changes ['changes']);
	} else {
		return ($_HOAM_language ['errors']['generic']['problem_retrieving_data']);
	}
}

function HOAM_upgradereturnDetails () {
	global $_HOAM_language;
	$changes = HOAM_upgradeLoadScript ();
	if ($changes) {
		return ($changes ['details']);
	} else {
		return ($_HOAM_language ['errors']['generic']['problem_retrieving_data']);
	}
}

function HOAM_upgradereturnNotice () {
	global $_HOAM_language;
	$notice = HOAM_upgradeLoadScript ();
	if ($notice) {
		return ($notice ['notice']);
	} else {
		return ($_HOAM_language ['errors']['generic']['problem_retrieving_data']);
	}
}

function HOAM_upgradeSQL ($code) {
	global $_HOAM_log, $_HOAM_setting;
	if (!empty ($code)) {
		// Protect against us accidentally including two sections of code with
		// the same name. 
		if (gettype ($code) != 'array') {
			$success = $_HOAM_log -> runQuery ($code, TRUE);
			$_HOAM_log -> add ('Executed provided SQL code.', HOAM_MESSAGE_DEBUG, array ('flags' => $_HOAM_setting ['log']['flags']['upgrade']));
			return ($success);
		} else {
			$_HOAM_log -> add ('Duplicate SQL code sections sent, not sure which to execute.', HOAM_MESSAGE_ERROR, array ('flags' => $_HOAM_setting ['log']['flags']['upgrade']));
			return FALSE;
		}
	} else {
		$_HOAM_log -> add ('No SQL code provided to execute.', HOAM_MESSAGE_DEBUG, array ('flags' => $_HOAM_setting ['log']['flags']['upgrade']));
		return TRUE;
	}
}

function HOAM_upgradeUnzip ($file_to_decompress, $dest = '') {
	global $_HOAM_language, $_HOAM_log, $_HOAM_setting;
	if (empty ($dest)) {
		$dest = HOAM_PATH . 'upgrade/';
			$_HOAM_log -> add ('Assuming default extraction directory "' . $dest . '"', HOAM_MESSAGE_DEBUG, array ('flags' => $_HOAM_setting ['log']['flags']['upgrade']));
	}
	$zip = zip_open ($dest . $file_to_decompress);
	if (is_writeable ($dest)) {
		if (is_resource ($zip)) {
			$_HOAM_log -> add ('Decompressing ZIP archive "' . $file_to_decompress . '"', HOAM_MESSAGE_INFORMATIONAL, array ('flags' => $_HOAM_setting ['log']['flags']['upgrade']));
			$tree = '';
			while (($zip_entry = zip_read ($zip)) !== FALSE) {
				$_HOAM_log -> add ('Extracting file "' . zip_entry_name ($zip_entry) . '" from ZIP archive "' . $file_to_decompress . '"', HOAM_MESSAGE_DEBUG, array ('flags' => $_HOAM_setting ['log']['flags']['upgrade']));
				if (strpos (zip_entry_name ($zip_entry), DIRECTORY_SEPARATOR) !== FALSE) {
					$last = strrpos (zip_entry_name ($zip_entry), DIRECTORY_SEPARATOR);
					$dir = substr (zip_entry_name ($zip_entry), 0, $last);
					$file = substr (zip_entry_name ($zip_entry), strrpos (zip_entry_name ($zip_entry), DIRECTORY_SEPARATOR) + 1);
					if (!is_dir ($dest . $dir)) {
						if (!mkdir ($dest . $dir, 0750, TRUE)) {
							$_HOAM_log -> add ('Could not create directory "' . $dest . $dir . '"', HOAM_MESSAGE_ERROR, array ('flags' => $_HOAM_setting ['log']['flags']['upgrade']));
							return FALSE;
						}
					}
					if (strlen (trim ($file)) > 0) {
						$success = @file_put_contents ($dest . $dir . DIRECTORY_SEPARATOR . $file, zip_entry_read ($zip_entry, zip_entry_filesize ($zip_entry)));
						if ($success !== FALSE) {
							$_HOAM_log -> add ('File "' . $dest . zip_entry_name ($zip_entry) . '" written to disk (' . HOAM_humanFilesize ($success) . ')', HOAM_MESSAGE_DEBUG, array ('flags' => $_HOAM_setting ['log']['flags']['upgrade']));
						} else {
							$_HOAM_log -> add ('Could not write file "' . $dest . $dir . DIRECTORY_SEPARATOR . $file . '"', HOAM_MESSAGE_ERROR, array ('flags' => $_HOAM_setting ['log']['flags']['upgrade']));
							return FALSE;
						}
					}
				} else {
					$success = file_put_contents ($dest . zip_entry_name ($zip_entry), zip_entry_read ($zip_entry, zip_entry_filesize ($zip_entry)));
					if ($success !== FALSE) {
						$_HOAM_log -> add ('File "' . $dest . zip_entry_name ($zip_entry) . '" written to disk (' . HOAM_humanFilesize ($success) . ')', HOAM_MESSAGE_DEBUG, array ('flags' => $_HOAM_setting ['log']['flags']['upgrade']));
					} else {
						$_HOAM_log -> add ('Could not write file "' . $dest . zip_entry_name ($zip_entry) . '"', HOAM_MESSAGE_ERROR, array ('flags' => $_HOAM_setting ['log']['flags']['upgrade']));
					}
				}
			}
		} else {
			$_HOAM_log -> add ('Could not read file "' . $file . '"', HOAM_MESSAGE_ERROR, array ('flags' => $_HOAM_setting ['log']['flags']['upgrade']));
			return FALSE;
		}
	} else {
		$_HOAM_log -> add ('Could not write to "' . $dest . '"', HOAM_MESSAGE_ERROR, array ('flags' => $_HOAM_setting ['log']['flags']['upgrade']));
		return FALSE;
	}
	$_HOAM_log -> add ('Decompression complete for archive "' . $file_to_decompress . '"', HOAM_MESSAGE_INFORMATIONAL, array ('flags' => $_HOAM_setting ['log']['flags']['upgrade']));
	return TRUE;
}

?>
