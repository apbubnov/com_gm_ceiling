<?php

/**
 * @version    CVS: 0.1.7
 * @package    Com_Gm_ceiling
 * @author     SpectralEye <Xander@spectraleye.ru>
 * @copyright  2016 SpectralEye
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

jimport('joomla.application.component.modellist');

/**
 * Methods supporting a list of Gm_ceiling records.
 *
 * @since  1.6
 */
class Gm_ceilingModelClients extends JModelList
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @see        JController
	 * @since      1.6
	 */
	public function __construct($config = array())
	{
		try
		{
			if (empty($config['filter_fields']))
			{
				$config['filter_fields'] = array(
					'id', 'a.id',
					'client_name', 'a.client_name',
					'client_data_id','a.client_data_id',
					'type_id','a.type_id',
					'dealer_id', 'a.dealer_id',
					'manager_id', 'a.manager_id'
				);
			}

			parent::__construct($config);
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   Elements order
	 * @param   string  $direction  Order direction
	 *
	 * @return void
	 *
	 * @throws Exception
	 *
	 * @since    1.6
	 */
	/*protected function populateState($ordering = null, $direction = null)
	{
		// Initialise variables.
		$app = JFactory::getApplication();

		$list = $app->getUserState($this->context . '.list');
		
		if (empty($list['ordering']))
{
	$list['ordering'] = 'ordering';
}

if (empty($list['direction']))
{
	$list['direction'] = 'asc';
}

		if (isset($list['ordering']))
		{
			$this->setState('list.ordering', $list['ordering']);
		}

		if (isset($list['direction']))
		{
			$this->setState('list.direction', $list['direction']);
		}

		// List state information.
		parent::populateState($ordering, $direction);
	}
*/
	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return   JDatabaseQuery
	 *
	 * @since    1.6
	 */
	protected function getListQuery()
	{
		try
		{
			// Create a new query object.
			$db    = $this->getDbo();
			$query = $db->getQuery(true);

			// Select the required fields from the table.
			$query
				->select(
					$this->getState(
						'list.select', ' a.*'
					)
				);
			$query->select('GROUP_CONCAT(b.phone SEPARATOR \', \') as client_contacts');		
			$query->from('`#__gm_ceiling_clients` AS a');
			$query->leftJoin('`#__gm_ceiling_clients_contacts` as b ON a.id = b.client_id ');
			$user = JFactory::getUser();
			$groups = $user->get('groups');
			
			$query->where('a.dealer_id = '.$user->dealer_id);
			
			//Если менеджер дилера, то показывать только его клиентов
			if(in_array("13",$groups)){
				$query->where('a.manager_id = '.$user->id);
			}
			$search = $this->getState('filter.search');

			if (!empty($search))
			{
				if (stripos($search, 'id:') === 0)
				{
					$query->where('a.id = ' . (int) substr($search, 3));
				}
				else
				{
					$search = $db->Quote('%' . $db->escape($search, true) . '%');
					$query->where("(a.client_name LIKE $search OR b.phone LIKE $search)");
				}
			}
			$query->order('`id` DESC');
			$query->group('`id`');
			return $query;
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}

	function getClientsAndProjects($dealer_id = null,$type = null)
	{
		try
		{
			$user = JFactory::getUser();
			if($user->dealer_type == 8){
			    $dealer_id = $user->id;
            }
            if ($dealer_id == null) {
                $dealer_id = $user->dealer_id;
            }

            $db = $this->getDbo();

            $query = $db->getQuery(true);
            $query->select('`client`.`client_name` as `client_name`,
                		    `client`.`created`,
                		    `client`.`id` as `client_id`,
                		    `client`.`label_id`,
                		    `p`.`id` AS `project_id`,
                		    if(`p`.`deleted_by_user` <> 1, `p`.`project_info` , \'-\') as `address`,
                		    if(`p`.`deleted_by_user` <> 1, `s`.`title`, \'-\') as `status`,
                		    `s`.`id` as `status_id`,
                		    GROUP_CONCAT(DISTINCT `phone`.`phone` SEPARATOR \', \') as `client_contacts`,
                		    `lbs`.`color_code` as `label_color_code`
                		    ')
            	->from("`#__gm_ceiling_clients` as `client`")
                ->leftJoin('`#__gm_ceiling_clients_contacts` as `phone` ON `phone`.`client_id` = `client`.`id`')
                ->leftJoin("`#__gm_ceiling_projects` as `p` ON `p`.`id` = (SELECT MAX(`id`) FROM `rgzbn_gm_ceiling_projects` WHERE `client_id` = `client`.`id`)")
                ->leftJoin('`#__gm_ceiling_status` as `s` ON `p`.`project_status` = `s`.`id`')
                ->leftJoin('`#__gm_ceiling_clients_labels` as `lbs` ON `client`.`label_id` = `lbs`.`id`');
            if(!empty($type)){
                $query
                    ->select("`u`.`name`")
                    ->leftJoin('`#__users` as `u`  ON `client`.`dealer_id` = `u`.`id`')
                    ->where("`u`.`dealer_type` IN(8) AND `client`.`id` != `u`.`associated_client`");

            }
            else {
                $query
                    ->leftJoin('`#__users` as `u` ON `client`.`id` = `u`.`associated_client`')
                    ->where("`client`.`dealer_id` = $dealer_id AND (`u`.`associated_client` IS NULL OR (u.dealer_type = 2 AND `u`.`associated_client` IS NOT NULL)) AND `client`.`deleted_by_user` <> 1 AND `client`.`deleted_by_user` <> 1");
                }
            $query
                ->where('`client`.`deleted_by_user` <> 1')
                ->order('`client`.`id` DESC')
                ->group('`client`.`id`');


            $db->setQuery($query);
            $result = $db->loadObjectList();
            return $result;
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}

	public function getDealersClientsListQuery($dealer_id, $id)
	{
		try
		{
			// Create a new query object.
			$db    = $this->getDbo();
			$query = $db->getQuery(true);

			// Select the required fields from the table.
			$query
				->select(
					$this->getState(
						'list.select', ' a.*'
					)
				);
			$query->select('GROUP_CONCAT(b.phone SEPARATOR \', \') as client_contacts');		
			$query->from('`#__gm_ceiling_clients` AS a');
			$query->leftJoin('`#__gm_ceiling_clients_contacts` as b ON a.id = b.client_id ');
			$user = JFactory::getUser();
			$groups = $user->get('groups');
			
			$query->where("a.dealer_id = $dealer_id AND a.id != $id");
			
			//Если менеджер дилера, то показывать только его клиентов
			if(in_array("13",$groups)){
				$query->where('a.manager_id = '.$user->id);
			}
			$search = $this->getState('filter.search');

			if (!empty($search))
			{
				if (stripos($search, 'id:') === 0)
				{
					$query->where('a.id = ' . (int) substr($search, 3));
				}
				else
				{
					$search = $db->Quote('%' . $db->escape($search, true) . '%');
					$query->where('( a.client_name LIKE ' . $search . ')');
				}
			}
			$query->order('`id` DESC');
			$query->group('`id`');

			$db->setQuery($query);
			$db->execute();
			$items = $db->loadObjectList();
			return $items;
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}

	/**
	 * Method to get an array of data items
	 *
	 * @return  mixed An array of data on success, false on failure.
	 */
	public function getItems()
	{
		try
		{
			$items = parent::getItems();
			
			return $items;
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}


	public function getphones(){
		try
		{
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query
				->select("*")
				->from("#__gm_ceiling_clients");
			$db->setQuery($query);
			$db->execute();
			$items = $db->loadObjectList();
			return $items;
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}
	public function getItemsByOwnerID($id,$number)
	{
		try
		{
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query
				->select("id")
				->from("#__gm_ceiling_clients")
				->where("dealer_id = ". $db->quote($id));
			$db->setQuery($query);
			$db->execute();
			$items = $db->loadResult();
			return $items;
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}

	public function getItemsByClientName($client_name)
	{
		try
		{
			$user = JFactory::getUser();
			$db    = JFactory::getDbo();
			$client_name = $db->escape($client_name);
			$query = $db->getQuery(true);
			$query
				->select("a.*, GROUP_CONCAT(b.phone SEPARATOR ', ') as client_contacts")
				->from("`#__gm_ceiling_clients` as `a`")
				->leftJoin('`#__gm_ceiling_clients_contacts` as `b` ON a.id = b.client_id ')
				->innerJoin('`#__users` as u on a.dealer_id = u.id')
				->where("(`a`.`client_name` LIKE '%$client_name%' OR `b`.`phone` LIKE '%$client_name%') AND a.dealer_id = $user->dealer_id and `a`.`deleted_by_user` <> 1 and `a`.`id` <> `u`.`associated_client`")
				->order('`id` DESC')
				->group('`id`');
			$db->setQuery($query);
			$items = $db->loadObjectList();
			return $items;
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}

	public function getDealersByClientName($client_name, $manager_id, $city)
	{
		try
		{
			$user = JFactory::getUser();
			$db    = JFactory::getDbo();
			$client_name = $db->escape($client_name);

			/*$kp_cnt_query = $db->getQuery(true);
			$comments_cnt_query = $db->getQuery(true);
			$dealer_instr_cnt_query = $db->getQuery(true);*/
			$manager_query = $db->getQuery(true);
			/*$kp_cnt_query
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
				->where('di.user_id = u.id');*/
			$manager_query
				->select('`name`')
				->from('`#__users`')
				->where('id = c.manager_id');

			$query = $db->getQuery(true);
			$query
				->select("`c`.`id`, `c`.`client_name`, `c`.`dealer_id`, `c`.`manager_id`, `c`.`created`")
				->select("GROUP_CONCAT(DISTINCT `b`.`phone` SEPARATOR ', ') AS `client_contacts`, `u`.`dealer_type`, `i`.`city`")
				->select("GROUP_CONCAT(`#__user_usergroup_map`.`group_id` SEPARATOR ',') AS `groups`")
				->select("($manager_query) as manager_name")
				->from("`#__gm_ceiling_clients` as `c`")
				->innerjoin('`#__gm_ceiling_clients_contacts` AS `b` ON `c`.`id` = `b`.`client_id`')
				->innerJoin('`#__users` AS `u` ON `c`.`id` = `u`.`associated_client`')
				->leftJoin('`#__user_usergroup_map` ON `u`.`id`=`#__user_usergroup_map`.`user_id`')
				->leftJoin('`#__gm_ceiling_dealer_info` as `i` on `u`.`id` = `i`.`dealer_id`')
				->where("(`c`.`client_name` LIKE '%$client_name%' OR `b`.`phone` LIKE '%$client_name%') AND (`u`.`dealer_type` = 0 OR `u`.`dealer_type` = 1 OR `u`.`dealer_type` = 6) AND `#__user_usergroup_map`.`group_id`IN (14,27,28,29,30,31)")
				->order('`c`.`id` DESC')
				->group('`c`.`id`');
				if (!empty($manager_id))
				{
					$query->where("`c`.`manager_id` = $manager_id");
				}
				if (!empty($city))
				{
					$query->where("`i`.`city` = '$city'");
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

	public function getDealersByFilter($manager_id,$city,$status,$client_name,$limit,$select_size,$coop, $label_id,$date_from,$date_to){
		try{
			$db    = JFactory::getDbo();
			$client_name = $db->escape($client_name);
			$query = $db->getQuery(true);
			$manager_query = $db->getQuery(true);
			$label_filter = '';
			if (!empty($label_id)) {
				$label_filter = " AND `c`.`label_id` = $label_id";
			}

            $query
				->select("`c`.`id`, `c`.`client_name`, `c`.`dealer_id`, `c`.`manager_id`, `c`.`created`, `lbs`.`color_code`")
                ->select("SUM(IF(score.operation_type = 1,score.sum,0)) - SUM(IF(score.operation_type = 2,score.sum,0)) AS rest")
				->select("GROUP_CONCAT(DISTINCT `b`.`phone` SEPARATOR ', ') AS `client_contacts`, `u`.`dealer_type`, `i`.`city`")
				->select("GROUP_CONCAT(DISTINCT `#__user_usergroup_map`.`group_id` SEPARATOR ',') AS `groups`")
				->select("u1.name AS manager_name")
                ->select('COUNT(DISTINCT chs.id) as calls_count')
                ->select('CONCAT(\'[\',GROUP_CONCAT(DISTINCT CONCAT(\'{"type":"\',cs.title,\'","date":"\',DATE_FORMAT(chs.change_time,\'%d.%m.%Y %H:%i\'),\'"}\') ORDER BY chs.change_time DESC SEPARATOR \',\'),\']\') AS calls_history')
                ->from("`#__gm_ceiling_clients` as `c`")
				->innerjoin('`#__gm_ceiling_clients_contacts` AS `b` ON `c`.`id` = `b`.`client_id`')
				->innerJoin('`#__users` AS `u` ON `c`.`id` = `u`.`associated_client`')
				->innerJoin(' `rgzbn_users` AS `u1` ON `c`.`manager_id` = `u1`.`id`')
                ->leftJoin('`#__user_usergroup_map` ON `u`.`id`=`#__user_usergroup_map`.`user_id`')
				->leftJoin('`#__gm_ceiling_client_state_of_account` AS score ON score.client_id = c.id')
				->leftJoin('`#__gm_ceiling_dealer_info` as `i` on `u`.`id` = `i`.`dealer_id`')
                ->leftJoin('`#__gm_ceiling_clients_labels` as `lbs` on `c`.`label_id` = `lbs`.`id`')
                ->leftJoin('`rgzbn_gm_ceiling_calls_status_history` AS chs ON chs.client_id = c.id')
                ->leftJoin('`rgzbn_gm_ceiling_calls_status` AS cs ON cs.id = chs.status')
                ->leftJoin('`rgzbn_users_dealer_id_map` as `dm` on `dm`.`user_id` = `u`.`id`')
				->where("(`c`.`client_name` LIKE '%$client_name%' OR `b`.`phone` LIKE '%$client_name%') AND (`u`.`dealer_type` = 0 OR `u`.`dealer_type` = 1 OR `u`.`dealer_type` = 6) and `u`.`refused_to_cooperate` = $coop  $label_filter");
            if((!empty($limit) || $limit == 0)&&!empty($select_size)){
                $query->order("`c`.`id` DESC LIMIT $limit,$select_size");
            }
            else{
                $query->order("`c`.`id` DESC");
            }
            $query->group('`c`.`id`');
            if (!empty($manager_id))
            {
                $query->where("`c`.`manager_id` = $manager_id");
            }
            if (!empty($city))
            {
                $query->where("`i`.`city` = '$city'");
            }
            if(!empty($status)){
                $query->where("(`#__user_usergroup_map`.`group_id`IN ($status) OR dm.group_id = 14)");
            }
            else{
                $query->where("(`#__user_usergroup_map`.`group_id`IN (14,27,28,29,30,31)OR dm.group_id = 14)");
            }
            if(!empty($date_from) && !empty($date_to)){
                $query->where("chs.change_time BETWEEN '$date_from 00:00:00' AND '$date_to 23:59:59'");
            }
            if(!empty($date_from) && empty($date_to)){
                $query->where("chs.change_time >= '$date_from 00:00:00'");
            }
            if(empty($date_from) && !empty($date_to)) {
                $query->where("chs.change_time <= '$date_to 23:59:59'");
            }
            //throw new Exception($query);
			$db->setQuery($query);
			$items = $db->loadObjectList();
			/*if(count($items)){
				foreach ($items as $key => $dealer) {
				    $user_dealer = JFactory::getUser($dealer->dealer_id);
				    $items[$key]->min_canvas_price = $user_dealer->getFunctionCanvasesPrice("MIN");
				    $items[$key]->min_component_price = $user_dealer->getFunctionComponentsPrice("MIN");
				}
			}*/
			return $items;
		}
		catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}

	public function getDesignersByClientName($client_name, $designer_type, $label_id = null)
	{
		try {
			$user = JFactory::getUser();
			$db    = JFactory::getDbo();
			$client_name = $db->escape($client_name);
			$label_filter = '';
			if (!empty($label_id)) {
				$label_filter = ' AND `label_id` = '.$label_id;
			}
			$query = $db->getQuery(true);
			$query
				->select("`c`.*, GROUP_CONCAT(DISTINCT `b`.`phone` SEPARATOR ', ') AS `client_contacts`, `p`.`project_status`, `calls`.`id` AS `call_id`, `cl`.`color_code` AS `label_color_code`")
				->from("`#__gm_ceiling_clients` as `c`")
				->leftJoin('`#__gm_ceiling_clients_contacts` AS `b` ON `c`.`id` = `b`.`client_id`')
				->innerJoin('`#__users` AS `u` ON `c`.`id` = `u`.`associated_client`')
				->leftJoin('(SELECT `id`,`client_id`,`project_status` FROM `#__gm_ceiling_projects` ORDER BY `id` DESC) AS `p` ON `c`.`id` = `p`.`client_id`')
				->leftJoin('`#__gm_ceiling_callback` AS `calls` ON `c`.`id` = `calls`.`client_id`')
				->leftJoin('`#__gm_ceiling_clients_labels` AS `cl` ON `c`.`label_id` = `cl`.`id`')
				->where("(`c`.`client_name` LIKE '%$client_name%' OR `b`.`phone` LIKE '%$client_name%') AND `u`.`dealer_type` = $designer_type $label_filter")
				->order('`c`.`id` DESC')
				->group('`c`.`id`');
			$db->setQuery($query);
			$items = $db->loadObjectList();
			return $items;
		} catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}

	public function searchClients($search,$dealer_id = null)
	{
		try
		{
			$user = JFactory::getUser();
			$db    = JFactory::getDbo();
			$search = $db->escape($search);
			$query = $db->getQuery(true);
			$userQuery = $db->getQuery(true);
            $query
				->select("`c`.`id`, `c`.`client_name`, `c`.`created`, GROUP_CONCAT(DISTINCT `b`.`phone` SEPARATOR ', ') AS `client_contacts`, `p`.`project_info`, `u`.`dealer_type`, GROUP_CONCAT(DISTINCT `p`.`id` SEPARATOR ', ') AS `projects_ids`, GROUP_CONCAT(DISTINCT `d`.`contact` SEPARATOR ', ') AS `client_dop_contacts`")
                ->select('`s`.`title` as `status`')
                ->select('NULL as groups')
				->from("`#__gm_ceiling_clients` as `c`")
				->leftJoin('`#__gm_ceiling_clients_contacts` AS `b` ON `c`.`id` = `b`.`client_id`')
				->leftJoin('`#__users` AS `u` ON `c`.`id` = `u`.`associated_client`')
				->leftJoin('(SELECT `id`,`project_info`,`project_status`,`client_id` FROM `#__gm_ceiling_projects` ORDER BY `id` DESC) AS `p` ON `c`.`id` = `p`.`client_id`')/*подзапрос нужен чтоб вывести адрес последнего проекта*/
				->leftJoin('`#__gm_ceiling_clients_dop_contacts` AS `d` ON `c`.`id` = `d`.`client_id`')
                ->leftJoin('`#__gm_ceiling_status` as s on `s`.`id` = `p`.`project_status`')
				->where("(`c`.`client_name` LIKE '%$search%' OR
					`b`.`phone` LIKE '%$search%' OR
					`p`.`project_info` LIKE '%$search%' OR
					`p`.`id` LIKE '%$search%' OR
					`d`.`contact` LIKE '%$search%')")
				->group('`c`.`id`');
				if(!empty($dealer_id)){
				    $query->where("`c`.`dealer_id` = $dealer_id");
                }
            $userQuery
                ->select('IFNULL(`u`.associated_client,`u`.`id`) as `id`,`u`.`name`,`u`.`registerDate` AS `created`, `u`.`username` COLLATE utf8_general_ci,NULL,`u`.`dealer_type`,NULL,`u`.`email` COLLATE utf8_general_ci,NULL,GROUP_CONCAT(`g`.`title`) AS `groups`')
                ->from('`rgzbn_users` AS `u`')
                ->leftJoin('`rgzbn_user_usergroup_map` AS `map` ON `map`.`user_id` = `u`.`id` AND `map`.`group_id` NOT IN (1,2,3,4,5,6,7,8,9,10)')
                ->leftJoin('`rgzbn_usergroups` AS `g` ON `map`.`group_id` = `g`.`id`')
                ->where("`u`.`username` LIKE '%$search%'")
                ->group('`u`.`id`');
            $query->union($userQuery);
			$db->setQuery($query);
			$items = $db->loadObjectList();
			return $items;
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}

	public function getItem($id)
    {
    	try
    	{
	        $db    = JFactory::getDbo();
	        $query = $db->getQuery(true);
	        $query
	            ->select("*")
	            ->from("`#__gm_ceiling_clients`")
	            ->where("id = ".$db->quote($id));
	        $db->setQuery($query);
	        $item = $db->loadObject();
	        return $item;
	    }
	    catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function getDealer($id)
    {
    	try
    	{
	        $db    = JFactory::getDbo();
	        $query = $db->getQuery(true);
	        $query
	            ->select("u.*")
	            ->from("`#__gm_ceiling_clients` AS c")
	            ->join("LEFT", "`#__users` AS u ON u.id = c.dealer_id")
	            ->where("c.id = ". $db->quote($id));
	        $db->setQuery($query);
	        $item = $db->loadObject();
	        return $item;
	    }
	    catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
    
    public function updateDealerId($client_id,$dealer_id){
    	try
    	{
	        $db    = JFactory::getDbo();
	        $query = $db->getQuery(true);
	        $query
	            ->update("`#__gm_ceiling_clients`")
	            ->set("`dealer_id` = $dealer_id")
	            ->where("id = $client_id");
	        $db->setQuery($query);
	        $db->execute();
	    }
	    catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
	/**
	 * Overrides the default function to check Date fields format, identified by
	 * "_dateformat" suffix, and erases the field if it's not correct.
	 *
	 * @return void
	 */
	protected function loadFormData()
	{
		try
		{
			$app              = JFactory::getApplication();
			$filters          = $app->getUserState($this->context . '.filter', array());
			$error_dateformat = false;

			foreach ($filters as $key => $value)
			{
				if (strpos($key, '_dateformat') && !empty($value) && $this->isValidDate($value) == null)
				{
					$filters[$key]    = '';
					$error_dateformat = true;
				}
			}

			if ($error_dateformat)
			{
				$app->enqueueMessage(JText::_("COM_GM_CEILING_SEARCH_FILTER_DATE_FORMAT"), "warning");
				$app->setUserState($this->context . '.filter', $filters);
			}

			return parent::loadFormData();
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}

	/**
	 * Checks if a given date is valid and in a specified format (YYYY-MM-DD)
	 *
	 * @param   string  $date  Date to be checked
	 *
	 * @return bool
	 */
	private function isValidDate($date)
	{
		try
		{
			$date = str_replace('/', '-', $date);
			return (date_create($date)) ? JFactory::getDate($date)->format("Y-m-d") : null;
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}

	function getClientsAndprojectsData($dealer_id,$stage){
	    try{
            $db    = JFactory::getDbo();
            $query = 'SET SESSION group_concat_max_len = 1000000';
            $db->setQuery($query);
            $db->execute();
            $query = $db->getQuery(true);
            $query
                ->select("c.id AS client_id,COUNT(calc.id) AS  calcs_count,COUNT(cm.mounter_id) AS mounters_count,c.client_name,SUM(cm.sum) AS total_sum,CONCAT('[',GROUP_CONCAT(DISTINCT CONCAT('{\"calc_id\":\"',calc.id,'\",\"calc_status\":\"',IFNULL(cm.status_id,'-'),'\",\"defect_status\":\"',calc.defect_status,'\",\"title\":\"',calc.calculation_title,'\",\"sum\":\"',cm.sum,'\",\"mounter\":\"',IFNULL(cm.mounter_id,\"\"),'\"}') SEPARATOR ','),']') AS calcs,
                p.project_status,p.project_info,p.id,cm.mounter_id,SUM(0) AS n7,SUM(calc.n4) AS quadr,SUM(calc.n5) AS per")
                ->select('GROUP_CONCAT(DISTINCT calc.walls_height) AS heights,ifnull(SUM(calc.n5*calc.walls_height),0) AS walls_square')
                ->from("`rgzbn_gm_ceiling_clients` AS c")
                ->innerJoin("`rgzbn_gm_ceiling_projects` AS p ON p.client_id = c.id")
                ->innerJoin("`rgzbn_gm_ceiling_calculations` AS calc ON p.id = calc.project_id")
                ->leftJoin("`rgzbn_gm_ceiling_calcs_mount` AS cm ON cm.calculation_id = calc.id AND cm.stage_id = $stage")
                ->where("c.dealer_id = $dealer_id and p.deleted_by_user != 1")
                ->group("p.id");
            $db->setQuery($query);
            $items = $db->loadObjectList();
            $result = [];

            foreach ($items as $value){
                $calcsData = [];
                $calcsMounts = json_decode($value->calcs);
                if(!empty($calcsMounts)){
                    foreach ($calcsMounts as $calc){
                        $calcsData[$calc->calc_id]['id'] = $calc->calc_id;
                        $calcsData[$calc->calc_id]['title'] = $calc->title;
                        $calcsData[$calc->calc_id]['sum'] = $calc->sum;
                        $calcsData[$calc->calc_id]['defect_status'] = $calc->defect_status;
                        $calcsData[$calc->calc_id]['calc_status'] = $calc->calc_status;
                        if(!empty($calc->mounter)) {
                            $calcsData[$calc->calc_id]['mounters'][] =  JFactory::getUser($calc->mounter);
                        }
                    }
                }
                $result[$value->client_id]['id'] = $value->client_id;
                $result[$value->client_id]['name'] = $value->client_name;
                $result[$value->client_id]['projects'][] = (object)array("id"=>$value->id,
                                                                         "title"=>$value->project_info,
                                                                         "value"=>($stage == 3) ? $value->quadr : $value->per,
                                                                         "calcs"=>$calcsData,
                                                                         "sum"=>$value->total_sum,
                                                                         "status"=>$value->project_status,
                                                                         "calcs_count" => $value->calcs_count,
                                                                         "mounters_count" => $value->mounters_count,
                                                                         "walls_heights" => $value->heights,
                                                                         "walls_square" => $value->walls_square
                                                                        );
            }
            return $result;
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function getCommonData($dealer_id){
	    try{
            $db    = JFactory::getDbo();
            $query = $db->getQuery(true);
            $db->setQuery($query);
            $query
                ->select("DISTINCT calc.id,calc.n4 as s,calc.n5 as p,c.id AS client_id,c.client_name,SUM(cm.sum) AS mount_sum")
                ->from ('`rgzbn_gm_ceiling_calculations` AS calc')
                ->innerjoin('`rgzbn_gm_ceiling_projects` AS p ON p.id = calc.project_id')
                ->leftJoin('`rgzbn_gm_ceiling_calcs_mount` AS cm ON cm.calculation_id = calc.id')
                ->innerjoin('`rgzbn_gm_ceiling_clients` AS c ON p.client_id = c.id')
                ->where("c.dealer_id = $dealer_id and p.deleted_by_user != 1")
                ->group("calc.id");
            $db->setQuery($query);
            $items = $db->loadObjectList();
            $result = (object)array("perimeter"=>0,"quadrature"=>0,"mount_sum"=>0);

            foreach ($items as $item) {
                $result->perimeter += $item->p;
                $result->quadrature += $item->s;
                $result->mount_sum += $item->mount_sum;
            }
           /* $calculation_model = Gm_ceilingHelpersGm_ceiling::getModel('calculation');
            $analyticModel =Gm_ceilingHelpersGm_ceiling::getModel('analytic_dealers');
            $projects = explode(';',$projects);
            if(!empty($projects)){
                foreach ($projects as $id) {
                    if(!empty($id))
                        $calcs = $calculation_model->getDataForAnalytic($id);
                    if(!empty($calcs)){
                        foreach ($calcs as $calc) {
                            $data = $analyticModel->calculateSelfPrice($calc,0.05,$id);
                            $sum += $data["sum"];
                        }
                    }
                    $total_self_sum += $sum;
                }
            }*/
            $result->self_sum = 0;// $total_self_sum;
            return $result;
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function saveNewLabel($id, $title, $color_code, $dealer_id) {
    	try {
	        $db    = JFactory::getDbo();
	        $query = $db->getQuery(true);
	        if (!empty($id)) {
		        $query
		            ->update('`#__gm_ceiling_clients_labels`')
		            ->set("`title` = '$title', `color_code` = '$color_code'")
		            ->where("`dealer_id` = $dealer_id AND `id` = $id");
	        } else {
	        	$query
		            ->insert('`#__gm_ceiling_clients_labels`')
		            ->columns('`title`, `color_code`, `dealer_id`')
		            ->values("'$title', '$color_code', $dealer_id");
	        }
	        $db->setQuery($query);
	        $db->execute();
	        
	        $result = (object) array('insertId' => $db->insertid());
	        return $result;
	    } catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function saveClientLabel($client_id, $label_id) {
    	try {
	        $db    = JFactory::getDbo();
	        $query = $db->getQuery(true);
	        $query
	            ->update('`#__gm_ceiling_clients`')
	            ->set("`label_id` = $label_id")
	            ->where("`id` = $client_id");
	        $db->setQuery($query);
	        $db->execute();

	        $query = $db->getQuery(true);
        	$query
	            ->insert('`#__gm_ceiling_clients_labels_history`')
	            ->columns('`client_id`, `label_id`')
	            ->values("$client_id, $label_id");
	        $db->setQuery($query);
	        $db->execute();
	        
	        $result = (object) array('insertId' => $db->insertid());
	        return $result;
	    } catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function getClientsLabels($dealer_id) {
    	try {
	        $db    = JFactory::getDbo();
	        $query = $db->getQuery(true);
	        $query
	        	->select('`id`,
	        			  `title`,
	        			  `color_code`,
	        			  `dealer_id`')
	            ->from('`#__gm_ceiling_clients_labels`')
	            ->where("`dealer_id` = $dealer_id");
	        $db->setQuery($query);
	        $result = $db->loadObjectList();
	        return $result;
	    } catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function deleteLabel($id, $dealer_id) {
    	try {
	        $db    = JFactory::getDbo();
	        $query = $db->getQuery(true);
	        $query
	            ->delete('`#__gm_ceiling_clients_labels`')
	            ->where("`dealer_id` = $dealer_id AND `id` = $id");
	        $db->setQuery($query);
	        $db->execute();
	        return true;
	    } catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function getBuilderCommonData($builderId){
	    try{
            $db    = JFactory::getDbo();
            $query = 'SET SESSION group_concat_max_len  = 16384';
            $db->setQuery($query);
            $db->execute();
            $query = $db->getQuery(true);
            $query
                ->select('c.id,c.client_name,SUM(calc.n4) AS square,SUM(calc.n5) AS perimeter,CONCAT(\'[\',GROUP_CONCAT( DISTINCT CONCAT(\'{"project_id":"\',p.id,\'","name":"\',p.project_info,\'","status":"\',s.title,\'"}\') SEPARATOR \',\'),\']\') AS projects')
                ->from('`rgzbn_gm_ceiling_clients` AS c')
                ->innerJoin('`rgzbn_gm_ceiling_projects` AS p ON p.client_id = c.id')
                ->innerJoin('`rgzbn_gm_ceiling_status` AS s ON p.project_status = s.id')
                ->innerJoin('`rgzbn_gm_ceiling_calculations` AS calc ON calc.project_id = p.id')
                ->where("c.dealer_id = $builderId")
                ->group('c.id');
            $db->setQuery($query);
            $items = $db->loadObjectList();
            return $items;

        }
        catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function getClientsByIds($ids){
	    try{
	        $result = [];
	        if(!empty($ids)){
                $db    = JFactory::getDbo();
                $query = 'SET SESSION group_concat_max_len  = 16384';
                $db->setQuery($query);
                $db->execute();
                $query = $db->getQuery(true);
                $query
                    ->select('c.id,c.client_name,GROUP_CONCAT(cc.phone separator \'; \') as contacts')
                    ->from('`rgzbn_gm_ceiling_clients` as c')
                    ->leftJoin('`rgzbn_gm_ceiling_clients_contacts` as cc ON c.id = cc.client_id')
                    ->where("c.id in ($ids)")
                    ->group('c.id');
                $db->setQuery($query);
                $result = $db->loadObjectList();
	        }
            return $result;
        }
        catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
}
