<?php
//header('Content-Type:text/plain');
error_reporting(E_ALL);
ini_set('display_errors', 1);

include_once 'Inc/Database.php';
include_once 'config.php';



class FileData{
    
    
    private $Filename = "";
    public $FileData;
    
    private function CreateDb(){
        $database = new Database();
        
        $database->Create($database->Prefix."operatingSystem", 
            array(
                array("os_id","int", "not null", "AUTO_INCREMENT"),
                array("osName","varchar(64)", "not null"),
                array("servicePack","varchar(32)", "not null")
            ), 
            "os_id"
        );
        $database->Create($database->Prefix."cpu", 
            array(
                array("cpu_id","int", "not null", "AUTO_INCREMENT"),
                array("cpuName","varchar(64)", "not null"),
            ), 
            "cpu_id"
        );
        $database->Create($database->Prefix."computerType", 
            array(
                array("comType_id","int", "not null", "AUTO_INCREMENT"),
                array("comTypeName","varchar(32)", "not null"),
            ), 
            "comType_id"
        );
        
        $database->Create($database->Prefix."motherboard", 
            array(
                array("mother_id","int", "not null", "AUTO_INCREMENT"),
                array("motherName","varchar(64)", "not null"),
                array("chipset","varchar(64)", "not null"),
                array("bios","varchar(32)", "not null"),
            ), 
            "mother_id"
        );
        
        $database->Create($database->Prefix."computers", 
            array(
                array("com_id","int", "not null", "AUTO_INCREMENT"),
                array("computerName","varchar(64)", "not null"),
                array("comType_id","int", "not null"),
                array("os_id","int", "not null"),
                array("cpu_id","int", "not null"),
                array("mother_id","int", "not null"),
                array("ipAddress","varchar(16)", "not null"),
                array("macAddress","varchar(18)", "not null"),
                array("hddTotalSize","varchar(32)", "not null"),
                array("date_time","varchar(20)", "not null")
            ), 
            "com_id", 
            array(
                "os_id"=>"{$database->Prefix}operatingSystem(os_id)",
                "cpu_id"=>"{$database->Prefix}cpu(cpu_id)",
                "comType_id"=>"{$database->Prefix}computerType(comType_id)",
                "mother_id"=>"{$database->Prefix}motherboard(mother_id)"
            )
        );
        $database->Create($database->Prefix."ram", 
            array(
                array("ram_id","int", "not null", "AUTO_INCREMENT"),
                array("ramName","varchar(64)", "not null"),
                array("size","varchar(12)", "not null"),
                array("fFactor","varchar(12)", "not null"),
                array("type","varchar(12)", "not null")
            ), 
            "ram_id"
        );
        $database->Create($database->Prefix."computersRam", 
            array(
                array("comR_ram","int", "not null", "AUTO_INCREMENT"),
                array("ram_id","int", "not null"),
                array("com_id","int", "not null")
            ), 
            "comR_ram", 
            array(
                "ram_id"=>"{$database->Prefix}ram(ram_id)",
                "com_id"=>"{$database->Prefix}computers(com_id)"
            )
        );
                
        
        $database->Create($database->Prefix."video", 
            array(
                array("video_id","int", "not null", "AUTO_INCREMENT"),
                array("videoName","varchar(64)", "not null")
            ), 
            "video_id"
        );
        $database->Create($database->Prefix."computersVideo", 
            array(
                array("comVid_id","int", "not null", "AUTO_INCREMENT"),
                array("video_id","int", "not null"),
                array("com_id","int", "not null")
            ), 
            "comVid_id", 
            array(
                "video_id"=>"{$database->Prefix}video(video_id)",
                "com_id"=>"{$database->Prefix}computers(com_id)"
            )
        );
                
        $database->Create($database->Prefix."audio", 
            array(
                array("audio_id","int", "not null", "AUTO_INCREMENT"),
                array("audioName","varchar(128)", "not null")
            ), 
            "audio_id"
        );
        $database->Create($database->Prefix."computersAudio", 
            array(
                array("comAud_id","int", "not null", "AUTO_INCREMENT"),
                array("audio_id","int", "not null"),
                array("com_id","int", "not null")
            ), 
            "comAud_id", 
            array(
                "audio_id"=>"{$database->Prefix}audio(audio_id)",
                "com_id"=>"{$database->Prefix}computers(com_id)"
            )
        );
    }

    
    public function __construct($param) {
        
        if(is_string($param)){
            $this->Filename = $param;
        }
        else if(is_int($param)){
            $this->FileData = $this->GetDataFormSQL($param);
        }
        else{
            return;
        }
        
        
        
        
        //$this->CreateDb();
        //$this->UploadToSQL();
        
    }
    
