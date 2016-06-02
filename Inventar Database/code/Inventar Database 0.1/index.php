<?php
//header('Content-Type:text/plain');
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('max_execution_time', 0);

include_once 'Inc/Database.php';
include_once 'Inc/FileData.php';
include_once 'Inc/MultiTable.php';
include_once 'Inc/functions.php';
if(file_exists('Inc/dbconfig.php')){
    include_once 'Inc/dbconfig.php';
}


class website{
    
    public $Styles = array();
    public $Scripts = array();
    private $Url = "";
    private $Page = "";
    private $Folder_upload = "Upload/";

    
    public function UPDATESQL() {
        $database = new Database();
        echo "SELECT `comTypeName` FROM `{$database->Prefix}computertype` WHERE `comType_id` = 1;";
        $data_comtype = $database->GetResult("SELECT `comTypeName` FROM `{$database->Prefix}computertype` WHERE `comType_id` = 1;");
        $data_os = $database->GetResult("SELECT `osName`, `servicePack` FROM `{$database->Prefix}operatingsystem` WHERE `os_id` = 1;");
        $data_mother = $database->GetResult("SELECT `motherName`, `chipset`, `bios` FROM `{$database->Prefix}motherboard` WHERE `mother_id` = 1;");
        $data_cpu = $database->GetResult("SELECT `cpuName` FROM `{$database->Prefix}cpu` WHERE `cpu_id` = 1;");
        $data_ram = $database->GetResult("SELECT `ramName`, `size`, `fFactor`, `type` FROM `{$database->Prefix}ram` WHERE `ram_id` = 1;");
        $data_video = $database->GetResult("SELECT `videoName` FROM `{$database->Prefix}video` WHERE `video_id` = 1;");
        $data_audio = $database->GetResult("SELECT `audioName` FROM `{$database->Prefix}audio` WHERE `audio_id` = 1;");
        
        
        $index_comtype = $database->Insert($database->Prefix."computertype", $data_comtype);
        $index_os = $database->Insert($database->Prefix."operatingsystem", $data_os);
        $index_mother = $database->Insert($database->Prefix."motherboard", $data_mother);
        $index_cpu = $database->Insert($database->Prefix."cpu", $data_cpu);
        $index_ram = $database->Insert($database->Prefix."ram", $data_ram);
        $index_video = $database->Insert($database->Prefix."video", $data_video);
        $index_audio = $database->Insert($database->Prefix."audio", $data_audio);
        
        foreach (array($index_comtype,$index_os,$index_mother,$index_cpu,$index_ram,$index_video,$index_audio) as $value) {
            echo "$value<br>";
        }
        
        $database->Update($database->Prefix."computers", array("comType_id" => $index_comtype), array("comType_id" => 1));
        $database->Update($database->Prefix."computers", array("os_id" => $index_os), array("os_id" => 1));
        $database->Update($database->Prefix."computers", array("mother_id" => $index_mother), array("mother_id" => 1));
        $database->Update($database->Prefix."computers", array("cpu_id" => $index_cpu), array("cpu_id" => 1));
        $database->Update($database->Prefix."computersram", array("ram_id" => $index_ram), array("ram_id" => 1));
        $database->Update($database->Prefix."computersvideo", array("video_id" => $index_video), array("video_id" => 1));
        $database->Update($database->Prefix."computersaudio", array("audio_id" => $index_audio), array("audio_id" => 1));
        
        $database->Delete($database->Prefix."computertype", array("comType_id" => 1));
        $database->Delete($database->Prefix."operatingsystem", array("os_id" => 1));
        $database->Delete($database->Prefix."motherboard", array("mother_id" => 1));
        $database->Delete($database->Prefix."cpu", array("cpu_id" => 1));
        $database->Delete($database->Prefix."ram", array("ram_id" => 1));
        $database->Delete($database->Prefix."video", array("video_id" => 1));
        $database->Delete($database->Prefix."audio", array("audio_id" => 1));
        
        $DeletedText = "# Slettet #";
        
        $database->Insert($database->Prefix."computertype", array("comType_id" => "1", "comTypeName" => $DeletedText));
        $database->Insert($database->Prefix."operatingsystem", array("os_id" => "1", "osName" => $DeletedText, "servicePack" => ""));
        $database->Insert($database->Prefix."motherboard", array("mother_id" => "1", "motherName" => $DeletedText, "chipset" => "", "bios" => ""));
        $database->Insert($database->Prefix."cpu", array("cpu_id" => "1", "cpuName" => $DeletedText));
        $database->Insert($database->Prefix."ram", array("ram_id" => "1", "ramName" => $DeletedText, "size" => "", "fFactor" => "", "type" => ""));
        $database->Insert($database->Prefix."video", array("video_id" => "1", "videoName" => $DeletedText));
        $database->Insert($database->Prefix."audio", array("audio_id" => "1", "audioName" => $DeletedText));
    }
    
    public function REINSTALLSQL(){
        $database = new Database();
        
        $database->Drop($database->Prefix."computersRam");
        $database->Drop($database->Prefix."computersVideo");
        $database->Drop($database->Prefix."computersAudio");
        $database->Drop($database->Prefix."ram");
        $database->Drop($database->Prefix."video");
        $database->Drop($database->Prefix."audio");
        $database->Drop($database->Prefix."computers");
        $database->Drop($database->Prefix."operatingSystem");
        $database->Drop($database->Prefix."cpu");
        $database->Drop($database->Prefix."computerType");
        $database->Drop($database->Prefix."motherboard");
        
        session_start();
        $_SESSION["INSTALL"] = array(
                "step" => 0,
                "database" => array(
                    "host"=> DB_HOST,
                    "user"=> DB_USER,
                    "pass"=> DB_PASS,
                    "database"=> DB_DATABASE,
                    "prefix"=> DB_PREFIX
                )
            );
        
        $install = new install();
        $install->Install_CreateTables();    
        
        echo "Uploading files... ".time()."<br>";
        $files = scandir("Upload/");
        foreach ($files as $filename) {
            if (!is_dir($filename)) {
                $fileData = new FileData("/Upload/".$filename);
            }
        }
        echo "Done Uploading ".time()."<br>";
    }
    
    public function __construct() {
        
        
        $this->Navigation_ajax();
        $this->Navigation_page();
    }
    
