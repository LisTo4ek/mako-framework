<?php

namespace mako
{
	use \Mako;
	use \RuntimeException;

	/**
	* Simple Redis client based on protocol specification at http://redis.io/topics/protocol.
	*
	* @author     Frederic G. Østby
	* @copyright  (c) 2008-2011 Frederic G. Østby
	* @license    http://www.makoframework.com/license
	*/

	class Redis
	{
		//---------------------------------------------
		// Class variables
		//---------------------------------------------

		/**
		* Command terminator.
		*/

		const CRLF = "\r\n";

		/**
		* Socket connection.
		*/

		protected $connection;

		//---------------------------------------------
		// Class constructor, destructor etc ...
		//---------------------------------------------

		/**
		* Constructor.
		*
		* @access  public
		* @param   string  (optional) Redis configuration name
		*/

		public function __construct($name = null)
		{
			$config = Mako::config('redis');

			$name = ($name === null) ? $config['default'] : $name;

			if(isset($config['configurations'][$name]) === false)
			{
				throw new RuntimeException(__CLASS__ . ": '{$name}' has not been defined in the growl configuration.");
			}

			$this->connection = @fsockopen('tcp://' . $config['configurations'][$name]['host'], $config['configurations'][$name]['port'], $errNo, $errStr);

			if(!$this->connection)
			{
				throw new RuntimeException(__CLASS__ . ": {$errStr}.");
			}

			if(!empty($config['configurations'][$name]['password']))
			{
				if($this->auth($config['configurations'][$name]['password']) !== 'OK')
				{
					throw new RuntimeException(__CLASS__ . ": Invalid password.");
				}
			}
		}

		/**
		* Factory method making method chaining possible right off the bat.
		*
		* @access  public
		* @param   string  (optional) Redis configuration name
		* @return  Redis
		*/

		public static function factory($name = null)
		{
			return new static($name);
		}

		/**
		* Destructor.
		*
		* @access  public
		*/

		public function __destruct()
		{
			if(is_resource($this->connection))
			{
				fclose($this->connection);	
			}
		}

		//---------------------------------------------
		// Class methods
		//---------------------------------------------

		/**
		* Sends command to Redis server and returns response.
		*
		* @access  public
		* @param   string  Command name
		* @param   array   Command parameters
		* @return  mixed  
		*/

		public function __call($name, $args)
		{
			// Build command

			array_unshift($args, strtoupper($name));

			$command = '*' . count($args) . static::CRLF;

			foreach($args as $arg)
			{
				$command .= '$' . strlen($arg) . static::CRLF . $arg . static::CRLF;
			}

			// Send command to server

			fwrite($this->connection, $command);

			$response = trim(fgets($this->connection));

			// Handle response

			switch(substr($response, 0, 1))
			{
				case '-': // error message
					throw new RuntimeException(__CLASS__ . ": " . substr($response, 5));
				break;
				case '+': // single line reply
				case ':': // integer number reply
					return substr($response, 1);
				break;
				case '$': // bulk reply
					if($response === '$-1')
					{
						return null;
					}
					else
					{
						$length = substr($response, 1) + 1;

						return fgets($this->connection, $length);
					}
				break;
				case '*': // multi-bulk reply
					if($response === '*-1' || $response === '*0')
					{
						return null;
					}

					$data = array();

					$count = substr($response, 1);

					for($i = 0; $i < $count; $i++)
					{
						$length = substr(trim(fgets($this->connection)), 1) + 1;

						$data[] = trim(fgets($this->connection, $length + strlen(static::CRLF)));
					}

					return $data;
				break;
				default:
					throw new RuntimeException(__CLASS__ . ": Unable to handle server response.");
			}
		}
	}
}

/** -------------------- End of file --------------------**/