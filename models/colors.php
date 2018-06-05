<?php

/**
 * @version    CVS: 1.0.0
 * @package    Com_Gm_ceiling
 * @author     Mikhail  <vms@itctl.ru>
 * @copyright  2016 Mikhail 
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

jimport('joomla.application.component.modellist');

/**
 * Methods supporting a list of Gm_ceiling records.
 *
 * @since  1.6
 */
class Gm_ceilingModelColors extends JModelList
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
	                'title', 'a.title',
	                'file', 'a.file',
	                'hex', 'a.hex'
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
			$app  = JFactory::getApplication();
			$list = $app->getUserState($this->context . '.list');

			$ordering  = isset($list['filter_order'])     ? $list['filter_order']     : null;
			$direction = isset($list['filter_order_Dir']) ? $list['filter_order_Dir'] : null;
			
			//$list['limit']     = $app->input->getInt('limit', 20);
			$list['limit']     = 0;
			$list['start']     = 0;
			$list['ordering']  = $ordering;
			$list['direction'] = $direction;

			$app->setUserState($this->context . '.list', $list);
			$app->input->set('list', null);
			//$app->setUserState($this->context . 'list.start', 25);
			//$app->setUserState($this->context . 'list.limitstart', 25);
			//$app->setUserState('list.start', 25);
			//$app->setUserState('list.limitstart', 25);
			
			//JFactory::getApplication()->enqueueMessage(print_r($app->getUserState($this->context . '.list'), true) , 'error');

	        $app = JFactory::getApplication();

	        $ordering  = $app->getUserStateFromRequest($this->context . '.ordercol', 'filter_order', $ordering);
	        $direction = $app->getUserStateFromRequest($this->context . '.orderdirn', 'filter_order_Dir', $ordering);

	        $this->setState('list.ordering', $ordering);
	        $this->setState('list.direction', $direction);

	        $start = $app->getUserStateFromRequest($this->context . '.limitstart', 'limitstart', 0, 'int');
	        $limit = $app->getUserStateFromRequest($this->context . '.limit', 'limit', 0, 'int');

	        if ($limit == 0)
	        {
	            $limit = $app->get('list_limit', 0);
	        }

	        $this->setState('list.limit', $limit);
	        $this->setState('list.start', $start);
			
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

			$query->from('`#__gm_ceiling_colors` AS a');
			/*
			// Join over the users for the checked out user.
			$query->select('uc.name AS uEditor');
			$query->join('LEFT', '#__users AS uc ON uc.id=a.checked_out');

			// Join over the created by field 'created_by'
			$query->join('LEFT', '#__users AS created_by ON created_by.id = a.created_by');

			// Join over the created by field 'modified_by'
			$query->join('LEFT', '#__users AS modified_by ON modified_by.id = a.modified_by');
			// Join over the foreign key 'color_canvas'
			$query->select('`#__gm_ceiling_canvases_2512177`.`canvas_title` AS tags_fk_value_2512177');
			$query->select('`#__gm_ceiling_canvases_2512177`.`canvas_texture` AS canvas_texture');
			$query->join('LEFT', '#__gm_ceiling_canvases AS #__gm_ceiling_canvases_2512177 ON #__gm_ceiling_canvases_2512177.`id` = a.`color_canvas`');
			// Join over the foreign key 'canvas_texture'
			$query->select('`#__gm_ceiling_textures_2460714`.`texture_title` AS textures_fk_value_2460714');
			$query->join('LEFT', '#__gm_ceiling_textures AS #__gm_ceiling_textures_2460714 ON #__gm_ceiling_textures_2460714.`id` = `#__gm_ceiling_canvases_2512177`.`canvas_texture`');
			
			if (!JFactory::getUser()->authorise('core.edit', 'com_gm_ceiling'))
			{
				$query->where('a.state = 1');
			}
	*/

	        $query->select('canvases.id AS canvases_id');
	        $query->join('LEFT', '#__gm_ceiling_canvases AS canvases ON canvases.color_id = a.id');

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
					$query->where('( a.title LIKE ' . $search . ' )');
				}
			}


			// Filtering color_title

			// Filtering color_canvas
			$filter_color_canvas = $this->state->get("filter.color_canvas");
			if ($filter_color_canvas)
			{
				$query->where("canvases_id = '".$db->escape($filter_color_canvas)."'");
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
	//			if (isset($item->color_canvas) && $item->color_canvas != '')
	//			{
	//				if (is_object($item->color_canvas))
	//				{
	//					$item->color_canvas = \Joomla\Utilities\ArrayHelper::fromObject($item->color_canvas);
	//				}
	//
	//				$values = (is_array($item->color_canvas)) ? $item->color_canvas : explode(',', $item->color_canvas);
	//				$textValue = array();
	//
	//				foreach ($values as $value)
	//				{
	//					$db = JFactory::getDbo();
	//					$query = $db->getQuery(true);
	//					$query
	//							->select('`#__gm_ceiling_canvases_2512177`.`name`')
	//							->from($db->quoteName('#__gm_ceiling_canvases', '#__gm_ceiling_canvases_2512177'))
	//						->where($db->quoteName('id') . ' = ' . $db->quote($db->escape($value)));
	//					$db->setQuery($query);
	//					$results = $db->loadObject();
	//
	//					if ($results)
	//					{
	//						$textValue[] = $results->name;
	//					}
	//				}
	//
	//				$item->name = !empty($textValue) ? implode(', ', $textValue) : $item->name;
	//			}
	//
	//			if (isset($item->canvas_texture) && $item->canvas_texture != '')
	//			{
	//				if (is_object($item->canvas_texture))
	//				{
	//					$item->canvas_texture = \Joomla\Utilities\ArrayHelper::fromObject($item->canvas_texture);
	//				}
	//
	//				$values = (is_array($item->canvas_texture)) ? $item->canvas_texture : explode(',', $item->canvas_texture);
	//				$textValue = array();
	//
	//				foreach ($values as $value)
	//				{
	//					$db = JFactory::getDbo();
	//					$query = $db->getQuery(true);
	//					$query
	//							->select('`#__gm_ceiling_textures_2460714`.`texture_title`')
	//							->from($db->quoteName('#__gm_ceiling_textures', '#__gm_ceiling_textures_2460714'))
	//						->where($db->quoteName('id') . ' = ' . $db->quote($db->escape($value)));
	//					$db->setQuery($query);
	//					$results = $db->loadObject();
	//
	//					if ($results)
	//					{
	//						$textValue[] = $results->texture_title;
	//					}
	//				}
	//
	//				$item->canvas_texture = !empty($textValue) ? implode(', ', $textValue) : $item->canvas_texture;
	//			}


			}
	        $db = JFactory::getDbo();
	        $query = $db->getQuery(true);
	        $query
	            ->select('CONCAT( mnfct.name , \' \', mnfct.country, \' \', canvases.width ) AS full_name,canvases.count' )
	            ->from('`#__gm_ceiling_canvases` AS canvases')
	            ->join('LEFT', '`#__gm_ceiling_canvases_manufacturers` as mnfct on canvases.manufacturer_id = mnfct.id')
	            ->select('textures.texture_title AS texture_title')
	            ->join('LEFT', '`#__gm_ceiling_textures` AS textures ON canvases.texture_id = textures.id')
	            ->select('colors.title AS colors_title, colors.file AS file, colors.id AS id ')
	            ->join('LEFT', '`#__gm_ceiling_colors` AS colors ON canvases.color_id = colors.id')
	            ->where('canvases.color_id IS NOT NULL');

	            $db->setQuery($query);
	            $items = $db->loadObjectList();

			return $items;
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}
	
	public function getFilteredItems($filter)
	{
		try
		{
			// Создаем новый query объект.
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			/*SELECT * FROM `#__gm_ceiling_colors` AS `a` INNER JOIN `#__gm_ceiling_canvases` AS `b` 
			ON `a`.`id` = `b`.`color_id` WHERE `b`.`count` > 0 AND `b`.`texture_id` = 2*/
		 //throw new Exception($filter, 11);
			// Выбераем поля.
			$query->select('a.id');
			$query->select('a.title');
			$query->select('a.file');
			$query->from('#__gm_ceiling_colors AS a');
			$query->innerJoin('`#__gm_ceiling_canvases` AS `b` ON `a`.`id` = `b`.`color_id`');
			//$query->where('a.state = 1');
			//throw new Exception($query, 11);
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

	public function getColorFile($id){
		try
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select('a.file');
			$query->from('#__gm_ceiling_colors AS a');
			$query->where('a.id = '.$id);
			$db->setQuery($query);	 
			return $db->loadObject();
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}

	public function getColorTitle($id){
		try
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select('a.title');
			$query->from('#__gm_ceiling_colors AS a');
			$query->where('a.id = '.$id);
			$db->setQuery($query);	 
			return $db->loadObject();
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
