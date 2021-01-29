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
			$query->select('DISTINCT `u`.`id`,`u`.`name`,`u`.`associated_client`,`c`.created,GROUP_CONCAT(`b`.`phone` SEPARATOR \', \') AS `client_contacts`,i.city,c.manager_id');
			$query->select("($kp_cnt_query) as kp_cnt");
			$query->select("($comments_cnt_query) as cmnt_cnt");
			$query->select("($dealer_instr_cnt_query) as inst_cnt");
			$query->from('`#__users` AS `u`');
			$query->leftJoin('`rgzbn_users_dealer_id_map` as `dm` on dm.user_id = u .id');
			$query->leftJoin('`#__user_usergroup_map` ON `u`.`id`=`#__user_usergroup_map`.`user_id`');
			$query->innerJoin('`#__gm_ceiling_clients` AS `c` ON `u`.`associated_client` = `c`.`id`');
			$query->leftJoin('`#__gm_ceiling_clients_contacts` AS `b` ON `c`.`id` = `b`.`client_id`');
			$query->leftJoin('`#__gm_ceiling_dealer_info` as i on u.id = i.dealer_id');
			$query->where('(`#__user_usergroup_map`.`group_id` = 14 OR dm.group_id = 14) AND `dealer_type` < 2');
			$query->group('`id`');
			$query->order('`id` DESC');
			$db->setQuery($query);
			$item = $db->loadObjectList();
			return $item;
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
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
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}

	function getBuilders(){
	    try{
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query->select('`u`.`id`,`u`.`name`,`u`.`associated_client`,`c`.created,GROUP_CONCAT(distinct `b`.`phone` SEPARATOR \', \') AS `client_contacts`,`u`.`refused_to_cooperate`');
            $query->select('GROUP_CONCAT(um.group_id SEPARATOR \';\') as groups');
            $query->select('m.name as manager,m.id as manager_id');
            $query->from('`#__users` AS `u`');
            $query->innerJoin('`#__gm_ceiling_clients` AS `c` ON `u`.`associated_client` = `c`.`id`');
            $query->leftJoin('`#__gm_ceiling_clients_contacts` AS `b` ON `c`.`id` = `b`.`client_id`');
            $query->INNERJOIN('`#__user_usergroup_map` AS `um` ON `um`.`user_id` = `u`.`id`');
            $query->leftJoin('`rgzbn_users` as m on m.id = c.manager_id');
            $query->where('`u`.`dealer_type` = 7');
            $query->group('`u`.`id`');
            $query->order('`u`.`id` DESC');
            $db->setQuery($query);
            $item = $db->loadObjectList();
            return $item;
        }

        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
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
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}

	function addDealerInstructionCode($user_id, $code, $manager_id)
	{
		try
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->delete('`#__users_dealer_instruction`');
			$query->where("`user_id` = $user_id and `code` = '$code'");
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
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}

	function acceptCommercialOfferCode($code, $type_kp)
	{
		try
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select('*');
			$query->from('`#__users_commercial_offer`');
			$query->where("`code` = '$code'");
			$db->setQuery($query);
			$item = $db->loadObject();

			if (empty($item))
			{
				throw new Exception('Code not found');
			}
			if ($item->status == 0)
			{
				$user = JFactory::getUser($item->user_id);
				$client_id = $user->associated_client;
				if (!empty($item->manager_id))
				{
					$manager_id = $item->manager_id;
				}
				else
				{
					$manager_id = 1;
				}

				$callback_model = Gm_ceilingHelpersGm_ceiling::getModel('callback');

				if ($type_kp == 0 && $user->dealer_type == 6)
				{
					$callback_model->save(date('Y-m-d H:i:s'),'Коммерческое предложение отклонено',
					$client_id,$manager_id);
				}
				elseif ($user->dealer_type == 7) {
					if ($type_kp == 3) {
						$callback_model->save(date('Y-m-d H:i:s'),'Московский застройщик просмотрел коммерческое предложение',
						$client_id,$manager_id);
					}
					else
					{
						$callback_model->save(date('Y-m-d H:i:s'),'Застройщик просмотрел коммерческое предложение',
						$client_id,$manager_id);
					}
				}
				elseif ($type_kp == 2 && $user->dealer_type == 1)
				{
					$callback_model->save(date('Y-m-d H:i:s'),'Просмотренны ошибки монтажа',
					$client_id,$manager_id);
				}
				else
				{
					$callback_model->save(date('Y-m-d H:i:s'),'Просмотрено коммерческое предложение',
					$client_id,$manager_id);
				}


				$query = $db->getQuery(true);
				$query->update('`#__users_commercial_offer`');
				$query->set('`status` = 1');
				$query->where("`user_id` = $item->user_id");
				$db->setQuery($query);
				$db->execute();
			}
			return $item;
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}

	function acceptDealerInstructionCode($code,$short = null)
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
				if($short == 2){
					$callback_model->save(date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s').'+2 hours')),'Просмотрено видео Быстрый заказ',
					$client_id,$manager_id);
				}
				if($short != 2){
					$callback_model->save(date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s').'+2 hours')),'Просмотрена инструкция по использованию программы',
					$client_id,$manager_id);
				}


				$query = $db->getQuery(true);
				$query->update('`#__users_dealer_instruction`');
				$query->set('`status` = 1');
				$query->where("`user_id` = $item->user_id and `code` = '$code'");
				$db->setQuery($query);
				$db->execute();
			}
			return $item;
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}

	function findNotViewCommercialOfferAfterWeek()
	{
		try
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select('*');
			$query->from('`#__users_commercial_offer`');
			$query->where("`change_time` < NOW() - INTERVAL 1 WEEK AND `status` = 0");
			$db->setQuery($query);
			$items = $db->loadObjectList();

			return $items;
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}

	function findDealersByCity($city)
	{
		try
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select('`u`.*');
			$query->from('`#__users` AS `u`');
			$query->innerJoin('`#__gm_ceiling_dealer_info` AS `i` ON `u`.`id` = `i`.`dealer_id`');
			$query->where("`i`.`city` = '$city'");
			$db->setQuery($query);
			$items = $db->loadObjectList();

			return $items;
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
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
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
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
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}

	function refuseToCooperate($id,$coop)
	{
		try
		{
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->update("`#__users`");
			$query->set("`refused_to_cooperate` = $coop");
			$query->where("`id` = $id");

			$db->setQuery($query);
			$db->execute();

			return true;
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
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
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}
	function updateName($id, $name)
	{
		try
		{
			$db    = JFactory::getDbo();

			$query = $db->getQuery(true);
			$query->select("`id`");
			$query->from("`#__users`");
			$query->where("`name` = '$name'");
			$db->setQuery($query);
			$items = $db->loadObjectList();

			if (count($items) == 0)
			{
				$query = $db->getQuery(true);
				$query->update("`#__users`");
				$query->set("`name` = '$name'");
				$query->where("`id` = $id");
				$db->setQuery($query);
				$db->execute();
			}

			return true;
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}
		function updatePhone($id, $phone)
		{
			try
			{
				$db    = JFactory::getDbo();
				$query = $db->getQuery(true);
				$query->update("`#__users`");
				$query->set("`username` = '$phone'");
				$query->where("`id` = $id");
				$db->setQuery($query);
				$db->execute();

				return true;
			}
			catch(Exception $e)
	        {
	            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
	        }
		}
		function updateAssocClient($id,$client_id)
		{
			try
			{
				$db    = JFactory::getDbo();
				$query = $db->getQuery(true);
				$query->update("`#__users`");
				$query->set("`associated_client` = '$client_id'");
				$query->where("`id` = $id");
				$db->setQuery($query);
				$db->execute();

				return true;
			}
			catch(Exception $e)
	        {
	            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
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
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
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
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}
	function add_request($id,$dealer_id){
		try
		{
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->insert("`#__gm_ceiling_manufacturer_map_request`");
			$query->columns("`manufacturer_id`,`dealer_id`");
			$query->values("$id,$dealer_id");
			$db->setQuery($query);
			$db->execute();
			return true;
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}

	function change_user_pass($id,$pass){
		try
		{
			$password = password_hash($pass, PASSWORD_BCRYPT);
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->update("`#__users`");
			$query->set("`password` = '$password'");
			$query->where("`id` = $id");
			$db->setQuery($query);
			$db->execute();
			return true;
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}

	function delete($id, $dealer_id){
		try
		{
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->delete("`#__users`");
			$query->where("`id` = $id AND `dealer_id` = $dealer_id");
			$db->setQuery($query);
			$db->execute();
			return true;
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}

	function getDealerMounters($dealerId) {
		try
		{
			$user = JFactory::getUser();
			$groups = $user->groups;
			$db = JFactory::getDbo();
			$type = 11;
			$items = [];
			if(!in_array(16, $groups) && $user->dealer_type != 2){
				$query = $db->getQuery(true);
				$query->select('distinct `u`.`id`, `u`.`name`')
					->from('`#__users` AS `u`')
                    ->leftJoin('`rgzbn_users_dealer_id_map` as `dm` on `dm`.`user_id`=`u`.`id`')
					->innerJoin('`#__user_usergroup_map` AS `g` ON `g`.`user_id` = `u`.`id`')
                    ->where("(`u`.`dealer_id` = $dealerId AND `g`.`group_id` = $type) OR (`dm`.`dealer_id` = $dealerId AND `dm`.`group_id` = 11)");
				$db->setQuery($query);
				//throw new Exception($query);
				$items = $db->loadObjectList();

				if (empty($items)) {
					$query = $db->getQuery(true);
					$query->select('`id`, `name`')
						->from('`#__users`')
						->where("`id` = $dealerId");
					$db->setQuery($query);
					$items = $db->loadObjectList();
				}
			}
			$mount_service = $this->getUserByGroup(26);
			foreach ($mount_service as $ms_brigade){
			    $ms_brigade->service = true;
            }
			$free_mounter = ($dealerId == 1 ) ? $this->getUserByGroup(32) : [];
			$items = array_merge($items,$free_mounter,$mount_service);

			return $items;
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}

	function getCountOfUsersByGroupAndDealer($group,$dealer_id){
	    try{
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query->select('count(distinct `u`.`id`) as brigades_count')
                ->from('`#__users` AS `u`')
                ->leftJoin('`rgzbn_users_dealer_id_map` as `dm` on`dm`.`user_id`=`u`.`id`')
                ->innerJoin('`#__user_usergroup_map` AS `g` ON `g`.`user_id` = `u`.`id`')
                ->where("(`u`.`dealer_id` = $dealer_id AND `g`.`group_id` = $group) OR (`dm`.`dealer_id` = $dealer_id and `dm`.`group_id` = $group)");
            $db->setQuery($query);
            $item = $db->loadObject();
            return $item;
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
	function getUserByGroup($group_id){
		try{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query
				->select(' distinct `u`.`id`, `u`.`name`,`u`.`username` as `phone`, `u`.`email`')
				->from('`#__users` AS `u`')
                ->leftJoin('`rgzbn_users_dealer_id_map` as `dm` on `dm`.`user_id` = `u`.`id`')
				->innerJoin('`#__user_usergroup_map` AS `g` ON `g`.`user_id` = `u`.`id`')
				->where("`g`.`group_id` = $group_id")
                ->order("`u`.`id`,`u`.`name`");
			$db->setQuery($query);

			$items = $db->loadObjectList();
			return $items;
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}

	function getUserByEmailAndUsername($email,$username){
	    try{
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query
                ->select('`u`.`id`')
                ->from('`#__users` AS `u`')
                ->where("email = '$email' and username = '$username'");
            $db->setQuery($query);
            $result = $db->loadObject();
            return $result;
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function getUsersByGroupAndDealer($group_id,$dealer_id){
	    try{
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query
                ->select('DISTINCT `u`.`id`, `u`.`name`, `u`.`email`')
                ->from('`#__users` AS `u`')
                ->leftJoin('`rgzbn_users_dealer_id_map` as `dm` on `dm`.`user_id` = `u`.`id`')
                ->innerJoin('`#__user_usergroup_map` AS `g` ON `g`.`user_id` = `u`.`id`')
                ->where("(`g`.`group_id` in ($group_id) and u.dealer_id = $dealer_id) OR (dm.dealer_id = $dealer_id AND dm.group_id IN ($group_id))");
            $db->setQuery($query);
            //throw new Exception($query);

            $items = $db->loadObjectList();
            return $items;
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function getUsersByGroupsAndDealer($groups, $dealer_id) {
	    try {
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query
                ->select('DISTINCT `u`.`id`, `u`.`name`, `u`.`email`')
                ->from('`#__users` AS `u`')
                ->leftJoin('`rgzbn_users_dealer_id_map` as `dm` on `dm`.`user_id` = `u`.`id`')
                ->innerJoin('`#__user_usergroup_map` AS `g` ON `g`.`user_id` = `u`.`id`')
                ->where("(`g`.`group_id` IN ($groups) and u.dealer_id = $dealer_id) OR (`dm`.`dealer_id` = $dealer_id AND `dm`.`group_id` in($groups))");
            $db->setQuery($query);
            $items = $db->loadObjectList();
            return $items;
        } catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function addGroup($userId,$group){
	    try{
	        $db= JFactory::getDbo();
	        $query = $db->getQuery(true);
            $query
                ->insert('rgzbn_user_usergroup_map')
                ->columns('`user_id`,`group_id`')
                ->values("$userId,$group");
            $db->setQuery($query);
            $db->execute();
            return true;
        }
        catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function deleteGroup($group,$userId){
	    try{
            $db= JFactory::getDbo();
            $query = $db->getQuery(true);
            $query
                ->delete('rgzbn_user_usergroup_map')
                ->where("group_id = $group and user_id = $userId");
            $db->setQuery($query);
            $db->execute();
            return true;
        }
        catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }

    }

    function getManufacturersInfo(){
        try{
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query
                ->select('u.id,u.name,CONCAT(c.name,\', \',ui.address) AS address,ui.email')
                ->from('`rgzbn_users` AS u')
                ->innerJoin('`rgzbn_user_info` AS ui ON u.id = ui.user_id')
                ->innerJoin('`rgzbn_city` AS c ON c.id = ui.city_id ')
                ->where('u.dealer_type = 6');

            $db->setQuery($query);
            $items = $db->loadObjectList();
            return $items;
        }
        catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function saveUserCity($cityId,$userId){
	    try{
	        $db = JFactory::getDbo();
	        $query = $db->getQuery(true);
	        $query
                ->insert('`rgzbn_user_info`')
                ->columns('`user_id`,`city_id`')
                ->values("$userId,$cityId");
	        $db->setQuery($query);
	        $db->execute();
        }
        catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function getUserByUsername($username){
	    try{
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query
                ->select("*")
                ->from('`rgzbn_users`')
                ->where("username = '$username'");
            $db->setQuery($query);
            $user = $db->loadObject();
            return $user;
        }
        catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function setVerificationCode($userId,$code){
	    try{
            $date = date('Y-m-d H:i:s');
	        if(empty($code)){
	            $code = '(NULL)';
	            $date = '(NULL)';
            }
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query
                ->update('`rgzbn_users`')
                ->set("`verification_code`= $code")
                ->set("code_creation_time = '$date'")
                ->where("id = $userId");
            $db->setQuery($query);
            $db->execute();
            return true;
        }
        catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }

    }

    function getVisitors($date_from,$date_to){
	    try{
	        $db = JFactory::getDbo();
	        $query = $db->getQuery(true);
	        $query
                ->select("u.id,u.name,u.associated_client,DATE_FORMAT(DATE_ADD(u.lastVisitDate, INTERVAL 3 HOUR),'%d.%m.%Y %H:%i') AS visit_date,u.username,c.name AS city1,di.city,GROUP_CONCAT(g.title) AS groups")
                ->from('`rgzbn_users` AS u')
                ->leftJoin('`rgzbn_gm_ceiling_dealer_info` AS di ON u.id = di.dealer_id')
                ->leftJoin('`rgzbn_user_usergroup_map` AS map ON u.id = map.user_id AND group_id NOT IN (1,2,3,4,5,6,7,8,9,10,25)')
                ->leftJoin('`rgzbn_usergroups` AS g ON map.group_id = g.id ')
                ->leftJoin('`rgzbn_user_info` AS ui ON u.id = ui.user_id')
                ->leftJoin('`rgzbn_city` AS c ON c.id = ui.city_id')
                ->where("u.lastVisitDate BETWEEN '$date_from 00:00:00' AND '$date_to 23:59:59'")
                ->order('u.lastVisitDate DESC')
                ->group('u.id');
	        $db->setQuery($query);
	        $items = $db->loadObjectList();
	        return $items;
        }
        catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function getUserCity($userId){
	    try{
	        $db = JFactory::getDbo();
	        $query = $db->getQuery(true);
	        $query
                ->select('city_id')
                ->from('`rgzbn_user_info`')
                ->where("user_id = $userId");
	        $db->setQuery($query);
	        $result = $db->loadObject();
	        return $result;
        }
        catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function getDealerUsers($dealerId){
	    try{
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query
                ->select('DISTINCT `u`.`id`,`u`.`name`,`u`.`dop_number`,GROUP_CONCAT( DISTINCT `g`.`title`) as groups,GROUP_CONCAT(`g`.`id`)')
                ->from('`rgzbn_users` AS `u`')
                ->leftJoin('`rgzbn_users_dealer_id_map` as `dm` on `dm`.`user_id` = `u`.`id`')
                ->leftJoin('`rgzbn_user_usergroup_map` AS `map` ON `map`.`user_id` = `u`.`id`')
                ->leftJoin('`rgzbn_usergroups` AS `g` ON `g`.`id` = `map`.`group_id` OR `g`.`id` = `dm`.`group_id`')
                ->where("(`u`.`dealer_id` = $dealerId AND `g`.`id` IN(11,12,13,14,21)) OR (`dm`.`dealer_id` = $dealerId and dm.group_id IN (11,12,13,14,21))")
                ->group('`u`.`id`');
            $db->setQuery($query);
            $items = $db->loadObjectList();
            return $items;
        }
        catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function updateDopNumber($userId,$dopNum){
	    try{
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            if(empty($dopNum)){
                $setStr = 'dop_number = NULL ';
            }
            else{
                $setStr = "dop_number = '$dopNum'";
            }
            $query
               ->update('`rgzbn_users`')
                ->set($setStr)
                ->where("id = $userId");
            $db->setQuery($query);
            $db->execute();
            return true;
        }
        catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function addGroupToExistUser($phone,$dealerId,$group){
	    try{
	        $mapModel = Gm_ceilingHelpersGm_ceiling::getModel('users_dealer_id_map');
            $oldUser = $this->getUserByUsername($phone);
            $groups = array_diff(JFactory::getUser($oldUser->id)->groups,SYSTEM_GROUPS);
            $savedGroups = $mapModel->getSavedGroups($oldUser->id);
            $groupsToSave = array_diff($groups,$savedGroups);

            if(!empty($groupsToSave)){
                foreach($groupsToSave as $groupId){
                    $mapModel->saveDealerIdMap($oldUser->id,$oldUser->dealer_id,$groupId,$oldUser->dealer_type);
                }
            }
            /*сохраняем новую группу*/
            if(!in_array($group,$savedGroups)) {
                $mapModel->saveDealerIdMap($oldUser->id, $dealerId, $group, $oldUser->dealer_type);
            }
            return $oldUser->id;
        }
        catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function updateUsersData($data){
	    try{
	        if(!empty($data)){
	            $db = JFactory::getDbo();
	            $query = $db->getQuery(true);
	            $query
                    ->update('rgzbn_users')
                    ->set("dealer_id = $data->dealer_id")
                    ->set("dealer_type = $data->dealer_type")
                    ->where("id = $data->user_id");
	            $db->setQuery($query);
	            $db->execute();
	            return true;
            }
        }
        catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function getGroupsByParentGroup($parentId){
	    try{
	        $result = [];
            if(!empty($parentId)){
                $db = JFactory::getDbo();
                $query = $db->getQuery(true);
                $query
                    ->select('id,title')
                    ->from('`rgzbn_usergroups`')
                    ->where("parent_id = $parentId")
                    ->order('id');
                $db->setQuery($query);
                $result = $db->loadObjectList();
            }
            return $result;
        }
        catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function getUserInGroups($dealerId,$groups){
	    try{
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $subquery = $db->getQuery(true);
            $subquery
                ->select('`u`.`id`, `u`.`name`, `u`.`username`,`u`.`email`,IF(ISNULL(g.group_id) OR g.group_id = 2,dm.group_id,g.group_id) AS group_id')
                ->from('`rgzbn_users` AS `u`')
                ->leftJoin('`rgzbn_users_dealer_id_map` AS `dm` ON `dm`.`user_id` = `u`.`id`')
                ->innerJoin('`rgzbn_user_usergroup_map` AS `g` ON `g`.`user_id` = `u`.`id`')
                ->where("(`g`.`group_id` IN ($groups) AND u.dealer_id = $dealerId) OR (`dm`.`dealer_id` = $dealerId AND `dm`.`group_id` IN($groups))");

            $query
                ->select('ug.id,ug.title,concat(\'[\',GROUP_CONCAT(CONCAT(\'{"id":"\',u1.id,\'","name":"\',u1.name,\'","phone":"\',u1.username,\'"}\')),\']\') AS users')
                ->from('`rgzbn_usergroups` AS ug')
                ->leftJoin("($subquery) AS u1 ON u1.group_id = ug.id")
                ->where("ug.id IN($groups)")
                ->group('ug.id');
            $db->setQuery($query);
            $data = $db->loadObjectList();
            $result = [];
            if(!empty($data)){
                foreach ($data as $item){
                    $result[$item->id] = (object)[
                        "id" => $item->id,
                        "title" => $item->title,
                        "users" => json_decode($item->users),
                        "count" => count(json_decode($item->users))
                    ];
                }
            }
            return $result;
        }
        catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function deleteUserFromGroup($userId,$groupId,$dealerId){
	    try{
	        $result = null;
	        if(!empty($userId) && !empty($groupId) && !empty($dealerId)) {
                $db = JFactory::getDbo();
                $query = $db->getQuery(true);
                $query
                    ->delete('`rgzbn_users_dealer_id_map`')
                    ->where("user_id = $userId AND group_id = $groupId AND dealer_id = $dealerId");
                $db->setQuery($query);
                $db->execute();
                $result = $db->getAffectedRows();
            }
	        return $result;
        }
        catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function createMountServiceUser($dealer){
	    try{
	        $db = JFactory::getDbo();
	        $query = $db->getQuery(true);
	        $query
                ->select('id,name')
                ->from('`rgzbn_users`')
                ->where("dealer_id = $dealer->id and name = 'МС $dealer->name'");
	        $db->setQuery($query);
	        $msUser = $db->loadObject();
	        if(empty($msUser)){
                $query = $db->getQuery(true);
                $query
                    ->insert('`rgzbn_users`')
                    ->columns('name,dealer_id')
                    ->values("'МС $dealer->name',$dealer->id");
                $db->setQuery($query);
                $db->execute();
                $userId = $db->insertId();
            }
	        else{
	            $userId = $msUser->id;
            }

            $this->addGroup($userId,26);
            return true;
        }
        catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function updateNameById($id,$name){
    	try{
	        $db = JFactory::getDbo();
	        $query = $db->getQuery(true);
	        $query
                ->update('`rgzbn_users`')
                ->set("`name` = '$name'")
                ->where("`id`=$id");
	        $db->setQuery($query);
	        $db->execute();
            return true;
        }
        catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }	
    }
}