    /**
     * kør kommandoer der
     * er blevet sat ved
     * hjælp af javascirpt
     * 
     * @return false på fejl
     */
    private function Navigation_ajax(){
        
        $inPost = isset($_POST["ajax"]) && !empty($_POST["ajax"]);
        $inGet = isset($_GET["ajax"]) && !empty($_GET["ajax"]);
        
        //hvis den ikke er sat
        if(!$inPost && !$inGet){
            return false;
        }
        
        //hent navnet som bliver brugt til at finde den 
        //function der skal køre
        $functionName = $inPost ? $_POST["ajax"] : $_GET["ajax"];
       
        switch ($functionName) {
            case "upload":
                $this->FileUpload();
                break;
            
            case "compareRemove":
                $this->ComparePcRemove();
                break;
            case "comUseThis":
                $this->ajax_GetComputere();
                break;
        }
        
        
        //stop med at udskrv mere
        //da disse functioner kun
        //bliver brugt i javascirpt
        die();
    }   
    /**
     * kør side function
     * default: Navigation_page
     */
    private function Navigation_page(){
        $content = "";
        
        //hent sidenavn gør det til lowercase
        $page = isset($_GET["page"]) ? $_GET["page"] : "";
        $page = strtolower($page);
        
        if(!file_exists("Inc/dbconfig.php") && $page != "install"){
            header("Location: {$_SERVER["PHP_SELF"]}?page=install");
        }
        
        //kør side function
        switch ($page) {
            case "compare":
                    $content = $this->page_ComputerCompare();
                break;
            
            case "partsused";
                    $content = $this->page_PartsUsed();
                break;
            
            case "addnew";
                    $content = $this->page_AddComputer();
                break;
            
            case "delete";
                    $content = $this->page_Delete();
                break;
            case "install";
                include_once 'Inc/install.php';
                
                    $install = new install();
                    $content = $install->PerformeStep();
                    
                    $this->Styles = array_merge($this->Styles, $install->Styles);
                    $this->Scripts = array_merge($this->Scripts, $install->Scripts);
                break;
            
            
            case "test";
                    $content = $this->Test_SelectValues();
                break;
            default:
                $content = $this->page_Welcome();
                break;
        }
        
        //kør template
        $this->ExecutePage($content);        
    }
    
    
    private function Test_SelectValues(){
        
        
        $this->Styles[] = "buttons.css";
        
        $filcontent = file_get_contents("Upload/1. PC-SKILT11 - Kopi (2).html");
        ob_start();
?>
<style>
    .middle .content{
        height: 98%;
    }
    .headerBox{
        font-size: 20px;
        font-weight: bold;
        padding: 5px;
        /*color: #03A9F4;*/
        margin: 5px 0px;
    }
    .headerBox:before{
        padding: 10px;
    }
    .headerBox.close:before{
        content : '\25BA';
    }
    .headerBox.open:before{
        content : '\25BC';
    }
    .headerBox:after{
        content : '';
        background: red;
        position: absolute;
        width: 7px;
        top: 33px;
        left: 21px;
        bottom: 0px;
    }
    td{
        padding-left: 30px;
    }
    li.header{
        font-weight: bold;
        padding-left: 10px;
        list-style: none;
    
    }
    h4.header span:hover:before{
        
        content: '\270F';
        display: block;
        position: absolute;
        color: yellow;
        transform: rotate(40deg);
        top: 3px;
        left: 6px;
    }
    li.options{
        cursor: pointer;
        list-style: none;
        padding-left: 41px;
    }
    li.options:hover{
        background: gray;
    }
    li.options:before{    
        content: '';
        background: white;
        width: 13px;
        height: 13px;
        border: 1px solid black;
        position: absolute;
        margin-left: -17px;
        margin-top: 1px;
    }
    li.options.selected:before{   
        background: lightskyblue;
    }
    .computerDbOptionBox{
        
        position: relative;
        width: 800px;
        height: 100%;
        margin: 0 auto;
        overflow: hidden;
        border-radius: 5px;
        border: 2px groove;
    }
    .computerDbOptionHeader{
        height: 20%;
        padding: 0 5px;
        border-bottom: 2px groove;
        white-space: nowrap;
        overflow: auto;
    }
    .computerDbOptionsList{
        
        width: 72%;        
        height: 80%;
        overflow-y: auto;
        padding: 0 20px 250px;
        display: inline-block;
    }
    .computerDbOptionMenu{
        
        /*position: absolute;*/
        vertical-align: top;
        width: 28%;
        height: 80%;
        display: inline-block;    
        border-left: 2px groove;
    }
    .buttonControl{
        margin: 0 0 25px;
    }
    .buttonControl button{
        display: block;
        width: 150px;
        margin: 5px auto;
    }
   
    
    .computerDbTableBox{
        display: inline-block;
        width: 160px;
        margin: 10px 5px;
        background: lightgray;
        border: 4px solid white;
        cursor: pointer;
    }
    .computerDbTableBox.selectedTable{
        border: 4px solid black;
        border-radius: 5px;
    }
    .computerDbTableBox p{
        margin: 0;
        padding: 7px 5px;}
    .computerDbTableBox p span{
        display: inline-block;
        float: right;
    }
    .computerDbTableBox .header{
        text-align: center;
        margin: 0;
        padding: 5px 0;
        background: red;
        color: white;
    }
    .computerDbTableBox .header span{
        position: relative;
    }
    .computerDbTableBox .columns span{
        
        background: gray;
        padding: 3px 5px;
        color: white;
        border-radius: 20%;
        font-size: 12px;
    }
    .computerDbTableBox .colorId span{
        
        width: 15px;
        height: 15px;
        background: red;
        border: 1px solid black;
    }
    .computerDbTableBox{}
</style>
<script>
    $(function(){
        
        $(".computerDbTableBox").click(function (){
            $(".computerDbTableBox").removeClass("selectedTable");
            $(this).addClass("selectedTable");
        });
        
        
        $(".pt").click(function (){
            $(this).toggleClass("open","close");
            $(this).parent().children("div").slideToggle();
        });
        
        $("li.options").click(function (){
            $(this).toggleClass("selected");
        });
    });
    
    
    function _bnt_OptionsVal(){
        console.log("###########################");
        $("li.options.selected").each(function(){
            console.log($(this).text());
        });
    }
</script>
<?php
        global $eachLoopVal, $filContentText;
        
        $eachLoopVal = array(
            "lastTable" => "",
            "titleDrop" => array(
                "Summary", "Portable Computer", "Sensor", "Debug - PCI",
                "Debug - Video BIOS","Debug - Unknown"
            ),
            "headUseLines" => array(
                "Multi-Monitor", "Video Modes", "Windows Audio", "PCI / PnP Audio", "SMART",
                "Logical Drives", "Physical Drives", "Device Resources"
            )
        );
        
        $testrun = new ElementHTML($filcontent);
        $filContentText = array();
        
        $testrun->Find("html")->Remove(true);
        $testrun->Find("head")->Remove();
        $testrun->Find("body")->Remove(true);
        $testrun->Find("div")->Remove();
        $testrun->RemoveEmptyAll();
        
        $testrun->Each("table", function($elementTable){
            global $eachLoopVal;
            
            $eachLoopVal["dropTillNew"] = isset($eachLoopVal["dropTillNew"]) ? $eachLoopVal["dropTillNew"] : false;
            
            $isTitle = $elementTable->Count("tr") == 0;
            if($isTitle){
                $eachLoopVal["lastTable"] = $elementTable->TextOnly()[0];                
                $eachLoopVal["dropTillNew"] = in_array($eachLoopVal["lastTable"], $eachLoopVal["titleDrop"]);
            }
            
            if($eachLoopVal["dropTillNew"]){ 
                $elementTable->Remove();
                return;
            }
            
            $elementTable->Each("tr",function ($elementRow, $i){
                global $saveData, $eachLoopVal;
                
                $saveData["headUseLinesIsSet"] = isset($saveData["headUseLinesIsSet"]) ?
                        $saveData["headUseLinesIsSet"] : false;
                
                if($i == 0) {
                    $saveData["noDub"] = array();
                    $saveData["header"] = "none";
                }
                
                $hasMore = $elementRow->Count("td") >= 2;
                
                if(!$hasMore){
                    
                    $header = $elementRow->TextOnly()[0];
                    
                    $charFirst = substr($header, 0, 1);
                    $charLast = substr($header, strlen($header) -1, 1);
                    if($charFirst == "[" && $charLast == "]") $elementRow->Remove();
                    else $saveData["header"] = $header;
                }                
                else if($hasMore){
                    
                            
                    if(in_array($eachLoopVal["lastTable"] ,$eachLoopVal["headUseLines"])){
                        
                        if($saveData["headUseLinesIsSet"]) $elementRow->Remove();
                        $saveData["headUseLinesIsSet"] = true;
                        return;
                    }
                    $elementRow->FindLast("td")->Remove();
                }
                
            });
        });
        
        $testrun->Each("table", function($elementTable, $tableId){
            global $saveData, $filContentText;
            
            $isTitle = $elementTable->Count("tr") == 0;
            
            if($isTitle){
                
                $title = $tableId == 0 ? 
                    "Computer Info" : $elementTable->TextOnly()[0];
                
                $saveData["title"] = $title;
                
                if(!array_key_exists($title, $filContentText))
                    $filContentText[$title] = array();
            }
            else{
                $title = $saveData["title"];
                $header = "#None";
                $allText = $elementTable->TextOnly();
                
                for($i = 0; $i < count($allText); $i++){
                    
                    $text = $allText[$i];
                    $isHeader = substr($text, strlen($text)-1, 1) == ":";
                    
                    if(!$isHeader && $i == 0) $header = "#None";
                    else if($isHeader) $header = $text;
                    
                    if(!array_key_exists($header, $filContentText[$title]))
                        $filContentText[$title][$header] = array();
                    
                    if(!$isHeader && !in_array($text, $filContentText[$title][$header]))
                            $filContentText[$title][$header][] = $text;
                }
            }
        });
        
        //db_title
        array("title1","title2","title3");
        //db_header
        array("header1","header2","header3");
        
        
        
        
        
        
        
        echo "<div class='computerDbOptionBox'>";
        echo "<div class='computerDbOptionHeader'>";
        
        for($i = 0; $i < 20; $i++){
        echo "<div class='computerDbTableBox'>";
        echo "<h4 class='header'><span>Table</span></h4>";
        echo "<p class='columns'>Kolonner: <span>1</span></p>";
        echo "<p class='colorId'>Farve: <span></span></p>";
        echo "</div>";
        }
        echo "</div>";
        echo "<div class='computerDbOptionsList'>";
        
        $elementList = array();
        foreach ($filContentText as $title => $headerVal) {
            //echo "$title<br>";
            
            $element = "<div style='position: relative;'>";
            $element .=  "<h2 class='pt headerBox close button blue'>{$title}</h2>";
            $element .= "<div style='display: none;'>";
            $element .= "<ul>";
            foreach ($headerVal as $header => $values) {
                
                if($header != "#None") 
                    $element .= "<li class='header'>{$header}</li>";
                    
                foreach ($values as $val) {
                     $element .= "<li class='options'>{$val}</li>";
                }
            }
            $element .= "</ul></div></div>";
            $elementList[] = $element;
        }
        
        foreach ($elementList as $element){ echo $element;}
//        
//        $testrun->Each("table", function($elementTable, $i){
//            global $ignoreElement, $titleDrop;
//            
//            
//            $textOnly = $elementTable->TextOnly();
//            $element = "";
//            //$textOnly = $this->CheckForDuplicates(0, count($textOnly), $textOnly);
//            if($i % 2 == 0){              
//                
//                $ignoreElement = in_array($textOnly[0], $titleDrop);
//                
//                $element .= "<div style='position: relative;'>";
//                $element .=  "<h2 class='pt headerBox close button blue'>{$textOnly[0]}</h2>";
//                $element .= "<div style='display: none;'>";
//            }
//            if($i % 2 != 0){
//                
//                $element .= "<ul>";
//                foreach ($textOnly as $value) {
//                    
//                    $sChar = substr($value, 0, 1);
//                    $eChar = substr($value, strlen($value) -1);
//                    if($sChar == "[" && $eChar == "]") continue;
//                    else if($eChar == ":") {
//                        //$style = $elementTable->GetAttr("style");
//                        $element .= "<li class='header'>$value";
//                    
//                    }
//                    else $element .= "<li class='options'>$value";
//                }
//                
//                $element .= "</ul>";
//                $element .= "</div></div>";
//            
//            }
//            
//            if(!$ignoreElement){
//                echo $element;
//            }
//        });
        echo "</div>";
        
        echo "<div class='computerDbOptionMenu'>";
        
        echo "<div class='buttonControl'>";
        echo "<button class='button blue' onclick='_bnt_OptionsVal();'>Opret som ny tabel</button>";
        echo "<button class='button blue'>Tilføj til valgte tabel</button>";
        echo "<button class='button blue'>Fjern valgte tabel</button>";
        echo "</div>";
        
        echo "<div class='buttonControl'>";
        echo "<button class='button blue'>Vælg kolonner fra tabellen</button>";
        echo "<button class='button blue'>Knap</button>";
        echo "<button class='button blue'>Knap</button>";
        echo "</div>";
        
        echo "<div class='buttonControl'>";
        echo "<button class='button blue'>Knap</button>";
        echo "<button class='button blue'>Knap</button>";
        echo "<button class='button blue'>Knap</button>";
        echo "</div>";
        echo "</div>";
        echo "</div>";
        
        $content = ob_get_contents();
        ob_end_clean();
        
        return $content;
    }
    private function CheckForDuplicates($start, $end, $array){
        
        $noDubStart = array_slice($array, 0, $start);
        $noDubEnd = array_slice($array, $end, count($array) - $end);
        $noDub = array();
        
        for($i = $start; $i < $end; $i++){
            
            if(!in_array($array[$i], $noDub)){
                $noDub[] = $array[$i];
            }
        }
        
        $val = array_merge($noDubStart, $noDub, $noDubEnd);
        return $val;
    }
    
