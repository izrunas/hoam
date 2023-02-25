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

class HOAM_log extends HOAM_db {

	function __construct ($table) {
		parent::__construct ($table);
	}

	/* We need to capture:
		1) Sequence?
		2) Date & time
		3) Message type: Emergency, Alert, Critical, Error, Warning, Notice,
		   Informational, Debug. 
		4) Message Text
		5) Associated IP source?
		6) Associated ID?
		7) Flags?
	*/
	
	function add ($message, $level = HOAM_MESSAGE_INFORMATIONAL, $options = array ()) {
		global $_HOAM_language, $_HOAM_setting;

		// Should have been created if not passed, but for some reason isn't.
		if (!is_array($options)) {
			$options = array ();
		}

		if ((isset ($options['clean'])) AND ($options['clean'] === FALSE)) {
        	$message = $this -> Clean ($message);
    	} else {
    		$message = $this -> superClean ($message);
    	}
		if (!isset ($options['flags'])) {
        	$options['flags'] = 0;
    	}
		if (!isset ($options['owner_id'])) {
        	$options['owner_id'] = NULL;
    	}
		if (!isset ($options['user_id'])) {
    		if (isset ($_SESSION['current_user']['id'])) {
    			$options['user_id'] = $_SESSION['current_user']['id'];
    		} else {
        		$options['user_id'] = $_HOAM_setting ['user']['ids']['system'];
			}
    	}
		if (!isset ($options['source_ip'])) {
			if (isset ($_SERVER['REMOTE_ADDR'])) {
        		$options['source_ip'] = $_SERVER['REMOTE_ADDR'];
			} else {
				// if an IP isn't defined, then the only logical explaination
				// is the script running via some CLI interface.
				$options['source_ip'] = strtoupper (PHP_SAPI);
			}
    	}

		// Generate an id for this log message.
		$id = md5 (uniqid (mt_rand (), TRUE));

    	if (!isset ($_HOAM_setting)) {
        	// This should only NOT be set if either 1) HOAM failed to start and
	        // the setting couldn't be read, or someone cleared the logging
	        // options. We want to make sure we still force logging somehow, so
	        // we dump to a file
	        $_HOAM_setting ['log']['destinations'] = HOAM_LOG_FILE;
    	    $_HOAM_setting ['log']['destination']['file'] = HOAM_PATH . 'HOAM_error_' . rand (1000, 9999) . '.log';
        	$_HOAM_setting ['log']['levels'] = HOAM_MESSAGE_EMERGENCY;
	    }
    	// Check that we should log this message
	    if (((int) $_HOAM_setting ['log']['levels'] & (int) $level) OR
			HOAM_DEBUG) {
			// Write the log to the requested destinations
			// OR, if HOAM_DEBUG is TRUE, write everything regardless whether
			// logging for that alert level is enabled.
			if (((int) $_HOAM_setting ['log']['destinations'] & (int) HOAM_LOG_DB) OR
    			HOAM_DEBUG) {
//				$statement = mysqli_prepare ($this -> connection, "INSERT INTO $this->table (id, datecreated, level, message, source_ip, owner_id, user_id, flags) VALUES (?, now(), ?, ?, ?, ?, ?, ?)");
//				mysqli_stmt_bind_param ($statement, "sissssi", $id, $level, $message, $options['source_ip'], $options['owner_id'], $options['user_id'], $options['flags']);

				// execute query:
//				$result = mysqli_stmt_execute ($statement);
//				mysqli_stmt_close ($statement);
		
		    	$query = "INSERT INTO $this->table (id, datecreated, level, message, source_ip, owner_id, user_id, flags)
		    	        VALUES ('$id', now(), '$level', '$message', '" . $options['source_ip'] . "', '" . $options['owner_id'] . "', '" . $options['user_id'] . "', '" . $options['flags'] . "')";
				// Execute the query
				$result = mysqli_query ($this -> connection, $query);
				// Always check that $result is not an error
				if (!$result) {
					echo $_HOAM_language ['errors']['hoam']['database']['problem'];
					if (HOAM_DEBUG) {
						printf ($_HOAM_language ['errors']['hoam']['database']['error'], mysqli_error ($this -> connection));
						printf ($_HOAM_language ['errors']['hoam']['database']['query'], $query);
						$backtrace = debug_backtrace();
						printf ('Backtrace Functions :: %s &rArr; %s &rArr; %s<br />', $backtrace[2]['function'], $backtrace[1]['function'], $backtrace[0]['function']);
						printf ('Backtrace File :: %s<br />', $backtrace[0]['file']);
						printf ('Backtrace Line :: %d<br />', $backtrace[0]['line']);
					} else {
						echo $_HOAM_language ['errors']['hoam']['debugging'];
					}
					echo $_HOAM_language ['errors']['hoam']['contact-support'];
					exit ();
				}
    		}
	    	if ((int) $_HOAM_setting ['log']['destinations'] & (int) HOAM_LOG_FILE) {
    	    	$file_str = date ('r') . " $level $source_ip $message $user_id";
	        	$handle = fopen ($_HOAM_setting ['log']['destination']['file'], 'a+');
		        if ($handle) {
    		        fwrite ($handle, $file_str);
        		    fclose ($handle);
		        } else {
    		        echo $_HOAM_language ['errors']['hoam']['configuration']['problem'];
        		    if (HOAM_DEBUG) {
            		    printf ($_HOAM_language ['errors']['hoam']['log']['file'], $_HOAM_setting ['log']['destination']['file']);
						$backtrace = debug_backtrace();
						printf ('Backtrace Functions :: %s &rArr; %s &rArr; %s<br />', $backtrace[2]['function'], $backtrace[1]['function'], $backtrace[0]['function']);
						printf ('Backtrace File :: %s<br />', $backtrace[0]['file']);
						printf ('Backtrace Line :: %d<br />', $backtrace[0]['line']);
		            } else {
    		            echo $_HOAM_language ['errors']['hoam']['debugging'];
        		    }
            		echo $_HOAM_language ['errors']['hoam']['contact-support'];
	            	exit ();
	    	    }
		    }
    		if ((int) $_HOAM_setting ['log']['destinations'] & (int) HOAM_LOG_SYSLOG) {
	        	// There really needs to be a way to select the syslog
	        	// destination, but it doesn't look like the default syslog()
	        	// command allows it.
    	    	syslog ($level, "$source_ip $message $user_id");
    		}
	    }
    	return ($id);
	}

