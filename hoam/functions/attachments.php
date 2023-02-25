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

function HOAM_attachmentAdd (&$tmp, &$real, $user_id, $type, $desc, $owners) {
	// Add an uploaded file to the database.
	global $_HOAM_country, $_HOAM_language, $_HOAM_log;	

	$attachment_class = new HOAM_attachments ('attachments');

	if (is_uploaded_file ($tmp['tmp_name'])) {
		// OK, so we know we have valid attachments now.
		$id = $attachment_class -> add ($user_id);
		if ($id) {
			$year = date ('Y');
			$file_path = HOAM_PATH_ATTACHMENT . $year . '/' . date ($_HOAM_country ['date']['format_mysql']);
			if (! file_exists ($file_path)) {
				mkdir ($file_path, 0700, TRUE);
			}
			$new_name = HOAM_attachmentGenerateName ($real['name']);
			move_uploaded_file ($real['tmp_name'], $file_path . '/' . $id . '_' . $new_name);
			chmod ($file_path . '/' . $id . '_' . $new_name, 0600);
			$attachment_class -> SetBit ($id, $type);
			$attachment_class -> changeFileName ($id, $real['name']);
			$attachment_class -> changeMime ($id, $real['type']);
			if (mb_substr ($real['type'], 0, 5) == 'image') {
				$img_info = getimagesize ($file_path . '/' . $id . '_' . $new_name);
				$attachment_class -> changeHeight ($id, $img_info[1]);
				$attachment_class -> changeWidth ($id, $img_info[0]); 
			}
			$attachment_class -> changeDescription ($id, $desc);
			// This is to catch any callers that neglected to send the owner list in an array as required.
			if (is_array ($owners)) {
				$attachment_class -> changeOwners ($id, $owners);
			} else {
				$attachment_class -> changeOwners ($id, array ($owners));
			}
			$_HOAM_log -> add (sprintf ($_HOAM_language ['log']['attachment']['add'], $real['name'], $tmp['tmp_name'], $id), HOAM_MESSAGE_INFORMATIONAL);
			return ($id);
		} else {
			$_HOAM_log -> add (sprintf ($_HOAM_language ['log']['attachment']['failed'], $real['name'], $tmp['tmp_name']), HOAM_MESSAGE_ERROR);
			return (FALSE);
		}
	} else {
		return (FALSE);
	}
}

function HOAM_attachmentAddInternal (&$filename, &$filedata, $mime, $user_id, $type, $desc, $owners) {
	// Add a generated file to the database.
	global $_HOAM_country, $_HOAM_log;

	$attachment_class = new HOAM_attachments ('attachments');

	$id = $attachment_class -> add ($user_id);
	if ($id) {
		$year = date ('Y');
		$file_path = HOAM_PATH_ATTACHMENT . $year . '/' . date ($_HOAM_country ['date']['format_mysql']);
		if (! file_exists ($file_path)) {
			if (! mkdir ($file_path, 0700, TRUE)) {
				$_HOAM_log -> add ('Could not create directory "' . $file_path . '".', HOAM_MESSAGE_ERROR);
			}
		}
		$new_name = HOAM_attachmentGenerateName ($filename);
		$handle = fopen ($file_path . '/' . $id . '_' . $new_name, 'xb');
		if ($handle === FALSE) {
			$_HOAM_log -> add ('Could not create file "' . $file_path . '/' . $id . '_' . $new_name . '".', HOAM_MESSAGE_ERROR);
		}
		if (! fwrite ($handle, $filedata)) {
			$_HOAM_log -> add ('Could not write data to file "' . $file_path . '/' . $id . '_' . $new_name . '".', HOAM_MESSAGE_ERROR);
			return FALSE;
		} else {
			fflush ($handle);
			fclose ($handle);
			chmod ($file_path . '/' . $id . '_' . $new_name, 0600);
			$attachment_class->SetBit ($id, $type);
			$attachment_class->changeFileName ($id, $filename);
			$attachment_class->changeMime ($id, $mime);
			if (mb_substr ($mime, 0, 5) == 'image') {
				$img_info = getimagesize ($file_path . '/' . $id . '_' . $new_name);
				$attachment_class->changeHeight ($id, $img_info[1] );
				$attachment_class->changeWidth ($id, $img_info[0] ); 
			}
			$attachment_class->changeDescription ($id, $desc);
			$attachment_class->changeOwners ($id, $owners);
			return ($id);
		}
	} else {
		return (FALSE);
	}
}

