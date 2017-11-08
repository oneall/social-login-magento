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

// Verifies the API Settings
class OneAll_SocialLogin_Model_Apiverify
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

  	var button = document.getElementById("oneall_sociallogin_general_verify");
		button.value = "Verify API Settings ";
		button.onclick = function ()
    {
			verify_api_settings();
    }


		var verify_ajax = function (url, callback_function)
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


  var verify_api_settings = function ()
  {
  	var e, result_container, api_connection_handler, api_port, api_key, api_secret, api_connection_handler;

  	e = document.getElementById('oneall_sociallogin_connection_handler');
		api_connection_handler = e.options[e.selectedIndex].value;

		e = document.getElementById('oneall_sociallogin_connection_port');
		api_connection_port = e.options[e.selectedIndex].value;

		api_key =  document.getElementById('oneall_sociallogin_general_key').value;
		api_secret = document.getElementById('oneall_sociallogin_general_secret').value;
		api_subdomain = document.getElementById('oneall_sociallogin_general_subdomain').value;

		var result_container = document.getElementById('oa_social_login_api_verify_result');
		result_container.innerHTML = 'Loading ...';
    verify_ajax('{$base_url}social/ajax/verify?api_key=' + api_key + '&api_subdomain=' + api_subdomain + '&api_secret='+  api_secret  +'&api_connection_handler='+  api_connection_handler +'&api_connection_port=' + api_connection_port, verify_complete);
  }


  var verify_complete = function (text)
  {
    var result_container;

 		result_container = document.getElementById('oa_social_login_api_verify_result');

		if (text == 'error_selected_handler_faulty')
		{
			result_container.innerHTML = '<b style="color:red">The connection handler does not work!</b>';
		}
		else if (text == 'error_not_all_fields_filled_out')
		{
			result_container.innerHTML = '<b style="color:red">Please fill out each of the fields above.</b>';
		}
		else if (text == 'error_subdomain_wrong')
		{
				result_container.innerHTML = '<b style="color:red">The API subdomain does not seem to exist!</b>';
		}
		else if (text == 'error_subdomain_wrong_syntax')
		{
			result_container.innerHTML = '<b style="color:red">The API subdomain does not seem to exist!</b>';
		}
		else if (text == 'error_communication')
		{
			result_container.innerHTML = '<b style="color:red">Could not establish a communication with OneAll.';
		}
		else if (text == 'error_authentication_credentials_wrong')
		{
			result_container.innerHTML = '<b style="color:red">The API keys are invalid!</b>';
		}
		else
		{
			result_container.innerHTML = '<b style="color:#3d6611">The API settings are correct !</b>';
		}
  }

  </script>

  <div id="oa_social_login_api_verify_result"></div>
HTML;
	}
}