    private function page_Welcome() {
       
        if(file_exists("readme.html")){
            return "<iframe src='readme.html' style='height: 90%; width: 95%;    position: absolute;'></iframe>";
        }
        
        return "";
    }    
    private function page_AddComputer() {
        
        //add styles
        $this->Styles[] = "buttons.css";
        $this->Styles[] = "page_AddComputer.css";
        
        //add scripts
        $this->Scripts[] = "page_AddComputer.js";
            
        ob_start();
        
        echo ByteToHighest(file_upload_max_size());
        echo "<span id='currentUpload'>11 vv</span>";
        
        // <editor-fold desc="Side layout">
        ?>

            <form id="computerFiles" style="display: none;" method="post" action="http://localhost/InventarDatabase/index.php?page=page3&ajax=upload">
                <input id="selectFiles" name="files[]" type="file" multiple accept=".html"/>
            </form>

<div class="uploadDiv">

                <h1 style="text-align: center;">Upload til databasen</h1>

                <div class="button_container">
                    <button id="bnt_select" class="button green mediumBig" style="float: left;">Tilføj filer...</button>
                    <button id="bnt_upload"  class="button blue mediumBig">Upload nu</button>
                    <button id="bnt_cancel"  class="button red mediumBig" style="float: right;">Annuller</button>
                </div>

<!--                <div id="uploadBar" class="uploadBar inaktiv">
                    <div id="processBar" class="process middleLine"></div>
                </div>-->

<hr>

                <div id="files_tbody" class="grid table"></div>
            </div>
            
        <?php
        // </editor-fold>
        
        $content = ob_get_contents();
        ob_end_clean();
        
        return $content;
    }    
    private function page_ComputerCompare() {

        if(!function_exists("array_GridDivReady")){
            function array_GridDivReady($array){
                $oneDimen = array();

                foreach ($array as $value) {
                    if(is_array($value)){
                        $oneLineArray = array_GridDivReady($value);
                        $oneDimen[] = implode("<br>-<br>", $oneLineArray);
                    }
                    else{
                        $oneDimen[] = $value;
                    }
                }

                return $oneDimen;
            }
        }

        if(!function_exists("CreateGridDiv")){
            function CreateGridDiv($array, $prefix, $checkboxVal){

                $array = array_merge(array("Alle"), $array);
                $checkboxVal = array_merge(array("-1"), $checkboxVal);

                $postString = "{$prefix}Values";

                $hasPost = isset($_POST[$postString]) && is_array($_POST[$postString]);

                $gridValDiv = "<div id='{$prefix}Selector' class='grid hidden'>";

                for($i = 0; $i < count($array); $i += 3){
                    $gridValDiv .= "<div class='row'>";
                    for($a = 0; $a < 3; $a++){

                        $key = $i + $a;
                        if(!array_key_exists($key, $array)){continue;}

                        $valueText = $array[$key];
                        $index = $prefix .($key);
                        $isChecked = $hasPost && in_array($checkboxVal[$key], $_POST[$postString]);

                        $checkedClass = $isChecked ? "green":"red";
                        $checkedBox = $isChecked ? "checked":"";

                        $gridValDiv .= "<div class='col-1-3'>";
                        $gridValDiv .= "<label class='button $checkedClass' for='$index'>$valueText</label>";

                        if($checkboxVal[$key] !== -1){


                            $gridValDiv .= "<input id='$index' name='{$postString}[]' style='display: none;' type='checkbox' value='$checkboxVal[$key]' $checkedBox/>";
                        }
                        $gridValDiv .= "</div>";
                    }

                    $gridValDiv .= "</div>";
                }

                $gridValDiv .= "</div>";

                return $gridValDiv;
            }
        }

        if(!function_exists("array_GetIndexs")){
            function array_GetIndexs(&$array){
                $indexs = array();

                foreach ($array as &$value) {
                    if(is_array($value)){
                        $indexs[] = array_shift($value);
                    }
                }

                return $indexs;
            }
        }

        if(!function_exists("MakeDataMultiTableReady")){
    function MakeDataMultiTableReady($array){
        
        $multiReady = array(
            "com" => array(
                "computerName" => $array["computer"]["name"],
                "comTypeName" => $array["computer"]["comTypeName"],
                "hddTotalSize" => $array["computer"]["hddTotalSize"],
                "ipAddress" => $array["computer"]["ip address"],
                "macAddress" => $array["computer"]["mac address"],
                "osName" => $array["os"]["name"],
                "servicePack" => $array["os"]["service Pack"],
                "motherName" => $array["motherboard"]["name"],
                "chipset" => $array["motherboard"]["chipset"],
                "bios" => $array["motherboard"]["bios"],
                "cpuName" => $array["cpu"]["name"]
            ),
            "ram" => $array["ram"],
            "vid" => $array["video"],
            "aud" => $array["audio"],
        );
        
        return $multiReady;
    }
}

        //class til at kunne lave tabel
        $computerTable = new MultiTable(); 
        $database = new Database();
        
        $computerShow = $this->post_ComputerComapre_GetComputers();
        
        //add styles
        $this->Styles[] = "buttons.css";
        $this->Styles[] = "multiTable.css";
        $this->Styles[] = "page_ComputerCompare.css";
        
        //add scripts
        $this->Scripts[] = "page_ComputerCompare.js";

        
        // <editor-fold desc="$tableHeadArr - hvordan multitabel skal se ud">     
        $tableHeadArr = array(
            "Main" => array(
                "dbKey" => "com",
                "Name:" => "computerName",
                "Type:" => "comTypeName", 
                "Hard Drive:" => "hddTotalSize"),        
            "Network" => array(
                "dbKey" => "com",
                "IP:" => "ipAddress", 
                "MAC:" => "macAddress"),
            "Operating System" => array(
                "dbKey" => "com",
                "Version:" => "osName", 
                "Service Pack:" => "servicePack"),
            "Motherboard" => array(
                "dbKey" => "com",
                "Name:" => "motherName", 
                "Chipset:" => "chipset", 
                "BIOS:" => "bios"),
            "Processor" => array(
                "dbKey" => "com",
                "Name:" => "cpuName"),
            "Ram" => array(
                "dbKey" => "ram",
                "Firm" => "ramName",
                "Size" => "size", 
                "Type" => "type"),
            "Display" => array(
                "dbKey" => "vid",
                "Video Adapter:" => "videoName"),
            "Multimedia" => array(
                "dbKey" => "aud",
                "Audio Adapter:" => "audioName")
        );
        // </editor-fold>
        
        //multitable index tæller
        $columnId = 0;
                
        //print_array($tableHeadArr);
         ob_start();
        //hent computer data og tilføj det til multitabellen
        for($columnId = 0; $columnId < count($computerShow); $columnId++){
            
            //hent com id til databasen
            //$index = $compuerIndexs[$columnId];
            
            //hent data fra databasen
            $comData = MakeDataMultiTableReady($computerShow[$columnId]->FileData);
            
            foreach ($tableHeadArr as $header => $subheaderArr) {
                //hent den key der skal bruge til at finde data i $comData
                $dbkey = $subheaderArr["dbKey"];
                
                //tjek om der er flere værdier med samme subheader
                $first_key = key($comData[$dbkey]);
                $arrayToOneLineDB = array();
                $isArray = false;
                $lastSubHead = "";
                
                foreach ($subheaderArr as $subheader => $databaseName) {
                    
                    //skal ikke tag denne med da den kun bliver brugt til $comData
                    if($subheader == "dbKey"){ continue;}
                    
                    //hvis der er flere med samme subheader
                    //skal man tilføj 
                    //(disse bruger array(array("subheader"=>"value")))
                    if(is_int($first_key)){
                        $isArray = true;
                        $arrayToOneLineDB[] = $databaseName;
                        $lastSubHead = $subheader;
                    }
                    else{
                        //vær sikker på at den inde holder key
                        if(!array_key_exists($databaseName, $comData[$dbkey])){ continue;}
                        $computerTable->AddValue($columnId, $header, $subheader, $comData[$dbkey][$databaseName]);
                    }
                }
                if($isArray){

                    foreach ($comData[$dbkey] as $row) {
                        $arrayToOneLine = "";
                        foreach ($arrayToOneLineDB as $key) {
                            
                            $arrayToOneLine .= $row[$key]. ", ";
                        }
                        
                        $arrayToOneLine = strshort($arrayToOneLine, 2);
                        $mySubHead = count($arrayToOneLineDB) == 1 ? $lastSubHead: $header;
                        $computerTable->AddValue($columnId, $header, $mySubHead,  $arrayToOneLine);
                    }

                }
                
            }       
        }
        
        
        
        $buttonForVal = "&nbsp;<span class='noMultiSytle controlButtons'>";
        $buttonForVal .= "<button class='button red noMultiSytle' onclick='ComputerHide(this);'>Fjern</button>";
        $buttonForVal .= "<button class='button blue noMultiSytle changeSize' onclick='ComputerToggleSize(this);'>Udvid</button>";
        //$buttonForVal .= "</span>";
        
        //tilføj header med knapper
        for($columnId = 0; $columnId < count($computerShow); $columnId++){
            
            $index = $computerShow[$columnId]->index;
            
            $buttonForValFinal = $buttonForVal;
            $buttonForValFinal .= "<span class='index'>$index</span>";
            $buttonForValFinal .= "</span>";
            
            $computerTable->AddValueDisplayedHeader($columnId, $buttonForValFinal);
        }     
        
        
        
        ?>

<style>
    .grid *{
        
        box-sizing: border-box;
    }
    
    .grid [class*="col-"] {
        float: left;
        padding: 15px 0px;    
    }
    .grid .row{
        overflow: hidden;
    }

    .grid .col-1-3 {width: 33.33%;}
</style>

<style>
    
    
    .selectNeeds .grid{
    }
    
    .selectNeeds .grid [class*="col-"]{
        text-align: center;
    }
    
    
    
    @media only screen and (max-width: 550px) {
        
        .selectNeeds .grid [class*="col-"]{
            width: 100%;
            padding: 3px 0;
        }
    }
</style>  

<script>
$(function (){
    
    $(".selectNeeds label").click(function (){
        
        var text = $(this).text();
        
        
        
        if(text === "Alle"){
            
            if($(this).hasClass("green")){
                
                $("input",$(this).parents(".grid")).prop("checked", false);
                $("label",$(this).parents(".grid")).not(".red").toggleClass("red green", 150);  
            }
            else{
                
                $("input",$(this).parents(".grid")).prop("checked", true);
                $("label",$(this).parents(".grid")).not(".green").toggleClass("green red", 150);
            }
        }
        else{
            $("label",$(this).parents(".grid")).first().not(".red").toggleClass("green red", 150);
            $(this).toggleClass("red green", 150);
        }
    });
});    

function ShowDiv(divId){
    $(".selectNeeds .grid").hide();
    $(divId).show();
    
    if(divId === "#select_All_Selector"){
        $(".goback").addClass("hidden");
    }
    else{
        $(".goback").removeClass("hidden");}
}

function Popup(){
    ShowDiv("#select_All_Selector");   
}
</script>

<?php

// <editor-fold desc="Kategori oprettelse">

$categories = array(
    array("index"=>"#select_Name_Selector","text"=>"Navn"),
    array("index"=>"#select_OS_Selector","text"=>"OS"),
    array("index"=>"#select_Mother_Selector","text"=>"Motherboard"),
    array("index"=>"#select_CPU_Selector","text"=>"Processor"),
    array("index"=>"#select_Ram_Selector","text"=>"Ram"),
    array("index"=>"#select_Video_Selector","text"=>"Display"),
    array("index"=>"#select_Audio_Selector","text"=>"Multimedia")
);

$categoryDiv = "<div id='select_All_Selector' class='grid'>";
for($row = 0; $row < count($categories); $row += 3){

    $categoryDiv .= "<div class='row'>";

    for($column = 0; $column < 3; $column++){

        if(!array_key_exists($row + $column, $categories)){continue;}

        $divVal = $categories[$row + $column];

        $categoryDiv .= "<div class='col-1-3'>";
        $categoryDiv .= "<input type='button' class='button blue' onclick='ShowDiv(\"{$divVal["index"]}\");' value='{$divVal["text"]}' />";
        $categoryDiv .= "</div>";
    }
    $categoryDiv .= "</div>";
}
$categoryDiv .= "</div>";

// </editor-fold>
// <editor-fold desc="Checkboxs oprettelse">

$computerNameRows = $database->GetResults("SELECT `computerName` FROM `{$database->Prefix}computers`;");
$computerOsRows = $database->GetResults("SELECT `os_id`, `osName`, `servicePack` FROM `{$database->Prefix}operatingsystem`;");
$computerMotherRows = $database->GetResults("SELECT `mother_id`, `motherName`, `chipset`, `bios` FROM `{$database->Prefix}motherboard`;");
$computerCpuRows = $database->GetResults("SELECT `cpu_id`, `cpuName` FROM `{$database->Prefix}cpu`;");
$computerRamRows = $database->GetResults("SELECT `ram_id`, `ramName`, `size` FROM `{$database->Prefix}ram`;");
$computerVideoRows = $database->GetResults("SELECT `video_id`, `videoName` FROM `{$database->Prefix}video`;");
$computerAudioRows = $database->GetResults("SELECT `audio_id`, `audioName` FROM `{$database->Prefix}audio`;");

$indexsOs = array_GetIndexs($computerOsRows);
$indexsMother = array_GetIndexs($computerMotherRows);
$indexsCpu = array_GetIndexs($computerCpuRows);
$indexsRam = array_GetIndexs($computerRamRows);
$indexsVideo = array_GetIndexs($computerVideoRows);
$indexsAduio = array_GetIndexs($computerAudioRows);

$computerNameRowsR = array_GridDivReady($computerNameRows);
$computerOsRowsR = array_GridDivReady($computerOsRows);
$computerMotherRowsR = array_GridDivReady($computerMotherRows);
$computerCpuRowsR = array_GridDivReady($computerCpuRows);
$computerRamRowsR = array_GridDivReady($computerRamRows);
$computerVideoRowsR = array_GridDivReady($computerVideoRows);
$computerAudioRowsR = array_GridDivReady($computerAudioRows);

$selectDivs = array();
$selectDivs[] = $categoryDiv;
$selectDivs[] = CreateGridDiv($computerNameRowsR, "select_Name_", $computerNameRowsR);
$selectDivs[] = CreateGridDiv($computerOsRowsR, "select_OS_", $indexsOs);
$selectDivs[] = CreateGridDiv($computerMotherRowsR, "select_Mother_", $indexsMother);
$selectDivs[] = CreateGridDiv($computerCpuRowsR, "select_CPU_", $indexsCpu);
$selectDivs[] = CreateGridDiv($computerRamRowsR, "select_Ram_", $indexsRam);
$selectDivs[] = CreateGridDiv($computerVideoRowsR, "select_Video_", $indexsVideo);
$selectDivs[] = CreateGridDiv($computerAudioRowsR, "select_Audio_", $indexsAduio);

// </editor-fold>

?>
<div style="text-align: center;">
    <button class="button blue big" onclick="PopUpShow(Popup);">Skal indeholde</button>
    <div id="selectComs" class="popup hidden">
        <div class="selectNeeds contentBox">
            <h2>
                <button class="button blue goback option hidden" onclick="ShowDiv('#select_All_Selector');">&#9668;</button>
                Vælg
                <button class="button red close option" onclick="PopUpHide();">&#10006;</button>
            </h2>

            <form method="post">
                <input name="action" type="hidden" value="selectComs"/>

                <?php 
                foreach ($selectDivs as $div) {
                    echo $div;
                }
                ?>

                <div class="buttonControls"><input type="submit" class="button blue" value="Hent computere"/></div>
            </form>
        </div>
    </div>
</div>



        <?php
        
        //skal kun skrive hvis den ikke er tom
        if(!empty($computerShow)){
            $computerTable->PrintTable();
        }
        
        $content = ob_get_contents();
        ob_end_clean();
        
        
        
        return $content;
    }
    private function page_PartsUsed() {
        
        //siden styles
        $this->Styles[] = "buttons.css";
        $this->Styles[] = "multiTable.css";
        $this->Styles[] = "page_PartsUsed.css";
        
        //class
        $multiTable = new MultiTable();
        $database = new Database();
        
        //størrelsen kategorier
        $sizeRam = array(array(32, 64, 0), array(16, 32, 0), array(8, 16, 0));
        $sizeHdd = array(array(1000, 2000, 0), array(500, 1000, 0), array(250, 500, 0), array(0, 250, 0));
        
        //hent data
        $osRows = $database->GetResults("SELECT * FROM `{$database->Prefix}operatingsystem`");
        $motherBoardRows = $database->GetResults("SELECT * FROM `{$database->Prefix}motherboard`");
        $cpuRows = $database->GetResults("SELECT * FROM `{$database->Prefix}cpu`");
        $comTypeRows = $database->GetResults("SELECT * FROM `{$database->Prefix}computertype`");
        $ramRows = $database->GetResults("SELECT * FROM `{$database->Prefix}ram`");
        $videoRows = $database->GetResults("SELECT * FROM `{$database->Prefix}video`");
        $audioRows = $database->GetResults("SELECT * FROM `{$database->Prefix}audio`");
        
                
        /**
         * laver en array med hvor mange computere
         * der bruger de enkeltdele
         * 
         * den sammenligner id fra en tabel med hvad
         * der står i computers tabellen
         */
        $CreateArrCom = function ($valueArr) use ($database){

            $arrayVal = array();
            
            foreach ($valueArr as $row) {
                $rowVal = array();
                
                //hent key navne så man kan bruge tal
                $key = array_keys($row);
                
                //antal computere
                $countRow = $database->GetResult("SELECT COUNT(*) FROM `{$database->Prefix}computers` WHERE {$key[0]} = {$row[$key[0]]};");
                
                //print_array($countRow);
                
                //hent navne
                for($i = 1; $i < count($row); $i++){
                    $rowVal[] = $row[$key[$i]];
                }
                
                //tilføj antal computere til sidst
                //(string) skal være der for ellers
                //kan multitable ikke udskrive 0
                $rowVal[] = (string)$countRow["COUNT(*)"];
            
                $arrayVal[] = $rowVal;
            }
            
            return $arrayVal;
        };
        
        /**
         * laver en array med hvor mange computere
         * der bruger de enkeltdele
         * 
         * forskellen på denne og $CreateArrCom
         * er at den ikke tæller flere computer 
         * hvis en computer har flere
         * af de samme dele
         */
        $CreateArrFromMultiVal = function ($valueArr, $table,$isRam = false) use ($database){
            
            $arrayVal = array();
            
            foreach ($valueArr as $row) {
                $rowVal = array();
                $comIdNoDub = array();
                
                //hent key navne så man kan bruge tal
                $key = array_keys($row);

                
                //hent computer ids
                $comIdRow = $database->GetResults("SELECT `com_id` FROM `$table` WHERE {$key[0]} = {$row[$key[0]]};");
                
                
                //tilføj id hvis det ikke find i forvejen        
                foreach($comIdRow as $comRow){
                    
                    if(!in_array($comRow, $comIdNoDub)){
                        $comIdNoDub[] = $comRow;
                    }
                }
                
                //hvormange gange det er blivet brugt
                //(string) skal være ellers kan
                //multitabl ike udskrive 0
                $countRow = (string)count($comIdNoDub);
                
                //hvis det skal stå på en linje
                if($isRam){
                    
                    $rowVal[] = "{$row[$key[1]]}, {$row[$key[2]]}, {$row[$key[4]]}";
                }
                else{
                    for($i = 1; $i < count($row); $i++){
                        $rowVal[] = $row[$key[$i]];
                    }
                }
                
                //tilføj antal computere til sidst
                $rowVal[] = $countRow;
                
                $arrayVal[] = $rowVal;
            }
            
            return $arrayVal;            
        };
        
        /**
         * laver en array med størrelsen kategori
         * og antal computere i denne
         */
        $CreateArrSizeVal = function ($valueArr, $sizeArr){
            
            
            if(!function_exists("SplitSize")){
                function SplitSize($val){
                    $numbStr = "1234567890.,";
                    $NumbersFound = false;
                    
                    $sizeStr = "";
                    $sizeType = "";
                    
                    for($i = 0; $i < strlen($val); $i++){
                        
                        $char = substr($val, $i, 1);
                        
                        if($char == " "){
                            if($NumbersFound){ break; }
                            else{ $NumbersFound = true; }
                        }
                        else if(!$NumbersFound && strpos($numbStr, $char) !== false){
                            $sizeStr .= $char;
                        }
                        else{
                            $sizeType .= $char;
                        }
                    }
                    
                    return array("size"=> intval($sizeStr), "type" => $sizeType);
                }
            }
            if(!function_exists("ConvertToByte")){
                function ConvertToByte($val, $type){                    
                    switch ($type) {
                        case "MB":
                            return $val * pow(1024,2);
                        case "GB":
                            return $val * pow(1024,3);
                        case "TB":
                            return $val  * pow(1024,4);

                        default:
                            return 0;
                    }
                    
                }
            }
            if(!function_exists("SortSize")){
                function SortSize(&$array){
                    
                    if(!function_exists("ArrayAppendToKey")){
                    function ArrayAppendToKey(&$array, $append){
                        $arrayApped = array();
                        foreach ($array as $key => $value) {
                            $arrayApped["$key $append"] = $value;
                        }
                        $array = $arrayApped;
                    }
                    }
                    
                    $size_TB = array();
                    $size_GB = array();
                    $size_MB = array();
                    
                    foreach ($array as $category => $value) {
                        $splidtCate = SplitSize($category);
                        
                        switch ($splidtCate["type"]) {
                            case "TB":
                                $size_TB[$splidtCate["size"]] = $value;
                                break;
                            case "GB":
                                $size_GB[$splidtCate["size"]] = $value;
                                break;
                            case "MB":
                                $size_MB[$splidtCate["size"]] = $value;
                                break;
                        }
                        
                        
                    }
                    
                    krsort($size_TB);
                    krsort($size_GB);
                    krsort($size_MB);
                    
                    ArrayAppendToKey($size_TB, "TB");
                    ArrayAppendToKey($size_GB, "GB");
                    ArrayAppendToKey($size_MB, "MB");
                    
                    
                    $array = array_merge($size_TB, $size_GB, $size_MB);                    
                }
            }
            
            
            //lav om til bytes (hvis der er flere bliver de lagt samme)
            $newSizeBytes = array();
            foreach ($valueArr as $value) {
                $comSize = SplitSize($value["size"]);
                $sizeInByte = ConvertToByte($comSize["size"], $comSize["type"]);
                if(array_key_exists($value["com_id"], $newSizeBytes)){                   
                    
                    $newSizeBytes[$value["com_id"]] += $sizeInByte;
                }
                else{
                    $newSizeBytes[$value["com_id"]] = $sizeInByte;
                }
            }
            
            //lav om til et mindre tal
            $newSizeConverted = array();
            foreach ($newSizeBytes as $key => $value) {
                $newSizeConverted[$key] = ByteToHighest($value);
            }
            
            //find ud af hvormange af de samme der er
            $newComInCategory = array();
            foreach ($newSizeConverted as $category) {
                if(!array_key_exists($category, $newComInCategory)){
                    $newComInCategory[$category] = 0;
                }
                $newComInCategory[$category]++;
            }
            
            SortSize($newComInCategory);
            
            //gør så det kan blive brugt
            $returnArray = array();
            foreach ($newComInCategory as $category => $value) {
                $returnArray[] = array($category, $value);
            }
            return $returnArray;
        };
        
        //hent ram størrelser
        $cmdRamSize = "SELECT `com_id`, {$database->Prefix}ram.`size` FROM `{$database->Prefix}computersram`";
        $cmdRamSize .= " INNER JOIN `{$database->Prefix}ram` ON";
        $cmdRamSize .= " {$database->Prefix}computersram.`ram_id` = {$database->Prefix}ram.`ram_id`;";
        $ramSizeRows = $database->GetResults($cmdRamSize);
        
        //hent harddisk størrelser
        $cmdHddSize = "SELECT `com_id`, `hddTotalSize` as `size` FROM `{$database->Prefix}computers`;";
        $hddSizeRows = $database->GetResults($cmdHddSize);

        //læg alle værdierne samme til en array
        $tableArr = array(
            "Computer Types" => $CreateArrCom($comTypeRows),
            "Operating System" => $CreateArrCom($osRows),
            "Motherboard" => $CreateArrCom($motherBoardRows),
            "Processor" => $CreateArrCom($cpuRows),
            "Ram Types" => $CreateArrFromMultiVal($ramRows, $database->Prefix."computersram", true),
            "Computer Ram Size" => $CreateArrSizeVal($ramSizeRows, $sizeRam),
            "Computer Hard Drive Size" => $CreateArrSizeVal($hddSizeRows, $sizeHdd),
            "Display" => $CreateArrFromMultiVal($videoRows, $database->Prefix."computersvideo"),
            "Multimedia" => $CreateArrFromMultiVal($audioRows, $database->Prefix."computersaudio")
        );
        
        //gør multi tabllen klar til at blive udskrevet
        foreach ($tableArr as $header => $row) {
            
            foreach ($row as $values) {
                for($i = 0; $i < count($values) -1; $i++){

                    $subheader = $values[$i];
                    
                    //hent den sidste værdi første gang som er antal computere
                    //eller skal den være tom
                    $value = $i == 0 ? $values[count($values) -1] : "&nbsp;";

                    //tilføj til tabellen
                    $multiTable->AddValue(0, $header, $subheader, $value);                
                }
                
            }
        }
        
        //hvor mange linjer der kan være i vær kategori
        //før den laver ny "mini kategori"
        $multiTable->SetLinesForSubheader("Computer Types", 1);
        $multiTable->SetLinesForSubheader("Operating System", 2);
        $multiTable->SetLinesForSubheader("Motherboard", 3);
        $multiTable->SetLinesForSubheader("Processor", 1);
        $multiTable->SetLinesForSubheader("Ram Types", 1);
        $multiTable->SetLinesForSubheader("Computer Ram Size", 1);
        $multiTable->SetLinesForSubheader("Computer Hard Drive Size", 1);
        $multiTable->SetLinesForSubheader("Display", 1);
        $multiTable->SetLinesForSubheader("Multimedia", 1);
        
        //lav overskift for antal computere
        $multiTable->AddValueDisplayedHeader(0, "Antal Computere");
        
        ob_start();
        
        ?>

<script>
$(function (){
    $(".multiTable .values ul").each(function (){
       
       var childCount = $(this).children("li").length;
       var lineHeight = 100 * childCount;
       var text = $(this).children("li").first().text();
       var textNew = "<span style='line-height: "+lineHeight+"%;'>"+text +"</span>&nbsp;";
       
       $(this).children("li").first().html(textNew);
    });
});
</script>


<?php
        
        //udskriv tabellen
        $multiTable->PrintTable();
 
        //send indholdet tilbage
        $content = ob_get_contents();
        ob_end_clean();
        
        return $content;
    }
    
