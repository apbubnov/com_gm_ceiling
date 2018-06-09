<?php
$id = $_GET['id'];
$complite = $_GET['complite'];
$server_name = $_SERVER['SERVER_NAME'];
?>
<html>
<head>
    <link rel="stylesheet" href="style.css">
    <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.9.0/jquery.min.js"></script>
    <script type="text/javascript"
            src="https://cdnjs.cloudflare.com/ajax/libs/jquery.maskedinput/1.4.1/jquery.maskedinput.js"></script>
    <script type="text/javascript"
            src="http://<?php echo $server_name;?>/templates/gantry/js/jquery.noty.packaged.min.js"></script>
</head>
<body>
<div class="PRELOADER_GM">
    <div class="PRELOADER_BLOCK"></div>
    <img src="/images/GM_R_HD.png" class="PRELOADER_IMG">
</div>

<div id="container" style="display: none;">
    <div id="header">
        <div id="logo">
            <img class="logo" src="/components/com_gm_ceiling/views/saverclient/gm-logo.png" alt="Логотип">
        </div>
        <div id="contacts">
            <input type="number" name="id" value="<?= $id; ?>" hidden>
            <p>Телефон для информации: <a></a></p>
        </div>
    </div>

    <div id="content">
        <? if (!empty($complite) && $complite == "1"): ?>
            <div id="banner-time-left">
                Спасибо за то, что Вы с нами! Скоро менеджер свяжется с Вами!
            </div>
        <? elseif (!empty($id)): ?>
            <div class="block">
                <div id="banner-time-left">
                    <p>
                        Срок действия данного предложения ограничен, до конца предложения осталось:
                        <span id="time"></span>
                    </p>
                    <p>Заполните все поля для закрепления за Вами дополнительной скидки 10%</p>
                </div>
                <form id="form" action="/index.php?option=com_gm_ceiling&task=addClient" method="post"
                      class="form-validate form-horizontal" enctype="multipart/form-data">
                    <input type="number" name="id" value="<?= $id; ?>" hidden>
                    <div id="container-for-input">
                        <p><input type="text" name="fio" id="FIO" placeholder="ФИО" required></p>
                        <p><input type="text" name="phone" id="phone" placeholder="Телефон" required></p>
                        <p><input type="text" name="adress" id="adress" placeholder="Адрес замера" required></p>
                        <p>Дата замера</p>
                        <input type = "date" name = "project_calc_date" id = "jform_project_new_calc_date" required>
                        <p>Время замера</p>
                        <select id="jform_new_project_calculation_daypart"
                                name="new_project_calculation_daypart" disabled required>
                            <option value="00:00" selected="">- Выберите время замера -</option>
                            <option value="9:00">9:00-10:00</option>
                            <option value="10:00">10:00-11:00</option>
                            <option value="11:00">11:00-12:00</option>
                            <option value="12:00">12:00-13:00</option>
                            <option value="13:00">13:00-14:00</option>
                            <option value="14:00">14:00-15:00</option>
                            <option value="15:00">15:00-16:00</option>
                            <option value="16:00">16:00-17:00</option>
                            <option value="17:00">17:00-18:00</option>
                            <option value="18:00">18:00-19:00</option>
                            <option value="19:00">19:00-20:00</option>
                            <option value="20:00">20:00-21:00</option>
                        </select>

                        <p>
                            <button type="submit" id="save">ОК</button>
                        </p>
                    </div>
                </form>
            </div>

        <? endif; ?>
    </div>
    <footer id="footer">
        <div id="footer-container">
            <div id="footer1">
                <p class="footer-caption">О компании</p>
                <p class="td1"><a href="#">О компании</a></p>
                <p class="td1"><a href="#">Контакты</a></p>
                <p class="td1"><a href="#">Адрес офиса</a></p>
            </div>
            <div id="footer2">
                <p class="footer-caption">Интернет-магазин</p>
                <p class="td1"><a href="#">Как оплатить монтаж</a></p>
                <p class="td1"><a href="#">Условия гарании</a></p>
                <p class="td1"><a href="http://calc.gm-vrn.ru/">Заказ материалов</a></p>
            </div>
            <div id="footer3">
                <p class="footer-caption">Правовая информация</p>
                <p class="td1"><a href="#">Пользовательское соглашение</a></p>
                <p class="td1"><a href="#">Ограничения на использование информации</a></p>
                <p class="td1"><a href="#">Согласие с обработкой персональных данных</a></p>
                <p class="td1"><a href="#">Политика конфиденциальности</a></p>
            </div>
            <div id="copyrights-container">
                <p class="copyrights">©2017 Монтажная служба ГМ. Все права защищены.</p>
            </div>
        </div>
    </footer>
