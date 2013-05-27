<?php
/**
 * cronCronModel 
 * 
 * @package 
 * @version $id$
 * @copyright 
 * @author Pierre-Alexis <pa@quai13.com> 
 * @license 
 */
class cronCronModel extends cronCronModel_Parent
{

    public $crontable = 'clementine_cron';

    public function logging($crontask)
    {
        $db = $this->getModel('db');
        if (!empty($crontask['date_stop'])) {
            if (!empty($crontask['id'])) {
                $sql = "UPDATE `" . $this->crontable . "`
                           SET `date_stop` = '" . $db->escape_string($crontask['date_stop']) . "'
                         WHERE `id` = '" . (int) $crontask['id'] . "' ";
                $db->query($sql);
            }
            return false;
        } else {
            $sql = "INSERT INTO `" . $this->crontable . "` (`id`, `lang`, `action`, `date_start`, `date_stop`)
                                           VALUES (NULL,
                                                   '" . $db->escape_string($crontask['lang']) . "',
                                                   '" . $db->escape_string($crontask['action']) . "',
                                                   '" . $db->escape_string($crontask['date_start']) . "',
                                                   NULL) ";
            if ($db->query($sql)) {
                return $db->insert_id();
            }
        }
        return false;
    }

    /**
     * get_last_execution_date : returns the last time when task finished successfuly
     * 
     * @param mixed $crontask 
     * @access public
     * @return void
     */
    public function get_last_execution_date($crontask)
    {
        $db = $this->getModel('db');
        $sql = "SELECT date_start FROM `" . $this->crontable . "`
                 WHERE date_stop IS NOT NULL
                   AND lang   = '" . $db->escape_string($crontask['lang']) . "'
                   AND action = '" . $db->escape_string($crontask['action']) . "'
                 ORDER BY date_start DESC
                 LIMIT 1 ";
        $stmt = $db->query($sql);
        $res = $db->fetch_assoc($stmt);
        if (isset($res['date_start'])) {
            return $res['date_start'];
        }
        return false;
    }

    /**
     * is_running : returns true if task is running (ie. if end date is null)
     * 
     * @param mixed $crontask 
     * @access public
     * @return void
     */
    public function is_running($crontask)
    {
        $db = $this->getModel('db');
        $sql = "
            SELECT `date_stop`
              FROM `" . $this->crontable . "`
             WHERE `lang`   = '" . $db->escape_string($crontask['lang']) . "'
               AND `action` = '" . $db->escape_string($crontask['action']) . "'
             ORDER BY `date_start` DESC
             LIMIT 0, 1
        ";
        $stmt = $db->query($sql);
        // renvoie faux si la tache n'a jamais tourné
        if (!$db->num_rows($stmt)) {
            return false;
        }
        $res = $db->fetch_assoc($stmt);
        if (isset($res['date_stop'])) {
            return !$res['date_stop'];
        }
        return true;
    }

    /**
     * list_running : returns a list of tasks running for a least $seconds_ago seconds
     * 
     * @param mixed $seconds_ago 
     * @access public
     * @return void
     */
    public function list_running($seconds_ago = null)
    {
        if (empty($seconds_ago)) {
            $seconds_ago = Clementine::$config['clementine_cron']['warning_if_longer_than'];
        }
        $date_limite = date('Y-m-d H:i:s', mktime(date('H'), date('i'), date('s') - $seconds_ago));
        $db = $this->getModel('db');
        // recupere les taches qui ont été lancées il y a au moins $seconds_ago secondes et ne se sont pas terminées
        $sql = "
            SELECT DISTINCT `lang`, `action`, MAX(`date_start`) as `date_start`
              FROM `" . $this->crontable . "`
             WHERE `date_start` <= '" . $db->escape_string($date_limite) . "'
               AND `date_stop` IS NULL
             GROUP BY `lang`, `action`
             ORDER BY `date_start` DESC
        ";
        $stmt = $db->query($sql);
        $tasks = array();
        for (true; $res = $db->fetch_assoc($stmt); true) {
            // verifie si la tache a été terminée lors d'un lancement ultérieur
            $sql_ult = "
                SELECT DISTINCT `lang`, `action`
                  FROM `" . $this->crontable . "`
                 WHERE `date_start` > '" . $db->escape_string($res['date_start']) . "'
                   AND `date_stop` > '" . $db->escape_string($res['date_start']) . "'
                   AND `lang` = '" . $db->escape_string($res['lang']) . "'
                   AND `action` = '" . $db->escape_string($res['action']) . "'
            ";
            $stmt_ult = $db->query($sql_ult);
            // renvoie faux si la tache ne s'est pas lancee et terminee depuis, on la garde dans notre liste
            if (!$db->num_rows($stmt_ult)) {
                $tasks[] = $res;
            }
        }
        return $tasks;
    }

}
?>
