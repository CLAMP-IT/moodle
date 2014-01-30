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
 * this file contains simple html dom classes and functions
 * 
 * @package mod_turningtech
 * @copyright 2012 Turning Technologies
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
/**
 * this file contains simple html dom classes and functions
 * Website: http://sourceforge.net/projects/simplehtmldom/
 * Acknowledge: Jose Solorzano (https://sourceforge.net/projects/php-html/)
 * Contributions by:
 * Yousuke Kumakura (Attribute filters)
 * Vadim Voituk (Negative indexes supports of "find" method)
 * Antcs (Constructor with automatically load contents either text or file/url)
 * all affected sections have comments starting with "PaperG"
 * Paperg - Added case insensitive testing of the value of the selector.
 * Paperg - Added tag_start for the starting index of tags - NOTE: This works but not accurately.
 * This tag_start gets counted AFTER \r\n have been crushed out, and after the remove_noice calls so it will not reflect the REAL
 * position of the tag in the source,
 * it will almost always be smaller by some amount.
 * We use this to determine how far into the file the tag in question is. This "percentage will never be accurate as the $dom->size
 * is the "real" number of bytes the dom was created from.
 * but for most purposes, it's a really good estimation.
 * Paperg - Added the forceTagsClosed to the dom constructor. Forcing tags closed is great for malformed html, but it CAN lead to
 * parsing errors.
 * Allow the user to tell us how much they trust the html.
 * Paperg add the text and plaintext to the selectors for the find syntax. plaintext implies text in the innertext of a node. text
 * implies that the tag is a text node.
 * This allows for us to find tags based on the text they contain.
 * Create find_ancestor_tag to see if a tag is - at any level - inside of another specific tag.
 * Paperg: added parse_charset so that we know about the character set of the source document.
 * NOTE: If the user's system has a routine called get_last_retrieve_url_contents_content_type availalbe, we will assume it's
 * returning the content-type header from the
 * last transfer or curl_exec, and we will parse that and use it in preference to any other method of charset detection.
 * Found infinite loop in the case of broken html in restore_noise. Rewrote to protect from that.
 * PaperG (John Schlick) Added get_display_size for "IMG" tags.
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 * 
 * @author S.C. Chen <me578022@gmail.com>
 * @author John Schlick
 * @author Rus Carroll
 * @package mod_turningtech
 * @copyright 2012 Turning Technologies
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define('TT_HDOM_TYPE_ELEMENT', 1);
define('TT_HDOM_TYPE_COMMENT', 2);
define('TT_HDOM_TYPE_TEXT', 3);
define('TT_HDOM_TYPE_ENDTAG', 4);
define('TT_HDOM_TYPE_ROOT', 5);
define('TT_HDOM_TYPE_UNKNOWN', 6);
define('TT_HDOM_QUOTE_DOUBLE', 0);
define('TT_HDOM_QUOTE_SINGLE', 1);
define('TT_HDOM_QUOTE_NO', 3);
define('TT_HDOM_INFO_BEGIN', 0);
define('TT_HDOM_INFO_END', 1);
define('TT_HDOM_INFO_QUOTE', 2);
define('TT_HDOM_INFO_SPACE', 3);
define('TT_HDOM_INFO_TEXT', 4);
define('TT_HDOM_INFO_INNER', 5);
define('TT_HDOM_INFO_OUTER', 6);
define('TT_HDOM_INFO_ENDSPACE', 7);
define('TT_DEFAULT_TARGET_CHARSET', 'UTF-8');
define('TT_DEFAULT_BR_TEXT', "\r\n");
define('TT_DEFAULT_SPAN_TEXT', " ");
define('TT_MAX_FILE_SIZE', 600000);
/**
 * simple html dom node class
 * 
 * @package mod_turningtech
 * @copyright 2012 Turning Technologies
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class simple_html_dom_node {
    /**
     *
     * @var unknown_type
     */
    public $nodetype = TT_HDOM_TYPE_TEXT;
    /**
     *
     * @var unknown_type
     */
    public $tag = 'text';
    /**
     *
     * @var unknown_type
     */
    public $attr = array ();
    /**
     *
     * @var unknown_type
     */
    public $children = array ();
    /**
     *
     * @var unknown_type
     */
    public $nodes = array ();
    /**
     *
     * @var unknown_type
     */
    public $parent = null;
    // The "info" array - see HDOM_INFO_... for what each element contains.
    /**
     *
     * @var unknown_type
     */
    public $_ = array ();
    /**
     *
     * @var unknown_type
     */
    public $tag_start = 0;
    /**
     *
     * @var unknown_type
     */
    private $dom = null;
    /**
     * Constructor for dom
     * 
     * @param unknown_type $dom
     */
    public function __construct($dom) {
        $this->dom = $dom;
        $dom->nodes[] = $this;
    }
    /**
     * Destructor for dom
     */
    public function __destruct() {
        $this->clear();
    }
    /**
     * to string function
     * 
     * @return Ambigous <Ambigous, multitype:, string, multitype:>
     */
    public function __toString() {
        return $this->outertext();
    }
    /**
     * clean up memory due to php5 circular references memory leak...
     */
    public function clear() {
        $this->dom = null;
        $this->nodes = null;
        $this->parent = null;
        $this->children = null;
    }
    /**
     * dump node's tree
     * 
     * @param unknown_type $show_attr
     * @param unknown_type $deep
     */
    public function dump($show_attr = true, $deep = 0) {
        $lead = str_repeat('    ', $deep);
        echo $lead . $this->tag;
        if ($show_attr && count($this->attr) > 0) {
            echo '(';
            foreach ($this->attr as $k => $v) {
                echo "[$k]=>\"" . $this->$k . '", ';
            }
            echo ')';
        }
        echo "\n";
        if ($this->nodes) {
            foreach ($this->nodes as $c) {
                $c->dump($show_attr, $deep + 1);
            }
        }
    }
    /**
     * Debugging function to dump a single dom node with a bunch of information about it.
     * 
     * @param unknown_type $echo
     * @return void string
     */
    public function dump_node($echo = true) {
        $string = $this->tag;
        if (count($this->attr) > 0) {
            $string .= '(';
            foreach ($this->attr as $k => $v) {
                $string .= "[$k]=>\"" . $this->$k . '", ';
            }
            $string .= ')';
        }
        if (count($this->_) > 0) {
            $string .= ' $_ (';
            foreach ($this->_ as $k => $v) {
                if (is_array($v)) {
                    $string .= "[$k]=>(";
                    foreach ($v as $k2 => $v2) {
                        $string .= "[$k2]=>\"" . $v2 . '", ';
                    }
                    $string .= ")";
                } else {
                    $string .= "[$k]=>\"" . $v . '", ';
                }
            }
            $string .= ")";
        }
        if (isset($this->text)) {
            $string .= " text: (" . $this->text . ")";
        }
        $string .= " HDOM_INNER_INFO: '";
        if (isset($node->_[TT_HDOM_INFO_INNER])) {
            $string .= $node->_[TT_HDOM_INFO_INNER] . "'";
        } else {
            $string .= ' NULL ';
        }
        $string .= " children: " . count($this->children);
        $string .= " nodes: " . count($this->nodes);
        $string .= " tag_start: " . $this->tag_start;
        $string .= "\n";
        if ($echo) {
            echo $string;
            return;
        } else {
            return $string;
        }
    }
    /**
     * returns the parent of node
     * If a node is passed in, it will reset the parent of the current node to that one.
     * 
     * @param unknown_type $parent
     * @return NULL
     */
    public function shdom_parent($parent = null) {
        // I am SURE that this doesn't work properly.
        // It fails to unset the current node from it's current parents nodes or children list first.
        if ($parent !== null) {
            $this->parent = $parent;
            $this->parent->nodes[] = $this;
            $this->parent->children[] = $this;
        }
        return $this->parent;
    }
    /**
     * verify that node has children
     * 
     * @return boolean
     */
    public function has_child() {
        return ! empty($this->children);
    }
    /**
     * returns children of node
     * 
     * @param unknown_type $idx
     * @return multitype: NULL
     */
    public function children($idx = -1) {
        if ($idx === - 1) {
            return $this->children;
        }
        if (isset($this->children[$idx])) {
            return $this->children[$idx];
        }
        return null;
    }
    /**
     * returns the first child of node
     * 
     * @return multitype: NULL
     */
    public function first_child() {
        if (count($this->children) > 0) {
            return $this->children[0];
        }
        return null;
    }
    /**
     * returns the last child of node
     * 
     * @return multitype: NULL
     */
    public function last_child() {
        if (($count = count($this->children)) > 0) {
            return $this->children[$count - 1];
        }
        return null;
    }
    /**
     * returns the next sibling of node
     * 
     * @return mixed
     */
    public function next_sibling() {
        if ($this->parent === null) {
            return null;
        }
        $idx = 0;
        $count = count($this->parent->children);
        while ($idx < $count && $this !== $this->parent->children[$idx]) {
            ++ $idx;
        }
        if (++ $idx >= $count) {
            return null;
        }
        return $this->parent->children[$idx];
    }
    /**
     * returns the previous sibling of node
     * 
     * @return mixed
     */
    public function prev_sibling() {
        if ($this->parent === null) {
            return null;
        }
        $idx = 0;
        $count = count($this->parent->children);
        while ($idx < $count && $this !== $this->parent->children[$idx]) {
            ++ $idx;
        }
        if (-- $idx < 0) {
            return null;
        }
        return $this->parent->children[$idx];
    }
    /**
     * function to locate a specific ancestor tag in the path to the root.
     * 
     * @param unknown_type $tag
     * @return simple_html_dom_node
     */
    public function find_ancestor_tag($tag) {
        global $debugobject;
        if (is_object($debugobject)) {
            $debugobject->debugLogEntry(1);
        }
        // Start by including ourselves in the comparison.
        $returndom = $this;
        while (! is_null($returndom)) {
            if (is_object($debugobject)) {
                $debugobject->debugLog(2, "Current tag is: " . $returndom->tag);
            }
            if ($returndom->tag == $tag) {
                break;
            }
            $returndom = $returndom->parent;
        }
        return $returndom;
    }
    /**
     * get dom node's inner html
     * 
     * @return multitype: string
     */
    public function innertext() {
        if (isset($this->_[TT_HDOM_INFO_INNER])) {
            return $this->_[TT_HDOM_INFO_INNER];
        }
        if (isset($this->_[TT_HDOM_INFO_TEXT])) {
            return $this->dom->restore_noise($this->_[TT_HDOM_INFO_TEXT]);
        }
        $ret = '';
        foreach ($this->nodes as $n) {
            $ret .= $n->outertext();
        }
        return $ret;
    }
    /**
     * get dom node's outer text (with tag)
     * 
     * @return Ambigous <multitype:, string>|multitype:|string
     */
    public function outertext() {
        global $debugobject;
        if (is_object($debugobject)) {
            $text = '';
            if ($this->tag == 'text') {
                if (! empty($this->text)) {
                    $text = " with text: " . $this->text;
                }
            }
            $debugobject->debugLog(1, 'Innertext of tag: ' . $this->tag . $text);
        }
        if ($this->tag === 'root') {
            return $this->innertext();
        }
            // Trigger callback.
        if ($this->dom && $this->dom->callback !== null) {
            call_user_func_array($this->dom->callback, array ($this));
        }
        if (isset($this->_[TT_HDOM_INFO_OUTER])) {
            return $this->_[TT_HDOM_INFO_OUTER];
        }
        if (isset($this->_[TT_HDOM_INFO_TEXT])) {
            return $this->dom->restore_noise($this->_[TT_HDOM_INFO_TEXT]);
        }
            // Render begin tag.
        if ($this->dom && $this->dom->nodes[$this->_[TT_HDOM_INFO_BEGIN]]) {
            $ret = $this->dom->nodes[$this->_[TT_HDOM_INFO_BEGIN]]->makeup();
        } else {
            $ret = "";
        }
        // Render inner text.
        if (isset($this->_[TT_HDOM_INFO_INNER])) {
            // If it's a br tag... don't return the HDOM_INNER_INFO that we may or may not have added.
            if ($this->tag != "br") {
                $ret .= $this->_[TT_HDOM_INFO_INNER];
            }
        } else {
            if ($this->nodes) {
                foreach ($this->nodes as $n) {
                    $ret .= $this->convert_text($n->outertext());
                }
            }
        }
        // Render end tag.
        if (isset($this->_[TT_HDOM_INFO_END]) && $this->_[TT_HDOM_INFO_END] != 0) {
            $ret .= '</' . $this->tag . '>';
        }
        return $ret;
    }
    /**
     * get dom node's plain text
     * 
     * @return multitype: string Ambigous unknown_type>
     */
    public function text() {
        if (isset($this->_[TT_HDOM_INFO_INNER])) {
            return $this->_[TT_HDOM_INFO_INNER];
        }
        switch ($this->nodetype) {
            case TT_HDOM_TYPE_TEXT :
                return $this->dom->restore_noise($this->_[TT_HDOM_INFO_TEXT]);
            case TT_HDOM_TYPE_COMMENT :
                return '';
            case TT_HDOM_TYPE_UNKNOWN :
                return '';
        }
        if (strcasecmp($this->tag, 'script') === 0) {
            return '';
        }
        if (strcasecmp($this->tag, 'style') === 0) {
            return '';
        }
        $ret = '';
        // In rare cases, (always node type 1 or HDOM_TYPE_ELEMENT - observed for some span tags, and some p tags) $this->nodes is
        // set to NULL.
        // NOTE: This indicates that there is a problem where it's set to NULL without a clear happening.
        // WHY is this happening?
        if (! is_null($this->nodes)) {
            foreach ($this->nodes as $n) {
                $ret .= $this->convert_text($n->text());
            }
            // If this node is a span... add a space at the end of it so multiple spans don't run into each other. This is plaintext
            // after all.
            if ($this->tag == "span") {
                $ret .= $this->dom->default_span_text;
            }
        }
        return $ret;
    }
    /**
     * Get xml string
     * 
     * @return mixed
     */
    public function xmltext() {
        $ret = $this->innertext();
        $ret = str_ireplace('<![CDATA[', '', $ret);
        $ret = str_replace(']]>', '', $ret);
        return $ret;
    }
    /**
     * build node's text with tag
     * 
     * @return string
     */
    public function makeup() {
        // Text, comment, unknown.
        if (isset($this->_[TT_HDOM_INFO_TEXT])) {
            return $this->dom->restore_noise($this->_[TT_HDOM_INFO_TEXT]);
        }
        $ret = '<' . $this->tag;
        $i = - 1;
        foreach ($this->attr as $key => $val) {
            ++ $i;
            // Skip removed attribute.
            if ($val === null || $val === false) {
                continue;
            }
            $ret .= $this->_[TT_HDOM_INFO_SPACE][$i][0];
            // No value attr: nowrap, checked selected...
            if ($val === true) {
                $ret .= $key;
            } else {
                switch ($this->_[TT_HDOM_INFO_QUOTE][$i]) {
                    case TT_HDOM_QUOTE_DOUBLE :
                        $quote = '"';
                        break;
                    case TT_HDOM_QUOTE_SINGLE :
                        $quote = '\'';
                        break;
                    default :
                        $quote = '';
                }
                $ret .= $key . $this->_[TT_HDOM_INFO_SPACE][$i][1] . '=' . $this->_[TT_HDOM_INFO_SPACE][$i][2].$quote.$val.$quote;
            }
        }
        $ret = $this->dom->restore_noise($ret);
        return $ret . $this->_[TT_HDOM_INFO_ENDSPACE] . '>';
    }
    /**
     * find elements by css selector
     * PaperG - added ability for find to lowercase the value of the selector.
     * 
     * @param unknown_type $selector
     * @param unknown_type $idx
     * @param unknown_type $lowercase
     * @return multitype: multitype:NULL
     */
    public function find($selector, $idx = null, $lowercase = false) {
        $selectors = $this->parse_selector($selector);
        if (($count = count($selectors)) === 0) {
            return array ();
        }
        $found_keys = array ();
        // Find each selector.
        for ($c = 0; $c < $count; ++ $c) {
            // The change on the below line was documented on the sourceforge code tracker id 2788009
            // used to be: if (($levle=count($selectors[0]))===0) return array();.
            if (($levle = count($selectors[$c])) === 0) {
                return array ();
            }
            if (! isset($this->_[TT_HDOM_INFO_BEGIN])) {
                return array ();
            }
            $head = array ($this->_[TT_HDOM_INFO_BEGIN] => 1);
            // Handle descendant selectors, no recursive!
            for ($l = 0; $l < $levle; ++ $l) {
                $ret = array ();
                foreach ($head as $k => $v) {
                    $n = ($k === - 1) ? $this->dom->root : $this->dom->nodes[$k];
                    // PaperG - Pass this optional parameter on to the seek function.
                    $n->seek($selectors[$c][$l], $ret, $lowercase);
                }
                $head = $ret;
            }
            foreach ($head as $k => $v) {
                if (! isset($found_keys[$k])) {
                    $found_keys[$k] = 1;
                }
            }
        }
        // Sort keys.
        ksort($found_keys);
        $found = array ();
        foreach ($found_keys as $k => $v) {
            $found[] = $this->dom->nodes[$k];
        }
            // Return nth-element or array.
        if (is_null($idx)) {
            return $found;
        } else if ($idx < 0) {
            $idx = count($found) + $idx;
        }
        return (isset($found[$idx])) ? $found[$idx] : null;
    }
    /**
     * seek for given conditions
     * PaperG - added parameter to allow for case insensitive testing of the value of a selector.
     * 
     * @param unknown_type $selector
     * @param unknown_type $ret
     * @param unknown_type $lowercase
     */
    protected function seek($selector, &$ret, $lowercase = false) {
        global $debugobject;
        if (is_object($debugobject)) {
            $debugobject->debugLogEntry(1);
        }
        list($tag, $key, $val, $exp, $no_key) = $selector;
        // Xpath index.
        if ($tag && $key && is_numeric($key)) {
            $count = 0;
            foreach ($this->children as $c) {
                if ($tag === '*' || $tag === $c->tag) {
                    if (++ $count == $key) {
                        $ret[$c->_[TT_HDOM_INFO_BEGIN]] = 1;
                        return;
                    }
                }
            }
            return;
        }
        $end = (! empty($this->_[TT_HDOM_INFO_END])) ? $this->_[TT_HDOM_INFO_END] : 0;
        if ($end == 0) {
            $parent = $this->parent;
            while (! isset($parent->_[TT_HDOM_INFO_END]) && $parent !== null) {
                $end -= 1;
                $parent = $parent->parent;
            }
            $end += $parent->_[TT_HDOM_INFO_END];
        }
        for ($i = $this->_[TT_HDOM_INFO_BEGIN] + 1; $i < $end; ++ $i) {
            $node = $this->dom->nodes[$i];
            $pass = true;
            if ($tag === '*' && ! $key) {
                if (in_array($node, $this->children, true)) {
                    $ret[$i] = 1;
                }
                continue;
            }
            // Compare tag.
            if ($tag && $tag != $node->tag && $tag !== '*') {
                $pass = false;
            }
            // Compare key.
            if ($pass && $key) {
                if ($no_key) {
                    if (isset($node->attr[$key])) {
                        $pass = false;
                    }
                } else {
                    if (($key != "plaintext") && ! isset($node->attr[$key])) {
                        $pass = false;
                    }
                }
            }
            // Compare value.
            if ($pass && $key && $val && $val !== '*') {
                // If they have told us that this is a "plaintext" search then we want the plaintext of the node - right?
                if ($key == "plaintext") {
                    $nodekeyvalue = $node->text();
                } else {
                    // This is a normal search, we want the value of that attribute of the tag.
                    $nodekeyvalue = $node->attr[$key];
                }
                if (is_object($debugobject)) {
                    $debugobject->debugLog(2, "testing node: " . $node->tag . " for attribute: " .
                                                     $key . $exp . $val . " where nodes value is: " . $nodekeyvalue);
                }
                // PaperG - If lowercase is set, do a case insensitive test of the value of the selector.
                if ($lowercase) {
                    $check = $this->match($exp, strtolower($val), strtolower($nodekeyvalue));
                } else {
                    $check = $this->match($exp, $val, $nodekeyvalue);
                }
                if (is_object($debugobject)) {
                    $debugobject->debugLog(2, "after match: " . ($check ? "true" : "false"));
                }
                // Handle multiple class.
                if (! $check && strcasecmp($key, 'class') === 0) {
                    foreach (explode(' ', $node->attr[$key]) as $k) {
                        // Without this, there were cases where leading, trailing, or double spaces lead to our comparing blanks -
                        // bad form.
                        if (! empty($k)) {
                            if ($lowercase) {
                                $check = $this->match($exp, strtolower($val), strtolower($k));
                            } else {
                                $check = $this->match($exp, $val, $k);
                            }
                            if ($check) {
                                break;
                            }
                        }
                    }
                }
                if (! $check) {
                    $pass = false;
                }
            }
            if ($pass) {
                $ret[$i] = 1;
            }
            unset($node);
        }
        // It's passed by reference so this is actually what this function returns.
        if (is_object($debugobject)) {
            $debugobject->debugLog(1, "EXIT - ret: ", $ret);
        }
    }
    /**
     * match it up
     * 
     * @param unknown_type $exp
     * @param unknown_type $pattern
     * @param unknown_type $value
     * @return boolean number
     */
    protected function match($exp, $pattern, $value) {
        global $debugobject;
        if (is_object($debugobject)) {
            $debugobject->debugLogEntry(1);
        }
        switch ($exp) {
            case '=' :
                return ($value === $pattern);
            case '!=' :
                return ($value !== $pattern);
            case '^=' :
                return preg_match("/^" . preg_quote($pattern, '/') . "/", $value);
            case '$=' :
                return preg_match("/" . preg_quote($pattern, '/') . "$/", $value);
            case '*=' :
                if ($pattern[0] == '/') {
                    return preg_match($pattern, $value);
                }
                return preg_match("/" . $pattern . "/i", $value);
        }
        return false;
    }
    /**
     * parse selectors
     * 
     * @param unknown_type $selector_string
     * @return multitype:multitype: multitype:multitype:boolean string unknown Ambigous <unknown, string>
     */
    protected function parse_selector($selector_string) {
        global $debugobject;
        if (is_object($debugobject)) {
            $debugobject->debugLogEntry(1);
        }
        // Pattern of CSS selectors, modified from mootools
        // Paperg: Add the colon to the attrbute, so that it properly finds <tag attr:ibute="something" > like google does.
        // Note: if you try to look at this attribute, yo MUST use getattribute since $dom->x:y will fail the php syntax check.
        // Notice the \[ starting the attbute? and the @? following? This implies that an attribute can begin with an @ sign that is
        // not captured.
        // This implies that an html attribute specifier may start with an @ sign that is NOT captured by the expression.
        // farther study is required to determine of this should be documented or removed.
        $pattern = "/([\w-:\*]*)(?:\#([\w-]+)|\.([\w-]+))?(?:\[@?(!?[\w-:]+)(?:([!*^$]?=)[\"']?(.*?)[\"']?)?\])?([\/, ]+)/is";
        preg_match_all($pattern, trim($selector_string) . ' ', $matches, PREG_SET_ORDER);
        if (is_object($debugobject)) {
            $debugobject->debugLog(2, "Matches Array: ", $matches);
        }
        $selectors = array ();
        $result = array ();
        foreach ($matches as $m) {
            $m[0] = trim($m[0]);
            if ($m[0] === '' || $m[0] === '/' || $m[0] === '//') {
                continue;
            }
                // For browser generated xpath.
            if ($m[1] === 'tbody') {
                continue;
            }
            list($tag, $key, $val, $exp, $no_key) = array ($m[1], null, null, '=', false);
            if (! empty($m[2])) {
                $key = 'id';
                $val = $m[2];
            }
            if (! empty($m[3])) {
                $key = 'class';
                $val = $m[3];
            }
            if (! empty($m[4])) {
                $key = $m[4];
            }
            if (! empty($m[5])) {
                $exp = $m[5];
            }
            if (! empty($m[6])) {
                $val = $m[6];
            }
            // Convert to lowercase.
            if ($this->dom->lowercase) {
                $tag = strtolower($tag);
                $key = strtolower($key);
            }
            // Elements that do NOT have the specified attribute.
            if (isset($key[0]) && $key[0] === '!') {
                $key = substr($key, 1);
                $no_key = true;
            }
            $result[] = array ($tag, $key, $val, $exp, $no_key);
            if (trim($m[7]) === ',') {
                $selectors[] = $result;
                $result = array ();
            }
        }
        if (count($result) > 0) {
            $selectors[] = $result;
        }
        return $selectors;
    }
    /**
     * Get it
     * 
     * @param unknown_type $name
     * @return Ambigous <Ambigous, unknown_type, string>|Ambigous <multitype:, string>|Ambigous <string, multitype:, Ambigous,
     *         unknown_type>|mixed|boolean
     */
    public function __get($name) {
        if (isset($this->attr[$name])) {
            return $this->convert_text($this->attr[$name]);
        }
        switch ($name) {
            case 'outertext' :
                return $this->outertext();
            case 'innertext' :
                return $this->innertext();
            case 'plaintext' :
                return $this->text();
            case 'xmltext' :
                return $this->xmltext();
            default :
                return array_key_exists($name, $this->attr);
        }
    }
    /**
     * Set it
     * 
     * @param unknown_type $name
     * @param unknown_type $value
     * @return unknown
     */
    public function __set($name, $value) {
        switch ($name) {
            case 'outertext' :
                return $this->_[TT_HDOM_INFO_OUTER] = $value;
            case 'innertext' :
                if (isset($this->_[TT_HDOM_INFO_TEXT])) {
                    return $this->_[TT_HDOM_INFO_TEXT] = $value;
                }
                return $this->_[TT_HDOM_INFO_INNER] = $value;
        }
        if (! isset($this->attr[$name])) {
            $this->_[TT_HDOM_INFO_SPACE][] = array (' ', '', '');
            $this->_[TT_HDOM_INFO_QUOTE][] = TT_HDOM_QUOTE_DOUBLE;
        }
        $this->attr[$name] = $value;
    }
    /**
     * check if set
     * 
     * @param unknown_type $name
     * @return boolean
     */
    public function __isset($name) {
        switch ($name) {
            case 'outertext' :
                return true;
            case 'innertext' :
                return true;
            case 'plaintext' :
                return true;
        }
        // No value attr: nowrap, checked selected...
        return (array_key_exists($name, $this->attr)) ? true : isset($this->attr[$name]);
    }
    /**
     * unset it
     * 
     * @param unknown_type $name
     */
    public function __unset($name) {
        if (isset($this->attr[$name])) {
            unset($this->attr[$name]);
        }
    }
    /**
     * PaperG - Function to convert the text from one character set to another if the two sets are not the same.
     * 
     * @param unknown_type $text
     * @return Ambigous <unknown, string>
     */
    public function convert_text($text) {
        global $debugobject;
        if (is_object($debugobject)) {
            $debugobject->debugLogEntry(1);
        }
        $converted_text = $text;
        $sourcecharset = "";
        $targetcharset = "";
        if ($this->dom) {
            $sourcecharset = strtoupper($this->dom->_charset);
            $targetcharset = strtoupper($this->dom->_target_charset);
        }
        if (is_object($debugobject)) {
            $debugobject->debugLog(3, "source charset: " . $sourcecharset . " target charaset: " . $targetcharset);
        }
        if (! empty($sourcecharset) && ! empty($targetcharset) && (strcasecmp($sourcecharset, $targetcharset) != 0)) {
            // Check if the reported encoding could have been incorrect and the text is actually already UTF-8.
            if ((strcasecmp($targetcharset, 'UTF-8') == 0) && ($this->is_utf8($text))) {
                $converted_text = $text;
            } else {
                $converted_text = iconv($sourcecharset, $targetcharset, $text);
            }
        }
        // Lets make sure that we don't have that silly BOM issue with any of the utf-8 text we output.
        if ($targetcharset == 'UTF-8') {
            if (substr($converted_text, 0, 3) == "\xef\xbb\xbf") {
                $converted_text = substr($converted_text, 3);
            }
            if (substr($converted_text, - 3) == "\xef\xbb\xbf") {
                $converted_text = substr($converted_text, 0, - 3);
            }
        }
        return $converted_text;
    }
    /**
     * Returns true if $string is valid UTF-8 and false otherwise.
     * 
     * @param mixed $str String to be tested
     * @return boolean
     */
    public static function is_utf8($str) {
        $c = 0;
        $b = 0;
        $bits = 0;
        $len = strlen($str);
        for ($i = 0; $i < $len; $i ++) {
            $c = ord($str[$i]);
            if ($c > 128) {
                if (($c >= 254)) {
                    return false;
                } else if ($c >= 252) {
                    $bits = 6;
                } else if ($c >= 248) {
                    $bits = 5;
                } else if ($c >= 240) {
                    $bits = 4;
                } else if ($c >= 224) {
                    $bits = 3;
                } else if ($c >= 192) {
                    $bits = 2;
                } else {
                    return false;
                }
                if (($i + $bits) > $len) {
                    return false;
                }
                while ($bits > 1) {
                    $i ++;
                    $b = ord($str[$i]);
                    if ($b < 128 || $b > 191) {
                        return false;
                    }
                    $bits --;
                }
            }
        }
        return true;
    }
    /**
     * Function to try a few tricks to determine the displayed size of an img on the page.
     * NOTE: This will ONLY work on an IMG tag. Returns FALSE on all other tag types.
     * 
     * @author John Schlick
     * @return array an array containing the 'height' and 'width' of the image on the page or -1 if we can't figure it out.
     */
    public function get_display_size() {
        global $debugobject;
        $width = - 1;
        $height = - 1;
        if ($this->tag !== 'img') {
            return false;
        }
        // See if there is aheight or width attribute in the tag itself.
        if (isset($this->attr['width'])) {
            $width = $this->attr['width'];
        }
        if (isset($this->attr['height'])) {
            $height = $this->attr['height'];
        }
        // Now look for an inline style.
        if (isset($this->attr['style'])) {
            // Thanks to user gnarf from stackoverflow for this regular expression.
            $attributes = array ();
            preg_match_all("/([\w-]+)\s*:\s*([^;]+)\s*;?/", $this->attr['style'], $matches, PREG_SET_ORDER);
            foreach ($matches as $match) {
                $attributes[$match[1]] = $match[2];
            }
            // If there is a width in the style attributes.
            if (isset($attributes['width']) && $width == - 1) {
                // Check that the last two characters are px (pixels).
                if (strtolower(substr($attributes['width'], - 2)) == 'px') {
                    $proposed_width = substr($attributes['width'], 0, - 2);
                    // Now make sure that it's an integer and not something stupid.
                    if (filter_var($proposed_width, FILTER_VALIDATE_INT)) {
                        $width = $proposed_width;
                    }
                }
            }
            // If there is a width in the style attributes.
            if (isset($attributes['height']) && $height == - 1) {
                // Check that the last two characters are px (pixels).
                if (strtolower(substr($attributes['height'], - 2)) == 'px') {
                    $proposed_height = substr($attributes['height'], 0, - 2);
                    // Now make sure that it's an integer and not something stupid.
                    if (filter_var($proposed_height, FILTER_VALIDATE_INT)) {
                        $height = $proposed_height;
                    }
                }
            }
        }
        // Future enhancement:
        // Look in the tag to see if there is a class or id specified that has a height or width attribute to it.
        // Far future enhancement
        // Look at all the parent tags of this image to see if they specify a class or id that has an img selector that specifies a
        // height or width
        // Note that in this case, the class or id will have the img subselector for it to apply to the image.
        // ridiculously far future development
        // If the class or id is specified in a SEPARATE css file thats not on the page, go get it and do what we were just doing
        // for the ones on the page.
        $result = array ('height' => $height, 'width' => $width);
        return $result;
    }
    /**
     * camel naming conventions
     * 
     * @return multitype:
     */
    public function getallattributes() {
        return $this->attr;
    }
    /**
     * get attributes
     * 
     * @param unknown_type $name
     * @return Ambigous <boolean, unknown, string, mixed, multitype:>
     */
    public function getattribute($name) {
        return $this->__get($name);
    }
    /**
     * set attributes
     * 
     * @param unknown_type $name
     * @param unknown_type $value
     */
    public function setattribute($name, $value) {
        $this->__set($name, $value);
    }
    /**
     * check if it has attributes
     * 
     * @param unknown_type $name
     * @return boolean
     */
    public function hasattribute($name) {
        return $this->__isset($name);
    }
    /**
     * remove attributes
     * 
     * @param unknown_type $name
     */
    public function removeattribute($name) {
        $this->__set($name, null);
    }
    /**
     * get element based on id
     * 
     * @param unknown_type $id
     * @return Ambigous <multitype:, NULL, multitype:NULL >
     */
    public function getelementbyid($id) {
        return $this->find("#$id", 0);
    }
    /**
     * get element based on id
     * 
     * @param unknown_type $id
     * @param unknown_type $idx
     * @return Ambigous <multitype:, NULL, multitype:NULL >
     */
    public function getelementsbyid($id, $idx = null) {
        return $this->find("#$id", $idx);
    }
    /**
     * get element based on tag name
     * 
     * @param unknown_type $name
     * @return Ambigous <multitype:, NULL, multitype:NULL >
     */
    public function getelementbytagname($name) {
        return $this->find($name, 0);
    }
    /**
     * get elements based on tag name
     * 
     * @param unknown_type $name
     * @param unknown_type $idx
     * @return Ambigous <multitype:, NULL, multitype:NULL >
     */
    public function getelementsbytagname($name, $idx = null) {
        return $this->find($name, $idx);
    }
    /**
     * get parent node
     * 
     * @return mixed
     */
    public function parentnode() {
        return $this->shdom_parent();
    }
    /**
     * find child nodes
     * 
     * @param unknown_type $idx
     * @return Ambigous <NULL, multitype:>
     */
    public function childnodes($idx = -1) {
        return $this->children($idx);
    }
    /**
     * get first child
     * 
     * @return Ambigous <NULL, multitype:>
     */
    public function firstchild() {
        return $this->first_child();
    }
    /**
     * find last child
     * 
     * @return Ambigous <NULL, multitype:>
     */
    public function lastchild() {
        return $this->last_child();
    }
    /**
     * get next sibling
     * 
     * @return mixed
     */
    public function nextsibling() {
        return $this->next_sibling();
    }
    /**
     * get previous sibling
     * 
     * @return mixed
     */
    public function previoussibling() {
        return $this->prev_sibling();
    }
    /**
     * check if it has child nodes
     * 
     * @return boolean
     */
    public function haschildnodes() {
        return $this->has_child();
    }
    /**
     * get node name
     * 
     * @return string
     */
    public function nodename() {
        return $this->tag;
    }
    /**
     * add child node
     * 
     * @param unknown_type $node
     * @return unknown
     */
    public function appendchild($node) {
        $node->shdom_parent($this);
        return $node;
    }
}
/**
 * simple html dom parser
 * 
 * @package mod_turningtech
 * @copyright 2012 Turning Technologies
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class simple_html_dom {
    /**
     *
     * @var unknown_type
     */
    public $root = null;
    /**
     *
     * @var unknown_type
     */
    public $nodes = array ();
    /**
     *
     * @var unknown_type
     */
    public $callback = null;
    /**
     *
     * @var unknown_type
     */
    public $lowercase = false;
    // Used to keep track of how large the text was when we started.
    /**
     *
     * @var unknown_type
     */
    public $original_size;
    /**
     *
     * @var unknown_type
     */
    public $size;
    /**
     *
     * @var unknown_type
     */
    protected $pos;
    /**
     *
     * @var unknown_type
     */
    protected $doc;
    /**
     *
     * @var unknown_type
     */
    protected $char;
    /**
     *
     * @var unknown_type
     */
    protected $cursor;
    /**
     *
     * @var unknown_type
     */
    protected $parent;
    /**
     *
     * @var unknown_type
     */
    protected $noise = array ();
    /**
     *
     * @var unknown_type
     */
    protected $token_blank = " \t\r\n";
    /**
     *
     * @var unknown_type
     */
    protected $token_equal = ' =/>';
    /**
     *
     * @var unknown_type
     */
    protected $token_slash = " />\r\n\t";
    /**
     *
     * @var unknown_type
     */
    protected $token_attr = ' >';
    // Note that this is referenced by a child node, and so it needs to be public for that node to see this information.
    /**
     *
     * @var unknown_type
     */
    public $_charset = '';
    /**
     *
     * @var unknown_type
     */
    public $_target_charset = '';
    /**
     *
     * @var unknown_type
     */
    protected $default_br_text = "";
    /**
     *
     * @var unknown_type
     */
    public $default_span_text = "";
    // Use isset instead of in_array, performance boost about 30%...
    /**
     *
     * @var unknown_type
     */
    protected $self_closing_tags = array ('img' => 1, 'br' => 1, 'input' => 1, 'meta' => 1,
                     'link' => 1, 'hr' => 1, 'base' => 1, 'embed' => 1, 'spacer' => 1);
    /**
     *
     * @var unknown_type
     */
    protected $block_tags = array ('root' => 1, 'body' => 1, 'form' => 1, 'div' => 1, 'span' => 1,
                     'table' => 1);
    // Known sourceforge issue #2977341
    // B tags that are not closed cause us to return everything to the end of the document.
    /**
     *
     * @var unknown_type
     */
    protected $optional_closing_tags = array ('tr' => array ('tr' => 1, 'td' => 1, 'th' => 1),
                     'th' => array ('th' => 1), 'td' => array ('td' => 1), 'li' => array ('li' => 1),
                    'dt' => array ('dt' => 1, 'dd' => 1), 'dd' => array ('dd' => 1, 'dt' => 1),
                     'dl' => array ('dd' => 1, 'dt' => 1), 'p' => array ('p' => 1),
                    'nobr' => array ('nobr' => 1), 'b' => array ('b' => 1),
                     'option' => array ('option' => 1));
    /**
     * Constructor
     * 
     * @param unknown_type $str
     * @param unknown_type $lowercase
     * @param unknown_type $forcetagsclosed
     * @param unknown_type $target_charset
     * @param unknown_type $striprn
     * @param unknown_type $defaultbrtext
     * @param unknown_type $defaultspantext
     */
    public function __construct($str = null, $lowercase = true, $forcetagsclosed = true,
                                     $target_charset = TT_DEFAULT_TARGET_CHARSET, $striprn = true,
                                     $defaultbrtext = TT_DEFAULT_BR_TEXT, $defaultspantext = TT_DEFAULT_SPAN_TEXT) {
        if ($str) {
            if (preg_match("/^http:\/\//i", $str) || is_file($str)) {
                $this->load_file($str);
            } else {
                $this->load($str, $lowercase, $striprn, $defaultbrtext, $defaultspantext);
            }
        }
        // Forcing tags to be closed implies that we don't trust the html, but it can lead to parsing errors if we SHOULD trust the
        // html.
        if (!$forcetagsclosed) {
            $this->optional_closing_array = array ();
        }
        $this->_target_charset = $target_charset;
    }
    /**
     * Destructor
     */
    public function __destruct() {
        $this->clear();
    }
    /**
     * load html from string
     * 
     * @param unknown_type $str
     * @param unknown_type $lowercase
     * @param unknown_type $striprn
     * @param unknown_type $defaultbrtext
     * @param unknown_type $defaultspantext
     * @return simple_html_dom
     */
    public function load($str, $lowercase = true, $striprn = true,
                                     $defaultbrtext = TT_DEFAULT_BR_TEXT,
                                     $defaultspantext = TT_DEFAULT_SPAN_TEXT) {
        global $debugobject;
        // Prepare.
        $this->prepare($str, $lowercase, $striprn, $defaultbrtext, $defaultspantext);
        // Strip out comments.
        $this->remove_noise("'<!--(.*?)-->'is");
        // Strip out cdata.
        $this->remove_noise("'<!\[CDATA\[(.*?)\]\]>'is", true);
        // Per sourceforge http://sourceforge.net/tracker/?func=detail&aid=2949097&group_id=218559&atid=1044037
        // Script tags removal now preceeds style tag removal.
        // Strip out <script> tags.
        $this->remove_noise("'<\s*script[^>]*[^/]>(.*?)<\s*/\s*script\s*>'is");
        $this->remove_noise("'<\s*script\s*>(.*?)<\s*/\s*script\s*>'is");
        // Strip out <style> tags.
        $this->remove_noise("'<\s*style[^>]*[^/]>(.*?)<\s*/\s*style\s*>'is");
        $this->remove_noise("'<\s*style\s*>(.*?)<\s*/\s*style\s*>'is");
        // Strip out preformatted tags.
        $this->remove_noise("'<\s*(?:code)[^>]*>(.*?)<\s*/\s*(?:code)\s*>'is");
        // Strip out server side scripts.
        $this->remove_noise("'(<\?)(.*?)(\?>)'s", true);
        // Strip smarty scripts.
        $this->remove_noise("'(\{\w)(.*?)(\})'s", true);
        // Sarsing.
        while ($this->parse()) {
            continue;
        }
        // End.
        $this->root->_[TT_HDOM_INFO_END] = $this->cursor;
        $this->parse_charset();
        // Make load function chainable.
        return $this;
    }
    /**
     * load html from file
     * 
     * @return boolean
     */
    public function load_file() {
        $args = func_get_args();
        $this->load(call_user_func_array('file_get_contents', $args), true);
        // Throw an error if we can't properly load the dom.
        if (($error = error_get_last()) !== null) {
            $this->clear();
            return false;
        }
    }
    /**
     * set callback function
     * 
     * @param unknown_type $function_name
     */
    public function set_callback($function_name) {
        $this->callback = $function_name;
    }
    /**
     * remove callback function
     */
    public function remove_callback() {
        $this->callback = null;
    }
    /**
     * save dom as string
     * 
     * @param unknown_type $filepath
     * @return unknown
     */
    public function save($filepath = '') {
        $ret = $this->root->innertext();
        if ($filepath !== '') {
            file_put_contents($filepath, $ret, LOCK_EX);
        }
        return $ret;
    }
    // Paperg - allow us to specify that we want case insensitive testing of the value of the selector.
    /**
     * find dom node by css selector
     * 
     * @param unknown_type $selector
     * @param unknown_type $idx
     * @param unknown_type $lowercase
     */
    public function find($selector, $idx = null, $lowercase = false) {
        return $this->root->find($selector, $idx, $lowercase);
    }
    /**
     * clean up memory due to php5 circular references memory leak...
     * 
     * @return unknown_type
     */
    public function clear() {
        foreach ($this->nodes as $n) {
            $n->clear();
            $n = null;
        }
        // This add next line is documented in the sourceforge repository. 2977248 as a fix for ongoing memory leaks that occur even
        // with the use of clear.
        if (isset($this->children)) {
            foreach ($this->children as $n) {
                $n->clear();
                $n = null;
            }
        }
        if (isset($this->parent)) {
            $this->parent->clear();
            unset($this->parent);
        }
        if (isset($this->root)) {
            $this->root->clear();
            unset($this->root);
        }
        unset($this->doc);
        unset($this->noise);
    }
    /**
     * dump
     * 
     * @param unknown_type $show_attr
     */
    public function dump($show_attr = true) {
        $this->root->dump($show_attr);
    }
    /**
     * prepare HTML data and init everything
     * 
     * @param unknown_type $str
     * @param unknown_type $lowercase
     * @param unknown_type $striprn
     * @param unknown_type $defaultbrtext
     * @param unknown_type $defaultspantext
     */
    protected function prepare($str, $lowercase = true, $striprn = true,
                                     $defaultbrtext = TT_DEFAULT_BR_TEXT,
                                     $defaultspantext = TT_DEFAULT_SPAN_TEXT) {
        $this->clear();
        // Set the length of content before we do anything to it.
        $this->size = strlen($str);
        // Save the original size of the html that we got in. It might be useful to someone.
        $this->original_size = $this->size;
        // Before we save the string as the doc... strip out the \r \n's if we are told to.
        if ($striprn) {
            $str = str_replace("\r", " ", $str);
            $str = str_replace("\n", " ", $str);
            // Set the length of content since we have changed it.
            $this->size = strlen($str);
        }
        $this->doc = $str;
        $this->pos = 0;
        $this->cursor = 1;
        $this->noise = array ();
        $this->nodes = array ();
        $this->lowercase = $lowercase;
        $this->default_br_text = $defaultbrtext;
        $this->default_span_text = $defaultspantext;
        $this->root = new simple_html_dom_node($this);
        $this->root->tag = 'root';
        $this->root->_[TT_HDOM_INFO_BEGIN] = - 1;
        $this->root->nodetype = TT_HDOM_TYPE_ROOT;
        $this->parent = $this->root;
        if ($this->size > 0) {
            $this->char = $this->doc[0];
        }
    }
    /**
     * parse html content
     * 
     * @return boolean
     */
    protected function parse() {
        if (($s = $this->copy_until_char('<')) === '') {
            return $this->read_tag();
        }
        // Text.
        $node = new simple_html_dom_node($this);
        ++ $this->cursor;
        $node->_[TT_HDOM_INFO_TEXT] = $s;
        $this->link_nodes($node, false);
        return true;
    }
    // PAPERG - dkchou - added this to try to identify the character set of the page we have just parsed so we know better how to
    // spit it out later.
    // NOTE: IF you provide a routine called get_last_retrieve_url_contents_content_type which returns the CURLINFO_CONTENT_TYPE
    // from the last curl_exec
    // (or the content_type header from the last transfer), we will parse THAT, and if a charset is specified, we will use it over
    // any other mechanism.
    /**
     * parse charset
     * 
     * @return Ambigous <NULL, string>
     */
    protected function parse_charset() {
        global $debugobject;
        $charset = null;
        if (function_exists('get_last_retrieve_url_contents_content_type')) {
            $contenttypeheader = get_last_retrieve_url_contents_content_type();
            $success = preg_match('/charset=(.+)/', $contenttypeheader, $matches);
            if ($success) {
                $charset = $matches[1];
                if (is_object($debugobject)) {
                    $debugobject->debugLog(2, 'header content-type found charset of: ' . $charset);
                }
            }
        }
        if (empty($charset)) {
            $el = $this->root->find('meta[http-equiv=Content-Type]', 0);
            if (! empty($el)) {
                $fullvalue = $el->content;
                if (is_object($debugobject)) {
                    $debugobject->debugLog(2, 'meta content-type tag found' . $fullvalue);
                }
                if (! empty($fullvalue)) {
                    $success = preg_match('/charset=(.+)/', $fullvalue, $matches);
                    if ($success) {
                        $charset = $matches[1];
                    } else {
                        // If there is a meta tag, and they don't specify the character set, research says that it's typically
                        // ISO-8859-1.
                        if (is_object($debugobject)) {
                            $debugobject->debugLog(2, 'meta content-type tag couldn\'t be parsed. using iso-8859 default.');
                        }
                        $charset = 'ISO-8859-1';
                    }
                }
            }
        }
        // If we couldn't find a charset above, then lets try to detect one based on the text we got...
        if (empty($charset)) {
            // Have php try to detect the encoding from the text given to us.
            $charset = mb_detect_encoding($this->root->plaintext . "ascii", $encoding_list = array ("UTF-8", "CP1252"));
            if (is_object($debugobject)) {
                $debugobject->debugLog(2, 'mb_detect found: ' . $charset);
            }
            // And if this doesn't work... then we need to just wrongheadedly assume it's UTF-8 so that we can move on - cause this
            // will usually give us most of what we need...
            if ($charset === false) {
                if (is_object($debugobject)) {
                    $debugobject->debugLog(2, 'since mb_detect failed - using default of utf-8');
                }
                $charset = 'UTF-8';
            }
        }
        // Since CP1252 is a superset, if we get one of it's subsets, we want it instead.
        if ((strtolower($charset) == strtolower('ISO-8859-1')) ||
                                         (strtolower($charset) == strtolower('Latin1')) ||
                                         (strtolower($charset) == strtolower('Latin-1'))) {
            if (is_object($debugobject)) {
                $debugobject->debugLog(2, 'replacing ' . $charset . ' with CP1252 as its a superset');
            }
            $charset = 'CP1252';
        }
        if (is_object($debugobject)) {
            $debugobject->debugLog(1, 'EXIT - ' . $charset);
        }
        return $this->_charset = $charset;
    }
    /**
     * read tag info
     * 
     * @return boolean
     */
    protected function read_tag() {
        if ($this->char !== '<') {
            $this->root->_[TT_HDOM_INFO_END] = $this->cursor;
            return false;
        }
        $begin_tag_pos = $this->pos;
        $this->char = (++ $this->pos < $this->size) ? $this->doc[$this->pos] : null; // Next.
                                                                                     // end tag.
        if ($this->char === '/') {
            $this->char = (++ $this->pos < $this->size) ? $this->doc[$this->pos] : null; // Next
                                                                                         // This represents the change in the
                                                                                         // simple_html_dom trunk from revision 180
                                                                                         // to
                                                                                         // 181.
            $this->skip($this->token_blank);
            $tag = $this->copy_until_char('>');
            // Skip attributes in end tag.
            if (($pos = strpos($tag, ' ')) !== false) {
                $tag = substr($tag, 0, $pos);
            }
            $parent_lower = strtolower($this->parent->tag);
            $tag_lower = strtolower($tag);
            if ($parent_lower !== $tag_lower) {
                if (isset($this->optional_closing_tags[$parent_lower]) && isset($this->block_tags[$tag_lower])) {
                    $this->parent->_[TT_HDOM_INFO_END] = 0;
                    $org_parent = $this->parent;
                    while (($this->parent->parent) && strtolower($this->parent->tag) !== $tag_lower) {
                        $this->parent = $this->parent->parent;
                    }
                    if (strtolower($this->parent->tag) !== $tag_lower) {
                        $this->parent = $org_parent; // Restore origonal parent.
                        if ($this->parent->parent) {
                            $this->parent = $this->parent->parent;
                        }
                        $this->parent->_[TT_HDOM_INFO_END] = $this->cursor;
                        return $this->as_text_node($tag);
                    }
                } else if (($this->parent->parent) && isset($this->block_tags[$tag_lower])) {
                    $this->parent->_[TT_HDOM_INFO_END] = 0;
                    $org_parent = $this->parent;
                    while (($this->parent->parent) && strtolower($this->parent->tag) !== $tag_lower) {
                        $this->parent = $this->parent->parent;
                    }
                    if (strtolower($this->parent->tag) !== $tag_lower) {
                        $this->parent = $org_parent; // Restore origonal parent.
                        $this->parent->_[TT_HDOM_INFO_END] = $this->cursor;
                        return $this->as_text_node($tag);
                    }
                } else if (($this->parent->parent) && strtolower($this->parent->parent->tag) === $tag_lower) {
                    $this->parent->_[TT_HDOM_INFO_END] = 0;
                    $this->parent = $this->parent->parent;
                } else {
                    return $this->as_text_node($tag);
                }
            }
            $this->parent->_[TT_HDOM_INFO_END] = $this->cursor;
            if ($this->parent->parent) {
                $this->parent = $this->parent->parent;
            }
            $this->char = (++ $this->pos < $this->size) ? $this->doc[$this->pos] : null; // Next.
            return true;
        }
        $node = new simple_html_dom_node($this);
        $node->_[TT_HDOM_INFO_BEGIN] = $this->cursor;
        ++ $this->cursor;
        $tag = $this->copy_until($this->token_slash);
        $node->tag_start = $begin_tag_pos;
        // Doctype, cdata & comments...
        if (isset($tag[0]) && $tag[0] === '!') {
            $node->_[TT_HDOM_INFO_TEXT] = '<' . $tag . $this->copy_until_char('>');
            if (isset($tag[2]) && $tag[1] === '-' && $tag[2] === '-') {
                $node->nodetype = TT_HDOM_TYPE_COMMENT;
                $node->tag = 'comment';
            } else {
                $node->nodetype = TT_HDOM_TYPE_UNKNOWN;
                $node->tag = 'unknown';
            }
            if ($this->char === '>') {
                $node->_[TT_HDOM_INFO_TEXT] .= '>';
            }
            $this->link_nodes($node, true);
            $this->char = (++ $this->pos < $this->size) ? $this->doc[$this->pos] : null; // Next.
            return true;
        }
        // Text.
        if ($pos = strpos($tag, '<') !== false) {
            $tag = '<' . substr($tag, 0, - 1);
            $node->_[TT_HDOM_INFO_TEXT] = $tag;
            $this->link_nodes($node, false);
            $this->char = $this->doc[-- $this->pos]; // Prev.
            return true;
        }
        if (! preg_match("/^[\w-:]+$/", $tag)) {
            $node->_[TT_HDOM_INFO_TEXT] = '<' . $tag . $this->copy_until('<>');
            if ($this->char === '<') {
                $this->link_nodes($node, false);
                return true;
            }
            if ($this->char === '>') {
                $node->_[TT_HDOM_INFO_TEXT] .= '>';
            }
            $this->link_nodes($node, false);
            $this->char = (++ $this->pos < $this->size) ? $this->doc[$this->pos] : null; // Next.
            return true;
        }
        // Begin tag.
        $node->nodetype = TT_HDOM_TYPE_ELEMENT;
        $tag_lower = strtolower($tag);
        $node->tag = ($this->lowercase) ? $tag_lower : $tag;
        // Handle optional closing tags.
        if (isset($this->optional_closing_tags[$tag_lower])) {
            while (isset($this->optional_closing_tags[$tag_lower][strtolower($this->parent->tag)])) {
                $this->parent->_[TT_HDOM_INFO_END] = 0;
                $this->parent = $this->parent->parent;
            }
            $node->parent = $this->parent;
        }
        $guard = 0; // Prevent infinity loop.
        $space = array ($this->copy_skip($this->token_blank), '', '');
        // Attributes.
        do {
            if ($this->char !== null && $space[0] === '') {
                break;
            }
            $name = $this->copy_until($this->token_equal);
            if ($guard === $this->pos) {
                $this->char = (++ $this->pos < $this->size) ? $this->doc[$this->pos] : null; // Next.
                continue;
            }
            $guard = $this->pos;
            // Handle endless '<'.
            if ($this->pos >= $this->size - 1 && $this->char !== '>') {
                $node->nodetype = TT_HDOM_TYPE_TEXT;
                $node->_[TT_HDOM_INFO_END] = 0;
                $node->_[TT_HDOM_INFO_TEXT] = '<' . $tag . $space[0] . $name;
                $node->tag = 'text';
                $this->link_nodes($node, false);
                return true;
            }
            // Handle mismatch '<'.
            if ($this->doc[$this->pos - 1] == '<') {
                $node->nodetype = TT_HDOM_TYPE_TEXT;
                $node->tag = 'text';
                $node->attr = array ();
                $node->_[TT_HDOM_INFO_END] = 0;
                $node->_[TT_HDOM_INFO_TEXT] = substr($this->doc, $begin_tag_pos, $this->pos - $begin_tag_pos - 1);
                $this->pos -= 2;
                $this->char = (++ $this->pos < $this->size) ? $this->doc[$this->pos] : null; // Next.
                $this->link_nodes($node, false);
                return true;
            }
            if ($name !== '/' && $name !== '') {
                $space[1] = $this->copy_skip($this->token_blank);
                $name = $this->restore_noise($name);
                if ($this->lowercase) {
                    $name = strtolower($name);
                }
                if ($this->char === '=') {
                    $this->char = (++ $this->pos < $this->size) ? $this->doc[$this->pos] : null; // Next.
                    $this->parse_attr($node, $name, $space);
                } else {
                    // No value attr: nowrap, checked selected...
                    $node->_[TT_HDOM_INFO_QUOTE][] = TT_HDOM_QUOTE_NO;
                    $node->attr[$name] = true;
                    if ($this->char != '>') {
                        $this->char = $this->doc[-- $this->pos]; // Prev.
                    }
                }
                $node->_[TT_HDOM_INFO_SPACE][] = $space;
                $space = array ($this->copy_skip($this->token_blank), '', '');
            } else {
                break;
            }
        } while ($this->char !== '>' && $this->char !== '/');
        $this->link_nodes($node, true);
        $node->_[TT_HDOM_INFO_ENDSPACE] = $space[0];
        // Check self closing.
        if ($this->copy_until_char_escape('>') === '/') {
            $node->_[TT_HDOM_INFO_ENDSPACE] .= '/';
            $node->_[TT_HDOM_INFO_END] = 0;
        } else {
            // Reset parent.
            if (! isset($this->self_closing_tags[strtolower($node->tag)])) {
                $this->parent = $node;
            }
        }
        $this->char = (++ $this->pos < $this->size) ? $this->doc[$this->pos] : null; // Next
                                                                                     // If it's a BR tag, we need to set it's text
                                                                                     // to
                                                                                     // the default text.
                                                                                     // This way when we see it in plaintext, we can
                                                                                     // generate formatting that the user wants.
                                                                                     // since a br tag never has sub nodes, this
                                                                                     // works
                                                                                     // well.
        if ($node->tag == "br") {
            $node->_[TT_HDOM_INFO_INNER] = $this->default_br_text;
        }
        return true;
    }
    /**
     * parse attributes
     * 
     * @param unknown_type $node
     * @param unknown_type $name
     * @param unknown_type $space
     */
    protected function parse_attr($node, $name, &$space) {
        // Per sourceforge: http://sourceforge.net/tracker/?func=detail&aid=3061408&group_id=218559&atid=1044037
        // If the attribute is already defined inside a tag, only pay atetntion to the first one as opposed to the last one.
        if (isset($node->attr[$name])) {
            return;
        }
        $space[2] = $this->copy_skip($this->token_blank);
        switch ($this->char) {
            case '"' :
                $node->_[TT_HDOM_INFO_QUOTE][] = TT_HDOM_QUOTE_DOUBLE;
                $this->char = (++ $this->pos < $this->size) ? $this->doc[$this->pos] : null; // Next.
                $node->attr[$name] = $this->restore_noise($this->copy_until_char_escape('"'));
                $this->char = (++ $this->pos < $this->size) ? $this->doc[$this->pos] : null; // Next.
                break;
            case '\'' :
                $node->_[TT_HDOM_INFO_QUOTE][] = TT_HDOM_QUOTE_SINGLE;
                $this->char = (++ $this->pos < $this->size) ? $this->doc[$this->pos] : null; // Next.
                $node->attr[$name] = $this->restore_noise($this->copy_until_char_escape('\''));
                $this->char = (++ $this->pos < $this->size) ? $this->doc[$this->pos] : null; // Next.
                break;
            default :
                $node->_[TT_HDOM_INFO_QUOTE][] = TT_HDOM_QUOTE_NO;
                $node->attr[$name] = $this->restore_noise($this->copy_until($this->token_attr));
        }
        // PaperG: Attributes should not have \r or \n in them, that counts as html whitespace.
        $node->attr[$name] = str_replace("\r", "", $node->attr[$name]);
        $node->attr[$name] = str_replace("\n", "", $node->attr[$name]);
        // PaperG: If this is a "class" selector, lets get rid of the preceeding and trailing space since some people leave it in
        // the multi class case.
        if ($name == "class") {
            $node->attr[$name] = trim($node->attr[$name]);
        }
    }
    /**
     * link node's parent
     * 
     * @param unknown_type $node
     * @param unknown_type $is_child
     */
    protected function link_nodes(&$node, $is_child) {
        $node->parent = $this->parent;
        $this->parent->nodes[] = $node;
        if ($is_child) {
            $this->parent->children[] = $node;
        }
    }
    /**
     * as a text node
     * 
     * @param unknown_type $tag
     * @return bool
     */
    protected function as_text_node($tag) {
        $node = new simple_html_dom_node($this);
        ++ $this->cursor;
        $node->_[TT_HDOM_INFO_TEXT] = '</' . $tag . '>';
        $this->link_nodes($node, false);
        $this->char = (++ $this->pos < $this->size) ? $this->doc[$this->pos] : null; // Next.
        return true;
    }
    /**
     * skip
     * 
     * @param unknown_type $chars
     */
    protected function skip($chars) {
        $this->pos += strspn($this->doc, $chars, $this->pos);
        $this->char = ($this->pos < $this->size) ? $this->doc[$this->pos] : null; // Next.
    }
    /**
     * skip copy
     * 
     * @param unknown_type $chars
     * @return string
     */
    protected function copy_skip($chars) {
        $pos = $this->pos;
        $len = strspn($this->doc, $chars, $pos);
        $this->pos += $len;
        $this->char = ($this->pos < $this->size) ? $this->doc[$this->pos] : null; // Next.
        if ($len === 0) {
            return '';
        }
        return substr($this->doc, $pos, $len);
    }
    /**
     * copy
     * 
     * @param unknown_type $chars
     * @return string
     */
    protected function copy_until($chars) {
        $pos = $this->pos;
        $len = strcspn($this->doc, $chars, $pos);
        $this->pos += $len;
        $this->char = ($this->pos < $this->size) ? $this->doc[$this->pos] : null; // Next.
        return substr($this->doc, $pos, $len);
    }
    /**
     * copy until a char
     * 
     * @param unknown_type $char
     * @return string
     */
    protected function copy_until_char($char) {
        if ($this->char === null) {
            return '';
        }
        if (($pos = strpos($this->doc, $char, $this->pos)) === false) {
            $ret = substr($this->doc, $this->pos, $this->size - $this->pos);
            $this->char = null;
            $this->pos = $this->size;
            return $ret;
        }
        if ($pos === $this->pos) {
            return '';
        }
        $pos_old = $this->pos;
        $this->char = $this->doc[$pos];
        $this->pos = $pos;
        return substr($this->doc, $pos_old, $pos - $pos_old);
    }
    /**
     * copy until a char escape
     * 
     * @param unknown_type $char
     * @return string
     */
    protected function copy_until_char_escape($char) {
        if ($this->char === null) {
            return '';
        }
        $start = $this->pos;
        while (1) {
            if (($pos = strpos($this->doc, $char, $start)) === false) {
                $ret = substr($this->doc, $this->pos, $this->size - $this->pos);
                $this->char = null;
                $this->pos = $this->size;
                return $ret;
            }
            if ($pos === $this->pos) {
                return '';
            }
            if ($this->doc[$pos - 1] === '\\') {
                $start = $pos + 1;
                continue;
            }
            $pos_old = $this->pos;
            $this->char = $this->doc[$pos];
            $this->pos = $pos;
            return substr($this->doc, $pos_old, $pos - $pos_old);
        }
    }
    /**
     * remove noise from html content
     * save the noise in the $this->noise array.
     * 
     * @param unknown_type $pattern
     * @param unknown_type $remove_tag
     */
    protected function remove_noise($pattern, $remove_tag = false) {
        global $debugobject;
        if (is_object($debugobject)) {
            $debugobject->debugLogEntry(1);
        }
        $count = preg_match_all($pattern, $this->doc, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);
        for ($i = $count - 1; $i > - 1; -- $i) {
            $key = '___noise___' . sprintf('% 5d', count($this->noise) + 1000);
            if (is_object($debugobject)) {
                $debugobject->debugLog(2, 'key is: ' . $key);
            }
            $idx = ($remove_tag) ? 0 : 1;
            $this->noise[$key] = $matches[$i][$idx][0];
            $this->doc = substr_replace($this->doc, $key, $matches[$i][$idx][1], strlen($matches[$i][$idx][0]));
        }
        // Reset the length of content.
        $this->size = strlen($this->doc);
        if ($this->size > 0) {
            $this->char = $this->doc[0];
        }
    }
    /**
     * restore noise to html content
     * 
     * @param unknown_type $text
     * @return string
     */
    public function restore_noise($text) {
        global $debugobject;
        if (is_object($debugobject)) {
            $debugobject->debugLogEntry(1);
        }
        while (($pos = strpos($text, '___noise___')) !== false) {
            // Sometimes there is a broken piece of markup, and we don't GET the pos+11 etc... token which indicates a problem
            // outside of us...
            if (strlen($text) > $pos + 15) {
                $key = '___noise___' . $text[$pos + 11] . $text[$pos + 12] . $text[$pos + 13] . $text[$pos + 14] . $text[$pos + 15];
                if (is_object($debugobject)) {
                    $debugobject->debugLog(2, 'located key of: ' . $key);
                }
                if (isset($this->noise[$key])) {
                    $text = substr($text, 0, $pos) . $this->noise[$key] . substr($text, $pos + 16);
                } else {
                    // Do this to prevent an infinite loop.
                    $text = substr($text, 0, $pos) . 'UNDEFINED NOISE FOR KEY: ' . $key . substr($text, $pos + 16);
                }
            } else {
                // There is no valid key being given back to us... We must get rid of the ___noise___ or we will have a problem.
                $text = substr($text, 0, $pos) . 'NO NUMERIC NOISE KEY' . substr($text, $pos + 11);
            }
        }
        return $text;
    }
    /**
     * Sometimes we NEED one of the noise elements.
     * 
     * @param unknown_type $text
     * @return unknown
     */
    public function search_noise($text) {
        global $debugobject;
        if (is_object($debugobject)) {
            $debugobject->debugLogEntry(1);
        }
        foreach ($this->noise as $noiseelement) {
            if (strpos($noiseelement, $text) !== false) {
                return $noiseelement;
            }
        }
    }
    /**
     * convert to string
     * 
     * @return mixed
     */
    public function __toString() {
        return $this->root->innertext();
    }
    /**
     * getters
     * 
     * @param unknown_type $name
     * @return string
     */
    public function __get($name) {
        switch ($name) {
            case 'outertext' :
                return $this->root->innertext();
            case 'innertext' :
                return $this->root->innertext();
            case 'plaintext' :
                return $this->root->text();
            case 'charset' :
                return $this->_charset;
            case 'target_charset' :
                return $this->_target_charset;
        }
    }
    /**
     * camel naming conventions
     * 
     * @param unknown_type $idx
     * @return mixed
     */
    public function childnodes($idx = -1) {
        return $this->root->childNodes($idx);
    }
    /**
     * get first child
     * 
     * @return mixed
     */
    public function firstchild() {
        return $this->root->first_child();
    }
    /**
     * get last child
     * 
     * @return mixed
     */
    public function lastchild() {
        return $this->root->last_child();
    }
    /**
     * create element
     * 
     * @param unknown_type $name
     * @param unknown_type $value
     * @return mixed
     */
    public function createelement($name, $value = null) {
        return @shdom_str_get_html("<$name>$value</$name>")->first_child();
    }
    /**
     * create text node
     * 
     * @param unknown_type $value
     * @return mixed
     */
    public function createtextnode($value) {
        return @end(shdom_str_get_html($value)->nodes);
    }
    /**
     * get element based on tag
     * 
     * @param unknown_type $id
     * @return mixed
     */
    public function getelementbyid($id) {
        return $this->find("#$id", 0);
    }
    /**
     * get elements based on id
     * 
     * @param unknown_type $id
     * @param unknown_type $idx
     * @return mixed
     */
    public function getelementsbyid($id, $idx = null) {
        return $this->find("#$id", $idx);
    }
    /**
     * get element based on tag
     * 
     * @param unknown_type $name
     * @return mixed
     */
    public function getelementbytagname($name) {
        return $this->find($name, 0);
    }
    /**
     * get elements based on tag
     * 
     * @param unknown_type $name
     * @param unknown_type $idx
     * @return mixed
     */
    public function getelementsbytagname($name, $idx = -1) {
        return $this->find($name, $idx);
    }
    /**
     * Load a file
     */
    public function shdom_loadfile() {
        $args = func_get_args();
        $this->load_file($args);
    }
}
// Helper functions
// $maxlen is defined in the code as PHP_STREAM_COPY_ALL which is defined as -1.
/**
 * get html dom from file
 * 
 * @param unknown_type $url
 * @param unknown_type $use_include_path
 * @param unknown_type $context
 * @param unknown_type $offset
 * @param unknown_type $maxlen
 * @param unknown_type $lowercase
 * @param unknown_type $forcetagsclosed
 * @param unknown_type $target_charset
 * @param unknown_type $striprn
 * @param unknown_type $defaultbrtext
 * @param unknown_type $defaultspantext
 * @return boolean simple_html_dom
 */
