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
 * Erzeugt RSS Feeds
 *
 * @author Johannes Bechberger
 * @copyright Johannes Bechberger
 * @license http://opensource.org/licenses/gpl-license.php
 * @package SampuSnustu
 * @subpackage Forum
 */
class RSS {

   /**
    * Erzeugt den RSS-XML-Code des in der URL spezifizierten FObject-Objektes
    * und gibt ihn zurück
    * 
    * @return string erzeugter RSS-XML-Code wenn erfolgreich, ansonsten ''
    */
   public static function createByRequest() {
      if (isset($_REQUEST["fid"]) && is_numeric($_REQUEST["fid"])) {
         return self::createFObjectRSS(FSystem::getForumById($_REQUEST["fid"]));
      } else if (isset($_REQUEST["tid"]) && is_numeric($_REQUEST["tid"])) {
         return self::createFObjectRSS(FSystem::getThreadById($_REQUEST["tid"]));
      }
      return '';
   }

   /**
    * Erzeugt den RSS-XML-Code des übergebenen Thread-Objektes und gibt ihn zurück
    * 
    * @param Thread $fobject Thread-Objekt
    * @return string erzeugter RSS-XML-Code wenn erfolgreich, ansonsten ''
    */
   public static function createThreadRSS($thread) {
      if ($thread) {
         return self::createFObjectRSS(get_class($thread) == "Thread" ? $thread : FSystem::getThreadById($thread));
      }
      return '';
   }

   /**
    * Erzeugt den RSS-XML-Code des übergebenen FObject-Objekt und gibt ihn zurück
    * 
    * @param FObject $fobject FObject-Objektes
    * @param boolean $setheader wenn true, wird der Header auf
    * "Content-Type: application/rss+xml" gesetzt
    * @return string erzeugter RSS-XML-Code wenn erfolgreich, ansonsten ''
    */
   public static function createFObjectRSS(FObject $fobject, $setheader = true) {
      if ($fobject && is_a($fobject, 'Thread')) {
         if ($setheader) {
            header("Content-Type: application/rss+xml");
         }
         $entries = $fobject->getLastEntries();
         $html = '
          <?xml version="1.0" encoding="ISO-8859-1"?>
            <rss version="2.0">
            <channel>
                <title>' . $fobject->getTitle() . '</title>
                <link>' . FSystem::URL . 'forum.php?' . (get_class($fobject) == 'Forum' ? 'fid' : 'tid') . '=' . $fobject->getID() . '</link>
                <description>' . $fobject->getDescription() . '</description>
                <language>de-de</language>
                <pubDate>' . $entries ? date("r", $entries[count($entries) - 1]->getMTime()) : date("r") . '</pubDate>';
         if ($entries) {
            foreach ($entries as $entry) {
               $html .= '  
                    <item>
                        <title>' . $entry->getThread()->getTitle() . ' | ' . $entry->getUser()->getName() . '</title>
                        <link>' . FSystem::URL . 'forum.php?method=show&eid=' . $entry->getID() . '</link>
                        <description><![CDATA[' . $entry->getContent() . ']]></description>
                        <pubDate>' . date("r", $entry->getCTime()) . '</pubDate>
                    </item>';
            }
         }
         return $html . '
                </channel>
            </rss>';
      }
      return '';
   }

   /**
    * Erzeugt den HTML-Link-Button zum Verweis auf den jeweiligen Feed
    * 
    * @param FObject $object Forumobjekt-Objekt (zur Zeit nur Thread-Objekte
    * unterstützt)
    * @return string HTML-Link-Button-Code
    */
   public static function createRSSHTML($object) {
      if (is_a($object, "Thread")) {
         return '<a class="rsslink" href="rss.xml?tid=' . $object->getID() . '">
                <img src="img/feed-icon-24x24.gif" title="RSS-Feed dieses Threads abbonieren"/>
                </a>';
      }
      return '';
   }

}

?>
