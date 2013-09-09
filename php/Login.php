<?php

require_once 'FSystem.php';

/**
 * Login-Seite für die Benutzeranmeldung
 * @author Julian 
 */
class Login implements Page {

    /**
     * Methode, mit der die Login-Seite aufgerufen wird.
     * @param string $method 
     */
    private $method = "default";

    /**
     * Setzt beim Starten die private $method auf den Wert.
     * @param string $method 
     */
    public function __construct($method = "default") {
        $this->method = $method;
    }

    /**
     * Erzeugt den kompletten HTML-Code für die Registrierungsseite.
     * @param string $method
     * @return string 
     */
    public function createPageContentHTML($method = "default", $link = "") {
        switch ($method) {
            case "default":
                return $this->createDefaultHTML();
            case "login":
                return $this->createLoginHTML();
        }
    }

    /**
     * Gibt den Eingabeteil der Anmeldeseite aus.
     * @return string 
     */
    public function getLoginHTMLpur() {
        return "<form action='" . FSystem::URL . "forum.php' method='GET'>
                <h1>Anmeldung</h1>
                <table border='1'>
                    <tr><td>Username: </td><td><input type='text' name='username' /></td></tr>
                    <tr><td>Passwort: </td><td><input type='password' name='passwort'/></td></tr>
                </table>
                <input type='submit' value='Anmelden' />
                <input type='hidden' name='login'/>
                </form>
        ";
    }

    public function getPageTitle() {
        return '';
    }

    /**
     * Erzeugt den HTML-Code, der im Falle der "default"-Methode aufgerufen wird.
     * @return string 
     */
    public function createDefaultHTML() {
        $html = "";
        if (isset($_REQUEST['passwort']) && ($_REQUEST['passwort'] != "") && isset($_REQUEST['username']) && ($_REQUEST['username'] != "") && FSystem::verifyUser($_REQUEST['name'], $_REQUEST['passwort'])) {
            $html = '<p>Anmeldung erfolgreich</p>
                    <a href="' . FSystem::URL . $link . '?' . FSystem::getURLAppendix() . '">
                        Weiter zur vorherigen Seite</a>';
        } elseif (isset($_REQUEST['passwort']) && $_REQUEST['passwort'] == "") {
            $html = $this->getLoginHTMLpur();
            $html .= "<p>Passwort erforderlich</p>";
        } elseif (isset($_REQUEST['username']) && $_REQUEST['username'] == "") {
            $html = $this->getLoginHTMLpur();
            $html .= "<p>Username erforderlich</p>";
        } else {
            $html = $this->getLoginHTMLpur();
        }
        return $html;
    }

    /**
     * Erzeugt den HTML-Code, wenn die Methode "login" ist, also der User auf den Anmelden-Button geklickt hat.
     */
    public function createLoginHTML() {
        return "";
    }

}

?>