    private function page_Delete(){
        
        $this->Styles[] = "buttons.css";
        
        ob_start();
        ?>
<style>
    .fieldsetBox{
        margin: 10px 0px 25px;
        padding: 20px 10px 10px;
        border: 2px solid gray;
        position: relative;
    }

    .fieldsetBox .title{
        margin: 0 -10px;
        position: absolute;
        top: -12px;
        width: 100%;
        text-align: center;
        color: gray;
    }
    .fieldsetBox .title span{
        
        
        background: white;
        padding: 4px 15px;
        border: 2px solid gray;
    }
    
    .computerDiv{
        
        min-width: 150px;
        max-width: 350px;
        border: 2px solid lightgray;
        border-radius: 5px;
        box-shadow: 3px 2px 6px;
        text-align: center;
        display: inline-block;
        margin: 8px 8px;
        padding: 10px 5px;
        white-space: nowrap;
    }
    .computerDiv .itemText{
        
        margin: 0 0 15px;
        text-overflow: ellipsis;
        width: 100%;
        overflow: hidden;
    }
    .comUseThis{
        
        text-align: left;
        font-size: 13px;
        color: gray;
        display: block;
        padding: 5px 0;
        text-decoration: underline;
        cursor: pointer;
    }
    .comUseThis:hover{
        color: #484848;
    }
    
    .betterListStyle{
        min-height: 75px;
        list-style: none;
        
        
        padding: 0;
        margin: 10px 50px;
    }
    
    .betterListStyle li:nth-child(odd){
        background: #e8e8e8;
    }
    .betterListStyle li:nth-child(even){
        background: #D4D4D4;
    }
    
    .betterListStyle li{
        position: relative;
        padding: 10px 5px 10px 32px;
        margin: 3px 0px;
        
        background: blue;
    }
    .betterListStyle li:before{
        content: '';
        position: absolute;
        
        top: 0px;
        left: 0px;
        border-top: 19px solid transparent;
        border-bottom: 19px solid transparent;
        border-left: 26px solid white;
        
    }
    .betterListStyle li:after{
        content: '';
        position: absolute;
        
        top: 3px;
        left: 0;
        border-top: 16px solid transparent;
        border-bottom: 16px solid transparent;
        border-left: 22px solid #B5B5B5;
        
    }
    .contentBox .info {
        margin: 10px 50px 0px;
        font-size: 17px;
        color: #464646;
    }
    @media only screen and (max-width: 550px) {
        .betterListStyle{
            margin: 10px 10%;
        }
        
    }
    
    
    @media only screen and (max-width: 360px) {
        
        .popup .contentBox .buttonControls button{
            display: block;
            margin: 5px auto;
            float: none !important;
        }
    }
</style>

<script>
    

    
    function makeForm(){
        var url = GetUrlNoParam(true);
        var form = Format("<form id='%v' action='%v' method='%v'>", "fdsf", url, "POST");
        form += "</form>";
        
        console.log(form);
    }
    
    
$(function (){
    $(".fieldsetBox .comUseThis").click(function(){
        makeForm();
        var comUseThis = $(this);
                
        if(comUseThis.text() === "Computere: 0"){
            return;
        }
        
        
        SetPopupComUseThis(comUseThis);
        
        $(".info").hide();
        $(".buttonConfirm").hide();
        $(".buttonView").css('display', 'block');

        PopUpShow();
    });
    
    
    $(".fieldsetBox .deleteItem").click(function(){
        
        var comUseThis = $(this).parents(".computerDiv").children(".comUseThis");
        
        if(comUseThis.length === 0){
            MakeFormSubmit("POST", {ajax : ""});
            return;
        }
        
        if(comUseThis.text() === "Computere: 0"){
            return;
        }
        
        SetPopupComUseThis(comUseThis);
        
        $(".buttonView").hide();
        $(".info").show();
        $(".buttonConfirm").show();

        PopUpShow();
    });
});

function SetPopupComUseThis(comUseThis){
    
    var table = comUseThis.data("table");
    var column = comUseThis.data("column");
    var index = comUseThis.data("index");
    
    var ajaxNames  = ajax_GetComNamesForItem(table, column, index);
    ajaxNames.success(function (jsonData){
        
        var jsonDataParse = $.parseJSON(jsonData);
        var ul = ".popup .comUseThisList";
        $(ul).html("");

        for(var i = 0; i < jsonDataParse.names.length; i++){
            $(ul).append("<li>"+jsonDataParse.names[i]+"</li>");
        }
    });
}
</script>
<?php
        
        $database = new Database();
        
        $infoComputerRows = $database->GetResults("SELECT `com_id`, `computerName` FROM `{$database->Prefix}computers`;");
        $infoComTyperRows = $database->GetResults("SELECT `comType_id`, `comTypeName` FROM `{$database->Prefix}computertype`;");
        $infoOsRows = $database->GetResults("SELECT `os_id`, `osName`, `servicePack` FROM `{$database->Prefix}operatingsystem`;");
        $infoMotherboardRows = $database->GetResults("SELECT `mother_id`, `motherName`, `chipset`, `bios` FROM `{$database->Prefix}motherboard`;");
        $infoCpuRows = $database->GetResults("SELECT `cpu_id`, `cpuName` FROM `{$database->Prefix}cpu`;");
        $infoRamRows = $database->GetResults("SELECT `ram_id`, `ramName`, `size`, `type` FROM `{$database->Prefix}ram`;");
        $infoVideoRows = $database->GetResults("SELECT `video_id`, `videoName` FROM `{$database->Prefix}video`;");
        $infoAudioRows = $database->GetResults("SELECT `audio_id`, `audioName` FROM `{$database->Prefix}audio`;");
        
        
        $headerAndVal = array(
            "Computere" => array($infoComputerRows, false),
            "Computer Typer" => array($infoComTyperRows, array("{$database->Prefix}computers", "comType_id")),
            "Operating Systems" => array($infoOsRows, array("{$database->Prefix}computers", "os_id")),
            "Motherboards" => array($infoMotherboardRows, array("{$database->Prefix}computers", "mother_id")),
            "Processere" => array($infoCpuRows, array("{$database->Prefix}computers", "cpu_id")),
            "Ram" => array($infoRamRows, array("{$database->Prefix}computersram", "ram_id")),
            "Video" => array($infoVideoRows, array("{$database->Prefix}computersvideo", "video_id")),
            "Audio" => array($infoAudioRows, array("{$database->Prefix}computersaudio", "audio_id"))
        );
        
        echo "<div class='popup hidden'>"
            . "<div class='contentBox'>"
                . "<h2 class='title'>Computere der bruger denne del</h2>"
                . "<p class='info'>"
                . "Hvis du fortsætter vil der ske ændre i disse computere."
                . "</p>"
                . "<hr style='margin: 10px 0 20px;'>"
                . "<ul class='betterListStyle comUseThisList'>"
                    . "<li>PC-SKILT1</li>"
                    . "<li>PC-SKILT2</li>"
                    . "<li>PC-SKILT3</li>"
                    . "<li>PC-SKILT2</li>"
                    . "<li>PC-SKILT3</li>"
                . "</ul>"
                . "<div class='buttonControls' style='text-align: left;'>"
                    . "<button class='button red buttonConfirm' onclick='PopUpHide();'>Annullere</button>"
                    . "<button class='button blue hidden buttonView'  onclick='PopUpHide();' style='margin: 0 auto;'>Luk vindue</button>"
                    . "<button class='button green buttonConfirm' style='float: right;'>Fortsæt</button>"
                . "</div>"
                . "</div>"
            . "</div>";
            
        foreach ($headerAndVal as $header => $values) {
            
            $rowVal = $values[0];
            $showCom = $values[1];
            $useThis = "";
            $echoDiv = "";
            
        
            $echoDiv .= "<div class='fieldsetBox'>"
                . "<h3 class='title'><span>$header</span></h3>";

            foreach ($rowVal as $value) {
                
                $echoItemDiv = "";
                $index = array_shift($value);
                $keys = array_keys($value);
                $string = implode("<br>", $value);
                
                
                $echoItemDiv .=  "<div class='computerDiv'>";
                $echoItemDiv .=   "<p class='itemText'>{$string}</p>";
                
                if($showCom !== false){
                    
                    if($index == 1) continue;
                    
                    $table = $showCom[0];
                    $column = $showCom[1];
                    
                    $useThis = $database->GetResults("SELECT `com_id` FROM `$table` WHERE `$column` = $index;");
                    $removeDub = array();
                    foreach ($useThis as $val) {
                        
                        if(!in_array($val["com_id"], $removeDub))
                        {
                            $removeDub[] = $val["com_id"];
                            //echo "in<br>";
                        }
                    }
                                        
                    $useThisNoDub = count($removeDub);
                    
                    $echoItemDiv .=  "<a class='comUseThis' data-table='$table' data-column='$column' data-index='$index'>Computere: {$useThisNoDub}</a>";
                }
                
                $echoItemDiv .=   "<button class='button red deleteItem' style='width: 100%;'>Slet</button>";
                $echoItemDiv .=   "</div>";
                $echoDiv .=  $echoItemDiv;
            }

            $echoDiv .=  "</div>";
            echo $echoDiv;
        }
        $content = ob_get_contents();
        ob_end_clean();
        
        return $content;
    }
    
