$(function() {
    var isSend = false;
    var currentFiles = [];
    _AllowUpload();
    
    /*
     * åbener fil vindue hvis 
     * man ikke har klikket åben
     */
    $("#bnt_select").click(function(){
        if(!isSend){$("#selectFiles").click();}
    });
    
    /*
     * upload filerne til servern
     */
    $("#bnt_upload").click(function(){
        
        //tjek om den allerede er 
        //blivet sendt til servern
        if(isSend){ return false; }
        
        //tjek om man må uploade til servern
        if(!_AllowUpload()){ return false; }
        
        //lås siden
        isSend = true;
        $("#bnt_upload").addClass("lock");
        $("#bnt_select").addClass("lock");
        $("#bnt_cancel").addClass("lock");
        $(".buttons").addClass("lock");
        
        //hent data fra form
        var url = $("#computerFiles").attr("action");
        var inputName = $("#selectFiles").attr("name");
        
        //class til at holde filerne
        var formdata = new FormData();
        
        
        //tilføj til upload data
        for(var i = 0; i < currentFiles.length; i++ ){
            var file = currentFiles[i];
            formdata.append(inputName, file);
        }
        
        //nulstil data
        currentFiles = [];
        
        //upload til servern
        $.ajax({
                url: url,
                type: "POST",
                data: formdata,
                processData: false,
                contentType: false,
                success: function (jsonData) {
                    //opdatere fil knapperne
                    _changeToUploadFiles(jsonData);
                },
                error: function(){},
                complete: function(){                    
                     
                    //fjern lås fra siden
                    isSend = false;
                    $("#bnt_select").removeClass("lock");
                    $("#bnt_cancel").removeClass("lock");
                    $(".buttons").removeClass("lock");

                    //nulstil
                    document.getElementById("computerFiles").reset();
                    _AllowUpload();
                }
            }
        );
        
        //$("#computerFiles").submit();
        
    });
    
    /*
     * fjern alle filer
     */
    $("#bnt_cancel").click(function(){
        
        if(!isSend){
            //fjern dem enkeltvis så der 
            //kommer en lille animation på
            var childCount = $("#files_tbody .row").length -1;
            
            for (var i = childCount; i >= 0; i--){
                _RemoveFileElement(i);
            }     
        }
        
        _updateCurrentUploadSize();
    });
    
    /*
     * fjern valgte fil
     */
    $(document).on("click",".bnt_remove",function(){
        
        //skal kun kunne fjerns hvis den ikke er sat til upload
        if(isSend){return false;}
        
        //hent id
        var fileIndex = $(this).parents(".row").index();
        //fjern med animation
        _RemoveFileElement(fileIndex);   
        
        _updateCurrentUploadSize();
        _AllowUpload();
    });
    
    /*
     * når man har klikket upload
     * og vil læse beskeden om filen
     */
    $(document).on("click",".buttons .message",function(){
        alert($(this).children("span").text());
    });
    
    /*
     * når man har valgt nogle filer
     * tilføj dem til en liste så
     * dem man allerede har valgt
     * ikke bliver slettet
     */
    $("#selectFiles").change(function(){
        
        //hvis man lige har upload nogle filer skal
        //den fjerne de filer der allerade er listed
        if($("#files_tbody").hasClass("uploaded")){
            $("#files_tbody").removeClass("uploaded");
            $("#files_tbody .row").remove();
        }
        
        //hent sidste nye index
        var nextFileId = currentFiles.length;
        
        //gem fil til listen
        for(var i = 0; i < $(this).get(0).files.length; i++){
            
            var file = $(this).get(0).files[i]; //hent fil
            var name = file.name; //filnavn
            var size = _byteToMegabyte(file.size) + " MB"; //ca størrelse i mb
            var childElements = "";//list element
            
            //så den ikke tilføjer den samme 
            //fil som man har valgt fra tidligere
            if(_arrayFile_contains(currentFiles, file)){continue;}

            //tilføj til array så den vil blive uplaoded
            currentFiles[nextFileId] = file;
            nextFileId++; //gør klar til næste
            
            //udseende i tabel listen
            childElements += "<div class='row'>";
            childElements += "<div class='col-4-6'><span class='name'>"+name+"</span></div>";
            childElements += "<div class='col-1-6 tRight'><span>"+size+"</span></div>";
            childElements += "<div class='col-1-6 tRight buttons'>";
            childElements += "<button id='bnt_remove"+i+"' class='button red bnt_remove'>Fjern</button>";
            childElements += "</div>";
            childElements += "</div>";
            
            //tilføj til listen
            $("#files_tbody").append(childElements);
        }
        
        _updateCurrentUploadSize();
        //tjek om man må upload nu
        _AllowUpload();
    });
    
    function _updateCurrentUploadSize(){
        
        var currentBytes = 0;
        
        for(var i = 0; i < currentFiles.length; i++){
            
           currentBytes += currentFiles[i].size;
        }
        
        $("#currentUpload").text(_byteToMegabyte(currentBytes) + " MB");
    }
    
    /*
     * fjern element et elemnt fra list
     * med en animation
     */
    function _RemoveFileElement(index){
        document.getElementById("computerFiles").reset();
        currentFiles.splice(index, 1);
        var rowElement = $("#files_tbody").children().eq(index);
        
        $(rowElement).slideToggle(200,function(){
            $(this).remove();
            _AllowUpload();
        });
        
        
    }
    
    /*
     * tjek om man må upload
     * hvis ikke få knap til
     * at ligne den er lås
     */
    function _AllowUpload(){
        if(currentFiles.length === 0){
            $("#bnt_upload").addClass("lock");
            return false;
        }
        else{
            $("#bnt_upload").removeClass("lock");
            return true;
        }
    }
    
    /**
     * tilføjet info knap og
     * man filen er blivet uplaode
     * uden fejl
     * 
     * @param {json} jsonData
     * @returns {undefined}
     */
    function _changeToUploadFiles(jsonData){

        //sig at de filer i denne 
        //tabel er blivet uploade        
        $("#files_tbody").addClass("uploaded");
        
        //lav om til javascirpt array
        var data = $.parseJSON(jsonData);

        for(var i = 0; i < data.length; i++){

           //hent data for fil
           var file = data[i];
           var name = file["filename"];
           var message = file["message"];
           var error = file["error"];

           //ændre vær fil
           $("#files_tbody .row").each(function (){
               
               var rowName = $(".name",this).text();

               //find i liste
               if(name === rowName){
                   
                    //nulstil knapper
                    $(".buttons",this).html("");

                    //tilføj info knap
                    $(".buttons",this).append("<button class='message button blue'>&#10067;<span style='display:none'>"+message+"</span></button>");

                    //om der er sket fejl ved upload (knap med status)
                    if(error){$(".buttons",this).append("<button class='button red'>&#10006;</button>");}
                    else{$(".buttons",this).append("<button class='button green'>&#10004;</button>");}

                    //stop da den ikke skal lede 
                    //efter flere filer i listen
                    //break;
               }
           });
       }
    }
});

/*
 * til at tjekke om en array indeholder en fil
 */
function _arrayFile_contains(array, value){

    for(var i = 0; i < array.length; i++){

        if(array[i].name === value.name && array[i].size === value.size){
            return true;
        }
    }
    
    return false;
}

/*
 * lav byte om til megabyte
 */
function _byteToMegabyte(byte){
    var oneMB = 1048576;
    var valInMB = byte / oneMB;
    return valInMB.toFixed(2);
}