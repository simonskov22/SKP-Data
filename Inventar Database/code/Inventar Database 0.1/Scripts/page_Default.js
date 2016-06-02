$(function (){

        /**
         * Flytter baggrund tekst "SKP DATA"
         * ned på buden af siden
         */
        function moveInfoToBottom(){
            var heightWindow = $(window).height();
            var heightTop = $(".top").outerHeight(true);
            var heightMiddle = $(".middle").outerHeight(true);
            var heightInUse = heightTop + heightMiddle;

            if(heightWindow > heightInUse + 60){
                var heightBottom = heightWindow - heightInUse -$(".bottomInfo").height() -40;
                $(".bottomInfo").css("margin-top", heightBottom);
            }
        }
        moveInfoToBottom();
        $(window).resize(function (){
            moveInfoToBottom();
        });


    });
    
    
function PopUpShow(callFunc){
    
    //gør så hvis den vil slidedown når man lige har loade siden
    $(".contentBox").slideUp(1);
    
    
    $("body").addClass("noScroll");
    $(".popup").fadeIn();
    $(".contentBox").slideDown();
    
    if(callFunc != null){
        callFunc();
    }
    
}
function PopUpHide(callFunc){
    $("body").removeClass("noScroll");
        $(".contentBox").slideUp();
        $(".popup").fadeOut();
    
    if(callFunc != null){
        callFunc();
    }
}
function GetUrlNoParam(fullUrl){
    var url = location.protocol + '//' + location.host + location.pathname;
    
    if(fullUrl === true){
        url = window.location.href ;
    }
    
    return url;
}

function ajax_GetComNamesForItem(table, column, index){
    var url = GetUrlNoParam();
    
    return $.ajax({
        url: url,
        type: "POST",
        data: {ajax: "comUseThis", table: table, column : column, index: index}
    });  
}

function MakeFormSubmit(type,data){
    var url = GetUrlNoParam(true);
    
    var form = Format("<form id='%v' method='%v' action='%v'","fdsfsfg",type,url);
    
    
    for (var key in data){
        if (data.hasOwnProperty(key)) {
            form += Format("<input type='hidden' name='%v' value='%v' />", key, data[key]);
        }
    }
    
    form += "</form>";
    $("body").append(form);
    $("#fdsfsfg").submit();
}
function ReplaceAtPos(string, replace, start, length){
    var text = string.substring(0, start);
    text += replace;
    text += string.substring(start+ length, string.length)

    return text;
}

function Format(){

    var textCurrent = arguments[0];

    for (var i = 1; i < arguments.length; i++) {
        var param =  arguments[i];
        var indexPos = textCurrent.indexOf("%v");

        if(indexPos !== -1){
            textCurrent = ReplaceAtPos(textCurrent, param, indexPos, 2);
        }
    }

    return textCurrent;
}