    /**
     * 
     */
    private function PrintData(){
        $dataInventar = $this->GetData();
        
        foreach ($dataInventar as $keyTitle => $valueHeader) {
            
            echo "<ul><li>{$keyTitle}</li><li><ul>";
            
            foreach ($valueHeader as $keyHeader => $valueContent) {
                
                $hasEkstraTitle = substr($keyHeader, 0,1) == "[";
                
                echo "<li>{$keyHeader}</li><li><ul>";
                
                foreach ($valueContent as $key => $value) {
                    
                    if($hasEkstraTitle){
                        echo "<li>{$key}</li><li><ul>";
                        foreach ($value as $keyEkstra => $valueEkstra) {
                            
                            if(is_array($valueEkstra)){
                                foreach ($valueEkstra as $valueArray) {
                                    echo "<li>{$keyEkstra} --> {$valueArray}</li>";
                                }
                            }
                            else{
                            
                                echo "<li>{$keyEkstra} --> {$valueEkstra}</li>";
                            }
                        }
                        echo "</ul></li>";                        
                    }
                    else{
                        
                    if(is_array($value)){
                                foreach ($value as $valueArray) {
                                    echo "<li>{$key} --> {$valueArray}</li>";
                                }
                            }
                            else{
                            
                                echo "<li>{$key} --> {$value}</li>";
                            }
                    }
                }
                
                echo "</ul></li>";
            }
            
            echo "</li></ul></ul>";
        }
    }
    
