<?php

/*
 * Copyright (C) 2012 Johannes Bechberger
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
 * Klasse, die Methoden bereitstellt, um HTML-Code zu verschönern
 *
 * @author Johannes Bechberger
 * @copyright Johannes Bechberger
 * @license http://opensource.org/licenses/gpl-license.php
 * @package SampuSnustu
 * @subpackage Utils
 */
class HTMLBeautifier {
   /**
    * Verwendete Standardeinrückung des HTML-Codes, in Anzahl der Leerzeichen
    * @var integer
    */

   const INDENTION = 4;

   /**
    * Hash-Array mit den zuersetzenden Strings als Schlüsses und dem dazugehörigen
    * Ersatz als Wert
    * @var array 
    */
   private static $replace_chars = array(
       'ä' => '&auml;',
       'ö' => '&ouml;',
       'ü' => '&uuml;',
       'Ä' => '&Auml;',
       'Ö' => '&Ouml;',
       'Ü' => '&Uuml;',
       'ß' => '&szlig;',
   );

   /**
    * Verschönert den übergebenen HTML-Code
    * 
    * @param string $htmlstring HTML-Code
    * @return string verschönerter HTML-Code
    */
   public static function beautify($htmlstring) {
      return self::indent(self::replaceChars($htmlstring));
   }

   /**
    * Ersetzt die zuersetzenden Strings aus <u>HTMLBeautifier->replace_chars</u>
    * mit dem jeweiligen Ersatz im HTML-Code
    * 
    * @param string $htmlstring HTML-Code
    * @return string verarbeiteter HTML-Code
    */
   public static function replaceChars($htmlstring) {
      foreach (self::$replace_chars as $char => $replacement) {
         $htmlstring = str_replace($char, $replacement, $htmlstring);
      }
      return $htmlstring;
   }

   /**
    * Rückt den HTML-Code richtig ein, Programmierung ausgesetzt aufgrund drohendem
    * Abitur und der damit fehlenden Zeit
    * 
    * @param string $htmlstring
    * @return string richtig eingerückter HTML-Code
    */
   public static function indent($htmlstring) {
      //$htmlstring = ereg_replace('(\\n[\\t\\s]){0}<', '</\\1>', $htmlstring);
//        $lines = explode('\n', $htmlstring);
//        $indentedlines = array();
//        $indent = 0; //current indention
//        $tags = array();
//        foreach ($lines as $line) {
//            $line = str_replace('\t', '', str_replace(' ', '', $line));
//            if (strpos('<', $line) == 0 && !strpos('/>', $line) && !strpos('</', $line)) {
//                $arr = explode('<', $line, 1);
//                $arr = explode(' ', $arr[0], 1);
//                $tag = $arr[0];
//                if (stripos('</' . $tag . '>', $line)){
//                    $indentedlines[] = str_pad($line, $indent);
//                    continue;
//                }
//                $tags[] = $tag;
//            } else {
//                $indentedlines[] = str_pad($line, $indent);
//            }
//        }
//        return implode('\n', $indentedlines);
      return $htmlstring;
   }

}

?>
