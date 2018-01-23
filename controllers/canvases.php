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
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
        }
	}
/*
	public function activate()
	{
        // Initialise variables.
        $app = JFactory::getApplication();

        // Checking if the user can remove object
        $user = JFactory::getUser();

        $jinput = JFactory::getApplication()->input;

        $margin = $user->get('dealer_canvases_margin');
        $new_margin = $jinput->get("new_margin",$margin,"INT");

        if ($new_margin != $margin && ( !empty($new_margin) || $new_margin == 0))
        {
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $fields = array(
                $db->quoteName('dealer_canvases_margin'). ' = '.$db->quote($new_margin)
            );
            $conditions = array(
                $db->quoteName('id').' = '.$db->quote($user->get("dealer_id"))
            );
            $query->update($db->quoteName('#__users'))->set($fields)->where($conditions);
            $db->setQuery($query);
            $result = $db->execute();
        }

        $this->setMessage("Данные успешно изменены");

        $this->setRedirect(JRoute::_('index.php?option=com_gm_ceiling&view=canvases', false));
/*
        if ($)

        $model = parent::getModel('Canvases', 'Gm_ceilingModel');

        $jinput = JFactory::getApplication()->input;

		$isDiscountChange = $jinput->get('isDiscountChange', '0', 'INT');


		if($isDiscountChange&&(!empty($new_discount)||$new_discount==0)){
					$db = JFactory::getDbo();
					$query = $db->getQuery(true);
					$fields = array(
						$db->quoteName('project_discount'). ' = '.$db->quote($new_discount)
						);
					$conditions = array(
						$db->quoteName('id').' = '.$db->quote($project_id)
						);
					$query->update($db->quoteName('#__dealer_canvases_margin'))->set($fields)->where($conditions);
					$db->setQuery($query); 
					$result = $db->execute();

			}
				$this->setMessage("Данные успешно изменены");*/
	//}
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
}
