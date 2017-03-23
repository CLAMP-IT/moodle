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

namespace filter_poodll;

defined('MOODLE_INTERNAL') || die();

use Aws\ElasticTranscoder\ElasticTranscoderClient;
use Aws\S3\S3Client;

/**
 *
 * This is a class for working with AWS
 *
 * @package   filter_poodll
 * @since      Moodle 2.7
 * @copyright  2014 Justin Hunt
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class awstools
{
	protected $awsversion="2.x";//3.x
	protected $transcoder = false; //the single transcoder object
	protected $s3client = false; //the single S3 object
	protected $default_segment_size = 4;
	protected $region = 'ap-northeast-1';
    protected $convfolder = 'transcoded/';
    protected $accesskey ='';
    protected $secretkey='';
	protected $pipeline_video ='1467090278549-scfvbk'; //standard-transcoding-pipeline
	protected $pipeline_audio ='1467090404312-4yquey'; //standard-transcoding-pipeline
	protected $bucket_video_archive ='poodll-video-process-archive'; //video archive bucket
	protected $bucket_video_in ='poodll-videoprocessing-in'; //video in bucket
	protected $bucket_video_out ='poodll-videoprocessing-out'; //video out bucket
	protected $bucket_audio_in ='poodll-audioprocessing-in'; //audio in bucket
	protected $bucket_audio_out ='poodll-audioprocessing-out'; //audio out bucket
	protected $bucket_audio_archive ='poodll-audioprocessing-archive'; //audio archive
	protected $bucket_thumbnails ='poodll-video-thumbs'; //video thumbs bucket
	protected $bucket_production ='poodll-pickup'; //web accessible bucket
	protected $preset_mp3 = "1467090564863-tc7k8e";
	protected $preset_mp4 = "1467090505514-0gibkw";
	protected $thumbnail_preset = '1467090505514-0gibkw'; //thumbnail size is set to preset size so use 720p preset to get a bigger thumbnail. 



    /**
     * Make S3 filename (ala object key)
     */
	public static function fetch_s3_filename($mediatype, $filename){
            global $CFG,$USER;
            $s3filename =$CFG->wwwroot . '/' . $USER->sesskey . '/' . $mediatype . '/' . $filename;
            $s3filename = mb_ereg_replace("([^\w\s\d\-_~,;\[\]\(\).])", '', $s3filename);
            // Remove any runs of periods (thanks falstro!)
            $s3filename = mb_ereg_replace("([\.]{2,})", '', $s3filename);       
            return $s3filename;
    }

	 /**
     * Constructor
     */
    public function __construct() {
		global $CFG;
                $lm = new \filter_poodll\licensemanager();
                $this->accesskey = $lm->get_cloud_access_key($CFG->filter_poodll_registrationkey);
		$this->secretkey = $lm->get_cloud_access_secret($CFG->filter_poodll_registrationkey);

                 //We need to support pre 5.5 versions of PHP
		// but aws 3.x is from php 5.5 and up.
		if($CFG->filter_poodll_aws_sdk=="2.x"){
			$this->awsversion = "2.x";
			require_once($CFG->dirroot . '/filter/poodll/3rdparty/aws-v2/aws-autoloader.php');
		}else{
			$this->awsversion = "3.x";
			require_once($CFG->dirroot . '/filter/poodll/3rdparty/aws-v3/aws-autoloader.php');
		 }
    }
	
