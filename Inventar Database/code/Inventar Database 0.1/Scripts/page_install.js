
function MoveStep(obj,move){
    
    if($(obj).hasClass("lock")){ return false;}
    
    switch(move){
        case "back":
            RedirectWithPost(window.location.href, {move : "back", option: "stop"});
            break;
        case "next":
            
            if($(".output").children("form").length){
                $(".output").children("form").submit();
            }
            else{
                RedirectWithPost(window.location.href, {move : "next", option: "stop"});
            }
            break;
    }
}

function RemoveSession(){
    
    PostData(window.location.href, {option : ["removeSession", "stop"]});
}

function TryAgain(){
    
    window.location.href = window.location.href;
}
function GoToStartPage(){
    window.location.href = GetUrlNoParam();
}

function PostData(url, data){
    $.post(url, data);
}

function RedirectWithPost(url, data){
    $.post(url, data, function(){
       window.location.href = url; 
    });
}