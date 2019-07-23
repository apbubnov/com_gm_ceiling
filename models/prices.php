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
class Gm_ceilingModelPrices extends JModelList
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
	                'component_id', 'a.component_id',
	                'title', 'a.title',
	                'price', 'a.price',
	                'count', 'a.count',
	                'date', 'a.date',
	                'user_accepted_id', 'a.user_accepted_id'
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

			$query->from('`#__gm_ceiling_components_option` AS a');


	        $query->select('CONCAT( component.title , \' \', a.title , \' - \', component.unit ) AS full_name');
	        $query->join('NATURAL', '#__gm_ceiling_components AS component');
			/*
			// Join over the users for the checked out user.
			$query->select('uc.name AS editor');
			$query->join('LEFT', '#__users AS uc ON uc.id=a.checked_out');

			// Join over the created by field 'created_by'
			$query->join('LEFT', '#__users AS created_by ON created_by.id = a.created_by');

			// Join over the created by field 'modified_by'
			$query->join('LEFT', '#__users AS modified_by ON modified_by.id = a.modified_by');

			if (!JFactory::getUser()->authorise('core.edit', 'com_gm_ceiling'))
			{
				$query->where('a.state = 1');
			}
	*/
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
					$query->where('( a.title LIKE ' . $search . '  OR  component.title LIKE ' . $search . '  OR  component.unit LIKE ' . $search . '  OR a.price LIKE ' . $search . ' )');
				}
			}
			
	/*
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
		 
			// Выбераем поля.
			$query->select('a.id');
			$query->select('a.title');
			$query->select('a.price');
			$query->from('#__gm_ceiling_components_option AS a');
		  
			//$query->where('a.state = 1');
			
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

	public function getGoodsPriceForDealer($dealer_id) {
		try {
			$db = JFactory::getDbo();
			
			$query = $db->getQuery(true);
			$query->select('
				`vc`.*,
				`gc`.`category`,
				CASE
				    WHEN `gdp`.`operation_id` = 1 then `gdp`.`value`
				    WHEN `gdp`.`operation_id` = 2 then CONCAT(\'+\', `gdp`.`value`)
				    WHEN `gdp`.`operation_id` = 3 then CONCAT(\'-\', `gdp`.`value`)
				    WHEN `gdp`.`operation_id` = 4 then CONCAT(\'+\', `gdp`.`value`, \'%\')
				    WHEN `gdp`.`operation_id` = 5 then CONCAT(\'-\', `gdp`.`value`, \'%\')
				    ELSE \'\'
				END as `operation`,
				CASE
				    WHEN `gdp`.`operation_id` = 1 then `gdp`.`value`
				    WHEN `gdp`.`operation_id` = 2 then `vc`.`price`+`gdp`.`value`
				    WHEN `gdp`.`operation_id` = 3 then `vc`.`price`-`gdp`.`value`
				    WHEN `gdp`.`operation_id` = 4 then `vc`.`price`+`gdp`.`value`/100*`vc`.`price`
				    WHEN `gdp`.`operation_id` = 5 then `vc`.`price`-`gdp`.`value`/100*`vc`.`price`
				    ELSE `vc`.`price`
				END as `final_price`
			');
			$query->from('`#__goods_canvases` as `vc`');
			$query->leftJoin('`#__gm_ceiling_goods_dealer_price` as `gdp`
				on `vc`.`id` = `gdp`.`goods_id`
			');
			$query->innerJoin('`#__gm_stock_goods_categories` as `gc`
				on `vc`.`category_id` = `gc`.`id`
			');
			$query->where("`created_by` = 1 OR `created_by` = $dealer_id");
			$query->order('`vc`.`id`'); 
			$db->setQuery($query);

			$canvases = $db->loadObjectList();
			$temp_result = array();
			$temp_result[1] = array();
			$result = array();
			$result[] = (object) array('category_id' => 1, 'category' => 'Полотна', 'textures' => array());

			foreach ($canvases as $item) {
				if (empty($temp_result[1][$item->texture_id])) {
					$temp_result[1][$item->texture_id] = array();
				}
				if (empty($temp_result[1][$item->texture_id][$item->manufacturer_id])) {
					$temp_result[1][$item->texture_id][$item->manufacturer_id] = array();
				}
				$temp_result[1][$item->texture_id][$item->manufacturer_id][] = $item;
			}

			foreach ($temp_result[1] as $key => $value) {
				$result[0]->textures[] = (object) array(
					'texture_id' => $key,
					'texture' => null,
					'manufacturers' => array()
				);
				$texture_index = count($result[0]->textures) - 1;
				foreach ($temp_result[1][$key] as $key2 => $value2) {
					$result[0]->textures[$texture_index]->manufacturers[] = (object) array(
						'manufacturer_id' => $key2,
						'manufacturer' => null,
						'goods' => array()
					);
					$manufacturer_index = count($result[0]->textures[$texture_index]->manufacturers) - 1;
					foreach ($temp_result[1][$key][$key2] as $key3 => $value3) {
						$result[0]->textures[$texture_index]->manufacturers[$manufacturer_index]->goods[] = $value3;
						$result[0]->textures[$texture_index]->texture = $value3->texture;
						$result[0]->textures[$texture_index]->manufacturers[$manufacturer_index]->manufacturer = $value3->manufacturer;
					}
				}
			}

			$query = $db->getQuery(true);
			$query->select('
				`vc`.*,
				`gc`.`category`,
				CASE
				    WHEN `gdp`.`operation_id` = 1 then `gdp`.`value`
				    WHEN `gdp`.`operation_id` = 2 then CONCAT(\'+\', `gdp`.`value`)
				    WHEN `gdp`.`operation_id` = 3 then CONCAT(\'-\', `gdp`.`value`)
				    WHEN `gdp`.`operation_id` = 4 then CONCAT(\'+\', `gdp`.`value`, \'%\')
				    WHEN `gdp`.`operation_id` = 5 then CONCAT(\'-\', `gdp`.`value`, \'%\')
				    ELSE \'\'
				END as `operation`,
				CASE
				    WHEN `gdp`.`operation_id` = 1 then `gdp`.`value`
				    WHEN `gdp`.`operation_id` = 2 then `vc`.`price`+`gdp`.`value`
				    WHEN `gdp`.`operation_id` = 3 then `vc`.`price`-`gdp`.`value`
				    WHEN `gdp`.`operation_id` = 4 then `vc`.`price`+`gdp`.`value`/100*`vc`.`price`
				    WHEN `gdp`.`operation_id` = 5 then `vc`.`price`-`gdp`.`value`/100*`vc`.`price`
				    ELSE `vc`.`price`
				END as `final_price`
			');
			$query->from('`#__goods_components` as `vc`');
			$query->leftJoin('`#__gm_ceiling_goods_dealer_price` as `gdp`
				on `vc`.`id` = `gdp`.`goods_id`
			');
			$query->innerJoin('`#__gm_stock_goods_categories` as `gc`
				on `vc`.`category_id` = `gc`.`id`
			');
			$query->where("`created_by` = 1 OR `created_by` = $dealer_id");
			$query->order('`vc`.`category_id`, `vc`.`id`'); 
			$db->setQuery($query);

			$components = $db->loadObjectList();

			foreach ($components as $item) {
				$bool_added = false;
				foreach ($result as $value) {
					if ($item->category_id === $value->category_id) {
						$value->goods[] = $item;
						$bool_added = true;
						break;
					}
				}
				if ($bool_added) {
					continue;
				} else {
					$result[] = (object) array(
						'category_id' => $item->category_id,
						'category' => $item->category,
						'goods' => array($item)
					);
				}
			}

			return $result;
		} catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}

	public function saveDealerPriceGoods($dealer_id, $dealer_prices, $reset_flag) {
		try {

			$db = $this->getDbo();
            $goods_ids = '';
            $values = array();

            if (empty($dealer_id)) {
            	throw new Exception("Empty dealer_id", 1);	
            }

			foreach ($dealer_prices as $value) {
				if ( empty((float)$value['goods_id']) || empty((float)$value['operation_id']) || empty((float)$value['value'])) {
					continue;
				}

				$goods_ids .= $value['goods_id'].',';
				$values[] = $value['goods_id'].','. $value['operation_id'] .','. $value['value'].','. $dealer_id;
			}

			if (empty($goods_ids)) {
				return false;
			}

			$goods_ids = substr($goods_ids, 0, -1);

            $query = $db->getQuery(true);
            $query ->delete('`#__gm_ceiling_goods_dealer_price`')
                ->where("`goods_id` in ($goods_ids) and `dealer_id` = $dealer_id");
            $db->setQuery($query);
            $db->execute();

            if ($reset_flag == 0) {
            	$query = $db->getQuery(true);
            	$query->insert('`#__gm_ceiling_goods_dealer_price`')
            	    ->columns('`goods_id`, `operation_id`, `value`, `dealer_id`')
            	    ->values($values);
            	$db->setQuery($query);
            	$db->execute();
            }

			return true;
		} catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}

	public function getJobsDealer($dealer_id){
		 try {

			$db = $this->getDbo();

            $query = $db->getQuery(true);
            $query ->select('`j`.`id`,`j`.`name`, `dp`.`price`, `dp`.`id` as `dp_id`, IFNULL(`dp`.`price`, 0) as `price`')
            	->from('`#__gm_ceiling_jobs` as `j`')
            	->leftJoin("`#__gm_ceiling_jobs_dealer_price` as `dp`
            		on `j`.`id` = `dp`.`job_id` and `dp`.`dealer_id` = $dealer_id")
            	->order('`j`.`id`');
            $db->setQuery($query);
            $db->execute();
			$items = $db->loadObjectList();

			return $items;
		} catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}
}