    private function post_ComputerComapre_GetComputers(){        
        
        if(!function_exists("GetValuesFromKey")){
            function GetValuesFromKey($array, $key){
                $indexs = array();
            
                foreach ($array as $value) {
                    $indexs[] = $value[$key];
                }

                return $indexs;
            }
        }
              
        $hasAction =        isset($_POST["action"]) &&                  $_POST["action"] == "selectComs";
        $hasName =          isset($_POST["select_Name_Values"]) &&      is_array($_POST["select_Name_Values"]);
        $hasOs =            isset($_POST["select_OS_Values"]) &&        is_array($_POST["select_OS_Values"]);
        $hasMotherboard =   isset($_POST["select_Mother_Values"]) &&    is_array($_POST["select_Mother_Values"]);
        $hasProcessor =     isset($_POST["select_CPU_Values"]) &&       is_array($_POST["select_CPU_Values"]);
        $hasRam =           isset($_POST["select_Ram_Values"]) &&       is_array($_POST["select_Ram_Values"]);
        $hasDisplay =       isset($_POST["select_Video_Values"]) &&     is_array($_POST["select_Video_Values"]);
        $hasMultimedia =    isset($_POST["select_Audio_Selector"]) &&   is_array($_POST["select_Audio_Selector"]);
        
        $hasOnlyAction = !$hasName && !$hasOs && !$hasMotherboard && !$hasProcessor && !$hasRam && !$hasDisplay && !$hasMultimedia;
        
        if(!$hasAction || $hasOnlyAction) return array();
        
        
        $database = new Database();
        $computerIndexs = $database->GetResults("SELECT `com_id` FROM `{$database->Prefix}computers`;");
        $computerData = array();
        $computerDataConfirm = array();
        
        foreach ($computerIndexs as $index) {
            $computerData[] = new FileData($index["com_id"]);
        }
        
        foreach ($computerData as $computer) {
            
            $comName = $computer->FileData["computer"]["name"];
            $comOs = $computer->FileData["os"]["index"];
            $comMother = $computer->FileData["motherboard"]["index"];
            $comCpu = $computer->FileData["cpu"]["index"];            
            $comRam = GetValuesFromKey($computer->FileData["ram"], "ram_id");
            $comVideo = GetValuesFromKey($computer->FileData["video"], "video_id");
            $comAudio = GetValuesFromKey($computer->FileData["audio"], "audio_id");
            
            if($hasName && !in_array($comName, $_POST["select_Name_Values"])){
                continue;
            }
            if($hasOs && !in_array($comOs, $_POST["select_OS_Values"])){
                continue;
            }
            if($hasMotherboard && !in_array($comMother, $_POST["select_Mother_Values"])){
                continue;
            }
            if($hasProcessor && !in_array($comCpu, $_POST["select_CPU_Values"])){
                continue;
            }
            if($hasRam){
                $found = false;
                foreach ($comRam as $index) {
                    if(in_array($index, $_POST["select_Ram_Values"])){
                        $found = true;
                        break;
                    }
                }
                
                if(!$found){
                    continue;
                }
            }
            if($hasDisplay){
                $found = false;
                foreach ($comVideo as $index) {
                    if(in_array($index, $_POST["select_Video_Values"])){
                        $found = true;
                        break;
                    }
                }
                
                if(!$found){
                    continue;
                }
            }
            if($hasMultimedia){
                $found = false;
                foreach ($comAudio as $index) {
                    if(in_array($index, $_POST["select_Audio_Values"])){
                        $found = true;
                        break;
                    }
                }
                
                if(!$found){
                    continue;
                }
            }
            
            $computerDataConfirm[] = $computer;
        }
        
        return $computerDataConfirm;
    }
    
    
    
