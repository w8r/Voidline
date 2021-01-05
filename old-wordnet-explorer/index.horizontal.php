<!DOCTYPE html>

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

	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    
	<link rel="stylesheet" href="style/style.css" type="text/css">

	<script src="d3/d3.v3.min.js" charset="utf-8"></script>
    
    <script type="text/javascript" src="d3/d3.layout.js"></script>
    
    <style type="text/css">

		.node circle {
		  cursor: pointer;
		  fill: #fff;
		  stroke: steelblue;
		  stroke-width: 1.5px;
		}
		
		.node text {
		  font-size: 11px;
		}
		
		path.link {
		  fill: none;
		  stroke: #ccc;
		  stroke-width: 1.5px;
		}

    </style>
    
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
			
			$wordnet->parseAndPrintJSON($wordnet->root, $childrenTree);
			
			/*
			
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
			
			*/

			?>
            			
			<div id="body"></div>
        
			<script type="text/javascript">
	
				var m = [20, 120, 20, 120],
					w = 1280 - m[1] - m[3],
					h = 800 - m[0] - m[2],
					i = 0,
					root;
				
				var tree = d3.layout.tree()
					.size([h, w]);
				
				var diagonal = d3.svg.diagonal()
					.projection(function(d) { return [d.y, d.x]; });
				
				var vis = d3.select("#body").append("svg:svg")
					.attr("width", w + m[1] + m[3])
					.attr("height", h + m[0] + m[2])
				  .append("svg:g")
					.attr("transform", "translate(" + m[3] + "," + m[0] + ")");
				
				d3.json("wordnet.json", function(json) {
				  root = json;
				  root.x0 = h / 2;
				  root.y0 = 0;
				
				  function toggleAll(d) {
					if (d.children) {
					  d.children.forEach(toggleAll);
					  toggle(d);
					}
				  }
				
				  // Initialize the display to show a few nodes.
				  root.children.forEach(toggleAll);
				  /*toggle(root.children[1]);
				  toggle(root.children[1].children[2]);
				  toggle(root.children[9]);
				  toggle(root.children[9].children[0]);*/
				
				  update(root);
		
				});
				
				function update(source) {
				  var duration = d3.event && d3.event.altKey ? 5000 : 500;
				
				  // Compute the new tree layout.
				  var nodes = tree.nodes(root).reverse();
				
				  // Normalize for fixed-depth.
				  nodes.forEach(function(d) { d.y = d.depth * 180; });
				
				  // Update the nodes…
				  var node = vis.selectAll("g.node")
					  .data(nodes, function(d) { return d.id || (d.id = ++i); });
				
				  // Enter any new nodes at the parent's previous position.
				  var nodeEnter = node.enter().append("svg:g")
					  .attr("class", "node")
					  .attr("transform", function(d) { return "translate(" + source.y0 + "," + source.x0 + ")"; })
					  .on("click", function(d) { toggle(d); update(d); });
				
				  nodeEnter.append("svg:circle")
					  .attr("r", 1e-6)
					  .style("fill", function(d) { return d._children ? "lightsteelblue" : "#fff"; });
				
				  nodeEnter.append("svg:text")
					  .attr("x", function(d) { return d.children || d._children ? -10 : 10; })
					  .attr("dy", ".35em")
					  .attr("text-anchor", function(d) { return d.children || d._children ? "end" : "start"; })
					  .text(function(d) { return d.name; })
					  .style("fill-opacity", 1e-6);
				
				  // Transition nodes to their new position.
				  var nodeUpdate = node.transition()
					  .duration(duration)
					  .attr("transform", function(d) { return "translate(" + d.y + "," + d.x + ")"; });
				
				  nodeUpdate.select("circle")
					  .attr("r", 4.5)
					  .style("fill", function(d) { return d._children ? "lightsteelblue" : "#fff"; });
				
				  nodeUpdate.select("text")
					  .style("fill", "#bfbfbf")
					  .style("fill-opacity", 1);
				
				  // Transition exiting nodes to the parent's new position.
				  var nodeExit = node.exit().transition()
					  .duration(duration)
					  .attr("transform", function(d) { return "translate(" + source.y + "," + source.x + ")"; })
					  .remove();
				
				  nodeExit.select("circle")
					  .attr("r", 1e-6);
				
				  nodeExit.select("text")
					  .style("fill-opacity", 1e-6);
				
				  // Update the links…
				  var link = vis.selectAll("path.link")
					  .data(tree.links(nodes), function(d) { return d.target.id; });
				
				  // Enter any new links at the parent's previous position.
				  link.enter().insert("svg:path", "g")
					  .attr("class", "link")
					  .attr("d", function(d) {
						var o = {x: source.x0, y: source.y0};
						return diagonal({source: o, target: o});
					  })
					.transition()
					  .duration(duration)
					  .attr("d", diagonal);
				
				  // Transition links to their new position.
				  link.transition()
					  .duration(duration)
					  .attr("d", diagonal);
				
				  // Transition exiting nodes to the parent's new position.
				  link.exit().transition()
					  .duration(duration)
					  .attr("d", function(d) {
						var o = {x: source.x, y: source.y};
						return diagonal({source: o, target: o});
					  })
					  .remove();
				
				  // Stash the old positions for transition.
				  nodes.forEach(function(d) {
					d.x0 = d.x;
					d.y0 = d.y;
				  });
				}
				
				// Toggle children.
				function toggle(d) {
				  if (d.children) {
					d._children = d.children;
					d.children = null;
				  } else {
					d.children = d._children;
					d._children = null;
				  }
				}
		
			</script>
        
        	<?php
			
		}
		
		if (!$search && !$id) {
			
			echo "Go ahead, try a search!";
			
		}
		
        ?>
    
    </div>

</div>

</body>

</html>