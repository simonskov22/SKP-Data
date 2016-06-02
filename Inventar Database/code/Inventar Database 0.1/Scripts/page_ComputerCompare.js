$(function (){
           
    //flyt alle computer i en div så man
    //kan scroll og den ikke hopper ned
    //på ny linje
    $(".multiTable").append("<div id='computerBox' class='computerBox noMultiSytle'></div>");        
    $(".multiTable").children("ul").each(function (i){
        if(i === 0){return true;}
        
        $(this).appendTo("#computerBox");
    });
    
    //gør så alle element start i lille størrelse
    var hideButton = $(".changeSize");
    ComputerToggleSize(hideButton);
});  

//til at fjern en computer
function ComputerHide(item){
        
    var ulForm = $(item).parent().parent().parent();
    var index = $(".index",ulForm).text();
    
    $(ulForm).animate({width: 0},500,function(){
        $(ulForm).hide();
    });

    //hvis man opdatere siden vil den ikke være der
    $.post("index.php",{ajax: "compareRemove", pc : index});
    //$(ulForm).hide();
}

//udvid og formindsk computer info
function ComputerToggleSize(item){

    var ulForm = $(item).parent().parent().parent();

    $(ulForm).toggleClass("smallVal",500);

    if($(ulForm).hasClass("smallVal")){
        $(item).text("Formindsk");
        //$(ulForm).stop().animate({ width: 200},500);
    }
    else{
        $(item).text("Udvid");
       // $(ulForm).stop().animate({ width: getInnerWidth(ulForm) },500);
    }
}