<?php

namespace mako;

use \mako\Config;
use \mako\Request;
use \mako\RequestException;
use \mako\Response;
use \mako\ErrorHandler;
use \ErrorException;

/**
* Mako.
*
* @author     Frederic G. Østby
* @copyright  (c) 2008-2012 Frederic G. Østby
* @license    http://www.makoframework.com/license
*/

class Mako
{
	//---------------------------------------------
	// Class variables
	//---------------------------------------------
	
	// Nothing here
	
	//---------------------------------------------
	// Class constructor, destructor etc ...
	//---------------------------------------------
	
	/**
	* Protected constructor since this is a static class.
	*
	* @access  protected
	*/
	
	protected function __construct()
	{
		// Nothing here
	}
	
	//---------------------------------------------
	// Class methods
	//---------------------------------------------
	
	/**
	* Executes request and sends response.
	*
	* @access  public
	* @param   string  $route  (optional) URL segments passed to the request handler.
	*/
	
	public static function run($route = null)
	{
		// Start output buffering and send default header

		ob_start();

		header('Content-Type: text/html; charset=' . MAKO_CHARSET);

		// Setup error handling if enabled
			
		if(Config::get('mako.error_handler.enable') === true)
		{
			register_shutdown_function(function()
			{
				$e = error_get_last();
				
				if($e !== null && (error_reporting() & $e['type']) !== 0)
				{
					ErrorHandler::exception(new ErrorException($e['message'], $e['type'], 0, $e['file'], $e['line']));

					exit(1);
				}
			});
			
			set_exception_handler(function($e)
			{
				ErrorHandler::exception($e);
			});
		}			
					
		// Removes slashes added to the superglobals by magic quotes

		if(MAKO_MAGIC_QUOTES === 1)
		{
			$superglobals = array(&$_GET, &$_POST, &$_COOKIE, &$_REQUEST);

			foreach($superglobals as &$superglobal)
			{
				array_walk_recursive($superglobal, function(&$value, $key)
				{
					$value = stripslashes($value);
				});
			}

			unset($superglobals);
		}

		// Execute the request

		try
		{
			Request::factory($route)->execute()->send();
		}
		catch(RequestException $e)
		{
			Response::factory(new View('_mako_.errors.' . $e->getMessage()))->send($e->getMessage());
		}	
	}
}

/** -------------------- End of file --------------------**/