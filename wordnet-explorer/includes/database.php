<?php

class database {
	
    var $Host     = "localhost";
	
    var $Database = "virtuema_voidline";
	
    var $User     = "root";
	
    var $Password = "";
 
    var $Link_ID  = 0;
	
    var $Query_ID = 0;
	
    var $Record   = array();
	
    var $Row; 
	
    var $LoginError = "";
 
    var $Errno    = 0;
	
    var $Error    = "";
 
	//-------------------------------------------
	//    Connects to the database
	//-------------------------------------------
	
    function connect()
        {
        if(!$this->Link_ID )
            $this->Link_ID=mysqli_connect( $this->Host, $this->User, $this->Password );
        if( !$this->Link_ID )
            $this->halt( "Link-ID == false, connect failed" );
        if( !mysqli_query($this->Link_ID, sprintf( "use %s", $this->Database )) )
            $this->halt( "cannot use database ".$this->Database );
        } // end function connect
 
	//-------------------------------------------
	//    Queries the database
	//-------------------------------------------
	
    function query( $Query_String )
        {
        $this->connect();
        $this->Query_ID = mysqli_query( $this->Link_ID,$Query_String );
        $this->Row = 0;
        $this->Errno = mysqli_errno($this->Link_ID);
        $this->Error = mysqli_error($this->Link_ID);
        if( !$this->Query_ID )
            $this->halt( "Invalid SQL: ".$Query_String );
        return $this->Query_ID;
        } // end function query
 
 	//-------------------------------------------
	//	  Loads all results to array
	//-------------------------------------------
	
	function loadResults() {
		
		$results = array();
		
		for($i = 0; $results[$i] = mysqli_fetch_assoc($this->Query_ID); $i++);
		
		array_pop($results);
		
		return $results;
		
	}
	
	//-------------------------------------------
	//    If error, halts the program
	//-------------------------------------------
	
    function halt( $msg )
        {
        printf( "
<strong>Database error:</strong> %s
n", $msg );
        printf( "<strong>mysqli Error</strong>: %s (%s)
n", $this->Errno, $this->Error );
        die( "Session halted." );
        } // end function halt
 
	//-------------------------------------------
	//    Retrieves the next record in a recordset
	//-------------------------------------------
	
    function nextRecord()
        {
        @ $this->Record = mysqli_fetch_array( $this->Query_ID );
        $this->Row += 1;
        $this->Errno = mysqli_errno();
        $this->Error = mysqli_error();
        $stat = is_array( $this->Record );
        if( !$stat )
            {
            @ mysqli_free_result( $this->Query_ID );
            $this->Query_ID = 0;
            }
        return $stat;
        } // end function nextRecord
 
	//-------------------------------------------
	//    Retrieves a single record
	//-------------------------------------------
	
    function singleRecord()
        {
        $this->Record = mysqli_fetch_array( $this->Query_ID );
        $stat = is_array( $this->Record );
        return $stat;
        } // end function singleRecord
 
	//-------------------------------------------
	//    Returns the number of rows  in a recordset
	//-------------------------------------------
	
    function numRows()
        {
        return mysqli_num_rows( $this->Query_ID );
        } // end function numRows
 
	//-------------------------------------------
	//    Returns the Last Insert Id
	//-------------------------------------------
	
    function lastId()
        {
        return mysqli_insert_id();
        } // end function numRows
 
	//-------------------------------------------
	//    Returns Escaped string
	//-------------------------------------------
	
    function mysqli_escape_mimic($inp)
        {
        if(is_array($inp))
            return array_map(__METHOD__, $inp);
        if(!empty($inp) && is_string($inp)) {
            return str_replace(array('\\', "\0", "\n", "\r", "'", '"', "\x1a"), array('\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z'), $inp);
        }
        return $inp;
        }
		
	//-------------------------------------------
	//    Returns the number of rows  in a recordset
	//-------------------------------------------
	
    function affectedRows()
        {
            return mysqli_affected_rows();
        } // end function numRows
	 
	//-------------------------------------------
	//    Returns the number of fields in a recordset
	//-------------------------------------------
	
    function numFields()
        {
            return mysqli_num_fields($this->Query_ID);
        } // end function numRows
 
    } // end class Database

?>