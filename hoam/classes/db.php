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

class HOAM_db {
	// NOTE!
	// This class does NOT perform any cleansing of data (although it has a
	// method to do so).
	//
	// We rely upon the calling code to clean all of the data we're given.

	protected $connection;
	protected $table;

	function __construct ($table = FALSE) {
		global $_HOAM_language;
		// Try to connect with the port first, some installations don't work
		// if the port is specified (even if it's null).
		if (HOAM_DB_PORT) {
			$server = HOAM_DB_SERVER . ':' . HOAM_DB_PORT;
		} else {
			$server = HOAM_DB_SERVER;
		}
		$this -> connection = mysqli_connect ($server, HOAM_DB_USER, HOAM_DB_PASSWORD);
		if ($this -> connection) {
			$db = mysqli_select_db ($this -> connection, HOAM_DB_NAME);
			if (!$db) {
				echo $_HOAM_language ['errors']['hoam']['database']['problem'];
				if (HOAM_DEBUG) {
					echo $_HOAM_language ['errors']['hoam']['configuration']['database'];
					echo mysqli_error ($this -> connection);
				} else {
					echo $_HOAM_language ['errors']['hoam']['debugging'];
				}
				echo $_HOAM_language ['errors']['hoam']['contact-support'];
				exit ();
			}
		} else {
			echo $_HOAM_language ['errors']['hoam']['database']['problem'];
			if (HOAM_DEBUG) {
				echo $_HOAM_language ['errors']['hoam']['configuration']['database-server'];
				echo mysqli_error ($this -> connection);
			} else {
				echo $_HOAM_language ['errors']['hoam']['debugging'];
			}
			echo $_HOAM_language ['errors']['hoam']['contact-support'];
			exit ();
		}
			// The only time we don't want a specific table assigned is when
			// we're doing an install or upgrade.
		if ($table) {
			$this -> table = $table;
		}
		if (!mysqli_set_charset ($this -> connection, 'utf8')) {
			echo 'Error loading character set utf8: %s\n', mysqli_error ($this -> connection);
		} else {
			return;
		}
	}

	function __destruct () {
		mysqli_close ($this -> connection);
	}

	function changeFloat ($id, $column, $value) {
		// Change one column with the specified ID from the database.
		global $_HOAM_language;

		// Unavailable when installing.
		if (!$this -> table) die ($_HOAM_language ['errors']['hoam']['installation']['missing-table'] . $_HOAM_language ['errors']['hoam']['installation']['must-complete']);

		if ($value !== '') {
			$value = (float) $value;
			$statement = mysqli_prepare ($this -> connection, "UPDATE $this->table SET $column=? WHERE id=?");
			mysqli_stmt_bind_param ($statement, 'ds', $value, $id);
		} else {
			$statement = mysqli_prepare ($this -> connection, "UPDATE $this->table SET $column=0 WHERE id=?");
			mysqli_stmt_bind_param ($statement, 's', $id);
		}

		// execute query:
		$result = mysqli_stmt_execute ($statement);
		
		if (!$result) {
			echo $_HOAM_language ['errors']['hoam']['database']['problem'];
			if (HOAM_DEBUG) {
				printf ($_HOAM_language ['errors']['hoam']['database']['error'], $statement-> error);
				$backtrace = debug_backtrace();
				printf ('Backtrace Functions :: %s &rArr; %s &rArr; %s<br />', $backtrace[2]['function'], $backtrace[1]['function'], $backtrace[0]['function']);
				printf ('Backtrace File :: %s<br />', $backtrace[0]['file']);
				printf ('Backtrace Line :: %d<br />', $backtrace[0]['line']);
			} else {
				echo $_HOAM_language ['errors']['hoam']['debugging'];
			}
			echo $_HOAM_language ['errors']['hoam']['contact-support'];
			mysqli_stmt_close ($statement);
			exit ();
		} else {
			mysqli_stmt_close ($statement);
			return TRUE;
		}
	}