    private function ajax_GetComputere(){
        
        $table = $_POST["table"];
        $column = $_POST["column"];
        $index = intval($_POST["index"]);
        $database = new Database();
        
        $comRows = $database->GetResults("SELECT `com_id` FROM `$table` WHERE `$column` = $index;");
        $comIndexs = array();
        $comNames = array();
        
        foreach ($comRows as $com_id) {
            if(!in_array($com_id["com_id"], $comIndexs)){
                $comIndexs[] = $com_id["com_id"];
            }
        }
        
        foreach ($comIndexs as $com_id) {
            
            $nameRow = $database->GetResult("SELECT `computerName` FROM `{$database->Prefix}computers` WHERE `com_id` = $com_id;");
            $comNames[] = $nameRow["computerName"];
        }
        
        echo json_encode(array("names" => $comNames));
    }
    private function FileUpload(){
        ob_start();
        
        //array til at holde på info om filerne
        $information = array();
        
        //print_array($_FILES);
        if(isset($_FILES["files"]) && !empty($_FILES["files"])){
            if(is_array($_FILES["files"])){
                $file = $_FILES["files"];
                
                for($i = 0; $i < count($file["name"]); $i++){
                    
                    
                $name = $file["name"][$i];
                $nameTmp = $file["tmp_name"][$i];

                $filePathAndName = $this->Folder_upload.$name;

                //flyt filen til upload mappen
                $hasMoved = move_uploaded_file($nameTmp, $filePathAndName);

                //kunne ikke flyte filen
                if(!$hasMoved){
                    $information[] = array("filename" => $name, "message" => "Filen kunne ikke flytes til upload mappen.", "error" => true);
                    continue;
                }

                //prøv at loade til databasen
                $fileResult = new FileData($filePathAndName);

                //tjek om den blev uploade
                if($fileResult->Success){
                    $information[] = array("filename" => $name, "message" => "Filen er uploaded uden fejl.", "error" => false);
                }
                else if($fileResult->Timeout)  {
                    $information[] = array("filename" => $name, "message" => "Filen tog for langt tid at loade.", "error" => true);
                }
                else if($fileResult->Exits) {
                    $information[] = array("filename" => $name, "message" => "Filen findes i forvejen.", "error" => true);
                }
                else  {
                    $information[] = array("filename" => $name, "message" => "Filen kunne ikke uploade til databasen.", "error" => true);
                }
            }
            }
        }
        
        //fjern alt det der er skrevet ud
        //f.eks fejl bekseder og echo
        ob_end_clean();
        
        //udskriv som json så javascript og jquery kan læse det
        echo json_encode($information);
        
    }
    private function ComparePcRemove(){
        $hasData = isset($_POST["pc"]) && !empty($_POST["pc"]);
        
        if(!$hasData){ return false;}
        
        
        session_start();
        
        $indexRemove = intval($_POST["pc"]);
        $indexSession = $_SESSION["comparePc"];
        
        if(!is_array($indexSession)){return false;}
        
        if(in_array("all", $indexSession)){
            
            $newSession = array();
            
            $database = new Database();
            $computerRows = $database->GetResults("SELECT `com_id` FROM `{$database->Prefix}computers`;");
            
            foreach ($computerRows as $row) {
                
                $index = $row["com_id"];
                if($indexRemove != $index){
                    $newSession[] = $index;
                }
            }
            
            $_SESSION["comparePc"] = $newSession;
        }
        else{
            
            $_SESSION["comparePc"] = array_removeVal($indexSession, $indexRemove);
        }
    }
    
