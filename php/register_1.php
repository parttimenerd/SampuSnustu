<?php

require_once "Register.php";		//TODO verwende doch einfach die FSystem::processPage() Methode
$reg = new Register();
if (isset($_REQUEST[method])) {
    echo $reg->createPageContentHTML($_REQUEST[method]);
} else {
    echo $reg->createPageContentHTML();
}
?>
