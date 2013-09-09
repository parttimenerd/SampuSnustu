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
 * Interface, das notwendige Methoden zur Seitenausgabe vorgibt
 *
 * @author Johannes Bechberger
 * @copyright Johannes Bechberger
 * @license http://opensource.org/licenses/gpl-license.php
 * @package SampuSnustu
 * @subpackage Utils
 */
interface Page {

   /**
    * Gibt den Seitentitel zurück
    * 
    * @return string Seitentitel  
    */
   public function getPageTitle();

   /**
    * Erzeugt den Seiteninhalt abhängig vom Methodenstring und gibt ihn zurück
    * 
    * @param string $method Methodenstring
    * @return string erzeugter HTML-Code
    */
   public function createPageContentHTML($method = "default");
}

?>
