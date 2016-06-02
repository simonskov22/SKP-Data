
$(function (){
    
    
    $('html').click(function() {
//         && $(".showMenu").css("display") !== "none"
            
        if($(".showMenu").hasClass("open") && $(".showMenu").css("display") === "none"){
            
            $(".showMenu").toggleClass("open");
            toggleNavCallback();
        }
        else if($(".showMenu").hasClass("open")){
            
            $(".showMenu").toggleClass("open");
            $(".navigation").slideUp(toggleNavCallback);
        }
    
   // alert(menuIsOpen);
    
    });
    
    $(".showMenu").change(function (){
        alert("");
    });
    
    $(".showMenu").click(function (event){
        event.stopPropagation();
        
        $(".showMenu").toggleClass("open");        
        
        if($(".navigation").hasClass("showBlock")){
            $(".navigation").slideUp("normal", toggleNavCallback);
        }
        else{
            $(".navigation").slideDown("normal", toggleNavCallback);}
//        
//        $(".navigation").toggleClass("showBlock");
//        
//        
//        
//        
//        $(".navigation").slideToggle("normal", function(){
//            
//            $(".navigation").css("display", "");
//            $(".navigation").toggleClass("showBlock");
//        });
//        $(".showMenu").toggleClass("open");
        
    });
    
    function toggleNavCallback(){
        
            $(".navigation").css("display", "");
            $(".navigation").toggleClass("showBlock");
    }
});