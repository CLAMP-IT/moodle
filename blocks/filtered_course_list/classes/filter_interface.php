<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * This file contains the filter_interface class.
 *
 * @package    block_filtered_course_list
 * @copyright  2018 CLAMP
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_filtered_course_list;

/**
 * This interface allows us to define the following static functions in a way
 * that mimics a "public abstract static function()" in the filter class itself.
 * This is a workaround for limitations in PHP 5 -- see the below link for more details.
 *
 * https://stackoverflow.com/questions/999066/why-does-php-5-2-disallow-abstract-static-class-methods/6386309#6386309
 *
 * @package    block_filtered_course_list
 * @copyright  2016 CLAMP
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
interface filter_interface {
    /**
     * Retrieve filter short name.
     *
     * @return string This filter's shortname.
     */
    public static function getshortname();

    /**
     * Retrieve filter full name.
     *
     * @return string This filter's shortname.
     */
    public static function getfullname();

    /**
     * Retrieve filter component.
     *
     * @return string This filter's component.
     */
    public static function getcomponent();

    /**
     * Retrieve filter version sync number.
     *
     * @return string This filter's version sync number.
     */
    public static function getversionsyncnum();
}