    /**
     * 
     * @return array
     */
    private function GetData() {
        //
        $dataInventar = array(
            "Summary" => array(
                "Computer:" => array(
                    "Computer Type" => "",
                    "Operating System" => "",
                    "OS Service Pack" => "",
                    "Computer Name" => "",
                    "Date / Time" => ""
                ),
                
                "Motherboard:" => array(
                    "CPU Type" => "",
                    "Motherboard Name" => "",
                    "Motherboard Chipset" => "",
                    "BIOS Type" => "",
                    
                ),
                "Partitions:" => array(
                    "Total Size" => ""
                ),
                "Network:" => array(
                    "Primary IP Address" => "",
                    "Primary MAC Address" => ""
                ),
                "Display:" => array(
                    "Video Adapter" => array()
                ),
                "Multimedia:" => array(
                    "Audio Adapter" => array()
                )
            ),
            "DMI" => array(   
                "[ Memory Devices / ChannelA-" => array(
                    "Memory Device Properties:" => array(
                        "Form Factor" => array(),
                        "Type" => array(),
                        "Size" => array(),
                        "Manufacturer" => array()
                    )
                )
            )
        );
        
        //hent fil som string
        $fileContent = file_get_contents($this->Filename);
       
        
        //til at tjekke om de er fundet
        $isFoundBody = false;
        $isFoundTitle = false;
        
        $useEsktraHeader = false; //om der bliver brugt ekstra header
        $changerKeyValue = false; //skift i mellem key og value
        
        $fileSelected = "";

        //tekst til at sammenligne
        $compareBody = strtolower("<body bgcolor=\"#FFFFFF\">");
        $compareTitle = strtolower("<td class=pt>");
        
        //nulstil/opret værdier til at holde
        //oplyninger til array
        $currentTitle = "";
        $currentHeaderEkstra = "";
        $currentHeader = "";
        $currentKey = "";
        
        //begyndt at læst filen igennem og 
        //dem de værdier den finder
        for($i = 0; $i < strlen($fileContent); $i++){
            
            //hent sidste tegn fra sidste runde
            $charPrevLast = substr($fileSelected, strlen($fileSelected) -1);
            
            //nulstil selected text da der
            // bliver på begyndt på en ny element
            if($charPrevLast == ">"){
                $fileSelected = "";
            }
            
            //hent sidte tegn og tilføj det til selected text
            $charLast = strtolower(substr($fileContent, $i, 1)); 
            $fileSelected .= $charLast;
            $charFirst = substr($fileSelected, 0, 1);
            
            //tjek om <body> er foundet
            if (strpos($fileSelected, $compareBody) !== false && !$isFoundBody){
                $isFoundBody = true;
            }
            //tjek om det er en ny title der er fundet
            else if(strpos($fileSelected, $compareTitle) !== false ){
                
                $currentTitle = ""; //nulstil title
                $isFoundTitle = true; //gør så man ikke kigger i menuen/navigation
                $useEsktraHeader = false; // nulstil ekstra header                
            }
            
            
            if(!$isFoundBody || !$isFoundTitle || 
                $charFirst == "<" || $charLast != "<"){
                continue;
            }
            
            //fjern html inputs
            //og mellerum
            $textLength = strlen($fileSelected) -1;
            $textInputHtml = substr($fileContent, $i -$textLength, $textLength);
            $textInputNoHtml = preg_replace("/&#?[a-z0-9]{2,8};/i", "", $textInputHtml);
            $textInput = trim($textInputNoHtml);

            //hvis den ikke indeholder noget stop
            if($textInput == ""){
                continue;
            }

            //find title
            if($currentTitle == ""){                       
                if(array_key_exists($textInput, $dataInventar)){

                    $currentTitle = $textInput; //sæt title key

                    //tjek om der bliver brugt 
                    //ekstra header ved at tjekke
                    //den første key
                    $useEsktraHeader = substr(key($dataInventar[$currentTitle]), 0, 1) == "[";
                }

                //nulstil key values
                $changerKeyValue = false;
                $currentKey = "";

            }

            //tjek om det er en ekstra header
            else if(substr($textInput, 0,1) == "["){

                //stop hvis man ikke skal bruge
                //ekstra header
                if(!$useEsktraHeader){
                    continue;
                }

                //find ud af om $textInput er den 
                //ekstra header der skal bruges
                foreach ($dataInventar[$currentTitle] as $key => $value) {
                    if (strpos($textInput, $key) !== false) {
                        //gem $key da $textInput kan indeholde mere
                        $currentHeaderEkstra = $key;
                        break;
                    }
                    else{
                        $currentHeaderEkstra = "";
                    }
                } 

                //nulstil key values
                $changerKeyValue = false;
                $currentKey = "";
            }

            //tjek om det er en header
            else if(substr($textInput, strlen($textInput) -1) == ":"){

                $keyHeaderExists = $useEsktraHeader ? 
                    $currentHeaderEkstra != "" && array_key_exists($textInput, $dataInventar[$currentTitle][$currentHeaderEkstra]) : 
                    array_key_exists($textInput, $dataInventar[$currentTitle]);

                //sæt header
                $currentHeader = $keyHeaderExists ? $textInput : "";

                //nulstil key values
                $changerKeyValue = false;
                $currentKey = "";
            }
            else if($currentHeader != ""){

                //skift i mellem key og value
                $changerKeyValue = !$changerKeyValue;
                
                if($changerKeyValue){

                    $keyExists = !$useEsktraHeader ?
                        array_key_exists($textInput, $dataInventar[$currentTitle][$currentHeader]) :
                        array_key_exists($textInput, $dataInventar[$currentTitle][$currentHeaderEkstra][$currentHeader]);

                    if($keyExists){
                        
                        $currentKey = $textInput;
                    }
                }
                else if($currentKey != ""){
                    
                    //lav det om til en alias
                    if($useEsktraHeader){
                        $dataInventValAlias = &$dataInventar[$currentTitle][$currentHeaderEkstra][$currentHeader][$currentKey];
                    }
                    else{
                        $dataInventValAlias = &$dataInventar[$currentTitle][$currentHeader][$currentKey];
                    }
                    
                    //tilføj data alt efter om 
                    //det er en array eller ej
                    if(is_array($dataInventValAlias)){
                        $dataInventValAlias[] = $textInput;
                    }
                    else{
                        $dataInventValAlias = $textInput;
                    }
                    
                    //nulstil key navn
                    $currentKey = "";
                }
            }        
        }   
        
        
        return $dataInventar;
    }
    
