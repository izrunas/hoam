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

require_once ('../../hoam-startup.php');

session_start ();

$group_members_class = new HOAM_group_members ('group_members');

// Is the current user allowed to add / edit articles?
if (isset ($_SESSION['current_user']['id']) AND
	($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['news_add'], $_SESSION['current_user']['id']) OR
	$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['news_delete'], $_SESSION['current_user']['id']) OR
	$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['news_edit'], $_SESSION['current_user']['id']) OR
	$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
	($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id']))) {
	if (isset ($_POST['add']) OR isset ($_POST['cancel']) OR isset ($_POST['delete']) OR isset ($_POST['modify'])) {
		
		$news_class = new HOAM_news ('news');
		
		// If this is the second (or greater) time through the script (ie, there were
		// errors that had to be fixed before committing the changes) get rid of the old
		// cached information the user entered, we're going to populate it with
		// information just received from the form posting.
		if (isset ($_SESSION['news_information'])) {
			unset ($_SESSION['news_information']);
		}
		// Get rid of any old errors, and start over.
		if (isset ($_SESSION['error_class_item'])) {
			unset ($_SESSION['error_class_item']);
		}

		if (isset ($_POST['cancel'])) {
			// If the user canceled the action, send them back to the main page
			header ('Location: /website/news/list/');
		} elseif (isset ($_POST['delete'])) {
			if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['news_delete'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
				($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
				$id = (string) $news_class -> superClean ($_POST['news|id'], $news_class -> returnColumnSize ('id'));
				$title = $news_class -> returnTitle ($id);
				$result = $news_class -> deleteId ($id);
				if ($result) {
					$_HOAM_log -> add (sprintf ($_HOAM_language ['log']['news']['deleted'], $title),
										HOAM_MESSAGE_NOTICE,
										array ('clean' => FALSE,
												'flags' => $_HOAM_setting ['log']['flags']['news']));
					// Send the user back to the news administration page.
					header ('Location: /website/news/list/');
				} else {
					// Error Removing Item
					header ('Location: /error/remove/');
				}
			} else {
				// User does not have the necessary permissions.
				header ('Location: /error/403/');
			}
		} else {
			// If we're still here, then we must be adding or modifying an existing article.
			// Start populating the news_information array, and check for errors.
			$_SESSION['news_information'] = array ();
			$_SESSION['error_class_item'] = new HOAM_errors ('errors');
		
			if (isset ($_POST['news|id'])) {
				$_SESSION['news_information']['trusted_news|id'] = (string) $news_class -> superClean ($_POST['news|id'], $news_class -> returnColumnSize ('id'));
				if ($news_class -> idExists ($_SESSION['news_information']['trusted_news|id'])) {
				} else {
					// The homeowner is unknown.
					$_SESSION ['error_class_item'] -> addError ('news|id', $_HOAM_language ['errors']['generic']['invalid-id']);
				}
			}
			if (isset ($_POST['news|title'])) {
				$_SESSION['news_information']['trusted_news|title'] = (string) $news_class -> superClean ($_POST['news|title'], $news_class -> returnColumnSize ('title'));
				// Make sure the title isn't too short.
				if (mb_strlen ($_SESSION['news_information']['trusted_news|title']) >= $_HOAM_setting ['news']['minimum_title_length']) {
				} else {
					// The subject is too short.
					$_SESSION ['error_class_item'] -> addError ('news|title', sprintf ($_HOAM_language ['errors']['news']['title']['too-short'], $_HOAM_setting ['news']['minimum_title_length']));
				}
			}
			if (isset ($_POST['news|article'])) {
				$_SESSION['news_information']['trusted_news|article'] = (string) $news_class -> clean ($_POST['news|article'], $news_class -> returnColumnSize ('article'));
				// Make sure the article isn't too short.
				if (mb_strlen ($_SESSION['news_information']['trusted_news|article']) >= $_HOAM_setting ['news']['minimum_article_length']) {
				} else {
					// The subject is too short.
					$_SESSION ['error_class_item'] -> addError ('news|article', sprintf ($_HOAM_language ['errors']['news']['article']['too-short'], $_HOAM_setting ['news']['minimum_article_length']));
				}
			}
// echo print_r ($_POST); print_r ($_SESSION['news_information']); $_SESSION['error_class_item'] -> errorCount; print_r ($_SESSION['error_class_item'] -> ShowErrors ()); exit();

			// Are there any errors that the current user needs to fix before committing the changes?
			if ($_SESSION['error_class_item'] -> errorCount > 0) {
				// If we have an id, send the user back to that.
				if (isset ($_POST['add'])) {
					header ('location: /website/news/add/');
				} else {
					header ('location: /website/news/edit/' . $_SESSION ['news_information']['trusted_news|id'] . '/');
				}
			} else {
				// Finally! We have good data and can start modifying the database.
				if (isset ($_POST['add'])) {
					$id = $news_class -> add ($_SESSION['current_user']['id']);
					if ($id == FALSE) {
						// Error Adding Item
						header ('Location: /error/add/');
					} else {
						// When adding a new article, set the modified date the same as the created.
						$_SESSION['news_information']['trusted_news|id'] = $id;
						$_HOAM_log -> add (sprintf ($_HOAM_language ['log']['news']['created'], $_SESSION['news_information']['trusted_news|title']),
											HOAM_MESSAGE_NOTICE,
											array ('clean' => FALSE,
													'owner_id' => $_SESSION['news_information']['trusted_news|id'],
													'flags' => $_HOAM_setting ['log']['flags']['news']));
					}
				} else {
					$changes = HOAM_diff ($news_class -> returnArticle ($_SESSION['news_information']['trusted_news|id']), $_SESSION['news_information']['trusted_news|article']);
					$changes = sprintf ($_HOAM_language ['tables']['generic']['history'], ucfirst ($_HOAM_language ['common']['old-value']), ucfirst ($_HOAM_language ['common']['new-value']), $changes['old'], $changes['new']);
					$_HOAM_log -> add (sprintf ($_HOAM_language ['log']['news']['modified'], $changes),
										HOAM_MESSAGE_NOTICE,
										array ('clean' => FALSE,
												'owner_id' => $_SESSION['news_information']['trusted_news|id'],
												'flags' => $_HOAM_setting ['log']['flags']['news']));
					$news_class -> changeDateModified ($_SESSION['news_information']['trusted_news|id']);
				}
				// By now, we've already verified that all of our data is correct and valid, therefore we can just update the db.
				// Consider it a poor-man's transaction :)
				$news_class -> changeTitle ($_SESSION['news_information']['trusted_news|id'], $_SESSION['news_information']['trusted_news|title']);
				$news_class -> changeArticle ($_SESSION['news_information']['trusted_news|id'], $_SESSION['news_information']['trusted_news|article']);

				/*************************************************************************/

				// Clear the variables since everything worked OK.
				if (isset ($_SESSION['error_class_item'])) {
					unset ($_SESSION['error_class_item']);
				}
				if (isset ($_SESSION['news_information'])) {
					unset ($_SESSION['news_information']);
				}

				header ('Location: /website/news/list/');
			}
		}
	} else {
		// We don't know what action (add / cancel / delete / modify) we're being asked to perform
		header ('Location: /error/unknown_action/');
	}
} else {
	// User does not have the necessary permissions.
	header ('Location: /error/403/');
	exit ();
}

?>
