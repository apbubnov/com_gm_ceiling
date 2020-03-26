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

/**
 * Methods supporting a list of Gm_ceiling records.
 *
 * @since  1.6
 */
class Gm_ceilingModelCallback extends JModelList
{

    function gettingData($filter = null)
    {
        try
        {
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query = 'SET lc_time_names = \'ru_RU\'';
            $db->setQuery($query);
            $db->execute();

            $query = $db->getQuery(true);
            $query->select('`a`.`id`,
							`a`.`client_id`,
							DATE_FORMAT(`a`.`date_time`,\'%e %M %Y %H:%i\') AS `date_time`,
							`a`.`comment`,
							`c`.`client_name`,
							`u`.`dealer_type`,
							`us`.`name` AS `manager_name`,
							`c`.`label_id`,
							`l`.`color_code` AS `label_color`,
							`p`.`project_status`')
                ->from('`#__gm_ceiling_callback` as `a`')
                ->innerJoin('`#__gm_ceiling_clients` as `c` ON `a`.`client_id` = `c`.`id`')
                ->leftJoin('`#__users` as `u` ON `a`.`client_id` = `u`.`associated_client`')
                ->innerJoin('`#__users` as `us` ON `a`.`manager_id` = `us`.`id`')
                ->leftJoin('(SELECT	MAX(`id`) AS `id`,
									`project_status`,
									`client_id`
							FROM	`rgzbn_gm_ceiling_projects`
							WHERE	`project_status` = 3
							GROUP BY	`client_id`) AS `p` ON `a`.`client_id` = `p`.`client_id`')
                ->leftJoin('`#__gm_ceiling_clients_labels` as `l` ON `c`.`label_id` = `l`.`id`')
                ->order('`a`.`date_time`');
            if(!empty($filter)){
                $query->where($filter);
            }
            $db->setQuery($query);
            $items = $db->loadObjectList();
            return $items;
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function getCallbackByDate($date){
        try
        {
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query->select('*');
            $query->from('`#__gm_ceiling_callback`');
            $query->where("`date_time`<= $date");
            $db->setQuery($query);
            $item = $db->loadObjectList();
            return $item;
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function getNearestCallback($manager_id){
        try
        {
            $db = JFactory::getDbo();
            $manager_id = $db->escape($manager_id);
            $query = $db->getQuery(true);
            $query->select('*');
            $query->from('`#__gm_ceiling_callback`');
            $query->where("`date_time` BETWEEN NOW() AND NOW() + INTERVAL 10 MINUTE AND `manager_id` = $manager_id");
            $db->setQuery($query);
            $item = $db->loadObjectList();
            return $item;
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function getCallbackByClient($client_id){
        try
        {
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query->select('*');
            $query->from('`#__gm_ceiling_callback`');
            $query->where("`client_id`= $client_id");
            $db->setQuery($query);
            $item = $db->loadObjectList();
            return $item;
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function getCallbackbyId($id){
        try
        {
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query->select('*');
            $query->from('`#__gm_ceiling_callback`');
            $query->where("`id`= $id");
            $db->setQuery($query);
            $item = $db->loadObject();
            return $item;
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function save($jdate, $comment, $id_client, $manager_id)
    {
        try
        {
            $db = JFactory::getDbo();
            $comment = $db->escape($comment, true);
            $id_client = $db->escape($id_client, true);
            $manager_id = $db->escape($manager_id, true);

            $comment = str_replace('\\"', '\\\\"', $comment);

            if (empty($manager_id))
            {
                $manager_id = 1;
            }

            $query = $db->getQuery(true);
            $query->insert('`#__gm_ceiling_callback`');
            $query->columns('`client_id`, `date_time`, `comment`, `manager_id`');
            $query->values("$id_client, '$jdate', '$comment', $manager_id");

            $db->setQuery($query);
            $db->execute();
            $last_id = $db->insertid();

            $history_model = Gm_ceilingHelpersGm_ceiling::getModel('client_history');
            $history_model->save($id_client,'Назначен звонок на '.date("d.m.Y H:i:s", strtotime($jdate)));
            return $last_id;
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
    function updateClientId($client_id,$ids)
    {
        try
        {
            $db = JFactory::getDbo();
            foreach($ids as $id){
                $query = $db->getQuery(true);
                $query->update('#__gm_ceiling_callback');
                $query->set('client_id = '.$client_id);
                $query->where('id = '.$id);
                $db->setQuery($query);
                $db->execute();
            }
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function updateCall($id,$time,$comment)
    {
        try
        {
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query->update('#__gm_ceiling_callback');
            $query->set("date_time = '$time'");
            if(!empty($comment))
            {
                $query->set("comment = '$comment'");
            }
            $query->set("notify = 0");
            $query->where('id = '.$id);
            $db->setQuery($query);
            $db->execute();
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function updateNotify($id)
    {
        try
        {
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query->update('`#__gm_ceiling_callback`');
            $query->set("`notify` = 1");
            $query->where('`id` = '.$id);
            $db->setQuery($query);
            $db->execute();
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function moveTime($id,$date,$comment){
        try
        {
            $db = JFactory::getDbo();

            $query = $db->getQuery(true);
            if(empty($date)){
                $subquery = $db->getQuery(true);
                $subquery
                    ->select('date_time')
                    ->from('#__gm_ceiling_callback')
                    ->where('id = '.$id);
                $db->setQuery($subquery);
                $old_date = $db->loadObject();
                $query->select('ADDTIME(\''.$old_date->date_time.'\',\'00:30:00\') as date');

                $db->setQuery($query);
                $date = $db->loadObject();
                $query->update('#__gm_ceiling_callback');
                $query->set('date_time = \''.$date->date.'\'');
                $query->set("notify = 0");
                $query->where('id = '.$id);
                $db->setQuery($query);
                $db->execute();
            }
            else{
                $this->updateCall($id,$date,$comment);

            }
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
    function deleteCall($id)
    {
        try
        {
            $db = JFactory::getDbo();
            $id = $db->escape($id, true);
            $query = $db->getQuery(true);
            $query->delete('`#__gm_ceiling_callback`');
            $query->where('`id` = '.$id);
            $db->setQuery($query);
            $db->execute();
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function addCallHistory($manager_id, $client_id, $status)
    {
        try
        {
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query->insert('`#__gm_ceiling_calls_status_history`');
            $query->columns('`manager_id`, `client_id`, `status`');
            $query->values("$manager_id, $client_id, $status");
            $db->setQuery($query);
            $db->execute();
            return $db->insertId();
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
    function selectCallAnalytic($dealerId,$date1 = null,$date2 = null){
        try{

            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $subquery = $db->getQuery(true);
            $measureQuery = $db->getQuery(true);
            $designerQuery = $db->getQuery(true);

            $query = 'SET SESSION group_concat_max_len  = 163840';
            $db->setQuery($query);
            $db->execute();

            $measureQuery
                ->select('ph.new_status,p.created_by,COUNT(DISTINCT p.id) AS `count`,GROUP_CONCAT( DISTINCT p.id SEPARATOR \',\') AS ids')
                ->from('`rgzbn_gm_ceiling_projects` AS p ')
                ->leftJoin('`rgzbn_gm_ceiling_projects_history` AS ph ON p.id = ph.project_id')
                ->where('ph.new_status IN(1,3,4,5)')
                ->group('ph.new_status,p.created_by');
            if(!empty($date1)&&!empty($date2)){
                $measureQuery->where("`ph`.`date_of_change` BETWEEN '$date1 00:00:00' AND '$date2 23:59:59'");
            }
            elseif(!empty($date1)&&empty($date2)){
                $measureQuery->where("`ph`.`date_of_change` > '$date1 00:00:00'");
            }
            elseif(empty($date1)&&!empty($date2)){
                $measureQuery->where("`ph`.`date_of_change` < '$date2 23:59:59'");
            }

            $designerQuery
                ->select('`c`.`manager_id`,COUNT(`us`.`id`) AS `added_users`')
                ->from('`rgzbn_users` AS `us`')
                ->innerJoin('`rgzbn_gm_ceiling_clients` AS `c` ON `c`.`id` = `us`.`associated_client`')
                ->where('`us`.`dealer_type` = 3')
                ->group('`c`.`manager_id`');
            if(!empty($date1)&&!empty($date2)){
                $designerQuery->where("`us`.`registerDate` BETWEEN '$date1 00:00:00' AND '$date2 23:59:59'");
            }
            elseif(!empty($date1)&&empty($date2)){
                $designerQuery->where("`us`.`registerDate` > '$date1 00:00:00'");
            }
            elseif(empty($date1)&&!empty($date2)){
                $designerQuery->where("`us`.`registerDate` < '$date2 23:59:59'");
            }

            $subquery
                ->select('`h`.`manager_id`,`h`.`status`,`u`.`name` AS `manager`,`h`.`change_time`,`u`.`dealer_id`,IFNULL(`au`.`added_users`,\'-\') AS `added_users`,COUNT( DISTINCT `h`.`id`)  AS `count`')
                //->select('GROUP_CONCAT( DISTINCT CONCAT(\'{"status":"\',IFNULL(pr.new_status,"-"),\'","count":"\',IFNULL(pr.count,"-"),\'","ids":"\',IFNULL(pr.ids,"-"),\'"}\')) AS projects_count')
                ->from('`rgzbn_gm_ceiling_calls_status_history` AS h ')
                ->innerJoin('`rgzbn_users` AS u ON u.id = h.manager_id')
                //->leftJoin("($measureQuery) as pr ON pr.created_by = h.manager_id")
                ->leftJoin("($designerQuery) AS au ON au.manager_id = h.manager_id")
                ->where("`u`.`dealer_id` = $dealerId")
                ->group('`h`.`manager_id`,`h`.`status`');
            if(!empty($date1)&&!empty($date2)){
                $subquery->where("`h`.`change_time` BETWEEN '$date1 00:00:00' AND '$date2 23:59:59'");
            }
            elseif(!empty($date1)&&empty($date2)){
                $subquery->where("`h`.`change_time` > '$date1 00:00:00'");
            }
            elseif(empty($date1)&&!empty($date2)){
                $subquery->where("`h`.`change_time` < '$date2 23:59:59'");
            }
            $query = $db->getQuery(true);
            $query
                ->select('cs.id,cs.title,CONCAT(\'[\',GROUP_CONCAT(CONCAT(\'{"manager":"\',cnt.manager,\'","added_users":"\',cnt.added_users,\'","count":"\',cnt.count,\'"}\')),\']\') AS manager_count')
                ->from('`rgzbn_gm_ceiling_calls_status` AS cs')
                ->leftJoin("($subquery) AS `cnt` ON `cs`.`id` = `cnt`.`status`")
                ->group('cs.id');
            $db->setQuery($query);
           // throw new Exception($query);
            $items = $db->loadObjectList();
            $projectsHistoryModel = Gm_ceilingHelpersGm_ceiling::getModel('projectshistory');
            $projectsData = $projectsHistoryModel->getManagersProjects($date1,$date2);
            $result = (object)['calls'=>$items,'projects'=>$projectsData];
            return $result;
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
    function selectCallHistoryByStatus($status, $dealerId,$date1 = null,$date2 = null) {
        try {
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query->select('`h`.`client_id`, `h`.`change_time`, `c`.`client_name`, `u`.`name` AS `manager_name`');
            $query->from('`#__gm_ceiling_calls_status_history` AS `h`');
            $query->leftJoin('`#__gm_ceiling_clients` AS `c` ON `h`.`client_id` = `c`.`id`');
            $query->leftJoin('`#__users` AS `u` ON `h`.`manager_id` = `u`.`id`');
            $query->where("`h`.`status` = $status and `u`.`dealer_id` = $dealerId");
            if(!empty($date1)&&!empty($date2)){
                $query->where("`h`.`change_time` BETWEEN '$date1 00:00:00' AND '$date2 23:59:59'");
            }
            elseif(!empty($date1)&&empty($date2)){
                $query->where("`h`.`change_time` > '$date1 00:00:00'");
            }
            elseif(empty($date1)&&!empty($date2)){
                $query->where("`h`.`change_time` < '$date2 23:59:59'");
            }
            $db->setQuery($query);
            $result = $db->loadObjectList();
            return $result;
        } catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function updateCallbackDate($new_date,$client_id){
        try
        {
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query->update('#__gm_ceiling_callback')
                ->set("date_time = '$new_date'")
                ->where("client_id = '$data->id_client' and date_time like '$olddate%'");
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