<?php

/*
 * Copyright (C) 2011-2012 Johannes Bechberger
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

/**
 * Abstrakte Klasse, die als Elternklasse der Klassen Forum und Thread dient und
 * wichtige Methoden schon implementiert
 *
 * @author Johannes Bechberger
 * @copyright Johannes Bechberger
 * @license http://opensource.org/licenses/gpl-license.php
 * @package SampuSnustu
 * @subpackage Forum
 */
abstract class FObject extends HObject implements Page {

    /**
     * Ersteller dieses Forumobjekt-Objekt
     * @var User
     */
    protected $user;

    /**
     * Eltern-Forumobjekt-Objekt
     * @var Forum
     */
    protected $parent;

    /**
     * Titel des Forumobjekt-Objektes
     * @var string 
     */
    protected $title;

    /**
     * Beschreibung des Forumobjekt-Objektes
     * @var string
     */
    protected $description;

    /**
     * Anzahl der Seiten, dieses Forumobjekt-Objektes
     * @var integer
     */
    protected $page_count = -1;

    /**
     * Anzahl der Einträge, die direkt in diesem Forumobjekt-Objekt geschrieben
     * wurden
     * @var integer 
     */
    protected $entry_count;

    /**
     * Aktuelle Seite dieses Forumobjekt-Objektes
     * @var integer
     */
    protected $page = -1;

    /**
     * Konstruktor der FObjekt-Klasse
     * 
     * @param integer $id ID
     * @param integer|User $creator Ersteller(-ID)
     * @param integer|FObject $parent Eltern-Forumobjekt-Objekt(-ID)
     * @param string $title Titel
     * @param string $description Beschreibung
     * @param integer $ctime Unix-Zeit der Erstellung
     * @param integer $mtime Unix-Zeit der letzen Veränderung
     */
    public function __construct($id, &$creator, &$parent, $title, $description, $ctime, $mtime) {
        parent::__construct($ctime, $mtime, $id);
        if (is_numeric($creator)) {
            $this->user = FSystem::getUserById($creator);
        } else {
            $this->user = $creator;
        }
        if (is_numeric($parent)) {
            $this->parent = FSystem::getForumById($parent);
        } else {
            $this->parent = $parent;
        }
        $this->title = $title;
        $this->description = $description;
    }

    /**
     * Erstellt ein Forumobjekt-Objekt per übergebener ID aus der Datenbank
     * 
     * @param integer $id ID
     * @return FObject Forumobjekt-Objekt wenn erfolgreich, ansonsten null
     */
    public static abstract function createByID($id);

    /**
     * Gibt den Ersteller zurück
     * 
     * @return User 
     */
    public function getCreator() {
        return $this->creator;
    }

    /**
     * Gibt den Titel zurück
     * 
     * @return string Titel 
     */
    public function getTitle() {
        return $this->title;
    }

    /**
     * Gibt den Seitentitel zurück
     * 
     * @return string Seitentitel
     */
    public function getPageTitle() {
        return FSystem::buildPageTitle($this->title);
    }

    /**
     * Ersetzt den aktuellen Titel durch den übergebenen
     * 
     * @param string $new_title neuer Titel 
     * @return boolean true wenn erfolgreich
     */
    public abstract function setTitle($new_title);

    /**
     * Gibt die Beschreibung zurück
     * 
     * @return string Beschreibung 
     */
    public function getDescription() {
        return $this->description;
    }

    /**
     * Ersetzt die aktuelle Beschreibung durch die übergebene
     * 
     * @param string $new_description neue Beschreibung 
     * @return boolean true wenn erfolgreich
     */
    public abstract function setDescription($new_description);

    /**
     * Löscht das Forumobjekt-Objekt aus der Datenbank
     * 
     * @return boolean true wenn erfolgreich
     */
    public abstract function delete();

    /**
     * Gibt das Eltern-Forumobjekt-Objekt zurück
     * 
     * @return FObject Eltern-Forumobjekt-Objekt
     */
    public function getParent() {
        return $this->parent;
    }

    /**
     * Ersetzt das alte Eltern-Forumobjekt-Objekt durch das übergebene
     * 
     * @param FObject $new_parent neues Eltern-Forumobjekt-Objekt 
     * @return boolean true wenn erfolgreich
     */
    public abstract function setParent($new_parent);

