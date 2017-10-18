<?php

class wordnet {
	
	function __construct() {
		
		// get database class

		require_once("database.php");

		$this->db = new database();
		
	}
	
	function loadName($word = '', $id = '') {
		
		$query = "SELECT `word` FROM wn_synset WHERE `synset_id` = '" . $id . "'";
		
		$this->db->query($query);
		
		$results = $this->db->loadResults();
		
		return $results[0]["word"];
		
	}
	
	function loadDefinition($word = '', $id = '') {
		
		$query = "SELECT `gloss` FROM wn_gloss WHERE `synset_id` = '" . $id . "'";
		
		$this->db->query($query);
		
		$results = $this->db->loadResults();
		
		return $results;
		
	}
	
	function loadLocalTree($word = '', $id = '', $limit = 0, $recursion = FALSE) {
		
		$query = "SELECT tree.`synset_id_1` as child, tree.`synset_id_2` as parent, " . 

				 "GROUP_CONCAT(name1.`word`) as childname, name2.`word` as parentname, " .
				 
				 "def1.`gloss` as childesc, def2.`gloss` as parentdesc " .
				 
				 "FROM wn_hyponym tree " . 
		
				 "INNER JOIN wn_synset name1 ON tree.`synset_id_1` = name1.`synset_id` " . 
				 
				 "INNER JOIN wn_synset name2 ON tree.`synset_id_2` = name2.`synset_id` " .
				 
				 "LEFT JOIN wn_gloss def1 ON tree.`synset_id_1` = def1.`synset_id` " . 
				 
				 "INNER JOIN wn_gloss def2 ON tree.`synset_id_2` = def2.`synset_id` " . 
				 
				 "WHERE " . ($word ? "name2.`word` = '" . $word . "'" : "tree.`synset_id_2` = '" . $id . "'") . " " . 
				 
				 "GROUP BY tree.`synset_id_1` " . 
				 
				 ($limit ? "LIMIT " . $limit : NULL);
		 
		$this->db->query($query);
		
		$i = 0;
		
		$tree = array();
		
		$rows = array();
		
		for ($j = 0; $rows[$j] = mysql_fetch_assoc($this->db->Query_ID); $j++); array_pop($rows);
		
		foreach ($rows as $row) {
			
			$tree[$row["child"]] 				= array();
			
			$tree[$row["child"]]["id"] 			= $row["child"];
			
			$tree[$row["child"]]["parent"] 		= $row["parent"];
			
			$names								= explode(",", $row["childname"]);

			for ($x = 0; $x < count($names); $x++) {
				
				$names[$x] 						= str_replace("_", " ", $names[$x]);
				
				$names[$x] 						= ucwords($names[$x]);
				
			}
			
			$names								= array_unique($names);

			$names								= implode(", ", $names);
			
			$tree[$row["child"]]["name"] 		= $names;
			
			$tree[$row["child"]]["children"] 	= $this->loadChildrenTree(NULL, $row["child"], 0, TRUE);
			
			if (!$i && !$recursion) {
				
				$this->root = $tree[$row["child"]];
				
			}
			
			$i++;
				
		}

		return $tree;
		 
	}
	
