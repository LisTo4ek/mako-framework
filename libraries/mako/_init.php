<?php

//------------------------------------------------------------------------------------------
// Define some constants
//------------------------------------------------------------------------------------------

define('MAKO_VERSION', '2.3.2');
define('MAKO_START', microtime(true));
define('MAKO_MAGIC_QUOTES', get_magic_quotes_gpc());
define('MAKO_IS_WINDOWS', (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN'));
define('MAKO_APPLICATION', MAKO_APPLICATION_PATH . '/' . MAKO_APPLICATION_NAME);
define('MAKO_APPLICATION_ID', md5(MAKO_APPLICATION));
define('MAKO_PACKAGES', MAKO_APPLICATION . '/packages');

//------------------------------------------------------------------------------------------
// Convert all errors to ErrorExceptions and set path for error logs
//------------------------------------------------------------------------------------------

set_error_handler(function($code, $message, $file, $line)
{
	if((error_reporting() & $code) !== 0)
	{
		throw new ErrorException($message, $code, 0, $file, $line);
	}

	return true;
});

ini_set('error_log', MAKO_APPLICATION . '/storage/logs/error_' . gmdate('Y_m_d') . '.log');

//------------------------------------------------------------------------------------------
// Map all core classes and set up autoloading
//------------------------------------------------------------------------------------------

include MAKO_LIBRARIES_PATH . '/mako/ClassLoader.php';

mako\ClassLoader::addClasses(array
(
	'mako\Arr'                               => MAKO_LIBRARIES_PATH . '/mako/Arr.php',
	'mako\Assets'                            => MAKO_LIBRARIES_PATH . '/mako/Assets.php',
	'mako\Cache'                             => MAKO_LIBRARIES_PATH . '/mako/Cache.php',
	'mako\cache\APC'                         => MAKO_LIBRARIES_PATH . '/mako/cache/APC.php',
	'mako\cache\Adapter'                     => MAKO_LIBRARIES_PATH . '/mako/cache/Adapter.php',
	'mako\cache\File'                        => MAKO_LIBRARIES_PATH . '/mako/cache/File.php',
	'mako\cache\Memcache'                    => MAKO_LIBRARIES_PATH . '/mako/cache/Memcache.php',
	'mako\cache\Memcached'                   => MAKO_LIBRARIES_PATH . '/mako/cache/Memcached.php',
	'mako\cache\Memory'                      => MAKO_LIBRARIES_PATH . '/mako/cache/Memory.php',
	'mako\cache\Redis'                       => MAKO_LIBRARIES_PATH . '/mako/cache/Redis.php',
	'mako\cache\SQLite'                      => MAKO_LIBRARIES_PATH . '/mako/cache/SQLite.php',
	'mako\cache\WinCache'                    => MAKO_LIBRARIES_PATH . '/mako/cache/WinCache.php',
	'mako\cache\XCache'                      => MAKO_LIBRARIES_PATH . '/mako/cache/XCache.php',
	'mako\cache\ZendDisk'                    => MAKO_LIBRARIES_PATH . '/mako/cache/ZendDisk.php',
	'mako\cache\ZendMemory'                  => MAKO_LIBRARIES_PATH . '/mako/cache/ZendMemory.php',
	//'mako\ClassLoader'                       => MAKO_LIBRARIES_PATH . '/mako/ClassLoader.php',
	'mako\Charset'                           => MAKO_LIBRARIES_PATH . '/mako/Charset.php',
	'mako\CLI'                               => MAKO_LIBRARIES_PATH . '/mako/CLI.php',
	'mako\Config'                            => MAKO_LIBRARIES_PATH . '/mako/Config.php',
	'mako\Controller'                        => MAKO_LIBRARIES_PATH . '/mako/Controller.php',
	'mako\controller\Rest'                   => MAKO_LIBRARIES_PATH . '/mako/controller/Rest.php',
	'mako\controller\View'                   => MAKO_LIBRARIES_PATH . '/mako/controller/View.php',
	'mako\Cookie'                            => MAKO_LIBRARIES_PATH . '/mako/Cookie.php',
	'mako\Crypto'                            => MAKO_LIBRARIES_PATH . '/mako/Crypto.php',
	'mako\crypto\Adapter'                    => MAKO_LIBRARIES_PATH . '/mako/crypto/Adapter.php',
	'mako\crypto\Mcrypt'                     => MAKO_LIBRARIES_PATH . '/mako/crypto/Mcrypt.php',
	'mako\crypto\OpenSSL'                    => MAKO_LIBRARIES_PATH . '/mako/crypto/OpenSSL.php',
	'mako\Database'                          => MAKO_LIBRARIES_PATH . '/mako/Database.php',
	'mako\database\Connection'               => MAKO_LIBRARIES_PATH . '/mako/database/Connection.php',
	'mako\database\Query'                    => MAKO_LIBRARIES_PATH . '/mako/database/Query.php',
	'mako\database\query\Compiler'           => MAKO_LIBRARIES_PATH . '/mako/database/query/Compiler.php',
	'mako\database\query\Join'               => MAKO_LIBRARIES_PATH . '/mako/database/query/Join.php',
	'mako\database\query\Subquery'           => MAKO_LIBRARIES_PATH . '/mako/database/query/Subquery.php',
	'mako\database\query\compiler\DB2'       => MAKO_LIBRARIES_PATH . '/mako/database/query/compiler/DB2.php',
	'mako\database\query\compiler\Firebird'  => MAKO_LIBRARIES_PATH . '/mako/database/query/compiler/Firebird.php',
	'mako\database\query\compiler\MySQL'     => MAKO_LIBRARIES_PATH . '/mako/database/query/compiler/MySQL.php',
	'mako\database\query\compiler\Oracle'    => MAKO_LIBRARIES_PATH . '/mako/database/query/compiler/Oracle.php',
	'mako\database\query\compiler\SQLServer' => MAKO_LIBRARIES_PATH . '/mako/database/query/compiler/SQLServer.php',
	'mako\database\query\Raw'                => MAKO_LIBRARIES_PATH . '/mako/database/query/Raw.php',
	'mako\DateTime'                          => MAKO_LIBRARIES_PATH . '/mako/DateTime.php',
	'mako\ErrorHandler'                      => MAKO_LIBRARIES_PATH . '/mako/ErrorHandler.php',
	'mako\Event'                             => MAKO_LIBRARIES_PATH . '/mako/Event.php',
	'mako\File '                             => MAKO_LIBRARIES_PATH . '/mako/File.php',
	'mako\Format '                           => MAKO_LIBRARIES_PATH . '/mako/Format.php',
	'mako\HTML'                              => MAKO_LIBRARIES_PATH . '/mako/HTML.php',
	'mako\I18n'                              => MAKO_LIBRARIES_PATH . '/mako/I18n.php',
	'mako\Input'                             => MAKO_LIBRARIES_PATH . '/mako/Input.php',
	'mako\Log'                               => MAKO_LIBRARIES_PATH . '/mako/Log.php',
	'mako\log\Adapter'                       => MAKO_LIBRARIES_PATH . '/mako/log/Adapter.php',
	'mako\log\File'                          => MAKO_LIBRARIES_PATH . '/mako/log/File.php',
	'mako\log\FirePHP'                       => MAKO_LIBRARIES_PATH . '/mako/log/FirePHP.php',
	'mako\log\Syslog'                        => MAKO_LIBRARIES_PATH . '/mako/log/Syslog.php',
	'mako\Mako'                              => MAKO_LIBRARIES_PATH . '/mako/Mako.php',
	'mako\Model'                             => MAKO_LIBRARIES_PATH . '/mako/Model.php',
	'mako\Num'                               => MAKO_LIBRARIES_PATH . '/mako/Num.php',
	'mako\Package'                           => MAKO_LIBRARIES_PATH . '/mako/Package.php',
	'mako\Pagination'                        => MAKO_LIBRARIES_PATH . '/mako/Pagination.php',
	'mako\reactor\Reactor'                   => MAKO_LIBRARIES_PATH . '/mako/reactor/Reactor.php',
	'mako\reactor\Task'                      => MAKO_LIBRARIES_PATH . '/mako/reactor/Task.php',
	'mako\reactor\tasks\Migrate'             => MAKO_LIBRARIES_PATH . '/mako/reactor/tasks/Migrate.php',
	'mako\reactor\tasks\Package'             => MAKO_LIBRARIES_PATH . '/mako/reactor/tasks/Package.php',
	'mako\Redis'                             => MAKO_LIBRARIES_PATH . '/mako/Redis.php',
	'mako\Request'                           => MAKO_LIBRARIES_PATH . '/mako/Request.php',
	'mako\Response'                          => MAKO_LIBRARIES_PATH . '/mako/Response.php',
	'mako\Rest'                              => MAKO_LIBRARIES_PATH . '/mako/Rest.php',
	'mako\security\Password'                 => MAKO_LIBRARIES_PATH . '/mako/security/Password.php',
	'mako\security\Token'                    => MAKO_LIBRARIES_PATH . '/mako/security/Token.php',
	'mako\Session'                           => MAKO_LIBRARIES_PATH . '/mako/Session.php',
	'mako\session\Adapter'                   => MAKO_LIBRARIES_PATH . '/mako/session/Adapter.php',
	'mako\session\Database'                  => MAKO_LIBRARIES_PATH . '/mako/session/Database.php',
	'mako\session\Redis'                     => MAKO_LIBRARIES_PATH . '/mako/session/Redis.php',
	'mako\String'                            => MAKO_LIBRARIES_PATH . '/mako/String.php',
	'mako\URL'                               => MAKO_LIBRARIES_PATH . '/mako/URL.php',
	'mako\UUID'                              => MAKO_LIBRARIES_PATH . '/mako/UUID.php',
	'mako\UserAgent'                         => MAKO_LIBRARIES_PATH . '/mako/UserAgent.php',
	'mako\Validate'                          => MAKO_LIBRARIES_PATH . '/mako/Validate.php',
	'mako\View'                              => MAKO_LIBRARIES_PATH . '/mako/View.php',
	'mako\view\Block'                        => MAKO_LIBRARIES_PATH . '/mako/view/Block.php',
	'mako\view\Compiler'                     => MAKO_LIBRARIES_PATH . '/mako/view/Compiler.php',
));

spl_autoload_register('mako\ClassLoader::load');

//------------------------------------------------------------------------------------------
// Define helper functions
//------------------------------------------------------------------------------------------

/**
* Returns path to a package or application directory.
*
* @access  public
* @param   string  $path    Path
* @param   string  $string  String
* @return  string
*/

function mako_path($path, $string)
{
	if(strpos($string, '::') !== false)
	{
		list($package, $file) = explode('::', $string);

		$path = MAKO_PACKAGES . '/' . $package . '/' . $path . '/' . $file . '.php';
	}
	else
	{
		$path = MAKO_APPLICATION . '/' . $path . '/' . $string . '.php';
	}

	return $path;
}

if(!function_exists('__'))
{
	/**
	* Alias of mako\I18n::translate()
	*
	* Returns a translated string of the current language. 
	* If no translation exists then the submitted string will be returned.
	*
	* @access  public
	* @param   string   Text to translate
	* @param   array   (optional) Value or array of values to replace in the translated text
	* @param   string  (optional) Name of the language you want to translate to
	* @return  string
	*/

	function __($string, array $vars = array(), $language = null)
	{
		return mako\I18n::translate($string, $vars, $language);
	}
}

if(!function_exists('dump_var'))
{
	/**
	* Works like var_dump except that it wraps the variable in <pre> tags.
	*
	* @access  public
	* @param   mixed   Variable you want to dump
	*/

	function dump_var()
	{
		ob_start();

		call_user_func_array('var_dump', func_get_args());

		echo '<pre>' . ob_get_clean() . '</pre>';
	}
}

//------------------------------------------------------------------------------------------
// Include application bootstrap file
//------------------------------------------------------------------------------------------
		
require MAKO_APPLICATION . '/bootstrap.php';

//------------------------------------------------------------------------------------------
// Configure the core
//------------------------------------------------------------------------------------------

$config = mako\Config::get('mako');

// Set internal charset

define('MAKO_CHARSET', $config['charset']);

mb_language('uni');
mb_regex_encoding(MAKO_CHARSET);
mb_internal_encoding(MAKO_CHARSET);

// Set default timezone

date_default_timezone_set($config['timezone']);

// Set locale information

setlocale(LC_ALL, $config['locale']['locales']);
	
if($config['locale']['lc_numeric'] === false)
{
	setlocale(LC_NUMERIC, 'C');
}

// Set up class aliases

foreach($config['aliases'] as $alias => $className)
{
	mako\ClassLoader::alias($alias, $className);
}

// Initialize packages

foreach($config['packages'] as $package)
{
	mako\Package::init($package);
}

unset($config);

/** -------------------- End of file --------------------**/