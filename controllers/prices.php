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
class Gm_ceilingControllerPrices extends Gm_ceilingController
{
    /**
     * Proxy for getModel.
     *
     * @param string $name The model name. Optional.
     * @param string $prefix The class prefix. Optional
     * @param array $config Configuration array for model. Optional
     *
     * @return object    The model
     *
     * @since    1.6
     */
    public function &getModel($name = 'Components', $prefix = 'Gm_ceilingModel', $config = array())
    {
        try {
            $model = parent::getModel($name, $prefix, array('ignore_request' => true));

            return $model;
        } catch (Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }
    }

    public function dealerPriceGoods()
    {
        try {
            $jinput = JFactory::getApplication()->input;
            $dealer_id = $jinput->get('dealer_id', null, 'INT');
            $dealer_prices = $jinput->get('dealer_prices', null, 'ARRAY');

            $reset_flag = $jinput->get('reset_flag', 0, 'INT');

            $model_prices = $this->getModel('Prices', 'Gm_ceilingModel');

            $result = $model_prices->saveDealerPriceGoods($dealer_id, $dealer_prices, $reset_flag);

            die(json_encode($result));
        } catch (Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function updateServicePrice()
    {
        try {
            $jinput = JFactory::getApplication()->input;
            $price = json_decode($jinput->getString('price'));
            $mountModel = Gm_ceilingHelpersGm_ceiling::getModel('mount');
            $mountModel->updateServicePrice($price);
            die(json_encode(true));
        } catch (Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function generatePricePDF()
    {
        try {
            $jinput = JFactory::getApplication()->input;
            $pdfType = $jinput->get('pdf_type','','STRING');
            $model_mount = Gm_ceilingHelpersGm_ceiling::getModel('mount');
            $model_prices = Gm_ceilingHelpersGm_ceiling::getModel('prices');

            $price =  ($pdfType == 'service') ? $model_mount->getServicePrice() : $model_prices->getJobsDealer(1);
            $title = ($pdfType == 'service') ? 'Прайс монтажной службы' : 'Прайс монтажа ГМ';

            $html = "
                <h1>$title</h1>
                <table border=\"0\" cellspacing=\"0\" width=\"100%\">
                    <tbody>
                        <tr>
                            <td><b>Наименование</b></td>
                            <td><b>Цена, руб.</b></td>
                        </tr>
            ";
            foreach ($price as $item) {
                $html.="<tr><td>$item->name</td><td>$item->price</td></tr>";
            }
            $html .=" </tbody></table>";
            $sheets_dir = $_SERVER['DOCUMENT_ROOT'] . '/files/';
            $filename = ($pdfType == 'service') ? 'price_mount_service.pdf' : 'mount_price_gm.pdf';
            Gm_ceilingHelpersGm_ceiling::save_pdf($html, $sheets_dir . $filename, "A4");
            die(json_encode('/files/'.$filename));
        } catch (Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }


}