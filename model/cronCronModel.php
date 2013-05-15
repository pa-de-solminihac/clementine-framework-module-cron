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

}
?>
