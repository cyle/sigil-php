# sigil-php

`sigil.php` provides a class, SIGIL, to act as a client of [a SIGIL database](https://github.com/cyle/sigil/).

## Usage

### Connecting

Require or include the `sigil.php` file:

    require('sigil.php');

Now make a new instance of the SIGIL client class:

    $sigil = new SIGIL();

By default, this assumes you are connecting to http://localhost:8777/ (which is the default SIGIL configuration). To change this, simply send the hostname (and port, if necessary) to the constructor, like so:

    $sigil = new SIGIL('somewhere-else.com:1337');

Leaving out the port entirely assumes you are using port 8777:

    $sigil = new SIGIL('somewhere-else.com');

That's it.

### Querying

To get all of the nodes in the database:

    $nodes = $sigil->nodes();

To get all of the connections in the database:

    $connections = $sigil->connections();

To get a specific node, where the only parameter is the unique ID of the node you want:

    $node = $sigil->node(4);

To get a specific connection, where the only parameter is the unique ID of the connection you want:

    $connection = $sigil->connection(6);

To get all of the connections attached to a specific node, where the only parameter is the unique ID of the node you want connections for:

    $connections = $sigil->nodeConnections(4);

To get all of the nodes connected (adjacent) to a specific node, where the only parameter is the unique ID of the node you want adjacent nodes for:

    $nodes = $sigil->adjacentNodes(4);

To get the shortest path between two nodes, where the first parameter is the source node ID and the second parameter is the target node ID:

    $connections = $sigil->shortestPath(4, 12);

To get the Euclidean distance between two nodes:

    $distance_float = $sigil->distance(4, 12);

What they return is dependent on what you asked for. I tried to name the above result-holding variables in a way that would hint at what you'll get back. If you get `false` as a result, that means there was an error. See the **Errors** section below.

### Creating Nodes and Connections

To create a new node, do this:

    $result = $sigil->newNode('A new node!', 1, 4, 0, array('something' => 'whatever'));

Technically you can send the `newNode()` method no arguments at all, but here they are in order: name, X, Y, Z, and extra info in an associative array. If you wanted to just set a new unnamed node at certain coords, do this:

    $result = $sigil->newNode(null, 4, 4, 4);

The result will be `true` on success and `false` on failure. See the **Errors** section below for more info on what to do if it returns `false`.

To create a new connection, do this:

    $result = $sigil->newConnection('A new connection!', 2, 4, array('bonus' => 'lol'));

You must at least send the `newConnection()` method source and target nodes. The full list of params are: name, source node ID, target node ID, and extra info in an associative array. If you wanted to just set a new unnamed connection between two nodes, do this:

    $result = $sigil->newConnection(null, 2, 4);

That will create a new connection between nodes 2 and 4.

The result will be `true` on success and `false` on failure. See the **Errors** section below for more info on what to do if it returns `false`.

### Updating Nodes and Connections

To update an existing node, do this:

    $result = $sigil->updateNode( array('Id' => 1, 'Name' => 'Updated name!') );

That will update the node with the unique ID 1 to an updated name. Set whatever attributes in an associative array and send it along to update a node.

The same is true for connections:

    $result = $sigil->updateConnection( array('Id' => 1, 'Name' => 'Updated name!', 'Source' => 1, 'Target' => 10) );

Note that when updating a connection you **must** supply the `Source` and `Target` attributes even if you are not modifying them.

### Deleting Nodes and Connections

To delete a specific node:

    $result = $sigil->deleteNode(11);

That will delete the node with the unique ID 11, and all of the connections attached to that node.

To delete a specific connection:

    $result = $sigil->deleteConnection(5);

That will delete the connection with the unique ID 5.

### Errors

If you call a method and provide incorrect or erroneous parameters, the call with throw an Exception with the relevant error message. Exceptions are thrown when the data you provide the sigil client is wrong in some way. If the data is alright and something is wrong on the database end, you will get `false` as a result.

If a call results in `false`, you can access the error message from the database server by calling:

    $error_message = $sigil->last_error

Which will give you a string of the last error that happened. This could be a `404 not found` (if you were looking for a node that doesn't exist, for example), or something else.

Better error reporting is a planned feature.

### Advanced Usage

You can do a raw call to the database by using the "rawCall()" method, like so:

    $get = $sigil->rawCall('/nodes'); // this will get all of the nodes as an array
    $post = $sigil->rawCall('/node', 'POST', array('Name' => 'A New Node')); // this will manually make a new node
    $delete = $sigil->rawCall('/node/3', 'DELETE'); // this will manually delete a node

The methods of the SIGIL class will do most of this for you, but you could do it yourself this way if you really wanted.

For `rawCall()`, you can send up to three arguments: the path of the API call as a string, the HTTP method to use, and finally any data you'd like to send along. The method defaults to 'GET', and the data defaults to `null`. The function will return either the expected result, or `false` on error.
