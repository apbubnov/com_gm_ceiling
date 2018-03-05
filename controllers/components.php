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
 * Components list controller class.
 *
 * @since  1.6
 */
class Gm_ceilingControllerComponents extends Gm_ceilingController
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
	public function &getModel($name = 'Components', $prefix = 'Gm_ceilingModel', $config = array())
	{
		try
		{
			$model = parent::getModel($name, $prefix, array('ignore_request' => true));

			return $model;
		}
		catch(Exception $e)
        {
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
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
            $answer->data = (object) ["price" => $price, "client_price" => (100 * $price)/(100 - $dealerInfo->dealer_components_margin)];
            $answer->status = "success";
            $answer->message = "Обновление произошло успешно!";

            die(json_encode($answer));
        }
        catch(Exception $e)
        {
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
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
                    $dealer->getComponentsPrice();
                }
            }

            $id = $app->input->get('id', null, 'int');
            $price = $app->input->get('Price', null, 'string');

            $price = str_replace(",", ".", $price);
            $p = str_replace("%", "", $price);
            $e = str_replace(["+", "-"], "", $p);
            $type = (strlen($e) != strlen($p))?((strlen($p) != strlen($price))?3:2):1;
            $number = floatval($p);

            if ($price == "*") $type = 0;

            $answer = (object) [];
            $answer->status = "success";
            $answer->message = "Обновление произошло успешно!";
            $answer->elements = [];

            if (empty($dealer)) {
                $oldPrice = $model->getPrice($id);
                $newPrice = $oldPrice;
                foreach ($oldPrice as $k => $v)
                {
                    $newPrice[$k]->price = $this->dealer_margin($v->price, 0, $number, $type);
                    $answer->elements[] = (object) [
                        "name" => ".Level2[data-option='$v->id'] #GMPrice",
                        "value" => self::margin($newPrice[$k]->price, $userDealer->gm_components_margin)];
                    $answer->elements[] = (object) [
                        "name" => ".Level2[data-option='$v->id'] #DealerPrice",
                        "value" => self::double_margin($newPrice[$k]->price, $userDealer->gm_components_margin, $userDealer->dealer_components_margin)];
                }
                $model->setPrice($newPrice);
            }
            else {
                $oldPrice = $model->getPrice($id);
                $flag = 0;
                foreach ($oldPrice as $k => $v) {
                    $OldDealerPrice = $dealer->ComponentsPrice[$v->id];
                    $OldDealerPrice = (empty($OldDealerPrice))?$v->price:self::dealer_margin($v->price, 0, $OldDealerPrice->value, $OldDealerPrice->type);
                    if ($type == 0) $OldDealerPrice = $v->price;
                    $NewDealerPrice = self::dealer_margin($OldDealerPrice, 0, $number, $type);
                    $DealerPrice = self::dealer_margin($OldDealerPrice, $userDealer->gm_components_margin, $number, $type);
                    $PPrice = $model->MinPriceOption($v->id);
                    $ComponentsPrice = self::margin($v->price, $userDealer->gm_components_margin);
                    $UpdateDelaerPrice = $DealerPrice - $ComponentsPrice;

                    if (floatval($NewDealerPrice) < floatval($PPrice) && false) $flag++;
                    else {
                        $dealer->setComponentsPrice(["value" => $NewDealerPrice, "type" => 1], $v->id);

                        $answer->elements[] = (object) [
                            "name" => ".Level2[data-option='$v->id'] #GMPrice",
                            "value" => $ComponentsPrice];
                        $answer->elements[] = (object) [
                            "name" => ".Level2[data-option='$v->id'] #UpdateDealerPrice",
                            "value" => (($UpdateDelaerPrice >= 0)?"+":"").$UpdateDelaerPrice];
                        $answer->elements[] = (object) [
                            "name" => ".Level2[data-option='$v->id'] #DealerPrice",
                            "value" => $DealerPrice];
                    }
                }
                if ($flag == 1) {
                    $answer->status = "error";
                    $answer->message = "Цена для дилера не должна быть ниже себестоймости.";
                }
                else if ($flag > 1) {
                    $answer->status = "error";
                    $answer->message = "Цена для дилера не должна быть ниже себестоймости."
                        . "Поэтому к некоторым компонентам новый прайс был не пременен.";
                }
            }

            die(json_encode($answer));
        }
        catch(Exception $e)
        {
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
        }
    }

    private function margin($value, $margin) { return ($value * 100 / (100 - $margin)); }
    private function double_margin($value, $margin1, $margin2) { return self::margin(self::margin($value, $margin1), $margin2); }
    private function dealer_margin($price, $margin, $value, $type) {
        $result = 0;
        switch ($type)
        {
            case 0: $result = $price; break;
            case 1: $result = $value; break;
            case 2: $result = $price + $value; break;
            case 3: $result = $price + $price * floatval($value) / 100; break;
        }
        return self::margin($result, $margin);
    }
}
