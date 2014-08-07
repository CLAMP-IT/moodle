<?php
/**
 * Defines global settings for the Course Description block
 *
 * Allows selection of roles to be considered "Teachers", and thus displayed in the block
 *
 * @package    block_course_description
 * @author     Sarah Ryder <sryder@hampshire.edu>
 * @copyright  2010 onwards Hampshire College
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$settings->add(new admin_setting_pickroles('block_course_description_roles',
                                           get_string('teachersinclude', 'block_course_description'),
                                           get_string('rolesdesc', 'block_course_description'),
                                           array('moodle/legacy:teacher'),
                                           PARAM_TEXT));