	function changeInt ($id, $column, $value) {
		// Change one column with the specified ID from the database.
		global $_HOAM_language;

		// Unavailable when installing.
		if (!$this -> table) die ($_HOAM_language ['errors']['hoam']['installation']['missing-table'] . $_HOAM_language ['errors']['hoam']['installation']['must-complete']);

		if ($value !== '') {
			$value = (int) $value;
			$statement = mysqli_prepare ($this -> connection, "UPDATE $this->table SET $column=? WHERE id=?");
			mysqli_stmt_bind_param ($statement, 'is', $value, $id);
		} else {
			$statement = mysqli_prepare ($this -> connection, "UPDATE $this->table SET $column=NULL WHERE id=?");
			mysqli_stmt_bind_param ($statement, 's', $id);
		}

		// execute query:
		$result = mysqli_stmt_execute ($statement);
		
		if (!$result) {
			echo $_HOAM_language ['errors']['hoam']['database']['problem'];
			if (HOAM_DEBUG) {
				printf ($_HOAM_language ['errors']['hoam']['database']['error'], $statement-> error);
				$backtrace = debug_backtrace();
				printf ('Backtrace Functions :: %s &rArr; %s &rArr; %s<br />', $backtrace[2]['function'], $backtrace[1]['function'], $backtrace[0]['function']);
				printf ('Backtrace File :: %s<br />', $backtrace[0]['file']);
				printf ('Backtrace Line :: %d<br />', $backtrace[0]['line']);
			} else {
				echo $_HOAM_language ['errors']['hoam']['debugging'];
			}
			echo $_HOAM_language ['errors']['hoam']['contact-support'];
			mysqli_stmt_close ($statement);
			exit ();
		} else {
			mysqli_stmt_close ($statement);
			return TRUE;
		}
	}

	function changeString ($id, $column, $value) {
		// Change one column with the specified ID from the database.
		global $_HOAM_language;

		// Unavailable when installing.
		if (!$this -> table) die ($_HOAM_language ['errors']['hoam']['installation']['missing-table'] . $_HOAM_language ['errors']['hoam']['installation']['must-complete']);

		if (is_null ($value)) {
			$statement = mysqli_prepare ($this -> connection, "UPDATE $this->table SET $column=NULL WHERE id=?");
			mysqli_stmt_bind_param ($statement, 's', $id);
		} else {
			$value = (string) $value;
			$statement = mysqli_prepare ($this -> connection, "UPDATE $this->table SET $column=? WHERE id=?");
			mysqli_stmt_bind_param ($statement, 'ss', $value, $id);
		}

		// execute query:
		$result = mysqli_stmt_execute ($statement);
		if (!$result) {
			echo $_HOAM_language ['errors']['hoam']['database']['problem'];
			if (HOAM_DEBUG) {
				printf ($_HOAM_language ['errors']['hoam']['database']['error'], $statement-> error);
				$backtrace = debug_backtrace();
				printf ('Backtrace Functions :: %s &rArr; %s &rArr; %s<br />', $backtrace[2]['function'], $backtrace[1]['function'], $backtrace[0]['function']);
				printf ('Backtrace File :: %s<br />', $backtrace[0]['file']);
				printf ('Backtrace Line :: %d<br />', $backtrace[0]['line']);
			} else {
				echo $_HOAM_language ['errors']['hoam']['debugging'];
			}
			echo $_HOAM_language ['errors']['hoam']['contact-support'];
			mysqli_stmt_close ($statement);
			exit ();
		} else {
			mysqli_stmt_close ($statement);
			return TRUE;
		}
	}

	function clean ($string, $maxlength = 0) {
		// Do a standard cleaning of input data.  This is used for all user-
		// generated input (ie, $_GET and $_POST) on the site.
		// Remove any beginning or ending whitespace padding.
		$string = trim ($string);
		// Make sure it won't cause problems in the db.
		$string = mysqli_real_escape_string ($this -> connection, $string);
		// Reduce the string down to the maximum length we want.
		if ($maxlength) {
			$string = mb_substr ($string, 0, $maxlength);
		}
		return ($string);
	}