    private function GetDataFormSQL($comuterId) {
        
        function addInnerJoin($values){
            $cmd = "";
            for($i = 0; $i < count($values); $i += 3){

                $cmd .= "INNER JOIN $values[$i] ";
                $cmd .= "ON {$values[$i+1]} = {$values[$i+2]} ";
            }
            
            return $cmd;
        }
        function creatCmd($table, $join){                    
            $command = "SELECT * FROM $table ";
            $command .= addInnerJoin($join);        
            $command .= "WHERE `com_id` = ?;";   
            
            return $command;
        }
        
        $database = new Database();
        
        //default where value
        $whereVal = array($comuterId);
        
        //table navne
        $tableCom = "`{$database->Prefix}computers`";
        $tableMother = "`{$database->Prefix}motherboard`";
        $tableOS = "`{$database->Prefix}operatingsystem`";
        $tableType = "`{$database->Prefix}computertype`";
        $tableCPU = "`{$database->Prefix}cpu`";
        $tableComRam = "`{$database->Prefix}computersram`";
        $tableRam = "`{$database->Prefix}ram`";
        $tableComDisplay = "`{$database->Prefix}computersvideo`";
        $tableDisplay = "`{$database->Prefix}video`";
        $tableComAudio = "`{$database->Prefix}computersaudio`";
        $tableAudio = "`{$database->Prefix}audio`";
                
        //array til inner join function
        $innerJoinCom = array(
            "$tableMother", "$tableCom.mother_id","$tableMother.mother_id",
            "$tableOS", "$tableCom.os_id","$tableOS.os_id",
            "$tableType", "$tableCom.comType_id","$tableType.comType_id",
            "$tableCPU", "$tableCom.cpu_id","$tableCPU.cpu_id",
        );
        $innerJoinRam = array(
            "$tableRam", "$tableComRam.ram_id","$tableRam.ram_id",
        );
        
        $innerJoinDisplay = array(
            "$tableDisplay", "$tableComDisplay.video_id","$tableDisplay.video_id",
        );
        
        $innerJoinAduio = array(
            "$tableAudio", "$tableComAudio.audio_id","$tableAudio.audio_id",
        );
        
        
        
        //opret sql kommando
        $commandCom = creatCmd($tableCom, $innerJoinCom);
        $commandRam = creatCmd($tableComRam, $innerJoinRam);
        $commandDisplay = creatCmd($tableComDisplay, $innerJoinDisplay);
        $commandAudio = creatCmd($tableComAudio, $innerJoinAduio);
        
        //hent data fra databasen
        $rowMain = $database->GetResult($commandCom, $whereVal);
        $rowRam = $database->GetResults($commandRam, $whereVal);
        $rowDisplay = $database->GetResults($commandDisplay, $whereVal);
        $rowAudio = $database->GetResults($commandAudio, $whereVal);
        
        
        //echo $commandRam;
        //echo count($result);
        
        //echo "<br>";
        //echo "<br>";
        //print_array($rowMain);
        //print_array($rowRam);
        //print_array($rowDisplay);
        //print_array($rowAudio);
        
        $combineVal = array(
            "computer" => array(
                "index" => $rowMain["com_id"],
                "name" => $rowMain["computerName"],
                "comType_id" => $rowMain["comType_id"],
                "comTypeName" => $rowMain["comTypeName"],
                "hddTotalSize" => $rowMain["hddTotalSize"],
                "ip address" => $rowMain["ipAddress"],
                "mac address" => $rowMain["macAddress"],
                "datetime" => $rowMain["date_time"]
            ),
            "os" => array(
                "index" => $rowMain["os_id"],
                "name" => $rowMain["osName"],
                "service Pack" => $rowMain["servicePack"]
            ),
            "motherboard" => array(
                "index" => $rowMain["mother_id"],
                "name" => $rowMain["motherName"],
                "chipset" => $rowMain["chipset"],
                "bios" => $rowMain["bios"]
            ),
            "cpu" => array(
                "index" => $rowMain["cpu_id"],
                "name" => $rowMain["cpuName"]
            ),
            "ram" => $rowRam,
            "video" => $rowDisplay,
            "audio" => $rowAudio
        );
        
        return $combineVal;
    }
    
    
    /**
     * 
     */
    private function UploadToSQL(){
        $database = new Database();
        
        if($this->FileData == null){
            $this->FileData = $this->GetData();
        }
        
        $tableForOs = $database->Prefix."operatingSystem";
        $tableForCpu = $database->Prefix."cpu";
        $tableForType = $database->Prefix."computerType";
        $tableForMother = $database->Prefix."motherboard";
        $tableForCom = $database->Prefix."computers";
        $tableForRam = $database->Prefix."ram";
        $tableForRamCom = $database->Prefix."computersRam";
        $tableForDisplay = $database->Prefix."video";
        $tableForDisplayCom = $database->Prefix."computersVideo";
        $tableForAudio = $database->Prefix."audio";
        $tableForAudioCom = $database->Prefix."computersAudio";
        
        
        $valueForOs = array(
            "osName" => $this->FileData["Summary"]["Computer:"]["Operating System"],
            "servicePack" => $this->FileData["Summary"]["Computer:"]["OS Service Pack"],
        );
        $valueForCpu = array(
            "cpuName" => $this->FileData["Summary"]["Motherboard:"]["CPU Type"],
        );
        $valueForType = array(
            "comTypeName"=> $this->FileData["Summary"]["Computer:"]["Computer Type"],
        );
        $valueForMother = array(
            "motherName" => $this->FileData["Summary"]["Motherboard:"]["Motherboard Name"],
            "chipset" => $this->FileData["Summary"]["Motherboard:"]["Motherboard Chipset"],
            "bios" => $this->FileData["Summary"]["Motherboard:"]["BIOS Type"],
        );
        $valueForCom = array(
            "computerName" => $this->FileData["Summary"]["Computer:"]["Computer Name"],
            "comType_id" => &$indexForType,
            "os_id" => &$indexForOs,
            "cpu_id" => &$indexForCpu,
            "mother_id" => &$indexForMother,
            "ipAddress" => $this->FileData["Summary"]["Network:"]["Primary IP Address"],
            "macAddress" => $this->FileData["Summary"]["Network:"]["Primary MAC Address"],
            "hddTotalSize" => $this->FileData["Summary"]["Partitions:"]["Total Size"],
            "date_time" => $this->FileData["Summary"]["Computer:"]["Date / Time"]
        );               
        
        //upload til database og gem index
        //index vil automatik blive brugt i $valueForCom
        $indexForOs = $database->Insert($tableForOs, $valueForOs, true);
        $indexForCpu = $database->Insert($tableForCpu, $valueForCpu, true);
        $indexForType = $database->Insert($tableForType, $valueForType, true);
        $indexForMother = $database->Insert($tableForMother, $valueForMother, true);
        
        //hvis den findes, stop
        if($database->Exist($tableForCom, $valueForCom)){
            return;
        }
        
        //upload
        $indexForCom = $database->Insert($tableForCom, $valueForCom, true);
        
        //bliver brugt til når der skal uploaded til databasen
        //hvor der kan være flere af samme typer f.eks ram
        $addMultiValues = function ($tables, $indexKey, $values) use (&$database, &$indexForCom){
            for($i=0; $i < count(reset($values)); $i++){
            
                //opret values array
                $valueArray = array();
                foreach ($values as $key => $value) {
                    $valueArray[$key] = $value[$i];
                }

                //upload til database og hent index
                $index = $database->Insert($tables[0], $valueArray, true);

                //upload til database
                $database->Insert($tables[1], array(
                    "com_id" => $indexForCom,
                    $indexKey => $index
                ));
            }
        };
        
        
        //forkort variabler
        $containerRam = &$this->FileData["DMI"]["[ Memory Devices / ChannelA-"]["Memory Device Properties:"];
        $containerDisplay = &$this->FileData["Summary"]["Display:"];
        $containerAudio = &$this->FileData["Summary"]["Multimedia:"];
        
        //opret value array med column navn
        //og values i array format
        $valueForRam = array(
            "ramName" => $containerRam["Manufacturer"],
            "size" => $containerRam["Size"],
            "fFactor" => $containerRam["Form Factor"],
            "type" => $containerRam["Type"]
        );
        $valueForDisplay = array("videoName" => $containerDisplay["Video Adapter"]);
        $valueForAudio = array("audioName" => $containerAudio["Audio Adapter"]);
        
        //upload til databasen ved hjælp af function
        $addMultiValues(array($tableForRam, $tableForRamCom), "ram_id", $valueForRam);        
        $addMultiValues(array($tableForDisplay, $tableForDisplayCom), "video_id", $valueForDisplay); 
        $addMultiValues(array($tableForAudio, $tableForAudioCom), "audio_id", $valueForAudio);
        
        
        echo "=========<br>========<br>=======<br>====<br>";
    }
}

