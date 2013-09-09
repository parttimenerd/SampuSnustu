<?php

set_time_limit(0);

require_once '/php/foreign_code/php-websocket/server/server.php';
require_once '/php/ChatServerApplication.php';

$server = new \WebSocket\Server('localhost', 8000);

// server settings:	
$server->setCheckOrigin(true);
$server->setAllowedOrigin('foo.lh');
$server->setMaxClients(50);
$server->setMaxConnectionsPerIp(5);
$server->setMaxRequestsPerMinute(1000);

$server->registerApplication('chatserver', ChatServerApplication::getInstance());
$server->run();
?>
