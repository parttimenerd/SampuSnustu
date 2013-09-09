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
 * Suchklasse
 *
 * @author Johannes Bechberger
 * @copyright Johannes Bechberger
 * @license http://opensource.org/licenses/gpl-license.php
 * @package SampuSnustu
 * @subpackage Forum
 */
class Search implements Page {

   /**
    * Einstellungen-Hash-Array
    * @var array
    */
   private $options;

   /**
    * Einstellungen-Hash-Array mit den default-Werten
    * @var array 
    */
   private static $defoptions = array("filter" => "", "regexp" => "no",
       "noforums" => "no", "nothreads" => "no", "noentries" => "no",
       "userfilter" => "");

   /**
    * Konstruktor der Search-Klasse
    * 
    * @param array $options Einstellungen-Hash-Array: Es sind folgende Einstellungen
    * möglich: [Schema: $key => $value] "filter" => [Filter bzw. Suchwort],
    * "regexp" => ["no"], "nothreads" => ["no"], "noentries" => ["no"],
     "userfilter" => [Filter für den Benutzernamen]
    */
   public function __construct(Array $options) {
      $this->options = FSystem::filterFDBArr(FSystem::extend($options, self::$defoptions));
   }

   /**
    * Erzeugt ein neues Suchobjekt mit den URL-Übergabe-Parametern als Einstellungen
    * und gibt es zurück
    * 
    * @return Search erzeugtes Suchobjekt
    */
   public static function createByRequest() {
      return new Search($_REQUEST);
   }

   /**
    * Erzeugt den HTML Code einer Suchleiste
    * 
    * @return string Suchleisten-HTML-Code, "<span class="searchbar">[...]</span>"
    */
   public static function createSearchBar() {
      $sword = isset($_REQUEST['filter']) ? $_REQUEST['filter'] : '';
      return '<span class="searchbar">
            <form action="search.php" method="GET" class="search">
                <input class="search_field" type="search" name="filter" ' . ($sword ? 'value="' . htmlentities($sword) . '"' : 'placeholder="Suche..."') . '/>
                <input type="submit" value=""/>
                <div class="options">
                    <span class="options_header">Optionen</span><br/>
                    <div class="options_items">
                        <input type="checkbox" name="regexp" value="yes" ' . (isset($_REQUEST['regexp']) ? 'selected="selected"' : '') . '/>Regul&auml;re Ausdr&uuml;cke<br/>
                        <input type="checkbox" name="noforums" value="yes" ' . (isset($_REQUEST['noforums']) ? 'selected="selected"' : '') . '/>Keine Foren<br/> 
                        <input type="checkbox" name="nothreads" value="yes" ' . (isset($_REQUEST['nothreads']) ? 'selected="selected"' : '') . '/>Keine Threads<br/>
                        <input type="checkbox" name="noentries" value="yes" ' . (isset($_REQUEST['noentries']) ? 'selected="selected"' : '') . '/>Keine Eintr&auml;ge<br/> 
                        <span class="userfiltlerspan">Benutzer <input type="search" placeholder="Benutzerfilter..." name="userfilter" value="' . (isset($_REQUEST["userfilter"]) ? $_REQUEST["userfilter"] : '') . '" class="userfilter" list="search_user_datalist"/>
                            ' . (!preg_match('/MSIE/i', $_SERVER['HTTP_USER_AGENT']) ? self::getUserDatalist() : '') . '
                        </span><br/> 
                        ' . FSystem::getFormAppendix() . '
                    </div>
                </div>
            </form>
          </span>';
   }

   /**
    * Erzeugt den HTML-Code des Seiteninhaltes der Suchseite
    * 
    * @param string $method Methodenstring, hat keine Bedeutung
    * @return string Seiteninhalt-HTML-Code
    */
   public function createPageContentHTML($method = "default") {
      $likestr = $this->options["regexp"] == "no" ? "LIKE" : "REGEXP";
      $likepre = $this->options["regexp"] == "no" ? '"%' : '"';
      $likepost = $this->options["regexp"] == "no" ? '%"' : '"';
      $html = '';
      $appendix = '';
      $fromstr = '';
      $headerapp = '';
      $time = microtime(true);
      if ($this->options["userfilter"] && $this->options["userfilter"] != '') {
         $appendix = ' AND creator = user.id && user.name ' . $likestr . ' ' . $likepre . $this->options["userfilter"] . $likepost;
         $fromstr = ', user';
         $headerapp = ' mit Benutzerfilter "' . $this->options["userfilter"] . '"';
      }
      $num = 0;
      if ($this->options["noforums"] != "yes") {
         $forumres = FDB::$db->query('SELECT * FROM forum' . $fromstr . ' WHERE title ' . $likestr . ' ' . $likepre . $this->options["filter"] . $likepost . ' OR description ' . $likestr . ' ' . $likepre . $this->options["filter"] . $likepost . $appendix);
         if ($forumres) {
            while ($forum = Forum::createFromFDBResult($forumres)) {
               $html .= "\n" . $forum->createSpecificHTML("info");
               $num++;
            }
         }
      }
      if ($this->options["nothreads"] != "yes") {
         $threadres = FDB::$db->query('SELECT * FROM thread' . $fromstr . ' WHERE title ' . $likestr . ' ' . $likepre . $this->options["filter"] . $likepost . ' OR description ' . $likestr . ' ' . $likepre . $this->options["filter"] . $likepost . $appendix);
         if ($threadres) {
            while ($thread = Thread::createFromFDBResult($threadres)) {
               $html .= "\n" . $thread->createSpecificHTML("info");
               $num++;
            }
         }
      }
      if ($this->options["noentries"] != "yes") {
         $entryres = FDB::$db->query('SELECT * FROM threadentry' . $fromstr . ' WHERE content ' . $likestr . ' ' . $likepre . $this->options["filter"] . $likepost . $appendix);
         if ($entryres) {
            while ($entry = Entry::createFromFDBResult($entryres)) {
               $html .= "\n" . $entry->createHTML();
               $num++;
            }
         }
      }
      $headerapp .= ' &rArr; ' . ($num == 0 ? 'keine' : $num) . ' Treffer in ' . round(microtime(true) - $time, 3) . ' Sekunden';
      return '<span class="search_header">Suche "' . $this->options["filter"] . '"' . $headerapp . '</span><br/>
            <table class="entry_table">
                ' . $html . '
            </table>' . (isset($_SERVER['HTTP_REFERER']) && $_SERVER['HTTP_REFERER'] ? '<br/><a href="' . $_SERVER['HTTP_REFERER'] . '&' . FSystem::getURLAppendix() . '" class="go_back search_go_back">Zurück zur vorherigen Seite</a>' : '');
   }

   /**
    * Gibt den Seitentitel zurück
    * 
    * @return string Seitentitel
    */
   public function getPageTitle() {
      return FSystem::buildPageTitle('Suche: "' . $this->options['filter'] . '"');
   }

   /**
    * Erzeugt den HTML-Code eines HTMl-Datalist-Elements mit den Benutzernamen
    * als Werte zurück
    * 
    * @param string $id ID des HTML-Elements
    * @return string Datalist-HTML-Code
    */
   public static function getUserDatalist($id = "search_user_datalist") {
      $usernames = FSystem::getUserNames();
      sort($usernames);
      $html = '<datalist id="' . $id . '" class="search_datalist">';
      foreach ($usernames as $name) {
         $html .= '<option value="' . $name . '"/>';
      }
      return $html . '</datalist>';
   }
}

?>