function HOAM_attachmentCallback ($matches) {
	return (HOAM_attachmentReturnHTML ($matches[1]));
}

function HOAM_attachmentCreatePreview ($id, $image) {
	// Create a 'preview' image of the attachment.
	global $_HOAM_country, $_HOAM_language, $_HOAM_setting;	

	$attachment_class = new HOAM_attachments ('attachments');

	if ($attachment_class -> idExists ($id)) {
		if (is_true ($_HOAM_setting ['attachment']['preview']['enable'])) {
			// Make sure the image itself isn't smaller than the specified
			// preview sizes.
			$datecreated = $attachment_class -> returnDateCreated ($id);
			if ($image AND
				($attachment_class -> returnHeight ($id) >= $_HOAM_setting ['attachment']['preview']['height']) AND
				($attachment_class -> returnWidth ($id) >= $_HOAM_setting ['attachment']['preview']['width'])) {
				// This cheats on the filename creation.
				$source_name = HOAM_PATH_ATTACHMENT . date ('Y', strtotime ($datecreated)) . '/' . date ($_HOAM_country ['date']['format_mysql'], strtotime ($datecreated)) . '/' . $id . '_' . $attachment_class -> returnFilename ($id);
			} else {
				// Only convert the first page of a PDF
				// This cheats on the filename creation.
				$source_name = HOAM_PATH_ATTACHMENT . date ('Y', strtotime ($datecreated)) . '/' . date ($_HOAM_country ['date']['format_mysql'], strtotime ($datecreated)) . '/' . $id . '_' . $attachment_class -> returnFilename ($id) . '[0]';
			}
			$file_path = HOAM_PATH_ATTACHMENT_PREVIEW . date ('Y', strtotime ($datecreated)) . '/' . date ($_HOAM_country ['date']['format_mysql'], strtotime ($datecreated));
			if (! file_exists ($file_path)) {
				mkdir ($file_path, 0777, TRUE);
			}
			$dest_name = $file_path . '/' . $id . '.png';
			$return_code = FALSE;
			exec ("convert -colorspace 'rgb' -flatten -alpha off \"$source_name\" -resize " . $_HOAM_setting ['attachment']['preview']['width'] . "x" . $_HOAM_setting ['attachment']['preview']['height'] . " -colors 256 PNG8:\"$dest_name\"", $output, $return_code);
			if ($return_code) {
				return FALSE;
			} else {
				return TRUE;
			}
		} else {
			return FALSE;
		}
	} else {
		header ('Location: /error/unknown_id/');
		exit ();
	}
}

function HOAM_attachmentGenerateName ($filename) {
	// Our max filename is held in HOAM_FILENAME_LENGTH, which at default is
	// 192 chars. md5 + '_' is an additional 33, giving us 225. We should be
	// safe as most contemporary filesystems appear to support at least 225 --
	// see http://en.wikipedia.org/wiki/Comparison_of_file_systems
	if (mb_strlen ($filename) < HOAM_FILENAME_LENGTH) {
		$new_name = $filename;
	} else {
		$ext_loc = mb_strrpos ($filename, '.');
		if ($ext_loc) {
			$new_name = mb_substr ($filename, 0, $ext_loc);
			// if we're still getting a long name (ie, the extension is more
			// than a simple '.'), trim it. 
			if ((mb_strlen ($filename) - $ext_loc) > 0) {
				$new_name = mb_substr ($new_name, 0, HOAM_FILENAME_LENGTH - (mb_strlen ($filename) - $ext_loc));
			}
			// Add the extention back on.
			$new_name .= mb_substr ($filename, $ext_loc, mb_strlen ($filename) - $ext_loc);
		}
	}
	return ($new_name);
}

