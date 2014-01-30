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
 * represents a user/course/deviceId mapping
 *
 * @author jacob
 * @package mod_turningtech
 * @copyright 2012 Turning Technologies
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
global $CFG;
/**
 * Class for user, course and deviceid mapping
 * @author jacob
 * @copyright 2012 Turning Technologies
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class TurningSessionMap {
    /**
     * @var unknown_type
     */
    protected $xmldata;
    /**
     * @var unknown_type
     */
    protected $sessionname;
    /**
     * @var unknown_type
     */
    protected $responders = array();
    /**
     * @var unknown_type
     */
    protected $questions = array();
    /**
     * @var unknown_type
     */
    protected $maxscore = 0;
    /**
     * @var unknown_type
     */
    public $classname = 'TurningTechSessionMap';
    /**
     * Session constructor
     * @param mixed $dataxml
     * @param mixed $sessname
     * @return unknown_type
     */
    public function __construct($dataxml, $sessname) {
        $this->sessionname = rtrim($sessname, '.tpzx');
        if (self::setxmldata($dataxml)) {
            return self::initializedata();
        } else {
            return false;
        }
    }
    /**
     * Initialize session variables
     * 
     */
    public function initializedata() {
        self::setresponders();
        self::setquestions();
        self::setdevicescore();
    }
    /**
     * Set Responders
     */
    public function setresponders() {
        $data = $this->xmldata;
        $responsers = array();
        $result = $data->xpath('responders');
        foreach ($result[0] as $res) {
            $responders[strval($res->deviceid)] = 0;
        }
        $this->responders = $responders;
    }
    /**
     * Get Device scorelist
     * 
     * @return array
     */
    public function getdevicescore() {
        $actions = array();
        if (count($this->responders)) {
            foreach ($this->responders as $key => $value) {
                $a = new stdClass();
                $a->deviceId = $key;
                $a->pointsEarned = $value;
                $a->pointsPossible = $this->maxscore;
                $actions[] = $a;
            }
            return $actions;
        } else {
            return false;
        }
    }
    /**
     * Get maxscore
     * 
     * @return double
     */
    public function getmaxscore() {
        return $this->maxscore;
    }
    /**
     * Set Questions
     * 
     * @return str
     */
    public function setquestions() {
        $data = $this->xmldata;
        $result = $data->xpath('//questions');
        $i = 0;
        foreach ($result[0] as $key => $value) {
            $multiplier = 0;
            switch ($key) {
                case 'numeric':
                case 'shortanswer':
                    $multiplier = 1;
                    break;
                case 'multichoice':
                    if ($value->demographic) {
                        $multiplier = 0;
                    } else {
                        $multiplier = strval($value->responselimit);
                    }
                    break;
                case 'priorityranking':
                case 'essay':
                    $multiplier = 0;
                    break;
                default:
                    $multiplier = strval($value->responselimit);
                    break;
            }
            $answer = array();
            $responses = array();
            $keywords = array();
            $matches = array();
            if ($value->answers->answer) {
                foreach ($value->answers as $k => $v) {
                    foreach ($v as $kk => $vv) {
                        $answer[] = array(
                        'guid'  =>  strval($vv->guid),
                        'answertext'  =>  strval($vv->answertext),
                        'valuetype'  =>  strval($vv->valuetype)
                        );
                    }
                }
            }
            if ($value->matches->match) {
                foreach ($value->matches as $ks => $vs) {
                    foreach ($vs as $kks => $vvs) {
                        $matches[] = strval($vvs->answer);
                    }
                }
            }
            if ($value->keywords) {
                foreach ($value->keywords->keyword as $keys => $vals) {
                    $keywords[] = strtolower(strval($vals));
                }
            }
            foreach ($value->responses->response as $ke => $va) {
                $responses[] = array(
                strval($va->deviceid)  =>  strval($va->responsestring)
                );
            }
            $this->questions[$i][$key] = array(
            'correctvalue'  =>  strval($value->correctvalue),
            'incorrectvalue'  =>  strval($value->incorrectvalue),
            'responselimit'  =>  strval($value->responselimit),
            'truefalse'      =>  strval($value->truefalse),
            'demographic'   =>  strval($value->demographic),
            'allowduplicates'  =>  strval($value->allowduplicates),
            'acceptablevalue'   =>  strval($value->acceptablevalue),
            'minvalue'   =>  strval($value->minvalue),
            'maxvalue'   =>  strval($value->maxvalue),
            'numericvaluetype'   =>  strval($value->numericvaluetype),
            'maxpoint'       =>  (float)(($value->correctvalue) * ($multiplier)),
            'answers'         =>     $answer,
            'matches'         =>     $matches,
            'responses'         =>     $responses,
            'keywords'         =>     $keywords
            );
            $this->maxscore += (float)(($value->correctvalue) * ($multiplier));
            $i++;
        }
    }
    /**
     * Remove Bom
     * @param String $str
     * @return str
     */
    public function removeBOM($str="") {
        if (substr($str, 0, 3) == pack("CCC", 0xef, 0xbb, 0xbf)) {
            $str=substr($str, 3);
        }
        return $str;
    }
    /**
     * Get Session Name
     * 
     * @return str
     */
    public function getsessionname() {
        return $this->sessionname;
    }
    /**
     * Set XML data
     * @param mixed $data
     * @return unknown_type
     */
    public function setxmldata($data) {
        try {
            $data = self::removeBOM($data);
            if (strpos($data, '<?xml') !== 0) {
                throw new moodle_exception('invalidxmlresponse');
            }
            $data = str_replace('xmlns=', 'ns=', $data);
            $xml = new SimpleXMLElement($data);
            $this->xmldata = $xml;
            return true;
        } catch (Exception $e) {
            // An error occured while trying to parse the XML, let's just return nothing. SimpleXML does not
            // return a more specific Exception, that's why the global Exception class is caught here.
            return false;
        }
    }
    /**
     * Set Values
     * 
     * @return array
     */
    public function setdevicescore() {
        foreach ($this->questions as $key => $val) {
            foreach ($val as $k => $v) {
                $ans = array();
                switch($k) {
                    case 'multichoice':
                        if (!$v['demographic']) {
                            foreach ($v['answers'] as $kk => $vv) {
                                if ($vv['valuetype'] == 1) {
                                    $ans[] = $kk+1;
                                }
                            }
                            foreach ($v['responses'] as $j => $l) {
                                foreach ($l as $keys => $vals) {
                                    if (in_array($vals, $ans)) {
                                        $this->responders[$keys] += $v['correctvalue'];
                                    } else {
                                        $this->responders[$keys] +=$v['incorrectvalue'];
                                    }
                                }
                            }
                        }
                        break;
                    case 'numeric':
                        foreach ($v['responses'] as $j => $l) {
                            foreach ($l as $keys => $vals) {
                                if (($vals == $v['acceptablevalue']) || ($vals <= $v['maxvalue'] && $vals >= $v['minvalue'])) {
                                    $this->responders[$keys] += $v['correctvalue'];
                                } else {
                                    $this->responders[$keys] +=$v['incorrectvalue'];
                                }
                            }
                        }
                        break;
                    case 'shortanswer':
                        foreach ($v['responses'] as $j => $l) {
                            foreach ($l as $keys => $vals) {
                                if (in_array(strtolower($vals), $v['keywords'])) {
                                    $this->responders[$keys] += $v['correctvalue'];
                                } else {
                                    $this->responders[$keys] +=$v['incorrectvalue'];
                                }
                            }
                        }
                        break;
                    case 'matching':
                        $answertxt = "";
                        foreach ($v['matches'] as $kk => $vv) {
                            foreach ($v['answers'] as $_key => $_value) {
                                if (array_search($vv, $_value)) {
                                    $answertxt.=($_key+1);
                                }
                            }
                        }
                        $strarray = str_split($answertxt);
                        $realcount = count($strarray);
                        foreach ($v['responses'] as $j => $l) {
                            foreach ($l as $keys => $vals) {
                                $strresponse = str_split($vals);
                                $rescount = count(array_intersect_assoc($strarray, $strresponse));
                                $this->responders[$keys] += ($v['correctvalue']*($rescount));
                                $this->responders[$keys] += ($v['incorrectvalue']*($realcount-$rescount));
                            }
                        }
                        break;
                    case 'default':
                        break;
                }
            }
        }
    }

}