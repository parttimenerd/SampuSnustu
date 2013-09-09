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
 * Template-Klasse
 *
 * @author Johannes Bechberger
 * @copyright Johannes Bechberger
 * @license http://opensource.org/licenses/gpl-license.php
 * @package SampuSnustu
 * @subpackage Utils
 */
class Template {

   /**
    * Hash-Array mit dem Kurznamen des Templatestils als Schlüssel und dem jeweiligen
    * Eigenschaften-Hash-Array als Wert
    * 
    * Aufbau:
    * <code>
    * $styles = array(
    *   "default" => array(
    *      "name" => "Default Template",
    *      "further_css_files" => array(),
    *      "further_js_files" => array(),
    *      "css" => "",
    *      "js" => "",
    *   )
    * );
    * </code>
    * 
    * @var array
    * @todo Mehr Templatestile implementieren
    */
   private static $styles = array(
       "default" => array(
           "name" => "Default Template",
           "further_css_files" => array(),
           "further_js_files" => array(),
           "css" => "",
           "js" => "",
       )
   );

   /**
    * Erstellt eine HTML-Seite abhängig von den übergebenen Parametern
    * 
    * @param Page $page Page-Objekt dessen <u>createPageHTML</u> Methode aufgerufen
    * wird
    * @param string $stylename Kurzname des zuverwendenden Templatestils
    * @param string $method Methodenstring welcher beim Aufruf der
    * <u>createPageHTML</u> übergeben wird, default: Methodenstring der URL
    * @return string erzeugter HTML-Code der Seite
    */
   public static function process($page, $stylename = "default", $method = "") {
      if (!$page || !array_key_exists('Page', class_implements(get_class($page)))) {
         return FSystem::getMainForum()->createPageContentHTML($method);
      } else if ($method == "") {
         $method = FSystem::getMethod();
      }
      if (isset($_REQUEST['ajax'])) {
         return $page->createPageContentHTML($method);
      }
      $stylearr = array_key_exists($stylename, self::$styles) ? self::$styles[$stylename] : self::$styles["default"];
      $headerapp = '';
      foreach ($stylearr['further_css_files'] as $file) {
         $headerapp .= '<link rel="stylesheet" type="text/css" href="' . $file . '" />';
      }
      foreach ($stylearr['further_js_files'] as $file) {
         $headerapp .= '<script src="' . $file . '"/></script>';
      }
      if ($stylearr['css'] != '') {
         $headerapp .= '<style>
                ' . $stylearr['css'] . '
            </style>';
      }
      if ($stylearr['js'] != '') {
         $headerapp .= '<script>
                $("body").onLoad(function(){
                    ' . $stylearr['js'] . '
                });
            </script>';
      }
      $debug = '';
      if (!empty($_REQUEST) && FSystem::DEBUG) {
         $debug .= '<table class="debug_table">';
         $col1 = '<tr><th>key</th>';
         $col2 = '<tr><th>value</th>';
         foreach ($_REQUEST as $key => $value) {
            $col1 .= '<td>' . $key . '</td>';
            $col2 .= '<td>' . $value . '</td>';
         }
         $debug .= $col1 . '</tr>' . $col2 . '</td></table><br/>';
      }
      $ischat = is_a($page, 'Chat');
      $content = HTMLBeautifier::beautify($page->createPageContentHTML($method));
      return '<html>
        <head>
            <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
            <title>' . $page->getPageTitle() . '</title>
            <link rel="stylesheet" type="text/css" href="css/freibadstyles.css" />
            <link rel="stylesheet" type="text/css" href="css/styles.css" />
            <script src="js/jquery-1.7.js"/></script>
            <script src="js/script.js"/></script>
            ' . $headerapp . '
        </head>
        <body>
        <table id="toolbar_top">
            ' . ($debug ? '<tr><td>' . $debug . '</td></tr>' : '') . '
            <tr class="toolbar_row">
                <td class="usercol">
                    ' . FSystem::getUser()->createUserHeader() . '
                </td>
                <td class="section_choose">
                    <a href="' . FSystem::URL . 'chat.php?&' . FSystem::getURLAppendix() . '" class="chat_choose button' . ($ischat ? ' button_selected' : '') . '">
                        <span class="chat_text">Chat</span>
                            ' . (FSystem::getChat()->getUserCount() > 0 ? '<span class="chat_user_count">[' . FSystem::getChat()->getUserCount() . ']
                        <span>' : '') . '
                    </a>
                    <a href="' . FSystem::URL . 'forum.php?&' . FSystem::getURLAppendix() . '" class="forum_choose button' . (!$ischat ? ' button_selected' : '') . '">
                        Forum
                    </a>
                </td>
                <td class="searchcol">
                    <div>
                        ' . Search::createSearchBar() . '
                    </div>
                </td>
                <td class="rss_buttoncol">
                    ' /* . (FSystem::getThread() ? RSS::createRSSHTML(FSystem::getThread()) : /'')//Nicht verwendet, da .xml nicht als php interpretiert werden kann */ . '
                </td>
            </tr>
        </table>
        <table id="page_content">
            <tr id="head_row">
                <td>
                    <a href="' . FSystem::URL . 'forum.php" id="head">
                        <span id="title">' . FSystem::TITLE . '</span>
                    </a>
                 </td>
            </tr>
            <tr id="content_row">
                <td>
                    <div id="content">
                       ' . $content . '
                    </div>
                </td>
            </tr>
            <tr>
            <td>
                <table id="foottable">
                    <tr>
                        <td id="quote">
                            ' . (FSystem::QUOTES ? Quote::createRandomQuoteSpan() : '') . FSystem::getPageChooseBar($page) . '
                        </td>
                        <td id="foottext">
                            &copy; Johannes Bechberger, Julian Quast 2010 - ' . date("Y") . '
                        </td>
                    </tr>
                </table>
            </tr>
        </table>
        </body>
</html>';
   }

   /**
    * Gibt das Templatestil-Hash-Array mit dem Kurznamen des Templatestils als
    * Schlüssel und dem jeweiligen Eigenschaften-Hash-Array als Wert zurück
    * 
    * @return array Templatestil-Hash-Array
    */
   public static function getStyles() {
      return clone self::$styles;
   }

   /**
    * Gibt die Namen der implementierten Templatestile als Array zurück
    * 
    * @return string[] Templatestile-Namen-Array
    */
   public static function getStyleNames() {
      $retarr = array();
      foreach (self::$styles as $value) {
         $retarr[] = $value["name"];
      }
      return $retarr;
   }

}

?>