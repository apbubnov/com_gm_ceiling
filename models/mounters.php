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
class Gm_ceilingModelMounters extends JModelList
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
					'ordering', 'a.ordering',
					'state', 'a.state',
					'created_by', 'a.created_by',
					'modified_by', 'a.modified_by',
					'team_title', 'a.team_title',
					'mounter_contacts', 'a.mounter_contacts',
					'dealer_id', 'a.dealer_id',
					'mounter_margin', 'a.mounter_margin',
					'mp1', 'a.mp1',
					'mp2', 'a.mp2',
					'mp3', 'a.mp3',
					'mp4', 'a.mp4',
					'mp5', 'a.mp5',
					'mp6', 'a.mp6',
					'mp7', 'a.mp7',
					'mp8', 'a.mp8',
					'mp9', 'a.mp9',
					'mp10', 'a.mp10',
					'mp11', 'a.mp11',
					'mp12', 'a.mp12',
					'mp13', 'a.mp13',
					'mp14', 'a.mp14',
					'mp15', 'a.mp15',
					'mp16', 'a.mp16',
					'mp17', 'a.mp17',
					'mt1', 'a.mt1',
					'mt2', 'a.mt2',
					'mt3', 'a.mt3',
					'mt4', 'a.mt4',
					'mt5', 'a.mt5',
					'mt6', 'a.mt6',
					'mt7', 'a.mt7',
					'mt8', 'a.mt8',
					'mt9', 'a.mt9',
					'mt10', 'a.mt10',
					'mt11', 'a.mt11',
					'mt12', 'a.mt12',
					'mt13', 'a.mt13',
					'mt14', 'a.mt14',
					'mt15', 'a.mt15',
					'mt16', 'a.mt16',
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
	protected function populateState($ordering = null, $direction = null)
	{
		try
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
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}

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
						'list.select', 'DISTINCT a.*'
					)
				);

			$query->from('`#__gm_ceiling_groups` AS a');
			/*
			// Join over the users for the checked out user.
			$query->select('uc.name AS editor');
			$query->join('LEFT', '#__users AS uc ON uc.id=a.checked_out');

			// Join over the created by field 'created_by'
			$query->join('LEFT', '#__users AS created_by ON created_by.id = a.created_by');

			// Join over the created by field 'modified_by'
			$query->join('LEFT', '#__users AS modified_by ON modified_by.id = a.modified_by');
			// Join over the foreign key 'dealer_id'
			$query->select('`#__gm_ceiling_dealers_2481843`.`dealer_name` AS dealers_fk_value_2481843');
			$query->join('LEFT', '#__gm_ceiling_dealers AS #__gm_ceiling_dealers_2481843 ON #__gm_ceiling_dealers_2481843.`id` = a.`dealer_id`');
	*/
			$user = JFactory::getUser();
			$groups = $user->get('groups');
			
			//Если менеджер дилера, то показывать дилерских клиентов
			//ИЛИ
			//Если дилер, то показывать дилерских клиентов
			if(in_array("13",$groups) || in_array("14",$groups)){
				$query->where('a.owner = '.$user->dealer_id);
			}
			$query->where('a.state = 1');

			// Filter by search in title
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
					$query->where('( a.team_title LIKE ' . $search . '  OR #__gm_ceiling_dealers_2481843.dealer_name LIKE ' . $search . ' )');
				}
			}
			

			// Add the list ordering clause.
			$orderCol  = $this->state->get('list.ordering');
			$orderDirn = $this->state->get('list.direction');

			if ($orderCol && $orderDirn)
			{
				$query->order($db->escape($orderCol . ' ' . $orderDirn));
			}

			return $query;
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
			
			foreach ($items as $item)
			{
				if (isset($item->dealer_id) && $item->dealer_id != '')
				{
					if (is_object($item->dealer_id))
					{
						$item->dealer_id = \Joomla\Utilities\ArrayHelper::fromObject($item->dealer_id);
					}

					$values = (is_array($item->dealer_id)) ? $item->dealer_id : explode(',', $item->dealer_id);
					$textValue = array();

					foreach ($values as $value)
					{
						$db = JFactory::getDbo();
						$query = $db->getQuery(true);
						$query
								->select('`#__gm_ceiling_dealers_2481843`.`dealer_name`')
								->from($db->quoteName('#__gm_ceiling_dealers', '#__gm_ceiling_dealers_2481843'))
							->where($db->quoteName('id') . ' = ' . $db->quote($db->escape($value)));
						$db->setQuery($query);
						$results = $db->loadObject();

						if ($results)
						{
							$textValue[] = $results->dealer_name;
						}
					}

					$item->dealer_id = !empty($textValue) ? implode(', ', $textValue) : $item->dealer_id;
				}

			}

			return $items;
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}
	
	//KM_CHANGED START
		
	public function getMounterByDealerId($dealer_id)
	{
		try
		{
			// Создаем новый query объект.
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
		 
			// Выбераем поля.
			$query->select('*');
		 
			$query->from('#__gm_ceiling_groups AS a');
		  
			$query->where('a.state = 1');
			if($dealer_id == 0) {
				$query->where('a.owner = 2');
			} else {
				$query->where('a.owner = '.$dealer_id);
			}
			
			$db->setQuery($query);	 
			return $db->loadObject();
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}

	public function getEmailMount($id)
	{
		try
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select('name, email');
			$query->from('#__users');
			$query->where("id in($id)");
			$db->setQuery($query);	 
			return $db->loadObject();
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}
	
	public function getFilteredItems($filter = "")
	{
		try
		{
			// Создаем новый query объект.
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
		 
			// Выбераем поля.
			$query->select('*');
		 
			$query->from('#__gm_ceiling_groups AS a');
		  
			$query->where('a.state = 1');
			
			if($filter) {
				$query->where($filter);
			}
			
			$db->setQuery($query);	 
			return $db->loadObjectList();
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}
	
	//KM_CHANGED END

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