	function changeOwnerId ($id, $value) {
		return ($this -> changeString ($id, 'owner_id', $value));
	}

	function clearBit ($id, $value, $column = "flags") {
		// IMHO, mysql handling of bit comparisons sucks. It may work fine in
		// some circumstances, but for what I want it's broken. So, we're just
		// going to do it in PHP.
		$bits = $this -> itemQuery ("SELECT $column FROM $this->table WHERE id='$id'");
		if ((int) $bits & (int) $value) {
			return ($this -> changeInt ($id, $column, $bits - $value));
		} else {
			// The flag was not set, nothing to change
			return (-1);
		}
	}
	
	function findGaps () {
		// Identify any gaps in the system logs (eg, did someone try to delete evidence?)
		// This obsoletes the old method using the HOAM_fillSequence stored procedure
		// From https://stackoverflow.com/a/29736658/760851
		$query = "SELECT CONCAT (z.expected, IF (z.got - 1 > z.expected, CONCAT(' %s ', z.got - 1), '')) AS missing
					FROM (SELECT
							@rownum:=@rownum + 1 AS expected,
							IF (@rownum=sequence, 0, @rownum:=sequence) AS got FROM
							(SELECT
								@rownum:=0) AS a
								JOIN $this->table
								ORDER BY sequence)
							AS z
						WHERE z.got != 0";
		return ($this -> listSimpleQuery ($query));
	}

	function findMessage ($message, $flags = NULL) {
		// Identify any gaps in the system logs (eg, did someone try to delete evidence?)
		// This obsoletes the old method using the HOAM_fillSequence stored procedure
		// From https://stackoverflow.com/a/29736658/760851
		$query = "SELECT id FROM $this->table WHERE message='$message'";
		if ($flags !== NULL) {
			$query .= " AND flags & $flags";
		}
		$query .= " ORDER BY datecreated DESC";
		return ($this -> listSimpleQuery ($query));
	}

	function listBitSet ($bit, $column = 'flags', $number = 0, $offset = 0, $sort = 'sequence DESC') {
		$query = "SELECT id FROM $this->table
					WHERE $column & $bit
					ORDER BY $sort";
		if ($number > 0) {
			$query .= " LIMIT $offset, $number";
		}
		return ($this -> listSimpleQuery ($query));
	}

	function listOwnerBit ($owner, $bit, $column = 'flags', $number = 0, $offset = 0, $sort = 'sequence DESC') {
		$query = "SELECT id FROM $this->table
					WHERE owner_id = '$owner' AND
					$column & $bit
					ORDER BY $sort";
		if ($number > 0) {
			$query .= " LIMIT $offset, $number";
		}
		return ($this -> listSimpleQuery ($query));
	}

	function returnDateCreated ($id) {
		return ($this -> returnItem ($id, 'datecreated'));
	}
	
	function returnMessage ($id) {
		return ($this -> returnItem ($id, 'message'));
	}

	function returnOwnerId ($id) {
		return ($this -> returnItem ($id, 'owner_id'));
	}

	function returnSequence ($id) {
		if ($id === 0) {
			return (0);
		}
		return ($this -> returnItem ($id, 'sequence'));
	}

	function setBit ($id, $value, $column = "flags") {
		// IMHO, mysql handling of bit comparisons sucks. It may work fine in
		// some circumstances, but for what I want it's broken. So, we're just
		// going to do it in PHP.
		$bits = $this -> itemQuery ("SELECT $column FROM $this->table WHERE id='$id'");
		if ((int) $bits & (int) $value) {
			// The flag is already set
			return (-1);
		} else {
			return ($this -> changeInt ($id, $column, $bits + $value));
		}
	}
	
}
?>
