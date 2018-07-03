<?php

class Imaginato_SocialLogin_Helper_Data extends OneAll_SocialLogin_Helper_Data
{
	/**
	 * Handle the callback from OneAll.
	 */
	public function handle_api_callback ()
	{
		// Read URL parameters
		$action = Mage::app ()->getRequest ()->getParam ('oa_action');
		$connection_token = Mage::app ()->getRequest ()->getParam ('connection_token');

		// Callback Handler
		if (strtolower ($action) == 'social_login' and ! empty ($connection_token))
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

						// The source name
						$source_name = $data->user->identity->source->name;

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
							
							// Reset customer id
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
							//$customer->setSkipConfirmationIfEmail ($email);
							$customer->setPassword ($password);
							$customer->setPasswordConfirmation ($password);
							$customer->setConfirmation ($password);
							$customer->setSignupResource($source_name);
							// Validate user details.
							$errors = $customer->validate ();

							// Do we have any errors?
							if (is_array ($errors) && count ($errors) > 0)
							{
								Mage::getSingleton ('core/session')->addError (implode (' ', $errors));
								return false;
							}

								// Save user.
								$customer->save ();
								$customer_id = $customer->getId ();
								
								// Save OneAll user_token.
								$model = Mage::getModel ('oneall_sociallogin/entity');
								$model->setData ('customer_id', $customer->getId ());
								$model->setData ('user_token', $user_token);
								$model->setData ('identity_token', $identity_token);
								$model->save ();
								
								// Send email.
								if (! $email_is_random)
								{
									// Site requires email confirmation.
									if ($customer->isConfirmationRequired ())
									{
										$customer->sendNewAccountEmail ('confirmation');
										Mage::getSingleton ('core/session')->addSuccess (
												__ ('Account confirmation is required. Please, check your email for the confirmation link. To resend the confirmation email please <a href="%s">click here</a>.',
												Mage::helper ('customer')->getEmailConfirmationUrl ($customer->getEmail ())));
										return false;
									}
									else
									{
										$customer->sendNewAccountEmail ('registered');
									}
								}
								// No email found in identity, but email confirmation required.
								else if ($customer->isConfirmationRequired ())
								{
										Mage::getSingleton ('core/session')->addError (
												__ ('Account confirmation by email is required. To provide an email address, <a href="%s">click here</a>.',
												Mage::helper ('customer')->getEmailConfirmationUrl ('')));
										return false;
								}
							}
						// This is an existing customer.
						else
						{
							// Check if we have a user for this user_token.
							if (strlen (Mage::getModel ('oneall_sociallogin/entity')->load($user_token, 'user_token')->customer_id) == 0)
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

							if (!Mage::getStoreConfigFlag(Mage_Customer_Helper_Data::XML_PATH_CUSTOMER_STARTUP_REDIRECT_TO_DASHBOARD)) {

								$referer = $this->_getRequest()->getParam(Mage_Customer_Helper_Data::REFERER_QUERY_PARAM_NAME);
								if ($referer) {
									// Rebuild referer URL to handle the case when SID was changed
									$referer = Mage::getModel('core/url')->getRebuiltUrl(Mage::helper('core')->urlDecode($referer));

									if (strpos($referer, 'http') !== false) {
										if ((strpos($referer, Mage::app()->getStore()->getBaseUrl()) === 0)
											|| (strpos($referer, Mage::app()->getStore()->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK, true)) === 0)
										) {
											Mage::app()->getResponse()->setRedirect($referer)->sendResponse();
										}
									}
								}
							}
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
}