    /**
     * Holt eine Zeile aus dem Datenbankabfrage-Result und erzeugt daraus
     * ein Forumobjekt-Objekt
     * 
     * @param mysqli_result $mysql_result Datenbankabfrage-Result
     * @return boolean|FObject Forumobjekt-Objekt wenn erfolgreich, sonst null
     */
    public static abstract function createFromFDBResult($mysql_result);

    /**
     * Erzeugt rekursiv den Location-Pre-HTML-Code-String des Forumobjekt-Objektes
     * 
     * @param boolean $last letztes Objekt in der Reihe?
     * @return string HTML-Code
     */
    public function createLocationPreHTML($last = false) {
        if ($this->parent != null) {
            return $this->parent->createLocationPreHTML() . ' &rArr; <a href="' . $this->getURL() . '"' . ($last ? ' id="titleLocation"' : '') . '>' . $this->title . '</a>';
        }
        return '<a href="' . $this->getURL() . '"' . ($last ? ' id="titleLocation"' : '') . '>' . $this->title . '</a>';
    }

    /**
     * Erzeugt den Location-HTML-Code, des Forumobjekt-Objektes, der die
     * Postion des Forumobjekt-Objektes im Forenbaum anzeigt
     * 
     * @return string Location-HTML-Code-Span
     */
    public function createLocationHTML() {
        return '<span id = "location">' . $this->createLocationPreHTML(true) . '</span>';
    }

    /**
     * Gibt die aktuelle URL zurück 
     * 
     * @return string aktuelle URL
     */
    public abstract function getURL();

    /**
     * Gibt ein <u>$length</u> Forumobjekt-Objekte fassendes Array mit den 
     * Forumobjekt-Objekten, zurück, deren Position im Forumobjekt-Objekt
     * mindestens <u>$begin</u> ist
     * 
     * @param $begin Postion des ersten Eintrages im Forumobjekt-Objekte
     * @param $length Anzahl der Forumobjekt-Objekte
     * @return Forum[] Forumobjekt-Objekte-Array
     */
    public abstract function getEntries($begin, $length);

    /**
     * Erzeugt den HTML-Code des Forumobjekt-Objektes abhängig von dem übergebenen
     * Methodenstring und gibt ihn zurück
     * 
     * @param string $method Methodenstring
     * @return string HTML-Code
     */
    public function createHTML($method = "default") {
        $html = '';
        $spechtml = $this->createSpecificHTML($method);
        if ($method == "default") {
            $html = $this->createLocationHTML() . "<br/>\n";
        }
        $html .= '<table class = "entry_table">
        ' . $this->createDescriptionHTML() . "\n" . $spechtml;
        return $html . '</table>' . (($this->getPage() >= $this->getPageCount() - 1) && !FSystem::isDefaultUser() ? "\n" . $this->getCreateDiv() : '');
    }

    /**
     * Erzeugt den spezifischen HTML-Code des Forumobjekt-Objektes abhängig von
     * dem übergebenen Methodenstring und gibt ihn zurück
     * 
     * @param string $method Methodenstring
     * @return string HTML-Code
     */
    public abstract function createSpecificHTML($method);

    /**
     * Gibt den Beschreibung-HTML-Code dieses Forumobjekt-Objektes zurück
     * 
     * @return string Beschreibung-HTML-Code-Div
     */
    public function createDescriptionHTML() {
        return '
            <tr>
                <td colspan="2" class="header description_header ">' . $this->getSpecificDescriptionHeader() . '</td>
            </tr>
            <tr class="entry description description_content">
                <td colspan="2">
                    <div class="entry_content description_content" id="description_content">
                    ' . ($this->isEditable() ? $this->getEditDiv() : '') . '
                    ' . $this->description . '
                    </div>
                </td>
            </tr>';
    }

    /**
     * Gibt den Editier-HTML-Code dieses Forumobjekt-Objektes zurück
     * 
     * @param string $fid Forum-ID, wenn in ein bestimmtes Forum, nachfolgend
     * gegangen werden soll
     * @return string Editier-HTML-Code-Div
     */
    public function getEditDiv($fid = '') {
        return '<div class = "textbox">
          <span class = "text">Editieren</span><br/>
          <div class="edit_div">
            <form action="forum.php" method="POST">
                <table>
                    <tr><td colspan="3" class="create_header">Editieren</td></tr>
                    <tr><td><input type="text" name="title" value="' . $this->title . '"/></td></tr>
                    <tr><td><textarea name="descr">' . $this->description . '</textarea></td></tr>
                    <tr>
                        <td>
                            <table>
                                <tr>
                                    <td><input type="reset" value="Reset"/></td>
                                    <td><input type="submit" value="Abschicken"/>
                                    <input type="hidden" name="method" value="forumedit"/></td>
                                </tr>
                             </table>
                         </td>
                    </tr>
                </table>
                ' . $this->getEditFormAppendix($fid) . '
                ' . FSystem::getFormAppendix() . '
            </form>
            ' . ($this->getID() != 1 ? $this->getDeleteForm() : '') . '
           </div>
        </div>';
    }