function shdom_file_get_html($url, $use_include_path = false, $context = null, $offset = -1, $maxlen = -1,
                                 $lowercase = true, $forcetagsclosed = true, $target_charset = TT_DEFAULT_TARGET_CHARSET,
                                 $striprn = true, $defaultbrtext = TT_DEFAULT_BR_TEXT, $defaultspantext = TT_DEFAULT_SPAN_TEXT) {
    // We DO force the tags to be terminated.
    $dom = new simple_html_dom(null, $lowercase, $forcetagsclosed, $target_charset, $striprn, $defaultbrtext, $defaultspantext);
    // For sourceforge users: uncomment the next line and comment the retreive_url_contents line 2 lines down if it is not already
    // done.
    $contents = file_get_contents($url, $use_include_path, $context, $offset);
    // Paperg - use our own mechanism for getting the contents as we want to control the timeout.
    // $contents = retrieve_url_contents($url);.
    if (empty($contents) || strlen($contents) > TT_MAX_FILE_SIZE) {
        return false;
    }
    // The second parameter can force the selectors to all be lowercase.
    $dom->load($contents, $lowercase, $striprn);
    return $dom;
}
/**
 * get html dom from string
 * 
 * @param unknown_type $str
 * @param unknown_type $lowercase
 * @param unknown_type $forcetagsclosed
 * @param unknown_type $target_charset
 * @param unknown_type $striprn
 * @param unknown_type $defaultbrtext
 * @param unknown_type $defaultspantext
 * @return boolean simple_html_dom
 */
function shdom_str_get_html($str, $lowercase = true, $forcetagsclosed = true,
                                 $target_charset = TT_DEFAULT_TARGET_CHARSET, $striprn = true,
                                 $defaultbrtext = TT_DEFAULT_BR_TEXT, $defaultspantext = TT_DEFAULT_SPAN_TEXT) {
    $dom = new simple_html_dom(null, $lowercase, $forcetagsclosed, $target_charset, $striprn, $defaultbrtext, $defaultspantext);
    if (empty($str) || strlen($str) > TT_MAX_FILE_SIZE) {
        $dom->clear();
        return false;
    }
    $dom->load($str, $lowercase, $striprn);
    return $dom;
}
/**
 * dump html dom tree
 * 
 * @param unknown_type $node
 * @param unknown_type $show_attr
 * @param unknown_type $deep
 */
function shdom_dump_html_tree($node, $show_attr = true, $deep = 0) {
    $node->dump($node);
}