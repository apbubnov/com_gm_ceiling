<?php

/**
 * @version    CVS: 0.1.7
 * @package    Com_Gm_ceiling
 * @author     SpectralEye <Xander@spectraleye.ru>
 * @copyright  2016 SpectralEye
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.modelitem');
jimport('joomla.event.dispatcher');

use Joomla\Utilities\ArrayHelper;
/**
 * Gm_ceiling model.
 *
 * @since  1.6
 */
class Gm_ceilingModelCalcshistory extends JModelItem
{
    function getDataByProjectId($project_id){
        try{
            /*SELECT ch.calc_id,GROUP_CONCAT(CONCAT('{"status":"',ch.status_id,'","date_time":"',ch.date_time,'"}') SEPARATOR ',')
                FROM `rgzbn_gm_ceiling_calcs_history` AS ch
                INNER JOIN `rgzbn_gm_ceiling_calculations` AS c ON c.id = ch.calc_id
                WHERE c.project_id = 2431
                GROUP BY ch.calc_id*/
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query
                ->select('ch.calc_id,CONCAT(\'[\',GROUP_CONCAT(CONCAT(\'{"status":"\',ch.status_id,\'","date_time":"\',ch.date_time,\'"}\') SEPARATOR \',\'),\']\') as history')
                ->from('`rgzbn_gm_ceiling_calcs_history` AS ch')
                ->innerJoin('`rgzbn_gm_ceiling_calculations` AS c ON c.id = ch.calc_id')
                ->where("c.project_id = $project_id")
                ->group('ch.calc_id');
            $db->setQuery($query);
            $result = $db->loadAssocList('calc_id');
            return $result;
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function saveData($data){
        try{
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $values = [];
            $status = $data['status'];
            $user = JFactory::getUser();
            if(!empty($data['ids'])){
                foreach ($data['ids'] as $id){
                    $values[] = "$id,$status,$user->id";
                }
            }
            $query
                ->insert('`rgzbn_gm_ceiling_calcs_history`')
                ->columns('`calc_id`,`status_id`,`user_id`')
                ->values($values);
            $db->setQuery($query);
            $db->execute();
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
}
?>