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

jimport('joomla.application.component.modelform');
jimport('joomla.event.dispatcher');

use Joomla\Utilities\ArrayHelper;
/**
 * Gm_ceiling model.
 *
 * @since  1.6
 */
class Gm_ceilingModelProjectForm extends JModelForm
{
	private $item = null;

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @return void
	 *
	 * @since  1.6
	 */
	protected function populateState()
	{
		try
		{
			$app = JFactory::getApplication('com_gm_ceiling');

			// Load state from the request userState on edit or from the passed variable on default
			if (JFactory::getApplication()->input->get('layout') == 'edit')
			{
				$id = JFactory::getApplication()->getUserState('com_gm_ceiling.edit.project.id');
			}
			else
			{
				$id = JFactory::getApplication()->input->get('id');
				JFactory::getApplication()->setUserState('com_gm_ceiling.edit.project.id', $id);
			}

			$this->setState('project.id', $id);

			// Load the parameters.
			$params       = $app->getParams();
			$params_array = $params->toArray();

			if (isset($params_array['item_id']))
			{
				$this->setState('project.id', $params_array['item_id']);
			}

			$this->setState('params', $params);
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}

	/**
	 * Method to get an ojbect.
	 *
	 * @param   integer  $id  The id of the object to get.
	 *
	 * @return Object|boolean Object on success, false on failure.
	 *
	 * @throws Exception
	 */
	public function &getData($id = null)
	{
		try
		{
			if ($this->item === null)
			{
				$this->item = false;

				if (empty($id))
				{
					$id = $this->getState('project.id');
				}

				// Get a level row instance.
				$table = $this->getTable();

				// Attempt to load the row.
				if ($table !== false && $table->load($id))
				{
					$user = JFactory::getUser();
					$id   = $table->id;
					$canEdit = $user->authorise('core.edit', 'com_gm_ceiling') || $user->authorise('core.create', 'com_gm_ceiling');

					if (!$canEdit && $user->authorise('core.edit.own', 'com_gm_ceiling'))
					{
						$canEdit = $user->id == $table->created_by;
					}


					// Convert the JTable to a clean JObject.
					$properties  = $table->getProperties(1);
					$this->item = ArrayHelper::toObject($properties, 'JObject');
					
					if (isset($this->item->client_id) && $this->item->client_id != '') {
						$this->item->_client_id = $this->item->client_id;
						if (is_object($this->item->client_id)){
							$this->item->client_id = \Joomla\Utilities\ArrayHelper::fromObject($this->item->client_id);
						}
						$values = (is_array($this->item->client_id)) ? $this->item->client_id : explode(',',$this->item->client_id);

						$textValue = array();
						foreach ($values as $value)
						{
							$db = JFactory::getDbo();
							$query = $db->getQuery(true);
							$query
								->select('client.`client_name`')
								->select('client.`id`')
                                ->select('client.`dealer_id`')
								->select('contact.`phone`')
								->from($db->quoteName('#__gm_ceiling_clients', 'client'))
	                            ->leftJoin('#__gm_ceiling_clients_contacts as contact ON contact.client_id = client.id')
								->where($db->quoteName('client.id') . ' = ' . $db->quote($db->escape($value)));
							$db->setQuery($query);
							$results = $db->loadObject();

							if ($results) {
							    $dealer_id = $results->dealer_id;
								$textValue[] = $results->client_name;
								$textValue3[] = $results->id;
								$textValue2[] = $results->phone;
							}
						}

				$db = JFactory::getDbo();
				$query = $db->getQuery(true);
				$query
					->select("m.type as stage,m.date_time as time,m.mounter_id as mounter")
					->from('`#__gm_ceiling_projects_mounts` as m')
					->where("m.project_id =". $this->item->id);
				$db->setQuery($query);
				
				$mount_array = $db->loadObjectList();
				$this->item->mount_data = htmlspecialchars(json_encode((!empty($mount_array)) ? $mount_array : array()),ENT_QUOTES);
                $query = $db->getQuery(true);
                $query
                    ->select("SUM(prepayment_sum) as total")
                    ->from('`rgzbn_gm_ceiling_projects_prepayment`')
                    ->where("project_id =". $this->item->id);
                $db->setQuery($query);
                $prepayment_total = $db->loadObject();
                if(!empty($prepayment_total)){
                    $this->item->prepayment_total = $prepayment_total->total;
                }
				$this->item->dealer_id = !empty($dealer_id) ? implode(', ', $textValue1) : $this->_item->dealer_id;
					if (!empty($textValue)) $this->item->client_id = implode(', ', $textValue);
					if (!empty($textValue3)) $this->item->id_client = implode(', ', $textValue3);
					if (!empty($textValue2)) $this->item->client_contacts = implode(', ', $textValue2);
					if (!empty($dealer_id)) $this->item->dealer_id = $dealer_id;

					}
				}
			}
			return $this->item;
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}

	/**
	 * Method to get the table
	 *
	 * @param   string  $type    Name of the JTable class
	 * @param   string  $prefix  Optional prefix for the table class name
	 * @param   array   $config  Optional configuration array for JTable object
	 *
	 * @return  JTable|boolean JTable if found, boolean false on failure
	 */
	public function getTable($type = 'Project', $prefix = 'Gm_ceilingTable', $config = array())
	{
		try
		{
			$this->addTablePath(JPATH_ADMINISTRATOR . '/components/com_gm_ceiling/tables');

			return JTable::getInstance($type, $prefix, $config);
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}

	/**
	 * Get an item by alias
	 *
	 * @param   string  $alias  Alias string
	 *
	 * @return int Element id
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
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
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
			$id = (!empty($id)) ? $id : (int) $this->getState('project.id');

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
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
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
			$id = (!empty($id)) ? $id : (int) $this->getState('project.id');

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
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}

	/**
	 * Method to get the profile form.
	 *
	 * The base form is loaded from XML
	 *
	 * @param   array    $data      An optional array of data for the form to interogate.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return    JForm    A JForm object on success, false on failure
	 *
	 * @since    1.6
	 */
	public function getForm($data = array(), $loadData = true)
	{
		try
		{
			// Get the form.
			$form = $this->loadForm('com_gm_ceiling.project', 'projectform', array(
				'control'   => 'jform',
				'load_data' => $loadData
				)
			);

			if (empty($form))
			{
				return false;
			}

			return $form;
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return    mixed    The data for the form.
	 *
	 * @since    1.6
	 */
	protected function loadFormData()
	{
		try
		{
			$data = JFactory::getApplication()->getUserState('com_gm_ceiling.edit.project.data', array());

			if (empty($data))
			{
				$data = $this->getData();
			}
			return $data;
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}

	/**
	 * Method to save the form data.
	 *
	 * @param   array  $data  The form data
	 *
	 * @return bool
	 *
	 * @throws Exception
	 * @since 1.6
	 */
	public function save($data)
	{
		try
		{
			$id    = (!empty($data['id'])) ? $data['id'] : (int) $this->getState('project.id');

			$state = (!empty($data['state'])) ? 1 : 0;
			$user  = JFactory::getUser();

			if ($id)
			{
				// Check the user can edit this item
				$authorised = $user->authorise('core.edit', 'com_gm_ceiling') || $authorised = $user->authorise('core.edit.own', 'com_gm_ceiling');
			}
			else
			{
				// Check the user can create new items in this section
				$authorised = $user->authorise('core.create', 'com_gm_ceiling');
			}

			if ($authorised !== true)
			{
				//throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'), 403);
			}
			
			$groups = $user->get('groups');

			$table = $this->getTable();
			if ($table->save($data) === true)
			{
				return $table->id;
			}
			else
			{
				return false;
			}
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}

	/**
	 * Method to delete data
	 *
	 * @param   array  $data  Data to be deleted
	 *
	 * @return bool|int If success returns the id of the deleted item, if not false
	 *
	 * @throws Exception
	 */
	public function delete($data)
	{
		try
		{
			$id = (!empty($data['id'])) ? $data['id'] : (int) $this->getState('project.id');

			if (JFactory::getUser()->authorise('core.delete', 'com_gm_ceiling') !== true)
			{
				throw new Exception(403, JText::_('JERROR_ALERTNOAUTHOR'));
			}

			$table = $this->getTable();

			if ($table->delete($data['id']) === true)
			{
				return $id;
			}
			else
			{
				return false;
			}
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}

	/**
	 * Check if data can be saved
	 *
	 * @return bool
	 */
	public function getCanSave()
	{
		try
		{
			$table = $this->getTable();

			return $table !== false;
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}
	
	public function WhatDealerGauger($gauger_id) {
		try
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			
			$query->select('dealer_id')
				->from('#__users')
				->where("id = '$gauger_id'");
			$db->setQuery($query);

			$items = $db->loadObjectList();
			return $items;
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}
}