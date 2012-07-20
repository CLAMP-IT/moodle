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
 * HTML class for an applet type element
 *
 * @author     Ning
 * @package    mod
 * @subpackage nanogong
 * @copyright  2012 The Gong Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @access     public
 * @version    4.2
 */

require_once(dirname(dirname(dirname(dirname(__FILE__))))."/lib/pear/HTML/QuickForm/element.php");

class HTML_QuickForm_applet extends HTML_QuickForm_element
{
    // {{{ constructor

    /**
     * Class constructor
     * 
     * @param     string    $elementName    (optional)Element name attribute
     * @param     string    $src            (optional)Applet source
     * @param     mixed     $attributes     (optional)Either a typical HTML attribute string 
     *                                      or an associative array
     * @since     1.0
     * @access    public
     * @return    void
     */
    function HTML_QuickForm_applet($elementName=null, $elementLabel=null, $attributes=null)
    {
        $this->HTML_QuickForm_element($elementName, $elementLabel, $attributes);
    } // end class constructor

    // }}}
    /**
     * Sets the applet field name
     * 
     * @param     string    $name   Input field name attribute
     * @since     1.0
     * @access    public
     * @return    void
     */
    function setName($name)
    {
        $this->updateAttributes(array('name'=>$name));
    } //end func setName
    
    // }}}
    // {{{ getName()

    /**
     * Returns the element name
     * 
     * @since     1.0
     * @access    public
     * @return    string
     */
    function getName()
    {
        return $this->getAttribute('name');
    } //end func getName
    
    // }}}
    // {{{ setArchive()

    /**
     * Sets archive for applet element
     * 
     * @param     string    $arc  archive for applet element
     * @since     1.0
     * @access    public
     * @return    void
     */
    function setArchive($arc)
    {
        $this->updateAttributes(array('archive' => $arc));
    } // end func setArchive

    // }}}
    // {{{ setCode()

    /**
     * Sets code for applet element
     * 
     * @param     string    $cod  code for applet element
     * @since     1.0
     * @access    public
     * @return    void
     */
    function setCode($cod)
    {
        $this->updateAttributes(array('code' => $cod));
    } // end func setCode

    // }}}
    // {{{ toHtml()

    /**
     * Returns the applet field in HTML
     * 
     * @since     1.0
     * @access    public
     * @return    string
     */
    function toHtml()
    {
        if ($this->_flagFrozen) {
            return $this->getFrozenHtml();
        } else {
            return $this->_getTabs() . '<applet' . $this->_getAttrString($this->_attributes) . ' /></applet>';
        }
    } //end func toHtml

    // }}}

} // end class HTML_QuickForm_applet
?>
