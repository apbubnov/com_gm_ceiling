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

/**
 * Canvases list controller class.
 *
 * @since  1.6
 */
class Gm_ceilingControllerCanvases extends Gm_ceilingController
{
	/**
	 * Proxy for getModel.
	 *
	 * @param   string  $name    The model name. Optional.
	 * @param   string  $prefix  The class prefix. Optional
	 * @param   array   $config  Configuration array for model. Optional
	 *
	 * @return object	The model
	 *
	 * @since	1.6
	 */
	public function &getModel($name = 'Canvases', $prefix = 'Gm_ceilingModel', $config = array())
	{
		try
		{
			$model = parent::getModel($name, $prefix/*, array('ignore_request' => true)*/);

			return $model;
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }
	}

	function save(){
	    try {
            $jinput = JFactory::getApplication()->input;
            $texture = $jinput->get('texture', null, 'INT');
            $manufacturer = $jinput->get('manufacturer',null,'INT');
            $price = $jinput->get('price','','STRING');
            $width = $jinput->get('width','','STRING');
            $color = $jinput->get('color_id',null,'INT');
            $model =  $this->getModel();

            $model->save($texture,$manufacturer,$price,$width,$color);
            die(json_encode(true));
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }
    }

    public function setPrice()
    {
        try
        {
            $model = $this->getModel();

            $user = JFactory::getUser();
            $dealerInfo = $user->getDealerInfo();
            if (empty($dealerInfo))
            {
                $dealer = JFactory::getUser($user->dealer_id);
                $dealerInfo = $dealer->getDealerInfo();
            }

            $app = JFactory::getApplication();
            $id = $app->input->get('id', null, 'int');
            $price = $app->input->get('price', null, 'int');

            $model->setPrice((object) ["price" => $price, "id" => $id]);
            $answer = (object) [];
            $answer->data = (object) ["price" => $price, "client_price" => (100 * $price)/(100 - $dealerInfo->dealer_canvases_margin)];
            $answer->status = "success";
            $answer->message = "Обновление произошло успешно!";

            die(json_encode($answer));
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }
    }

