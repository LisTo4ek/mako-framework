<?php

namespace mako\cache;

use \Memcached as PHP_Memcached;
use \RuntimeException;

/**
* Memcached adapter.
*
* @author     Frederic G. Østby
* @copyright  (c) 2008-2012 Frederic G. Østby
* @license    http://www.makoframework.com/license
*/

class Memcached extends \mako\cache\Adapter
{
	//---------------------------------------------
	// Class variables
	//---------------------------------------------

	/**
	* Memcached object.
	*
	* @var Memcached
	*/

	protected $memcached;

	//---------------------------------------------
	// Class constructor, destructor etc ...
	//---------------------------------------------

	/**
	* Constructor.
	*
	* @access  public
	* @param   array   $config  Configuration
	*/

	public function __construct(array $config)
	{
		parent::__construct($config['identifier']);
		
		if(class_exists('\Memcached', false) === false)
		{
			throw new RuntimeException(vsprintf("%s(): Memcached is not available.", array(__METHOD__)));
		}
		
		$this->memcached = new PHP_Memcached();
		
		if($config['compress_data'] !== 1)
		{
			$this->memcached->setOption(PHP_Memcached::OPT_CONNECT_TIMEOUT, ($config['timeout'] * 1000)); // Multiply by 1000 to convert to ms
		}

		if($config['compress_data'] === false)
		{
			$this->memcached->setOption(PHP_Memcached::OPT_COMPRESSION, false);
		}

		// Add servers to the connection pool

		foreach($config['servers'] as $server)
		{
			$this->memcached->addServer($server['server'], $server['port'], $server['weight']);
		}
	}

	/**
	* Destructor.
	*
	* @access  public
	*/

	public function __destruct()
	{
		$this->memcached = null;
	}

	//---------------------------------------------
	// Class methods
	//---------------------------------------------

	/**
	* Store variable in the cache.
	*
	* @access  public
	* @param   string   $key    Cache key
	* @param   mixed    $value  The variable to store
	* @param   int      $ttl    (optional) Time to live
	* @return  boolean
	*/

	public function write($key, $value, $ttl = 0)
	{
		if($ttl !== 0)
		{
			$ttl += time();
		}

		if($this->memcached->replace("{$this->identifier}_{$key}", $value, $ttl) === false)
		{
			return $this->memcached->set("{$this->identifier}_{$key}", $value, $ttl);
		}

		return true;
	}

	/**
	* Fetch variable from the cache.
	*
	* @access  public
	* @param   string  $key  Cache key
	* @return  mixed
	*/

	public function read($key)
	{
		return $this->memcached->get("{$this->identifier}_{$key}");
	}

	/**
	* Delete a variable from the cache.
	*
	* @access  public
	* @param   string   $key  Cache key
	* @return  boolean
	*/

	public function delete($key)
	{
		return $this->memcached->delete("{$this->identifier}_{$key}", 0);
	}

	/**
	* Clears the user cache.
	*
	* @access  public
	* @return  boolean
	*/

	public function clear()
	{
		return $this->memcached->flush();
	}
}

/** -------------------- End of file --------------------**/