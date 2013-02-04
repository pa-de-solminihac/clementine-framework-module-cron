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
        if (isset($crontask['date_stop']) && $crontask['date_stop']) {
            $sql = "UPDATE `" . $this->crontable . "`
                       SET `date_stop` = '" . $db->escape_string($crontask['date_stop']) . "'
                     WHERE `id` = '" . (int) $crontask['id'] . "' ";
            return $db->query($sql);
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

}
?>
