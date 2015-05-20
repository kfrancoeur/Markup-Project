<?php
/* Markup Score - Built using PHP 5.5.12 and MySQL5.6.17 (WAMP Server 2.5)
//This class accepts local filename input, as a single value or an array
		in order to give an arbitrary score based on it.
	The file name must be properly formatted as 'Name_YYYY_MM_DD', and must be
		of an HTML-readable type.
	To use, instantiate a new MarkupScore object, then set a file using the 
		set_file($filename) function. Current results can be saved to SQL using 
		save_data().
//By: Kyle Francoeur
*/
class MarkupScore{
	
	//Element Scoring Values and tag storage.
	private $values;
		
	//Class Variables
	private $score = array(); //Score storage
	private $dom; //DOMDocument storage
	private $fName; // File name storage
	
	//SQL Server Info
	private $sqlhost = "localhost";
	private $sqlun = "root";
	private $sqlpw = "";
	private $sqldb = "MarkupProject";
	private $mysqli = NULL; 

	
	//Constructor
	function __construct($filename = NULL){
		//If you want to add a new score to the parse, do it here.
		$this->values = array("body" => 5, "div" => 3, "footer" => 10, "h1" => 3,
		"h2" => 2, "header" => 10, "html" => 5, "p" => 1, "big" => -2,
		"center" => -2, "font" => -1, "frame" => -5, "frameset" => -5,
		"strike" => -1, "tt" => -2);
		
		if (isset($filename)){
			$this->set_file($filename);
		}
	}
	
	/*Private Functions*/
	
	//Parse $dom for raw score
	private function parse_data(){
		//Set cursor
		$sizevar = 0;
		
		foreach ($this->dom as $singledom){
			//Set base score
			$this->score[$sizevar] = 0;
			
			//Calculate score
			foreach ($this->values as $key => $value){
				//Get number of any given element from values variable
				$elementCount = $singledom->getElementsByTagName($key)->length;
				if ($elementCount > 0){
					$this->score[$sizevar] += $elementCount * $value;
				}
			}
			$sizevar += 1;
		}
	}
	
	//Opens connection to a server.
	private function open_connection(){
		$this->mysqli = new mysqli($this->sqlhost,$this->sqlun,$this->sqlpw,$this->sqldb);
		if ($this->mysqli->connect_error){
			die('Connect Error (' . $this->mysqli->connect_errno . ') '
            . $this->mysqli->connect_error);
		}
	}
	
	//Closes connection to a server based on information passed in.
	private function close_connection(){
		return $this->mysqli->close();
	}
	
	//Takes a file name and returns the relevant information from it:
	//Prefix, YYYY, MM, DD
	private function file_date_parse($exname){
		return explode('_', basename($exname,pathinfo($exname,PATHINFO_EXTENSION)));
	}
	
	/* Public Functions */
		
	//Passes the file's data to the mySQL server to insert into table
	function save_data(){

		$this->open_connection();
		
		//Begin query string
		$query = "INSERT INTO Scores (PrefixID,DateRun,TimeRun,FileDate,Score)
					VALUES ";

		
		//This portion does not use automatic index selection to assure indexes match.
		for($i=0; $i < count($this->score); $i++){
			
			//Parse and clean up file name into Prefix and date
			$finfo = $this->file_date_parse(strtolower($this->fName[$i]));
			$fdate = $finfo[1].'-'.$finfo[2].'-'.rtrim($finfo[3],'.');

			//Append various sets of values to query
			$query .= sprintf("('%s',CURDATE(),CURTIME(),'%s',%d),",
							$this->mysqli->escape_string($finfo[0]), $this->mysqli->escape_string($fdate),
							$this->score[$i]);
			
			
		}

		//Trim trailing comma and submit query
		$querycheck = $this->mysqli->query(rtrim($query,','));
		
		$this->close_connection();
		
		//returns success or failure
		return $querycheck;
	}
	
	//Sets the file for the class object and begins processing.
	function set_file($fileName){
		
		$this->dom = array(); //(Re)set DomDocument array
		
		//Force array type for single file input
		if (is_array($fileName)){
			$this->fName = $fileName;
		}else{
			$this->fName[] = $fileName;
		}
		
		//Loading files into memory below may give warnings of invalid tags.
		//Disabling because they're unneeded for this exercise. 
		error_reporting(E_ALL ^ E_WARNING);

		//Load files into memory.
		$sizevar = 0;
		foreach($this->fName as $name){
			$this->dom[$sizevar] = new DOMDocument;
			$this->dom[$sizevar]->loadHTMLFile($name);
			$sizevar += 1;
		}

		//Re-enables warnings.
		error_reporting(E_ALL);
		
		$this->parse_data();

	}
	
	//Retrieves the a score based on PrefixID
	function pid_score($PrefixID){
		
		$this->open_connection();
		
		//Sterilize input to prevent easy hacks.
		$query = sprintf("SELECT RunID, DateRun, TimeRun, FileDate, Score FROM Scores
							WHERE PrefixID = '%s'",
						$this->mysqli->escape_string(strtolower($PrefixID)));

		//Submit query
		$result = $this->mysqli->query($query);
			

		$this->close_connection();
		
		//Format results
		while($row = $result->fetch_array(MYSQLI_ASSOC)){
			$rows[] = $row;
		}

		return $rows;
		
	}
	
	//Returns the rows within a date range.
	//First variable is starting date, second score is ending date.
	function date_score($StartDate, $EndDate){
		//Open connection
		$this->open_connection();
		
		//Sterilize input to prevent easy hacks.
		$query = sprintf("SELECT RunID, PrefixID, DateRun, TimeRun, FileDate, Score FROM Scores
							WHERE DateRun BETWEEN '%s' AND '%s'",
						$this->mysqli->escape_string($StartDate), $this->mysqli->escape_string($EndDate));
		//Submit query
		$result = $this->mysqli->query($query);

		//Close Connection
		$this->close_connection();
		
		//Format results
		while($row = $result->fetch_array(MYSQLI_ASSOC)){
			$rows[] = $row;
		}
		
		return $rows;
		
	}
	
	//Returns highest score(s) and PrefixID for them.  May return multiple.
	//Returns score in associative field labelled 'Score', Prefixes as numeric fields.
	function retrieve_high(){
		//Open connection
		$this->open_connection();

		//Submit query
		$result = $this->mysqli->query("SELECT PrefixID,Score FROM Scores WHERE Score = (select MAX(Score) from Scores)");
		
		//Close connection
		$this->close_connection();
		
		//Format results
		while($row = $result->fetch_array(MYSQLI_ASSOC)){
			if (!isset($rows['Score'])){
				$rows['Score'] = $row['Score'];
			}
			$rows[] = $row['PrefixID'];
		}
		
		return $rows;
	}
	
	//Returns lowest score(s) and PrefixID for them as an array.  May return multiple.
	//Returns score in associative field labelled 'Score', Prefixes as numeric fields.
	function retrieve_low(){
		//Open connection
		$this->open_connection();
		
		//Submit query
		$result = $this->mysqli->query("SELECT PrefixID,Score FROM Scores WHERE Score = (select MIN(Score) from Scores)");
		
		//close connection
		$this->close_connection();

		//Format results
		while($row = $result->fetch_array(MYSQLI_ASSOC)){
			if (!isset($rows['Score'])){
				$rows['Score'] = $row['Score'];
			}
			$rows[] = $row['PrefixID'];
		}
		
		return $rows;
		
	}
	
	//Public Getters.
	function get_scores(){ return $this->score; }
	function get_file_name(){ return $this->fName; }
	
	
}
?>