	function loadParentsTree($word = '', $id = '', $limit = 0, $recursion = FALSE) {
		
		$query = "SELECT tree.`synset_id_1` as child, tree.`synset_id_2` as parent, " . 

				 "GROUP_CONCAT(name1.`word`) as childname, name2.`word` as parentname, " .
				 
				 "def1.`gloss` as childesc, def2.`gloss` as parentdesc " .
				 
				 "FROM wn_hyponym tree " . 
		
				 "INNER JOIN wn_synset name1 ON tree.`synset_id_1` = name1.`synset_id` " . 
				 
				 "INNER JOIN wn_synset name2 ON tree.`synset_id_2` = name2.`synset_id` " .
				 
				 "LEFT JOIN wn_gloss def1 ON tree.`synset_id_1` = def1.`synset_id` " . 
				 
				 "INNER JOIN wn_gloss def2 ON tree.`synset_id_2` = def2.`synset_id` " . 
				 
				 "WHERE " . ($word ? "name2.`word` = '" . $word . "'" : "tree.`synset_id_2` = '" . $id . "'") . " " . 
				 
				 "GROUP BY tree.`synset_id_1` " . 
				 
				 ($limit ? "LIMIT " . $limit : NULL);
		 
		$this->db->query($query);
		
		$i = 0;
		
		$tree = array();
		
		$rows = array();
		
		for ($j = 0; $rows[$j] = mysql_fetch_assoc($this->db->Query_ID); $j++); array_pop($rows);
		
		foreach ($rows as $row) {
			
			$tree[$row["child"]] 				= array();
			
			$tree[$row["child"]]["id"] 			= $row["child"];
			
			$tree[$row["child"]]["parent"] 		= $row["parent"];
			
			$names								= explode(",", $row["childname"]);

			for ($x = 0; $x < count($names); $x++) {
				
				$names[$x] 						= str_replace("_", " ", $names[$x]);
				
				$names[$x] 						= ucwords($names[$x]);
				
			}
			
			$names								= array_unique($names);

			$names								= implode(", ", $names);
			
			$tree[$row["child"]]["name"] 		= $names;
			
			$tree[$row["child"]]["children"] 	= $this->loadParentsTree(NULL, $row["child"], 0, TRUE);
			
			if (!$i && !$recursion) {
				
				$this->root = $tree[$row["child"]];
				
			}
			
			$i++;
				
		}

		return $tree;
		 
	}
	
	function loadChildrenTree($word = '', $id = '', $limit = 0, $recursion = FALSE) {

		$query = "SELECT tree.`synset_id_1` as child, tree.`synset_id_2` as parent, " . 

				 "GROUP_CONCAT(DISTINCT name1.`word`) as childname, name2.`word` as parentname, " .
				 
				 "def1.`gloss` as childesc, def2.`gloss` as parentdesc " .
				 
				 "FROM wn_hypernym tree " . 
		
				 "INNER JOIN wn_synset name1 ON tree.`synset_id_1` = name1.`synset_id` " . 
				 
				 "INNER JOIN wn_synset name2 ON tree.`synset_id_2` = name2.`synset_id` " .
				 
				 "LEFT JOIN wn_gloss def1 ON tree.`synset_id_1` = def1.`synset_id` " . 
				 
				 "LEFT JOIN wn_gloss def2 ON tree.`synset_id_2` = def2.`synset_id` " . 
				 
				 "WHERE " . ($word ? "name2.`word` = '" . $word . "'" : "tree.`synset_id_2` = '" . $id . "'") . " " .
				 
				 "GROUP BY tree.`synset_id_1` " . 
				 
				 ($limit ? "LIMIT " . $limit : NULL);
		 
		$this->db->query($query);
		
		$i = 0;
		
		$tree = array();
		
		$rows = array();
		
		for ($j = 0; $rows[$j] = mysql_fetch_assoc($this->db->Query_ID); $j++); array_pop($rows);
		
		if (!$recursion) $this->items = 0;
		
		foreach ($rows as $row) {
			
			$tree[$row["child"]] 				= array();
			
			$tree[$row["child"]]["id"] 			= $row["child"];
			
			$tree[$row["child"]]["parent"] 		= $row["parent"];
			
			$names								= explode(",", $row["childname"]);

			for ($x = 0; $x < count($names); $x++) {
				
				$names[$x] 						= str_replace("_", " ", $names[$x]);
				
				$names[$x] 						= ucwords($names[$x]);
				
			}

			$names								= implode(", ", $names);
			
			$tree[$row["child"]]["name"] 		= $names;
			
			$tree[$row["child"]]["children"] 	= $this->loadChildrenTree(NULL, $row["child"], 0, TRUE);
			
			if (!$i && !$recursion) {
				
				$this->root = $tree[$row["child"]];
				
			}
			
			$this->items += 1;
			
			$i++;
				
		}

		return $tree;
		 
	}
	
