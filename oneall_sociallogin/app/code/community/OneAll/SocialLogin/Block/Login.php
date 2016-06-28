<?php

/**
 * @package   	OneAll Social Login
 * @copyright 	Copyright 2014-2016 http://www.oneall.com - All rights reserved
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

class OneAll_SocialLogin_Block_Login extends Mage_Core_Block_Template
{
	private $place;

	public function __construct ()
	{
		parent::__construct ();

		if (! $this->getSession ()->isLoggedIn () && ! Mage::helper ('oneall_sociallogin')->handle_api_callback ())
		{
			$this->setTemplate ('oneall/sociallogin/login.phtml');
		}
	}

	private function getSession ()
	{
		return Mage::getSingleton ('customer/session');
	}

	public function setPlace ($place)
	{
		$this->place = $place;
		return $this;
	}

	public function getPlace ()
	{
		return $this->place;
	}

	public function getLoginType ($service = '')
	{
		return Mage::getStoreConfig ('oneall_sociallogin/' . $service . '/' . $this->getPlace () . '_type');
	}
}