    /**
     * Gibt den Untergeordnetes-Forumobjekt-Objekt-Erzeugen-HTML-Code dieses 
     * Forumobjekt-Objektes zurück
     * 
     * @return string Untergeordnetes-Forumobjekt-Objekt-Erzeugen-HTML-Code-Div
     */
    public abstract function getCreateDiv();

    /**
     * Gibt den spezifischen Headertext des Beschreibung-HTML-Code-Div zurück
     * 
     * @return string Beschreibung-HTML-Code-Div-Headertext
     */
    public abstract function getSpecificDescriptionHeader();

    /**
     * Gibt die Seitenanzahl zurück
     * 
     * @return integer Seitenanzahl
     */
    public function getPageCount() {
        if ($this->page_count == -1) {
            $this->page_count = $this->getSpecificPageCount();
        }
        return $this->page_count;
    }

    /**
     * Gibt die Nummer der aktuellen Seite zurück
     * 
     * @return integer aktuelle Seitennummer (die erste Seite hat die Seitennummer 0)
     */
    public function getPage() {
        if ($this->page == -1) {
            $page = isset($_REQUEST["page"]) ? $_REQUEST["page"] : -1;
            $this->page = $page < $this->getPageCount() ? $page : $this->getPageCount() - 1;
        }
        return $this->page < 0 ? 0 : $this->page;
    }

    /**
     * Ersetzt die aktuelle Seitennummer nach Überprüfung durch die übergebene
     * 
     * @param integer $page neue Seitennummer (die erste Seite hat die Seitennummer 0)
     */
    public function setPage($page) {
        if ($page >= $this->getPageCount()) {
            $this->page = $this->getPageCount() - 1;
        } else {
            $this->$page = $page < 0 ? 0 : $page;
        }
    }

    /**
     * Gibt zurück, ob das Forumobjekt-Objekt vom aktuellen Benutzer editiert
     * werden kann
     * 
     * @return boolean true, wenn das Forumobjekt-Objekt vom aktuellen Benutzer
     * editiert werden kann
     */
    public function isEditable() {
        return FSystem::isAdmin() || (FSystem::getUser() == $this->user && !FSystem::isDefaultUser());
    }

    /**
     * Gibt die spezifische Seitenanzahl zurück
     * 
     * @return integer spezifische Seitenanzahl 
     */
    public abstract function getSpecificPageCount();

    /**
     * Speichert ein neues Forumobjekt-Objekt in der Datenbank und gibt es zurück
     * 
     * @param integer|User $creator Ersteller(-ID)
     * @param integer|FObject $parent Elternforumobjekt-Objekt(-ID)
     * @param string $title Titel
     * @param string $description Beschreibung
     * @return null|FObject Forumobjekt-Objekt wenn erfolgreich, ansonsten null
     */
    public abstract static function store($creator, $parent, $title, $description);

    /**
     * Gibt den HTML-Code zurück, der im Editier-HTML-Code-Div-Formular
     * hinzugefügt werden muss
     * 
     * @return string HTML-Code
     */
    public abstract function getEditFormAppendix();

    /**
     * Gibt den HTML-Code zurück, der im Objekt-Löschen-Formular
     * hinzugefügt werden muss
     * 
     * @return string HTML-Code
     */
    public abstract function getDeleteFormAppendix();

    /**
     * Gibt das Objekt-Löschen-Formular zurück
     * 
     * @return string Objekt-Löschen-Formular-HTML-Code
     */
    public function getDeleteForm() {
        return '<form action = "forum.php" method = "POST" class = "delete_form">
            <input type = "submit" value = "Löschen"/>
            <input type = "hidden" name = "method" value = "delete"/>
        ' . $this->getDeleteFormAppendix() . '
        ' . FSystem::getFormAppendix() . '
        </form>';
    }

    /**
     * Alias für die <u>FObject->createHTML()</u>-Methode
     * 
     * @param string $method Methodenstring
     * @return string HTML-Code 
     */
    public function createPageContentHTML($method = "default") {
        return $this->createHTML($method);
    }
}

?>
