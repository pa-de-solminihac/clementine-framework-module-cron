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

    public function __construct($request, $params = null)
    {
        $cron_config = Clementine::$config['clementine_cron'];
        if (!isset($cron_config['allowed_ip']) 
            || (isset($cron_config['allowed_ip']) && (!$cron_config['allowed_ip'] || (in_array($_SERVER['REMOTE_ADDR'], explode(',', $cron_config['allowed_ip'])))))) {
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
        } else {
            // forbidden for this calling IP
            header($_SERVER['SERVER_PROTOCOL'] . ' 403 Forbidden', true);
            echo 'Forbidden';
            die();
        }
    }

    public function __destruct()
    {
        // log end date
        if (isset($this->crontask['logging']) && $this->crontask['logging']) {
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
    public function indexAction($request, $params = null)
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
