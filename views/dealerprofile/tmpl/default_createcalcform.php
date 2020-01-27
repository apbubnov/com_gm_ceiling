<?php
/**
 * Created by PhpStorm.
 * User: bubnov
 * Date: 11.11.2019
 * Time: 16:09
 */
$user = JFactory::getUser();
$calcFormModel = Gm_ceilingHelpersGm_ceiling::getModel('calculationform');
$data = $calcFormModel->getNainGroupsData();
//throw new Exception(print_r(json_decode($data[0]->data),true));
?>
<style>
    .movable{
        border: 2px solid red;
        margin-left: 15px;
    }
</style>
<div class="container">
    <div class="row">
    <h6>
        Создать основные пункты.<br>
        Например: основные работы, освещение и т.д.
        <div class="row">
            <div class="col-md-3">
                <input class="form-control" id="maingroup_name">
            </div>
            <div class="col-md-2">
                <button class="btn btn-primary">Создать</button>
            </div>
        </div>
        <b>Созданные категории</b>
        <div class="row" id="created_categories">

        </div>
    </h6>

    </div>
    <div class="row" id="edit_page">

    </div>
</div>

<script type="text/javascript">
    jQuery(document).ready(function () {
        let movableDiv,
            data = JSON.parse('<?php echo quotemeta(json_encode($data)); ?>');

       jQuery.each(data,function(index,elem){
          elem.fields = JSON.parse(elem.data);
       });
        createBlocks(data);

        jQuery("body").on('mousedown','.add_fields',function(event){
            movableDiv = jQuery(this).closest('.row');
            movableDiv.css('zIndex', 1000);
            movableDiv.addClass('movable');
            document.addEventListener('mousemove', function(event){
                moveAt(event.pageX, event.pageY,movableDiv);
            });
        });

        jQuery("body").on('touchstart','.add_fields',function(event){
            movableDiv = jQuery(this).closest('.row');
            movableDiv.css('zIndex', 1000);
            movableDiv.addClass('movable');
            document.addEventListener('touchmove', function(event){
                event.preventDefault();
                if ((event.clientX)&&(event.clientY)) {
                    moveAt(event.clientX, event.clientY,movableDiv);
                } else if (event.targetTouches) {
                    moveAt( event.targetTouches[0].clientX,event.targetTouches[0].clientY,movableDiv);
                }
            });
        });

        function onMouseMove(event) {
            moveAt(event.pageX, event.pageY,movableDiv);
        }
        jQuery('body').on('mouseup',function() {
            movableDiv.css('left','0px');
            document.removeEventListener('mousemove', onMouseMove);
            movableDiv.onmouseup = null;
            movableDiv.removeClass('movable');
        });
        jQuery('body').on('touchend',function() {
            movableDiv.css('left','0px');
            document.removeEventListener('mousemove', onMouseMove);
            movableDiv.onmouseup = null;
            movableDiv.removeClass('movable');
        });
    });
    function moveAt(pageX, pageY,movableDiv) {
        console.log(pageX,pageY);
        var elem = document.elementFromPoint(pageX,pageY),
            heightHalf = jQuery(elem).outerHeight()/2,
            closestDiv = jQuery(elem).closest('.group'),
            divY = !empty(closestDiv[0]) ? closestDiv[0].getBoundingClientRect().y : 0;
        if(pageY < divY+heightHalf){
            closestDiv.before(movableDiv);
        }
        if(pageY > divY+heightHalf){
            closestDiv.after(movableDiv);
        }
    }
    function createBlocks(data) {
        var div, containerDiv = jQuery("#edit_page");
        jQuery.each(data, function (index, elem) {
            var buttonTitle = '<div class="col-xs-11"><b>' + elem.title + '</b></div><div class="col-xs-1"><i class="fa fa-angle-down" style="color: #414099;"></i></div>';
            var btnDiv = jQuery(document.createElement('div')),
                button = jQuery(document.createElement('button'));
            div = jQuery(document.createElement('div'));
            div.addClass('row');
            div.append('<div class="col-sm-3"></div>');

            button.addClass('btn btn_calc');
            button.prop('type', 'button');
            button.html(buttonTitle);
            button.attr("data-maingroup_id", elem.id);
            btnDiv.addClass('col-sm-6');
            btnDiv.append(button);
            btnDiv.append(createWorkButton(elem.fields));
            div.append(btnDiv);
            div.append('<div class="col-sm-3"></div>');
            containerDiv.before(div);
        });
    }

    function createWorkButton(buttonsArray) {
        var resultDiv = jQuery(document.createElement('div'));
        resultDiv.addClass('inner_container');
        jQuery.each(buttonsArray, function (index, elem) {
            var rowDiv = jQuery(document.createElement('div')),
                button = jQuery(document.createElement('button')),
                buttonDelete = jQuery(document.createElement('div')),
                buttonDivCol = jQuery(document.createElement('div')),
                helpDivCol = jQuery(document.createElement('div')),
                fieldsDiv = jQuery(document.createElement('div'));
            buttonDivCol.addClass('col-sm-11 col-xs-11');
            buttonDivCol.css({"padding-right": "5px"});
            helpDivCol.addClass('col-sm-1 col-xs-1');
            helpDivCol.css({"padding-left": "0px"});
            fieldsDiv.addClass('div-fields');
            fieldsDiv.css({"display": "none"});
            /*кнопка подсказки*/
            buttonDelete.addClass('btn-primary help');
            buttonDelete.css({
                'padding': '5px 10px',
                'border-radius': '5px',
                'height': '42px',
                'width': '42px',
                'margin-left': '5px;'
            });
            buttonDelete.append('<div class="help_question center" style="padding-top:2px;"><i class="fas fa-times-circle"></i></div>');
            helpDivCol.append(buttonDelete);
            /*кнопка раскрытия работы*/
            button.prop('type', 'button');
            button.attr('data-group_id', elem.id);
            button.attr('data-maingroup_id', elem.main_group_id);
            button.addClass('btn add_fields');
            //button.css({'background-color': 'rgb(1, 0, 132)'});
            button.html('<div class="col-xs-10 col-sm-10" style="text-align: left;">' + elem.title + '</div>');
            buttonDivCol.append(button);
            //поля под кнопкой
            //fieldsDiv.append(createFields(elem.fields));
            rowDiv.addClass('group');
            rowDiv.append(buttonDivCol);
            rowDiv.append(helpDivCol);
            rowDiv.append(fieldsDiv);
            rowDiv.addClass('row');
            rowDiv.css({'margin-bottom': '5px', 'margin-top': '5px'});
            resultDiv.append(rowDiv);
        });
        return resultDiv;
    }
</script>
