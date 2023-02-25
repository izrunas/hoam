/*
 * This file is part of HOAM, copyright (C) 2002-2015 ARP Realty, Inc.
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

/* HOAM_attachmentList is used to return the requested number of attachment ids
for the attachment listing portion of the website administration page. */
DROP PROCEDURE IF EXISTS `HOAM_attachmentList`;
GO

delimiter $$

CREATE PROCEDURE HOAM_attachmentList (id CHAR(32), num SMALLINT UNSIGNED)
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

END $$

delimiter ;