    public function updatePrice() {
        try
        {

            $app = JFactory::getApplication();
            $model = $this->getModel();

            $user = JFactory::getUser();
            $user->groups = $user->get('groups');

            $userDealer = $user;

            if (!(in_array(14, $user->groups) || in_array(15, $user->groups))) {
                $userDealer = JFactory::getUser($user->dealer_id);
                $userDealer->groups = $userDealer->get('groups');
                $userDealer->getDealerInfo();
            }

            $managerGM = in_array(16, $user->groups) || in_array(15, $userDealer->groups);

            $dealer = null;

            if ($managerGM) {
                $dealerId = $app->input->get('dealer', null, 'int');

                if (!empty($dealerId)) {
                    $dealer = JFactory::getUser($dealerId);
                    $dealer->getCanvasesPrice();
                }
            }

            $id = $app->input->get('id', null, 'string');
            $price = $app->input->get('Price', null, 'string');
            $level = $app->input->get('level', null, 'int');

            $answer = (object) [];
            $answer->status = "success";
            $answer->message = "Обновление произошло успешно!";
            $answer->elements = [];

            $get = (object) [];
            switch ($level) {
                case 1:
                    $get->where = [];
                    $get->where[] = "texture.id = '$id'";
                    break;
                case 2:
                    $object = preg_split("/\//", $id);
                    $get->where = [];
                    $get->where[] = "texture.id = '$object[0]'";
                    $get->where[] = "canvas.country = '$object[1]'";
                    $get->where[] = "canvas.name = '$object[2]'";
                    break;
                case 3:
                    $get = $id;
                    break;
                default:
                    $get = null;
                    break;
            }

            if (empty($dealer)) {
                $oldPrice = $model->getPrice($get);
                $newPrice = $oldPrice;
                foreach ($oldPrice as $k => $v)
                {
                    $data = $this->parse_price("*" . $price, (object) [], $v->price);
                    $objectPrice = $data->dealerPrice;

                    $newPrice[$k]->price = $this->dealer_margin($v->price, 0, $objectPrice);
                    $answer->elements[] = (object) [
                        "name" => ".Level3[data-canvas='$v->id'] #GMPrice",
                        "value" => self::margin($newPrice[$k]->price, $userDealer->gm_canvases_margin)];
                    $answer->elements[] = (object) [
                        "name" => ".Level3[data-canvas='$v->id'] #DealerPrice",
                        "value" => self::double_margin($newPrice[$k]->price, $userDealer->gm_canvases_margin, $userDealer->dealer_canvases_margin)];
                    $answer->data[] = $data;
                }
                $model->setPrice($newPrice);
            }
            else {
                $oldPrice = $model->getPrice($get);
                $flag = 0;

                foreach ($oldPrice as $k => $v) {
                    $OldDealerPrice = $dealer->CanvasesPrice[$v->id];
                    $OldDealerPrice = (empty($OldDealerPrice))
                        ?(object)["type"=>0, "price"=>0, "value"=>0]
                        :$OldDealerPrice;
                    $NewDealerData = $this->parse_price($price, $OldDealerPrice, $v->price);
                    $NewDealerPrice = $NewDealerData->dealerPrice;
                    $UpdateDealerPrice = $NewDealerData->updatePrice;
                    //throw new Exception(print_r($NewDealerPrice,true));
                    $DealerPrice = self::dealer_margin($OldDealerPrice, $userDealer->gm_canvases_margin, $NewDealerPrice);
                    $PPrice = $model->MinPriceCanvas($v->id);

                    if (floatval($NewDealerPrice) < floatval($PPrice) && false) $flag++;
                    else { 
                        $dealer->setCanvasesPrice($NewDealerPrice, $v->id);

                        $answer->elements[] = (object) [
                            "name" => ".Level3[data-canvas='$v->id'] #GMPrice",
                            "value" => $v->price];
                        $answer->elements[] = (object) [
                            "name" => ".Level3[data-canvas='$v->id'] #UpdateDealerPrice",
                            "value" => $UpdateDealerPrice];
                        $answer->elements[] = (object) [
                            "name" => ".Level3[data-canvas='$v->id'] #DealerPrice",
                            "value" => $DealerPrice];
                        $answer->data[] = $NewDealerData;
                    } 
                }
                if ($flag == 1) {
                    $answer->status = "error";
                    $answer->message = "Цена для дилера не должна быть ниже себестоймости.";
                }
                else if ($flag > 1) {
                    $answer->status = "error";
                    $answer->message = "Цена для дилера не должна быть ниже себестоймости."
                        . "Поэтому к некоторым полотнам новый прайс был не пременен.";
                }
            }

            die(json_encode($answer));
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }
    }

    public function getCanvasesWidth(){
        try{
            $jinput = JFactory::getApplication()->input;
            $filter = $jinput->get('filter','','STRING');
            $canvasesModel = Gm_ceilingHelpersGm_ceiling::getModel('canvases');
            $widths = $canvasesModel->getCanvasesWidths($filter);
            die(json_encode($widths)); 
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }
    }

    private function margin($value, $margin) { return Gm_ceilingHelpersGm_ceiling::margin($value, $margin); }
    private function double_margin($value, $margin1, $margin2) { return Gm_ceilingHelpersGm_ceiling::double_margin($value, $margin1, $margin2); }
    private function dealer_margin($price, $margin, $objectDealerPrice) { return Gm_ceilingHelpersGm_ceiling::dealer_margin($price, $margin, $objectDealerPrice); }
    private function parse_price($price, $dealerPrice, $PriceDB = null) { return Gm_ceilingHelpersGm_ceiling::parse_price($price, $dealerPrice, $PriceDB); }
}
