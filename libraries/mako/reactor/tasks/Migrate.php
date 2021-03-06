<?php

namespace mako\reactor\tasks;

use \StdClass;
use \mako\CLI;
use \mako\Database;

/**
* Database migrations.
*
* @author     Frederic G. Østby
* @copyright  (c) 2008-2012 Frederic G. Østby
* @license    http://www.makoframework.com/license
*/

class Migrate extends \mako\reactor\Task
{
	//---------------------------------------------
	// Class variables
	//---------------------------------------------

	/**
	* Database connection.
	*
	* @var mako\database\Connection
	*/

	protected $connection;

	//---------------------------------------------
	// Class constructor, destructor etc ...
	//---------------------------------------------

	/**
	* Constructor.
	*
	* @access  public
	*/

	public function __construct()
	{
		$this->connection = Database::connection();
	}

	//---------------------------------------------
	// Class methods
	//---------------------------------------------

	/**
	* Returns a query builder instance.
	*
	* @access  protected
	* @return  mako\database\Query
	*/

	protected function table()
	{
		return $this->connection->table('mako_migrations');
	}

	/**
	* Returns array of all outstanding migrations.
	*
	* @access  protected
	*/

	protected function getOutstanding()
	{
		$migrations = array();

		// Get application migrations

		$files = glob(MAKO_APPLICATION . '/migrations/*.php');

		foreach($files as $file)
		{
			$migration = new StdClass();
			
			$migration->version = basename($file, '.php');
			$migration->package = '';

			$migrations[] = $migration;
		}

		// Get package migrations

		$packages = glob(MAKO_PACKAGES . '/*');

		foreach($packages as $package)
		{
			if(is_dir($package))
			{
				$files = glob($package . '/migrations/*.php');

				foreach($files as $file)
				{
					$migration = new StdClass();

					$migration->version = basename($file, '.php');
					$migration->package = basename($package);

					$migrations[] = $migration;
				}
			}
		}

		// Remove migrations that have already been executed

		foreach($this->table()->all() as $ran)
		{
			foreach($migrations as $key => $migration)
			{
				if($ran->package === $migration->package && $ran->version === $migration->version)
				{
					unset($migrations[$key]);
				}
			}
		}

		// Sort remaining migrations so that they get executed in the right order

		usort($migrations, function($a, $b)
		{
			return strcmp($a->version, $b->version);
		});

		return $migrations;
	}

	/**
	* Displays the number of outstanding migrations.
	*
	* @access  public
	*/

	public function status()
	{
		if(($count = count($this->getOutstanding())) > 0)
		{
			CLI::stdout(sprintf(($count === 1 ? 'There is %d outstanding migration.' : 'There are %d outstanding migrations.'), $count));
		}
		else
		{
			CLI::stdout('There are no outstanding migrations.');
		}
	}

	/**
	* Returns a migration instance.
	*
	* @access  protected
	* @param   StdClass   $migration  Migration object
	* @return  Migration
	*/

	protected function resolve($migration)
	{
		$file = $migration->version;

		if(!empty($migration->package))
		{
			$file = $migration->package . '::' . $file;
		}

		include mako_path('migrations', $file);

		$class = '\Migration_' . $migration->version;

		return new $class();
	}

	/**
	* Runs all outstanding migrations.
	*
	* @access  public
	*/

	public function run()
	{
		$migrations = $this->getOutstanding();

		if(empty($migrations))
		{
			return CLI::stdout('There are no outstanding migrations.');
		}

		$batch = $this->table()->max('batch') + 1;

		foreach($migrations as $migration)
		{
			$this->resolve($migration)->up();

			$this->table()->insert(array('batch' => $batch, 'package' => $migration->package, 'version' => $migration->version));

			$name = $migration->version;

			if(!empty($migration->package))
			{
				$name = $migration->package . '::' . $name;
			}

			CLI::stdout('Ran the ' . $name . ' migration.');
		}
	}

	/**
	* Rolls back the last migration batch.
	*
	* @access  public
	*/

	public function rollback()
	{
		$migrations = $this->table()
			->where('batch', '=', $this->table()->max('batch'))
			->orderBy('version', 'desc')
			->all(array('version', 'package'));

		if(empty($migrations))
		{
			CLI::stdout('There are no migrations to roll back.');

			return false;
		}

		foreach($migrations as $migration)
		{
			$this->resolve($migration)->down();

			$this->table()->where('version', '=', $migration->version)->delete();

			$name = $migration->version;

			if(!empty($migration->package))
			{
				$name = $migration->package . '::' . $name;
			}

			CLI::stdout('Rolled back the ' . $name . ' migration.');
		}

		return true;
	}

	/**
	* Rolls back all migrations.
	*
	* @access  public
	*/

	public function reset()
	{
		while($this->rollback())
		{
			// Rolling back all migrations
		}
	}

	/**
	* Creates the migration log table.
	*
	* @access  public
	*/

	public function install()
	{
		/*CREATE TABLE `mako_migrations` (
		  `batch` int(10) unsigned NOT NULL,
		  `package` varchar(255) NOT NULL,
		  `version` varchar(255) NOT NULL
		);*/

		CLI::stderr('Migration installation has not been implemented yet.');
	}

	/**
	* Creates a migration template.
	*
	* @access  public
	* @param   string  $package  (optional) Package name
	*/

	public function create($package = '')
	{
		// Get file path

		$file = $version = gmdate('YmdHis');

		if(!empty($package))
		{
			$file = $package . '::' . $file;
		}

		$file = mako_path('migrations', $file);

		// Create migration

		$migration = str_replace('{{version}}', $version, file_get_contents(__DIR__ . '/migrate/migration.tpl'));

		if(!@file_put_contents($file, $migration))
		{
			return CLI::stderr('Failed to create migration. Make sure that the migrations directory is writable.');
		}

		CLI::stdout(sprintf('Migration created at "%s".', $file));
	}
}

/** -------------------- End of file --------------------**/