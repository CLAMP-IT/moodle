<?php
/**
 * The Turnitin API Communication Class
 *
 * @package turnitintool
 * @subpackage classes
 * @copyright 2010 iParadigms LLC
 */
class turnitintool_commclass {
    /**
     * @var string $apiurl The API Url
     */
    var $apiurl;
    /**
     * @var boolean $encrypt The encrypt parameter for API calls
     */
    var $encrypt;
    /**
     * @var int $accountid The Account ID parameter for API calls
     */
    var $accountid;
    /**
     * @var int $utp The user type parameter for API calls
     */
    var $utp;
    /**
     * @var int $uid The User ID parameter for API calls
     */
    var $uid;
    /**
     * @var string $ufn The User Firstname parameter for API calls
     */
    var $ufn;
    /**
     * @var string $uln The User Lastname parameter for API calls
     */
    var $uln;
    /**
     * @var string $uem The User Email parameter for API calls
     */
    var $uem;
    /**
     * @var string $tiisession Turnitin Session ID parameter for API calls
     */
    var $tiisession;
    /**
     * @var object $loaderbar The Loader Bar Object NULL if no loaderbar is to be displayed
     */
    var $loaderbar;
    /**
     * @var string $result The entires xml result from the API call
     */
    var $result;
    /**
     * @var int $rcode The RCODE returned by the API call
     */
    var $rcode;
    /**
     * @var string $rmessage The RMESSAGE returned by the API call
     */
    var $rmessage;
    /**
     * @var string $curlerror The curl_error returned by the API call if connection issues occur in curl
     */
    var $curlerror;
    /**
     * A backward compatible constructor / destructor method that works in PHP4 to emulate the PHP5 magic method __construct
     */
    function turnitintool_commclass($iUid,$iUfn,$iUln,$iUem,$iUtp,&$iLoaderBar) {
        if (version_compare(PHP_VERSION,"5.0.0","<")) {
            $this->__construct($iUid,$iUfn,$iUln,$iUem,$iUtp,$iLoaderBar);
        }
    }
    /**
     * The constructor for the class, Calls the startsession() method if we are using sessions
     *
     * @param int $iUid The User ID passed in the class creation call
     * @param string $iUfn The User First Name passed in the class creation call
     * @param string $iUln The User Last Name passed in the class creation call
     * @param string $iUem The User Email passed in the class creation call
     * @param int $iUtp The User Type passed in the class creation call
     * @param object $iLoaderBar The Loader Bar object passed in the class creation call (may be NULL if no loaderbar is to be used)
     * @param boolean $iUseSession Determines whether we start a session for this call (set to false for SSO calls)
     */
    function __construct($iUid,$iUfn,$iUln,$iUem,$iUtp,&$iLoaderBar) {
        global $CFG;
        $this->callback=false;
        $this->apiurl=$CFG->turnitin_apiurl;
        $this->accountid=$CFG->turnitin_account_id;
        $this->uid=$iUid;
        
        // Convert the email, firstname and lastname to psuedos for students if the option is set in config
        // Unless the user is already logged as a tutor then use real details
        if ( isset( $CFG->turnitin_enablepseudo ) AND $CFG->turnitin_enablepseudo == 1 AND $iUtp == 1 AND !turnitintool_istutor( $iUem ) ) {
            $iUfn = turnitintool_pseudofirstname();
            $iUln = turnitintool_pseudolastname( $iUem );
            $iUem = turnitintool_pseudoemail( $iUem );
        }
        
        $this->ufn=$iUfn;
        $this->uln=$iUln;
        $this->uem=$iUem;
        $this->utp=$iUtp;
        $this->loaderbar =& $iLoaderBar;
    }
    /**
     * Calls FID1, FCMD 2 with create_session set to 1 in order to create a session for this user / object call
     */
    function startSession() {
        global $CFG;
        $assigndata=array('gmtime'=>$this->tiiGmtime(),
                'encrypt'=>TII_ENCRYPT,
                'aid'=>$this->accountid,
                'diagnostic'=>0,
                'fid'=>1,
                'fcmd'=>2,
                'utp'=>$this->utp,
                'uid'=>$this->uid,
                'uem'=>$this->uem,
                'ufn'=>$this->ufn,
                'uln'=>$this->uln
        );
        $assigndata['dis']=$this->disableEmail();
        $assigndata['md5']=$this->doMD5($assigndata);
        $assigndata['create_session']=1;
        $assigndata['src']=TURNITIN_APISRC;
        $assigndata['apilang']=$this->getLang();
        $this->result=$this->doRequest("POST", $this->apiurl, $assigndata,true);
        $this->tiisession=$this->getSessionid();
        sleep(TII_LATENCY_SLEEP);
    }
    /**
     * Calls FID18, FCMD 2 to kill the session for this user / object call
     */
    function endSession() {
        $assigndata=array('gmtime'=>$this->tiiGmtime(),
                'encrypt'=>TII_ENCRYPT,
                'aid'=>$this->accountid,
                'diagnostic'=>0,
                'fid'=>18,
                'fcmd'=>2,
                'utp'=>$this->utp,
                'uid'=>$this->uid,
                'uem'=>$this->uem,
                'ufn'=>$this->ufn,
                'uln'=>$this->uln
        );
        $assigndata['dis']=$this->disableEmail();
        $assigndata['md5']=$this->doMD5($assigndata);
        $assigndata['session-id']=$this->tiisession;
        $assigndata['src']=TURNITIN_APISRC;
        $assigndata['apilang']=$this->getLang();
        $this->result=$this->doRequest("POST", $this->apiurl, $assigndata,true);
    }
    /**
    * disableEmail - determines whether to use dis=0 or dis=1
    *
    * @param boolean $submission true for a submission call false otherwise
    * @return string 1 or 0
    */
    function disableEmail($submission=false) {
        global $CFG;
        if ( ($this->utp==1 AND $CFG->turnitin_receiptemail!="1" AND $submission) OR  // If student and submission and sends receipts = no
             ($this->utp==1 AND $CFG->turnitin_studentemail!="1" AND !$submission) OR // If student and not submission and student emails = no
             ($this->utp==2 AND $CFG->turnitin_tutoremail!="1")	) {                   // If instructor and instructor emails = no
            return "1";
        } else {
            return "0";
        }
    }
    /**
     * Converts XML string to array (_xmlarray), array keys (_xmlkeys) and array values (_xmlvalues)
     *
     * @param string $string
     * @return boolean
     */
    function xmlToArray($string) {
        $xml_parser = xml_parser_create( 'UTF-8' );
        $xml_struct=xml_parse_into_struct($xml_parser, $string, $this->_xmlvalues, $this->_xmlkeys);
        xml_parser_set_option( $xml_parser, XML_OPTION_CASE_FOLDING, 0 );
        xml_parser_set_option( $xml_parser, XML_OPTION_SKIP_WHITE, 1 );
        xml_parser_free( $xml_parser );

        if ( !$this->_xmlvalues ) {
            $output = array();
        } else {
            $output = array();
            $parents = array();
            $opened_tags = array();
            $arr = array();
            $repeat_index = array();
            $current = & $output;
            foreach ( $this->_xmlvalues as $data ) {
                $result = array();
                $trimval = isset( $data['value'] ) ? trim( $data['value'] ) : null;
                if ( isset( $data['value'] ) AND (!empty( $trimval ) OR $trimval == "0" ) )  {
                    $result['value'] = $data['value'];
                }
                if ( $data['type'] == 'open' ) {
                    $parent[ ( $data['level'] - 1 ) ] = & $current;
                    if ( !is_array( $current ) OR !in_array( $data['tag'], array_keys( $current ) ) ) {
                        $current[ $data['tag'] ] = $result;
                        $repeat_index[ $data['tag'] . '_' . $data['level'] ] = 1;
                        $current = & $current[ $data['tag'] ][0];
                    } else {
                        if ( isset( $current[ $data['tag'] ][0] ) ) {
                            $current[ $data['tag'] ][ $repeat_index[ $data['tag'] . '_' . $data['level'] ] ] = $result;
                            $repeat_index[ $data['tag'] . '_' . $data['level'] ]++;
                        } else {
                            $current[ $data['tag'] ] = array (
                                $current[ $data['tag'] ],
                                $result
                            );
                            $repeat_index[ $data['tag'] . '_' . $data['level'] ] = 2;
                        }
                        $last_index = $repeat_index[ $data['tag'] . '_' . $data['level'] ] - 1;
                        $current = & $current[ $data['tag'] ][ $last_index ];
                    }
                } else if ( $data['type'] == 'complete' ) {
                    if ( !isset( $current[ $data['tag'] ] ) ) {
                        $current[ $data['tag'] ] = $result;
                        $repeat_index[ $data['tag'] . '_' . $data['level'] ] = 1;
                    } else {
                        if ( isset( $current[ $data['tag'] ][0] ) AND is_array( $current[ $data['tag'] ] ) ) {
                            $current[ $data['tag'] ][ $repeat_index[ $data['tag'] . '_' . $data['level'] ] ] = $result;
                            $repeat_index[ $data['tag'] . '_' . $data['level'] ]++;
                        } else {
                            $current[ $data['tag'] ] = array(
                                $current[ $data['tag'] ],
                                $result
                            );
                            $repeat_index[ $data['tag'] . '_' . $data['level'] ] = 1;
                        }
                    }
                } else if ( $data['type'] == 'close' ) {
                    $current = & $parent[ $data['level'] - 1 ];
                }
            }
            $this->_xmlarray = $output;
        }
        return $xml_struct;
    }
    /**
     * Returns a multidimensional array built in the format array[OBJECTID][fieldname]
     *
     * @return array
     */
    function getSubmissionArray() {
        $output=array();

        $xmlcall=$this->xmlToArray($this->result);

        if ( !isset( $this->_xmlarray['RETURNDATA'][0]['OBJECT'] ) ) {
            return $output;
        }

        foreach ( $this->_xmlarray['RETURNDATA'][0]['OBJECT'] as $values ) {

            $objectid = $values['OBJECTID']['value'];

            $output[$objectid]["userid"] = $values['USERID']['value'];
            $output[$objectid]["firstname"]=(isset($values['FIRSTNAME']['value'])) ? $values['FIRSTNAME']['value'] : '';
            $output[$objectid]["lastname"]=(isset($values['LASTNAME']['value'])) ? $values['LASTNAME']['value'] : '';
            $output[$objectid]["title"] = html_entity_decode($values['TITLE']['value'],ENT_QUOTES,"UTF-8");
            $output[$objectid]["similarityscore"]=(isset($values['SIMILARITYSCORE']['value']) AND $values['SIMILARITYSCORE']['value']!="-1") ? $values['SIMILARITYSCORE']['value'] : NULL;

            $output[$objectid]["overlap"]=(isset($values['OVERLAP']['value']) // this is the Originality Percentage Score
                AND $values['OVERLAP']['value']!="-1"
                AND !is_null($output[$objectid]["similarityscore"])) ? $values['OVERLAP']['value'] : NULL;

            $output[$objectid]["grademark"]=(isset($values['SCORE']['value'])
                AND $values['SCORE']['value']!="-1"
                AND !is_null($values['SCORE']['value'])) ? $values['SCORE']['value'] : NULL;
            $output[$objectid]["anon"]=(isset($values['ANON']['value']) AND $values['ANON']['value']!="-1") ? $values['ANON']['value'] : NULL;
            $output[$objectid]["grademarkstatus"]=(isset($values['GRADEMARKSTATUS']['value']) AND $values['GRADEMARKSTATUS']['value']!="-1") ? $values['GRADEMARKSTATUS']['value'] : NULL;
            $output[$objectid]["date_submitted"]=(isset($values['DATE_SUBMITTED']['value']) AND $values['DATE_SUBMITTED']['value']!="-1") ? $values['DATE_SUBMITTED']['value'] : NULL;

            $output[$objectid]["student_view"]=(isset( $values['STUDENT_RESPONSES'][0]['STUDENT_RESPONSE'][0]['RESPONSE_TIME']['value'])) ? $values['STUDENT_RESPONSES'][0]['STUDENT_RESPONSE'][0]['RESPONSE_TIME']['value'] : 0;

        }

        return $output;

    }

