<?php

namespace mako\database\query;

/**
* Raw SQL container.
*
* @author     Frederic G. Østby
* @copyright  (c) 2008-2012 Frederic G. Østby
* @license    http://www.makoframework.com/license
*/

class Raw
{
	//---------------------------------------------
	// Class variables
	//---------------------------------------------

	/**
	* Raw SQL
	*
	* @var string
	*/

	protected $sql;

	//---------------------------------------------
	// Class constructor, destructor etc ...
	//---------------------------------------------

	/**
	* Constructor.
	*
	* @access  public
	* @param   string  $sql  Raw SQL
	*/

	public function __construct($sql)
	{
		$this->sql = $sql;
	}

	//---------------------------------------------
	// Class methods
	//---------------------------------------------

	/**
	* Returns the raw SQL.
	*
	* @access  public
	* @return  string
	*/

	public function get()
	{
		return $this->sql;
	}
}

/** -------------------- End of file --------------------**/