/**
*
*
*  TRANSCODING CODE STARTS HERE
*
*/
		
		
	 //fetch or create the transcoder object 
	function fetch_transcoder(){
        global $CFG;
		if(!$this->transcoder){
		        $config = array();
                $config['region'] = $this->region;
                $config['version']='2012-09-25';
                $config['default_caching_config'] = $CFG->tempdir . '/tmp';
                $config['credentials']= array('key' => $this->accesskey, 'secret' => $this->secretkey);
                //add proxy settings if necessary
                if(!empty($CFG->proxyhost)){
                    $proxy=$CFG->proxytype . '://' . $CFG->proxyhost;
                    if($CFG->proxyport > 0) {$proxy = $proxy . ':' . $CFG->proxyport;}
                    if(!empty($CFG->proxyuser)){
                        $proxy = $CFG->proxyuser . ':' . $CFG->proxypassword . '@' . $proxy;
                    }
                    $config['request.options']=array('proxy'=>$proxy);
                }
			    $this->transcoder = ElasticTranscoderClient::factory($config);
		}
		return $this->transcoder;
	}
    
    //post file data directly to S3
	function s3_put_filedata($mediatype,$key,$filedata){
		$s3client = $this->fetch_s3client();
	
		//Get bucket
		$bucket='';
		switch($mediatype){
			case 'audio':
				$bucket=$this->bucket_audio_in;
				break;
			case 'video':
				$bucket=$this->bucket_video_in;
				break;
		}
		//options
		$options = array();
		$options['Bucket']=$bucket;
		$options['Key']=$key;
		$options['Body']=$filedata;
		//$options['Sourcefile']=$filepath;
		//$options['ContentMD5']=false;
		$options['ContentType']='application/octet-stream';
		
		$result = $s3client->putObject($options);
		if($result){
			return true;
		}else{
			return false;
		}	
	}

	
	//create a single job
	function create_one_transcoding_job($mediatype, $input_key,$output_key) {
	
		$transcoder_client = $this->fetch_transcoder();
	
		//create the output prefix
		$output_key_prefix = $this->convfolder;
//		echo 'creating transcoding job:' . PHP_EOL;

		
		  # Setup the job input using the provided input key.
		  $input = array('Key' => $input_key);


		  # Specify the outputs based on the hls presets array spefified.
		  $outputs = array();
		  switch($mediatype){

		  	case 'video':
		  		$pipeline_id= $this->pipeline_video;
		  		$one_output = array('Key'=> $output_key, 'PresetId' =>$this->preset_mp4);
		  		$outputs[] = $one_output;
		  		break;

            case 'audio':
            default:
              $pipeline_id= $this->pipeline_audio;
              $one_output = array('Key'=> $output_key, 'PresetId' =>$this->preset_mp3);
              $outputs[] = $one_output;
              break;
		  }

		  # Create the job.
		  $create_job_request = array(
				'PipelineId' => $pipeline_id, 
				'Input' => $input, 
				'Outputs' => $outputs, 
				'OutputKeyPrefix' => $output_key_prefix
		  );
		  $create_job_result = $transcoder_client->createJob($create_job_request)->toArray();
		  return $job = $create_job_result['Job'];
	}   
    
	
        /**
        *
        *
        *  COMMON CODE STARTS HERE
        *
        */

        function does_file_exist($mediatype, $filename, $in_out='in'){
			$s3client= $this->fetch_s3client();
			$bucket='';
			switch($mediatype){
			 case 'audio':
				if($in_out == 'out'){
					$bucket =$this->bucket_audio_out;
				}else{
					$bucket =$this->bucket_audio_in;
				}
				break;
		 
			 case 'video':
				if($in_out == 'out'){
					$bucket =$this->bucket_video_out;
				}else{
					$bucket =$this->bucket_video_in;
				}
				break;
			}		
                return $s3client->doesObjectExist($bucket,$filename);		
        }
        
        //called if we get a file submitted twice
        function remove_transcoded($mediatype, $filename){
        	switch ($mediatype){

		 		case 'video':
		 		   $bucketname =$this->bucket_video_out;
		 		   break;

                case 'audio':
                default:
                    $bucketname =$this->bucket_audio_out;
                    break;
        	}
        	$this->s3remove($bucketname,$filename);
        }

        function fetch_s3_converted_file($mediatype,$infilename, $outfilename,$filename,$filerecord){
           global $CFG;
            if($this->does_file_exist($mediatype, $this->convfolder . $outfilename,'out')){
                $tempfilepath = $CFG->tempdir . "/" . $filename;
                $this->save_converted_to_file($mediatype,$outfilename, $tempfilepath);
                return $tempfilepath;
            }else{
                if(!$this->does_file_exist($mediatype,$infilename,'in')){
                    //if we do not even have an input file then just return, somethings wrong
                    //but it can not be fixed
                    return false;
                }else{
                    return true;
                }
            }
            
        }