function HOAM_attachmentReturnHTML ($id) {
	// Returns a string that contains HTML formatted output.

	global $_HOAM_country, $_HOAM_language, $_HOAM_setting;
	
	// Needed for the HOAM_is_image function.
	require_once (HOAM_PATH_FUNCTION . 'image.php');
	
	$attachment_class = new HOAM_attachments ('attachments');
	$group_members_class = new HOAM_group_members ('group_members');
	$user_class = new HOAM_user ('users');

	$HTML = (string) NULL;
	$HTML .= '<div class="binary_attachment">';

	if ($attachment_class -> idExists ($id)) {
		$attachment = $attachment_class -> listAll ($id);
		$attachment = $attachment[0];
	
		if (isset ($_SESSION['current_user']['id'])) {
			if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['attachment_view'], $_SESSION['current_user']['id'])) {
				$HTML .= '<a href="/hoam/scripts/attachment/view.php?id=' . $id . '">';
			}
		}
		switch (TRUE) {
			case (($attachment['mimetype'] === 'application/vnd.ms-excel') OR
					($attachment['mimetype'] === 'application/vnd.ms-excel.addin.macroEnabled.12') OR
					($attachment['mimetype'] === 'application/vnd.ms-excel.sheet.binary.macroEnabled.12') OR
					($attachment['mimetype'] === 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet') OR
					($attachment['mimetype'] === 'application/vnd.openxmlformats-officedocument.spreadsheetml.template')) :
				$HTML .= '<img src="/hoam/images/icons/page_white_excel.png" alt="' . $_HOAM_language ['attachment']['name']['xls'] . '" /><br />';
				break;
			case (($attachment['mimetype'] === 'application/vnd.ms-powerpoint') OR
					($attachment['mimetype'] === 'application/vnd.openxmlformats-officedocument.presentationml.presentation') OR
					($attachment['mimetype'] === 'application/vnd.openxmlformats-officedocument.presentationml.slide') OR
					($attachment['mimetype'] === 'application/vnd.openxmlformats-officedocument.presentationml.slideshow') OR
					($attachment['mimetype'] === 'application/vnd.openxmlformats-officedocument.presentationml.template')) :
				$HTML .= '<img src="/hoam/images/icons/page_white_powerpoint.png" alt="' . $_HOAM_language ['attachment']['name']['ppt'] . '" /><br />';
				break;
			case (($attachment['mimetype'] === 'application/acrobat') OR
					($attachment['mimetype'] === 'application/pdf') OR
					($attachment['mimetype'] === 'application/vnd.cups-pdf') OR
					($attachment['mimetype'] === 'application/x-pdf')) :
				if (is_true ($_HOAM_setting['attachment']['preview']['enable'])) {
					$HTML .= '<img src="' . HOAM_attachmentReturnPreview ($id, FALSE) . '"';
					if ($attachment['description']) {
						$HTML .= ' alt="' . $attachment['description'] . '"';
						$HTML .= ' title="' . $attachment['description'] . '"';
					} else {
						$desc = $attachment['filename'];
						$desc .= ' ' . ucfirst ($_HOAM_language ['common']['uploaded']);
						$desc .= ' ' . date ($_HOAM_country ['date']['format_reports'], strtotime ($attachment ['datecreated']));
						$desc .= ' ' . ucfirst ($_HOAM_language ['common']['by']);
						$desc .= ' ' . $user_class -> returnAccountName ($attachment['user_id']);
						$desc .= '"';
						$HTML .= ' alt="' . $desc . '"';
						$HTML .= ' title="' . $desc . '"';
						unset ($desc); 
					}
					$HTML .= ' height="' . $_HOAM_setting ['attachment']['preview']['height'] . '"';
					$HTML .= ' width="' . $_HOAM_setting ['attachment']['preview']['width'] . '"';
					$HTML .= ' /><br />';
				} else {
					$HTML .= '<img src="/hoam/images/icons/page_white_acrobat.png" alt="' . $_HOAM_language ['attachment']['name']['pdf'] . '" /><br />';
				}
				break;
			case (($attachment['mimetype'] === 'application/msword') OR
					($attachment['mimetype'] === 'application/vnd.openxmlformats-officedocument.wordprocessingml.document') OR
					($attachment['mimetype'] === 'application/vnd.openxmlformats-officedocument.wordprocessingml.template')) :
				$HTML .= '<img src="/hoam/images/icons/page_white_word.png" alt="' . $_HOAM_language ['attachment']['name']['doc'] . '" /><br />';
				break;
			case (mb_substr ($attachment['mimetype'], 0, 5) === 'image') :
				if (is_true ($_HOAM_setting['attachment']['preview']['enable'])) {
					if (($attachment['height'] > $_HOAM_setting ['attachment']['preview']['height']) OR
						($attachment['width'] > $_HOAM_setting ['attachment']['preview']['width'])) {
						$HTML .= '<img src="' . HOAM_attachmentReturnPreview ($id) . '"';
						if (($attachment['width'] / $attachment['height']) < 1.0) {
							$HTML .= ' height="' . $_HOAM_setting ['attachment']['preview']['height'] . '"';
							$HTML .= ' width="' . $_HOAM_setting ['attachment']['preview']['width'] . '"';
						} else {
							// Just swap to show in landscape instead
							$HTML .= ' height="' . $_HOAM_setting ['attachment']['preview']['width'] . '"';
							$HTML .= ' width="' . $_HOAM_setting ['attachment']['preview']['height'] . '"';
						}
					} elseif (!empty ($attachment['height']) AND !empty ($attachment['width'])) {
						// If the original image is small enough, just display
						$HTML .= '<img src="' . HOAM_attachmentReturnPath ($id) . '"';
						$HTML .= ' height="' . $attachment['height'] . '"';
						$HTML .= ' width="' . $attachment['width'] . '"';
					} else {
						// If all else fails, just display the generic icon.
						$HTML .= '<img src="/hoam/images/icons/image.png" ';
					}
					if ($attachment['description']) {
						$HTML .= ' alt="' . $attachment['description'] . '"';
						$HTML .= ' title="' . $attachment['description'] . '"';
					} else {
						$desc = $attachment['filename'];
						$desc .= ' ' . ucfirst ($_HOAM_language ['common']['uploaded']);
						$desc .= ' ' . date ($_HOAM_country ['date']['format_reports'], strtotime ($attachment ['datecreated']));
						$desc .= ' ' . ucfirst ($_HOAM_language ['common']['by']);
						$desc .= ' ' . $user_class -> returnAccountName ($attachment['user_id']);
						$HTML .= ' alt="' . $desc . '"';
						$HTML .= ' title="' . $desc . '"';
						unset ($desc); 
					}
					$HTML .= ' /><br />';
				} else {
					$HTML .= '<img src="/hoam/images/icons/image.png" alt="Image" /><br />';
				}
				break;
			case (mb_substr ($attachment['mimetype'], 0, 5) === 'sound') :
				$HTML .= '<img src="/hoam/images/icons/sound.png" alt="Sound" /><br />';
				break;
			default:
				// Just use a default icon.
				$HTML .= '<img src="/hoam/images/icons/page_white.png" alt="Document" /><br />';
				break;
		}
		// $HTML .= $attachment['filename'];
		$HTML .= HOAM_returnStringMultiLine ($attachment['filename'], 30);
		if (isset ($_SESSION['current_user']['id'])) {
			if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['attachment_view'], $_SESSION['current_user']['id'])) {
				$HTML .= '</a>';
			}
