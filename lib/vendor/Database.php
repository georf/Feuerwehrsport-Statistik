<?php

/**
 * Implements AbstractDatabase using MySQL
 *
 * @author Sebastian Gaul <sebastian@mgvmedia.com>
 *
 */
class Database {


	/**
	 * Defines the size of pagination pages
	 *
	 * Keep 0 to disable pagination.
	 *
	 * @var int $pageSize
	 */
	private $pageSize = 0;

	/**
	 * Determines the currently chosen page
	 *
	 * @var int
	 */
	private $currentPage = 0;


	/**
	 * Link to database session
	 *
	 * @var int
	 */
	private $dbConnection = false;


	/**
	 * Establishes database connection
	 *
	 * @param string $host
	 * @param string $database
	 * @param string $username
	 * @param string $password
	 */
	public function __construct($host, $database, $username, $password) {

		// Connect with persistent connection
		$this->dbConnection = @mysql_pconnect($host, $username, $password);

		if (!$this->dbConnection) {
			throw new Exception('MySQL Connection Database Error: ' . mysql_error());
		}

		if (!mysql_select_db($database, $this->dbConnection)) {
			throw new Exception('MySQL Connection Database Error: ' . mysql_error());
		}

		$this->query("SET NAMES 'utf8';");
	}


	/**
	 * Execute query and return rows as array
	 *
	 * @param string $mysqlQuery
	 * @return array
	 */
	public function getRows($mysqlQuery) {

		$result = $this->query($mysqlQuery);

		if (!$result) {
			throw new Exception('MySQL Connection Database Error: ' . mysql_error().'<br/>'.$mysqlQuery);
		}

		$rows = array();
		while ($row = mysql_fetch_assoc($result)) {
			$rows[] = $row;
		}
		return $rows;
	}


	/**
	 * Excecute query and return the first row
	 *
	 * @param string $mysqlQuery
	 * @return array
	 */
	public function getFirstRow($mysqlQuery, $key = false) {

		$result = $this->query($mysqlQuery);

		if (!$result) {
			throw new Exception('MySQL Connection Database Error: ' . mysql_error().'<br/>'.$mysqlQuery);
		}

		if (mysql_num_rows($result) === 0) {
			return false;
		}

		$assoc = mysql_fetch_assoc($result);

        if ($key && isset($assoc[$key])) {
            return $assoc[$key];
        } else {
            return $assoc;
        }
	}


	/**
	 * Escape a string
	 *
	 * @param string $string
	 * @return string
	 */
	public function escape($string) {
		return mysql_escape_string($string);
	}


	/**
	 * Inserts a row given by an array
	 *
	 * Returns the inserted id, 0 or false
	 *
	 * @param string $table
	 * @param array $values
	 * @return int | boolean
	 */
	public function insertRow($table, $values, $cleanCache = true) {

		if (count($values) === 0) {
			throw new Exception(_('No value given'));
		}

		$mysqlQuery = 'INSERT INTO `'.$table.'` SET ';

		$mysqlQuery .= $this->set($values);

		$mysqlQuery .= ";";

		$result = $this->query($mysqlQuery);

		if (!$result) {
			throw new Exception('MySQL Connection Database Error: ' . mysql_error().'<br/>'.$mysqlQuery);
		}

		if (mysql_affected_rows($this->dbConnection) !== 1) {
			return false;
		}

        $id = mysql_insert_id($this->dbConnection);

        if ($cleanCache) {
            // clean cache
            Cache::clean();
        }

		return $id;
	}

  public function deleteRow($table, $id, $colName = 'id') {

		$mysqlQuery = 'DELETE FROM `'.$table.'` ';

		$mysqlQuery .= " WHERE `".$colName."`='".$id."' LIMIT 1;";

		$result = $this->query($mysqlQuery);

		if (!$result) {
			throw new Exception('MySQL Connection Database Error: ' . mysql_error().'<br/>'.$mysqlQuery);
		}


        $result =  (mysql_affected_rows($this->dbConnection) === 1);

        // clean cache
        Cache::clean();

		return $result;
  }


	/**
	 * Updates a row given by an array
	 *
	 * Returns success
	 *
	 * @param string $table
	 * @param array $values
	 * @return int | boolean
	 */
	public function updateRow($table, $id, $values, $colName = 'id') {

		if (count($values) === 0) {
			throw new Exception(_('No value given'));
		}

		$mysqlQuery = 'UPDATE `'.$table.'` SET ';

		$mysqlQuery .= $this->set($values);

		$mysqlQuery .= " WHERE `".$colName."`='".$id."' LIMIT 1;";

		$result = $this->query($mysqlQuery);

		if (!$result) {
			throw new Exception('MySQL Connection Database Error: ' . mysql_error().'<br/>'.$mysqlQuery);
		}

        $result = (mysql_affected_rows($this->dbConnection) === 1);

        // clean cache
        Cache::clean();

		return $result;
	}



	private function set($values) {

		$mysqlQuery = '';

		foreach ($values as $col => $value) {
			if (is_int($value))
				$mysqlQuery .= '`'.$col."` = ".$this->escape($value).",";
			elseif (is_null($value))
				$mysqlQuery .= '`'.$col."` = NULL,";
			else
				$mysqlQuery .= '`'.$col."` = '".$this->escape($value)."',";
		}
		$mysqlQuery = substr($mysqlQuery, 0, strlen($mysqlQuery) - 1);

		return $mysqlQuery;
	}

	/**
	 * Execute a query and returns the result
	 *
	 * @param string $query
	 * @retun int
	 */
	public function query($query) {
		return mysql_query($query, $this->dbConnection);
	}
}
