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

// Autodetects the API connection handler
class OneAll_SocialLogin_Model_Apiautodetect
{
	public function toOptionArray ()
	{
		$helper = Mage::helper ('oneall_sociallogin');
	}
	public function getCommentText ()
	{
		$base_url = Mage::getBaseUrl ();

		return <<<HTML

  <script language="javascript">

    var button = document.getElementById("oneall_sociallogin_connection_autodetect");
    button.value = "Autodetect API Connection";
    button.onclick = function ()
    {
			autodetect_api_connection();
    }

		var autodetect_api_connection = function ()
		{
			var div = document.getElementById('oa_social_login_api_test_result');
			div.innerHTML = 'Loading ...';
			autodetect_ajax('{$base_url}social/ajax', autodetect_complete);
		}

		var autodetect_ajax = function (url, callback_function)
    {
			var request =  new XMLHttpRequest();

			request.open("GET", url, true);
			request.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
			request.onreadystatechange = function()
			{
				if (request.readyState == 4 && request.status == 200)
				{
					if (request.responseText)
					{
						callback_function(request.responseText);
        	}
      	}
    	};
			request.send();
  	}

		var autodetect_complete = function (text)
		{
			var is_success,result_container, e, handler, port;

			result_container	= document.getElementById('oa_social_login_api_test_result');

			handler = document.getElementById('oneall_sociallogin_connection_handler');
			handler.value = '';

			port = document.getElementById('oneall_sociallogin_connection_port');
			port.value = '';

			/* CURL detected, HTTPS */
			if (text == 'success_autodetect_api_curl_https')
			{
				is_success = true;
				result_container.innerHTML =  '<b style="color:#3d6611">Detected CURL on Port 443 !</b>';
				handler.value = 'curl';
				port.value = '443';
			}
			/* CURL detected, HTTP */
			else if (text == 'success_autodetect_api_curl_http')
			{
				is_success = true;
				result_container.innerHTML =  '<b style="color:#3d6611">Detected CURL on Port 80 !</b>';
				handler.value = 'curl';
				port.value = '80';
			}
			/* CURL detected, ports closed */
			else if (text == 'error_autodetect_api_curl_ports_blocked')
			{
				is_success = false;
				result_container.innerHTML =  '<b style="color:red">Detected CURL but both ports are blocked!</b>';
				handler.value = 'curl'
			}
			/* FSOCKOPEN detected, HTTPS */
			else if (text == 'success_autodetect_api_fsockopen_https')
			{
				is_success = true;
				result_container.innerHTML =  '<b style="color:#3d6611">Detected FSOCKOPEN on Port 443!</b>';
				handler.value = 'fsockopen';
				port.value = '80';
			}
			/* FSOCKOPEN detected, HTTP */
			else if (text == 'success_autodetect_api_fsockopen_http')
			{
				is_success = true;
				result_container.innerHTML =  '<b style="color:#3d6611">Detected FSOCKOPEN on Port 80!</b>';
				handler.value = 'fsockopen';
				port.value = '80';
			}
			/* FSOCKOPEN detected, ports closed */
			else if (text == 'error_autodetect_api_fsockopen_ports_blocked')
			{
				is_success = false;
				result_container.innerHTML =  '<b style="color:red">Detected FSOCKOPEN  but both ports are blocked!</b>';
				handler.value = 'fsockopen';
			}
			/* No handler detected */
			else
			{
				is_success = false;
				result_container.innerHTML =  '<b style="color:red">No  connection handler detected!</b>';
			}
   	}

  </script>

  <div id="oa_social_login_api_test_result"></div>
HTML;
	}
}
