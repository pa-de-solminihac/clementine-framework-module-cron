<?php
/**
 * cronCronController : clementine cron module
 *     check if ip is allowed
 *     log cron calls into database
 *     ignore calls if previous task is still running (cronCronModel provides is_running() and get_last_execution_date())
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

    /**
     * __construct : check if ip is allowed, ensures previous task is not still running, and log cron calls into database
     * 
     * @param mixed $request 
     * @param mixed $params 
     * @access public
     * @return void
     */
    public function __construct($request, $params = null)
    {
        $cron_config = Clementine::$config['clementine_cron'];
        if ($request->METHOD == 'CLI'
            || !isset($cron_config['allowed_ip']) 
            || (isset($cron_config['allowed_ip']) && (!$cron_config['allowed_ip'] || (in_array($_SERVER['REMOTE_ADDR'], explode(',', $cron_config['allowed_ip'])))))) {
            // no time limit
            ini_set('max_execution_time', 0);
            // be quiet
            define('__NO_DEBUG_DIV__', 1);
            // get action info in order to log start and stop date
            $this->crontask['lang']       = $request->LANG;
            $this->crontask['action']     = $request->ACT;
            $this->crontask['date_start'] = date('Y-m-d H:i:s');
            $this->crontask['date_stop']  = null;
            // log start date
            $cron = $this->getModel('cron');
            if (!isset($this->crontask['logging'])) {
                $this->crontask['logging'] = 1;
            }
            if ($this->crontask['logging']) {
                $this->crontask['id'] = null;
                $forcing = $request->get('int', 'force');
                if (!$forcing && $cron->is_running($this->crontask)) {
                    $errmsg = 'Clementine cron : ignored task (' . $this->crontask['action'] . ') because previous call did not finish cleanly (still running ?)';
                    // on utilise le handler d'erreur de Clementine, qui peut envoyer un mail
                    trigger_error($errmsg);
                    die();
                } else {
                    $this->crontask['id'] = $cron->logging($this->crontask);
                    $errmsg = 'Clementine cron : started task ' . $this->crontask['action'] . ' #' . $this->crontask['id'];
                    if ($forcing) {
                        $errmsg .= ' (forcing)';
                    }
                    error_log($errmsg);
                }
            }
        } else {
            // forbidden for this calling IP
            header($_SERVER['SERVER_PROTOCOL'] . ' 403 Forbidden', true);
            $errmsg = 'Clementine cron : forbidden call';
            error_log($errmsg);
            echo $errmsg;
            die();
        }
    }

    /**
     * __destruct : log end date 
     * 
     * @access public
     * @return void
     */
    public function __destruct()
    {
        // log end date
        if (isset($this->crontask['logging']) && $this->crontask['logging']) {
            $this->crontask['date_stop'] = date('Y-m-d H:i:s');
            $cron = $this->getModel('cron');
            $ret = $cron->logging($this->crontask);
            if ($this->crontask['id']) {
                $errmsg = 'Clementine cron : finished task ' . $this->crontask['action'] . ' #' . $this->crontask['id'];
                error_log($errmsg);
            }
            return $ret;
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
     * get_last_execution_date : returns the date of last execution of the current task
     * (if you call cronController/indexAction, this task is "index")
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