</div>
<script>
    <?if(!empty($complite)):?>
    $(document).ready(function () {
        jQuery("#container").show();
        getClientInfo();
    });

    function getClientInfo() {
        jQuery.ajax({
            url: "/index.php?option=com_gm_ceiling&task=getClientInfoApi",
            data: {
                id: jQuery("input[name='id']").val()
            },
            dataType: "json",
            success: function (data) {
                jQuery('.PRELOADER_GM').remove();
                jQuery("#contacts p a").text('+'+data.number).attr("href","tel:+"+data.number);
            },
            error: function (data) {
                console.log(data);
                var n = noty({
                    theme: 'relax',
                    layout: 'center',
                    maxVisible: 5,
                    type: "error",
                    text: "Ошибка"
                });
            }
        })
    }
    <?elseif (!empty($id)): ?>
    var time = 0;

    $(document).ready(function () {
        $("#phone").mask("+7 (999) 999-99-99");
        getClientInfo();
    });

    function Timer() {
        var old = new Date();
        var s = time - Math.floor(old.getTime() / 1000);
        if (s < 0) {
            jQuery("form").remove();
            jQuery("#banner-time-left").html("Данная акция завершена!");
        } else {
            var seconds = s % 60;
            s = Math.floor(s / 60);
            var minutes = s % 60;
            s = Math.floor(s / 60);
            var hours = s % 24;
            s = Math.floor(s / 24);
            var days = s;
            jQuery("#time").text(days + " д. " + hours + " ч. " + minutes + " м. " + seconds + " с.");
        }
    }

    function getClientInfo() {
        jQuery.ajax({
            url: "/index.php?option=com_gm_ceiling&task=getClientInfoApi",
            data: {
                id: jQuery("input[name='id']").val()
            },
            dataType: "json",
            success: function (data) {
                jQuery("#contacts p a").text('+'+data.number).attr("href","tel:+"+data.number);
                time = data.date + 3 * 24 * 3600;
                Timer();
                setInterval(Timer, 1000);
                jQuery("#container").show();
                jQuery('.PRELOADER_GM').remove();
            },
            error: function (data) {
                console.log(data);
                var n = noty({
                    theme: 'relax',
                    layout: 'center',
                    maxVisible: 5,
                    type: "error",
                    text: "Ошибка"
                });
            }
        })
    }
    jQuery("#jform_project_new_calc_date").on("keyup", function () {
        //update_times("#jform_project_new_calc_date", "#jform_new_project_calculation_daypart");
        jQuery("#jform_new_project_calculation_daypart").prop("disabled", false);

    });
    jQuery("#jform_project_new_calc_date").change(function () {
        jQuery("#jform_new_project_calculation_daypart").prop("disabled", false);
    });

    /*jQuery('#save').click(function(){
        var fio = jQuery("#FIO").val();
        var phone = jQuery("#phone").val();

        jQuery.ajax({
            url: "/index.php?option=com_gm_ceiling&task=addClient",
            data: {
                id: jQuery("input[name='id']").val(),
                fio: fio,
                phone:  phone
            },
            dataType: "json",
            success: function (data) {
                if(data == 1) {
                    jQuery("#banner-time-left").html("Cпасибо!");
                }
            },
            error: function (data) {
                console.log(data);
                var n = noty({
                    theme: 'relax',
                    layout: 'center',
                    maxVisible: 5,
                    type: "error",
                    text: "Ошибка"
                });
            }
        })
    });*/

    <?endif;?>
</script>
</body>
</html>
