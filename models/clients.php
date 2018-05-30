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

	function getClientsAndProjects($dealer_id = null)
	{
		try
		{
			$user = JFactory::getUser();
            if ($dealer_id == null) {
                $dealer_id = $user->dealer_id;
            }

            $db = $this->getDbo();
            $query = $db->getQuery(true);
            $query->from("`#__gm_ceiling_clients` as `client`")
                ->join("LEFT", "`#__gm_ceiling_clients_contacts` as `phone` ON `phone`.`client_id` = `client`.`id`")
                ->join("LEFT", "(SELECT * FROM `#__gm_ceiling_projects` ORDER BY `id` DESC) as `p` ON `p`.`client_id` = `client`.`id`")
                ->join("LEFT", "`#__users` as `u` ON `client`.`id` = `u`.`associated_client`")
                ->join("LEFT", "`#__gm_ceiling_status` as `s` ON `p`.`project_status` = `s`.`id`")
                ->select("`p`.`id`, `p`.`project_info` as `address`, `s`.`title` as `status`, `s`.`id` as `status_id`")
                ->select("`client`.`client_name` as `client_name`, `client`.`created`, `client`.`id` as `client_id`")
                ->select("GROUP_CONCAT(distinct `phone`.`phone` SEPARATOR ', ') as `client_contacts`")
                ->where('`client`.`deleted_by_user` <> 1')
                ->order("`client`.`id` DESC")
                ->group("`client`.`id`");

            $query->where("`client`.`dealer_id` = $dealer_id AND `u`.`associated_client` IS NULL");

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
				->where("(`a`.`client_name` LIKE '%$client_name%' OR `b`.`phone` LIKE '%$client_name%') AND a.dealer_id = $user->dealer_id")
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

			$kp_cnt_query = $db->getQuery(true);
			$comments_cnt_query = $db->getQuery(true);
			$dealer_instr_cnt_query = $db->getQuery(true);
			$manager_query = $db->getQuery(true);
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
			$manager_query
				->select('`name`')
				->from('`#__users`')
				->where('id = c.manager_id');

			$query = $db->getQuery(true);
			$query
				->select("`c`.`id`, `c`.`client_name`, `c`.`dealer_id`, `c`.`manager_id`, `c`.`created`")
				->select("GROUP_CONCAT(`b`.`phone` SEPARATOR ', ') AS `client_contacts`, `u`.`dealer_type`, `i`.`city`")
				->select("($kp_cnt_query) as kp_cnt")
				->select("($comments_cnt_query) as cmnt_cnt")
				->select("($dealer_instr_cnt_query) as inst_cnt")
				->select("($manager_query) as manager_name")
				->from("`#__gm_ceiling_clients` as `c`")
				->leftJoin('`#__gm_ceiling_clients_contacts` AS `b` ON `c`.`id` = `b`.`client_id`')
				->innerJoin('`rgzbn_users` AS `u` ON `c`.`id` = `u`.`associated_client`')
				->leftJoin('`#__user_usergroup_map` ON `u`.`id`=`rgzbn_user_usergroup_map`.`user_id`')
				->leftJoin('`#__gm_ceiling_dealer_info` as `i` on `u`.`id` = `i`.`dealer_id`')
				->where("(`c`.`client_name` LIKE '%$client_name%' OR `b`.`phone` LIKE '%$client_name%') AND (`u`.`dealer_type` = 0 OR `u`.`dealer_type` = 1 OR `u`.`dealer_type` = 6) AND `#__user_usergroup_map`.`group_id`=14")
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

	public function getDesignersByClientName($client_name,$designer_type)
	{
		try
		{
			$user = JFactory::getUser();
			$db    = JFactory::getDbo();
			$client_name = $db->escape($client_name);
			$query = $db->getQuery(true);
			$query
				->select("`c`.*, GROUP_CONCAT(DISTINCT `b`.`phone` SEPARATOR ', ') AS `client_contacts`, `p`.`project_status`, `calls`.`id` AS `call_id`, `u`.`refused_to_cooperate`")
				->from("`#__gm_ceiling_clients` as `c`")
				->leftJoin('`#__gm_ceiling_clients_contacts` AS `b` ON `c`.`id` = `b`.`client_id`')
				->innerJoin('`#__users` AS `u` ON `c`.`id` = `u`.`associated_client`')
				->leftJoin('(SELECT `id`,`client_id`,`project_status` FROM `#__gm_ceiling_projects` ORDER BY `id` DESC) AS `p` ON `c`.`id` = `p`.`client_id`')
				->leftJoin('`#__gm_ceiling_callback` AS `calls` ON `c`.`id` = `calls`.`client_id`')
				->where("(`c`.`client_name` LIKE '%$client_name%' OR `b`.`phone` LIKE '%$client_name%') AND `u`.`dealer_type` = $designer_type")
				->order('`c`.`id` DESC')
				->group('`c`.`id`');
			$db->setQuery($query);
			$items = $db->loadObjectList();
			return $items;
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}

	public function searchClients($search)
	{
		try
		{
			$user = JFactory::getUser();
			$db    = JFactory::getDbo();
			$search = $db->escape($search);
			$query = $db->getQuery(true);
			$query
				->select("`c`.`id`, `c`.`client_name`, `c`.`created`, GROUP_CONCAT(DISTINCT `b`.`phone` SEPARATOR ', ') AS `client_contacts`, `p`.`project_info`, `u`.`dealer_type`, GROUP_CONCAT(DISTINCT `p`.`id` SEPARATOR ', ') AS `projects_ids`, GROUP_CONCAT(DISTINCT `d`.`contact` SEPARATOR ', ') AS `client_dop_contacts`")
				->from("`#__gm_ceiling_clients` as `c`")
				->leftJoin('`#__gm_ceiling_clients_contacts` AS `b` ON `c`.`id` = `b`.`client_id`')
				->leftJoin('`#__users` AS `u` ON `c`.`id` = `u`.`associated_client`')
				->leftJoin('(SELECT `id`,`project_info`,`client_id` FROM `#__gm_ceiling_projects` ORDER BY `id` DESC) AS `p` ON `c`.`id` = `p`.`client_id`')/*подзапрос нужен чтоб вывести адрес последнего проекта*/
				->leftJoin('`#__gm_ceiling_clients_dop_contacts` AS `d` ON `c`.`id` = `d`.`client_id`')
				->where("`c`.`client_name` LIKE '%$search%' OR
					`b`.`phone` LIKE '%$search%' OR
					`p`.`project_info` LIKE '%$search%' OR
					`p`.`id` LIKE '%$search%' OR
					`d`.`contact` LIKE '%$search%'")
				->group('`c`.`id`');
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
}
