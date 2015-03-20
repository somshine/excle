<?php
class CDatabase {

	protected $m_strServerHost;
	protected $m_strUserName;
	protected $m_strPassword;
	protected $m_strDatabaseName;
	protected $m_strResource;

	public function __construct() {
		$this->setServerHost( SERVER_HOST );
		$this->setUserName( USER_NAME );
		$this->setPassword( PASSWORD );
		$this->setDatabaseName( DATABASE_NAME );
	}

	/**************************************
	************* SET FUNCTIONS ***********
	**************************************/

	public function setServerHost( $strServerHost ) {
		$this->m_strServerHost = $strServerHost;
	}

	public function setUserName( $strUserName ) {
		$this->m_strUserName = $strUserName;
	}

	public function setPassword( $strPassword ) {
		$this->m_strPassword = $strPassword;
	}

	public function setDatabaseName( $strDatabaseName ) {
		$this->m_strDatabaseName = $strDatabaseName;
	}

	public function setResource( $strResource ) {
		$this->m_strResource = $strResource;
	}

	/**************************************
	************* GET FUNCTIONS ***********
	**************************************/

	public function getServerHost() {
		return $this->m_strServerHost;
	}

	public function getUserName() {
		return $this->m_strUserName;
	}

	public function getPassword() {
		return $this->m_strPassword;
	}

	public function getDatabaseName() {
		return $this->m_strDatabaseName;
	}

	public function getResource() {
		return $this->m_strResource;
	}


	/*******************************************
	************* DATABASE FUNCTIONS ***********
	*******************************************/

	public function connectDatabase() {
		$strResource = mysql_connect( $this->getServerHost(), $this->getUserName(), $this->getPassword() ) OR die( "Failed Connecting To Mysql" );
		$this->setResource( $strResource );
	}

	public function setDatabase() {
		mysql_select_db( $this->getDatabaseName(), $this->getResource() ) OR die( "Failed Connecting To Database" );
	}

	public function closeDatabase() {
		mysql_close( $this->getResource() ) OR die( "Failed To Close Connection." );
	}

	public function runQuery( $strSql ) {
		return mysql_query( $strSql );// or display(mysql_error());
	}
	
	public function commit() {
		return mysql_query( 'COMMIT;' );
	}
	
	public function rollback() {
		return mysql_query( 'ROLLBACK;' );
	}
	
	/*******************************************
	************* DB Basic Operation ***********
	*******************************************/

	public function insert( $strSql ) {
		$intResourceId = $this->runQuery( $strSql );
		return mysql_insert_id();
	}

	public function update( $strSql ) {
		$this->runQuery( $strSql );
		return true;
	}

	public function delete( $strSql ) {
		$this->runQuery( $strSql );
		return true;
	}

	public function fetchResults( $strSql ) {
		$intResourceId = $this->runQuery( $strSql );
		$arrmixResult = array();
		if( true == is_resource( $intResourceId ) ) {
			while( $arrmixRow = mysql_fetch_object( $intResourceId ) ) {
				$arrmixResult[] = $arrmixRow;
			}
		}
		return $arrmixResult;
	}

	public function fetchResult( $strSql ) {
		$intResourceId = $this->runQuery( $strSql );
		$arrmixResult = array();

		if( true == is_resource( $intResourceId ) ) {
			$arrmixResult = mysql_fetch_object( $intResourceId );
		}
		return $arrmixResult;
	}

}
?>