<?php

/*
 * Copyright (C) 2012 Julian Quast
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

require_once 'FSystem.php';

class UserEdit implements Page {

    /**
     * User, der editiert werden soll.
     * @var User 
     */
    private $toEditUser;

    /**
     * Erzeugt den kompletten HTML-Code für die UserEdit-Seite in Abhängigkeit von der Methode.
     * @param type $method
     * @return string 
     */
    public function createPageContentHTML($method = "default") {
        if (isset($_REQUEST['id'])) {
            $this->toEditUser = new User($_REQUEST['id']);
        }
        if ($method == "default") {
            if ($_REQUEST['sid'] == $_REQUEST['id']) {
                // Unsicher !!! nachfragen
                return $this->createUserSelfEditHTML();
            } else {
                return "<p class='error'>Sie haben keine Berechtigung andere User zu editieren, oder ändern sie die Methode in \"adminedit\"</p>";
            }
        } else if ($_REQUEST['method'] == "adminedit") {
            if (FSystem::isAdmin()) {
                return $this->createUserEditHTML();
            } else {
                return "<p class='error'>Sie dürfen keine User im Adminedit-Modus editieren</p>";
            }
        } else if ($_REQUEST['method'] == "edit") {
            if (FSystem::getID() == $this->toEditUser->getID() && !FSystem::isDefaultUser()) {
                return $this->createUserSelfEditHTML();
            } else {
                return "<p class='error'>Sie haben keine Berechtigung andere User zu editieren, oder ändern sie die Methode in \"adminedit\"</p>";
            }
        } else if ($_REQUEST['method'] == "change") {
            if (FSystem::isAdmin() || $_REQUEST['name'] == FSystem::getUser()->getName()) {
                return $this->createUserChangingHTML();
            } else {
                return "<p class='error'>Sie versuchen einen fremden User zu editieren, oder Sie sind kein Admin.</p>";
            }
        }
    }

    /**
     * Erzeugt den HTML-Code der Edit-Seite
     * @return string 
     */
    public function createUserEditHTML() {
        $html = "";
        $html .= "<h1>Editieren: " . $this->toEditUser->getName() . "  </h1>";
        $html .= "<form method='get' action='" . FSystem::URL . "useredit.php'>";  //TODO FormAppendix hinzufügen
        $html .= $this->toEditUser->getUserEditHTML();
        $html .= "<input type='hidden' name='method' value='change' />";
        $html .= "<input type='Submit' value='Edit' name='Edit'>";
        $html .= FSystem::getFormAppendix();
        $html .= "</form>";
        return $html;
    }

    /**
     * Gibt den HTML-Code zurück, für die Seite, auf der der User sein eigenes Profil editieren kann.
     * @return string
     */
    public function createUserSelfEditHTML() {
        $html = "";
        $html .= "<h1>Editieren: " . $this->toEditUser->getName() . "  </h1>";
        $html .= "<form method='get' action='" . FSystem::URL . "useredit.php'>";  //TODO FormAppendix hinzufügen
        $html .= $this->toEditUser->getUserSelfEditHTML();
        $html .= "<input type='hidden' name='method' value='change' />";
        $html .= "<input type='Submit' value='Edit' name='Edit'>";
        $html .= FSystem::getFormAppendix();
        $html .= "</form>";
        return $html;
    }

    /**
     * Speichert die Daten, die nach dem Editieren übergeben werden.
     * @return string
     */
    public function createUserChangingHTML() {
        FDB::connect();

        $_REQUEST['name'] = FSystem::filter($_REQUEST['name']);
        $_REQUEST['signatur'] = FSystem::filter($_REQUEST['signatur']);
        $_REQUEST['description'] = FSystem::filter($_REQUEST['description']);
        $_REQUEST['mailadress'] = FSystem::filter($_REQUEST['mailadress']);

        FDB::$db->query("UPDATE user SET 
                                name = '" . $_REQUEST['name'] . "', 
                                mailadress = '" . $_REQUEST['mailadress'] . "', 
                                signatur = '" . $_REQUEST['signatur'] . "',
                                description = '" . $_REQUEST['description'] . "' 
                            WHERE id=" . $_REQUEST['id']) or die(mysqli_error(FDB::$db));
        return "<span class='success'>Daten erfolgreich gespeichert.</span>
                <a href='userlist.php?" . FSystem::getURLAppendix() . "'>Userliste</a>
                ";
    }

    /**
     * Erzeugt den Seitentitel
     * @return string 
     */
    public function getPageTitle() {
        return FSystem::buildPageTitle("UserEdit");
    }

}

?>
