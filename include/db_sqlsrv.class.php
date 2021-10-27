<?php
/*
	[UENEN TECHNOLOGIES] Copyright (c) 2021 unn.tech
	This is a freeware, use is subject to license.txt
*/
class db_sqlsrv {
	var $querynum = 0;
	var $connid = 0;
	var $insertid = 0;

	function connect( $dbhost = 'localhost', $dbuser, $dbpw, $dbname, $dbttl, $dbcharset, $pconnect = 0 ) {
		$connectionOptions = array(
			"Database" => $dbname,
			"Uid" => $dbuser,
			"PWD" => $dbpw
		);
		//Establishes the connection
		if ( !$this->connid = sqlsrv_connect( $dbhost, $connectionOptions ) ) {
			$this->halt( 'Can not connect to MsSQL server' );
		}
		return $this->connid;
	}

	function query( $sql ) {
		$this->querynum++;
		$sql = trim( $sql );
		if ( preg_match( "/^insert into/i", $sql ) ) {
			$sql = "{$sql}; SELECT @@identity as insertid;";
			//echo $sql;
			$query = sqlsrv_query( $this->connid, $sql )or $this->halt( 'MsSQL Query Error', $sql );
			sqlsrv_next_result( $query );
			$insid = $this->fetch_row( $query );
			$this->insertid = intval( $insid[ 0 ] );
		} else {
			$query = sqlsrv_query( $this->connid, $sql )or $this->halt( 'MsSQL Query Error', $sql );
		}
		return $query;
	}

	function get_one( $sql ) {
		$query = $this->query( $sql );
		$rs = $this->fetch_array( $query );
		$this->free_result( $query );
		return $rs;
	}

	function get_value( $sql ) {
		$query = $this->query( $sql );
		$rs = $this->fetch_array( $query, SQLSRV_FETCH_NUMERIC );
		$this->free_result( $query );
		return $rs[ 0 ];
	}

	function fetch_array( $res, $type = SQLSRV_FETCH_ASSOC ) {
		$r = sqlsrv_fetch_array( $res, $type );
		return $r;
	}

	function fetch_row( $res, $type = SQLSRV_FETCH_NUMERIC ) {
		$r = sqlsrv_fetch_array( $res, $type );
		return $r;
	}

	function halt( $message = '', $sql = '' ) {
		exit( "MsSQL Query:$sql <br/> Message:$message" );
	}

	function close() {
		return sqlsrv_close( $this->connid );
	}

	function free_result( $stmt ) {
		sqlsrv_free_stmt( $stmt );
	}

	function insert_id() {
		return $this->insertid;
	}

	function affected_rows( $res ) {
		return sqlsrv_rows_affected( $res );
	}

	function server_info() {
		return sqlsrv_server_info( $this->connid );
	}

	function client_info() {
		return sqlsrv_client_info( $this->connid );
	}

	function errors() {
		return sqlsrv_errors();
	}
	
	function has_rows($res){
		return sqlsrv_has_rows($res);
	}
	
	function num_fields($res){
		return sqlsrv_num_fields($res);
	}

}
?>