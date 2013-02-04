<?php
/**
 * cronCronController 
 * 
 * @package 
 * @version $id$
 * @copyright 
 * @author Pierre-Alexis <pa@quai13.com> 
 * @license 
 */
class cronCronController extends cronCronController_Parent
{

    public $crontask = array();

    public function __construct($params = null)
    {
        // no time limit
        ini_set('max_execution_time', 0);
        // be quiet
        define('__NO_DEBUG_DIV__', 1);
        // get action info in order to log start and stop date
        $req = $this->getRequest();
        $this->crontask['lang']       = $req['LANG'];
        $this->crontask['action']     = $req['ACT'];
        $this->crontask['date_start'] = date('Y-m-d H:i:s');
        $this->crontask['date_stop']  = null;
        // log start date
        if (!isset($this->crontask['logging'])) {
            $this->crontask['logging'] = 1;
            $cron = $this->getModel('cron');
            $this->crontask['id'] = $cron->logging($this->crontask);
        }
    }

    public function __destruct()
    {
        // log end date
        if ($this->crontask['logging']) {
            $this->crontask['date_stop'] = date('Y-m-d H:i:s');
            $cron = $this->getModel('cron');
            return $cron->logging($this->crontask);
        }
    }

    /**
     * indexAction : main cron controller
     * 
     * @access public
     * @return void
     */
    public function indexAction($params = null)
    {
        // be quiet
        return array('dont_getblock' => true);
    }

    /**
     * get_last_execution_date : returns the date of last execution for the current task
     * 
     * @access public
     * @return void
     */
    public function get_last_execution_date()
    {
        return $this->getModel('cron')->get_last_execution_date($this->crontask);
    }

}
?>