	function getChildren($word = '', $id = '', $limit = 0) {
		
		if (!$word && !$id) die("Either a name or an ID must be provided");
		
		$query = "SELECT tree.`synset_id_1` as child, tree.`synset_id_2` as parent, " . 

		 "GROUP_CONCAT(name1.`word`) as childname, name2.`word` as parentname " .
		 
		 "FROM wn_hypernym tree " . 

		 "INNER JOIN wn_synset name1 ON tree.`synset_id_1` = name1.`synset_id` " . 
		 
		 "INNER JOIN wn_synset name2 ON tree.`synset_id_2` = name2.`synset_id` " .
		 
		 "LEFT JOIN wn_gloss def1 ON tree.`synset_id_1` = def1.`synset_id` " . 
		 
		 "INNER JOIN wn_gloss def2 ON tree.`synset_id_2` = def2.`synset_id` " . 
		 
		 "WHERE " . ($word ? "name2.`word` = '" . $word . "'" : "tree.`synset_id_2` = '" . $id . "'") . " " . 
		 
		 "GROUP BY def1.`synset_id` " . 
		 
		 ($limit ? "LIMIT " . $limit : NULL);
		 
		 $this->db->query($query);
		 
		 return $this->db->loadResults();
		 
	}
	
	function getParents($word = '', $id = '', $limit = 0) {
		
		if (!$word && !$id) die("Either a name or an ID must be provided");
		
		$query = "SELECT tree.`synset_id_1` as child, tree.`synset_id_2` as parent, " . 

		 "name1.`word` as childname, name2.`word` as parentname " .
		 
		 "FROM wn_hypernym tree " . 

		 "INNER JOIN wn_synset name1 ON tree.`synset_id_1` = name1.`synset_id` " . 
		 
		 "INNER JOIN wn_synset name2 ON tree.`synset_id_2` = name2.`synset_id` " .
		 
		 "INNER JOIN wn_gloss def1 ON tree.`synset_id_1` = def1.`synset_id` " . 
		 
		 "INNER JOIN wn_gloss def2 ON tree.`synset_id_2` = def2.`synset_id` " . 
		 
		 "WHERE " . ($word ? "name1.`word` = '" . $word . "'" : "tree.`synset_id_1` = '" . $id . "'") . " " . 
		 
		 ($limit ? "LIMIT " . $limit : NULL);
		 
		 $this->db->query($query);
		 
		 return $this->db->loadResults();
		 
	}
	
	function parseAndPrintTree($root, $tree) {
		
		$return = array();
		
		if (!is_null($tree) && count($tree) > 0) {
			
			echo '<ul>';
			
			foreach ($tree as $child) {
				
				if ($child["parent"] == $root["parent"]) { 
				                  
					unset($tree[$child["id"]]);
					
					echo '<li>' . $child["name"];
					
					if (isset($child["children"]) && count($child["children"])) {
						
						$this->parseAndPrintTree(array("parent" => $child["id"]), $child["children"]);
					
					}
					
					echo '</li>';
					
				}
				
			}
			
			echo '</ul>';
			
		}
		
	}
	
	function parseAndPrintJSON($root, $tree, $first = TRUE) {
		
		$return = array();
		
		if (!is_null($tree) && count($tree) > 0) {
			
			if ($first) {
				
				ob_start();
				
			}
			
			foreach ($tree as $child) {
				
				if ($child["parent"] == $root["parent"]) { 
				                  
					unset($tree[$child["id"]]);
					
					echo '{"name": "' . $child["name"] . '",';
					
					if (isset($child["children"]) && count($child["children"])) {
						
						echo '"children": [';
						
						$this->parseAndPrintJSON(array("parent" => $child["id"]), $child["children"], FALSE);
					
						echo ']';
						
					}
					
					echo '},';
					
				}
				
			}
			
			if ($first) {
				
				$output = ob_get_clean();
				
				$output = rtrim(str_replace(",]", "]", str_replace(",}", "}", $output)), ",");
				
				$name 	= ucwords(str_replace("_", " ", $this->loadName(NULL, $root["parent"])));
				
				$output = '{"name": "' . $name . '", "children": [' . $output . ']}';

				file_put_contents("wordnet.json", $output);

				//return $output;
				
			}
			
		}
		
	}
	
}

?>