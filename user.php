<?php

require_once "php/FSystem.php";
if (isset($_REQUEST['id']) && isset($_REQUEST['method'])) {
    echo FSystem::processPage(new User($_REQUEST['id'], $_REQUEST['method']));
} else {
    echo FSystem::processPage(new User());
}
?>
