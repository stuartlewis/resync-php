<?php

include '../../config/resync-config.php';

?><html>
<head>
    <title>ResourceSync web test</title>
</head>
<body>
<h1>ResourceSync PHP Client Library</h1>

<?php
// Test http options
include '../../util/http.php';
echo "<strong>Testing http method used:</strong> ";
show_method();
echo '<br /><br />';
echo '<strong>Output:</strong><br />';

?>
</body>
</html>