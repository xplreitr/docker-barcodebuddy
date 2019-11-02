<?php

namespace Grocy\Services;

use \Grocy\Services\ApplicationService;

class DatabaseService
{
	private function GetDbFilePath()
	{
		if (GROCY_MODE === 'demo' || GROCY_MODE === 'prerelease')
		{
			return GROCY_DATAPATH . '/grocy_' . GROCY_CULTURE . '.db';
		}

		return GROCY_DATAPATH . '/grocy.db';
	}

	private $DbConnectionRaw;
	/**
	 * @return \PDO
	 */
	public function GetDbConnectionRaw()
	{
		if ($this->DbConnectionRaw == null)
		{
			$pdo = new \PDO('sqlite:' . $this->GetDbFilePath());
			$pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
			$this->DbConnectionRaw = $pdo;
		}

		return $this->DbConnectionRaw;
	}

	private $DbConnection;
	/**
	 * @return \LessQL\Database
	 */
	public function GetDbConnection()
	{
		if ($this->DbConnection == null)
		{
			$this->DbConnection = new \LessQL\Database($this->GetDbConnectionRaw());
		}

		return $this->DbConnection;
	}

	/**
	 * @return boolean
	 */
	public function ExecuteDbStatement(string $sql)
	{
		$pdo = $this->GetDbConnectionRaw();
        $fp = fopen('/www/data/sql.log', 'a');
        fwrite($fp, "$sql\n");
        $time_start = microtime(true);
		if ($pdo->exec($sql) === false)
		{
			throw new Exception($pdo->errorInfo());
		}
        fwrite($fp, "Total execution time in seconds: " . (microtime(true) - $time_start) . "\n");
        fclose($fp);

		return true;
	}

	/**
	 * @return boolean|\PDOStatement
	 */
	public function ExecuteDbQuery(string $sql)
	{
		$pdo = $this->GetDbConnectionRaw();
        $fp = fopen('/www/data/sql.log', 'a');
        fwrite($fp, "$sql\n");
        $time_start = microtime(true);
		if ($this->ExecuteDbStatement($sql) === true)
		{
			return $pdo->query($sql);
		}
        fwrite($fp, "Total execution time in seconds: " . (microtime(true) - $time_start) . "\n");
        fclose($fp);

		return false;
	}

	public function GetDbChangedTime()
	{
		return date('Y-m-d H:i:s', filemtime($this->GetDbFilePath()));
	}

	public function SetDbChangedTime($dateTime)
	{
		touch($this->GetDbFilePath(), strtotime($dateTime));
	}
}