    public function ExecutePage($content) {
        
        //add styles
        $this->Styles[] = "navigation.css";
        $this->Styles[] = "page_Default.css";
        //add scripts
        $this->Scripts[] = "navigation.js";
        $this->Scripts[] = "page_Default.js";
        
        ?>
<!DOCTYPE html>
<!--
        ######################################
        ##                                  ##
        ##        Siden er kode af          ##
        ##          Simon Skov              ##
        ##                                  ##
        ##     SKP TEC DATA BALLERUP        ##
        ##                                  ##
        ######################################

-->
<html>
    <head>
        <title>SKP DATA - Inventar Database</title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta charset="utf-8"/>
                
        <?php
            foreach ($this->Styles as $name) {
                echo "<link rel='stylesheet' href='Style/$name'>";
            }
        ?>
        
        
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.2/jquery.min.js"></script>
        <link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/themes/smoothness/jquery-ui.css">
        <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/jquery-ui.min.js"></script>
         <?php
            foreach ($this->Scripts as $name) {
                echo "<script src='scripts/$name'></script>";
            }
        ?>

    </head>
    <body>
        <div class="top">
            <div class="inner">
                <a href="#" class="logo">Inventar Database</a>
                <div class="showMenu">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>

                <ul class="navigation">
                    <li><a href="?page=addnew" >Tilføj Computere</a></li>
                    <li><a href="?page=compare">Vis Computere</a></li>
                    <li><a href="?page=partsused">Hvad bliver brugt</a></li>
                    <li><a href="?page=delete">Slet Computere</a></li>
                </ul>
                <div id="output"></div>
            </div>
        </div>
        <div class="middle">
            <div class="content"><?php echo $content; ?></div>
        </div>
        
        <div class="bottomInfo">
            <p unselectable="on" onselectstart="return false;" onmousedown="return false;">SKP DATA</p>
        </div>
    </body>
</html>    
        <?php
        
    }
}


class ElementHTML{
    private $isDeleted = false;
    public $parentIndex = -1;
    private $index = 0;
    private $parent;
    public $parentClass = array();
    public $content = array();
    private $text = "";
    
    public function SetAttr($attr, $val){
        
        
        if(!array_key_exists($attr, $this->parentClass))
            $this->parentClass[$attr] = array();
        
        $this->parentClass[$attr][] = $val;
    }
    public function GetAttr($attr){
        
        echo "Get $attr <br>";
        print_array($this->parentClass);
        //print_array($this->TextOnly());
        if(!array_key_exists($attr, $this->parentClass)) return "";
        
        $attrString = "$attr=\"";
        foreach ($this->parentClass[$attr] as $value) {
            $attrString .= $value ." ";
        }
        $attrString = substr($attrString, 0, strlen($attrString) -1);
        $attrString .= "\"";
        
        print_array($this->parentClass);
        return $attrString;
    }
    
    public function __construct($html, &$parent = null, $parentId = -1) {
        
        if($parent != null){      
            //if($this->index == 2) return;        
        }
        
        //echo "<center style='font-size: 30px; font-weight:bold;'>### START ###</center>";
        $this->parent = &$parent;
        $this->parentIndex = $parentId;
        
        
        $hasHtmlTag = $this->GetTagName(0, $html) !== false ? true : false;
        if($hasHtmlTag) $this->ReadString($html);
        else $this->text =  str_replace("&nbsp;", '', $html);
        
    }
    
    public function PrintParantContentArray() {
        print_array($this->parent->content);
    }
    
    public function GetContentArray(){
        
        $contentArr = array();
        
        foreach ($this->content as $content) {
            $htmlTag = $content["tag"];
            $htmlElement = $content["element"];
                        
            if($htmlElement->isDeleted){
                
                $contentArr = array_merge($contentArr, $htmlElement->GetContentArray());
            }
            else{
                $contentArr[] = $content;
            }
        }
        
        return $contentArr;
    }
    
    public function &MoveUp(){
        
        $hasParent = $this->HasParent();
        
        if(!$hasParent) 
        {return $this;}
        
        
        $lastElement = array();
        
        
        foreach ($this->parent->content as $key => $value) {
            
            $isFirst = count($lastElement) == 0;
            
            if($this->parentIndex == $key && !$isFirst){
                
                $oldKey = $value["element"]->parentIndex;
                $newKey = $lastElement["element"]["element"]->parentIndex;
                
                $value["element"]->parentIndex = $newKey;
                $lastElement["element"]["element"]->parentIndex = $oldKey;
                
                $this->parent->content[$newKey] = $value;
                $this->parent->content[$oldKey] = $lastElement["element"];
                
                break;
            }
            
            $lastElement["key"] = $key;
            $lastElement["element"] = $value;
        }
        
        return $this;
    }
    
    public function &Prev(){
        
        $hasParent = $this->HasParent();
        
        if(!$hasParent) 
        {return $this;}
        
        $lastElement = array();
        
        
        foreach ($this->parent->content as $key => $value) {
            
            $isFirst = count($lastElement) == 0;
            
            if($this->parentIndex == $key){
                
                if($isFirst) return $this;
                else return $lastElement["element"]["element"];
                
                break;
            }
            
            $lastElement["key"] = $key;
            $lastElement["element"] = $value;
        }
    }
    
    
    public function RemoveEmptyAll(){
                
        foreach ($this->content as $content) {
            
            $htmlTag = $content["tag"];
            $htmlElement = $content["element"];
        
            
            $htmlElement->RemoveEmptyAll();
        }
        $this->RemoveEmpty();
    }
    
    public function RemoveEmpty(){
        
        foreach ($this->content as $content) {
            
            $htmlTag = $content["tag"];
            $htmlElement = $content["element"];
            
            $hasChilds = count($htmlElement->content) != 0;
            $hasText = $htmlElement->text != "";
            
            if(!$hasChilds && !$hasText) $htmlElement->Remove();
        }
    }
    
    public function Text(){
        $text = "";
        $hasHtmlTags = count($this->content) != 0;
        $parentTag = $this->GetParentTag();
        
        if($parentTag != "" && !$this->isDeleted){
            //$text .= "# S P #";
            
            //if(array_key_exists("href", $this->parentClass))print_array($this->parentClass);
            
            $attrString = "";
            
            foreach ($this->parentClass as $attr => $values) {
                $attrString .= " $attr=\"";
                foreach ($values as $value) {
                    $attrString .= "$value ";
                }
                $attrString .= "\"";
            }
            
            $text .= "<$parentTag{$attrString}>";
        }
        
        if ($hasHtmlTags) {
            foreach ($this->content as $content) {
                
                $htmlTag = $content["tag"];
                $htmlElement = $content["element"];
                
                //$text .= "  ## $htmlTag -> ";
                //$text .= "<$htmlTag>";
                $text .= $htmlElement->Text();
                //$text .= "</$htmlTag>";             
                
            }
        }
        else{
            $text .= $this->text;
        }
        
        if($parentTag != "" && !$this->isDeleted){
            $text .= "</$parentTag>";
            //$text .= "# E P #";
        }
        return $text;
    }
    
    public function TextOnly(){
       $text = array();
       
       
        if ($this->text == "") {
            
            foreach ($this->content as $content) {
                
                $htmlTag = $content["tag"];
                $htmlElement = $content["element"];
                $text = array_merge($text, $htmlElement->TextOnly());
                
            }
        }
        else{
            $text[] = $this->text;
        }
        
        return $text; 
    }
    
    public function &Find($tag, $offset = 0, &$loop = 0) {
        
        $returnVal = false;
        $returnVal2 = &$returnVal;
        
        foreach ($this->content as &$content) {
            
            $htmlTag = $content["tag"];
            $htmlElement = &$content["element"];
        
            if(!$htmlElement->isDeleted){
                
                if($tag == $htmlTag){
                   // echo "loop->$loop<br>";
                    if($offset == $loop) return $htmlElement;
                    $loop++;
                }            
            }
            else{
                
                $found = &$htmlElement->Find($tag, $offset, $loop);
                if($found !== false) return $found;
            }
        }
        return $returnVal2;
    }
    
    public function Count($tag){
        
        $count = 0;
        
        while ($this->Find($tag, $count) !== false){ 
            $count++;
        }
        
        return $count;
    }
    
    public function &FindLast($tag){
        
        $lastId = $this->Count($tag) -1;
        $child = &$this->Find($tag, $lastId);
        
        return $child;
    }
    
    public function Each($tag, $callback){
        
        global $saveData;
        $saveData = array();
        $loop = 0;
        foreach ($this->content as $content) {

            $htmlTag = $content["tag"];
            $htmlElement = $content["element"];
            
            if($htmlElement->isDeleted){
                $htmlElement->Each($tag, $callback);
            }
            else{

                if($htmlTag == $tag){
                    call_user_func($callback, $htmlElement, $loop);
                    $loop++;
                }
            }
        }
    }
    
    public function Remove($keepChild = false){
        
        $hasParent = $this->parent != null;
        
        if($hasParent){
            
            
            $this->isDeleted = true;
            if(!$keepChild){
                unset($this->parent->content[$this->parentIndex]);
            }
            
            //print_array($this->parent->content);
            //unset($this);
            //$key = array_search($this, $this->parent->content);
            
            //echo '<br><br><br>';
            
           // print_array($this->parent->content);
            //array_removeVal($this->parent->content, $this->parent->content[$key]);
            //array_pop($this->parent->content);
//            foreach ($this->parent->content as $value) {
//                $value["tag"] .= "Hallo";
//            }
        }
    }
    
    
    private function HasParent(){
        
        $hasParentSet = $this->parentIndex != -1;
        if($hasParentSet) return true;
            
        return false;
    }
    
    private function GetParentTag(){
        
        $hasParent = $this->HasParent();
        $tag = "";
        if($hasParent){
            $tag .= $this->parent->content[$this->parentIndex]["tag"];
            //$tag .= $htmlTag;
        }
        
        return $tag;
    }
    
