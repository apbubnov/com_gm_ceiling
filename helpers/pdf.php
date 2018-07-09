<?php

/**
 * @version    CVS: 0.1.7
 * @package    Com_Gm_ceiling
 * @author     SpectralEye <Xander@spectraleye.ru>
 * @copyright  2016 SpectralEye
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;
//echo Gm_ceilingHelpersPDF::Code(5);

/* включаем библиотеку для формирования PDF */
//include($_SERVER['DOCUMENT_ROOT'] . "/libraries/mpdf/mpdf.php");

/* Данный хелпер используется для создания pdf файлов. Например: Накладная. */
class Gm_ceilingHelpersPDF {

    public static function getModel($name)
    {
        $model = null;

        // If the file exists, let's
        if (file_exists(JPATH_SITE . '/components/com_gm_ceiling/models/' . strtolower($name) . '.php')) {
            require_once JPATH_SITE . '/components/com_gm_ceiling/models/' . strtolower($name) . '.php';
            $model = JModelLegacy::getInstance($name, 'Gm_ceilingModel');
        }

        return $model;
    }

    public static function Code($code, $num = 8)
    {
        $code = floatval($code);
        $result = "";
        for ($i = 0; $i < $num; $i++) {
            $result = ($code % 10) . $result;
            $code /= 10;
        }
        return $result;
    }

    public static function Format($data)
    {
        $PackingList = [];
        $SalesInvoice = [];

        $i = 1;
        $sum = 0;

        foreach ($data as $d) {
            if (!empty($d->rollers))
            {
                $object = (object) [];
                $object->name = $d->name . " " . $d->country . " " . $d->texture . ((empty($d->color))?" ":" ".$d->color." ") . $d->width;
                $object->code = "П" . self::Code($d->id);
                $object->unit = $d->unit;
                $object->unitCode = $d->code;
                $object->price = $d->price;
                $object->number = $i++;
                $count = 0;
                foreach ($d->quad as $r)
                {
                    $clone_object = clone $object;
                    $clone_object->count = floatval($r);
                    $count += $clone_object->count;
                    $clone_object->totalVAL = ceil(floatval($d->price) * $clone_object->count * 100) / 100;
                    $clone_object->VALTotal = $clone_object->totalVAL;
                    $SalesInvoice[] = $clone_object;
                }
                $object->count = $count;
                $object->totalVAL = ceil(floatval($d->price) * (floatval($count)) * 100) / 100;
                $object->VALTotal = $object->totalVAL;
                $sum += floatval($object->VALTotal);
                $PackingList[] = $object;
            } else {
                $object = (object) [];
                $object->number = $i++;
                $object->name = $d->type . " " . $d->name;
                $object->code = "К" . self::Code($d->id);
                $object->unit = $d->unit;
                $object->unitCode = $d->code;
                $object->price = $d->price;
                $object->count = $d->count;
                $object->totalVAL = ceil(floatval($d->price) * (floatval($d->count)) * 100) / 100;
                $object->VALTotal = $object->totalVAL;
                $SalesInvoice[] = $object;
                $PackingList[] = $object;
                $sum += floatval($object->VALTotal);
            }
        }
        return (object) ["PackingList" => $PackingList, "SalesInvoice" => $SalesInvoice, "sum" => $sum];
    }

    private static function MonthStr($key)
    {
        $month = array(1 => 'января', 'февраля', 'марта', 'апреля', 'мая', 'июня', 'июля', 'августа', 'сентября', 'октября', 'ноября', 'декабря');
        return $month[intval($key)];
    }

