<?php
require_once 'php/FSystem.php';
echo isset($_REQUEST["ajax"]) ? FSystem::getChat()->createHTML(FSystem::getMethod()) : FSystem::processPage(FSystem::getChat());
?>
