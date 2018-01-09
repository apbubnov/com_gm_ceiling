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
class Gm_ceilingModelClientForm extends JModelForm
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
				$id = JFactory::getApplication()->getUserState('com_gm_ceiling.edit.client.id');
			}
			else
			{
				$id = JFactory::getApplication()->input->get('id');
				JFactory::getApplication()->setUserState('com_gm_ceiling.edit.client.id', $id);
			}

			$this->setState('client.id', $id);

			// Load the parameters.
			$params       = $app->getParams();
			$params_array = $params->toArray();

			if (isset($params_array['item_id']))
			{
				$this->setState('client.id', $params_array['item_id']);
			}

			$this->setState('params', $params);
		}
		catch(Exception $e)
        {
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
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
					$id = $this->getState('client.id');
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

					if (!$canEdit)
					{
						throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'), 500);
					}

					// Check published state.
					if ($published = $this->getState('filter.published'))
					{
						if ($table->state != $published)
						{
							return $this->item;
						}
					}

					// Convert the JTable to a clean JObject.
					$properties  = $table->getProperties(1);
					$this->item = ArrayHelper::toObject($properties, 'JObject');
				}
			}

			return $this->item;
		}
		catch(Exception $e)
        {
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
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
	public function getTable($type = 'Client', $prefix = 'Gm_ceilingTable', $config = array())
	{
		try
		{
			$this->addTablePath(JPATH_ADMINISTRATOR . '/components/com_gm_ceiling/tables');

			return JTable::getInstance($type, $prefix, $config);
		}
		catch(Exception $e)
        {
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
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
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
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
			$id = (!empty($id)) ? $id : (int) $this->getState('client.id');

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
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
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
			$id = (!empty($id)) ? $id : (int) $this->getState('client.id');

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
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
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
			$form = $this->loadForm('com_gm_ceiling.client', 'clientform', array(
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
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
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
			$data = JFactory::getApplication()->getUserState('com_gm_ceiling.edit.client.data', array());

			if (empty($data))
			{
				$data = $this->getData();
			}
			return $data;
		}
		catch(Exception $e)
        {
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
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
			$id    = (!empty($data['id'])) ? $data['id'] : (int) $this->getState('client.id');
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
			
			if(empty($data['dealer_id'])) {
				if(isset($user->dealer_id)) {
					$data['dealer_id'] = $user->dealer_id;
				} else {
					$data['dealer_id'] = 2;
				}
			}
			
			//Если менеджер дилера, то показывать дилерских клиентов
			if(in_array("13",$groups)){
				$data['manager_id'] = $user->id;
			}
			

			$table = $this->getTable();

			if ($table->save($data) === true)
			{
				$id_client = $table->id;
				if(!empty($data['client_contacts']))
				{
					$db = JFactory::getDbo();
					$phone = $db->escape($data['client_contacts'], true);

					$query = $db->getQuery(true);
					$query->insert('#__gm_ceiling_clients_contacts');
					$query->columns('`client_id`, `phone`');
					$query->values("$id_client, '$phone'");
					
					$db->setQuery($query);
					$db->execute();
				}
				return $id_client;
			}
			else
			{
				return false;
			}
		}
		catch(Exception $e)
        {
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
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
			$id = (!empty($data['id'])) ? $data['id'] : (int) $this->getState('client.id');

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
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
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
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
        }
	}

	public function getInfo($id)
	{
		try
		{
			$db = JFactory::getDbo();
	 		$query = $db->getQuery(true);
			$query->select('clients.client_name')
				->from('`#__gm_ceiling_clients` AS clients')
				->select('phone.phone')
				->join('LEFT','`#__gm_ceiling_clients_contacts` AS phone ON clients.id = phone.client_id')
				->where('clients.id = ' . $id);
			$db->setQuery($query);
			$info = $db->loadObject();
			return $info;
		}
		catch(Exception $e)
        {
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
        }
	}
	public function getManager($manager_id)
	{
		try
		{
			$db = JFactory::getDbo();
	 		$query = $db->getQuery(true);
			$query->select('users.name AS manager_name, users.email AS email')
				->from('`#__users` AS users')
				->where('users.id = ' . $manager_id);
			$db->setQuery($query);
			$info = $db->loadObject();
			return $info;
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
