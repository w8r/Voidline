var ogma = null;

var settings = {
    loadAll: false,
    startNode: 100001740,
    algorithm: 'forcelink',
    all: false
};

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
    $.getJSON(("/voidline/wordnet-explorer/get.php?id=" + nodeID + "&subsequent=" + subsequent + "&all=" + settings.all), function(data) {
        ogma.graph.addNodes(data.nodes);
        ogma.graph.addEdges(data.edges);
        updateGraph();
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