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
            $info->customer = (object)['dealer'=>(object)['counterparty' => $counterparty]];
            //$info->customer->dealer->counterparty = $counterparty;
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
                    if(isset($c->Count)){
                         $c->Count = intval($c->Count);
                    }
                    else {
                         $c->Count = 0;
                    }
                    $c->Width = str_replace(",", ".", $c->Width);
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
            $filter = JFactory::getApplication()->input->get('filter', '', 'STRING');
            if (!empty($filter)) {
                $model = $this->getModel();
                $result = $model->getCustomer($filter);
                die(json_encode($result));
            }
        } catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }
    }

    public function getProject()
    {
        $id = $_POST['id'];
        $data = $this->getModel('Project')->getProjectForStock($id);
        print_r($data); die(true);
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

    public function addCounterparty(){
        try {
            $jinput = JFactory::getApplication()->input;
            $user_id = $jinput->get('user_id',null,'INT');
            $name = $jinput->get('name','','STRING');
            $phone = $jinput->get('phone','','STRING');
            $email = $jinput->get('email','','STRING');
            $model = Gm_ceilingHelpersGm_ceiling::getModel('counterparty');
            die(json_encode($model->addCounterpartyForDealer($user_id,$name,$phone,$email)));
        }
        catch(Exception $e){
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function saveProvider(){
        try{
            $jinput = JFactory::getApplication()->input;
            $provider = $jinput->get(data,'','STRING');
            $counterpartyModel = Gm_ceilingHelpersGm_ceiling::getModel('counterparty');
            $result = $counterpartyModel->setCounterParty(json_decode($provider));
            die(json_encode($result));
        }
        catch(Exception $e){
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function addProvider(){
        try{
            $jinput = JFactory::getApplication()->input;
            $provider = $jinput->get('provider','','STRING');
            $counterpartyModel = Gm_ceilingHelpersGm_ceiling::getModel('counterparty');
            $result = $counterpartyModel->addProvider(json_decode($provider));
            die(json_encode($result));
        }
        catch(Exception $e){
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
    public static function margin($value, $margin) {
        try {
            if($margin == 100){
                $margin = 99;
            }
            return ($value * 100 / (100 - $margin));
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public static function dealer_margin($price, $margin, $objectDealerPrice) {
        try {
            $result = 0;
            if (!empty($objectDealerPrice)) {
                $objectDealerPrice->value = floatval($objectDealerPrice->value);
                $objectDealerPrice->price = floatval($objectDealerPrice->price);
                switch ($objectDealerPrice->type)
                {
                    case 0: $result = $price; break;
                    case 1: $result = $objectDealerPrice->price; break;
                    case 2: $result = $price + $objectDealerPrice->value; break;
                    case 3: $result = $price + $price * $objectDealerPrice->value / 100; break;
                    case 4: $result = $objectDealerPrice->price + $objectDealerPrice->value; break;
                    case 5: $result = $objectDealerPrice->price + $objectDealerPrice->price * $objectDealerPrice->value / 100; break;
                }
            }
            else{
                $result = $price;
            }
            return ($margin > 0)? self::margin($result, $margin):$result;
        }
        catch(Exception $e)
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
            $dealerObject = JFactory::getUser($dealer->id);
            $margin = $dealerObject->getDealerInfo();
            $ComDP = $dealerObject->getComponentsPrice();
            $CanDP = $dealerObject->getCanvasesPrice();

            $info = (object) [];
            $info->customer = $customer;
            $info->date = $date;
            $info->user = $user->id;
            $info->stock = $customer->stock;
            $info->dateFormat = $dateFormat;

            try {
                if (!empty($customer->project))
                    $model->NextStatusProject($customer->project);
                else
                    $customer->project = $model->AddProject($client);
            }
            catch (Exception $ex)
            {
                die(json_encode((object) ["status" => "error", "error" => $ex->getMessage()]));
            }

            $info->project = $customer->project;

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

                foreach ($components as $index => $component) {
                    $components[$index]->price = self::dealer_margin($component->price, 0, $ComDP[$index]);
                }

                foreach ($canvases as $index => $canvase) {
                    $canvases[$index]->price = $CanDP[$index]->price_itog;

                    $price = $canvases[$index]->price;
                    $sum = 0;
                    $discount_sum = 0;
                    foreach ($canvase->discount as $i => $v) {
                        $sum += $v * $price;
                        $discount_price = ($i == 100)?$price:$price * ((100 - $i) / 100);
                        $discount_sum += $v * $discount_price;
                    }
                    $canvases[$index]->price = $discount_sum * $price / $sum;
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
            //throw new Exception(print_r($CanRealization,true));
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
                $message = "";
                if (empty($status)) $message = "Реализация прошла успешно";
                else if (floatval($status) == 5) $message = "Укомплектация прошла успешно";
                else if (floatval($status) == 6) $message = "Соборка прошла успешно";
                else if (floatval($status) == 19) $message = "Выдача прошла успешно";

                if (empty($status) || floatval($status) == 5 || floatval($status) == 6)
                {
                    $allGoods = array_merge($canvases, $components);

                    $out = Gm_ceilingHelpersPDF::Format($allGoods);

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
            die(true);
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

    public function getStockGoods()
    {
        try
        {
            $jinput = JFactory::getApplication()->input;
            $goods_id = $jinput->get('goods_id', null, 'INT');

            $model_goods = $this->getModel('Stock', 'Gm_ceilingModel');

            $result = $model_goods->getGoods($goods_id);

            die(json_encode($result));
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function getCounterparty()
    {
        try {
            $jinput = JFactory::getApplication()->input;
            $model = $this->getModel('Stock', 'Gm_ceilingModel');
            $result = $model->getCounterparty();

            die(json_encode($result));

        } catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }
    }

    public function getStocks(){
        try {
            $jinput = JFactory::getApplication()->input;
            $goods_id = $jinput->get('goods_id', null, 'INT');

            $model_goods = $this->getModel('Stock', 'Gm_ceilingModel');

            $result = $model_goods->getStocks($goods_id);

            die(json_encode($result));

        } catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }
    }

    public function saveInventory(){
        try {
            $userId = JFactory::getUser()->id;
            $jinput = JFactory::getApplication()->input;
            $array = $jinput->get('array', null, 'ARRAY');
            $id_stock = $jinput->get('id_stock', null, 'INT');
            $id_counterparty = $jinput->get('id_counterparty', null, 'INT');
            $ids = implode(',',array_column($array,'id'));
            $model_stock = $this->getModel('Stock', 'Gm_ceilingModel');
            $goods_cost = [];
            foreach ($array as $item) {
                $goods_cost[$item['id']]['cost'] = $item['cost'];
                $goods_cost[$item['id']]['count'] = $item['count'];
            }
            $goods = $model_stock->getGoodsArrayByIds($ids);
            foreach($goods as $value){
                $value->cost = $goods_cost[$value->goods_id]['cost'];
                $value->count = $goods_cost[$value->goods_id]['count'];
            }
            $model_stock->saveDataInventory($array, $id_stock, $id_counterparty);

            $date = date("Y-m-d H:i:s");
            $dateFormat = (object)[];
            $dateFormat->date = date("d.m.Y");
            $dateFormat->day = date("d");
            $dateFormat->month = date("m");
            $dateFormat->year = date("Y");
            $dateFormat->time = time();

            $info = (object) [];
            $info->counterparty = $id_counterparty;
            $info->customer = (object)['dealer'=>(object)['counterparty' => $id_counterparty]];
            $info->date = $date;
            $info->user = $userId;
            $info->stock = $id_stock;
            $info->dateFormat = $dateFormat;


            $out = Gm_ceilingHelpersPDF::Format($goods);

            $info->sum = $out->sum;
            $href = array();
            $href['InventoryOfGoods'] = Gm_ceilingHelpersPDF::InventoryOfGoods($info, $out->SalesInvoice);
            $href['RetailCashOrder'] = Gm_ceilingHelpersPDF::RetailCashOrder($info);
            $href['MergeFiles'] = Gm_ceilingHelpersPDF::MergeFiles($href);


            /*
             *  $info->sum = $out->sum;
                $href = array();
                $href['PackingList'] = Gm_ceilingHelpersPDF::PackingList($info, $out->PackingList);
                $href['RetailCashOrder'] = Gm_ceilingHelpersPDF::RetailCashOrder($info);
                $href['SalesInvoice'] = Gm_ceilingHelpersPDF::SalesInvoice($info, $out->SalesInvoice);
                $href['MergeFiles'] = Gm_ceilingHelpersPDF::MergeFiles($href);*/
            die(json_encode((object) ["status" => "ok", "href" => $href]));


        } catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }
    }

    public function addPropColor() {
        try {
            $jinput = JFactory::getApplication()->input;
            $id = $jinput->get('id', null, 'INT');
            $value = $jinput->get('value', '', 'STRING');

            $model_stock = $this->getModel('Stock', 'Gm_ceilingModel');
            $result = $model_stock->addPropColor($id, $value);
            return $result;
        } catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function addPropManufacturer() {
        try {
            $jinput = JFactory::getApplication()->input;
            $value = $jinput->get('value', '', 'STRING');

            $model_stock = $this->getModel('Stock', 'Gm_ceilingModel');
            $result = $model_stock->addPropManufacturer($value);
            return $result;
        } catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function addPropTexture() {
        try {
            $jinput = JFactory::getApplication()->input;
            $value = $jinput->get('value', '', 'STRING');

            $model_stock = $this->getModel('Stock', 'Gm_ceilingModel');
            $result = $model_stock->addPropTexture($value);
            return $result;
        } catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function delPropColor() {
        try {
            $jinput = JFactory::getApplication()->input;
            $id = $jinput->get('id', null, 'INT');

            $model_stock = $this->getModel('Stock', 'Gm_ceilingModel');
            $result = $model_stock->delPropColor($id);
            return $result;
        } catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function delPropManufacturer() {
        try {
            $jinput = JFactory::getApplication()->input;
            $id = $jinput->get('id', null, 'INT');

            $model_stock = $this->getModel('Stock', 'Gm_ceilingModel');
            $result = $model_stock->delPropManufacturer($id);
            return $result;
        } catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function delPropTexture() {
        try {
            $jinput = JFactory::getApplication()->input;
            $id = $jinput->get('id', null, 'INT');

            $model_stock = $this->getModel('Stock', 'Gm_ceilingModel');
            $result = $model_stock->delPropTexture($id);
            return $result;
        } catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function editPropColor() {
        try {
            $jinput = JFactory::getApplication()->input;
            $id = $jinput->get('id', null, 'INT');
            $value = $jinput->get('value', '', 'STRING');

            $model_stock = $this->getModel('Stock', 'Gm_ceilingModel');
            $result = $model_stock->editPropColor($id, $value);
            return $result;
        } catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function editPropManufacturer() {
        try {
            $jinput = JFactory::getApplication()->input;
            $id = $jinput->get('id', null, 'INT');
            $value = $jinput->get('value', '', 'STRING');

            $model_stock = $this->getModel('Stock', 'Gm_ceilingModel');
            $result = $model_stock->editPropManufacturer($id, $value);
            return $result;
        } catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function editPropTexture() {
        try {
            $jinput = JFactory::getApplication()->input;
            $id = $jinput->get('id', null, 'INT');
            $value = $jinput->get('value', '', 'STRING');

            $model_stock = $this->getModel('Stock', 'Gm_ceilingModel');
            $result = $model_stock->editPropTexture($id, $value);
            return $result;
        } catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function addGoods(){
        try{
            $jinput = JFactory::getApplication()->input;
            $category = $jinput->get('category',null,'INT');
            $goodsName = $jinput->get('goodsName',"",'STRING');
            $goodsUnit = $jinput->get('goodsUnit',null,'INT');
            $goodsMultiplicity = $jinput->get('goodsMultiplicity',null,'FLOAT');
            $goodsPrice = $jinput->get('goodsPrice',null,'FLOAT');
            $texture = $jinput->get('texture',null,'INT');
            $manufacturer = $jinput->get('manufacturer',null,'INT');
            $width = $jinput->get('width',null,'INT');
            $color = $jinput->get('color',null,'INT');
            if(!empty($category)){
                $stockModel = $this->getModel('Stock','Gm_ceilingModel');
                switch ($category){
                    case 1:
                        if(!empty($texture)&&!empty($manufacturer)&&!empty($width)&&!empty($color)&&!empty($goodsName)&&!empty($goodsMultiplicity)&&!empty($goodsUnit)&&!empty($goodsPrice)){
                            //insert into goods
                            $goodsId = $stockModel->addGoods($category,$goodsName,$goodsUnit,$goodsMultiplicity,$goodsPrice);
                            //goods_map_color
                            $stockModel->addProps('rgzbn_gm_stock_map_prop_colors','color',$goodsId,$color);
                            //goods_map_manufacturers
                            $stockModel->addProps('rgzbn_gm_stock_map_prop_manufacturers','manufacturer_id',$goodsId,$manufacturer);
                            //goods_map_textures
                            $stockModel->addProps('rgzbn_gm_stock_map_prop_textures','texture_id',$goodsId,$texture);
                            //goods_map_width
                            $stockModel->addProps('rgzbn_gm_stock_map_prop_canvas_widths','width',$goodsId,$width);
                            //привязываем натяжку за каждым полотном
                            //$stockModel->addJobToGoods($goodsId,26);
                        }
                        else{
                            throw new Exception("Empty data!");
                        }
                            break;
                    case 4:
                        if(!empty($color)&&!empty($goodsName)&&!empty($goodsMultiplicity)&&!empty($goodsUnit)&&!empty($goodsPrice)){
                            //insert into goods
                            $goodsId = $stockModel->addGoods($category,$goodsName,$goodsUnit,$goodsMultiplicity,$goodsPrice);
                            //goods_map_color
                            $stockModel->addProps('rgzbn_gm_stock_map_prop_colors','color',$goodsId,$color);
                        }
                        else{
                            throw new Exception("Empty data!");
                        }
                        break;
                    default:
                        if(!empty($goodsName)&&!empty($goodsMultiplicity)&&!empty($goodsUnit)&&!empty($goodsPrice)) {
                            $goodsId = $stockModel->addGoods($category,$goodsName,$goodsUnit,$goodsMultiplicity,$goodsPrice);
                        }
                        else{
                            throw new Exception("Empty data!");
                        }
                        break;
                }
            }
            else{
                throw new Exception("empty category!");
            }
            die(json_encode(true));
        }catch(Exception $e){
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function makeRealisation($projectId = null,$data = null,$stockId = null,$noneAjax = null){
        try{
            $realiseArrays = [];
            $jinput = JFactory::getApplication()->input;
            if(empty($projectId)){
                $projectId = $jinput->getInt('project_id');
            }
            if(empty($stockId)){
                $stockId = $jinput->getInt('stock');
            }
            $input_data = $jinput->get('goods','','STRING');
            if(!empty($input_data)){
                $goods = json_decode($input_data);
            }
            if(!empty($data)) {
                $goods = $data->goods;
                $customer = $data->customer;
            }

            if(empty($customer)){
                $projectModel = Gm_ceilingHelpersGm_ceiling::getModel('project');
                $customer = $projectModel->getProjectForStock($projectId)->customer;
            }

            $stockModel = $this->getModel('Stock','Gm_ceilingModel');
            $projectModel = Gm_ceilingHelpersGm_ceiling::getModel('Project');
            $goodsInventory = $stockModel->getGoodsFromInvetory($goods->ids);
            $diff = [];
            $userId = JFactory::getUser()->id;
            foreach ($goods->goods as $key=>$value){
                $existInInventory = false;
                foreach ($goodsInventory as $key2=>$value2){
                    if($value->goods_id == $value2->goods_id){
                        $existInInventory = true;
                        if($value->count > $value2->total_count){
                            array_push($diff,$value);
                        }
                    }
                }
                if(!$existInInventory){
                    array_push($diff,$value);
                }
            }
            if(count($diff)){
                $response = (object)array("type"=>"error","text"=>"Реализация невозможна, некоторые товары отсутствуют на складе!","goods"=>$diff);
                if($noneAjax){
                    return $response;
                }
                else{
                    die(json_encode($response,JSON_UNESCAPED_UNICODE ));
                }
            }
            else{
                foreach($goods->goods as $key => $value){
                    $realiseCount = $value->count;
                    foreach ($goodsInventory as $value1){
                        if($value1->goods_id == $value->goods_id){
                            $goodsExistenceArray = json_decode($value1->detailed_count);
                            break;
                        }
                    }
                    $goodsExistanceOnStocks = [];
                    $gTotalCountOnStocks = [];
                    foreach ($goodsExistenceArray as $var) {
                        $goodsExistanceOnStocks[$var->stock][] = $var;
                    }
                    foreach ($goodsExistanceOnStocks as $stock_id=>$var) {
                        $gTotalCountOnStocks[$stock_id] = array_sum(array_column($var, 'count'));
                    }
                    if(!empty($goodsExistenceArray)) {
                        if ($value->category_id != 1) { //реализация для компонентов, берем то что раньше приняли и списываем частями если не хватает целиком
                            if ($gTotalCountOnStocks[$stockId] >= $realiseCount) {
                                //если общее количество компонента на выбранном складе достаточно, то списываем
                                if(empty($realiseArrays)){
                                    $realiseArrays = $this->makeArrayForRealisation($goodsExistanceOnStocks[$stockId],$realiseCount,$value->dealer_price,$projectId,$userId);
                                }
                                else{
                                    $goodsToRealisationArray = $this->makeArrayForRealisation($goodsExistanceOnStocks[$stockId],$realiseCount,$value->dealer_price,$projectId,$userId);
                                    $realiseArrays['realisation'] = array_merge($realiseArrays['realisation'],$goodsToRealisationArray['realisation']);
                                    $realiseArrays['inventory'] = array_merge($realiseArrays['inventory'],$goodsToRealisationArray['inventory']);
                                }

                            } else {
                                //если не хватает общего количества на выбранном складе
                                $lackOfQuantity = $realiseCount - $gTotalCountOnStocks[$stockId];
                                $moveArray = [];
                                $updateInvetory = [];
                                foreach ($goodsExistanceOnStocks as $stock_id=>$goodsArrayOnStock) {
                                    //бежим по остальным складам
                                    if($stockId != $stock_id){
                                        foreach($goodsArrayOnStock as $stockGoods){
                                            //если кол-во достаточное, готовим массивы для перемещения кол-ва с одного на другой склад
                                            if($stockGoods->count >= $lackOfQuantity){
                                                $moveArray[] = (object)array("old_stock"=>$stock_id,"new_stock"=>$stockId,
                                                                            "count"=>$lackOfQuantity,"old_inventory_id"=>$stockGoods->id,"goods_id"=>$value->goods_id);
                                                $updateInvetory[] = (object)array("id" => $stockGoods->id, "count" => $stockGoods->count - $lackOfQuantity);
                                                $lackOfQuantity = 0;
                                                break;
                                            }
                                            else {
                                                //иначе собираем кол-во частями
                                                if ($lackOfQuantity > 0 && $stockGoods > 0) {
                                                    $partOfLack = $stockGoods->count;
                                                    $lackOfQuantity -= $partOfLack;
                                                    $moveArray[] = (object)array("old_stock" => $stock_id, "new_stock" => $stockId, "count" => $partOfLack,"old_inventory_id"=>$stockGoods->id,"goods_id"=>$value->goods_id);
                                                    $updateInvetory[] = (object)array("id" => $stockGoods->id, "count" => 0);
                                                }
                                            }
                                        }
                                        if($lackOfQuantity == 0){
                                            break;
                                        }
                                    }
                                }
                                //перемещаем товары и добавляем только что созданные позициии
                                //в таблице inventaries в массив наличия на складе и создаем массивы для реализации
                                $newGoodsOnStock = $stockModel->makeGoodsMovement($moveArray,$updateInvetory);

                                $goodsExistanceOnStocks[$stockId] = array_merge($goodsExistanceOnStocks[$stockId],$newGoodsOnStock);
                                if(empty($realiseArrays)){
                                    $realiseArrays = $this->makeArrayForRealisation($goodsExistanceOnStocks[$stockId],$realiseCount,$value->dealer_price,$projectId,$userId);
                                }
                                else{
                                    $goodsToRealisationArray = $this->makeArrayForRealisation($goodsExistanceOnStocks[$stockId],$realiseCount,$value->dealer_price,$projectId,$userId);
                                    $realiseArrays['realisation'] = array_merge($realiseArrays['realisation'],$goodsToRealisationArray['realisation']);
                                    $realiseArrays['inventory'] = array_merge($realiseArrays['inventory'],$goodsToRealisationArray['inventory']);
                                }
                            }
                        }
                        else{
                            //реализация для полотна, берем то что подходит по квадратуре целиком
                            foreach ($goodsExistanceOnStocks[$stockId] as $existGoods){
                                //бежим по компонентам на выбранном складе
                                if($existGoods->count >= $realiseCount){
                                    //если кол-во больше списываемого, создаем объекты и выходим из цикла
                                    $rCanvObject = (object)array("inventory_id" => $existGoods->id, "sale_price" => $value->dealer_price, "count" => $realiseCount, "date_time" => "'" . date('Y-m-d H:i:s') . "'", "project_id" => $projectId, "created_by" => $userId);
                                    $uCanvObject = (object)array("id" => $existGoods->id, "count" => $existGoods->count - $realiseCount);
                                    $realiseCount = 0;
                                    break;
                                }
                            }

                            if($realiseCount != 0){
                                //если на выбранном складе нет нужного кол-ва бежим по другим складам и производим такой же поиск
                                foreach ($goodsExistanceOnStocks as $stock_id=>$goodsArrayOnStock) {
                                    if ($stockId != $stock_id) {
                                        foreach($goodsArrayOnStock as $stockGoods){
                                            if($stockGoods->count >= $realiseCount){
                                                $moveArray[] = (object)array("old_stock"=>$stock_id,"new_stock"=>$stockId,
                                                    "count"=>$realiseCount,"old_inventory_id"=>$stockGoods->id,"goods_id"=>$value->goods_id);
                                                $updateInvetory[] = (object)array("id" => $stockGoods->id, "count" => $stockGoods->count - $realiseCount);
                                                $newGoodsOnStock = $stockModel->makeGoodsMovement($moveArray,$updateInvetory);
                                                $rCanvObject = (object)array("inventory_id" => $newGoodsOnStock[0]->id, "sale_price" => $value->dealer_price, "count" => $realiseCount, "date_time" => "'" . date('Y-m-d H:i:s') . "'", "project_id" => $projectId, "created_by" => $userId);
                                                $uCanvObject = (object)array("id" => $newGoodsOnStock[0]->id, "count" => $newGoodsOnStock[0]->count - $realiseCount);
                                                $realiseCount = 0;
                                                break;
                                            }
                                        }
                                    }
                                }
                                if($realiseCount!= 0){
                                    //если не нашли подходящее, то возвращаем ошиьбку
                                    $response = (object)array("type"=>"error","text"=>"Реализация невозможна, товара не хватает на складе!","goods"=>[$value]);
                                    if($noneAjax){
                                        return $response;
                                    }
                                    else{
                                        die(json_encode($response,JSON_UNESCAPED_UNICODE ));
                                    }
                                }
                            }

                            if($realiseCount == 0 && !empty($rCanvObject)&&!empty($uCanvObject)){
                                //если найдено и объекты созданы добавляем их в массив для реализации
                                $realiseArrays['realisation'][] = $rCanvObject;
                                $realiseArrays['inventory'][] = $uCanvObject;
                            }
                        }
                    }
                    else{
                        throw new Exception("EMPTY!!!");
                    }
                }
                //throw new Exception(print_r($realiseArrays,true));
                $stockModel->makeRealisation( $realiseArrays['realisation'],$realiseArrays['inventory']); // обновление данных в таблице inventories и записиь в sales
                if(!$noneAjax){
                    $projectModel->change_status($projectId,8);//переводим в статус "Выдан", только если со страницы кладовщика(пришло через ajax)
                }

                $date = date("Y-m-d H:i:s");
                $dateFormat = (object)[];
                $dateFormat->date = date("d.m.Y");
                $dateFormat->day = date("d");
                $dateFormat->month = date("m");
                $dateFormat->year = date("Y");
                $dateFormat->time = time();

                $info = (object) [];
                $info->customer = $customer;
                $info->date = $date;
                $info->user = $userId;
                $info->stock = $stockId;
                $info->dateFormat = $dateFormat;

                $out = Gm_ceilingHelpersPDF::Format($goods->goods);

                $info->sum = $out->sum;
                $href = array();
                $href['PackingList'] = Gm_ceilingHelpersPDF::PackingList($info, $out->PackingList);
                $href['RetailCashOrder'] = Gm_ceilingHelpersPDF::RetailCashOrder($info);
                $href['SalesInvoice'] = Gm_ceilingHelpersPDF::SalesInvoice($info, $out->SalesInvoice);
                $href['MergeFiles'] = Gm_ceilingHelpersPDF::MergeFiles($href);
                $response = (object)array("status"=>"ok","href" =>$href);
                if($noneAjax){
                    return $response;
                }
                else{
                    die(json_encode($response));
                }
            }

        }
        catch(Exception $e){
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function makeArrayForRealisation($goodsExistanceOnStock,$realiseCount,$dealer_price,$projectId,$userId){
        try{
            $result = [];
            $realisationArr = [];
            $updateInventoryArr = [];
            foreach ($goodsExistanceOnStock as $i_goods) {
                if ($i_goods->count >= $realiseCount) {
                    //throw new Exception("$i_goods->count >= $realiseCount");
                    $rObject = (object)array("inventory_id" => $i_goods->id, "sale_price" => $dealer_price, "count" => $realiseCount, "date_time" => "'" . date('Y-m-d H:i:s') . "'", "project_id" => $projectId, "created_by" => $userId);
                    $realisationArr[] = $rObject;

                    $uObject = (object)array("id" => $i_goods->id, "count" => $i_goods->count - $realiseCount);
                    $updateInventoryArr[] = $uObject;
                    $realiseCount = 0;
                } else {
                    if ($realiseCount > 0 && $i_goods->count != 0) {
                        $partOfCount = $i_goods->count;
                        $realiseCount -= $partOfCount;
                        $rObject = (object)array("inventory_id" => $i_goods->id, "sale_price" => $dealer_price, "count" => $partOfCount, "date_time" => "'" . date('Y-m-d H:i:s') . "'", "project_id" => $projectId, "created_by" => $userId);
                        $realisationArr[] = $rObject;


                        $uObject = (object)array("id" => $i_goods->id, "count" => 0);
                        $updateInventoryArr[] = $uObject;
                    }
                }
                if ($realiseCount == 0) {
                    break;
                }
            }
            $result['realisation'] = $realisationArr;
            $result['inventory'] = $updateInventoryArr;
            return $result;
        }
        catch(Exception $e){
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
    function makeReturn(){
        try{
            $jinput = JFactory::getApplication()->input;
            $projectId = $jinput->getInt('project_id');
            $input_data = json_decode($jinput->get('data','','STRING'));
            $stockModel = $this->getModel('Stock','Gm_ceilingModel');
            $stockModel->makeReturn($input_data->return_array,$input_data->realisation_update,$input_data->inventory_update,$projectId);
        }
        catch(Exception $e){
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function updateGoods(){
        try{
            $jinput = JFactory::getApplication()->input;
            $goodsId = $jinput->getInt('goodsId');
            $name =  $jinput->get('name','','STRING');
            $price =  $jinput->get('price','','STRING');
            $stockModel = $this->getModel('Stock','Gm_ceilingModel');
            $stockModel->updateGoods($goodsId,$name,$price);
            die(json_encode(true));
        }
        catch(Exception $e){
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function getReceivedGoods(){
        try{
            $jinput = JFactory::getApplication()->input;
            $dateFrom = $jinput->get('date_from','','STRING');
            $dateTo = $jinput->get('date_to','','STRING');
            $search = $jinput->get('search','','STRING');
            $stockModel = $this->getModel('Stock','Gm_ceilingModel');
            $data = $stockModel->getReceivedGoods($search,$dateFrom,$dateTo);
            die(json_encode($data));
        }
        catch(Exception $e){
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function getGoodsByCategory(){
        try{
            $result = [];
            $jinput = JFactory::getApplication()->input;
            $category = $jinput->getInt('category');
            if(!empty($category)){
                $stockModel = Gm_ceilingHelpersGm_ceiling::getModel('stock');
                $result = $stockModel->getGoodsByCategory($category);
            }
            die(json_encode($result));
        }
        catch(Exception $e){
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function updateReceived(){
        try{
            $jinput = JFactory::getApplication()->input;
            $newGoods = $jinput->get('goods_id',null,'STRING');
            $newCost = $jinput->get('cost',null,'STRING');
            $newCount = $jinput->get('count',null,'STRING');
            $newStock = $jinput->get('stock',null,'STRING');
            $id = $jinput->get('id',null,'STRING');
            $inventoryId = $jinput->get('inventory',null,'STRING');
            $stockModel = Gm_ceilingHelpersGm_ceiling::getModel('stock');
            $receptionInfo = $stockModel->getReceivedInfo($id);
            if($receptionInfo->received_count == $receptionInfo->count){
                /*если ничего не списывалось с этой приемки*/
                $stockModel->updateReceived($newGoods,$newCost,$newCount,$newCount,$newStock,$inventoryId,$id);
            }
            else{
                $count = null;
                /*если что-то уже было списано*/
                if($receptionInfo->count > 0 && !empty($newCount)){
                    /*если списан не весь товар и пришло новое количество*/
                    $diff = $receptionInfo->received_count -$newCount;
                    if($diff<0){
                        /*если количество увеличивается, то в inventory к количеству добавляем разницу*/
                        $count = $receptionInfo->count+abs($diff);
                    }
                    if($diff>0){
                        /*если количество уменьшается, то если количество минус разница больше нуля то обновляем*/
                        if($receptionInfo->count-$diff >= 0){
                            $count = $receptionInfo->count-$diff;
                        }
                    }
                }
                else{
                    die(json_encode('Весь товар списан, изменение количества невозможно'));
                }
                $stockModel->updateReceived($newGoods,$newCost,$newCount,$count,$newStock,$inventoryId,$id);
            }
            $result = $stockModel->getReceivedInfo($id);
            die(json_encode($result));
        }
        catch(Exception $e){
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function getRests(){
        try{
            $jinput = JFactory::getApplication()->input;
            $dateTo = $jinput->get('date_to','','STRING');
            $search = $jinput->get('search','','STRING');
            $modelStock = Gm_ceilingHelpersGm_ceiling::getModel('stock');
            $result = $modelStock->getRests($dateTo,$search);
            die(json_encode($result));
        }
        catch(Exception $e){
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
    function getGoods(){
        try{
            $jinput = JFactory::getApplication()->input;
            $id = $jinput->getInt('goods_id');
            $model = Gm_ceilingHelpersGm_ceiling::getModel('stock');
            die(json_encode($model->getGoods($id)));
        }
        catch(Exception $e){
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function createCategory(){
        try{
            $jinput = JFactory::getApplication()->input;
            $categoryName = $jinput->getSting('name');
            $stockModel = Gm_ceilingHelpersGm_ceiling::getModel('stock');
            $categoryId = $stockModel->createCategory($categoryName);
            die(json_encode($categoryId));
        }
        catch(Exception $e){
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
}

