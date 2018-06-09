<?php
/**
 * @version    CVS: 0.1.7
 * @package    Com_Gm_ceiling
 * @author     CEH4TOP <CEH4TOP@gmail.com>
 * @copyright  2017 CEH4TOP
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

/* включаем библиотеку для формирования PDF */
//include($_SERVER['DOCUMENT_ROOT'] . "/libraries/mpdf/mpdf.php");

/**
 * Stock list controller class.
 *
 * @since  1.6
 */
class Gm_ceilingControllerStock extends JControllerLegacy
{
    public function &getModel($name = 'Stock', $prefix = 'Gm_ceilingModel', $config = array())
    {
        try {
            $model = parent::getModel($name, $prefix, array('ignore_request' => true));

            return $model;
        } catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }
    }

    public function Status()
    {
        try {
            $app = JFactory::getApplication();
            $status = $app->input->get('status', 0, 'int');
            $id = $app->input->get('id', 0, 'int');
            $model = $this->getModel('Project', 'Gm_ceilingModel');
            $model->newStatus($id, $status);
            die(json_encode(true));
        } catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }
    }

    public function Receipt()
    {
        try {
            $app = JFactory::getApplication();

            $dateFormat = (object)[];
            $dateFormat->date = date("d.m.Y");
            $dateFormat->day = date("d");
            $dateFormat->month = date("m");
            $dateFormat->year = date("Y");
            $dateFormat->time = time();
            $date = date("Y.m.d H:i:s");

            $stock = $app->input->get('stock', '', 'string');
            $goods = $app->input->get('goods', array(), 'array');
            $stock = json_decode($stock);

            $components = array();
            $canvases = array();

            $model = $this->getModel('Counterparty', 'Gm_ceilingModel');
            $result = $model->SetCounterparty($stock);
            $stock = $result->stock_id;
            $counterparty = $result->counterparty_id;

            $info = (object) [];
            $info->stock = $stock;
            $info->counterparty = $counterparty;
            $info->customer->dealer->counterparty = $counterparty;
            $info->dateFormat = $dateFormat;

            foreach ($goods AS $g) {
                $temp = json_decode($g);
                if ($temp->page == "Canvas") $canvases[] = $temp;
                else $components[] = $temp;
            }

            $errors = array();
            if ($stock == 0) {
                $errors[] = "Ошибка! Склад не выбран!";
            } else {
                $canvases_group = array();
                foreach ($canvases as $c) {
                    $c->Count = intval($c->Count);
                    $c->Width = floatval(str_replace(",", ".", $c->Width));
                    $c->Price = floatval(str_replace(",", ".", $c->Price));
                    $c->Quad = floatval(str_replace(",", ".", $c->Quad));

                    $id = $c->Name . $c->Country . $c->Width . $c->Texture . $c->Color;
                    if (empty($canvases_group[$id])) {
                        $canvas = (object)array();
                        $canvas->Name = $c->Name;
                        $canvas->Country = $c->Country;
                        $canvas->Width = $c->Width;
                        $canvas->Texture = $c->Texture;
                        $canvas->Color = $c->Color;
                        $canvas->Rollers = array();
                        $canvas->Count = 0;
                        $canvases_group[$id] = $canvas;
                    }

                    $roller = (object)array();
                    $roller->Barcode = $c->Barcode;
                    $roller->Article = $c->Article;
                    $roller->Quad = $c->Quad;
                    $roller->Count = $c->Count;
                    $canvases_group[$id]->Count += $c->Count;
                    $roller->Price = $c->Price;
                    $roller->Stock = $stock;
                    $roller->Counterparty = $counterparty;
                    $roller->Date = $date;
                    $canvases_group[$id]->Rollers[] = $roller;
                    $canvases_group[$id]->rollers[] = $roller;
                }
                $model = $this->getModel('CanvasForm', 'Gm_ceilingModel');
                $errors[] = $model->receipt($canvases_group);

                $components_group = array();
                foreach ($components as $c) {
                    $c->Count = floatval(str_replace(",", ".", $c->Count));
                    $c->CountUnit = floatval(str_replace(",", ".", $c->CountUnit));
                    $c->Price = floatval(str_replace(",", ".", $c->Price));

                    $id = $c->Type;
                    if (empty($components_group[$id])) {
                        $component = (object)array();
                        $component->Type = $c->Type;
                        $component->Unit = $c->Unit;
                        $component->Options = array();
                        $components_group[$id] = $component;
                    }
                    $id2 = $c->Name;
                    if (empty($components_group[$id]->Options[$id2])) {
                        $option = (object)array();
                        $option->Name = $c->Name;
                        $option->CountSale = $c->CountUnit;
                        $option->Count = 0;
                        $option->Goods = array();
                        $components_group[$id]->Options[$id2] = $option;
                    }

                    $good = (object)array();
                    $good->Barcode = $c->Barcode;
                    $good->Article = $c->Article;
                    $good->Count = $c->Count;
                    $components_group[$id]->Options[$id2]->Count += $c->Count;
                    $good->Price = $c->Price;
                    $good->Stock = $stock;
                    $good->Counterparty = $counterparty;
                    $good->Date = $date;
                    $components_group[$id]->Options[$id2]->Goods[] = $good;
                }
                $model = $this->getModel('ComponentForm', 'Gm_ceilingModel');
                $errors[] = $model->receipt($components_group);
            }


            $CanModel = $this->getModel('Canvases', 'Gm_ceilingModel');
            $ComModel = $this->getModel('Components', 'Gm_ceilingModel');

            $canvases = $CanModel->Format($canvases, "Receipt");
            $components = $ComModel->Format($components, "Receipt");

            foreach ($canvases as $k => $canvase) {
                $canvases[$k]->rollers = $canvase->quad;
            }

            try {
                $out = Gm_ceilingHelpersPDF::Format(array_merge($canvases, $components));
                $info->sum = $out->sum;

                $href = array();
                $href['InventoryOfGoods'] = Gm_ceilingHelpersPDF::InventoryOfGoods($info, $out->SalesInvoice);
                $href['RetailCashOrder'] = Gm_ceilingHelpersPDF::RetailCashOrder($info);
                $href['MergeFiles'] = Gm_ceilingHelpersPDF::MergeFiles($href);
                die(json_encode((object) ["status" => "ok", "href" => $href]));
            } catch (Exception $ex)
            {
                $errors[] = $ex->getMessage();
            }

            $successMessage = true;
            foreach ($errors as $e) if (!empty($e)) {
                $successMessage = false;
                break;
            }

            if ($successMessage) $this->setMessage("Прием произошел успешно!", 'success');
            else {
                $res = array("goods" => $goods, "stock" => $stock, "errors" => $errors);
                setcookie("receipt", json_encode($res), time() + 30);
            }

            $url = 'index.php?option=com_gm_ceiling&view=stock&type=receipt';
            $this->setRedirect(JRoute::_($url, false));
        } catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }
    }

    public function inventory()
    {
        try {
            $app = JFactory::getApplication();
            $date = date("Y-m-d H:i:s");

            $size = 200;

            $info = $app->input->get('info', null, 'Array');
            $info = ($info == null) ? null : (object)$info;

            if (empty($info->type)) die("{'error': 'Не правильная отправка данных!'}");
            else if ($info->type == 'SendCanvases') {
                $canvases = $app->input->get('canvases', null, 'Array');
                $model = $this->getModel('CanvasForm', 'Gm_ceilingModel');
                $result = $model->inventory($canvases, $date);

                $this->getModel()->updateCountGoods();

                if ($info->page != $info->pages) die(json_encode($result));
                else {
                    die(json_encode((object)array("document" => "http://".$_SERVER['SERVER_NAME']."/index.php?option=com_gm_ceiling&view=stock")));
                }
            } else if ($info->type == 'SendComponents') {
                $components = $app->input->get('components', null, 'Array');
                $model = $this->getModel('ComponentForm', 'Gm_ceilingModel');
                $result = $model->inventory($components, $date);

                $this->getModel()->updateCountGoods();

                if ($info->page != $info->pages) die(json_encode($result));
                else {
                    die(json_encode((object)array("document" => "http://".$_SERVER['SERVER_NAME']."/index.php?option=com_gm_ceiling&view=stock")));
                }
            } else if ($info->type == 'GetCanvases') {
                $model = $this->getModel('Canvases', 'Gm_ceilingModel');
                $canvases = $model->getCanvasesForInventory($info->start, $size);
                die(json_encode($canvases));
            } else if ($info->type == 'GetComponents') {
                $model = $this->getModel('Components', 'Gm_ceilingModel');
                $components = $model->getComponentsForInventory($info->start, $size);
                die(json_encode($components));
            } else die("{'error': 'Не правильная отправка данных!'}");
        } catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }
    }

    public function getCustomer()
    {
        try {
            $filter = JFactory::getApplication()->input->get('filter', array(), 'array');
            if ($filter != null) {
                $model = $this->getModel();
                $result = $model->getCustomer($filter);
                echo json_encode($result);
            }
            exit;
        } catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }
    }

    public function getProject()
    {
        $id = $_POST['id'];
        $data = $this->getModel('Project')->getProjectForStock($id);
        print_r($data); exit();
    }

    public function getHistoryComponent()
    {
        try {
            $app = JFactory::getApplication();

            $id = $app->input->get('id', 0, 'INT');
            $date = $app->input->get('date', null, 'ARRAY');
            $date = (object)array(
                "start" => ((empty($date['start'])) ? null : date("Y.m.d H:i:s", strtotime($date['start']))),
                "end" => ((empty($date['end'])) ? null : date("Y.m.d H:i:s", strtotime($date['end'])))
            );

            $model = $this->getModel();
            $data = $model->getHistoryComponent($id, $date);

            die(json_encode($data));
        } catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }
    }

    public function getHistoryCanvas()
    {
        try {
            $app = JFactory::getApplication();

            $id = $app->input->get('id', 0, 'INT');
            $date = $app->input->get('date', null, 'ARRAY');
            $date = (object)array(
                "start" => ((empty($date['start'])) ? null : date("Y.m.d H:i:s", strtotime($date['start']))),
                "end" => ((empty($date['end'])) ? null : date("Y.m.d H:i:s", strtotime($date['end'])))
            );

            $model = $this->getModel();
            $data = $model->getHistoryCanvas($id, $date);

            die(json_encode($data));
        } catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }
    }

    public function getCounterparty()
    {
        try {
            $filter = JFactory::getApplication()->input->get('filter', array(), 'array');
            if ($filter != null) {
                $model = $this->getModel('Counterparty', 'Gm_ceilingModel');
                $result = $model->getCounterparty($filter);
                echo json_encode($result);
            }
            exit;
        } catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }
    }

    public function Realization()
    {
        try {
            $CBR = json_decode(file_get_contents("https://www.cbr-xml-daily.ru/daily_json.js"));
            $USD = $CBR->Valute->USD->Value;
            $PriceCanvasUSD = floatval(0.75);

            $app = JFactory::getApplication();
            $user = JFactory::getUser();
            $model = $this->getModel();
            $date = date("Y-m-d H:i:s");
            $dateFormat = (object)[];
            $dateFormat->date = date("d.m.Y");
            $dateFormat->day = date("d");
            $dateFormat->month = date("m");
            $dateFormat->year = date("Y");
            $dateFormat->time = time();

            $canvases = array();
            $components = array();
            if(!is_array($_POST["customer"])) $customer = json_decode($_POST["customer"]);
            else $customer = $_POST["customer"];
            $customer->stock = $_POST["stock"];
            $customer->project = $_POST["project"];
            $status = $_POST["status"];
            $Counterparty = $this->getModel('Counterparty', 'Gm_ceilingModel');
            $customer->dealer->counterparty = empty($Counterparty->getCounterparty(array("user_id" => $customer->dealer->id))[0]->id) ? 1 : $Counterparty->getCounterparty(array("user_id" => $customer->dealer->id))[0]->id;
           
            if (empty($customer->dealer->counterparty))
                die(json_encode((object) ["status" => "error", "error" => "У дилера закончился срок договора!\nРеализация не возможна!"]));
            /*Не испоблзутеся*/
           /* if(isset($_POST["valute"])){
                $valute = $_POST["valute"];
            }*/
            $client = $customer->client;
            $dealer = $customer->dealer;
            $margin = JFactory::getUser($dealer->id)->getDealerInfo();

            $info = (object) [];
            $info->customer = $customer;
            $info->date = $date;
            $info->user = $user->id;
            $info->stock = $customer->stock;
            $info->dateFormat = $dateFormat;

            foreach ($_POST["goods"] as $good) {
                $good = json_decode($good);
                if ($good->page == "Canvas") $canvases[] = $good;
                else $components[] = $good;
            }

            $CanModel = $this->getModel('Canvases', 'Gm_ceilingModel');
            $ComModel = $this->getModel('Components', 'Gm_ceilingModel');

            try {
                $canvases = $CanModel->Format($canvases, "Realization");
                if ($customer->type == 2)
                    foreach ($canvases as $i => $c) $canvases[$i]->price = ceil($PriceCanvasUSD * $USD * 100)/100;

                $components = $ComModel->Format($components, "Realization");

                if (($customer->type != 4 || !empty($customer->client)) && !empty($margin))
                {
                    foreach ($canvases as $i => $c) $canvases[$i]->price = ((floatval($c->price) * 100)/(100 - floatval($margin->dealer_canvases_margin)));
                    foreach ($components as $i => $c) $components[$i]->price = ((floatval($c->price) * 100)/(100 - floatval($margin->dealer_components_margin)));
                }

                if (count($components) + count($canvases) <= 0)
                    throw new Exception("Пустую реализацию нельзя проводить!");

            }
            catch (Exception $ex)
            {
                die(json_encode((object) ["status" => "error", "error" => $ex->getMessage()]));
            }

            $CanForModel = $this->getModel('CanvasForm', 'Gm_ceilingModel');
            $CanRealization = null;
            $ComForModel = $this->getModel('ComponentForm', 'Gm_ceilingModel');
            $ComRealization = null;

            try {
                if (count($canvases) > 0) $CanRealization = $CanForModel->TestRealization($canvases, $customer);
                if (count($components) > 0) $ComRealization = $ComForModel->TestRealization($components, $customer);
            }
            catch (Exception $ex)
            {
                die(json_encode((object) ["status" => "error", "error" => $ex->getMessage()]));
            }
            try {
                if (empty($status) || floatval($status) == 5 || floatval($status) == 6)
                {
                    $query = [];
                    if (count($canvases) > 0) $query = array_merge($query, $CanForModel->Realization($CanRealization, $info));
                    if (count($components) > 0) $query = array_merge($query, $ComForModel->Realization($ComRealization, $info));
                    $result = $model->setQuery($query);

                    if ($result) $model->updateCountGoods();
                    else throw new Exception("Произошла ошибка изменения!<br>Обратитесь в техподдержку!");
                }
            }
            catch (Exception $ex)
            {
                die(json_encode((object) ["status" => "error", "error" => $ex->getMessage()]));
            }

            try {
                if (!empty($customer->project))
                    $model->NextStatusProject($customer->project);
            }
            catch (Exception $ex)
            {
                die(json_encode((object) ["status" => "error", "error" => $ex->getMessage()]));
            }

            try {
                $message = "";
                if (empty($status)) $message = "Реализация прошла успешно";
                else if (floatval($status) == 5) $message = "Укомплектация прошла успешно";
                else if (floatval($status) == 6) $message = "Соборка прошла успешно";
                else if (floatval($status) == 19) $message = "Выдача прошла успешно";

                if (empty($status) || floatval($status) == 5 || floatval($status) == 6)
                {
                    $out = Gm_ceilingHelpersPDF::Format(array_merge($canvases, $components));
                    $info->sum = $out->sum;

                    $href = array();
                    $href['PackingList'] = Gm_ceilingHelpersPDF::PackingList($info, $out->PackingList);
                    $href['RetailCashOrder'] = Gm_ceilingHelpersPDF::RetailCashOrder($info);
                    $href['SalesInvoice'] = Gm_ceilingHelpersPDF::SalesInvoice($info, $out->SalesInvoice);
                    $href['MergeFiles'] = Gm_ceilingHelpersPDF::MergeFiles($href);
                    die(json_encode((object) ["status" => "ok", "href" => $href, "message" => $message]));
                }
                else
                    die(json_encode((object) ["status" => "ok", "message" => $message]));
            }
            catch (Exception $ex)
            {
                die(json_encode((object) ["status" => "error", "error" => $ex->getMessage()]));
            }
        } catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
            /*$date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            die(json_encode((object) ["status" => "error", "error" => $e->getMessage()]));*/
        }
    }

    public function Moving()
    {
        try {
            $head = (object)array();
            $head->type = "moving";
            $head->number = 0;
            $head->name = $head->type . "/" . $head->number;

            $head->day = date("d");
            $head->month = date("m");
            $head->year = date("Y");

            $goods = array();

            $data = (object)array("head" => $head, "goods" => $goods);
            $result = Gm_ceilingHelpersPDF::Moving($data);
            /*
                    $pdf=fopen($result->name,'r');
                    $content=fread($pdf,filesize($result->name));
                    fclose($pdf);
                    header('Content-type: application/pdf');
                    print($content);
                    exit;
            */
            $url = "index.php?option=com_gm_ceiling&view=stock&type=document&document=" . $head->type . "&number=" . $head->number;
            $this->setRedirect(JRoute::_($url, false));
        } catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }
    }

    public function Posting()
    {
        try {
            $head = (object)array();
            $head->type = "posting";
            $head->number = 0;
            $head->name = $head->type . "/" . $head->number;

            $head->day = date("d");
            $head->month = date("m");
            $head->year = date("Y");

            $goods = array();
            $good = (object)array("Индекс" => "1", "Название" => "кольцо", "Количество" => 50, "Размерность" => "шт.", "Цена" => 5.00, "Сумма" => 250.00);
            $goods[] = $good;

            $data = (object)array("head" => $head, "goods" => $goods);
            $result = Gm_ceilingHelpersPDF::Posting($data);

            $pdf = fopen($result->name, 'r');
            $content = fread($pdf, filesize($result->name));
            fclose($pdf);
            header('Content-type: application/pdf');
            print($content);
            exit;
        } catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }
    }

    public function MergeFiles() {
        $files = $_POST["files"];
        die(Gm_ceilingHelpersPDF::MergeFiles($files));
    }

    public function FreeLine() {
        $query = $_POST['query'];
        $query = (empty($query))?$_GET['query']:$query;

        if (isset($query))
        {
            $model = $this->getModel();
            $model->setQuery($query);
        }
    }
}

