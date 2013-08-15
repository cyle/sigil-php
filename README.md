# SIGIL-PHP

`sigil.php` provides a class, SIGIL, to use an instance of the SIGIL database.

## Usage

### Connecting

Require or include the `sigil.php` file:

    require('sigil.php');

Now make a new instance of the SIGIL client class:

    $sigil = new SIGIL();

By default, this assumes you are connecting to http://localhost:8777/ (which is the default SIGIL configuration). To change this, simply send the hostname (and port, if necessary) to the constructor, like so:

    $sigil = new SIGIL('somewhere-else.com:1337');

Leaving out the port entirely assumes you are using port 8777.

### Querying

Not done yet...

### Creating Nodes and Connections

To create a new Node, do this:

    $result = $sigil->newNode('A new node!', 1, 4, 0, array('something' => 'whatever'));

Technically you can send the `newNode()` method no arguments at all, but here they are in order: name, X, Y, Z, and extra info in an associative array. If you wanted to just set a new unnamed node at certain coords, do this:

    $result = $sigil->newNode(null, 4, 4, 4);

The result will be `true` on success and `false` on failure. See the **Errors** section below for more info on what to do if it returns `false`.

To create a new Connection, do this:

    $result = $sigil->newConnection('A new connection!', 2, 4, array('bonus' => 'lol'));

You must at least send the `newConnection()` method source and target nodes. The full list of params are: name, source node ID, target node ID, and extra info in an associative array. If you wanted to just set a new unnamed connection between two nodes, do this:

    $result = $sigil->newConnection(null, 2, 4);

That will create a new connection between nodes 2 and 4.

The result will be `true` on success and `false` on failure. See the **Errors** section below for more info on what to do if it returns `false`.

### Errors

If you call a method and provide incorrect or erroneous parameters, the call with throw an Exception with the relevant error message.

If a call results in `false`, that means there was an error of some kind. You can access the error by calling:

    $error_message = $sigil->last_error

Which will give you a string of the last error that happened. This could be a `404 not found`, or something else.

Better error reporting is a planned feature.

### Advanced Usage

You can do a raw call to the database by using the "rawCall()" method, like so:

    $get = $sigil->rawCall('/nodes'); // this will get all of the nodes as an array
    $post = $sigil->rawCall('/node', 'POST', array('Name' => 'A New Node')); // this will manually make a new node
    $delete = $sigil->rawCall('/node/3', 'DELETE'); // this will manually delete a node

The methods of the SIGIL class will do most of this for you, but you could do it yourself this way if you really wanted.

For `rawCall()`, you can send up to three arguments: the path of the API call as a string, the HTTP method to use, and finally any data you'd like to send along. The method defaults to 'GET', and the data defaults to `null`. The function will return either the expected result, or `false` on error.
