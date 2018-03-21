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
class Gm_ceilingModelUsers extends JModelList
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @see        JController
	 * @since      1.6
	 */

	function getDealers()
	{
		try
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$kp_cnt_query = $db->getQuery(true);
			$comments_cnt_query = $db->getQuery(true);
			$dealer_instr_cnt_query = $db->getQuery(true);
			$kp_cnt_query
				->select('COUNT(*)')
				->from('`#__users_commercial_offer` as co')
				->where('co.user_id = u.id');
			$comments_cnt_query
				->select('COUNT(*)')
				->from('`#__gm_ceiling_client_history` as h')
				->where('h.client_id = u.associated_client');
			$dealer_instr_cnt_query
				->select('COUNT(*)')
				->from('`#__users_dealer_instruction` as di')
				->where('di.user_id = u.id');
			$query->select('`u`.`id`,`u`.`name`,`u`.`dealer_type`,`u`.`associated_client`,`c`.created,GROUP_CONCAT(`b`.`phone` SEPARATOR \', \') AS `client_contacts`,i.city,c.manager_id');
			$query->select("($kp_cnt_query) as kp_cnt");
			$query->select("($comments_cnt_query) as cmnt_cnt");
			$query->select("($dealer_instr_cnt_query) as inst_cnt");
			$query->from('`#__users` AS `u`');
			$query->leftJoin('`#__user_usergroup_map` ON `u`.`id`=`rgzbn_user_usergroup_map`.`user_id`');
			$query->innerJoin('`#__gm_ceiling_clients` AS `c` ON `u`.`associated_client` = `c`.`id`');
			$query->leftJoin('`#__gm_ceiling_clients_contacts` AS `b` ON `c`.`id` = `b`.`client_id`');
			$query->leftJoin('`#__gm_ceiling_dealer_info` as i on u.id = i.dealer_id');
			$query->where('`#__user_usergroup_map`.`group_id`= 14 AND (`u.dealer_type` < 2 OR `u.dealer_type` = 6)');
			$query->group('`id`');
			$query->order('`id` DESC');
			$db->setQuery($query);
			$item = $db->loadObjectList();
			return $item;
		}
		catch(Exception $e)
        {
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
        }
	}

	function getDesigners()
	{
		try
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select('`u`.`id`,`u`.`name`,`u`.`associated_client`,`c`.created,GROUP_CONCAT(`b`.`phone` SEPARATOR \', \') AS `client_contacts`,`u`.`refused_to_cooperate`');
			$query->from('`#__users` AS `u`');
			$query->innerJoin('`#__gm_ceiling_clients` AS `c` ON `u`.`associated_client` = `c`.`id`');
			$query->leftJoin('`#__gm_ceiling_clients_contacts` AS `b` ON `c`.`id` = `b`.`client_id`');
			$query->where('`dealer_type` = 3');
			$query->group('`id`');
			$query->order('`id` DESC');
			$db->setQuery($query);
			$item = $db->loadObjectList();
			return $item;
		}
		catch(Exception $e)
        {
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
        }
	}

	function addCommercialOfferCode($user_id, $code, $manager_id)
	{
		try
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->delete('`#__users_commercial_offer`');
			$query->where("`user_id` = $user_id");
			$db->setQuery($query);
			$db->execute();

			$query = $db->getQuery(true);
			$query->insert('`#__users_commercial_offer`');
			$query->columns('`user_id`,`code`,`manager_id`');
			$query->values("$user_id, '$code', $manager_id");
			$db->setQuery($query);
			$db->execute();
			
			return true;
		}
		catch(Exception $e)
        {
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
        }
	}

	function addDealerInstructionCode($user_id, $code, $manager_id)
	{
		try
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->delete('`#__users_dealer_instruction`');
			$query->where("`user_id` = $user_id");
			$db->setQuery($query);
			$db->execute();

			$query = $db->getQuery(true);
			$query->insert('`#__users_dealer_instruction`');
			$query->columns('`user_id`,`code`,`manager_id`');
			$query->values("$user_id, '$code', $manager_id");
			$db->setQuery($query);
			$db->execute();
			
			return true;
		}
		catch(Exception $e)
        {
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
        }
	}

	function acceptCommercialOfferCode($code)
	{
		try
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select('*');
			$query->from('`rgzbn_users_commercial_offer`');
			$query->where("`code` = '$code'");
			$db->setQuery($query);
			$item = $db->loadObject();

			if (empty($item))
			{
				throw new Exception('Code not found');
			}
			if ($item->status == 0)
			{
				$client_id = JFactory::getUser($item->user_id)->associated_client;
				if (!empty($item->manager_id))
				{
					$manager_id = $item->manager_id;
				}
				else
				{
					$manager_id = 1;
				}
				
				$callback_model = Gm_ceilingHelpersGm_ceiling::getModel('callback');
				$callback_model->save(date('Y-m-d H:i:s'),'Просмотрено коммерческое предложение',
					$client_id,$manager_id);

				$query = $db->getQuery(true);
				$query->update('`rgzbn_users_commercial_offer`');
				$query->set('`status` = 1');
				$query->where("`user_id` = $item->user_id");
				$db->setQuery($query);
				$db->execute();
			}
			return $item;
		}
		catch(Exception $e)
        {
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
        }
	}

	function acceptDealerInstructionCode($code)
	{
		try
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select('*');
			$query->from('`#__users_dealer_instruction`');
			$query->where("`code` = '$code'");
			$db->setQuery($query);
			$item = $db->loadObject();

			if (empty($item))
			{
				throw new Exception('Code not found');
			}
			if ($item->status == 0)
			{
				$client_id = JFactory::getUser($item->user_id)->associated_client;
				if (!empty($item->manager_id))
				{
					$manager_id = $item->manager_id;
				}
				else
				{
					$manager_id = 1;
				}
				
				$callback_model = Gm_ceilingHelpersGm_ceiling::getModel('callback');
				$callback_model->save(date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s').'+2 hours')),'Просмотрена инструкция по использованию программы',
					$client_id,$manager_id);

				$query = $db->getQuery(true);
				$query->update('`#__users_dealer_instruction`');
				$query->set('`status` = 1');
				$query->where("`user_id` = $item->user_id");
				$db->setQuery($query);
				$db->execute();
			}
			return $item;
		}
		catch(Exception $e)
        {
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
        }
	}

	function findNotViewCommercialOfferAfterWeek()
	{
		try
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select('*');
			$query->from('`rgzbn_users_commercial_offer`');
			$query->where("`change_time` < NOW() - INTERVAL 1 WEEK AND `status` = 0");
			$db->setQuery($query);
			$items = $db->loadObjectList();
			
			return $items;
		}
		catch(Exception $e)
        {
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
        }
	}

	function updateUserNameByAssociatedClient($associated_client, $name)
	{
		try
		{
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->update("`#__users`");
			$query->set("`name` = '$name'");
			$query->where("`associated_client` = $associated_client");
			
			$db->setQuery($query);
			$db->execute();
			
			return true;
		}
		catch(Exception $e)
        {
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
        }
	}

	function getUserByAssociatedClient($associated_client)
	{
		try
		{
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select('*');
			$query->from("`#__users`");
			$query->where("`associated_client` = $associated_client");
			
			$db->setQuery($query);
			$item = $db->loadObject();
			
			return $item;
		}
		catch(Exception $e)
        {
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
        }
	}

	function refuseToCooperate($id)
	{
		try
		{
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->update("`#__users`");
			$query->set("`refused_to_cooperate` = 1");
			$query->where("`id` = $id");
			
			$db->setQuery($query);
			$db->execute();
			
			return true;
		}
		catch(Exception $e)
        {
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
        }
	}

	function updateEmail($id, $email)
	{
		try
		{
			$db    = JFactory::getDbo();

			$query = $db->getQuery(true);
			$query->select("`id`");
			$query->from("`#__users`");
			$query->where("`email` = '$email'");
			$db->setQuery($query);
			$items = $db->loadObjectList();

			if (count($items) == 0)
			{
				$query = $db->getQuery(true);
				$query->update("`#__users`");
				$query->set("`email` = '$email'");
				$query->where("`id` = $id");
				$db->setQuery($query);
				$db->execute();
			}
			
			return true;
		}
		catch(Exception $e)
        {
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
        }
	}

	public function update_demo_date($dealer_id,$date){
		try
		{
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->update("`#__users`");
			$query->set("`demo_end_date` = '$date'");
			$query->where("`id` = $dealer_id");
			$db->setQuery($query);
			$db->execute();
			
			return true;
		}
		catch(Exception $e)
        {
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
        }
	}
	function change_dealer_type($id,$type){
		try
		{
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->update("`#__users`");
			$query->set("`dealer_type` = $type");
			$query->where("`id` = $id");
			$db->setQuery($query);
			$db->execute();
			return true;
		}
		catch(Exception $e)
        {
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
        }
	}
}