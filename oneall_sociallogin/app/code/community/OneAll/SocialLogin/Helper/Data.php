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

// Helper
class OneAll_SocialLogin_Helper_Data extends Mage_Core_Helper_Abstract
{
	
	const OA_USER_AGENT = 'SocialLogin/1.1.2 Magento/1.x (+http://www.oneall.com/)';
	
	/**
	 * Generate a random email address.
	 */
	protected function create_random_email ()
	{
		$customer = Mage::getModel ('customer/customer');
		$customer->setWebsiteId (Mage::app ()->getWebsite ()->getId ());

		do
		{
			// Create a random email.
			$email = md5 (uniqid (rand (10000, 99000))) . "@example.com";

			// Try to load a customer for it
			$customer->loadByEmail ($email);
			$id = $customer->getId ();
		}
		while (! empty ($id));

		// Done
		return $email;
	}

	/**
	 * Check if the current connection is being made over https.
	 */
	public function is_https_on ()
	{
		if (! empty ($_SERVER ['SERVER_PORT']))
		{
			if (trim ($_SERVER ['SERVER_PORT']) == '443')
			{
				return true;
			}
		}

		if (! empty ($_SERVER ['HTTP_X_FORWARDED_PROTO']))
		{
			if (strtolower (trim ($_SERVER ['HTTP_X_FORWARDED_PROTO'])) == 'https')
			{
				return true;
			}
		}

		if (! empty ($_SERVER ['HTTPS']))
		{
			if (strtolower (trim ($_SERVER ['HTTPS'])) == 'on' or trim ($_SERVER ['HTTPS']) == '1')
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Handle the callback from OneAll.
	 */
	public function handle_api_callback ()
	{
		// Read URL parameters
		$action = Mage::app ()->getRequest ()->getParam ('oa_action');
		$connection_token = Mage::app ()->getRequest ()->getParam ('connection_token');

		// Callback Handler
		if ($action == 'social_login' and ! empty ($connection_token))
		{
			// Read settings
			$settings = array ();
			$settings ['api_connection_handler'] = Mage::getStoreConfig ('oneall_sociallogin/connection/handler');
			$settings ['api_connection_port'] = Mage::getStoreConfig ('oneall_sociallogin/connection/port');
			$settings ['api_subdomain'] = Mage::getStoreConfig ('oneall_sociallogin/general/subdomain');
			$settings ['api_key'] = Mage::getStoreConfig ('oneall_sociallogin/general/key');
			$settings ['api_secret'] = Mage::getStoreConfig ('oneall_sociallogin/general/secret');

			// API Settings
			$api_connection_handler = ((! empty ($settings ['api_connection_handler']) and $settings ['api_connection_handler'] == 'fsockopen') ? 'fsockopen' : 'curl');
			$api_connection_port = ((! empty ($settings ['api_connection_port']) and $settings ['api_connection_port'] == 80) ? 80 : 443);
			$api_connection_protocol = ($api_connection_port == 80 ? 'http' : 'https');
			$api_subdomain = (! empty ($settings ['api_subdomain']) ? trim ($settings ['api_subdomain']) : '');

			// We cannot make a connection without a subdomain
			if (! empty ($api_subdomain))
			{
				// See: http://docs.oneall.com/api/resources/connections/read-connection-details/
				$api_resource_url = $api_connection_protocol . '://' . $api_subdomain . '.api.oneall.com/connections/' . $connection_token . '.json';

				// API Credentials
				$api_credentials = array ();
				$api_credentials ['api_key'] = (! empty ($settings ['api_key']) ? $settings ['api_key'] : '');
				$api_credentials ['api_secret'] = (! empty ($settings ['api_secret']) ? $settings ['api_secret'] : '');

				// Retrieve connection details
				$result = $this->do_api_request ($api_connection_handler, $api_resource_url, $api_credentials);

				// Check result
				if (is_object ($result) and property_exists ($result, 'http_code') and $result->http_code == 200 and property_exists ($result, 'http_data'))
				{
					// Decode result
					$decoded_result = @json_decode ($result->http_data);

					if (is_object ($decoded_result) and isset ($decoded_result->response->result->data->user))
					{
						// Extract user data.
						$data = $decoded_result->response->result->data;

						// The user_token uniquely identifies the user.
						$user_token = $data->user->user_token;

						// The identity_token uniquely identifies the social network account.
						$identity_token = $data->user->identity->identity_token;

						// Check if we have a user for this user_token.
						$oneall_entity = Mage::getModel ('oneall_sociallogin/entity')->load ($user_token, 'user_token');
						$customer_id = $oneall_entity->customer_id;

						// No user for this token, check if we have a user for this email.
						if (empty ($customer_id))
						{
							if (isset ($data->user->identity->emails) and is_array ($data->user->identity->emails))
							{
								$customer = Mage::getModel ("customer/customer");
								$customer->setWebsiteId (Mage::app ()->getWebsite ()->getId ());
								$customer->loadByEmail ($data->user->identity->emails [0]->value);
								$customer_id = $customer->getId ();
							}
						}
						// If the user does not exist anymore.
						else if (! Mage::getModel ("customer/customer")->load ($customer_id)->getId ()) 
						{
							// Cleanup our table.
							$oneall_entity->delete ();
							$customer_id = null;
						}
						
						// This is a new customer.
						if (empty ($customer_id))
						{
							// Generate email address
							if (isset ($data->user->identity->emails) and is_array ($data->user->identity->emails))
							{
								$email = $data->user->identity->emails [0]->value;
								$email_is_random = false;
							}
							else
							{
								$email = $this->create_random_email ();
								$email_is_random = true;
							}

							// Create a new customer.
							$customer = Mage::getModel ('customer/customer');

							// Generate a password for the customer.
							$password = $customer->generatePassword (8);

							// Setup customer details.
							$first_name = 'unknown';
							if (! empty ($data->user->identity->name->givenName))
							{
								$first_name = $data->user->identity->name->givenName;
							}
							else if (! empty ($data->user->identity->displayName))
							{
								$names = explode (' ', $data->user->identity->displayName);
								$first_name = $names[0];
							}
							else if (! empty($data->user->identity->name->formatted))
							{
								$names = explode (' ', $data->user->identity->name->formatted);
								$first_name = $names[0];
							}
							$last_name = 'unknown';
							if (! empty ($data->user->identity->name->familyName))
							{
								$last_name = $data->user->identity->name->familyName;
							}
							else if (!empty ($data->user->identity->displayName))
							{
								$names = explode (' ', $data->user->identity->displayName);
								if (! empty ($names[1]))
								{
									$last_name = $names[1];
								}
							}
							else if (!empty($data->user->identity->name->formatted))
							{
								$names = explode (' ', $data->user->identity->name->formatted);
								if (! empty ($names[1]))
								{
									$last_name = $names[1];
								}
							}
							$customer->setFirstname ($first_name);
							$customer->setLastname ($last_name);
							$customer->setEmail ($email);
							$customer->setSkipConfirmationIfEmail ($email);
							$customer->setPassword ($password);
							$customer->setPasswordConfirmation ($password);
							$customer->setConfirmation ($password);

							// Validate user details.
							$errors = $customer->validate ();

							// Do we have any errors?
							if (is_array ($errors) && count ($errors) > 0)
							{
								// This would break it for Twitter users as they have no first/lastname
								Mage::getSingleton ('customer/session')->addError (implode (' ', $errors));
								return false;
							}

							// Save user.
							$customer->save ();

							// Send email.
							if (! $email_is_random)
							{
								$customer->sendNewAccountEmail ();
							}

							// Log this user in.
							$customer_id = $customer->getId ();

							// Save OneAll user_token.
							$model = Mage::getModel ('oneall_sociallogin/entity');
							$model->setData ('customer_id', $customer->getId ());
							$model->setData ('user_token', $user_token);
							$model->setData ('identity_token', $identity_token);
							$model->save ();
						}
						// This is an existing customer.
						else
						{
							// Check if we have a user for this user_token.
							if (strlen (Mage::getModel ('oneall_sociallogin/entity')->load ($user_token, 'user_token')->customer_id) == 0)
							{
								// Save OneAll user_token.
								$model = Mage::getModel ('oneall_sociallogin/entity');
								$model->setData ('customer_id', $customer_id);
								$model->setData ('user_token', $user_token);
								$model->setData ('identity_token', $identity_token);
								$model->save ();
							}
						}

						// Login
						if (! empty ($customer_id))
						{
							// Login
							Mage::getSingleton ('customer/session')->loginById ($customer_id);

							// Done
							return true;
						}
					}
				}
			}
		}

		// Not logged in.
		return false;
	}

	/**
	 * Return the list of disabled PHP functions.
	 */
	public function get_disabled_php_functions ()
	{
		$disabled_functions = trim (ini_get ('disable_functions'));
		if (strlen ($disabled_functions) == 0)
		{
			$disabled_functions = array ();
		}
		else
		{
			$disabled_functions = explode (',', $disabled_functions);
			$disabled_functions = array_map ('trim', $disabled_functions);
		}
		return $disabled_functions;
	}

	/**
	 * Send an API request by using the given handler
	 */
	public function do_api_request ($handler, $url, $options = array(), $timeout = 25)
	{
		// FSOCKOPEN
		if ($handler == 'fsockopen')
		{
			return $this->do_fsockopen_request ($url, $options, $timeout);
		}
		// CURL
		else
		{
			return $this->do_curl_request ($url, $options, $timeout);
		}
	}

	/**
	 * Check if fsockopen is available.
	 */
	public function is_fsockopen_available ()
	{
		// Make sure fsockopen has been loaded
		if (function_exists ('fsockopen') and function_exists ('fwrite'))
		{
			// Read the disabled functions
			$disabled_functions = $this->get_disabled_php_functions ();

			// Make sure fsockopen has not been disabled
			if (! in_array ('fsockopen', $disabled_functions) and ! in_array ('fwrite', $disabled_functions))
			{
				// Loaded and enabled
				return true;
			}
		}

		// Not loaded or disabled
		return false;
	}

	/**
	 * Check if fsockopen is enabled and can be used to connect to OneAll.
	 */
	public function is_api_connection_fsockopen_ok ($secure = true)
	{
		if ($this->is_fsockopen_available ())
		{
			$result = $this->do_fsockopen_request (($secure ? 'https' : 'http') . '://www.oneall.com/ping.html');
			if (is_object ($result) and property_exists ($result, 'http_code') and $result->http_code == 200)
			{
				if (property_exists ($result, 'http_data'))
				{
					if (strtolower ($result->http_data) == 'ok')
					{
						return true;
					}
				}
			}
		}
		return false;
	}

	/**
	 * Send an fsockopen request.
	 */
	public function do_fsockopen_request ($url, $options = array(), $timeout = 15)
	{
		// Store the result
		$result = new stdClass ();

		// Make sure that this is a valid URL
		if (($uri = parse_url ($url)) == false)
		{
			$result->http_code = - 1;
			$result->http_data = null;
			$result->http_error = 'invalid_uri';
			return $result;
		}

		// Make sure that we can handle the scheme
		switch ($uri ['scheme'])
		{
			case 'http':
				$port = (isset ($uri ['port']) ? $uri ['port'] : 80);
				$host = ($uri ['host'] . ($port != 80 ? ':' . $port : ''));
				$fp = @fsockopen ($uri ['host'], $port, $errno, $errstr, $timeout);
				break;

			case 'https':
				$port = (isset ($uri ['port']) ? $uri ['port'] : 443);
				$host = ($uri ['host'] . ($port != 443 ? ':' . $port : ''));
				$fp = @fsockopen ('ssl://' . $uri ['host'], $port, $errno, $errstr, $timeout);
				break;

			default:
				$result->http_code = - 1;
				$result->http_data = null;
				$result->http_error = 'invalid_schema';
				return $result;
				break;
		}

		// Make sure that the socket has been opened properly
		if (! $fp)
		{
			$result->http_code = - $errno;
			$result->http_data = null;
			$result->http_error = trim ($errstr);
			return $result;
		}

		// Construct the path to act on
		$path = (isset ($uri ['path']) ? $uri ['path'] : '/');
		if (isset ($uri ['query']))
		{
			$path .= '?' . $uri ['query'];
		}

		// Create HTTP request
		$defaults = array (
			'Host' => "Host: $host",
			'User-Agent' => 'User-Agent: ' . self::OA_USER_AGENT
		);

		// Enable basic authentication
		if (isset ($options ['api_key']) and isset ($options ['api_secret']))
		{
			$defaults ['Authorization'] = 'Authorization: Basic ' . base64_encode ($options ['api_key'] . ":" . $options ['api_secret']);
		}

		// Build and send request
		$request = 'GET ' . $path . " HTTP/1.0\r\n";
		$request .= implode ("\r\n", $defaults);
		$request .= "\r\n\r\n";
		fwrite ($fp, $request);

		// Fetch response
		$response = '';
		while (! feof ($fp))
		{
			$response .= fread ($fp, 1024);
		}

		// Close connection
		fclose ($fp);

		// Parse response
		list ($response_header, $response_body) = explode ("\r\n\r\n", $response, 2);

		// Parse header
		$response_header = preg_split ("/\r\n|\n|\r/", $response_header);
		list ($header_protocol, $header_code, $header_status_message) = explode (' ', trim (array_shift ($response_header)), 3);

		// Build result
		$result->http_code = $header_code;
		$result->http_data = $response_body;

		// Done
		return $result;
	}

	/**
	 * Check if cURL has been loaded and is enabled.
	 */
	public function is_curl_available ()
	{
		// Make sure cURL has been loaded.
		if (in_array ('curl', get_loaded_extensions ()) and function_exists ('curl_init') and function_exists ('curl_exec'))
		{
			// Read the disabled functions.
			$disabled_functions = $this->get_disabled_php_functions ();

			// Make sure CURL has not been disabled.
			if (! in_array ('curl_init', $disabled_functions) and ! in_array ('curl_exec', $disabled_functions))
			{
				// Loaded and enabled.
				return true;
			}
		}

		// Not loaded or disabled.
		return false;
	}

	/**
	 * Check if CURL is available and can be used to connect to OneAll
	 */
	public function is_api_connection_curl_ok ($secure = true)
	{
		// Is CURL available and enabled?
		if ($this->is_curl_available ())
		{
			// Make a request to the OneAll API.
			$result = $this->do_curl_request (($secure ? 'https' : 'http') . '://www.oneall.com/ping.html');
			if (is_object ($result) and property_exists ($result, 'http_code') and $result->http_code == 200)
			{
				if (property_exists ($result, 'http_data'))
				{
					if (strtolower ($result->http_data) == 'ok')
					{
						return true;
					}
				}
			}
		}
		return false;
	}

	/**
	 * Send a CURL request.
	 */
	public function do_curl_request ($url, $options = array(), $timeout = 15)
	{
		// Store the result
		$result = new stdClass ();

		// Send request
		$curl = curl_init ();
		curl_setopt ($curl, CURLOPT_URL, $url);
		curl_setopt ($curl, CURLOPT_HEADER, 0);
		curl_setopt ($curl, CURLOPT_TIMEOUT, $timeout);
		curl_setopt ($curl, CURLOPT_VERBOSE, 0);
		curl_setopt ($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt ($curl, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt ($curl, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt ($curl, CURLOPT_USERAGENT, self::OA_USER_AGENT);

		// Basic AUTH?
		if (isset ($options ['api_key']) and isset ($options ['api_secret']))
		{
			curl_setopt ($curl, CURLOPT_USERPWD, $options ['api_key'] . ":" . $options ['api_secret']);
		}

		// Make request
		if (($http_data = curl_exec ($curl)) !== false)
		{
			$result->http_code = curl_getinfo ($curl, CURLINFO_HTTP_CODE);
			$result->http_data = $http_data;
			$result->http_error = null;
		}
		else
		{
			$result->http_code = - 1;
			$result->http_data = null;
			$result->http_error = curl_error ($curl);
		}

		// Done
		return $result;
	}
}
