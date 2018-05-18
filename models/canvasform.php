<?php
/**
 * @version    CVS: 1.0.0
 * @package    Com_Gm_ceiling
 * @author     aleksander <nigga@hotmail.ru>
 * @copyright  2017 aleksander
 * @license    GNU General Public License версии 2 или более поздней; Смотрите LICENSE.txt
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
class Gm_ceilingModelCanvasForm extends JModelForm
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
        try {
            $app = JFactory::getApplication('com_gm_ceiling');

            // Load state from the request userState on edit or from the passed variable on default
            $id = null;
            if (JFactory::getApplication()->input->get('layout') == 'edit') {
                $id = JFactory::getApplication()->getUserState('com_gm_ceiling.edit.canvas.id');
            } else {
                $id = JFactory::getApplication()->input->get('id');
                JFactory::getApplication()->setUserState('com_gm_ceiling.edit.canvas.id', $id);
            }
            $this->setState('canvas.id', $id);

            // Load the parameters.
            $params = $app->getParams();
            $params_array = $params->toArray();

            if (isset($params_array['item_id'])) {
                $this->setState('canvas.id', $params_array['item_id']);
            }

            $this->setState('params', $params);
        } catch (Exception $e) {
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    /**
     * Method to get an ojbect.
     *
     * @param   integer $id The id of the object to get.
     *
     * @return Object|boolean Object on success, false on failure.
     *
     * @throws Exception
     */
    public function &getData($id = null)
    {
        try {
            $this->item = false;

            $app = JFactory::getApplication();

            $ID = $app->input->get('ID', null, 'string');
            $id = $app->input->get('id', null, 'string');

            $modelCanvases = Gm_ceilingHelpersGm_ceiling::getModel('canvases');
            if (!empty($ID))
                $this->item = $modelCanvases->getCanvases(array('id_canvas' => $ID))[0];
            else if (!empty($id))
                $this->item = $modelCanvases->getCanvases(array('id_canvas_all' => $id))[0];
            else return null;

            $this->item->color = (empty($this->item->color)) ? "Нет" : $this->item->color;
            $this->item->roller = (object)array();
            $this->item->roller->purchasingPrice = $this->item->purchasing_price;
            $this->item->roller->quad = $this->item->lenghtAll;
            $this->item->roller->id = $this->item->idAll;


            return $this->item;
        } catch (Exception $e) {
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    /**
     * Method to get the table
     *
     * @param   string $type Name of the JTable class
     * @param   string $prefix Optional prefix for the table class name
     * @param   array $config Optional configuration array for JTable object
     *
     * @return  JTable|boolean JTable if found, boolean false on failure
     */
    public function getTable($type = 'Canvas', $prefix = 'Gm_ceilingTable', $config = array())
    {
        try {
            $this->addTablePath(JPATH_ADMINISTRATOR . '/components/com_gm_ceiling/tables');

            return JTable::getInstance($type, $prefix, $config);
        } catch (Exception $e) {
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    /**
     * Get an item by alias
     *
     * @param   string $alias Alias string
     *
     * @return int Element id
     */
    public function getItemIdByAlias($alias)
    {
        try {
            $table = $this->getTable();
            $properties = $table->getProperties();

            if (!in_array('alias', $properties)) {
                return null;
            }

            $table->load(array('alias' => $alias));

            return $table->id;
        } catch (Exception $e) {
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    /**
     * Method to check in an item.
     *
     * @param   integer $id The id of the row to check out.
     *
     * @return  boolean True on success, false on failure.
     *
     * @since    1.6
     */
    public function checkin($id = null)
    {
        try {
            // Get the id.
            $id = (!empty($id)) ? $id : (int)$this->getState('canvas.id');

            if ($id) {
                // Initialise the table
                $table = $this->getTable();

                // Attempt to check the row in.
                if (method_exists($table, 'checkin')) {
                    if (!$table->checkin($id)) {
                        return false;
                    }
                }
            }

            return true;
        } catch (Exception $e) {
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    /**
     * Method to check out an item for editing.
     *
     * @param   integer $id The id of the row to check out.
     *
     * @return  boolean True on success, false on failure.
     *
     * @since    1.6
     */
    public function checkout($id = null)
    {
        try {
            // Get the user id.
            $id = (!empty($id)) ? $id : (int)$this->getState('canvas.id');

            if ($id) {
                // Initialise the table
                $table = $this->getTable();

                // Get the current user object.
                $user = JFactory::getUser();

                // Attempt to check the row out.
                if (method_exists($table, 'checkout')) {
                    if (!$table->checkout($user->get('id'), $id)) {
                        return false;
                    }
                }
            }

            return true;
        } catch (Exception $e) {
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    /**
     * Method to get the profile form.
     *
     * The base form is loaded from XML
     *
     * @param   array $data An optional array of data for the form to interogate.
     * @param   boolean $loadData True if the form is to load its own data (default case), false if not.
     *
     * @return    JForm    A JForm object on success, false on failure
     *
     * @since    1.6
     */
    public function getForm($data = array(), $loadData = true)
    {
        try {
            // Get the form.
            $form = $this->loadForm('com_gm_ceiling.canvas', 'canvasform', array(
                    'control' => 'jform',
                    'load_data' => $loadData
                )
            );

            if (empty($form)) {
                return false;
            }

            return $form;
        } catch (Exception $e) {
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
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
        try {
            $data = JFactory::getApplication()->getUserState('com_gm_ceiling.edit.canvas.data', array());

            if (empty($data)) {
                $data = $this->getData();
            }

            return $data;
        } catch (Exception $e) {
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    /**
     * Method to save the form data.
     *
     * @param   array $data The form data
     *
     * @return bool
     *
     * @throws Exception
     * @since 1.6
     */
    public function save($data)
    {
        try {
            $id = (!empty($data['id'])) ? $data['id'] : (int)$this->getState('canvas.id');
            $state = (!empty($data['state'])) ? 1 : 0;
            $user = JFactory::getUser();

            if ($id) {
                // Check the user can edit this item
                $authorised = $user->authorise('core.edit', 'com_gm_ceiling') || $authorised = $user->authorise('core.edit.own', 'com_gm_ceiling');
            } else {
                // Check the user can create new items in this section
                $authorised = $user->authorise('core.create', 'com_gm_ceiling');
            }

            if ($authorised !== true) {
                throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'), 403);
            }

            $table = $this->getTable();

            if ($table->save($data) === true) {
                return $table->id;
            } else {
                return false;
            }
        } catch (Exception $e) {
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function edit($data)
    {
        try {
            $db = $this->getDbo();

            $data['width'] = str_replace(",", ".", $data['width']);
            $data['roller']['quad'] = str_replace(",", ".", $data['roller']['quad']);
            $data['roller']['purchasingPrice'] = str_replace(",", ".", $data['roller']['purchasingPrice']);

            $messageError = null;
            if ($data['id'] != "") {
                $query = $db->getQuery(true);
                $query->update('`#__gm_ceiling_canvases` AS canvases')
                    ->where('canvases.id = ' . $data['id']);

                if ($data['name'] != "") $query->set('canvases.name = ' . $db->quote($data['name']));
                if ($data['country'] != "") $query->set('canvases.country = ' . $db->quote($data['country']));
                if ($data['width'] != "") $query->set('canvases.width = ' . $db->quote($data['width']));
                if ($data['price'] != "") $query->set('canvases.price = ' . $db->quote($data['price']));

                $db->setQuery($query);
                $error = $db->execute();
                if (empty($error)) $messageError = "Изменение не удалось, попробуйте позже!";
            }

            if (empty($messageError) && $data['roller']['id'] != "") {
                $query = $db->getQuery(true);
                $query->update('`#__gm_ceiling_canvases_all` AS rollers')
                    ->where('rollers.id = ' . $db->quote($data['roller']['id']));

                if ($data['roller']['quad'] != "") $query->set('rollers.quad = ' . $db->quote($data['roller']['quad']));
                if ($data['roller']['purchasingPrice'] != "") $query->set('rollers.purchasing_price = ' . $db->quote($data['roller']['purchasingPrice']));

                $db->setQuery($query);
                $error = $db->execute();
                if (empty($error)) $messageError = "Изменение не удалось, попробуйте позже!";
            }

            if (empty($messageError) && $data['roller']['purchasingPrice'] != "") {
                $user = JFactory::getUser();

                $query = $db->getQuery(true);
                $query->insert($db->quoteName('#__gm_ceiling_analytics_canvases'))
                    ->columns('id_canvas, quad, price, date_update, dealer_id, user_id, note')
                    ->values($db->quote($data['roller']['id']) . ', ' . $db->quote(0) . ', ' . $db->quote($data['roller']['purchasingPrice']) . ', ' .
                        $db->quote($data['date']) . ', ' . $db->quote($user->dealer_id) . ', ' . $db->quote($user->id) . ', ' .
                        $db->quote("Изменение цены"));
                $db->setQuery($query);
                $error = $db->execute();
                if (empty($error)) $messageError = "Изменение не удалось, попробуйте позже!";
            }

            return $messageError;
        } catch (Exception $e) {
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    /**
     * Method to delete data
     *
     * @param   int $pk Item primary key
     *
     * @return  int  The id of the deleted item
     *
     * @throws Exception
     *
     * @since 1.6
     */
    public function delete($pk)
    {
        try {
            $modelCanvases = Gm_ceilingHelpersGm_ceiling::getModel('canvases');
            $filter = array(
                'select' => array(
                    'canvasesId' => 'canvases_all.id_canvas',
                    'fullTitle' => 'CONCAT(canvases.country, \' \', canvases.name, \' \', canvases.width)',
                    'count' => 'canvases.count',
                    'price' => 'canvases_all.purchasing_price',
                    'quad' => 'canvases_all.quad'
                ),
                'where' => array(
                    '=' => array(
                        'canvases_all.id' => $pk
                    )
                ),
                'group' => array('canvases_all.id')
            );
            $result = ($modelCanvases->getCanvases($filter))[0];
            $canvasesId = $result->canvasesId;
            $fullTitle = $result->fullTitle;
            $count = $result->count;
            $purchasing_price = $result->price;
            $quad = $result->quad;

            $filter = array(
                'select' => array(
                    'rollerId' => 'canvases_all.id'
                ),
                'where' => array(
                    '=' => array(
                        'canvases.id' => $canvasesId
                    )
                ),
                'group' => array('canvases_all.id')
            );
            $rollersId = $modelCanvases->getCanvases($filter);

            $errorMessage = null;
            $date = date("Y-m-d H:i:s");

            $db = $this->getDbo();
            if (empty($errorMessage)) {
                $user = JFactory::getUser();

                $query = $db->getQuery(true);
                $query->insert($db->quoteName('#__gm_ceiling_analytics_canvases'))
                    ->columns('id_canvas, quad, price, date_update, dealer_id, user_id, note')
                    ->values($db->quote($canvasesId) . ', ' . $db->quote('-' . $quad) . ', ' . $db->quote($purchasing_price) . ', ' .
                        $db->quote($date) . ', ' . $db->quote($user->dealer_id) . ', ' . $db->quote($user->id) . ', ' .
                        $db->quote("Удаление " . $fullTitle . " - " . $pk));
                $db->setQuery($query);
                $error = $db->execute();
                if (empty($error)) $messageError = "Удаление произошло неудачно, попробуйте позже!";
            }
            if (empty($errorMessage)) {
                $query = $db->getQuery(true);
                $query->delete('`#__gm_ceiling_canvases_all`');
                $query->where('id = ' . $pk);
                $db->setQuery($query);
                $error = $db->execute();
                if (empty($error)) $errorMessage = "Удаление произошло неудачно, попробуйте позже!";

                $query = $db->getQuery(true);
                $query->update('`#__gm_ceiling_canvases` AS canvases')
                    ->where('canvases.id = ' . $canvasesId)
                    ->set('canvases.count = ' . $db->quote((int)$count - 1));
                $db->setQuery($query);
                $error = $db->execute();
                if (empty($error)) $messageError = "Удаление произошло неудачно, попробуйте позже!";
            }

            if (count($rollersId) <= 1 && empty($errorMessage)) {
                $query = $db->getQuery(true);
                $query->update('`#__gm_ceiling_canvases` AS canvases')
                    ->where('canvases.id = ' . $canvasesId)
                    ->set('canvases.count = ' . $db->quote(0));
                $db->setQuery($query);
                $error = $db->execute();
                if (empty($error)) $messageError = "Удаление произошло неудачно, попробуйте позже!";
            }

            return $errorMessage;
        } catch (Exception $e) {
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    /**
     * Check if data can be saved
     *
     * @return bool
     */
    public function getCanSave()
    {
        try {
            $table = $this->getTable();

            return $table !== false;
        } catch (Exception $e) {
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function receipt($data)
    {
        try {
            $errorMessage = null;

            $user = JFactory::getUser();
            $user_id = $user->get('id');
            $dealer_id = $user->get('dealer_id');

            $db = $this->getDbo();

            foreach ($data as $val) {
                $query = $db->getQuery(true);
                $query->select('canvases.id AS id, canvases.texture_id AS texture_id, canvases.color_id AS color_id, canvases.count AS count')
                    ->from('`#__gm_ceiling_canvases` AS canvases')
                    ->join('LEFT', '`#__gm_ceiling_textures` AS textures ON textures.id = canvases.texture_id')
                    ->join('LEFT', '`#__gm_ceiling_colors` AS colors ON colors.id = canvases.color_id')
                    ->where('canvases.country = ' . $db->quote($val->Country))
                    ->where('canvases.name = ' . $db->quote($val->Name))
                    ->where('canvases.width = ' . $db->quote($val->Width))
                    ->where('textures.texture_title = ' . $db->quote($val->Texture));
                if ($val->Color != "Нет") $query->where('colors.title = ' . $db->quote($val->Color));
                else $query->where('canvases.color_id IS NULL');

                $db->setQuery($query);
                $result = $db->loadObject();

                if (empty($result)) {
                    $result = (object)array();
                    $result->count = $val->Count;

                    $query = $db->getQuery(true);
                    $query->select('textures.id AS id')
                        ->from('`#__gm_ceiling_textures` AS textures')
                        ->where('textures.texture_title = ' . $db->quote($val->Texture));
                    $db->setQuery($query);
                    $result->texture = $db->loadObject();

                    if (empty($result->texture)) {
                        $query = $db->getQuery(true);
                        $query->insert($db->quoteName('#__gm_ceiling_textures'))
                            ->columns('texture_title, texture_colored')
                            ->values($db->quote($val->Texture) . ', ' . $db->quote(($val->Color == "Нет") ? 0 : 1));
                        $db->setQuery($query);
                        $db->execute();
                        $result->texture = (object)array("id" => $db->insertid());
                    }
                    $result->texture_id = $result->texture->id;

                    if ($val->Color == "Нет") $result->color = null;
                    else {
                        $query = $db->getQuery(true);
                        $query->select('colors.id AS id')
                            ->from('`#__gm_ceiling_colors` AS colors')
                            ->where('colors.title = ' . $db->quote($val->Color));
                        $db->setQuery($query);
                        $result->color = $db->loadObject();

                        if (empty($result->color)) {
                            $query = $db->getQuery(true);
                            $query->insert($db->quoteName('#__gm_ceiling_colors'))
                                ->columns('title')
                                ->values($db->quote($val->Color));
                            $db->setQuery($query);
                            $db->execute();
                            $result->color = (object)array("id" => $db->insertid());
                        }
                    }
                    $result->color_id = (!empty($result->color)) ? $result->color->id : null;

                    $query = $db->getQuery(true);
                    $query->insert($db->quoteName('#__gm_ceiling_canvases'))
                        ->columns('texture_id, color_id, name, country, width, count')
                        ->values($db->quote($result->texture_id) . ', ' . (empty($result->color_id) ? 'NULL' : $db->quote($result->color_id)) . ', ' . $db->quote($val->Name)
                            . ', ' . $db->quote($val->Country) . ', ' . $db->quote($val->Width) . ', ' . $db->quote($result->count));
                    $db->setQuery($query);
                    $db->execute();
                    $result->id = $db->insertid();
                } else {
                    $query = $db->getQuery(true);
                    $query->update('`#__gm_ceiling_canvases`')
                        ->set('count = ' . $db->quote(intval($result->count) + intval($val->Count)))
                        ->where('id = ' . $db->quote($result->id));
                    $db->setQuery($query);
                    $db->execute();
                }

                foreach ($val->Rollers as $rol) {
                    $width = floatval($val->Width);
                    $lenght = floatval($rol->Quad) / $width;
                    $points = "[{x:0,y:0},{x:0,y:" . $width . "},{x:" . $lenght . ",y:" . $width . "},{x:" . $lenght . ",y:0}]";

                    $query = $db->getQuery(true);
                    $query->insert($db->quoteName('#__gm_ceiling_canvases_all'))
                        ->columns('`id_canvas`, `barcode`, `article`, `stock`, `type`, `quad`, `points`')
                        ->values($db->quote($result->id) . ', ' . $db->quote($rol->Barcode)
                            . ', ' . $db->quote($rol->Article) . ', ' . $db->quote($rol->Stock)
                            . ', ' . $db->quote(0) . ', ' . $db->quote($rol->Quad) . ', ' . $db->quote($points));

                    $db->setQuery($query);
                    $db->execute();
                    $rol->Id = $db->insertid();

                    $query = $db->getQuery(true);
                    $query->insert($db->quoteName('#__gm_ceiling_analytics_canvases'))
                        ->columns('`canvas_id`, `roller_id`, `barcode`, `article`, `quad`, `price`, `stock`, `date_update`, `user_id`, `counterparty_id`, `status`')
                        ->values($db->quote($result->id) . ", " . $db->quote($rol->Id) . ", " . $db->quote($rol->Barcode) . ", " . $db->quote($rol->Article)
                            . ", " . $db->quote($rol->Quad) . ", " . $db->quote($rol->Price) . ", " . $db->quote($rol->Stock) . ", " . $db->quote($rol->Date)
                            . ", " . $db->quote($user_id) . ", " . $db->quote($rol->Counterparty) . ", " . $db->quote(1));
                    $db->setQuery($query);
                    $db->execute();
                    $rol->Anal_Id = $db->insertid();
                }
            };
            return $errorMessage;
        } catch (Exception $e) {
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function inventory($data, $date)
    {
        try {
            $errorMessage = null;
            try {
                $db = $this->getDbo();
                $user = JFactory::getUser();
                foreach ($data as $item) {
                    $item = (object)$item;
                    $item->quad = floatval(str_replace(",", ".", $item->quad));

                    $query = $db->getQuery(true);
                    $query->from('`#__gm_ceiling_canvases_all` AS roller')
                        ->select('roller.*')
                        ->where("roller.id = " . $db->quote($item->id));
                    $db->setQuery($query);
                    $result = $db->loadObject();

                    if (!empty($result) && $item->quad != 0) {
                        $new_quad = floatval($result->quad) + $item->quad;
                        $new_quad = ($new_quad <= 0) ? 0 : $new_quad;

                        $query = $db->getQuery(true);
                        $query->insert($db->quoteName('#__gm_ceiling_analytics_canvases'))
                            ->columns('`canvas_id`, `roller_id`, `barcode`, `article`, `quad`, `stock`, `date_update`, `user_id`, `status`')
                            ->values($db->quote($result->id_canvas) . ", " . $db->quote($result->id) . ", " .
                                $db->quote($result->barcode) . ", " . $db->quote($result->article) . ", " .
                                $db->quote($item->quad) . ", " . $db->quote($result->stock) . ", " . $db->quote($date) . ", " .
                                $db->quote($user->id) . ", " . $db->quote(3));
                        $db->setQuery($query);
                        $test = $db->execute();

                        if (!empty($test)) {
                            $query = $db->getQuery(true);
                            if ($new_quad == 0) {
                                $query->delete($db->quoteName('#__gm_ceiling_canvases_all'))
                                    ->where('id = ' . $result->id);
                            } else {
                                $query->update($db->quoteName('#__gm_ceiling_canvases_all'))
                                    ->set('quad = ' . $db->quote($new_quad))
                                    ->where('id = ' . $db->quote($result->id));
                            }
                            $db->setQuery($query);
                            $db->execute();
                        }
                    }
                }
            } catch (Exception $ex) {
                $errorMessage = $ex->getMessage();
            }

            return (object)array("error" => $errorMessage);
        } catch (Exception $e) {
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function TestRealization($data, $customer)
    {
        $db = $this->getDbo();

        $stock = $customer->stock;

        foreach ($data as $id => $v) {
            $sum_customer = 0;
            foreach ($v->quad as $i => $q) {
                $v->quad[$i] = floatval($q);
                $sum_customer += floatval($q);
            }

            $query = $db->getQuery(true);
            $query->select("R.id, R.quad, R.stock, R.barcode, R.article")
                ->from("`#__gm_ceiling_canvases` AS C")
                ->join("LEFT", "#__gm_ceiling_canvases_all AS R ON R.id_canvas = C.id")
                ->where("C.id = '$v->id'")
                ->order("R.id DESC");
            if ($stock == 2) $query->where("R.stock = '$stock'");
            $db->setQuery($query);
            $Rollers = $db->loadObjectList();

            $sum_stock = 0;
            foreach ($Rollers as $index => $roller) {
                $Rollers[$index]->quad = floatval($roller->quad);
                $Rollers[$index]->quadOld = floatval($roller->quad);
                $Rollers[$index]->stock2 = $stock;
                $Rollers[$index]->off = false;
                $sum_stock += floatval($roller->quad);
            }

            if ($sum_stock < $sum_customer)
                throw new Exception("Не хватает квадратуры!<br>$v->country $v->name $v->width<br>Текстура: $v->texture"
                    . ((empty($v->color)) ? "" : "Цвет: $v->color") . "<br>Нужно: $sum_customer - На складе: $sum_stock");

            $count = count($v->quad);
            foreach ($v->quad as $i => $q) {
                $map = array();

                foreach ($Rollers as $index => $roller) {
                    $dp = floor(($roller->quad) / ($q));
                    $qp = floor((($roller->quad * 10000) % ($q * 10000)) / 10000);

                    $key = (($qp <= 1) ? $dp . "0" : $dp) . "." . $qp;
                    if ($v->stock != $stock) $key = (string)(floatval($key) / 100000);
                    else $key = (string)(floatval($key));

                    $map[$key] = $index;
                }
                ksort($map);

                $count_temp = $count;
                foreach ($map as $index)
                    if ($Rollers[$index]->quad >= $q) {
                        $Rollers[$index]->quad -= $q;
                        $Rollers[$index]->realizate += $q;
                        $Rollers[$index]->off = true;
                        $count -= 1;
                        break;
                    }

                if ($count_temp == $count)
                    throw new Exception("Нет полотна под нужную квадратуру!<br>$v->country $v->name $v->width<br>Текстура: $v->texture"
                        . ((empty($v->color)) ? "" : "Цвет: $v->color") . "<br>Нужно: $q");
            }

            foreach ($Rollers as $index => $roller)
                if ($roller->off == false) unset($Rollers[$index]);

            $data[$id]->rollers = $Rollers;
        }
        return $data;
    }

    public function Realization($data, $info)
    {
        try {
            if (count($data) <= 0) return "";

            $db = $this->getDbo();
            $query = [];

            $customer = $info->customer;
            $dealer_id = $customer->dealer->id;
            $client_id = ($customer->client->id == null) ? "NULL" : "'" . $customer->client->id . "'";
            $counterparty_id = ($customer->dealer->counterparty == null) ? "NULL" : "'" . $customer->dealer->counterparty . "'";
            $stock = $customer->stock;

            $query_update = null;
            $query_delete = null;
            $query_analytic = null;
            $query_transfer = null;

            $query_analytic = $db->getQuery(true);
            $query_analytic->insert($db->quoteName('#__gm_ceiling_analytics_canvases'))
                ->columns("`canvas_id`, `roller_id`, `barcode`, `article`, `quad`, `price`, `stock`, `date_update`, `client_id`, `dealer_id`, `user_id`, `counterparty_id`, `status`");

            $query_transfer = $db->getQuery(true);
            $query_transfer->insert($db->quoteName('#__gm_ceiling_canvases_transfer'))
                ->columns("`canvas_id`, `quad`, `in`, `out`, `date`");
            $count_transefer = 0;

            foreach ($data as $index => $canvas) {
                foreach ($canvas->rollers as $roller) {
                    $price = ceil(floatval($canvas->price) * (floatval($roller->realizate)) * 100) / 100;

                    if ($roller->stock != $roller->stock2) {
                        $count_transefer += 1;
                        $query_transfer->values("'$canvas->id', '$roller->realizate', '$roller->stock2', '$roller->stock', '$info->date'");
                    }

                    if ($roller->quad == 0) {
                        $query_delete = $db->getQuery(true);
                        $query_delete->delete($db->quoteName('#__gm_ceiling_canvases_all'))
                            ->where("id = '$roller->id'");

                        $query_analytic->values("'$canvas->id', NULL, '$roller->barcode', '$roller->article', '-$roller->realizate', '$price', " .
                            "'$stock', '$info->date', $client_id, '$dealer_id', '$info->user', $counterparty_id, '2'");
                    } else {
                        $query_update = $db->getQuery(true);
                        $query_update->update($db->quoteName('#__gm_ceiling_canvases_all'))
                            ->where("id = '$roller->id'")
                            ->set("quad = '$roller->quad'");

                        $query_analytic->values("'$canvas->id', '$roller->id', '$roller->barcode', '$roller->article', '-$roller->realizate', '$price', " .
                            "'$stock', '$info->date', $client_id, '$dealer_id', '$info->user', $counterparty_id, '2'");
                    }

                    if (!empty($query_update)) $query[] = $query_update;
                    if (!empty($query_delete)) $query[] = $query_delete;
                }
            }

            $query[] = $query_analytic;
            if ($count_transefer > 0) $query[] = $query_transfer;

            return $query;
        } catch (Exception $e) {
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
}