    private static function NumToStr($in, $type = 0)
    {
        $number = array(
            array('','один','два','три','четыре','пять','шесть','семь', 'восемь','девять'),
            array('','одна','две','три','четыре','пять','шесть','семь', 'восемь','девять'),
            array('десять','одиннадцать','двенадцать','тринадцать','четырнадцать' ,'пятнадцать','шестнадцать','семнадцать','восемнадцать','девятнадцать'),
            array(2 => 'двадцать','тридцать','сорок','пятьдесят','шестьдесят','семьдесят' ,'восемьдесят','девяносто'),
            array('','сто','двести','триста','четыреста','пятьсот','шестьсот', 'семьсот','восемьсот','девятьсот')
        );
        $unit = array(
            array('копейка' ,'копейки' ,'копеек',	 1),
            array('рубль'   ,'рубля'   ,'рублей'    ,0),
            array('тысяча'  ,'тысячи'  ,'тысяч'     ,1),
            array('миллион' ,'миллиона','миллионов' ,0),
            array('миллиард','милиарда','миллиардов',0)
        );
        $other = array('руб.', 'коп.');

        $out = array();

        list($left,$right) = explode('.',sprintf("%015.2f", floatval($in)));
        if ($type == 1)
        {
            $out[] = intval($left);
            $out[] = $other[0];
        }
        else if (intval($left) > 0)
        {
            foreach (str_split($left, 3) as $k => $v)
            {
                if (!intval($v)) continue;
                $index = 4 - $k;

                if (intval($v[1]) == 1) $iunit = $unit[$index][2];
                else if (intval($v[2]) == 1) $iunit = $unit[$index][0];
                else if (intval($v[2]) < 5 && intval($v[2]) > 1) $iunit = $unit[$index][1];
                else $iunit = $unit[$index][2];
                $isex = $unit[$index][3];

                if (intval($v[0])) $out[] = $number[4][intval($v[0])];
                if (intval($v[1]) == 1) $out[] = $number[2][intval($v[2])];
                else {
                    if (intval($v[1])) $out[] = $number[3][intval($v[1])];
                    if (intval($v[2])) $out[] = $number[$isex][intval($v[2])];
                }

                if ($index != 1 || $type == 2) $out[] = $iunit;
            }
        }
        else
        {
            $out[] = "ноль";
            if ($type == 2) $out[] = $unit[1][2];
            else if ($type == 1) $out[] = $other[0];
        }

        if ($type == 2)
        {
            $out[] = $right;
            if ($right[0] == 1) $out[] = $unit[0][2];
            else if ($right[1] == 1) $out[] = $unit[0][0];
            else if ($right[1] < 5 && $right[1] > 1) $out[] = $unit[0][1];
            else $out[] = $unit[0][2];
        }
        else if ($type == 1)
        {
            $out[] = $right;
            $out[] = $other[1];
        }

        return self::UpperFirst(implode(" ", $out));
    }

    public static function UpperFirst($string)
    {
        $up = mb_strtoupper($string, "UTF-8");
        $string = substr($up, 0, 2) . substr($string, 2, strlen($string) - 2);
        return $string;
    }

    public static function getRequisites($stock, $counterparty)
    {
        $model = self::getModel("Stock");
        $stock = $model->getInformationStock($stock);
        $counterparty = $model->getCounterparty($counterparty);
        return (object) ["stock" => $stock, "counterparty" => $counterparty];
    }

    public static function MergeFiles($files)
    {
        $mpdf = new mPDF();
        $mpdf->SetImportUse();

        foreach ($files as $file) {
            while (get_headers($file)[0] == "404 Not Found");
            $file = str_replace("http://".$_SERVER['SERVER_NAME'],$_SERVER['DOCUMENT_ROOT'], $file);

            $pagecount = $mpdf->SetSourceFile($file);
            for($i = 1; $i <= $pagecount; $i++)
            {
                if (stristr($file, 'PackingList')) $mpdf->AddPage("L");
                else $mpdf->AddPage("P");

                $tplId = $mpdf->ImportPage($i);
                $mpdf->UseTemplate($tplId);
            }
        }

        $dir = "/components/com_gm_ceiling/views/pdf/files/";
        $super_dir = $_SERVER['DOCUMENT_ROOT'] . $dir;
        $filename = __FUNCTION__ . "/" . ((string) date("His")) . ".pdf";

        $mpdf->Output( $super_dir . $filename, 'F');

        return "http://".$_SERVER['SERVER_NAME'].$dir.$filename;
    }

