<?php
defined('_JEXEC') or die;


class Gm_ceilingControllerCashbox extends Gm_ceilingController
{
    function getData(){
        try{
            $jinput = JFactory::getApplication()->input;
            $dateFrom = $jinput->get('date_from',date('Y-m-d'),'STRING');
            $dateTo = $jinput->get('date_to',date('y-m-d'),'STRING');
            $counterparty = $jinput->getInt('counterparty');
            $userId = $jinput->getInt('user_id');
            $cashboxType = $jinput->getString('cashbox_type');
            $cashboxModel = Gm_ceilingHelpersGm_ceiling::getModel('cashbox');
            $data = $cashboxModel->getFilteredData($dateFrom,$dateTo,$cashboxType,$counterparty,$userId);
            die(json_encode($data));
        }
        catch(Exception $e){
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function save(){
        try{
            $jinput = JFactory::getApplication()->input;
            $data = $jinput->get('data_to_save',[],'ARRAY');
            $modelCashbox = Gm_ceilingHelpersGm_ceiling::getModel('cashbox');
            $result = $modelCashbox->save($data);
            if($data['operation_type'] == 3){
                $dataBuh = $data;
                $dataBuh['operation_type'] = 1;
                $dataBuh['cashbox_type'] = 3;
                $modelCashbox->save($dataBuh);
            }
            if(!empty($data['dealer_id'])){
               /*сохраняем платеж за дилером*/
                $stateModel = Gm_ceilingHelpersGm_ceiling::getModel('client_state_of_account');
                $dealer = JFactory::getUser($data['dealer_id']);
                $stateModel->save($dealer->associated_client,1,$data['sum'],"Оплата ".date('d.m.Y'),null);
            }
            die(json_encode($result));
        }
        catch(Exception $e){
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function getCashboxSum(){
        try{
            $jinput = JFactory::getApplication()->input;
            $cashboxType = $jinput->getInt('cashbox_type');
            $modelCashbox = Gm_ceilingHelpersGm_ceiling::getModel('cashbox');
            $result = $modelCashbox->getCashboxSum($cashboxType);
            die(json_encode($result));
        }
        catch(Exception $e){
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
    function createCashOrder(){
        try{
            $jinput=JFactory::getApplication()->input;
            $id = $jinput->getInt('id');
            $modealCashbox = Gm_ceilingHelpersGm_ceiling::getModel('cashbox');
            $data = $modealCashbox->getDatabyId($id);

            $dateFormat = (object)[];
            $dateFormat->date = date("d.m.Y",strtotime($data->datetime));
            $dateFormat->day = date("d",strtotime($data->datetime));
            $dateFormat->month = date("m",strtotime($data->datetime));
            $dateFormat->year = date("Y",strtotime($data->datetime));
            $data->dateFormat = $dateFormat;

            $number = $id;
            $dir = "/tmp/";
            $super_dir = $_SERVER['DOCUMENT_ROOT'] . $dir;
            $filename =  "/" . Gm_ceilingHelpersPDF::Code($number, $num = 11) . ".pdf";

            if($data->operation_type == 1) {

                $html = file_get_contents("http://" . $_SERVER['SERVER_NAME'] . "/components/com_gm_ceiling/views/pdf/templates/RetailCashOrder/body.html");
                $style = file_get_contents("http://" . $_SERVER['SERVER_NAME'] . "/components/com_gm_ceiling/views/pdf/templates/RetailCashOrder/style.css");

                $html = str_replace('@ОКУД@', " 0310001 ", $html);
                $html = str_replace('@Дебет@', "50.01", $html);
                $html = str_replace('@СубСчет@', "62.01, 62.02", $html);

            }
            if($data->operation_type == 2) {
                $html = file_get_contents("http://" . $_SERVER['SERVER_NAME'] . "/components/com_gm_ceiling/views/pdf/templates/ExpenseCashOrder/body.html");
                $style = file_get_contents("http://" . $_SERVER['SERVER_NAME'] . "/components/com_gm_ceiling/views/pdf/templates/ExpenseCashOrder/style.css");

                $html = str_replace('@ОКУД@', " 0310002 ", $html);
                $html = str_replace('@Кредит@', "50.01", $html);
                $html = str_replace('@СубСчет@', "71.01", $html);
                $html = str_replace('@Выдать@', $data->comment, $html);


            }
            $html = str_replace('@НомерДокумента@', $number, $html);
            $html = str_replace('@СуммаПрописью@', Gm_ceilingHelpersPDF::NumToStr($data->sum, 2), $html);
            $html = str_replace('@Организация@', 'Индивидуальный предприниматель Руденко Игорь Александрович', $html);
            $html = str_replace('@Подразделение@', "Основное", $html);
            $html = str_replace('@ОКПО@', " 29304028 ", $html);
            $html = str_replace('@Дата@', $data->dateFormat->date, $html);
            $html = str_replace('@Дата1@', $data->dateFormat->day." ".Gm_ceilingHelpersPDF::MonthStr($data->dateFormat->month)." ".$data->dateFormat->year." г.", $html);
            $html = str_replace('@ПринятоОт@', $data->dealer_name, $html);
            $html = str_replace('@Основание@', "Основное", $html);
            $html = str_replace('@СуммаЦифрами@', Gm_ceilingHelpersPDF::NumToStr($data->sum, 1), $html);
            $html = str_replace('@Сумма@', number_format($data->sum, 2, ',', ' '), $html);
            $html = str_replace('@НДС@', "0-00 руб.", $html);
            $html = preg_replace("/(@[\S]{0,50}@)/"," ",$html);
            $mpdf = new mPDF('utf-8', "A4", '8', '', 5, 3, 3, 3, 0, 0,"");
            $mpdf->SetDisplayMode('fullpage');
            $mpdf->list_indent_first_level = 0;
            $mpdf->AddPage("P");
            $mpdf->WriteHTML($style,1);
            $mpdf->WriteHTML($html,2);
            $mpdf->Output( $super_dir . $filename, 'F');

            die ("http://".$_SERVER['SERVER_NAME'].$dir.$filename);
        }
        catch(Exception $e){
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
}
?>