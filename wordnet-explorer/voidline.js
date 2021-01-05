var ogma = null;

var settings = {
    loadAll: false,
    startNode: 100012748,
    algorithm: 'forcelink',
    all: false, // currently get all is not working, gets too large a graph; try using the iteration parameters below
    iterations: 30
};

var currentIterations = settings.iterations;

var nodeIDs = [];
var edgeIDs = [];

function updateGraph() {
    switch(settings.algorithm) {
        case "hierarchical":
            performDAG();
            break;
        case "forcelink":
            performFL();
            break;
    }
}

function loadData(nodeID, subsequent) {
    if (typeof (subsequent) == "undefined") subsequent = false;

    var dataURL = "/voidline/wordnet-explorer/get.php?id=" + nodeID + "&subsequent=" + subsequent + "&all=" + settings.all;
    $.ajax({
        url: dataURL,
        dataType: 'json',
        async: false,
        success: function(data) {
			data.nodes = data.nodes.filter(item => !nodeIDs.includes(item.id));
			data.edges = data.edges.filter(item => !edgeIDs.includes(item.id));
            ogma.graph.addNodes(data.nodes);
            ogma.graph.addEdges(data.edges);
			nodeIDs = [...nodeIDs, ...data.nodes.map(node => node.id)];
			edgeIDs = [...edgeIDs, ...data.edges.map(edge => edge.id)];
            if (currentIterations > 0) {
                $.each(data.nodes, function(i, node) {
                    if (node.data.hasChildren) {
                        loadData(node.id, true);
                    }
                });
                currentIterations--;
            }
            updateGraph();
        }
    });
}

function performDAG() {
    ogma.dagre.start({
        directed: true, // Take edge direction into account
        rankdir: 'TB',  // Direction for rank nodes. Can be TB, BT, LR, or RL,
                        // where T = top, B = bottom, L = left, and R = right.
        duration: 300,  // Duration of the animation
        nodesep: 30,     // Number of pixels that separate nodes horizontally in the layout.
        ranksep: 40     // Number of pixels between each rank in the layout.
    });
}

function performFL() {
    ogma.locate.center();

    ogma.layouts.start('forceLink', {
        // layout parameters
        autoStop: true,
        iterationsPerRender: 1,
        startingIterations: 1,
        slowDown: 2
    }, {
        duration: 0
    });
}

function centerGraph() {
    ogma.locate.center({
        easing: 'linear',
        duration: 300
    });
}

$(document).ready(function() {
    ogma = new Ogma({
        container: 'graph-container',
        settings: {
          tooltip: {
            className: 'ogma-tooltip'
          }
        },
        plugins: ['dagre']
    });

    ogma.events.bind({
        'captor.clickNode': function (node, x, y) {
            loadData(node.id, true);
        }
    });

    ogma.tooltip.onNodeHover(function (node) {
        return '<div>' + node.data.description + '</div>';
    });

    loadData(settings.startNode, false);
});