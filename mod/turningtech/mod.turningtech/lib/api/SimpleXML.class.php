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
 * Simple XML for PHP
 *
 * @package   mod_turningtech
 * @copyright 2012 Turning Technologies
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
/**
 * Simple XML for PHP
 * @copyright 2012 Turning Technologies
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class SimpleXMLObject {
    /**
     * Finds and returns attributes
     * @return object
     */
    public function attributes() {
        $container = get_object_vars($this);
        return (object) $container["@attributes"];
    }
    /**
     * Finds and returns content
     * @return object
     * 
     */
    public function content() {
        $container = get_object_vars($this);
        return (object) $container["@content"];
    }
}

/**
 * The Main XML Parser Class
 * @copyright 2012 Turning Technologies
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
class SimpleXML {
    /**
     * @var array
     */
    public $result = array();
    /**
     * @var unknown_type
     */
    public $ignore_level = 0;
    /**
     * @var unknown_type
     */
    public $skip_empty_values = false;
    /**
     * @var unknown_type
     */
    public $php_errormsg;
    /**
     * @var unknown_type
     */
    public $evalcode = "";
    /**
     * Get File Contents using curl
     * @param string $url
     * @return mixed
     */
    public function curl_get_file_contents($url) {
        if (ini_get('allow_url_fopen') != 1) {
            $c = curl_init();
            curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($c, CURLOPT_URL, $url);
            $contents = curl_exec($c);
            curl_close($c);
        } else {
            $contents = file_get_contents($url);
        }
        if ($contents) {
            return $contents;
        } else {
            return false;
        }
    }

    /**
     * Adds Items to Array
     *
     * @param int $level
     * @param array $tags
     * @param mixed $value
     * @param string $type
     */
    public function array_insert($level, $tags, $value, $type) {
        $temp = '';
        for ($c = $this->ignore_level + 1; $c < $level + 1; $c++) {
            if (isset($tags[$c]) && (is_numeric(trim($tags[$c])) || trim($tags[$c]))) {
                if (is_numeric($tags[$c])) {
                    $temp .= '[' . $tags[$c] . ']';
                } else {
                    $temp .= '["' . $tags[$c] . '"]';
                }
            }
        }
        // Fix for getting PHP variable like string in XML node values (Contributor:Saulius Okunevicius).
        $this->evalcode .= '$this->result' . $temp . "= '" . addslashes($value) . "';//(" . $type . ")\n";
    }

    /**
     * Define the repeated tags in XML file so we can set an index
     *
     * @param array $array
     * @return array
     */
    public function xml_tags($array) {
        $repeats_temp  = array();
        $repeats_count = array();
        $repeats       = array();

        if (is_array($array)) {
            $n = count($array) - 1;
            for ($i = 0; $i < $n; $i++) {
                $idn = $array[$i]['tag'] . $array[$i]['level'];
                if (in_array($idn, $repeats_temp)) {
                    $repeats_count[array_search($idn, $repeats_temp)] += 1;
                } else {
                    array_push($repeats_temp, $idn);
                    $repeats_count[array_search($idn, $repeats_temp)] = 1;
                }
            }
        }
        $n = count($repeats_count);
        for ($i = 0; $i < $n; $i++) {
            if ($repeats_count[$i] > 1) {
                array_push($repeats, $repeats_temp[$i]);
            }
        }
        unset($repeats_temp);
        unset($repeats_count);
        return array_unique($repeats);
    }

    /**
     * Converts Array Variable to Object Variable
     *
     * @param array $arg_array
     * @return $tmp
     */
    public function array2object($arg_array) {
        if (is_array($arg_array)) {
            $keys = array_keys($arg_array);
            if (!is_numeric($keys[0])) {
                $tmp = new SimpleXMLObject;
            }
            foreach ($keys as $key) {
                if (is_numeric($key)) {
                    $has_number = true;
                }
                if (is_string($key)) {
                    $has_string = true;
                }
            }
            if (isset($has_number) and !isset($has_string)) {
                foreach ($arg_array as $key => $value) {
                    $tmp[] = $this->array2object($value);
                }
            } else if (isset($has_string)) {
                foreach ($arg_array as $key => $value) {
                    if (is_string($key)) {
                        $tmp->$key = $this->array2object($value);
                    }
                }
            }
        } else if (is_object($arg_array)) {
            foreach ($arg_array as $key => $value) {
                if (is_array($value) or is_object($value)) {
                    $tmp->$key = $this->array2object($value);
                } else {
                    $tmp->$key = $value;
                }
            }
        } else {
            $tmp = $arg_array;
        }
        return $tmp; // Return the object.
    }

    /**
     * Reindexes the whole array with ascending numbers
     *
     * @param array $array
     * @return array
     */
    public function array_reindex($array) {
        if (is_array($array)) {
            if (count($array) == 1 && array_key_exists(0, $array)) {
                return $this->array_reindex($array[0]);
            } else {
                foreach ($array as $keys => $items) {
                    if (is_array($items)) {
                        if (is_numeric($keys)) {
                            $array[$keys] = $this->array_reindex($items);
                        } else {
                            $array[$keys] = $this->array_reindex(array_merge(array(), $items));
                        }
                    }
                }
            }
        }

        return $array;
    }

    /**
     * Parse the XML generation to array object
     *
     * @param array $array
     * @return array
     */
    public function xml_reorganize($array) {
        $count       = count($array);
        $repeat      = $this->xml_tags($array);
        $repeatedone = false;
        $tags        = array();
        $k           = 0;

        for ($i = 0; $i < $count; $i++) {
            switch ($array[$i]['type']) {
                case 'open':
                    array_push($tags, $array[$i]['tag']);
                    if ($i > 0 && ($array[$i]['tag'] == $array[$i - 1]['tag']) && ($array[$i - 1]['type'] == 'close')) {
                        $k++;
                    }
                    if (isset($array[$i]['value']) && ($array[$i]['value'] || !$this->skip_empty_values)) {
                        array_push($tags, '@content');
                        $this->array_insert(count($tags), $tags, $array[$i]['value'], "open");
                        array_pop($tags);
                    }

                    if (in_array($array[$i]['tag'] . $array[$i]['level'], $repeat)) {
                        if (($repeatedone == $array[$i]['tag'] . $array[$i]['level']) && ($repeatedone)) {
                            array_push($tags, strval($k++));
                        } else {
                            $repeatedone = $array[$i]['tag'] . $array[$i]['level'];
                            array_push($tags, strval($k));
                        }
                    }

                    if (isset($array[$i]['attributes']) && $array[$i]['attributes'] && $array[$i]['level'] != $this->ignore_level) {
                        array_push($tags, '@attributes');
                        foreach ($array[$i]['attributes'] as $attrkey => $attr) {
                            array_push($tags, $attrkey);
                            $this->array_insert(count($tags), $tags, $attr, "open");
                            array_pop($tags);
                        }
                        array_pop($tags);
                    }
                    break;

                case 'close':
                    array_pop($tags);
                    if (in_array($array[$i]['tag'] . $array[$i]['level'], $repeat)) {
                        if ($repeatedone == $array[$i]['tag'] . $array[$i]['level']) {
                            array_pop($tags);
                        } else {
                            $repeatedone = $array[$i + 1]['tag'] . $array[$i + 1]['level'];
                            array_pop($tags);
                        }
                    }
                    break;

                case 'complete':
                    array_push($tags, $array[$i]['tag']);
                    if (in_array($array[$i]['tag'] . $array[$i]['level'], $repeat)) {
                        if ($repeatedone == $array[$i]['tag'] . $array[$i]['level'] && $repeatedone) {
                            array_push($tags, strval($k));
                        } else {
                            $repeatedone = $array[$i]['tag'] . $array[$i]['level'];
                            array_push($tags, strval($k));
                        }
                    }

                    if (isset($array[$i]['value']) && ($array[$i]['value'] || !$this->skip_empty_values)) {
                        if (isset($array[$i]['attributes']) && $array[$i]['attributes']) {
                            array_push($tags, '@content');
                            $this->array_insert(count($tags), $tags, $array[$i]['value'], "complete");
                            array_pop($tags);
                        } else {
                            $this->array_insert(count($tags), $tags, $array[$i]['value'], "complete");
                        }
                    }

                    if (isset($array[$i]['attributes']) && $array[$i]['attributes']) {
                        array_push($tags, '@attributes');
                        foreach ($array[$i]['attributes'] as $attrkey => $attr) {
                            array_push($tags, $attrkey);
                            $this->array_insert(count($tags), $tags, $attr, "complete");
                            array_pop($tags);
                        }
                        array_pop($tags);
                    }

                    if (in_array($array[$i]['tag'] . $array[$i]['level'], $repeat)) {
                        array_pop($tags);
                        $k++;
                    }

                    array_pop($tags);
                    break;
            }
        }
        eval($this->evalcode);
        $last = $this->array_reindex($this->result);
        return $last;
    }

    /**
     * Get the XML contents and parse like SimpleXML
     *
     * @param string $file
     * @param string $resulttype
     * @param string $encoding
     * @return array/object
     */
    public function xml_load_file($file, $resulttype = 'object', $encoding = 'UTF-8') {
        $php_errormsg   = "";
        $this->result   = "";
        $this->evalcode = "";
        $values         = "";

        $data = $this->curl_get_file_contents($file);

        if (!$data) {
            return 'Cannot open xml document: ' . (isset($php_errormsg) ? $php_errormsg : $file);
        }

        $parser = xml_parser_create($encoding);
        xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
        xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
        $ok = xml_parse_into_struct($parser, $data, $values);
        if (!$ok) {
            $errmsg = sprintf("XML parse error %d '%s' at line %d, column %d (byte index %d)",
                             xml_get_error_code($parser), xml_error_string(xml_get_error_code($parser)),
                             xml_get_current_line_number($parser), xml_get_current_column_number($parser),
                             xml_get_current_byte_index($parser));
        }
        xml_parser_free($parser);
        if (!$ok) {
            return $errmsg;
        }
        if ($resulttype == 'array') {
            return $this->xml_reorganize($values);
        }
        // Default $resulttype is 'object'.
        return $this->array2object($this->xml_reorganize($values));
    }

    /**
     * Get the XML contents and parse like SimpleXML
     *
     * @param string $data
     * @param string $resulttype
     * @param string $encoding
     * @return array|object
     */
    public function xml_load_string($data, $resulttype = 'object', $encoding = 'UTF-8') {
        $php_errormsg   = "";
        $this->result   = "";
        $this->evalcode = "";
        $values         = "";

        if (!$data) {
            return 'Cannot open xml document: ' . (isset($php_errormsg) ? $php_errormsg : $file);
        }

        $parser = xml_parser_create($encoding);
        xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
        xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
        $ok = xml_parse_into_struct($parser, $data, $values);
        if (!$ok) {
            $errmsg = sprintf("XML parse error %d '%s' at line %d, column %d (byte index %d)",
                             xml_get_error_code($parser), xml_error_string(xml_get_error_code($parser)),
                             xml_get_current_line_number($parser), xml_get_current_column_number($parser),
                             xml_get_current_byte_index($parser));
        }
        xml_parser_free($parser);
        if (!$ok) {
            return $errmsg;
        }
        if ($resulttype == 'array') {
            return $this->xml_reorganize($values);
        }
        // Default $resulttype is 'object'.
        return $this->array2object($this->xml_reorganize($values));
    }
}
/**
 * Finds and returns attributes
 * @param string $string
 * @return mixed
 */
function turningtech_simplexml_load_string($string) {
    $sx = new SimpleXML();

    return $sx->xml_load_string($string);
}