//new SaveFileData("C:\\Users\\sisk\\Desktop\\1. PC-SKILT11.html");


class website{
    
    private $maxRam = 1;
    private $maxVideo = 1;
    private $maxAudio = 1;




    public function __construct() {
        $content = $this->ComparePc();
        
        $this->ExecutePage($content);
    }
    
    private function ShowPcInventar($index) {
        $pc = new FileData($index);
        
        
        if(count($pc->FileData["ram"]) > $this->maxRam){
            $this->maxRam = count($pc->FileData["ram"]);
        }
        if(count($pc->FileData["video"]) > $this->maxVideo){
            $this->maxVideo = count($pc->FileData["video"]);
        }
        if(count($pc->FileData["audio"]) > $this->maxAudio){
            $this->maxAudio = count($pc->FileData["audio"]);
        }
        
        //echo '222';
        
        //print_array($pc->FileData);
        
        
        ob_start();
        
        ?>


<ul class="nonStyle itemTitle itemContainer toggleSizeSmall">
    <li class="headerColor" style="white-space: nowrap;" >&nbsp;
        <span style="position: absolute;">
            <button onclick="ComputerHide(this);">Skjul</button>
            <button onclick="ComputerToggleSize(this);">Udvid</button>
        </span>
    </li>
    <li>
        <ul class="itemValues">
            <li><?php echo $pc->FileData["computer"]["name"]; ?></li>
            <li><?php echo $pc->FileData["computer"]["comTypeName"]; ?></li>
            <li><?php echo $pc->FileData["computer"]["hddTotalSize"]; ?></li>
<!--            <li><?php echo $pc->FileData["computer"]["datetime"]; ?></li>-->
        </ul>
    </li>
    <li class="headerColor" >&nbsp;</li>
    <li>
        <ul class="itemValues">
            <li><?php echo $pc->FileData["computer"]["ip address"]; ?></li>
            <li><?php echo $pc->FileData["computer"]["mac address"]; ?></li>
        </ul>
    </li>
    <li class="headerColor" >&nbsp;</li>
    <li>
        <ul class="itemValues">
            <li><?php echo $pc->FileData["os"]["name"]; ?></li>
            <li><?php echo $pc->FileData["os"]["service Pack"]; ?></li>
        </ul>
    </li>
    <li class="headerColor" >&nbsp;</li>
    <li>
        <ul class="itemValues">
            <li><?php echo $pc->FileData["motherboard"]["name"]; ?></li>
            <li><?php echo $pc->FileData["motherboard"]["chipset"]; ?></li>
            <li><?php echo $pc->FileData["motherboard"]["bios"]; ?></li>
        </ul>
    </li>
    <li class="headerColor" >&nbsp;</li>
    <li>
        <ul class="itemValues">
            <li><?php echo $pc->FileData["cpu"]["name"]; ?></li>
        </ul>
    </li>
    <?php 
    for($i = 0; $i < count($pc->FileData["ram"]); $i++){ 
        
        if($i == 0){
            echo "<li class='headerColor' >&nbsp;</li>";        
        }
        else{
            echo "<li class='headerColor smallHeader' ></li>";        
        }
        
        echo "<li><ul class='itemValues'>";
        
        echo "<li>{$pc->FileData["ram"][$i]["ramName"]}</li>";
        echo "<li>{$pc->FileData["ram"][$i]["size"]}</li>";
//        echo "<li>{$pc->FileData["ram"][$i]["fFactor"]}</li>";
        echo "<li>{$pc->FileData["ram"][$i]["type"]}</li>";
        echo "</ul></li>";
    }
?>
    <li class="headerColor" >&nbsp;</li>
    <li>
        <ul class="itemValues">
            <?php 
                foreach ($pc->FileData["video"] as $value) {
                    
                    echo "<li>{$value["videoName"]}</li>";
                }
            ?>
            
        </ul>
    </li>
    <li class="headerColor" >&nbsp;</li>
    <li>
        <ul class="itemValues">
            <?php 
                foreach ($pc->FileData["audio"] as $value) {
                    
                    echo "<li>{$value["audioName"]}</li>";
                }
            ?>
            
        </ul>
    </li>
</ul>
        <?php
        
        $content = ob_get_contents();
        ob_end_clean();
        
        echo $content;
    }
    