	function deleteId ($id) {
		// Delete the row with the specified ID from the database.
		global $_HOAM_language;

		// Unavailable when installing.
		if (!$this -> table) die ($_HOAM_language ['errors']['hoam']['installation']['missing-table'] . $_HOAM_language ['errors']['hoam']['installation']['must-complete']);

		$query = "DELETE FROM $this->table
					WHERE id = '$id'";
		$result = mysqli_query ($this -> connection, $query);

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
		} else {
			return TRUE;
		}
	}

	function idExists ($id) {
		// Does the id given exist in the database?
		global $_HOAM_language;
		
		// Unavailable when installing.
		if (!$this -> table) die ($_HOAM_language ['errors']['hoam']['installation']['missing-table'] . $_HOAM_language ['errors']['hoam']['installation']['must-complete']);

		$query = "SELECT COUNT(id) FROM $this->table
					WHERE id = '$id'";
		$result = mysqli_query ($this -> connection, $query);
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
		} else {
			$count = mysqli_fetch_row ($result);
			// If we receive any result other than 1, the id does not exist, or
			// there is a problem with the database.
			if ($count[0] == 1) {
				return TRUE;
			} else {
				return FALSE;
			}
		}
	}

	function import ($filename, $delimiter = ';') {
		// Import the provided file into the database.
		global $_HOAM_language;

		// Force the script not to time out while importing if it happens to be
		// a particularly large file
	    set_time_limit (0);

    	if (is_file ($filename) === TRUE) {
        	$fp = fopen ($filename, 'r');
	        if (is_resource ($fp) === TRUE) {
            	$query = array ();

            	while (feof ($fp) === FALSE) {
                	$query[] = fgets ($fp);

                	if (preg_match ('~' . preg_quote ($delimiter, '~') . '\s*$~iS', end ($query)) === 1) {
                    	$query = trim (implode ('', $query));
	                    if (mysqli_query ($this -> connection, $query) === FALSE) {
							if (HOAM_DEBUG) {
								printf ($_HOAM_language ['errors']['hoam']['database']['error'], mysqli_error ($this -> connection));
								printf ($_HOAM_language ['errors']['hoam']['database']['query'], $query);
							} else {
								echo $_HOAM_language ['errors']['hoam']['debugging'];
							}
	                    }
                	}

                	if (is_string($query) === TRUE) {
                    	$query = array();
                	}
            	}
            }
            return fclose ($fp);
        }
    }

	function itemQuery ($query) {
		// Execute the supplied query and return only the first result.
		global $_HOAM_language, $_HOAM_log;

		if (empty ($query)) {
			echo $_HOAM_language ['errors']['hoam']['database']['problem'];
			echo $_HOAM_language ['errors']['hoam']['database']['query_empty'];
			return FALSE;
		} else {
			$result = mysqli_query ($this -> connection, $query);
			$_HOAM_log -> add (sprintf ($_HOAM_language ['log']['sql']['query_executed'], $query), HOAM_DEBUG);
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
			} else {
				// make sure we have something to do!
				if (gettype ($result) != 'boolean') {
					$row = mysqli_fetch_row ($result);
					return $row[0];
				} else {
					return FALSE;
				}
			}
		}	
	}

	function listAll ($id = NULL, $sort = 'datecreated DESC') {
		global $_HOAM_language;
		
		// Unavailable when installing.
		if (!$this -> table) die ($_HOAM_language ['errors']['hoam']['installation']['missing-table'] . $_HOAM_language ['errors']['hoam']['installation']['must-complete']);

		if (isset ($id)) {
			if ($this -> idExists ($id) ) {
				// Formulate the SQL query
				$query = "SELECT * FROM $this->table
							WHERE id = '$id'
							ORDER BY $sort";
			} else {
				return FALSE;
			}
		} else {
			// Formulate the SQL query
			$query = "SELECT * FROM $this->table
						ORDER BY $sort";
		}
		// Execute the query
		return ($this -> listQuery ($query));
	}

	function listAllId ($order = "datecreated DESC", $number = 0, $offset = 0) {
		global $_HOAM_language;
		
		// Unavailable when installing.
		if (!$this -> table) die ($_HOAM_language ['errors']['hoam']['installation']['missing-table'] . $_HOAM_language ['errors']['hoam']['installation']['must-complete']);

		// Return all IDs
		$query = "SELECT id FROM $this->table
						ORDER BY $order";
		// Are we retrieving a limited number of items?
		if ($number > 0) {
			$query .= " LIMIT $offset, $number";
		}
		// Execute the query
		return ($this -> listSimpleQuery ($query) );
	}

	function listIdGivenField ($column, $value) {
		global $_HOAM_language;
		
		// Unavailable when installing.
		if (!$this -> table) die ($_HOAM_language ['errors']['hoam']['installation']['missing-table'] . $_HOAM_language ['errors']['hoam']['installation']['must-complete']);

		// This needs to be used cautiously, since we're not matching
		// against a unique row id
		$query = "SELECT id FROM $this->table
					WHERE $column = '$value'";
		$result = $this -> listSimpleQuery ($query);
		if ($result) {
			return $result;
		} else {
			return FALSE;
		}
	}

	function listQuery ($query) {
		// Execute the query
		global $hoam_started, $_HOAM_language, $_HOAM_log;
		
		if (empty ($query)) {
			echo $_HOAM_language ['errors']['hoam']['database']['problem'];
			echo $_HOAM_language ['errors']['hoam']['database']['query_empty'];
			return FALSE;
		} else {
			$result = mysqli_query ($this -> connection, $query);
			if (isset ($hoam_started)) {
				$_HOAM_log -> add (sprintf ($_HOAM_language ['log']['sql']['query_executed'], $query), HOAM_DEBUG);
			}
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
			} else {
				// make sure we have something to do!
				$count = mysqli_affected_rows ($this -> connection);
				if ($count > 0) {
					$array = array ();
					for ($row = 0; $row < $count; $row ++) {
						$array[$row] = mysqli_fetch_assoc ($result);
					}
					return $array;
				} else {
					return FALSE;
				}
			}
		}
	}

	function listSimpleQuery ($query) {
		// Execute the query
		global $_HOAM_language, $_HOAM_log;
		
		if (empty ($query)) {
			echo $_HOAM_language ['errors']['hoam']['database']['problem'];
			echo $_HOAM_language ['errors']['hoam']['database']['query_empty'];
			return FALSE;
		} else {
			$result = mysqli_query ($this -> connection, $query);
			$_HOAM_log -> add (sprintf ($_HOAM_language ['log']['sql']['query_executed'], $query), HOAM_DEBUG);
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
			} else {
				// make sure we have something to do!
				$count = mysqli_num_rows ($result);
				if ($count > 0) {
					$array = array ();
					for ($row = 0; $row < $count; $row ++) {
						$items = mysqli_fetch_row ($result);
						$array[$row] = $items[0];
					}
					return $array;
				} else {
					return FALSE;
				}
			}
		}
	}

	function returnAllGivenField ($column, $value) {
		global $_HOAM_language;
		
		// Unavailable when installing.
		if (!$this -> table) die ($_HOAM_language ['errors']['hoam']['installation']['missing-table'] . $_HOAM_language ['errors']['hoam']['installation']['must-complete']);

		// This needs to be used cautiously, since we're not matching
		// against a unique row id
		$query = "SELECT * FROM $this->table
					WHERE $column = '$value'";
		$result = $this -> listQuery ($query);
		if ($result) {
			return $result;
		} else {
			return FALSE;
		}
	}

	function returnColumnSize ($column) {
		// Get the maximum size (in bytes) of a field
		global $_HOAM_language;
		
		global $_HOAM_language;
		
		// Unavailable when installing.
		if (!$this -> table) die ($_HOAM_language ['errors']['hoam']['installation']['missing-table'] . $_HOAM_language ['errors']['hoam']['installation']['must-complete']);

		// OK. The problem is, when using UTF8 in the database, that fields
		// defined as a certain length aren't returned as the correct
		// length by mysqli_fetch_field_direct. IOW, a char(32) field is
		// shown as actually being 96 chars long because it's 32 * 3 (3
		// bytes per char instead of one).
		// So, what we're doing now is getting the data type from the database
		// and depending on the type we get the column size two different ways. 
		$query = "SELECT DATA_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME='$this->table' AND COLUMN_NAME='$column'";
		$result = mysqli_query ($this -> connection, $query);
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
		} else {
			$type = mysqli_fetch_row ($result);
			switch ($type[0]) {
				case 'blob';
				case 'char':
				case 'longtext':
				case 'mediumtext':
				case 'text':
				case 'varchar':
					$query = "SELECT CHARACTER_MAXIMUM_LENGTH FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME='$this->table' AND COLUMN_NAME='$column'";
					$result = mysqli_query ($this -> connection, $query);
					$length = mysqli_fetch_row ($result);
					return ($length[0]);
					break;
				case 'bigint':
				case 'date':
				case 'datetime':
				case 'float':
				case 'int':
				case 'mediumint':
				case 'smallint':
				case 'tinyint':
				case '':
					$query = "SELECT $column FROM $this->table LIMIT 1";
					$result = mysqli_query ($this -> connection, $query);
					$field_info = mysqli_fetch_field_direct ($result, 0);
					return ($field_info -> length);
					break;
				default:
					return FALSE;
			}
		}
		// We had data, but couldn't find the column_name specified.
		return FALSE;
	}

	function returnCountQuery ($userquery = NULL) {
		global $_HOAM_language;
		
		// Unavailable when installing.
		if (!$this -> table) die ($_HOAM_language ['errors']['hoam']['installation']['missing-table'] . $_HOAM_language ['errors']['hoam']['installation']['must-complete']);

		// How many rows are in the table?
		$query  = "SELECT COUNT(*) FROM $this->table ";
		$query .= $userquery;
		return ($this -> itemQuery ($query));
	}

	function returnItem ($id, $column) {
		global $_HOAM_language;
		
		// Unavailable when installing.
		if (!$this -> table) die ($_HOAM_language ['errors']['hoam']['installation']['missing-table'] . $_HOAM_language ['errors']['hoam']['installation']['must-complete']);

		$query = "SELECT $column FROM $this->table
					WHERE id = '$id'";
		return ($this -> itemQuery ($query));
	}

	function runQuery ($query, $multi = FALSE) {
		// Execute the supplied query. We don't expect a return result. Usually
		// used for stored procedure calls.
		global $_HOAM_language, $_HOAM_log;
		
		if (empty ($query)) {
			echo $_HOAM_language ['errors']['hoam']['database']['problem'];
			echo $_HOAM_language ['errors']['hoam']['database']['query_empty'];
			return FALSE;
		} else {
			if (!$multi) {
				$result = mysqli_query ($this -> connection, $query);
			} else {
				$result = mysqli_multi_query ($this -> connection, $query);
			}
			if (defined (HOAM_INSTALLING) AND HOAM_INSTALLING) {
				$_HOAM_log -> add (sprintf ($_HOAM_language ['log']['sql']['query_executed'], $query), HOAM_DEBUG);
			}
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
			return ($result);
		}
	}

	function superClean ($string, $maxlength = 0) {
		// Do a "Super" clean of the data input.
		// Escape everything that needs to be before truncating the string.
		// Strip out any HTML or Javascript
		$string = strip_tags ($string);
		// Use the clean function to do the final cleaning
		$string = $this -> clean ($string, $maxlength = 0);

		return ($string);
	}

	function valueExists ($value, $column) {
		// Does the selected value appear in the database?
		global $_HOAM_language;
		
		// Unavailable when installing.
		if (!$this -> table) die ($_HOAM_language ['errors']['hoam']['installation']['missing-table'] . $_HOAM_language ['errors']['hoam']['installation']['must-complete']);

		$query = "SELECT COUNT(*) FROM $this->table
					WHERE $column LIKE '$value%'";
		$count = $this -> itemQuery ($query);
		// If we receive any result other than 0, we know at least one row in the table matches.
		if ($count > 0) {
			return TRUE;
		} else {
			return FALSE;
		}
	}

}

?>
