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
 * Custom renderers
 *
 * @package    theme_cosmic
 * @copyright  2013 Lafayette College ITS
 * @copyright  2012 Julian Ridden (original Rocket theme)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class theme_cosmic_core_renderer extends core_renderer {

    /**
     * Renders a custom menu object (located in outputcomponents.php)
     *
     * The custom menu this method override the render_custom_menu function
     * in outputrenderers.php
     * @staticvar int $menucount
     * @param custom_menu $menu
     * @return string
     */
    protected function render_custom_menu(custom_menu $menu) {
        global $CFG;
        require_once($CFG->dirroot.'/course/lib.php');

        $mycoursetitle = $this->page->theme->settings->mycoursetitle;
        if (isloggedin() && !isguestuser() && $mycourses = enrol_get_my_courses(null, 'visible DESC, fullname ASC')) {
            $branchurl   = new moodle_url('/my/index.php');
            $branchsort  = 10000;
            switch ($mycoursetitle) {
                case 'module':
                    $branchlabel = get_string('mymodules', 'theme_cosmic');
                    break;
                case 'unit':
                    $branchlabel = get_string('myunits', 'theme_cosmic');
                    break;
                case 'class':
                    $branchlabel = get_string('myclasses', 'theme_cosmic');
                    break;
                default:
                    $branchlabel = get_string('mycourses', 'theme_cosmic');
                    break;
            }
            $branchtitle = $branchlabel;
            $branch = $menu->add($branchlabel, $branchurl, $branchtitle, $branchsort);

            foreach ($mycourses as $mycourse) {
                $branch->add($mycourse->shortname, new moodle_url('/course/view.php',
                    array('id' => $mycourse->id)), $mycourse->fullname);
            }
        } else {
            switch ($mycoursetitle) {
                case 'module':
                    $branchlabel = get_string('allmodules', 'theme_cosmic');
                    break;
                case 'unit':
                    $branchlabel = get_string('allunits', 'theme_cosmic');
                    break;
                case 'class':
                    $branchlabel = get_string('allclasses', 'theme_cosmic');
                    break;
                default:
                    $branchlabel = get_string('allcourses', 'theme_cosmic');
                    break;
            }

            $branchtitle = $branchlabel;
            $branchurl   = new moodle_url('/course/index.php');
            $branchsort  = 10000;
            $branch = $menu->add($branchlabel, $branchurl, $branchtitle, $branchsort);
        }

        // If the menu has no children return an empty string.
        if (!$menu->has_children()) {
            return '';
        }

        // Initialise this custom menu.
        $content = html_writer::start_tag('ul', array('class'=>'dropdown dropdown-horizontal'));

        // Render each child.
        foreach ($menu->get_children() as $item) {
            $content .= $this->render_custom_menu_item($item);
        }

        // Close the open tags.
        $content .= html_writer::end_tag('ul');

        // Return the custom menu.
        return $content;
    }

    /**
     * Renders a custom menu node as part of a submenu
     *
     * The custom menu this method override the render_custom_menu_item function
     * in outputrenderers.php
     *
     * @see render_custom_menu()
     *
     * @staticvar int $submenucount
     * @param custom_menu_item $menunode
     * @return string
     */
    protected function render_custom_menu_item(custom_menu_item $menunode) {
        // Required to ensure we get unique trackable id's.
        static $submenucount = 0;
        $content = html_writer::start_tag('li');
        if ($menunode->has_children()) {
            // If the child has menus render it as a sub menu.
            $submenucount++;
            if ($menunode->get_url() !== null) {
                $url = $menunode->get_url();
            } else {
                $url = '#cm_submenu_'.$submenucount;
            }
            $content .= html_writer::start_tag('span', array('class'=>'customitem'));
            $content .= html_writer::link($url, $menunode->get_text(), array('title'=>$menunode->get_title()));
            $content .= html_writer::end_tag('span');
            $content .= html_writer::start_tag('ul');
            foreach ($menunode->get_children() as $menunode) {
                $content .= $this->render_custom_menu_item($menunode);
            }
            $content .= html_writer::end_tag('ul');
        } else {
            // The node doesn't have children so produce a final menu item.
            if ($menunode->get_url() !== null) {
                $url = $menunode->get_url();
            } else {
                $url = '#';
            }
            $content .= html_writer::link($url, $menunode->get_text(), array('title'=>$menunode->get_title()));
        }
        $content .= html_writer::end_tag('li');

        // Return the sub menu.
        return $content;
    }

    /**
     * Reformat edit button to new status indicator
     */
    public function edit_button(moodle_url $url) {
        $edittoggle = 'enable';
        if (!empty($this->page->theme->settings->edittoggle)) {
            $edittoggle = $this->page->theme->settings->edittoggle;
        }
        if ($edittoggle == 'enable') {
            $url->param('sesskey', sesskey());
            $formclose ='</span><div id="editmode">'.get_string('editmode', 'theme_cosmic')
                .'<div id="edittoggle">'.get_string('edittoggle', 'theme_cosmic').'&nbsp;</div></div>';
            if ($this->page->user_is_editing()) {
                $formopen = '<span id="editbuttonon">';
                $url->param('edit', 'off');
                $editstring = get_string('turneditingoff', 'theme_cosmic');
            } else {
                $formopen ='<span id="editbuttonoff">';
                $url->param('edit', 'on');
                $editstring = get_string('turneditingon', 'theme_cosmic');
            }
            return $formopen . $this->single_button($url, $editstring) . $formclose;
        } else {
            $url->param('sesskey', sesskey());
            if ($this->page->user_is_editing()) {
                $url->param('edit', 'off');
                $editstring = get_string('turneditingoff');
            } else {
                $url->param('edit', 'on');
                $editstring = get_string('turneditingon');
            }
            return $this->single_button($url, $editstring);
        }
    }
}