    /**
     * Returns the Session ID for the API call
     *
     * @return string The Session ID String or Empty if not available
     */
    function getSessionid() {
        if ($this->xmlToArray($this->result) AND isset($this->_xmlkeys['SESSIONID'][0])) {
            $pos = $this->_xmlkeys['SESSIONID'][0];
            return $this->_xmlvalues[$pos]['value'];
        } else {
            return '';
        }
    }
    /**
     * Returns the Return Message (rmessage) for the API call
     *
     * @return string The RMESSAGE or Empty if not available
     */
    function getRmessage() {
        if ($this->xmlToArray($this->result)) {
            $pos = $this->_xmlkeys['RMESSAGE'][0];
            return $this->_xmlvalues[$pos]['value'];
        } else if (strlen($this->curlerror)>0) {
            return 'CURL ERROR: '.$this->curlerror;
        } else {
            return '';
        }
    }
    /**
     * Returns the User ID for the API call
     *
     * @return string The USERID or Empty if not available
     */
    function getUserID() {
        if ($this->xmlToArray($this->result)) {
            $pos = $this->_xmlkeys['USERID'][0];
            return $this->_xmlvalues[$pos]['value'];
        } else {
            return '';
        }
    }
    /**
     * Returns the Class ID for the API call
     *
     * @return string The CLASSID or Empty if not available
     */
    function getClassID() {
        if ($this->xmlToArray($this->result)) {
            $pos = $this->_xmlkeys['CLASSID'][0];
            return $this->_xmlvalues[$pos]['value'];
        } else {
            return '';
        }
    }
    /**
     * Returns the Return Code (rcode) for the API call
     *
     * @return string The RCODE or NULL if not available
     */
    function getRcode() {
        if ($this->xmlToArray($this->result)) {
            $pos = $this->_xmlkeys['RCODE'][0];
            return $this->_xmlvalues[$pos]['value'];
        } else {
            return NULL;
        }
    }
    /**
     * Returns the Error State for the API call
     *
     * @return boolean True API call success or False API failure
     */
    function getRerror() {
        if (is_null($this->getRcode()) OR $this->getRcode()>=API_ERROR_START) {
            return true;
        } else {
            return false;
        }
    }
    /**
     * Checks the availability of the API
     *
     * @return boolean Returns a true if the API has returned an RCODE or false if unavailable
     */
    function getAPIunavailable() {
        if (is_null($this->getRcode())) {
            return true;
        } else {
            return false;
        }
    }
    /**
     * Returns the Object ID (objectid) for the API call
     *
     * @return string The OBJECTID or Empty String if not available
     */
    function getObjectid() {
        if ($this->xmlToArray($this->result)) {
            $pos = $this->_xmlkeys['OBJECTID'][0];
            return $this->_xmlvalues[$pos]['value'];
        } else {
            return '';
        }
    }
    /**
     * Returns the Assignment ID (ASSIGNMENTID) for the API call
     *
     * @return string The ASSIGNMENTID or Empty String if not available
     */
    function getAssignid() {
        if ($this->xmlToArray($this->result)) {
            $pos = $this->_xmlkeys['ASSIGNMENTID'][0];
            return $this->_xmlvalues[$pos]['value'];
        } else {
            return '';
        }
    }
    /**
     * Returns the Originality Score (ORIGINALITYSCORE) for the API call
     *
     * @return string The ORIGINALITYSCORE or Empty String if not available
     */
    function getScore() {
        if ($this->xmlToArray($this->result) AND isset($this->_xmlkeys['ORIGINALITYSCORE'][0])) {
            $pos = $this->_xmlkeys['ORIGINALITYSCORE'][0];
            return $this->_xmlvalues[$pos]['value'];
        } else {
            return '';
        }
    }
    /**
     * Does a HTTPS Request using cURL and returns the result
     *
     * @param string $method The request method to use POST or GET
     * @param string $url The URL to send the request to
     * @param string $vars A query string style name value pair string (e.g. name=value&name2=value2)
     * @param boolean $timeout Whether to timeout after 240 seconds or not timeout at all
     * @param string $status The status to pass to the loaderbar redraw method
     * @return string The result of the HTTPS call
     */
    function doRequest($method, $url, $vars, $timeout=true, $status="") {
        global $CFG;
        $this->result=NULL;
        if (is_callable(array($this->loaderbar,'redrawbar'))) {
            $this->loaderbar->redrawbar($status);
        }
        if ($timeout) {
            set_time_limit(240);
        } else {
            set_time_limit(0);
        }
        $ch = curl_init();
        $useragent="Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.1) Gecko/20061204 Firefox/2.0.0.1";
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_USERAGENT, $useragent);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_ENCODING, '');
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
        if (isset($CFG->turnitin_proxyurl) AND !empty($CFG->turnitin_proxyurl)) {
            curl_setopt($ch, CURLOPT_PROXY, $CFG->turnitin_proxyurl.':'.$CFG->turnitin_proxyport);
        }
        if (isset($CFG->turnitin_proxyuser) AND !empty($CFG->turnitin_proxyuser)) {
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
            curl_setopt($ch, CURLOPT_PROXYUSERPWD, sprintf('%s:%s', $CFG->turnitin_proxyuser, $CFG->turnitin_proxypassword));
        }
        if ($timeout) {
            curl_setopt($ch, CURLOPT_TIMEOUT, 240);
        }
        if ($method == 'POST') {
            //curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $vars);
        }
        $data = curl_exec($ch);
        if ($data) {
            $result=$data;
        } else {
            $result=curl_error($ch);
            $this->curlerror=$result;
        }
        $this->doLogging($vars,$result);
        return utf8_decode($result);
        curl_close($ch);

    }
    /**
     * Logging function to log outgoing API calls and the return XML
     *
     * @param string $vars The query variables passed to the API
     * @param string $result The Result of the query
     */
    function doLogging($vars,$result) {
        global $CFG;
        if ( $CFG->turnitin_enablediagnostic AND !empty( $vars ) ) {
            $this->result=$result;
            // ###### DELETE SURPLUS LOGS #########
            $numkeeps=10;
            $prefix="commslog_";
            $dirpath=$CFG->dataroot."/temp/turnitintool/logs";
            if (!file_exists($dirpath)) {
                mkdir($dirpath,0777,true);
            }
            $dir=opendir($dirpath);
            $files=array();
            while ($entry=readdir($dir)) {
                if (substr(basename($entry),0,1)!="." AND substr_count(basename($entry),$prefix)>0) {
                    $files[]=basename($entry);
                }
            }
            sort($files);
            for ($i=0;$i<count($files)-$numkeeps;$i++) {
                unlink($dirpath."/".$files[$i]);
            }
            // ####################################
            $newline = "\r\n";
            $filepath=$dirpath."/".$prefix.date('Ymd',time()).".log";
            $file=fopen($filepath,'ab');
            $fid = isset($vars["fid"]) ? $vars["fid"] : "N/A";
            $fcmd = isset($vars["fcmd"]) ? $vars["fcmd"] : "N/A";
            $output="== FID:".$fid." | FCMD:".$fcmd." ===========================================================".$newline;
            if ($this->getRcode() > 100 OR is_null($this->getRcode()) ) {
                $output.="== SUCCESS ===================================================================".$newline;
            } else if ($fid!="N/A") {
                $output.="== ERROR =====================================================================".$newline;
            }
            $output.="CALL DATE TIME: ".date('r',time()).$newline;
            $output.="URL: ".$this->apiurl.$newline;
            $output.="------------------------------------------------------------------------------".$newline;
            $output.="REQUEST VARS: ".PHP_EOL.str_replace("\n","\r\n",str_replace("\r\r\n","\r\n",print_r($vars,true))).$newline;
            $output.="------------------------------------------------------------------------------".$newline;
            $output.="RESPONSE: ".PHP_EOL.PHP_EOL.str_replace("\n","\r\n",str_replace("\r\r\n","\r\n",$result)).$newline;
            $output.="##############################################################################".$newline.$newline;
            fwrite($file,$output);
            fclose($file);
        }
    }
    /**
     * Call to API FID4, FCMD6 that deletes an assignment from Turnitin
     *
     * @param object $post The post object that contains the necessary query parameters for the call
     * @param string $status The status to pass to the loaderbar class
     */
    function deleteAssignment($post,$status) {
        global $CFG;

        if (!turnitintool_check_config()) {
            turnitintool_print_error('configureerror','turnitintool',NULL,NULL,__FILE__,__LINE__);
            exit();
        }

        $assigndata=array('gmtime'=>$this->tiiGmtime(),
                'encrypt'=>TII_ENCRYPT,
                'aid'=>$this->accountid,
                'diagnostic'=>0,
                'fcmd'=>6,
                'cid'=>$post->cid,
                'ctl'=>stripslashes($post->ctl),
                'assignid'=>$post->assignid,
                'assign'=>stripslashes($post->name),
                'utp'=>2,
                'fid'=>4,
                'uid'=>$this->uid,
                'uem'=>$this->uem,
                'ufn'=>$this->ufn,
                'uln'=>$this->uln
        );
        $assigndata['dis']=$this->disableEmail();
        $assigndata["md5"]=$this->doMD5($assigndata);
        $assigndata['session-id']=$this->tiisession;
        $assigndata['src']=TURNITIN_APISRC;
        $assigndata['apilang']=$this->getLang();
        $this->result=$this->doRequest("POST", $this->apiurl, $assigndata,true,$status);
    }
    /**
     * Call to API FID4, FCMD2/FCMD3 that creates/updates an assignment in Turnitin
     *
     * @param object $post The post object that contains the necessary query parameters for the call
     * @param string $do The call type 'INSERT' or 'UPDATE'
     * @param string $status The status to pass to the loaderbar class
     */
    function createAssignment($post,$do='INSERT',$status) {

        if (!turnitintool_check_config()) {
            turnitintool_print_error('configureerror','turnitintool',NULL,NULL,__FILE__,__LINE__);
            exit();
        }

        if ($do!='INSERT') {
            $thisfcmd=3;
            $userid=$this->uid;
        } else {
            $thisfcmd=2;
            $userid='';
        }

        $assigndata=array('gmtime'=>$this->tiiGmtime(),
                'encrypt'=>TII_ENCRYPT,
                'aid'=>$this->accountid,
                'diagnostic'=>0,
                'fcmd'=>$thisfcmd,
                'cid'=>$post->cid,
                'ctl'=>stripslashes($post->ctl),
                'assignid'=>$post->assignid,
                'utp'=>2,
                'dtstart'=>userdate($post->dtstart,'%Y-%m-%d %H:%M:%S',20,false), // Default Moodle Timezone
                'dtdue'=>userdate($post->dtdue,'%Y-%m-%d %H:%M:%S',20,false), // Default Moodle Timezone
                'dtpost'=>userdate($post->dtpost,'%Y-%m-%d %H:%M:%S',20,false), // Default Moodle Timezone
                'fid'=>4,
                'uid'=>$userid,
                'uem'=>$this->uem,
                'ufn'=>$this->ufn,
                'uln'=>$this->uln
        );
        if ($do!='INSERT') {
            $assigndata['newassign']=stripslashes($post->name);
            $assigndata['assign']=stripslashes($post->currentassign);
        } else {
            $assigndata['assign']=stripslashes($post->name);
        }
        $assigndata['dis']=$this->disableEmail();
        $assigndata["md5"]=$this->doMD5($assigndata);
        $assigndata['session-id']=$this->tiisession;
        $assigndata["s_view_report"]=$post->s_view_report;
        if (isset($post->max_points)) {
            $assigndata["max_points"]=$post->max_points;
        }
        if (isset($post->report_gen_speed)) {
            $assigndata["report_gen_speed"]=$post->report_gen_speed;
        }
        if (isset($post->anon)) {
            $assigndata["anon"]=$post->anon;
        }
        if (isset($post->late_accept_flag)) {
            $assigndata["late_accept_flag"]=$post->late_accept_flag;
        }
        if (isset($post->submit_papers_to)) {
            $assigndata["submit_papers_to"]=$post->submit_papers_to;
        }
        if (isset($post->s_paper_check)) {
            $assigndata["s_paper_check"]=$post->s_paper_check;
        }
        if (isset($post->internet_check)) {
            $assigndata["internet_check"]=$post->internet_check;
        }
        if (isset($post->journal_check)) {
            $assigndata["journal_check"]=$post->journal_check;
        }
        // Add Exclude small matches, biblio, quoted etc 20100920
        if (isset($post->exclude_biblio)) {
            $assigndata["exclude_biblio"]=$post->exclude_biblio;
        }
        if (isset($post->exclude_quoted)) {
            $assigndata["exclude_quoted"]=$post->exclude_quoted;
        }
        if (isset($post->exclude_value)) {
            $assigndata["exclude_value"]=$post->exclude_value;
        }
        if (isset($post->exclude_type) AND $assigndata["exclude_value"]==0) {
            $assigndata["exclude_type"]=0;
        } else if (isset($post->exclude_type)) {
            $assigndata["exclude_type"]=$post->exclude_type;
        }
        if (isset($post->erater) AND $post->erater != 0) {
            $assigndata["erater"]=$post->erater;
            $assigndata["ets_handbook"]=$post->erater_handbook;
            $assigndata["ets_dictionary"]=$post->erater_dictionary;
            $assigndata["ets_spelling"]=$post->erater_spelling;
            $assigndata["ets_grammar"]=$post->erater_grammar;
            $assigndata["ets_usage"]=$post->erater_usage;
            $assigndata["ets_mechanics"]=$post->erater_mechanics;
            $assigndata["ets_style"]=$post->erater_style;
        }
        if (isset($post->idsync)) {
            $assigndata['idsync']=$post->idsync;
        }
        $assigndata['src']=TURNITIN_APISRC;
        $assigndata['apilang']=$this->getLang();
        $this->result=$this->doRequest("POST", $this->apiurl, $assigndata,true,$status);
    }
    /**
     * Call to API FID2, FCMD4 that changes the owner tutor for a Turnitin class
     *
     * @param object $post The post object that contains the necessary query parameters for the call
     * @param string $status The status to pass to the loaderbar class
     */
    function changeOwner($post,$status) {

        $assigndata=array('gmtime'=>$this->tiiGmtime(),
                'encrypt'=>TII_ENCRYPT,
                'aid'=>$this->accountid,
                'diagnostic'=>0,
                'fcmd'=>4,
                'cid'=>$post->cid,
                'ctl'=>stripslashes($post->ctl),
                'tem'=>$this->uem,
                'utp'=>2,
                'fid'=>2,
                'uid'=>$this->uid,
                'uem'=>$this->uem,
                'ufn'=>$this->ufn,
                'uln'=>$this->uln
        );
        $assigndata['dis']=$this->disableEmail();
        $assigndata['md5']=$this->doMD5($assigndata);
        $assigndata['session-id']=$this->tiisession;
        $assigndata['src']=TURNITIN_APISRC;
        $assigndata['apilang']=$this->getLang();
        $assigndata['new_teacher_email']=$post->new_teacher_email;

        $this->result=$this->doRequest("POST", $this->apiurl, $assigndata,true,$status);
    }
    /**
     * Call to API FID5, FCMD2 that submits a paper to Turnitin
     *
     * @param object $post The post object that contains the necessary query parameters for the call
     * @param string $filedata The filepath / filedata of the file to upload
     * @param string $status The status to pass to the loaderbar class
     */
    function submitPaper($post,$filedata,$status) {

        $assigndata=array('gmtime'=>$this->tiiGmtime(),
                'encrypt'=>TII_ENCRYPT,
                'aid'=>$this->accountid,
                'diagnostic'=>0,
                'fcmd'=>2,
                'cid'=>$post->cid,
                'ctl'=>stripslashes($post->ctl),
                'assignid'=>$post->assignid,
                'assign'=>stripslashes($post->assignname),
                'tem'=>$post->tem,
                'ptype'=>2,
                'ptl'=>stripslashes(turnitintool_fix_quote($post->papertitle)),
                'utp'=>1,
                'fid'=>5,
                'uid'=>$this->uid,
                'uem'=>$this->uem,
                'ufn'=>$this->ufn,
                'uln'=>$this->uln,
                'oid'=>$post->oid
        );
        $assigndata['dis']=$this->disableEmail(true);
        $assigndata['md5']=$this->doMD5($assigndata);
        $assigndata['session-id']=$this->tiisession;
        $assigndata['src']=TURNITIN_APISRC;
        $assigndata['apilang']=$this->getLang();
        $assigndata['pdata']='@'.$filedata;

        $this->result=$this->doRequest("POST", $this->apiurl, $assigndata,false,$status);
    }
    /**
     * Call to API FID4, FCMD7 that queries the settings for the Turnitin assignment
     *
     * @param object $post The post object that contains the necessary query parameters for the call
     * @param string $status The status to pass to the loaderbar class
     */
    function queryAssignment($post,$status) {

        $assigndata=array('gmtime'=>$this->tiiGmtime(),
                'encrypt'=>TII_ENCRYPT,
                'aid'=>$this->accountid,
                'diagnostic'=>0,
                'fcmd'=>7,
                'cid'=>$post->cid,
                'ctl'=>stripslashes($post->ctl),
                'assignid'=>$post->assignid,
                'assign'=>stripslashes($post->assign),
                'utp'=>2,
                'fid'=>4,
                'uid'=>$this->uid,
                'uem'=>$this->uem,
                'ufn'=>$this->ufn,
                'uln'=>$this->uln
        );
        $assigndata['dis']=$this->disableEmail();
        $assigndata['md5']=$this->doMD5($assigndata);
        $assigndata['session-id']=$this->tiisession;
        $assigndata['src']=TURNITIN_APISRC;
        $assigndata['apilang']=$this->getLang();
        $this->result=$this->doRequest("POST", $this->apiurl, $assigndata,true,$status);
    }
    /**
     * Converts the Turnitin Assignment settings to an object in the correct format for a create/update assignment call
     *
     * @param object $post The post object that contains the necessary query parameters for the call
     * @param string $status The status to pass to the loaderbar class
     */
    function getAssignmentObject() {
        $output=new object();
        $xmlcall=$this->xmlToArray($this->result);
        if (isset($this->_xmlkeys['ASSIGN']) AND is_array($this->_xmlkeys['ASSIGN'])) {
            for ($i=0;$i<count($this->_xmlkeys['ASSIGN']);$i++) {

                $pos1 = $this->_xmlkeys['ASSIGN'][$i];
                $pos2 = $this->_xmlkeys['DTSTART'][$i];
                $pos3 = $this->_xmlkeys['DTDUE'][$i];
                $pos4 = $this->_xmlkeys['DTPOST'][$i];
                $pos5 = $this->_xmlkeys['AINST'][$i];
                $pos6 = $this->_xmlkeys['GENERATE'][$i];
                $pos7 = $this->_xmlkeys['SVIEWREPORTS'][$i];
                $pos8 = $this->_xmlkeys['LATESUBMISSIONS'][$i];
                $pos9 = $this->_xmlkeys['REPOSITORY'][$i];
                $pos10 = $this->_xmlkeys['SEARCHPAPERS'][$i];
                $pos11 = $this->_xmlkeys['SEARCHINTERNET'][$i];
                $pos12 = $this->_xmlkeys['SEARCHJOURNALS'][$i];
                $pos13 = $this->_xmlkeys['ANON'][$i];
                $pos14 = $this->_xmlkeys['MAXPOINTS'][$i];

                $output->assign = $this->_xmlvalues[$pos1]['value'];
                $output->dtstart = strtotime($this->_xmlvalues[$pos2]['value']);
                $output->dtdue = strtotime($this->_xmlvalues[$pos3]['value']);
                $output->dtpost = strtotime($this->_xmlvalues[$pos4]['value']);
                if (isset($this->_xmlvalues[$pos5]['value'])) {
                    $output->ainst = $this->_xmlvalues[$pos5]['value'];
                } else {
                    $output->ainst = NULL;
                }
                $output->report_gen_speed = $this->_xmlvalues[$pos6]['value'];
                $output->s_view_report = $this->_xmlvalues[$pos7]['value'];
                $output->late_accept_flag = $this->_xmlvalues[$pos8]['value'];
                $output->submit_papers_to = $this->_xmlvalues[$pos9]['value'];
                $output->s_paper_check = $this->_xmlvalues[$pos10]['value'];
                $output->internet_check = $this->_xmlvalues[$pos11]['value'];
                $output->journal_check = $this->_xmlvalues[$pos12]['value'];
                $output->anon = $this->_xmlvalues[$pos13]['value'];
                $output->maxpoints = $this->_xmlvalues[$pos14]['value'];

            }
            return $output;
        } else {
            return $output;
        }
    }
    /**
     * Call to API FID3, FCMD2 to join the user to a Turnitin Class
     *
     * @param object $post The post object that contains the necessary query parameters for the call
     * @param string $status The status to pass to the loaderbar class
     */
    function joinClass($post,$status) {

        $assigndata=array('gmtime'=>$this->tiiGmtime(),
                'encrypt'=>TII_ENCRYPT,
                'aid'=>$this->accountid,
                'diagnostic'=>0,
                'fcmd'=>2,
                'cid'=>$post->cid,
                'ctl'=>stripslashes($post->ctl),
                'tem'=>stripslashes($post->tem),
                'utp'=>1,
                'fid'=>3,
                'uid'=>$this->uid,
                'uem'=>$this->uem,
                'ufn'=>$this->ufn,
                'uln'=>$this->uln
        );
        $assigndata['dis']=$this->disableEmail();
        $assigndata['md5']=$this->doMD5($assigndata);
        $assigndata['session-id']=$this->tiisession;
        $assigndata['src']=TURNITIN_APISRC;
        $assigndata['apilang']=$this->getLang();
        $this->result=$this->doRequest("POST", $this->apiurl, $assigndata,true,$status);
    }
    /**
     * Call to API FID1, FCMD2 to create a user on the Turnitin System
     *
     * @param object $post The post object that contains the necessary query parameters for the call
     * @param string $status The status to pass to the loaderbar class
     */
    function createUser($post,$status) {
        $assigndata=array('gmtime'=>$this->tiiGmtime(),
                'encrypt'=>TII_ENCRYPT,
                'aid'=>$this->accountid,
                'diagnostic'=>0,
                'fcmd'=>2,
                'utp'=>$this->utp,
                'fid'=>1,
                'uid'=>$this->uid,
                'uem'=>$this->uem,
                'ufn'=>$this->ufn,
                'uln'=>$this->uln
        );
        $assigndata['dis']=$this->disableEmail();
        $assigndata['md5']=$this->doMD5($assigndata);
        $assigndata['session-id']=$this->tiisession;
        if (isset($post->idsync)) {
            $assigndata['idsync']=$post->idsync;
        }
        $assigndata['src']=TURNITIN_APISRC;
        $assigndata['apilang']=$this->getLang();
        $this->result=$this->doRequest("POST", $this->apiurl, $assigndata,true,$status);
    }
    /**
     * Call to API FID1, FCMD2 to create a class on the Turnitin System
     *
     * @param object $post The post object that contains the necessary query parameters for the call
     * @param string $status The status to pass to the loaderbar class
     */
    function createClass($post,$status,$type="INSERT") {

        if (!isset($post->cid)) {
            $post->cid="";
            $userid="";
        } else {
            $userid=$this->uid;
        }
        $ced = isset( $post->ced ) ? $post->ced : null;
        if ( $type == "INSERT" ) {
            $fcmd = 2;
        } else {
            $fcmd = 3;
        }

        $assigndata=array('gmtime'=>$this->tiiGmtime(),
                'encrypt'=>TII_ENCRYPT,
                'aid'=>$this->accountid,
                'diagnostic'=>0,
                'fcmd'=>$fcmd,
                'utp'=>2,
                'fid'=>2,
                'cid'=>$post->cid,
                'ctl'=>stripslashes($post->ctl),
                'uid'=>$userid,
                'uem'=>$this->uem,
                'ced'=>$ced,
                'ufn'=>$this->ufn,
                'uln'=>$this->uln
        );
        $assigndata['dis']=$this->disableEmail();
        $assigndata['md5']=$this->doMD5($assigndata);
        $assigndata['session-id']=$this->tiisession;
        if (isset($post->idsync)) {
            $assigndata['idsync']=$post->idsync;
        }
        $assigndata['src']=TURNITIN_APISRC;
        $assigndata['apilang']=$this->getLang();
        $this->result=$this->doRequest("POST", $this->apiurl, $assigndata,true,$status);
    }
    /**
     * Call to API FID6, FCMD2 to get the Originality Report score
     *
     * @param object $post The post object that contains the necessary query parameters for the call
     * @param string $status The status to pass to the loaderbar class
     */
    function getReportScore($post,$status) {

        if (!turnitintool_check_config()) {
            turnitintool_print_error('configureerror','turnitintool',NULL,NULL,__FILE__,__LINE__);
            exit();
        }

        $assigndata=array('gmtime'=>$this->tiiGmtime(),
                'encrypt'=>TII_ENCRYPT,
                'aid'=>$this->accountid,
                'diagnostic'=>0,
                'fcmd'=>2,
                'oid'=>$post->paperid,
                'utp'=>$post->utp,
                'fid'=>6,
                'uid'=>$this->uid,
                'uem'=>$this->uem,
                'ufn'=>$this->ufn,
                'uln'=>$this->uln
        );
        $assigndata['dis']=$this->disableEmail();
        $assigndata["md5"]=$this->doMD5($assigndata);
        $assigndata['session-id']=$this->tiisession;
        $assigndata['src']=TURNITIN_APISRC;
        $assigndata['apilang']=$this->getLang();
        $this->result=$this->doRequest("POST", $this->apiurl, $assigndata,true,$status);
    }
    /**
     * Call to API FID10, FCMD2 to list the Submissions for an Assignment
     *
     * @param object $post The post object that contains the necessary query parameters for the call
     * @param string $status The status to pass to the loaderbar class
     */
    function listSubmissions($post,$status) {

        if (!turnitintool_check_config()) {
            turnitintool_print_error('configureerror','turnitintool',NULL,NULL,__FILE__,__LINE__);
            exit();
        }

        $fcmd = (isset($post->fcmd) AND $post->fcmd==4) ? 4 : 2;
        $tem = (isset($post->tem)) ? $post->tem : '';

        $assigndata=array('gmtime'=>$this->tiiGmtime(),
                'encrypt'=>TII_ENCRYPT,
                'aid'=>$this->accountid,
                'diagnostic'=>0,
                'fcmd'=>$fcmd,
                'assignid'=>$post->assignid,
                'assign'=>$post->assign,
                'cid'=>$post->cid,
                'ctl'=>$post->ctl,
                'tem'=>$tem,
                'utp'=>$this->utp,
                'fid'=>10,
                'uid'=>$this->uid,
                'uem'=>$this->uem,
                'ufn'=>$this->ufn,
                'uln'=>$this->uln
        );
        $assigndata['dis']=$this->disableEmail();
        $assigndata["md5"]=$this->doMD5($assigndata);
        $assigndata['session-id']=$this->tiisession;
        $assigndata['src']=TURNITIN_APISRC;
        $assigndata['apilang']=$this->getLang();
        $this->result=$this->doRequest("POST", $this->apiurl, $assigndata,true,$status);
    }
    /**
     * Call to API FID10, FCMD2 to list the Enrolled users
     *
     * @param object $post The post object that contains the necessary query parameters for the call
     * @param string $status The status to pass to the loaderbar class
     */
    function listEnrollment($post,$status) {

        if (!turnitintool_check_config()) {
            turnitintool_print_error('configureerror','turnitintool','',NULL,__FILE__,__LINE__);
            exit();
        }

        $assigndata=array('gmtime'=>$this->tiiGmtime(),
                'encrypt'=>TII_ENCRYPT,
                'aid'=>$this->accountid,
                'diagnostic'=>0,
                'fcmd'=>5,
                'cid'=>$post->cid,
                'ctl'=>$post->ctl,
                'utp'=>$this->utp,
                'fid'=>19,
                'uid'=>$this->uid,
                'uem'=>$this->uem,
                'ufn'=>$this->ufn,
                'uln'=>$this->uln
        );
        $assigndata['dis']=$this->disableEmail();
        $assigndata["md5"]=$this->doMD5($assigndata);
        $assigndata['session-id']=$this->tiisession;
        $assigndata['src']=TURNITIN_APISRC;
        $assigndata['apilang']=$this->getLang();
        $this->result=$this->doRequest("POST", $this->apiurl, $assigndata,true,$status);
    }
    /**
     * Call to API FID19, FCMD2 to unenrol user from class
     *
     * @param object $post The post object that contains the necessary query parameters for the call
     * @param string $status The status to pass to the loaderbar class
     */
    function unenrolUser($post,$status) {

        if (!turnitintool_check_config()) {
            turnitintool_print_error('configureerror','turnitintool','',NULL,__FILE__,__LINE__);
            exit();
        }

        $assigndata=array('gmtime'=>$this->tiiGmtime(),
                'encrypt'=>TII_ENCRYPT,
                'aid'=>$this->accountid,
                'diagnostic'=>0,
                'fcmd'=>2,
                'cid'=>$post->cid,
                'ctl'=>$post->ctl,
                'utp'=>$this->utp,
                'fid'=>19,
                'uid'=>$this->uid,
                'uem'=>$this->uem,
                'ufn'=>$this->ufn,
                'uln'=>$this->uln
        );
        $assigndata['dis']=$this->disableEmail();
        $assigndata["md5"]=$this->doMD5($assigndata);
        $assigndata['session-id']=$this->tiisession;
        $assigndata['src']=TURNITIN_APISRC;
        $assigndata['apilang']=$this->getLang();
        $this->result=$this->doRequest("POST", $this->apiurl, $assigndata,true,$status);
    }
    /**
     * Call to API FID2, FCMD2 to update the class having the effect of enrolling the tutor to that class
     *
     * @param object $post The post object that contains the necessary query parameters for the call
     * @param string $status The status to pass to the loaderbar class
     */
    function enrolTutor($post,$status) {

        if (!turnitintool_check_config()) {
            turnitintool_print_error('configureerror','turnitintool','',NULL,__FILE__,__LINE__);
            exit();
        }

        $assigndata=array('gmtime'=>$this->tiiGmtime(),
                'encrypt'=>TII_ENCRYPT,
                'aid'=>$this->accountid,
                'diagnostic'=>0,
                'fcmd'=>2,
                'cid'=>$post->cid,
                'ctl'=>$post->ctl,
                'utp'=>$this->utp,
                'fid'=>2,
                'uid'=>$this->uid,
                'uem'=>$this->uem,
                'ufn'=>$this->ufn,
                'uln'=>$this->uln
        );
        $assigndata['dis']=$this->disableEmail();
        $assigndata["md5"]=$this->doMD5($assigndata);
        $assigndata['session-id']=$this->tiisession;
        $assigndata['src']=TURNITIN_APISRC;
        $assigndata['apilang']=$this->getLang();
        $this->result=$this->doRequest("POST", $this->apiurl, $assigndata,true,$status);
    }
     /**
     * Output an array of tutors from a listEnrollment call
     *
     * @param object $post The post object that contains the necessary query
     * parameters for the listEnrollment call
     * @param string $status The status to pass to the loaderbar class
     * return array Returns an array of tutors with key of Turnitin UID
     */
    function getTutors($post,$status) {
        $this->listEnrollment($post,$status);
        $result=null;
        $xmlcall=$this->xmlToArray($this->result);
        if (isset($this->_xmlkeys['INSTRUCTOR']) AND is_array($this->_xmlkeys['INSTRUCTOR'])) {
            $n=0;
            for ($i=0;$i<count($this->_xmlkeys['INSTRUCTOR']);$i++) {
                $pos=$this->_xmlkeys['INSTRUCTOR'][$i]+1;
                $key=(isset($this->_xmlvalues[$pos]['tag'])) ? strtolower($this->_xmlvalues[$pos]['tag']) : null;;
                $value=(isset($this->_xmlvalues[$pos]['value'])) ? $this->_xmlvalues[$pos]['value'] : null;
                $level=(isset($this->_xmlvalues[$pos]['level'])) ? $this->_xmlvalues[$pos]['level'] : null;
                if ($level==4) {
                    $result[$n][$key]=$value;
                } else {
                    $n++;
                    continue;
                }
            }
        }
        return $result;
    }
     /**
     * Output an array of students from a listEnrollment call
     *
     * @param object $post The post object that contains the necessary query
     * parameters for the listEnrollment call
     * @param string $status The status to pass to the loaderbar class
     * return array Returns an array of tutors with key of Turnitin UID
     */
    function getStudents($post,$status) {
        $this->listEnrollment($post,$status);
        $result=null;
        $xmlcall=$this->xmlToArray($this->result);
        if (isset($this->_xmlkeys['STUDENT']) AND is_array($this->_xmlkeys['STUDENT'])) {
            $n=0;
            for ($i=0;$i<count($this->_xmlkeys['STUDENT']);$i++) {
                $pos=$this->_xmlkeys['STUDENT'][$i]+1;
                $key=strtolower($this->_xmlvalues[$pos]['tag']);
                $value=$this->_xmlvalues[$pos]['value'];
                $level=$this->_xmlvalues[$pos]['level'];
                if ($level==4) {
                    $result[$n][$key]=$value;
                } else {
                    $n++;
                    continue;
                }
            }
        }
        return $result;
    }
    /**
     * Call to API FID6, FCMD1 to Single Sign On to Turnitin's Originality Report for the submission
     *
     * @param object $post The post object that contains the necessary query parameters for the call
     * @return string Returns the URL to access the Originality Report
     */
    function getReportLink($post) {

        $assigndata=array('gmtime'=>$this->tiiGmtime(),
                'encrypt'=>TII_ENCRYPT,
                'aid'=>$this->accountid,
                'diagnostic'=>0,
                'fcmd'=>1,
                'oid'=>$post->paperid,
                'utp'=>$this->utp,
                'fid'=>6,
                'uid'=>$this->uid,
                'uem'=>$this->uem,
                'ufn'=>$this->ufn,
                'uln'=>$this->uln
        );
        $assigndata['md5']=$this->doMD5($assigndata);
        $assigndata['src']=TURNITIN_APISRC;
        $assigndata['apilang']=$this->getLang();

        $keys = array_keys($assigndata);
        $values = array_values($assigndata);
        $querystring='';
        for ($i=0;$i<count($values); $i++) {
            if ($i!=0) {
                $querystring .= '&';
            }
            $querystring .= $keys[$i].'='.urlencode($values[$i]);
        }
        return $this->apiurl."?".$querystring;
    }
    /**
     * Call to API FID13, FCMD1 to Single Sign On to Turnitin's GradeMark for the submission
     *
     * @param object $post The post object that contains the necessary query parameters for the call
     * @return string Returns the URL to access the GradeMark
     */
    function getGradeMarkLink($post) {
        $assigndata=array('gmtime'=>$this->tiiGmtime(),
                'encrypt'=>TII_ENCRYPT,
                'aid'=>$this->accountid,
                'diagnostic'=>0,
                'fcmd'=>1,
                'oid'=>$post->paperid,
                'utp'=>$this->utp,
                'fid'=>13,
                'uid'=>$this->uid,
                'uem'=>$this->uem,
                'ufn'=>$this->ufn,
                'uln'=>$this->uln
        );
        $assigndata['md5']=$this->doMD5($assigndata);
        $assigndata['src']=TURNITIN_APISRC;
        $assigndata['apilang']=$this->getLang();

        $keys = array_keys($assigndata);
        $values = array_values($assigndata);
        $querystring='';
        for ($i=0;$i<count($values); $i++) {
            if ($i!=0) {
                $querystring .= '&';
            }
            $querystring .= $keys[$i].'='.urlencode($values[$i]);
        }

        return $this->apiurl."?".$querystring;
    }
    /**
     * Call to API FID7, FCMD1 to Single Sign On to Turnitin's Submission View for the submission
     *
     * @param object $post The post object that contains the necessary query parameters for the call
     * @return string Returns the URL to access the Submission View
     */
    function getSubmissionURL($post) {
        $assigndata=array('gmtime'=>$this->tiiGmtime(),
                'encrypt'=>TII_ENCRYPT,
                'aid'=>$this->accountid,
                'diagnostic'=>0,
                'fcmd'=>1,
                'oid'=>$post->paperid,
                'utp'=>$this->utp,
                'fid'=>7,
                'assignid'=>$post->assignid,
                'assign'=>$post->assign,
                'ctl'=>$post->ctl,
                'cid'=>$post->cid,
                'uid'=>$this->uid,
                'tem'=>$post->tem,
                'uem'=>$this->uem,
                'ufn'=>$this->ufn,
                'uln'=>$this->uln
        );
        $assigndata['md5']=$this->doMD5($assigndata);
        $assigndata['src']=TURNITIN_APISRC;
        $assigndata['apilang']=$this->getLang();

        $keys = array_keys($assigndata);
        $values = array_values($assigndata);
        $querystring='';
        for ($i=0;$i<count($values); $i++) {
            if ($i!=0) {
                $querystring .= '&';
            }
            $querystring .= $keys[$i].'='.urlencode($values[$i]);
        }
        return $this->apiurl."?".$querystring;
    }
    /**
     * Call to API FID7, FCMD2 to download the original paper of the submission
     *
     * @param object $post The post object that contains the necessary query parameters for the call
     * @return string Returns the URL to access the Download Link
     */
    function getSubmissionDownload($post) {
        $assigndata=array('gmtime'=>$this->tiiGmtime(),
                'encrypt'=>TII_ENCRYPT,
                'aid'=>$this->accountid,
                'diagnostic'=>0,
                'fcmd'=>2,
                'oid'=>$post->paperid,
                'utp'=>$this->utp,
                'fid'=>7,
                'assignid'=>$post->assignid,
                'assign'=>$post->assign,
                'ctl'=>$post->ctl,
                'cid'=>$post->cid,
                'uid'=>$this->uid,
                'tem'=>$post->tem,
                'uem'=>$this->uem,
                'ufn'=>$this->ufn,
                'uln'=>$this->uln
        );
        $assigndata['md5']=$this->doMD5($assigndata);
        $assigndata['src']=TURNITIN_APISRC;
        $assigndata['apilang']=$this->getLang();

        $keys = array_keys($assigndata);
        $values = array_values($assigndata);
        $querystring='';
        for ($i=0;$i<count($values); $i++) {
            if ($i!=0) {
                $querystring .= '&';
            }
            $querystring .= $keys[$i].'='.urlencode($values[$i]);
        }
        return $this->apiurl."?".$querystring;
    }
    /**
     * Call to API FID15, FCMD3 to set the GradeMark grade for a particular submission
     *
     * @param object $post The post object that contains the necessary query parameters for the call
     * @param string $status The status to pass to the loaderbar class
     */
    function setGradeMark($post,$status) {

        if (!turnitintool_check_config()) {
            turnitintool_print_error('configureerror','turnitintool',NULL,NULL,__FILE__,__LINE__);
            exit();
        }

        $assigndata=array('gmtime'=>$this->tiiGmtime(),
                'encrypt'=>TII_ENCRYPT,
                'aid'=>$this->accountid,
                'diagnostic'=>0,
                'fcmd'=>3,
                'cid'=>$post->cid,
                'oid'=>$post->oid,
                'utp'=>2,
                'fid'=>15,
                'uid'=>$this->uid,
                'uem'=>$this->uem,
                'ufn'=>$this->ufn,
                'uln'=>$this->uln
        );
        $assigndata['dis']=$this->disableEmail();
        $assigndata["md5"]=$this->doMD5($assigndata);
        $assigndata['session-id']=$this->tiisession;
        $assigndata["score"]=$post->score;
        $assigndata['src']=TURNITIN_APISRC;
        $assigndata['apilang']=$this->getLang();
        $this->result=$this->doRequest("POST", $this->apiurl, $assigndata,true,$status);
    }
    /**
     * Call to API FID8, FCMD2 to delete a submission
     *
     * @param object $post The post object that contains the necessary query parameters for the call
     * @param string $status The status to pass to the loaderbar class
     */
    function deleteSubmission($post,$status) {

        $assigndata=array('gmtime'=>$this->tiiGmtime(),
                'encrypt'=>TII_ENCRYPT,
                'aid'=>$this->accountid,
                'diagnostic'=>0,
                'fcmd'=>2,
                'oid'=>$post->paperid,
                'utp'=>$this->utp,
                'fid'=>8,
                'uid'=>$this->uid,
                'uem'=>$this->uem,
                'ufn'=>$this->ufn,
                'uln'=>$this->uln
        );
        $assigndata['dis']=$this->disableEmail();
        $assigndata['md5']=$this->doMD5($assigndata);
        $assigndata['session-id']=$this->tiisession;
        $assigndata['src']=TURNITIN_APISRC;
        $assigndata['apilang']=$this->getLang();
        $this->result=$this->doRequest("POST", $this->apiurl, $assigndata,true,$status);
    }
    /**
     * Call to API FID16, FCMD3 to reveal the name of a student when anonymous marking is switched on
     *
     * @param object $post The post object that contains the necessary query parameters for the call
     * @param string $status The status to pass to the loaderbar class
     */
    function revealAnon($post,$status) {

        $assigndata=array('gmtime'=>$this->tiiGmtime(),
                'encrypt'=>TII_ENCRYPT,
                'aid'=>$this->accountid,
                'diagnostic'=>0,
                'fcmd'=>3,
                'oid'=>$post->paperid,
                'utp'=>$this->utp,
                'fid'=>16,
                'uid'=>$this->uid,
                'uem'=>$this->uem,
                'ufn'=>$this->ufn,
                'uln'=>$this->uln
        );
        $assigndata['dis']=$this->disableEmail();
        $assigndata['md5']=$this->doMD5($assigndata);
        $assigndata['session-id']=$this->tiisession;
        $assigndata['anon_reason']=$post->anon_reason;
        $assigndata['src']=TURNITIN_APISRC;
        $assigndata['apilang']=$this->getLang();
        $this->result=$this->doRequest("POST", $this->apiurl, $assigndata,true,$status);
    }
    /**
     * Call to API FID21, FCMD1 to download inbox data bulk zip file
     *
     * @param object $post The post object that contains the necessary query parameters for the call
     * @param string $status The status to pass to the loaderbar class
     */
    function bulkDownload($post,$status) {

        $assigndata=array('gmtime'=>$this->tiiGmtime(),
                'encrypt'=>TII_ENCRYPT,
                'aid'=>$this->accountid,
                'diagnostic'=>0,
                'fcmd'=>1,
                'utp'=>$this->utp,
                'fid'=>21,
                'assignid'=>$post->assignid,
                'assign'=>$post->assign,
                'ctl'=>$post->ctl,
                'cid'=>$post->cid,
                'uid'=>$this->uid,
                'uem'=>$this->uem,
                'ufn'=>$this->ufn,
                'uln'=>$this->uln
        );
        $assigndata['md5']=$this->doMD5($assigndata);
        $assigndata['session-id']=$this->tiisession;
        $assigndata['export_data']=$post->export_data;
        $assigndata['src']=TURNITIN_APISRC;

        $assigndata['apilang']=$this->getLang();
        $keys = array_keys($assigndata);
        $values = array_values($assigndata);
        $querystring='';
        for ($i=0;$i<count($values); $i++) {
            if ($i!=0) {
                $querystring .= '&';
            }
            $querystring .= $keys[$i].'='.urlencode($values[$i]);
        }
        return $this->apiurl."?".$querystring;
    }
    /**
     * Returns the Return Message (rmessage) for the API call
     *
     * @return string The RMESSAGE or Empty if not available
     */
    function getFileData() {
        if ($this->xmlToArray($this->result)) {
            $pos = $this->_xmlkeys['FILE_DATA'][0];
            return base64_decode($this->_xmlvalues[$pos]['value']);
        } else if (strlen($this->curlerror)>0) {
            return 'CURL ERROR: '.$this->curlerror;
        } else {
            return '';
        }
    }
    /**
     * Call to API FID99, FCMD1 to migrate Turnitin Open API entries to Moodle Native SRC 12
     */
    function migrateSRCData() {

        $assigndata=array('gmtime'=>$this->tiiGmtime(),
                'encrypt'=>TII_ENCRYPT,
                'aid'=>$this->accountid,
                'diagnostic'=>0,
                'fcmd'=>2,
                'utp'=>$this->utp,
                'fid'=>99,
                'uem'=>$this->uem,
                'ufn'=>$this->ufn,
                'uln'=>$this->uln
        );
        $assigndata['md5']=$this->doMD5($assigndata);
        $assigndata['src']=TURNITIN_APISRC;
        $this->result=$this->doRequest("POST", $this->apiurl, $assigndata,true,"");
    }
    /**
     * Creates a Turnitin MD5 parameter from the $post object
     *
     * @param object $post The post object that contains the necessary query parameters for the call
     * @return string The MD5 hash of the posted query values
     */
    function doMD5($post) {
        global $CFG;
        $output="";
        ksort($post);
        $postKeys=array_keys($post);
        for ($i=0;$i<count($post);$i++) {
            $thisKey=$postKeys[$i];
            $output.=$post[$thisKey];
        }
        $output.=$CFG->turnitin_secretkey;
        // $this->doLogging($postKeys,$output);
        return md5($output);
    }
    /**
     * Creates a Turnitin GMTIME parameter to pass to the API
     *
     * @return string The GMTIME parameter with the last digit stripped off
     */
    function tiiGmtime() {
        $output="";
        $output.=gmdate('YmdH',time());
        $output.=substr(gmdate('i',time()),0,1);
        return $output;
    }
    /**
     * Outputs a Turnitin compatible lang+api code
     *
     * @param string $langcode The Moodle language code
     * @return string The cleaned and mapped associated Turnitin lang code
     */
    function getLang() {
        $langcode=str_replace("_utf8","",current_language());
        $langarray = array(
            'en'=>'en_us',
            'en_us'=>'en_us',
            'fr'=>'fr',
            'fr_ca'=>'fr',
            'es'=>'es',
            'de'=>'de',
            'de_du'=>'de',
            'zh_cn'=>'cn',
            'zh_tw'=>'zh_tw',
            'pt_br'=>'pt_br',
            'th'=>'th',
            'ja'=>'ja',
            'ko'=>'ko',
            'ms'=>'ms',
            'tr'=>'tr',
            'ca'=>'es',
            'sv'=>'sv',
            'nl'=>'nl'
        );
        $langcode = (isset($langarray[$langcode])) ? $langarray[$langcode] : 'en_us';
        return $langcode;
    }

}

/* ?> */