    private function ComparePc() {
        
        ob_start();
        
        $this->ShowPcInventar(1);
        $computerContent = ob_get_contents();
        ob_end_clean();
        
        ob_start();
        
        
        ?>
<form method="get">
    <select name="test">
        <option value="all">Alle</option>
        <option value="1">PC-SKILT11</option>
    </select>
    <input type="submit" value="Hent"/>
</form>

<style>
    
    ul.headerContainer li ul,ul.itemContainer li ul{
        padding-top: 0px;
        padding-left: 25px;
        padding-bottom: 15px;
    }
    ul.headerContainer, ul.itemContainer{
        /*display: inline-block;*/
        float: left;
        cursor: default;
    }
    
    ul.headerContainer{
        margin-left: 20px;
        background: #34A2E2;
        color: #383838;
        border: 2px solid #227AAD;
/*        border-left: 2px solid #227AAD;
        border-bottom: 2px solid #227AAD;*/
        
        border-top-left-radius: 5px;
        border-bottom-left-radius: 5px;        
    }
    ul.itemContainer{
        
        position: relative;
        border-top: 2px solid #227AAD;
        border-right: 2px solid #227AAD;
        border-bottom: 2px solid #227AAD;
    }
    .headerTitle > li, .itemTitle > li{
        font-size: 18px;
        font-weight: bold;
    }
    ul.headerContainer .headerSubtitle li, ul.itemContainer .itemValues li{
        padding: 2px 0;
        font-size: 15px;
    }
    ul.itemContainer .itemValues li{
        white-space: nowrap;
        overflow: hidden;
        font-weight: normal;
        text-overflow: ellipsis;
        padding-right: 5px;
    }
    ul.headerContainer .headerSubtitle li{
        font-weight: bold;        
    }
    ul.headerContainer li.headerColor,ul.itemContainer li.headerColor{
        padding: 10px;
        background:#227AAD;
        color: black;
    }
    ul.headerContainer li.smallHeader,ul.itemContainer li.smallHeader{
        height: 5px;
    }
    .toggleSizeSmall{
        width: 200px;
    }
</style>

<div style="margin-top: 20px;" id="itemDiv">
    <ul class="nonStyle headerTitle headerContainer">
        <li class="headerColor" >Main</li>
        <li>
            <ul class="headerSubtitle">
                <li>Name:</li>
                <li>Type:</li>
                <li>Hard Drive:</li>
            </ul>
        </li>
        <li class="headerColor">Network</li>
        <li>
            <ul class="headerSubtitle">
                <li>IP:</li>
                <li>MAC:</li>
            </ul>
        </li>
        <li class="headerColor">Operating System</li>
        <li>
            <ul class="headerSubtitle">
                <li>Version:</li>
                <li>Service Pack:</li>
            </ul>
        </li>
        <li class="headerColor">Motherboard</li>
        <li>
            <ul class="headerSubtitle">
                <li>Name:</li>
                <li>Chipset:</li>
                <li>BIOS:</li>
            </ul>
        </li>
        <li class="headerColor">Processor</li>
        <li>
            <ul class="headerSubtitle">
                <li>Name:</li>
            </ul>
        </li>
        <?php 
        for($i = 0; $i < $this->maxRam; $i++ ){
            if($i == 0){
                echo "<li class='headerColor'>Ram</li>";
            }
            else{
                echo "<li class='headerColor smallHeader'></li>";}
        
            ?>
        <li>
            <ul class="headerSubtitle">
                <li>Firm:</li>
                <li>Size:</li>
                <!--<li>Form Factor:</li>-->
                <li>Type:</li>
            </ul>
        </li>
        <?php } ?>
        <li class="headerColor">Display</li>
        <li>
            <ul class="headerSubtitle">
                <?php 
                for($i = 0; $i < $this->maxVideo; $i++ ){
                    if($i == 0){
                        echo "<li>Video Adapter:</li>";
                    }
                    else{
                        echo "<li>&nbsp;</li>";}
                }
                ?>                
            </ul>
        </li>
        <li class="headerColor">Multimedia</li>
        <li>
            <ul class="headerSubtitle">
                <?php 
                for($i = 0; $i < $this->maxAudio; $i++ ){
                    if($i == 0){
                        echo "<li>Audio Adapter:</li>";
                    }
                    else{
                        echo "<li>&nbsp;</li>";}
                }
                ?>
            </ul>
        </li>
    </ul>
    
    <?php 
        echo $computerContent;
    
    ?>
    
</div>


        <?php
        
        $content = ob_get_contents();
        ob_end_clean();
        
        
        
        return $content;
    }
    
    
    
