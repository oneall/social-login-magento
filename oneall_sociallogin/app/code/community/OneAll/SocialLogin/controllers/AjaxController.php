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
class OneAll_SocialLogin_AjaxController extends Mage_Core_Controller_Front_Action
{
	// Autodetect API Handler
	public function indexAction ()
	{
		// Check if CURL is available
		if (Mage::helper ('oneall_sociallogin')->is_curl_available ())
		{
			// Check CURL HTTPS - Port 443
			if (Mage::helper ('oneall_sociallogin')->is_api_connection_curl_ok (true) === true)
			{
				die ('success_autodetect_api_curl_https');
			}
			// Check CURL HTTP - Port 80
			elseif (Mage::helper ('oneall_sociallogin')->is_api_connection_curl_ok (false) === true)
			{
				die ('success_autodetect_api_curl_http');
			}
			else
			{
				die ('error_autodetect_api_curl_ports_blocked');
			}
		}
		// Check if FSOCKOPEN is available
		elseif (Mage::helper ('oneall_sociallogin')->is_fsockopen_available ())
		{
			// Check FSOCKOPEN HTTPS - Port 443
			if (Mage::helper ('oneall_sociallogin')->is_api_connection_fsockopen_ok (true) == true)
			{
				die ('success_autodetect_api_fsockopen_https');
			}
			// Check FSOCKOPEN HTTP - Port 80
			elseif (Mage::helper ('oneall_sociallogin')->is_api_connection_fsockopen_ok (false) == true)
			{
				die ('success_autodetect_api_fsockopen_http');
			}
			else
			{
				die ('error_autodetect_api_fsockopen_ports_blocked');
			}
		}

		// No working handler found
		die ('error_autodetect_api_no_handler');
	}

	// Verify API Settings
	public function verifyAction ()
	{
		// API Credentials
		$api_subdomain = trim (Mage::app ()->getRequest ()->getParam ('api_subdomain'));
		$api_key = trim (Mage::app ()->getRequest ()->getParam ('api_key'));
		$api_secret = trim (Mage::app ()->getRequest ()->getParam ('api_secret'));

		// API Handler
		$api_connection_handler = (trim (Mage::app ()->getRequest ()->getParam ('api_connection_handler')) == 'fsockopen' ? 'fsockopen' : 'curl');
		$api_connection_port = (trim (Mage::app ()->getRequest ()->getParam ('api_connection_port')) == '80' ? 80 : 443);

		// Fields missing
		if (empty ($api_subdomain) or empty ($api_key) or empty ($api_secret))
		{
			die ('error_not_all_fields_filled_out');
		}

		// Full domain entered
		if (preg_match ("/([a-z0-9\-]+)\.api\.oneall\.com/i", $api_subdomain, $matches))
		{
			$api_subdomain = $matches [1];
		}

		// Check subdomain format
		if (! preg_match ("/^[a-z0-9\-]+$/i", $api_subdomain))
		{
			die ('error_subdomain_wrong_syntax');
		}

		// Domain
		$api_domain = $api_subdomain . '.api.oneall.com';

		// Connection to
		$api_resource_url = ($api_connection_port == 443 ? 'https' : 'http') . '://' . $api_domain . '/tools/ping.json';

		// API Credentials
		$api_credentials = array ();
		$api_credentials ['api_key'] = $api_key;
		$api_credentials ['api_secret'] = $api_secret;

		// Get connection details
		$result = Mage::helper ('oneall_sociallogin')->do_api_request ($api_connection_handler, $api_resource_url, $api_credentials);

		// Parse result
		if (is_object ($result) and property_exists ($result, 'http_code') and property_exists ($result, 'http_data'))
		{
			switch ($result->http_code)
			{
				// Success
				case 200:
					die ('success');

				// Authentication Error
				case 401:
					die ('error_authentication_credentials_wrong');

				// Wrong Subdomain
				case 404:
					die ('error_subdomain_wrong');

				// Other error
				default:
					die ('error_communication');
			}
		}

		die ('error_communication');
	}
}
