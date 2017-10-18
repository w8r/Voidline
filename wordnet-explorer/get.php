<?php

require("includes/database.php");

$data = Array();

class getter {
	private $results = null;
	private $db = null;
	private $subsequent = null;
	private $all = false;
    private $children = null;

	function __construct() {
		$this->db = new database();
		$this->subsequent = $_GET["subsequent"] == "true" ? true : false;
		$this->all = $_GET["all"] == "true" ? true : false;
		$this->results = new \stdClass;
		$this->results->edges = array();
		$this->results->nodes = array();
		$this->children = array();
	}

	function startGet($node) {
	    if (!$this->all) {
            $this->getEdges($node);
            return $this->results;
	    }
	    else {
	        $this->getAllEdges($node);
	        return $this->results;
	    }
	}

    function getAllEdges($node) {
		$query = "SELECT synset_id_1 as source, synset_id_2 as target FROM wn_hyponym";
		$this->db->query($query);
		$results = $this->db->loadResults();

		for ($i = 0; $i < count($results); $i++) {
		    array_push($this->results->edges, $results[$i]);
		    $lastItem = count($this->results->edges) - 1;
			$this->results->edges[$lastItem]["id"] = uniqid();
		}

		$this->getAllNodes();
	}

	function getAllNodes() {
        $query = "SELECT a.synset_id as id, proper(replace(a.word, '_', ' ')) as text, b.gloss as description FROM wn_synset a INNER JOIN wn_gloss b ON a.synset_id = b.synset_id " .
                 "GROUP BY a.synset_id";
        $this->db->query($query);
        $results = $this->db->loadResults();

        for ($i = 0; $i < count($results); $i++) {
            $results[$i]["data"] = new \stdClass;
            $results[$i]["data"]->description = $results[$i]["description"];
            unset($results[$i]["description"]);
            array_push($this->results->nodes, $results[$i]);
        }
    }

	function getEdges($node) {
		$query = "SELECT synset_id_1 as source, synset_id_2 as target FROM wn_hyponym WHERE synset_id_1 = " . $node;
		$this->db->query($query);
		$results = $this->db->loadResults();

		$children = array($node);
		for ($i = 0; $i < count($results); $i++) {
		    array_push($this->results->edges, $results[$i]);
		    $lastItem = count($this->results->edges) - 1;
			$this->results->edges[$lastItem]["id"] = uniqid();
			$children[($i + 1)] = $results[$i]["target"];
		}

        $this->getNodes($children, $node);
	}

	function getNodes($edges, $node) {
		$query = "SELECT a.synset_id as id, proper(replace(a.word, '_', ' ')) as text, b.gloss as description FROM wn_synset a INNER JOIN wn_gloss b ON a.synset_id = b.synset_id " . 
				 "WHERE a.synset_id IN (" . join($edges, ",") . ") " .
				 ($this->subsequent ? ("AND a.synset_id != " . $node . " ") : "") .
				 "GROUP BY a.synset_id";
		$this->db->query($query);
		$results = $this->db->loadResults();

		for ($i = 0; $i < count($results); $i++) {
		    $results[$i]["data"] = new \stdClass;
		    $results[$i]["data"]->hasChildren = $this->hasChildren($results[$i]["id"]);
		    $results[$i]["color"] = $results[$i]["data"]->hasChildren ? "#c4772b" : "#38701a";
		    $results[$i]["data"]->description = $results[$i]["description"];
		    unset($results[$i]["description"]);
		    array_push($this->results->nodes, $results[$i]);
        }
	}

	function hasChildren($node) {
	    $query = "SELECT synset_id_2 FROM wn_hyponym WHERE synset_id_1 = " . $node;
        $this->db->query($query);
        $results = $this->db->loadResults();
        return count($results) > 0 ? true : false;
	}
}
$getter = new getter();
$results = $getter->startGet($_GET["id"]);
die(json_encode($results));
?>