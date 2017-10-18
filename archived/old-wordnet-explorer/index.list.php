<?php

require_once("includes/wordnet.php");

$wordnet 	= new wordnet();

$search  	= @$_POST["search"] 	? $_POST["search"] 	: (@$_GET["search"] ? @$_GET["search"] 	: NULL);

$type	 	= @$_POST["type"] 		? $_POST["type"] 	: (@$_GET["type"] 	? @$_GET["type"] 	: NULL);

$id			= @$_POST["id"] 		? $_POST["id"] 		: (@$_GET["id"] 	? @$_GET["id"] 		: NULL);

ini_set("memory_limit", "512M");

?>

<html>

<head>

	<link rel="stylesheet" href="style/style.css" type="text/css">

	<script type="text/javascript">
	var fb_param = {};
	fb_param.pixel_id = '6011292256853';
	fb_param.value = '0.00';
	fb_param.currency = 'RON';
	(function(){
	  var fpw = document.createElement('script');
	  fpw.async = true;
	  fpw.src = '//connect.facebook.net/en_US/fp.js';
	  var ref = document.getElementsByTagName('script')[0];
	  ref.parentNode.insertBefore(fpw, ref);
	})();
	</script>
	<noscript><img height="1" width="1" alt="" style="display:none" src="https://www.facebook.com/offsite_event.php?id=6011292256853&amp;value=0&amp;currency=RON" /></noscript>

</head>

<body>

<div id="page">

    <div id="sidebar">
    
        <div id="logo" style="cursor: pointer;" onclick="window.location = 'http://voidline.org';"></div>
        
        <div id="searchBox">
        
        	<form method="post" action="index.php">
        
                <div>
                
                    <input type="text" name="search" value="<?php echo $search; ?>" />
                
                </div>
                
                <!--
                
                <div id="searchForm">
                
                    <div>Show:</div>
                    
                    <div>
                    
                        <input type="radio" name="type" value="parents" id="Parents" <?php echo $type == "parents" ? "checked=\"checked\"" : NULL; ?>>
                        
                        <label for="Parents">Parents</label>
                    
                    </div>
                    
                    <div>
                    
                        <input type="radio" name="type" value="children" id="Children" <?php echo $type == "children" ? "checked=\"checked\"" : NULL; ?>>
                        
                        <label for="Children">Children</label>
                        
                    </div>
                
                </div>
            
            	-->
                
            	<div>
                
                	<input type="submit" value="Search" />
                
                </div>
                
        	</form>
        
        </div>
        
    </div>
    
    <div id="content">
    
        <?php
        
		if ($search && !$id) {

			// get meanings
						
			$query 			= "SELECT s.`synset_id` as id, s.`word` as name, g.`gloss` as definition " . 
			
							  "FROM wn_synset s LEFT JOIN wn_gloss g ON s.synset_id = g.synset_id WHERE s.`word` = '" . $search . "'";
			
			$wordnet->db->query($query);
			
			$meanings   	= $wordnet->db->loadResults();
		
			if (!count($meanings)) {
				
				echo "No such term was found.";
				
			}
			
			else if (count($meanings) > 1) {
				
				$name = ucwords(str_replace("_", " ", $search));
				
				echo "<p>Multiple meanings were found for this term. Please select the appropriate meaning from the list below.</p>";
				
				echo "<ul>";
				
				foreach ($meanings as $meaning) {
				
					echo "<li><strong><a href=\"index.php?search=" . $search . "&id=" . $meaning["id"] . "\">" . $name . "</a></strong>: " . 
					
						 $meaning["definition"] . "</li>";
						
				}
				
				echo "</ul>";
				
			}
			
		}
		
		if ($id || @count($meanings) == 1) {

			// get the single meaning
			
			if (@count($meanings) == 1) {
				
				$id = $meanings[0]["id"];
				
			}
			
			// get children tree
			
			$childrenTree 	= $wordnet->loadChildrenTree(NULL, $id);
			
			// get definition
			
			$definition 	= $wordnet->loadDefinition(NULL, $id);
			
			echo "<p>" . $definition[0]["gloss"] . "</p>";
			
			echo "Children (" . $wordnet->items . "):<br />";
			
			$wordnet->parseAndPrintTree($wordnet->root, $childrenTree);
			
			echo "<br />";
			
			echo "Context:<br />";
			
			// get local tree
			
			$localTree = $wordnet->loadLocalTree(NULL, $id);
			
			$wordnet->parseAndPrintTree($wordnet->root, $localTree);
			
			echo "<br />";
			
			echo "Parents:<br />";
			
			// get parents tree
			
			$parentsTree = $wordnet->loadParentsTree(NULL, $id);
			
			$wordnet->parseAndPrintTree($wordnet->root, $parentsTree);
			
			// get parents/children
			
			if ($type) {
				
				echo "<pre>";
				
				$results = $type == "parents" ? $wordnet->getParents($search) : $wordnet->getChildren($search);
				
				//print_r($results);
				
				echo "</pre";
			
			}
			
		}
		
		if (!$search && !$id) {
			
			echo "Go ahead, try a search!";
			
		}
		
        ?>
    
    </div>

</div>

</body>

</html>