<?php

/**
 * @version    CVS: 1.0.0
 * @package    Com_Gm_ceiling
 * @author     Mikhail  <vms@itctl.ru>
 * @copyright  2016 Mikhail 
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.modelitem');
jimport('joomla.event.dispatcher');

use Joomla\Utilities\ArrayHelper;
/**
 * Gm_ceiling model.
 *
 * @since  1.6
 */
class Gm_ceilingModelColor extends JModelItem
{
	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @return void
	 *
	 * @since    1.6
	 *
	 */
	protected function populateState()
	{
		try
		{
			$app  = JFactory::getApplication('com_gm_ceiling');
	        $user = JFactory::getUser();

	        // Check published state
	        if ((!$user->authorise('core.edit.state', 'com_gm_ceiling')) && (!$user->authorise('core.edit', 'com_gm_ceiling')))
	        {
	            $this->setState('filter.published', 1);
	            $this->setState('fileter.archived', 2);
	        }

			// Load state from the request userState on edit or from the passed variable on default
			if (JFactory::getApplication()->input->get('layout') == 'edit')
			{
				$id = JFactory::getApplication()->getUserState('com_gm_ceiling.edit.color.id');
			}
			else
			{
				$id = JFactory::getApplication()->input->get('id');
				JFactory::getApplication()->setUserState('com_gm_ceiling.edit.color.id', $id);
			}

			$this->setState('color.id', $id);

			// Load the parameters.
			$params       = $app->getParams();
			$params_array = $params->toArray();

			if (isset($params_array['item_id']))
			{
				$this->setState('color.id', $params_array['item_id']);
			}

			$this->setState('params', $params);
		}
		catch(Exception $e)
        {
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}

	/**
	 * Method to get an object.
	 *
	 * @param   integer  $id  The id of the object to get.
	 *
	 * @return  mixed    Object on success, false on failure.
	 */
	public function &getData($id = null)
	{
		try
		{
			if ($this->_item === null)
			{
				$this->_item = false;

				if (empty($id))
				{
					$id = $this->getState('color.id');
				}

				// Get a level row instance.
				$table = $this->getTable();

				// Attempt to load the row.
				if ($table->load($id))
				{
					// Check published state.
					if ($published = $this->getState('filter.published'))
					{
						if (isset($table->state) && $table->state != $published)
						{
							throw new Exception(JText::_('COM_GM_CEILING_ITEM_NOT_LOADED'), 403);
						}
					}

					// Convert the JTable to a clean JObject.
					$properties  = $table->getProperties(1);
					$this->_item = ArrayHelper::toObject($properties, 'JObject');
				}
			}

			if (isset($this->_item->created_by) )
			{
				$this->_item->created_by_name = JFactory::getUser($this->_item->created_by)->name;
			}if (isset($this->_item->modified_by) )
			{
				$this->_item->modified_by_name = JFactory::getUser($this->_item->modified_by)->name;
			}

				if (isset($this->_item->color_canvas) && $this->_item->color_canvas != '') {
					if (is_object($this->_item->color_canvas))
					{
						$this->_item->color_canvas = \Joomla\Utilities\ArrayHelper::fromObject($this->_item->color_canvas);
					}
					$values = (is_array($this->_item->color_canvas)) ? $this->_item->color_canvas : explode(',',$this->_item->color_canvas);

					$textValue = array();
					$textValue2 = array();
					foreach ($values as $value)
					{
						$db = JFactory::getDbo();
						$query = $db->getQuery(true);
						$query
							->select('`#__gm_ceiling_canvases_2512177`.`canvas_title`')
							->select('`#__gm_ceiling_canvases_2512177`.`canvas_texture`')
							->from($db->quoteName('#__gm_ceiling_canvases', '#__gm_ceiling_canvases_2512177'))
							->where($db->quoteName('id') . ' = ' . $db->quote($db->escape($value)));
						$db->setQuery($query);
						$results = $db->loadObject();
						if ($results) {
							$textValue[] = $results->canvas_title;

						
							$query = $db->getQuery(true);
							$query
								->select('`#__gm_ceiling_textures_2460714`.`texture_title`')
								->from($db->quoteName('#__gm_ceiling_textures', '#__gm_ceiling_textures_2460714'))
								->where($db->quoteName('id') . ' = ' . $db->quote($db->escape($results->canvas_texture)));
							$db->setQuery($query);
							$results2 = $db->loadObject();
							if ($results2) {
								$textValue2[] = $results2->texture_title;
							}
						}
					}

				$this->_item->color_canvas = !empty($textValue) ? implode(', ', $textValue) : $this->_item->color_canvas;
				$this->_item->canvas_texture = !empty($textValue2) ? implode(', ', $textValue2) : $this->_item->canvase_texture;

				}
	        $db = JFactory::getDbo();
	        $query = $db->getQuery(true);
	        $query
	            ->select('CONCAT( manufac.name , \' \', manufac.country, \' \', canvases.width ) AS full_name,canvases.count' )
	            ->from('`#__gm_ceiling_canvases` AS canvases')
	            ->select('textures.texture_title AS texture_title')
	            ->join('LEFT', '`#__gm_ceiling_textures` AS textures ON canvases.texture_id = textures.id')
	            ->select('colors.title AS colors_title, colors.file AS file, colors.id AS id , colors.hex AS hex ')
	            ->join('LEFT', '`#__gm_ceiling_colors` AS colors ON canvases.color_id = colors.id')
	            ->join('LEFT', '`#__gm_ceiling_canvases_manufacturers` AS manufac ON canvases.manufacturer_id = manufac.id')
	            ->where('canvases.color_id =' . $id);

	        $db->setQuery($query);
	        $this->_item = $db->loadObject();

			return $this->_item;
		}
		catch(Exception $e)
        {
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}

	/**
	 * Get an instance of JTable class
	 *
	 * @param   string  $type    Name of the JTable class to get an instance of.
	 * @param   string  $prefix  Prefix for the table class name. Optional.
	 * @param   array   $config  Array of configuration values for the JTable object. Optional.
	 *
	 * @return  JTable|bool JTable if success, false on failure.
	 */
	public function getTable($type = 'Color', $prefix = 'Gm_ceilingTable', $config = array())
	{
		try
		{
			$this->addTablePath(JPATH_ADMINISTRATOR . '/components/com_gm_ceiling/tables');

			return JTable::getInstance($type, $prefix, $config);
		}
		catch(Exception $e)
        {
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}

	/**
	 * Get the id of an item by alias
	 *
	 * @param   string  $alias  Item alias
	 *
	 * @return  mixed
	 */
	public function getItemIdByAlias($alias)
	{
		try
		{
			$table = $this->getTable();

			$table->load(array('alias' => $alias));

			return $table->id;
		}
		catch(Exception $e)
        {
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}

	/**
	 * Method to check in an item.
	 *
	 * @param   integer  $id  The id of the row to check out.
	 *
	 * @return  boolean True on success, false on failure.
	 *
	 * @since    1.6
	 */
	public function checkin($id = null)
	{
		try
		{
			// Get the id.
			$id = (!empty($id)) ? $id : (int) $this->getState('color.id');

			if ($id)
			{
				// Initialise the table
				$table = $this->getTable();

				// Attempt to check the row in.
				if (method_exists($table, 'checkin'))
				{
					if (!$table->checkin($id))
					{
						return false;
					}
				}
			}

			return true;
		}
		catch(Exception $e)
        {
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}

	/**
	 * Method to check out an item for editing.
	 *
	 * @param   integer  $id  The id of the row to check out.
	 *
	 * @return  boolean True on success, false on failure.
	 *
	 * @since    1.6
	 */
	public function checkout($id = null)
	{
		try
		{
			// Get the user id.
			$id = (!empty($id)) ? $id : (int) $this->getState('color.id');

			if ($id)
			{
				// Initialise the table
				$table = $this->getTable();

				// Get the current user object.
				$user = JFactory::getUser();

				// Attempt to check the row out.
				if (method_exists($table, 'checkout'))
				{
					if (!$table->checkout($user->get('id'), $id))
					{
						return false;
					}
				}
			}

			return true;
		}
		catch(Exception $e)
        {
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}

	/**
	 * Get the name of a category by id
	 *
	 * @param   int  $id  Category id
	 *
	 * @return  Object|null	Object if success, null in case of failure
	 */
	public function getCategoryName($id)
	{
		try
		{
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query
				->select('title')
				->from('#__categories')
				->where('id = ' . $id);
			$db->setQuery($query);

			return $db->loadObject();
		}
		catch(Exception $e)
        {
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}

	/**
	 * Publish the element
	 *
	 * @param   int  $id     Item id
	 * @param   int  $state  Publish state
	 *
	 * @return  boolean
	 */
	public function publish($id, $state)
	{
		try
		{
			$table = $this->getTable();
			$table->load($id);
			$table->state = $state;

			return $table->store();
		}
		catch(Exception $e)
        {
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}

	function getColorById($id){
		try
		{
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query
				->select('*')
				->from('#__gm_ceiling_colors')
				->where('id = ' . $id);
			$db->setQuery($query);
			return $db->loadObject();
		}
		catch(Exception $e)
        {
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}

	/**
	 * Method to delete an item
	 *
	 * @param   int  $id  Element id
	 *
	 * @return  bool
	 */
	public function delete($id)
	{
		try
		{
			$table = $this->getTable();

			return $table->delete($id);
		}
		catch(Exception $e)
        {
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}

	
}