//fetch or create the S3 object 
	function fetch_s3client(){
		global $CFG;
		
		if(!$this->s3client){
			$config = array();
			$config['region']=$this->region;
			$config['version']='2006-03-01';
			$config['credentials']=array('key' => $this->accesskey, 'secret' => $this->secretkey);
			//add proxy settings if necessary
			if(!empty($CFG->proxyhost)){
				$proxy=$CFG->proxytype . '://' . $CFG->proxyhost;
				if($CFG->proxyport > 0) {$proxy = $proxy . ':' . $CFG->proxyport;}
				if(!empty($CFG->proxyuser)){
					$proxy = $CFG->proxyuser . ':' . $CFG->proxypassword . '@' . $proxy;
				}
				$config['request.options']=array('proxy'=>$proxy);
			}
			$this->s3client = S3Client::factory($config);
		}
		return $this->s3client;
	}
	
	function save_converted_to_file($mediatype,$filename,$filepath){
		$s3client = $this->fetch_s3client();
		$bucket = '';
		switch($mediatype){
			case 'audio':
				$bucket=$this->bucket_audio_out;
				break;
			case 'video':
				$bucket=$this->bucket_video_out;
				break;
		}
		 $s3client->getObject(array(
   			 'Bucket' => $bucket,
    		'Key'    => $this->convfolder . $filename,
    		'SaveAs' => $filepath
		));
                 return true;
	}
	
	function get_presigned_download_url($mediatype,$minutes=30,$key){
		$s3client = $this->fetch_s3client();
		//Get bucket
		$bucket='';
		$key= 'convertedmedia/' . $key;
		switch($mediatype){
			case 'audio':
				$bucket=$this->bucket_audio_out;
				break;
			case 'video':
				$bucket=$this->bucket_video_out;
				break;
		}
		//options
		$options = array();
		$options['Bucket']=$bucket;
		$options['Key']=$key;
		
		$cmd = $s3client->getCommand('GetObject', $options);
		$request = $s3client->createPresignedRequest($cmd, '+' . $minutes .' minutes');
		$theurl = (string) $request->getUri();
		return $theurl;
	}

	function get_presigned_upload_url($mediatype,$minutes=30,$key, $iosvideo=false){
		$s3client = $this->fetch_s3client();
		//Get bucket
		$bucket='';
		switch($mediatype){
			case 'audio':
				$bucket=$this->bucket_audio_in;
				break;
			case 'video':
				$bucket=$this->bucket_video_in;
				break;
		}
		//options
		$options = array();
		$options['Bucket']=$bucket;
		$options['Key']=$key;
		$options['Body']='';
		//$options['ContentMD5']=false;
		if($iosvideo){
			$options['ContentType']='video/quicktime';
		}else{
			$options['ContentType']='application/octet-stream';
		}
		
		$cmd = $s3client->getCommand('PutObject', $options);
		if($this->awsversion=="3.x"){
			$request = $s3client->createPresignedRequest($cmd, '+' . $minutes .' minutes');
			$theurl = (string) $request->getUri();
		}else{
			$theurl =$cmd->createPresignedUrl('+' . $minutes .' minutes');
		}
		return $theurl;
	}
	
	// list one bucket files
	function iterate_bucket_listing($thebucket){
	
		$s3client = $this->fetch_s3client();
		$objects = $s3client->getIterator('ListObjects', array(
			'Bucket' => $thebucket,
			'Prefix' => ''
		));
		
		//echo 'listing :' . $objects->count() . ' files' . PHP_EOL;
		foreach ($objects as $object) {
		    $filename = $object['Key'] ;
    		//do something here ....
			//echo 'file:' . $filename . PHP_EOL;
    	}			
	}
	
		
	function s3copy($sourcebucket, $sourceitemname, $targetbucket,$targetitemname, $ispublic=false){
		$s3client = $this->fetch_s3client();
		
		//delete if it exists 
		if($s3client->doesObjectExist($targetbucket,$targetitemname)){
				 echo  "deleting";
				 $this->s3remove($targetbucket,$targetitemname);
		 }
		
		//do the copy 
		$acl = $ispublic ? 'public-read' : 'private';
		$s3client->copyObject(array(
			'Bucket'     => $targetbucket,
			'Key'        => $targetitemname,
			'CopySource' => "{$sourcebucket}/{$sourceitemname}",
			'ACL' => $acl
		));
	}
	
	function s3remove($bucket, $itemname){
		$s3client = $this->fetch_s3client();
		$s3client->deleteObject(array(
			'Bucket'     => $bucket,
			'Key'        => $itemname,
		));
	}
		
	//post process transcoded files
	function s3copy_folder($sourcebucket, $sourceitemname, $targetbucket,$targetitemname, $ispublic=false){
	
		$s3client = $this->fetch_s3client();
		$objects = $s3client->getIterator('ListObjects', array(
			'Bucket' => $sourcebucket,
			'Prefix' => $sourceitemname
		));
	
		//get the folder name .. should be a better way .. but tired ..
		$partsarray = split('/',$sourceitemname);
		array_pop($partsarray);
		$foldername = array_pop($partsarray);
		
		//loop through all the objects and copy them
		//then delete them!!!!
		foreach ($objects as $object) {
			$filename = str_replace($sourceitemname, '',$object['Key']);
		//	echo 'lets copy object:' . $filename .  PHP_EOL ;	
		//	echo 'targetbucket:' . $targetbucket .  PHP_EOL ;
		//	echo 'targetitemname:' . $targetitemname .  PHP_EOL ;
    		$this->s3copy($sourcebucket, $sourceitemname  . $filename, 
    			$targetbucket,$targetitemname  . $foldername . '/' . $filename, $ispublic);
		}
		//echo 'folder copied:' . $targetitemname . PHP_EOL ;	
		
	}
	
		//post process transcoded files
	function s3remove_folder($bucket, $itemname){
	
		$s3client = $this->fetch_s3client();
		$objects = $s3client->getIterator('ListObjects', array(
			'Bucket' => $bucket,
			'Prefix' => $itemname
		));

		//loop through all the objects and copy them
		//then delete them!!!!
		foreach ($objects as $object) {
			$filename = str_replace($itemname, '',$object['Key']);
    		//remove object 
    		$s3client->deleteObject(array(
				'Bucket'     => $bucket,
				'Key'        => $itemname . $filename
				));
    	}
    	
    	//remove the source folder too
    	$s3client->DeleteObject(array(
				'Bucket'     => $bucket,
				'Key'        => $itemname
		));
		//echo 'folder removed:' . $itemname . PHP_EOL ;		
	}
	
	

	
	
	
}//end of class