    private function GetAttrNames($startPos, $endPos, $subject){        
        $tagOpen = false;
        
        $tagText = "";
        $tagAttr = "";
        $attrFound = false;
        $tagFound = false;
        $isInsideAttr = false;
        $isInString = false;
        $values = array();
        
        for($i = $startPos; $i < $endPos; $i++){
            $sChar = substr($subject, $i, 1);
             
            
            if($tagOpen && $tagFound){  
                if(!$isInsideAttr){
                    switch ($sChar) {
                        case "=":
                        case " ":
                        case "\"":
                        case "'":
                            $tagAttr = $tagText;
                            break; 

                    }   
                }
                
                if($tagAttr != ""){

                    $tagAttrLower =  strtolower($tagAttr);
                    $values[] = $tagAttrLower;
                    $tagAttr = "";
                }
                
                switch ($sChar) {
                    case "<":
                    case ">":
                    case "/":
                    case "=":
                    case ":":
                    case ";":
                    case "\"":
                    case "'":
                    case " ":
                        $tagText = "";
                        
                        if($sChar == "\"" || $sChar == "'"){
                            
                            $isInString = !$isInString;
                            $i = strpos($subject, $sChar, $i +1);
                            continue;
                        }
                        
                        $isInsideAttr = !$isInsideAttr;
                        if($isInsideAttr) $attrFound = false;
                        
                        break;
                    default:
                        $tagText .= $sChar;
                        break;
                } 
                
                
            }
            
            
            
            if ($sChar == " " && $tagOpen) {
                $tagFound = true;
            }
            
            if($sChar == "<"){
                $tagOpen = true;
            }
        }
        
        return $values;
    }
    
    private function GetAttrVal($startPos, $endPos, $subject, $attr){
        
        $textLength = $this->GetLenghtByPositions($startPos, $endPos);
        $textSubject = substr($subject, $startPos, $textLength);
        $textSubjectLow = strtolower($textSubject);
        
        $attrToLower = strtolower($attr);
        $attrPos = strpos($textSubjectLow, $attrToLower ."=");
        if($attrPos === false) return array();
        
        $attrPos += strlen($attrToLower) + 1;
        $attrEnd = strlen($textSubject);
        $useBrackets = substr($textSubject, $attrPos, 1) == "\"";
        
        if($useBrackets){
            $attrPos += 1;
            $attrEnd = strpos($textSubject, "\"", $attrPos);
        }
        else{
            $attrEndCloser = array(" ", "/", ">");
            
            foreach ($attrEndCloser as $value) {
                
                $endClosePos = strpos($textSubject, $value, $attrPos);
                if($endClosePos === false) continue;
                
                
                if($endClosePos < $attrEnd){
                    $attrEnd = $endClosePos;
                }
            }            
        }
        
        $attrLength = $this->GetLenghtByPositions($attrPos, $attrEnd);
        $attrText = substr($textSubject, $attrPos, $attrLength);
        
        switch ($attrToLower) {
            case "style":

                
                $attrSplit = explode(";", $attrText);
                $attrSplit = array_removeVal($attrSplit, "");
                
                foreach ($attrSplit as &$value) {
                    $value .= ";";
                }
                break;

            default:
                $attrSplit = explode(" ", $attrText);
                break;
        }
        
        return $attrSplit;
    }
    
    private function GetLenghtByPositions($start, $end){
        $length = $end - $start;
        
        return $length;
    }
    
    private function GetTagPositions($startPos, $subject, &$start, &$end) {
        
        $tagOpen = false;
        
        $start = -1;
        $end = -1;
        
        for($i = $startPos; $i < strlen($subject); $i++){
            $sChar = substr($subject, $i, 1);
                    
            if($sChar == "<"){
                $start = $i;
                $tagOpen = true;
            }
            else if($sChar == ">" && $tagOpen){
                $end = $i +1;
                break;
            }
        }
        if($end == -1){
            return false;
        }
        else{
            return true;
        }
    }

    private function GetTagEndPositions($tagStartPos,$tagEndPos, $subject, &$start, &$end){
        
        
        $start = -1;
        $end = -1;
        
        $singleTag = array("br", "hr");
        $canEnd = array("td"=>array("tr"));
        $positionStart = 0;
        $positionEnd = 0;
        
        
        $tagTextLen = $this->GetLenghtByPositions($tagStartPos, $tagEndPos);
        $tagText = substr($subject, $tagStartPos, $tagTextLen);
        $tagName = $this->GetTagName($tagStartPos, $subject);
        $nextTagStartPos = strpos($subject, "<", $tagEndPos);
        //if(substr($tagText, $tagTextLen -2,1) == "/")
        if(in_array($tagName, $singleTag) || substr($tagText, $tagTextLen -2,1) == "/"){
            $start = $tagEndPos;
            $end = -1;
            return ;
        }
        
        $this->GetTagPositionsOfType($tagEndPos, $subject, $positionStart, $positionEnd, $tagName);
        
        if(key_exists($tagName, $canEnd) && $tagEndPos != $positionStart){
            
            
            foreach ($canEnd[$tagName] as $value) {
                $canEndPositionStart = -1;
                $canEndPositionEnd = -1;
                
                $this->GetTagPositionsOfType($tagEndPos, $subject, $canEndPositionStart, $canEndPositionEnd, $value);
                if($positionStart > $canEndPositionStart && $canEndPositionStart != -1){
                    $positionStart = $canEndPositionStart;
                    $positionEnd = $canEndPositionStart;
                }
            }            
        }
        
        if($positionStart > 0){
            
            $foundCloser = substr($subject, $positionStart +1, 1) == "/";
            
            if(!$foundCloser) $positionEnd = $positionStart;
        }
        
        if(in_array($tagName, $singleTag)){
            $positionStart = $tagEndPos;
            $positionEnd = -1;
        }
        
        
        if($positionEnd == -1){
            
            $positionStart = strlen($subject);
            //$positionEnd = strlen($subject);
        }
        
        $start = $positionStart;
        $end = $positionEnd;
    }

    private function GetTagPositionsOfType($startPos, $subject, &$start, &$end, $type) {
        
        $start -1;
        $end = -1;
        $nextIndex = $startPos;
        $positionStart = 0;
        $positionEnd = 0;
        $loopForCloser = substr($type, 0,1) == "/";
        
        
        $loop = 0;
        
        while ($nextIndex != -1) {
            $this->GetTagPositions($nextIndex, $subject, $positionStart, $positionEnd);            
            $tagname = $this->GetTagName($positionStart, $subject, !$loopForCloser);        
            $nextIndex = $positionEnd;
            
            //echo "$loop --> $tagname == $type<br>";
            if($tagname == $type){
                $start = $positionStart;
                $end = $positionEnd;
                break;
            }        
            
            //if($loop == 80) break;
            $loop++;
        }        
    }
    private function GetTagName($startPos, $subject, $removeCloser = false){
        
        $tagOpen = false;
        $name = "";
        for($i = $startPos; $i < strlen($subject); $i++){
            $sChar = substr($subject, $i, 1);
            
            if($tagOpen){            
                switch ($sChar) {
                    case ">":
                    case "/":
                    case " ":
                        if($name == ""){
                            if(!$removeCloser){
                                $name .= $sChar;
                            }
                            break;
                            
                        }
                        break 2;

                    default:
                        $name .= $sChar;
                        break;
                }
            }
            
            if($sChar == "<"){
                $tagOpen = true;
            }
        }
        $nameLowerCase = strtolower($name);
        
        $nameTrim = trim($nameLowerCase);
        
        if(strlen($nameTrim) == 0) return false;
        else return $nameTrim;
    }
    
    private function ReadString($htmlString){
        
        $htmlString = trim($htmlString);
        
        $startPositionStart = 0;
        $startPositionEnd = 0;
        $endPositionStart = 0;
        $endPositionEnd = 0;
        
        $loop = 0;
        $parentIndex = 0;
        while (true){
            $hasMore = $this->GetTagPositions($endPositionEnd, $htmlString, $startPositionStart, $startPositionEnd);
            
            if(!$hasMore) break;
            
            $this->GetTagEndPositions($startPositionStart, $startPositionEnd, $htmlString, $endPositionStart, $endPositionEnd);

              
           
            $lenghtOfStart = $this->GetLenghtByPositions($startPositionStart, $startPositionEnd);
            $lenghtOfMiddle = $this->GetLenghtByPositions($startPositionEnd, $endPositionStart);
            $lenghtOfEnd = 0;
            if($endPositionEnd != -1 ) $lenghtOfEnd = $this->GetLenghtByPositions($endPositionStart, $endPositionEnd);
            
            
            $textStart = substr($htmlString, $startPositionStart, $lenghtOfStart);
            $textMiddle = substr($htmlString, $startPositionEnd, $lenghtOfMiddle);
            $textEnd = substr($htmlString, $endPositionStart, $lenghtOfEnd);
            
            
            $tagName = $this->GetTagName($startPositionStart, $htmlString);
            //echo $this->name ."-->$tagName  -- $textMiddle<br>";
            
            if($tagName == "!doctype" && false){
                echo "<table style='width: 90%; margin: 0px auto 20px; border: 1px solid;'>";
                echo "<tr><th colspan='3' style='font-size: 20px;'>($this->name) - ReadString --> $loop";
                echo "<tr style='font-size: 16px;'>"
                    . "<th style='border: 1px solid;'>Start ($startPositionStart - $startPositionEnd - $lenghtOfStart)"
                    . "<th style='border: 1px solid;'>Middle ($startPositionEnd - $endPositionStart - $lenghtOfMiddle)"
                    . "<th style='border: 1px solid;'>End ($endPositionStart - $endPositionEnd - $lenghtOfEnd)";
                echo "<tr style='text-align: left; vertical-align: top !important;'>";
                echo "<td style='border: 1px solid; padding: 15px; 10px'>"; print_html($textStart);
                echo "<td style='border: 1px solid; padding: 15px; 10px''>"; print_html($textMiddle);
                echo "<td style='border: 1px solid; padding: 15px; 10px''>"; print_html($textEnd);
                echo "</table>";
            }
            
            if($tagName == "!doctype"){
                $this->ReadString($textMiddle);
                break;
            }
            
            $hasHtmlTag = $this->GetTagName(0, $textMiddle) !== false ? true : false;
            
            $childContent = new ElementHTML($textMiddle, $this, $parentIndex);
            
            $attrNames = $this->GetAttrNames(0, strlen($textStart), $textStart);
            
            foreach ($attrNames as $attr) {
                
                $attVal = $this->GetAttrVal(0, strlen($textStart), $textStart, $attr);
                $childContent->parentClass[$attr] = $attVal;
                //$this->class[$parentIndex][$attr] = $attVal;
            }
            $this->content[] = array("tag" => $tagName, "element" => $childContent);
            $parentIndex++;
            
            //if($loop == 150) break;
            
            $loop++;                        
            if($endPositionEnd == -1 ) $endPositionEnd = $endPositionStart;
        }
        
        //echo "<center style='font-size: 30px; font-weight:bold;'>### END ###</center>";
    }
}

$website = new website();
//$website->UPDATESQL();
//$website->REINSTALLSQL();