    public function ExecutePage($content) {
        
        ?>
<!DOCTYPE html>
<html>
    <head>
        <title>SKP DATA - Inventar Database -</title>
        <meta charset="utf-8"/>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.2/jquery.min.js"></script>
        <link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/themes/smoothness/jquery-ui.css">
        <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/jquery-ui.min.js"></script>
        
        <style>
            html,body{
                width: 100%;
                padding: 0;
                margin: 0;
                font-family: helvetica, sans-serif, serif;
                background: lightgray;
            }
            .top, .middle{
                position: relative;
                z-index: 1;
            }
            .top{
                z-index: 1000;
                width: 100%;
                top: 0;
                left: 0;
                position: fixed;
                background: #34A2E2;
                height: 80px;
                border-bottom: 5px solid #227AAD;
            }
            
            .top a.logo{
                display: inline-block;
                text-decoration: none;
                font-size: 30px;
                font-weight: bold;
                padding: 10px;
                margin: 10px 5px;
                color: white;
            }
            .top .navigation, .top .navigation li{
                margin: 0;
                padding: 0;
                list-style: none;
                display: inline-block;
            }
            
            .top .navigation{
                margin-left: 150px;
            }
            
            .top .navigation li a{
                font-size: 15px;
                padding: 5px 5px;
                background: green;
                font-weight: bold;
                text-decoration: none;
            }
            .middle .content{
                padding: 20px 10px 10px 10px;
                width: 95%;
                min-height: 500px;
                margin: 100px auto 20px auto;
                background: white;
                border-radius: 5px;
                border: lightgray groove 2px;
                
            }
            
            .bottomInfo{
                font-size: 100px;
                font-weight: bold;
                color: gray;
                position: absolute;
                padding: 0;
                margin: 0;
                margin-top: -100px;
                margin-left: 20px;
                cursor: default;
                
                -moz-user-select: none; 
                -webkit-user-select: none; 
                -ms-user-select:none; 
                user-select:none;
                -o-user-select:none;
            }
            ul.nonStyle, ul.nonStyle li{
                margin: 0;
                padding: 0;
                list-style: none;
            }
        </style>
       
        <script>
        $(function (){
             
                
            var divHeight = 0;
            $("#itemDiv").children().each(function(){
                var childrenHeight = $(this).height();
                
                if(divHeight < childrenHeight){
                    divHeight = childrenHeight;
                }
            });
            $("#itemDiv").css("height",divHeight);
        });    
        </script>
        
        <script>
            $(function (){
                
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
        </script>
        
        
        <script>
    function ComputerHide(item){
        var ulForm = $(item).parent().parent().parent();
        
        $(ulForm).animate({width: 0},500,function(){
            $(ulForm).hide();
        });
        //$(ulForm).hide();
    }
    
    function ComputerToggleSize(item){
        var ulForm = $(item).parent().parent().parent();
//        $(ulForm).clearQueue();
        $(ulForm).toggleClass("toggleSizeSmall",500);
        /*
        if($(ulForm).hasClass("toggleSizeSmall")){
            $(ulForm).removeClass("toggleSizeSmall");
            $(item).text("Tilbage");
        }
        else{
            $(ulForm).addClass("toggleSizeSmall",5000);
            $(item).text("Udvid");
        }*/
        
    }
</script> 
    </head>
    <body>
        <div class="top">
            <a href="#" class="logo">Inventar Database</a>
            <ul class="navigation">
                <li><a href="#">Link 1</a></li>
                <li><a href="#">Link 2</a></li>
                <li><a href="#">Link 3</a></li>
                <li><a href="#">Link 4</a></li>
            </ul>
        </div>
        <div class="middle">
            <div class="content"><?php echo $content; ?></div>
        </div>
        
        <p class="bottomInfo" unselectable="on" onselectstart="return false;" onmousedown="return false;">SKP DATA</p>
    </body>
</html>    
        <?php
        
    }
}

new website();