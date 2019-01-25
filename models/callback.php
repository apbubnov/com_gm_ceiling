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

			$query->select('`a`.`id`,`a`.`client_id`,`a`.`date_time`,`a`.`comment`, `c`.`client_name`, `u`.`dealer_type`')
				->from('`#__gm_ceiling_callback` as `a`')
				->innerJoin('`#__gm_ceiling_clients` as `c` ON `a`.`client_id` = `c`.`id`')
				->leftJoin('`#__users` as `u` ON `a`.`client_id` = `u`.`associated_client`')
				->order('`date_time` DESC');
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

	function selectCallHistoryByStatus($status, $dealerId) {
		try {
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select('`h`.`client_id`, `h`.`change_time`, `c`.`client_name`, `u`.`name` AS `manager_name`');
			$query->from('`#__gm_ceiling_calls_status_history` AS `h`');
			$query->leftJoin('`#__gm_ceiling_clients` AS `c` ON `h`.`client_id` = `c`.`id`');
			$query->leftJoin('`#__users` AS `u` ON `h`.`manager_id` = `u`.`id`');
			$query->where("`h`.`status` = $status and `u`.`dealer_id` = $dealerId");
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