//			if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['attachment_delete'], $_SESSION['current_user']['id'])) {
//				$HTML .= '<br/><a href="#">' . $_HOAM_language ['common']['delete'] . '</a>';
//			}
		} else {
			$HTML .= '<br /><span class="caution">' . $_HOAM_language ['errors']['attachment']['must_login'] . '</span>';
		}
	} else {
		$HTML .= '<img src="/hoam/images/icons/stop.png" /><br />';
		$HTML .= '<small class="error">' . $_HOAM_language ['errors']['attachment']['not-found'] . '</small>';
	}
	$HTML .= '</div>';
	
	return $HTML;
}

function HOAM_attachmentReturnPath ($id) {
	global $_HOAM_country;
	
	$attachment_class = new HOAM_attachments ('attachments');
	
	$filename = $attachment_class -> returnFilename ($id); 
	if (mb_strlen ($filename) < HOAM_FILENAME_LENGTH) {
		$new_name = $filename;
	} else {
		$ext_loc = mb_strrpos ($filename, '.');
		if ($ext_loc) {
			$new_name = mb_substr ($filename, 0, $ext_loc);
			// if we're still getting a long name (ie, the extension is more
			// than a simple '.'), trim it. 
			if ((mb_strlen ($filename) - $ext_loc) > 0) {
				$new_name = mb_substr ($new_name, 0, HOAM_FILENAME_LENGTH - (mb_strlen ($filename) - $ext_loc));
			}
			// Add the extension back on.
			$new_name .= mb_substr ($filename, $ext_loc, mb_strlen ($filename) - $ext_loc);
		}
	}
	$datecreated = $attachment_class -> returnDateCreated ($id);
	$full_path = '/attachments/' . date ('Y', strtotime ($datecreated)) . '/' . date ($_HOAM_country ['date']['format_mysql'], strtotime ($datecreated)) . '/' . $id . '_' . $new_name;		

	return ($full_path);
}

