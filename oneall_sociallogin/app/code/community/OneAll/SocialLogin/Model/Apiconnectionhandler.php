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

//API connection handler dropdown
class OneAll_SocialLogin_Model_Apiconnectionhandler
{
	public function toOptionArray ()
	{
		$helper = Mage::helper ('oneall_sociallogin');

		return array (
			array (
				'value' => '',
				'label' => ''
			),
			array (
				'value' => 'curl',
				'label' => $helper->__ ('Use PHP CURL to communicate with the API')
			),
			array (
				'value' => 'fsockopen',
				'label' => $helper->__ ('Use PHP FSOCKOPEN to communicate with the API')
			)
		);
	}
}
