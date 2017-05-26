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

require_once($CFG->libdir.'/cronlib.php');


/**
 *
 * This is a class for working with AWS
 *
 * @package   filter_poodll
 * @since      Moodle 2.7
 * @copyright  2014 Justin Hunt
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class taskrunner
{
	protected $task_records=array();
    protected $taskstart=0;


	 /**
     * Constructor
     */
    public function __construct($taskclassname, $timestart=0)
    {
        global $CFG;
        $this->load_all_tasks($taskclassname,$timestart);
    }

    function load_all_tasks($taskclassname, $timestart=false)
    {
        global $CFG, $DB;
        if ($timestart) {
            $where = "(nextruntime IS NULL OR nextruntime < :timestart1) AND classname = :classname";
            $params = array('timestart1' => $timestart, 'classname' => $taskclassname);
        } else {
            $where = "classname = :classname";
            $params = array('classname' => $taskclassname);
        }

        $records = $DB->get_records_select('task_adhoc', $where, $params);
        if ($records) {
            $this->task_records = $records;
        }
        mtrace("Found ". count($this->task_records) . " eligible to run $taskclassname task records \n\n");
        $this->timestart=$timestart;
    }

    function fetch_task_from_record($record){
        global $CFG, $DB;

        $cronlockfactory = \core\lock\lock_config::get_lock_factory('cron');

        if (!$cronlock = $cronlockfactory->get_lock('core_cron', 10)) {
            throw new \moodle_exception('locktimeout');
        }

        if ($lock = $cronlockfactory->get_lock('adhoc_' . $record->id, 0)) {

            // Safety check, see if the task has been already processed by another cron run.
            $record = $DB->get_record('task_adhoc', array('id' => $record->id));
            if (!$record) {
                $lock->release();
                return false;
            }

            $task = \core\task\manager::adhoc_task_from_record($record);
            // Safety check in case the task in the DB does not match a real class (maybe something was uninstalled).
            if (!$task) {
                $lock->release();
                return false;
            }

            $task->set_lock($lock);
            if (!$task->is_blocking()) {
                $cronlock->release();
            } else {
                $task->set_cron_lock($cronlock);
            }
            return $task;
        }
    }

    /**
     *Run all the move tasks outstanding
     */
    function get_task_by_filename($filename){

        // Run all adhoc tasks.
        foreach($this->task_records as $record){
            $thetask=$this->fetch_task_from_record($record);
            if($thetask){
                //mtrace('filename: ' . $thetask->get_custom_data()->filename . "\n\n");
                if($thetask->get_custom_data()->filename==$filename){
                    return $thetask;
                }else{
                    $this->adhoc_task_release($thetask);
                }//end of if classname
            }else{
                continue;
            }//end of if thetask
        }//end of foreach
        return false;
    }

    /**
     * Execute the move task with the given filename
     */
    function run_task_by_filename($filename){
        $task = $this->get_task_by_filename($filename);
        if($task){$this->run_task($task);}
    }

    /**
    *Run all the tasks outstanding
    */
    function run_all_tasks(){

        foreach($this->task_records as $record){
            //if the task cache has not been updated since we began, execute
            if(!\core\task\manager::static_caches_cleared_since($this->timestart)) {
                $task = $this->fetch_task_from_record($record);
                if ($task) {
                    $this->run_task($task);
                }//end of if task
            }//end of if static caches
        }//end of foreach
    }//end of function

    /**
     * Execute a specific task
     */
    function run_task($thetask) {
        global $DB, $CFG, $OUTPUT;

        if (CLI_MAINTENANCE) {
            echo "CLI maintenance mode active, cron execution suspended.\n";
            $this->adhoc_task_release($thetask);
            exit(1);
        }

        if (moodle_needs_upgrading()) {
            echo "Moodle upgrade pending, cron execution suspended.\n";
            $this->adhoc_task_release($thetask);
            exit(1);
        }

        require_once($CFG->libdir.'/adminlib.php');

        if (!empty($CFG->showcronsql)) {
            $DB->set_debug(true);
        }
        if (!empty($CFG->showcrondebugging)) {
            set_debugging(DEBUG_DEVELOPER, true);
        }

        try {

            \core_php_time_limit::raise();
            $starttime = microtime();

            // Increase memory limit
            raise_memory_limit(MEMORY_EXTRA);

            // Emulate normal session - we use admin accoutn by default
           // cron_setup_user();

            // Start output log
            $timenow  = time();
            mtrace("Server Time: ".date('r', $timenow)."\n\n");
            mtrace("Poodll task runner Execute adhoc task: " . get_class($thetask));
            cron_trace_time_and_memory();
            $predbqueries = null;
            $predbqueries = $DB->perf_get_queries();
            $pretime      = microtime(1);

            get_mailer('buffer');
            $thetask->execute();
            if ($DB->is_transaction_started()) {
                throw new coding_exception("Poodll Task Runner: Task left transaction open");
            }
            if (isset($predbqueries)) {
                mtrace("... used " . ($DB->perf_get_queries() - $predbqueries) . " dbqueries");
                mtrace("... used " . (microtime(1) - $pretime) . " seconds");
            }
            mtrace("Poodll task runner Adhoc task complete: " . get_class($thetask));
            \core\task\manager::adhoc_task_complete($thetask);
        } catch (Exception $e) {
            if ($DB && $DB->is_transaction_started()) {
                error_log('Database transaction aborted automatically in ' . get_class($thetask));
                $DB->force_transaction_rollback();
            }
            if (isset($predbqueries)) {
                mtrace("... used " . ($DB->perf_get_queries() - $predbqueries) . " dbqueries");
                mtrace("... used " . (microtime(1) - $pretime) . " seconds");
            }
            mtrace("Poodll taskrunner Adhoc task failed: " . get_class($thetask) . "," . $e->getMessage());
            if ($CFG->debugdeveloper) {
                if (!empty($e->debuginfo)) {
                    mtrace("Debug info:");
                    mtrace($e->debuginfo);
                }
                mtrace("Backtrace:");
                mtrace(format_backtrace($e->getTrace(), true));
            }
            \core\task\manager::adhoc_task_failed($thetask);
        }
        get_mailer('close');
        unset($thetask);
        mtrace("Poodll task runner completed correctly");

        gc_collect_cycles();
        mtrace('Task runner completed at ' . date('H:i:s') . '. Memory used ' . display_size(memory_get_usage()) . '.');
        $difftime = microtime_diff($starttime, microtime());
        mtrace("Execution took ".$difftime." seconds");
    }

    /**
     * This function indicates that an adhoc task was fetched, unused and should be marked as untouched
     *
     * @param \core\task\adhoc_task $task
     */
   function adhoc_task_release($task) {
        global $DB;
        if ($task->is_blocking()) {
            $task->get_cron_lock()->release();
        }
        $task->get_lock()->release();
    }
}//end of class