function HOAM_attachmentReturnPreviewPath ($id) {
	$filename = HOAM_attachmentReturnPreview ($id);
	if (mb_substr ($filename, 0, 13) != '/hoam/images/') {
		return ($filename);
	} else {
		return FALSE;
	}
}

function HOAM_attachmentReturnPreview ($id, $image = TRUE) {
	// return either the path to a preview image, or create one if it doesn't
	// exist already. In case of failure, return a generic image. 
	global $_HOAM_country, $_HOAM_language, $_HOAM_setting;

	$attachment_class = new HOAM_attachments ('attachments');
	$datecreated = $attachment_class -> returnDateCreated ($id);
	$file_path = HOAM_PATH_ATTACHMENT_PREVIEW . date ('Y', strtotime ($datecreated)) . '/' . date ($_HOAM_country ['date']['format_mysql'], strtotime ($datecreated));
	if (file_exists ($file_path . '/' . $id . '.png')) {
		return ('/attachments/preview/' . date ('Y', strtotime ($datecreated)) . '/' . date ($_HOAM_country ['date']['format_mysql'], strtotime ($datecreated)) . '/' . $id . '.png');
	} else {
		if (is_true ($_HOAM_setting ['attachment']['preview']['enable'])) {
			if (HOAM_attachmentCreatePreview ($id, $image)) {
				return ('/attachments/preview/' . date ('Y', strtotime ($datecreated)) . '/' . date ($_HOAM_country ['date']['format_mysql'], strtotime ($datecreated)) . '/' . $id . '.png');
			} else {
				return ('/hoam/images/icons/image.png');
			}
		} else {
			return ('/hoam/images/icons/image.png');
		}
	}
}

function HOAM_attachmentUnknown ($id, $owner = FALSE, $image = FALSE) {
	// This function should generally only be called when we've already
	// identified an unknown attachment. This is simply a way to consolidate a
	// number of $HTML error messages about the specified attachment. 
	global $_HOAM_language;

	$attachment_class = new HOAM_attachments ('attachments');

	$HTML = '';
	if ($image) {
		$HTML .= HOAM_attachmentReturnPreview ($id); 
	}
	$HTML .= '<span class="error">' . $_HOAM_language ['errors']['attachment']['unknown_owner'] . '</span>: ' . $attachment_class -> returnFilename ($id);
	if ($owner) {
		$HTML .= ' (Owner is "' . $owner . '")';
	}
	
	return ($HTML);
}

?>
