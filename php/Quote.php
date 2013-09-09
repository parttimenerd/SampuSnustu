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
 * Liest zufällig ein Zitat aus der resources/quotes aus.
 *
 * @author Johannes Bechberger
 * @copyright Johannes Bechberger
 * @license http://opensource.org/licenses/gpl-license.php
 * @package SampuSnustu
 * @subpackage Utils
 */
class Quote {

   /**
    * Array der eingelesen Zitate
    * @var string[]
    */
   private static $quotes;

   /**
    * Pfad zur Datei mit den Zitaten
    * @var string
    */

   const QUOTE_FILE = "resources/quotes.txt";

   /**
    * Gibt ein zufälliges Zitat zurück
    * 
    * @return string zufälliges Zitat 
    */
   public static function getRandomQuote() {
      if (self::$quotes == null) {
         self::readQuotes();
      }
      return self::$quotes[rand(0, count(self::$quotes) - 1)];
   }

   /**
    * Einzeugt ein zufälliges Zitat-Span und gibt es zurück, Alias für
    * <u>Quote->createRandomQuoteSpan()</u>
    * 
    * @return string Span-HTML-Code
    */
   public function __toString() {
      return self::createRandomQuoteSpan();
   }

   /**
    * Erzeugt ein zufälliges Zitat-Span und gibt es zurück
    * 
    * @return string Span-HTML-Code
    */
   public static function createRandomQuoteSpan() {
      return '<div class="randomquotediv">
            <div class="randomquote">
            ' . str_replace("\n", "<br/>", self::getRandomQuote()) . '
                </div>
          </div>';
   }

   /**
    * Liest die Zitate aus der Zitat-Datei aus und speichert sie im
    * <u>Quotes::$quotes</u> Array
    * 
    * @return boolean true, wenn Zitat-Datei existiert 
    */
   private static function readQuotes() {
      if (file_exists(__DIR__ . '/resources/quotes.txt')) {
         self::$quotes = explode("~~~\r\n", file_get_contents(__DIR__ . "/resources/quotes.txt"));
         return true;
      } else {
         file_put_contents(__DIR__ . "/resources/quotes.txt", "\n~~~\n");
         Logger::getLogger("Quote")->error('Datei "' . self::QUOTE_FILE . '" existiert nicht!!!');
         self::$quotes = array("");
         return false;
      }
   }

   /**
    * Gibt das Zitate-Array zurück
    * 
    * @return string[] 
    */
   public static function getQuotes() {
      if (!self::$quotes) {
         self::readQuotes();
      }
      return self::$quotes;
   }

}

?>
