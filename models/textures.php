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
class Gm_ceilingModelTextures extends JModelList
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
	                'texture_title', 'a.texture_title',
	                'texture_colored', 'a.texture_colored'
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
		/*// Initialise variables.
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
		parent::populateState($ordering, $direction);*/
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

			$query->from('`#__gm_ceiling_textures` AS a');
			/*
			// Join over the users for the checked out user.
			$query->select('uc.name AS editor');
			$query->join('LEFT', '#__users AS uc ON uc.id=a.checked_out');

			// Join over the created by field 'created_by'
			$query->join('LEFT', '#__users AS created_by ON created_by.id = a.created_by');

			// Join over the created by field 'modified_by'
			$query->join('LEFT', '#__users AS modified_by ON modified_by.id = a.modified_by');
			// Join over the foreign key 'texture_type'
			$query->select('`#__gm_ceiling_types_2460657`.`type_title` AS types_fk_value_2460657');
			$query->join('LEFT', '#__gm_ceiling_types AS #__gm_ceiling_types_2460657 ON #__gm_ceiling_types_2460657.`id` = a.`texture_type`');
			
			if (!JFactory::getUser()->authorise('core.edit', 'com_gm_ceiling'))
			{
				$query->where('a.state = 1');
			}*/

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
					$query->where('( a.texture_title LIKE ' . $search . ' )');
				}
			}
			

			// Filtering texture_colored
			$filter_texture_colored = $this->state->get("filter.texture_colored");
			if ($filter_texture_colored != '') {
				$query->where("a.texture_colored = '".$db->escape($filter_texture_colored)."'");
			}
	/*
			// Filtering texture_type
			$filter_texture_type = $this->state->get("filter.texture_type");
			if ($filter_texture_type)
			{
				$query->where("a.texture_type = '".$db->escape($filter_texture_type)."'");
			}

			// Add the list ordering clause.
			$orderCol  = $this->state->get('list.ordering');
			$orderDirn = $this->state->get('list.direction');

			if ($orderCol && $orderDirn)
			{
				$query->order($db->escape($orderCol . ' ' . $orderDirn));
			}
	*/
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

	            $item->texture_colored = JText::_('COM_GM_CEILING_TEXTURES_TEXTURE_COLORED_OPTION_' . strtoupper($item->texture_colored));
	            /*if (isset($item->texture_type) && $item->texture_type != '')
				{
					if (is_object($item->texture_type))
					{
						$item->texture_type = \Joomla\Utilities\ArrayHelper::fromObject($item->texture_type);
					}

					$values = (is_array($item->texture_type)) ? $item->texture_type : explode(',', $item->texture_type);
					$textValue = array();

					foreach ($values as $value)
					{
						$db = JFactory::getDbo();
						$query = $db->getQuery(true);
						$query
								->select('`#__gm_ceiling_types_2460657`.`type_title`')
								->from($db->quoteName('#__gm_ceiling_types', '#__gm_ceiling_types_2460657'))
							->where($db->quoteName('id') . ' = ' . $db->quote($db->escape($value)));
						$db->setQuery($query);
						$results = $db->loadObject();

						if ($results)
						{
							$textValue[] = $results->type_title;
						}
					}

					$item->texture_type = !empty($textValue) ? implode(', ', $textValue) : $item->texture_type;
				}
	            */
			}

			return $items;
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}
	
	//KM_CHANGED START
	
	public function getFilteredItems($filter = "")
	{
		try
		{
			// Создаем новый query объект.
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select('distinct a.id');
			$query->select('a.texture_title');
			$query->select('a.texture_colored');
			$query->from('#__gm_ceiling_textures AS a');
			$query->innerJoin('#__gm_ceiling_canvases AS b ON a.id = b.texture_id');
			$query->where( 'b.count > 0');
			
			$db->setQuery($query);	 
			return $db->loadObjectList();
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}

	function getFilteredData($filter){
        try
        {
            // Создаем новый query объект.
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query->select('distinct a.id');
            $query->select('a.texture_title');
            $query->from('#__gm_ceiling_textures AS a');
            $query->where($filter);

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

	public function save($title,$is_colored){
	    try{
            $db    = JFactory::getDbo();
            $query = $db->getQuery(true);
            $db->setQuery($query);
            $query
                ->insert('`rgzbn_gm_ceiling_textures`')
                ->columns('`texture_title`,`texture_colored`')
                ->values("'$title',$is_colored");
            $db->setQuery($query);
            $db->execute();
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
}
