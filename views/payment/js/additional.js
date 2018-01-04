var bindings = [];
var bindingsEnabled = false;
var OTHER_TEXT = "Другая...";

function togglePasswordField(id) {
    if ($(id).get(0).type == 'password') {
        $(id).get(0).type = 'text'
    } else {
        $(id).get(0).type = 'password'
    }
}
function initBindings(){
    $("#combobox").change(function(){
        //setEnableBinding(selectdItem.value != OTHER_TEXT);
		setEnableBinding(false);
    });
    $("#iCVC").keyup(function(value){
        $("#bindingCvc").val($("#iCVC").val());
    });
    $("#formBinding").hide();
    $("#buttonBindingPayment2").hide();
    $("#buttonBindingPayment2").click(function(){
        $("#buttonBindingPayment").click();
    });
    //$('#combobox').append('<option value="other">'+OTHER_TEXT+'</option>');
    $("#bindingIdSelect option").each(function(index, element){
        if (element.text.length > 0){
            var binding = {value:'',month:'',year:''};
            var len = element.text.length;
            var pan = element.text.substr(0, len-6);
            binding.value = element.value;
            binding.month = element.text.substr(len-5, 2);
            binding.year = element.text.substr(len-2, 2);
            bindings[pan] = binding;
            element.text = pan;
        }
        $('#combobox').append("<option value='"+element.value+"'>"+element.text+"</option>");
    });
}
function setEnableBinding(enable){
    $("#pan_visible").val("");
    if (enable){
        //ENABLE bindings
        clearErrorsView();
        bindingsEnabled = true;
        $("#buttonPayment").hide();
        $("#buttonBindingPayment2").show();
        $.mask.definitions['h'] = "^.{0,19}$";
        bindingSelector.input.mask("?hhhh hhhh hhhh hhhh hhh",{placeholder:" "});
        $("#bindingIdSelect [value='"+bindings[selectdItem.value].value+"']").attr("selected", "selected");
        $(".select-month .ui-selectmenu-status").text($("#month option[value='"+bindings[selectdItem.value].month+"']").text());
        $(".select-year .ui-selectmenu-status").text($("#year option[value='20"+bindings[selectdItem.value].year+"']").text());
        $("#iTEXT").val("");
        document.getElementById("iTEXT").disabled=true;
    } else {
        //DISABLE bindings
        bindingsEnabled = false;
        $("#buttonPayment").show();
        $("#buttonBindingPayment2").hide();
        bindingSelector.input.mask("?9999 9999 9999 9999 999",{placeholder:" "});
        document.getElementById("iTEXT").disabled=false;
    }
}
var errorFields = [
    {id:'#iTEXT',  borderId:'.name-card',       message:'Владелец карты указан неверно'},
    {id:'#iPAN',   borderId:'.number-selection',message:'Номер карты указан неверно'},
    {id:'#iCVC',   borderId:'.code',            message:'CVC указан неверно'},
    {id:'#year',   borderId:'.choice-date',     message:'Срок действия карты указан неверно'},
	{id:'#iAgree', borderId:'.agreeBox',        message:'Укажите Ваше согласие с условиями'}
];
function updateErrors(){
    $('#errorBlock1').empty();
    errorFields.forEach(function(element){
        if ($(element.id).hasClass("invalid")){
            $(element.borderId).addClass('error');
            $('#errorBlock1').append('<p class = "errorField">'+element.message+'</p>');
        } else {
            $(element.borderId).removeClass('error');
        }
    });
}
function clearErrorsView(){
    $('#errorBlock').empty();
    $('#errorBlock1').empty();
    errorFields.forEach(function(element){
        $(element.borderId).removeClass('error');
    });
}