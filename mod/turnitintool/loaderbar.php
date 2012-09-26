<?php
/**
 * The loader bar class deals with the status bar it expects one parameter $total
 *
 * @package turnitintool
 * @subpackage classes
 * @copyright 2010 iParadigms LLC
 */
class turnitintool_loaderbarclass {
    /**
     * @var int $proc The current procedure in progress
     */
    var $proc;
    /**
     * @var int $total The total number of procedures
     */
    var $total;
    /**
     * @var int $starttime The object start time represented by a unix timestamp
     */
    var $starttime;
	/**
	 * A backward compatible constructor / destructor method that works in PHP4 to emulate the PHP5 magic method __construct
	 */
    function turnitintool_loaderbarclass(){
        if (version_compare(PHP_VERSION,"5.0.0","<")) {
            $argcv = func_get_args();
            call_user_func_array(array(&$this, '__construct'), $argcv);
        }
    }
    /**
     * The constructor for the class, Calls the startloader() method
     * 
     * @param int $iTotal The total number of Procedures to follow as sent in the total paramet for the class
     */
    function __construct($iTotal) {
        $this->proc=0;
        $this->total=$iTotal;
        $this->starttime=time();
        $this->startloader();
        register_shutdown_function(array(&$this,"endloader"));
    }
    /**
     * The startloader method draws the header and div tags for the loaderbar
     */
    function startloader() {
        global $CFG;
        for ($i = 0; $i < ob_get_level(); $i++) {
            ob_end_flush();
        }
        // start output buffering
        if (ob_get_length() === false) {
            ob_start();
        }
		echo '<head>
                <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
                </head>
		<body></body>
		<script type="text/javascript" src="'.$CFG->wwwroot.'/mod/turnitintool/loaderbar.js"></script>
		<script type="text/javascript" language="javascript">
			function updateLoader(proc,total,percentdone,timeleft,status) {
				headText.innerHTML=\''.get_string('turnitinloading','turnitintool').'\';
				if (proc==total) {
					graphicURL="'.$CFG->wwwroot.'/mod/turnitintool/pix/progressgrad_animated.gif";
				} else {
					graphicURL="'.$CFG->wwwroot.'/mod/turnitintool/pix/progressgrad.gif";
				}
				backpos=(proc/total*250)-250;
				barBlock.style.backgroundImage=\'url(\'+graphicURL+\')\';
				barBlock.style.backgroundPosition=backpos+\'px 0px\';
				barBlock.style.border="1px solid #999999";
				if (status=="") {
					statusTEXT="'.get_string('synchronisingturnitin','turnitintool').'<br />"+percentdone+" - ("+timeleft+")";
				} else {
					statusTEXT=status+"<br />"+percentdone+" - ("+timeleft+")";
				}
				statusText.innerHTML=statusTEXT;
			}
			function closeLoader() {
				document.body.removeChild(loaderDiv);
				loaderBar.removeChild(statusText);
				loaderBar.removeChild(barBlock);
				loaderBar.removeChild(headText);
				loaderDiv.removeChild(loaderBar);
			}
		</script>
		';
        ob_flush();
        flush();
    }
    /**
     * The endloader method draws the footer and sets CSS display:none; to the loader bar div
     */
    function endloader() {
        echo '
            <script language="javascript" type="text/javascript">
                closeLoader();
            </script>';
    }
    /**
     * Returns the percentage that has been processed given the current procedure and the total
     *
     * @return Returns the percentage as a string (eg. 15%)
     */
    function getpercentdone() {
        $percentNum=ceil(($this->proc/$this->total)*100);
        return $percentNum.'%';
    }
    /**
     * Returns the estimated time remaing for the outstanding procedures 
     * based on the time it has taken to do the previous procedures
     *
     * @return Returns the time remaining string
     */
    function gettimeleft() {
        $timenow=time();
        $timeremaining=(($timenow-$this->starttime)/$this->proc)*($this->total-$this->proc+1);
        if ($this->proc>ceil($this->total-($this->total*0.99))) {
            return get_string('remaining','turnitintool',$this->secs2words(ceil($timeremaining)));
        } else {
            return get_string('estimatingtime','turnitintool');
        }
    }
    /**
     * Converts a number of seconds into a human readable string
     *
     * @param int $seconds The seconds to convert to a human readable format
     * @return Returns human readable time based on the seconds parameter
     */
    function secs2words($seconds) {
        $return="";
        $days=(int)($seconds/86400);
        $hours=(int)(($seconds-($days*86400))/3600);
        if($hours>0) {
            $return.=$hours." ".get_string('hrs','turnitintool');
        }
        $minutes = (int)(($seconds-$days*86400-$hours*3600)/60);
        if($hours>0 OR $minutes>0) {
            $return.=" ".$minutes." ".get_string('mins','turnitintool');
        } else {
            $secs=(int)($seconds-($days*86400)-($hours*3600)-($minutes*60)); 
            if (intval($secs)==0) {
                $secs='-';
            }
            $return.=" ".$secs." ".get_string('secs','turnitintool');
        }
        return $return;
    }
    /**
     * Sends a redraw command to output a javascript command to update the div
     * with the progress, staus and estimated time remaining
     *
     * @param string $status The status message to display to the user that describes this procedure
     */
    function redrawbar($status="") {
        $this->proc++;
        echo '
        <script language="javascript" type="text/javascript">
            updateLoader('.$this->proc.','.$this->total.',"'.$this->getpercentdone().'","'.$this->gettimeleft().'","'.$status.'");
        </script>';
        if (ob_get_length()) {
            ob_flush();
            flush();
        }
    }
}

/* ?> */