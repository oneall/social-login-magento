<?php
/**
 * @package   	OneAll Social Login
 * @copyright 	Copyright 2014 http://www.oneall.com - All rights reserved.
 * @license   	GNU/GPL 2 or later
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307,USA.
 *
 * The "GNU General Public License" (GPL) is available at
 * http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 *
 */


$installer = $this;
$installer->startSetup ();

// Table structure
$sql = "CREATE TABLE `".$this->getTable('oneall_sociallogin_entity')."`(
	`entity_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
	`customer_id` int(11) UNSIGNED NOT NULL,
	`user_token` char(36) NOT NULL,
	`identity_token` char(36) NOT NULL,
	PRIMARY KEY (`entity_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1;";

// Create table
$installer->run ($sql);

$installer->endSetup ();

?>