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
 * @package SampuSnustu
 * @subpackage Utils 
 */

error_reporting(0);

define('PARENT_DIR', __DIR__);
$filearr = array();

/**
 * Registriert eine Autoload-Methode, die benötigte Klassen dynamisch nachlädt
 */
spl_autoload_register(function($classname) {
            if (empty($filearr)) {
                $dir = opendir(PARENT_DIR);
                while ($filestr = readdir($dir)) {
                    $fpath = PARENT_DIR . '\\' . $filestr;
                    $filearr[$filestr] = $fpath;
                    if (is_dir($fpath) && $filestr) {
                        $dir2 = opendir($fpath);
                        while ($filestr2 = readdir($dir2)) {
                            $filearr[$filestr2] = $fpath . '\\' . $filestr2;
                        }
                    }
                }
            }
            if (array_key_exists($classname . '.php', $filearr)) {
                require_once $filearr[$classname . '.php'];
            }
        });
?>
