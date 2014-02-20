<?php
/*
 * Paiement Bancaire
 * module de paiement bancaire multi prestataires
 * stockage des transactions
 *
 * Auteurs :
 * Cedric Morin, Nursit.com
 * (c) 2014 - Distribue sous licence GNU/GPL
 *
 */

session_start();

/****************************************************
Constantes par defaut pour le paiement PAYPAL, a configurer dans mes_options
This is the configuration file for the samples.This file
defines the parameters needed to make an API call.

PayPal includes the following API Signature for making API
calls to the PayPal sandbox:

API Username 	sdk-three_api1.sdk.com
API Password 	QFZCWN5HZM8VBG7Q
API Signature 	A-IzJhZZjhg29XQ2qnhapuwxIDzyAZQ92FRP5dqBzVesOkzbdUONzmOU

Called by CallerService.php.
****************************************************/

/**
# API user: The user that is identified as making the call. you can
# also use your own API username that you created on PayPal�s sandbox
# or the PayPal live site
# Sandbox : sdk-three_api1.sdk.com
*/
if (!defined('_PAYPAL_API_USERNAME'))
	define('_PAYPAL_API_USERNAME', $GLOBALS['config_bank_paiement']['config_paypalexpress']['API_USERNAME']);

/**
# API_password: The password associated with the API user
# If you are using your own API username, enter the API password that
# was generated by PayPal below
# IMPORTANT - HAVING YOUR API PASSWORD INCLUDED IN THE MANNER IS NOT
# SECURE, AND ITS ONLY BEING SHOWN THIS WAY FOR TESTING PURPOSES
# Sandbox : QFZCWN5HZM8VBG7Q
*/

if (!defined('_PAYPAL_API_PASSWORD'))
	define('_PAYPAL_API_PASSWORD', $GLOBALS['config_bank_paiement']['config_paypalexpress']['API_PASSWORD']);

/**
# API_Signature:The Signature associated with the API user. which is generated by paypal.
# Sandbox : A.d9eRKfd1yVkRrtmMfCFLTqa6M9AyodL0SJkhYztxUi8W9pCXF6.4NI
*/

if (!defined('_PAYPAL_API_SIGNATURE'))
	define('_PAYPAL_API_SIGNATURE', $GLOBALS['config_bank_paiement']['config_paypalexpress']['API_SIGNATURE']);

$sandbox = (defined('_PAYPAL_API_SANDBOX')?true:false);

/**
# Endpoint: this is the server URL which you have to connect for submitting your API request.
*/
if (!defined('_PAYPAL_API_ENDPOINT'))
	define('_PAYPAL_API_ENDPOINT', $sandbox?'https://api-3t.sandbox.paypal.com:443/nvp':'https://api-3t.paypal.com:443/nvp');

/* Define the PayPal URL. This is the URL that the buyer is
   first sent to to authorize payment with their paypal account
   change the URL depending if you are testing on the sandbox
   or going to the live PayPal site
   For the sandbox, the URL is
   https://www.sandbox.paypal.com/webscr&cmd=_express-checkout&token=
   For the live site, the URL is
   https://www.paypal.com/webscr&cmd=_express-checkout&token=
   */
if (!defined('_PAYPAL_PAYPAL_URL'))
	define('_PAYPAL_API_PAYPAL_URL', $sandbox?'https://www.sandbox.paypal.com/webscr&cmd=_express-checkout':'https://www.paypal.com/webscr&cmd=_express-checkout');


/**
USE_PROXY: Set this variable to TRUE to route all the API requests through proxy.
like define('USE_PROXY',TRUE);
*/
define('_PAYPAL_API_USE_PROXY',FALSE);
/**
PROXY_HOST: Set the host name or the IP address of proxy server.
PROXY_PORT: Set proxy port.

PROXY_HOST and PROXY_PORT will be read only if USE_PROXY is set to TRUE
*/
define('_PAYPAL_API_PROXY_HOST', '127.0.0.1');
define('_PAYPAL_API_PROXY_PORT', '808');


/**
# Version: this is the API version in the request.
# It is a mandatory parameter for each API request.
# The only supported value at this time is 2.3
*/

define('_PAYPAL_API_VERSION', '3.0');



function action_paypalexpress_order_dist($id_transaction){
	if (is_null($id_transaction)){
		$securiser_action = charger_fonction('securiser_action','inc');
		$id_transaction = $securiser_action();
	}

/* An express checkout transaction starts with a token, that
   identifies to PayPal your transaction
   In this example, when the script sees a token, the script
   knows that the buyer has already authorized payment through
   paypal.  If no token was found, the action is to send the buyer
   to PayPal to first authorize payment
   */

	if(!$token = _request('token')) {

		include_spip("presta/payalexpress/inc/paypalexpress");
		$redirect = bank_paypalexpress_order_init($id_transaction,_request('url_confirm'));

		if (!$redirect)
			$redirect = url_de_base();
	}

	$GLOBALS['redirect'] = $redirect;
}


function action_paypalexpress_checkoutpayment_dist($arg){
	if (is_null($arg)){
		$securiser_action = charger_fonction('securiser_action','inc');
		$arg = $securiser_action();
	}

	include_spip("presta/payalexpress/inc/paypalexpress");
	$res = bank_paypalexpress_checkoutpayment();

	list($id_transaction, $success) = $res;

	include_spip("action/bank_response");
	redirige_apres_retour_transaction("paypalexpress","acte",$success,$id_transaction);
}

