<?php

require("includes/database.php");

$data = Array();

class getter {
	private $results = null;
	private $db = null;
	private $startID = null;
	private $subsequent = null;
	
	function __construct() {
		$this->db = new database();
		$this->startID = $_GET["id"];
		$this->subsequent = $_GET["subsequent"] == "true" ? true : false;
	}
	
	function getEdges() {
		$query = "SELECT synset_id_1 as source, synset_id_2 as target FROM wn_hyponym WHERE synset_id_1 = " . $this->startID;		
		$this->db->query($query);
		$results = $this->db->loadResults();
		
		$this->results = new \stdClass;
		$this->results->edges = array();
		
		$children = array($this->startID);
		for ($i = 0; $i < count($results); $i++) {
			$this->results->edges[$i] = $results[$i];
			$this->results->edges[$i]["id"] = uniqid();
			$children[($i + 1)] = $results[$i]["target"];
		}
		
		$this->results->nodes = $this->getNodes($children);
		return $this->results;
	}

	function getNodes($edges) {
		$query = "SELECT a.synset_id as id, proper(replace(a.word, '_', ' ')) as text, b.gloss as description FROM wn_synset a INNER JOIN wn_gloss b ON a.synset_id = b.synset_id " . 
				 "WHERE a.synset_id IN (" . join($edges, ",") . ") " .
				 ($this->subsequent ? ("AND a.synset_id != " . $this->startID . " ") : "") .
				 "GROUP BY a.synset_id";
		$this->db->query($query);
		$results = $this->db->loadResults();

		for ($i = 0; $i < count($results); $i++) {
		    $results[$i]["data"] = new \stdClass;
		    $results[$i]["data"]->description = $results[$i]["description"];
		    unset($results[$i]["description"]);
        }
		return $results;
	}
}
$getter = new getter();
$results = $getter->getEdges();
die(json_encode($results));
?>