    public static function PackingList($info, $data) {
        try {
        $model = self::getModel('Stock', 'Gm_ceilingModel');
        $number = $model->newDocument($info->stock, __FUNCTION__, $info->date);

        $requisites = self::getRequisites($info->stock, $info->customer->dealer->counterparty);

        $dir = "/components/com_gm_ceiling/views/pdf/files/";
        $super_dir = $_SERVER['DOCUMENT_ROOT'] . $dir;
        $filename = __FUNCTION__ . "/" . self::Code($number, $num = 11) . ".pdf";

        $header = file_get_contents("http://".$_SERVER['SERVER_NAME']."/components/com_gm_ceiling/views/pdf/templates/".__FUNCTION__."/header.html");
        $body = file_get_contents("http://".$_SERVER['SERVER_NAME']."/components/com_gm_ceiling/views/pdf/templates/".__FUNCTION__."/body.html");
        $table = file_get_contents("http://".$_SERVER['SERVER_NAME']."/components/com_gm_ceiling/views/pdf/templates/".__FUNCTION__."/table.html");
        $footer_table = file_get_contents("http://".$_SERVER['SERVER_NAME']."/components/com_gm_ceiling/views/pdf/templates/".__FUNCTION__."/footer_table.html");
        $footer = file_get_contents("http://".$_SERVER['SERVER_NAME']."/components/com_gm_ceiling/views/pdf/templates/".__FUNCTION__."/footer.html");
        $style = file_get_contents("http://".$_SERVER['SERVER_NAME']."/components/com_gm_ceiling/views/pdf/templates/".__FUNCTION__."/style.css");

        $pages = [];
        $number_count = count($data);
        $count = 0;
        $index = 0;
        $left = true;
        $right = true;
        foreach ($data as $i => $g)
        {
            $it = $i + 1;
            if ($it <= 26) { $index = 1; $left = ($it <= 7)?true:false; $right = ($it <= 24)?true:false;}
            else { $index = ceil(($it - 26) / 42) + 1; $left = (($it - 26) <= (($index - 1) * 42 - 19)); $right = (($it - 26) <= (($index - 1) * 42 - 1)); }

            $temp = $table;
            $temp = str_replace('@НомерТовара@', $it, $temp);
            $temp = str_replace('@ИмяТовара@', $g->name, $temp);
            $temp = str_replace('@КодТовара@', $g->code, $temp);
            $temp = str_replace('@ЕдиницаИзмерения@', $g->unit, $temp);
            $temp = str_replace('@КодОКЕИ@', $g->unitCode, $temp);
            $temp = str_replace('@ВидУпаковки@', " ", $temp);
            $temp = str_replace('@КоличествоМасса@', number_format($g->count, 3, ',', ''), $temp);
            $temp = str_replace('@Цена@', number_format($g->price, 2, ',', ' '), $temp);
            $temp = str_replace('@Сумма@', number_format($g->totalVAL, 2, ',', ' '), $temp);
            $temp = str_replace('@Ставка@', "Без НДС", $temp);
            $temp = str_replace('@НДССумма@', "", $temp);
            $temp = str_replace('@СуммаНДС@', number_format($g->VALTotal, 2, ',', ' '), $temp);

            $pages[$index] .= $temp;
            $count += floatval($g->count);
        }
        if (!$left) $pages[] = "";

        $mpdf = new mPDF('utf-8', "A4", '8', '', 5, 3, 3, 3, 0, 0,"");
        $mpdf->SetDisplayMode('fullpage');
        $mpdf->list_indent_first_level = 0;

        foreach ($pages as $i => $t)
        {
            $mpdf->AddPage("L");
            $number_page = "<div class=\"t2\">Страница " . $i . "</div>";

            $html = "";
            if ($i == 1)  $html .= $header . $number_page . $body;
            else if ($i <= $index) $html .= $number_page . $body;
            else $html .= $number_page;

            $html = str_replace('@Товары@', $t, $html);

            if ($i > $index && !$right) $html .= $body;
            if (($i > $index && !$right) || ($i == $index && $right)) $html = str_replace('@ИтогПоТоварам@', $footer_table, $html);
            if (($i > $index && !$left) || ($i == $index && $left)) $html .= $footer;

            $html = str_replace('@НомерДокумента@', $number, $html);
            $html = str_replace('@ОрганизацияОтправитель@', $requisites->stock->name.", ".$requisites->stock->requisites, $html);
            $html = str_replace('@Поставщик@', $requisites->stock->name.", ".$requisites->stock->requisites, $html);
            $html = str_replace('@Подразделение@', 'Основное', $html);
            $html = str_replace('@Основание@', "Основной договор", $html);
            $html = str_replace('@ОКУД@', " ".$requisites->stock->OKUD." ", $html);
            $html = str_replace('@ОКПО@', " ".$requisites->stock->OKPO." ", $html);
            $html = str_replace('@ОКПО1@', " ".$requisites->stock->OKPO." ", $html);
            $html = str_replace('@ОКДП@', " ".$requisites->stock->OKDP." ", $html);
            $html = str_replace('@СуммаПрописью@', self::NumToStr($info->sum, 2), $html);
            $html = str_replace('@ОрганизацияПолучатель@', $requisites->counterparty->full_name, $html);
            $html = str_replace('@Плательщик@', $requisites->counterparty->full_name, $html);
            $html = str_replace('@Дата@', $info->dateFormat->date, $html);
            $html = str_replace('@ДатаЧисло@', $info->dateFormat->day, $html);
            $html = str_replace('@ДатаМесяц@', self::MonthStr($info->dateFormat->month), $html);
            $html = str_replace('@ДатаГод@', $info->dateFormat->year, $html);
            $html = str_replace('@ИтогоКоличество@', number_format($count, 3, ',', ''), $html);
            $html = str_replace('@ИтогоСумма@', number_format($info->sum, 2, ',', ' '), $html);
            $html = str_replace('@ИтогоСуммаНДС@', number_format($info->sum, 2, ',', ' '), $html);
            $html = str_replace('@КоличествоСтрокПрописью@', self::NumToStr($number_count, 0), $html);
            $html = preg_replace("/(@[\S]{0,50}@)/"," ",$html);

            $mpdf->WriteHTML($style,1);
            $mpdf->WriteHTML($html,2);
        }

        $mpdf->Output( $super_dir . $filename, 'F');

        return "http://".$_SERVER['SERVER_NAME'].$dir.$filename;
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public static function RetailCashOrder($info) {
        try {
            $model = self::getModel('Stock', 'Gm_ceilingModel');
            $number = $model->newDocument($info->stock, __FUNCTION__, $info->date);

            $requisites = self::getRequisites($info->stock, $info->customer->dealer->counterparty);

            $dir = "/components/com_gm_ceiling/views/pdf/files/";
            $super_dir = $_SERVER['DOCUMENT_ROOT'] . $dir;
            $filename = __FUNCTION__ . "/" . self::Code($number, $num = 11) . ".pdf";

            $html = file_get_contents("http://".$_SERVER['SERVER_NAME']."/components/com_gm_ceiling/views/pdf/templates/".__FUNCTION__."/body.html");
            $style = file_get_contents("http://".$_SERVER['SERVER_NAME']."/components/com_gm_ceiling/views/pdf/templates/".__FUNCTION__."/style.css");

            $html = str_replace('@НомерДокумента@', $number, $html);
            $html = str_replace('@СуммаПрописью@', self::NumToStr($info->sum, 2), $html);
            $html = str_replace('@Организация@', $requisites->stock->name, $html);
            $html = str_replace('@Подразделение@', "Основное", $html);
            $html = str_replace('@ОКУД@', " ".$requisites->stock->OKUD." ", $html);
            $html = str_replace('@ОКПО@', " ".$requisites->stock->OKPO." ", $html);
            $html = str_replace('@Дата@', $info->dateFormat->date, $html);
            $html = str_replace('@Дата1@', $info->dateFormat->day." ".self::MonthStr($info->dateFormat->month)." ".$info->dateFormat->year." г.", $html);
            $html = str_replace('@ПринятоОт@', $requisites->counterparty->full_name, $html);
            $html = str_replace('@Основание@', "Основное", $html);
            $html = str_replace('@СуммаЦифрами@', self::NumToStr($info->sum, 1), $html);
            $html = str_replace('@Сумма@', number_format($info->sum, 2, ',', ' '), $html);
            $html = str_replace('@НДС@', "0-00 руб.", $html);
            $html = str_replace('@Дебет@', "50.01", $html);
            $html = str_replace('@СубСчет@', "62.01, 62.02", $html);
            $html = preg_replace("/(@[\S]{0,50}@)/"," ",$html);

            $mpdf = new mPDF('utf-8', "A4", '8', '', 5, 3, 3, 3, 0, 0,"");
            $mpdf->SetDisplayMode('fullpage');
            $mpdf->list_indent_first_level = 0;
            $mpdf->AddPage("P");
            $mpdf->WriteHTML($style,1);
            $mpdf->WriteHTML($html,2);
            $mpdf->Output( $super_dir . $filename, 'F');

            return "http://".$_SERVER['SERVER_NAME'].$dir.$filename;
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public static function SalesInvoice($info, $data) {
        try {
            $model = self::getModel('Stock', 'Gm_ceilingModel');
            $number = $model->newDocument($info->stock, __FUNCTION__, $info->date);

            $requisites = self::getRequisites($info->stock, $info->customer->dealer->counterparty);

            $dir = "/components/com_gm_ceiling/views/pdf/files/";
            $super_dir = $_SERVER['DOCUMENT_ROOT'] . $dir;
            $filename = __FUNCTION__ . "/" . self::Code($number, $num = 11) . ".pdf";

            $html = file_get_contents("http://" . $_SERVER['SERVER_NAME'] . "/components/com_gm_ceiling/views/pdf/templates/" . __FUNCTION__ . "/body.html");
            $table = file_get_contents("http://" . $_SERVER['SERVER_NAME'] . "/components/com_gm_ceiling/views/pdf/templates/" . __FUNCTION__ . "/table.html");
            $style = file_get_contents("http://" . $_SERVER['SERVER_NAME'] . "/components/com_gm_ceiling/views/pdf/templates/" . __FUNCTION__ . "/style.css");

            $tables = "";
            foreach ($data as $i => $g)
            {
                $temp = $table;
                $temp = str_replace('@НомерТовара@', $i + 1, $temp);
                $temp = str_replace('@Наименование@', $g->name, $temp);
                $temp = str_replace('@Количество@', number_format($g->count, 3, ',', ''), $temp);
                $temp = str_replace('@ЕдиницаИзмерения@', $g->unit, $temp);
                $temp = str_replace('@Цена@', number_format($g->price, 2, ',', ' '), $temp);
                $temp = str_replace('@Сумма@', number_format($g->VALTotal, 2, ',', ' '), $temp);
                $tables .= $temp;
            }

            $html = str_replace('@Номер@', $number, $html);
            $html = str_replace('@Товары@', $tables, $html);
            $html = str_replace('@СуммаПрописью@', self::NumToStr($info->sum, 2), $html);
            $html = str_replace('@Дата@', $info->dateFormat->day." ".self::MonthStr($info->dateFormat->month)." ".$info->dateFormat->year, $html);
            $html = str_replace('@Организация@', $requisites->stock->name, $html);
            $html = str_replace('@Покупатель@', $requisites->counterparty->full_name, $html);
            $html = str_replace('@Количество@', count($data), $html);
            $html = str_replace('@ИтогоСумма@', number_format($info->sum, 2, ',', ' '), $html);
            $html = str_replace('@СуммаЦифрами@', number_format($info->sum, 2, ',', ' ')." руб.", $html);
            $html = preg_replace("/(@[\S]{0,50}@)/"," ",$html);

            $mpdf = new mPDF('utf-8', "A4", '8', '', 5, 3, 3, 3, 0, 0,"");
            $mpdf->SetDisplayMode('fullpage');
            $mpdf->list_indent_first_level = 0;
            $mpdf->AddPage("P");
            $mpdf->WriteHTML($style,1);
            $mpdf->WriteHTML($html,2);
            $mpdf->Output( $super_dir . $filename, 'F');

            return "http://".$_SERVER['SERVER_NAME'].$dir.$filename;

        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public static function Posting($data)
    {
        $head = $data->head;
        $goods = $data->goods;

        $dir = '/files/stock/';
        $super_dir = $_SERVER['DOCUMENT_ROOT'] . $dir;
        $filename = $head->name . ".pdf";

        $css = file_get_contents("http://".$_SERVER['SERVER_NAME']."/components/com_gm_ceiling/views/pdf/style.css");
        $html = file_get_contents("http://".$_SERVER['SERVER_NAME']."/components/com_gm_ceiling/views/pdf/body.html");

        $html = str_replace("#Номер#", $head->number, $html);
        $html = str_replace("#Дата#", $head->day." ".self::MonthStr($head->month)." ".$head->year." г.", $html);
        $html = str_replace("#Отправитель#", $head->out, $html);
        $html = str_replace("#Получатель#", $head->in, $html);

        $table =
            "
            <tr class=\"tbody\">
                <td class=\"center\">#Индекс#</td>
                <td class=\"left\">#Название#</td>
                <td class=\"right\">#Количество#</td>
                <td class=\"left\">#Размерность#</td>
                <td class=\"right\">#Цена#</td>
                <td class=\"right\">#Сумма#</td>
            </tr>
            ";
        $tables = "";

        foreach ($goods as $good) {
            $tables .= $table;

            foreach ($good as $i => $g)
                $tables = str_replace("#$i#", $g, $tables);
        }

        $html = str_replace("#Таблица#", $tables, $html);


        $mpdf = new mPDF('utf-8', "A4", '8', '', 5, 3, 3, 3, 0, 0,"");
        $mpdf->SetDisplayMode('fullpage');
        $mpdf->list_indent_first_level = 0;
        $mpdf->AddPage("P");
        $mpdf->WriteHTML($css,1);
        $mpdf->WriteHTML($html,2);

        $mpdf->Output( $super_dir . $filename, 'F');

        return (object) array("href" => "http://".$_SERVER['SERVER_NAME'].$dir.$filename, "name" => $super_dir . $filename);
    }

    public static function InventoryOfGoods($info, $data)
    {
        try {
            $model = self::getModel('Stock', 'Gm_ceilingModel');
            $number = $model->newDocument($info->stock, __FUNCTION__, $info->date);

            $requisites = self::getRequisites($info->stock, $info->counterparty);

            $dir = "/components/com_gm_ceiling/views/pdf/files/";
            $super_dir = $_SERVER['DOCUMENT_ROOT'] . $dir;
            $filename = __FUNCTION__ . "/" . self::Code($number, $num = 11) . ".pdf";

            $html = file_get_contents("http://".$_SERVER['SERVER_NAME']."/components/com_gm_ceiling/views/pdf/templates/".__FUNCTION__."/body.html");
            $table = file_get_contents("http://".$_SERVER['SERVER_NAME']."/components/com_gm_ceiling/views/pdf/templates/".__FUNCTION__."/table.html");
            $style = file_get_contents("http://".$_SERVER['SERVER_NAME']."/components/com_gm_ceiling/views/pdf/templates/".__FUNCTION__."/style.css");

            $tables = "";
            foreach ($data as $i => $g)
            {
                $temp = $table;
                $temp = str_replace('@НомерТовара@', $i + 1, $temp);
                $temp = str_replace('@Наименование@', $g->name, $temp);
                $temp = str_replace('@Количество@', number_format($g->count, 3, ',', ''), $temp);
                $temp = str_replace('@ЕдиницаИзмерения@', $g->unit, $temp);
                $temp = str_replace('@Цена@', number_format($g->price, 2, ',', ' '), $temp);
                $temp = str_replace('@Сумма@', number_format($g->VALTotal, 2, ',', ' '), $temp);
                $tables .= $temp;
            }

            $html = str_replace('@Номер@', $number, $html);
            $html = str_replace('@Товары@', $tables, $html);
            $html = str_replace('@СуммаПрописью@', self::NumToStr($info->sum, 2), $html);
            $html = str_replace('@Дата@', $info->dateFormat->day." ".self::MonthStr($info->dateFormat->month)." ".$info->dateFormat->year, $html);
            $html = str_replace('@Организация@', $requisites->stock->name, $html);
            $html = str_replace('@Покупатель@', $requisites->counterparty->full_name, $html);
            $html = str_replace('@Количество@', count($data), $html);
            $html = str_replace('@ИтогоСумма@', number_format($info->sum, 2, ',', ' '), $html);
            $html = str_replace('@СуммаЦифрами@', number_format($info->sum, 2, ',', ' ')." руб.", $html);
            $html = preg_replace("/(@[\S]{0,50}@)/"," ",$html);

            $mpdf = new mPDF('utf-8', "A4", '8', '', 5, 3, 3, 3, 0, 0,"");
            $mpdf->SetDisplayMode('fullpage');
            $mpdf->list_indent_first_level = 0;
            $mpdf->AddPage("P");
            $mpdf->WriteHTML($style,1);
            $mpdf->WriteHTML($html,2);
            $mpdf->Output( $super_dir . $filename, 'F');

            return "http://".$_SERVER['SERVER_NAME'].$dir.$filename;
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }



            /*----------------------------------------------------------------------------------------------------------------*/



    public function Moving($data)
    {
        $head = $data->head;
        $goods = $data->goods;

        $dir = '/files/stock/';
        $super_dir = $_SERVER['DOCUMENT_ROOT'] . $dir;
        $filename = $head->name . ".pdf";

        $css = file_get_contents("http://".$_SERVER['SERVER_NAME']."/components/com_gm_ceiling/views/pdf/moving.css");
        $html = file_get_contents("http://".$_SERVER['SERVER_NAME']."/components/com_gm_ceiling/views/pdf/moving.html");

        $html = str_replace("#Номер#", $head->number, $html);
        $html = str_replace("#Дата#", $head->day." ".self::MonthStr($head->month)." ".$head->year." г.", $html);
        $html = str_replace("#Отправитель#", $head->out, $html);
        $html = str_replace("#Получатель#", $head->in, $html);

        $table =
            "
            <tr class=\"tbody\">
                <td class=\"center\">#Индекс#</td>
                <td class=\"left\">#Название#</td>
                <td class=\"center\"> </td>
                <td class=\"center\"> </td>
                <td class=\"right\">#Количество#</td>
                <td class=\"left\">#Размерность#</td>
            </tr>
            ";
        $tables = "";

        foreach ($goods as $good) {
            $tables .= $tables;
            $tables = str_replace("#Индекс#", $good->index, $tables);
            $tables = str_replace("#Название#", $good->name, $tables);
            $tables = str_replace("#Количество#", number_format($good->count, 2, ',', ' '), $tables);
            $tables = str_replace("#Размерность#", $good->unit, $tables);
        }

        $html = str_replace("#Таблица#", $tables, $html);


        $mpdf = new mPDF('utf-8', "A4", '8', '', 5, 3, 3, 3, 0, 0,"");
        $mpdf->SetDisplayMode('fullpage');
        $mpdf->list_indent_first_level = 0;
        $mpdf->AddPage("P");
        $mpdf->WriteHTML($css,1);
        $mpdf->WriteHTML($html,2);

        $mpdf->Output( $super_dir . $filename, 'F');

        return (object) array("href" => "http://".$_SERVER['SERVER_NAME'].$dir.$filename, "name" => $super_dir . $filename);
    }
}