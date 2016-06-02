<?php


class FileData{
    
    public $index = -1;
    public $Success = false;
    public $Timeout = false;
    private $Filename = "";
    public $FileData;
    public $Exits = false;
    public $Message = "No message";
    

    
    public function __construct($param) {
        
        
        //echo is_int($param) ? "true" : "false";
        
        if(is_string($param)){
            $this->Filename = $param;
            $this->UploadToSQL();
        }
        else if(is_int($param)){
            $this->index = $param;
            $database = new Database();
            $this->Exits = $database->Exist($database->Prefix."computers", array("com_id"=> $param), false);
            if(!$this->Exits){ return false; }
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
     * @return array
     */
    private function GetData() {
        
        // <editor-fold desc="file data array">
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
        //</editor-fold>
        //
        //hent fil som string
        $fileContent = file_get_contents($this->Filename);
        $timeStart = time();
        
        
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
            
            $timeNow = time();
            $timeDiffSec = ($timeNow - $timeStart) / 60;
            
            //denne function må ikke være længere end et minut
            if($timeDiffSec == 1 || false){
                $this->Timeout = true;
                break;
            }
            
            
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
            
            echo $charFirst;
            
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
        
        if(!function_exists("addInnerJoin")){
            function addInnerJoin($values){
                $cmd = "";
                for($i = 0; $i < count($values); $i += 3){

                    $cmd .= "INNER JOIN $values[$i] ";
                    $cmd .= "ON {$values[$i+1]} = {$values[$i+2]} ";
                }

                return $cmd;
            }
        }
        if(!function_exists("creatCmd")){
            function creatCmd($table, $join){                    
                $command = "SELECT * FROM $table ";
                $command .= addInnerJoin($join);        
                $command .= "WHERE `com_id` = ?;";   

                return $command;
            }
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
        $this->Success = false;
        
        $database = new Database();
        
        if($this->FileData == null){
            $this->FileData = $this->GetData();
        }
        
        // <editor-fold desc="Database tabel navne">
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
        
        // </editor-fold>
        
        // <editor-fold desc="fildata til database kategori">
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
        
        // </editor-fold>
        
        $computerName = $valueForCom["computerName"];
        $fileDate = $valueForCom["date_time"];
        
        //hvis den findes, stop
        $hasBeenUploaded = $database->Exist($tableForCom, 
                array(
                    "computerName" => $computerName, 
                    "date_time" => $fileDate
            )
        );
        
        if($hasBeenUploaded){
            $this->Message = "Er blevet uploaded";
            $this->Exits = true;
            return false;
        }
        if($computerName == ""){
            $this->Message = "Ikke noget navn";
            return false;
        }
        
        
        //upload til database og gem index
        //index vil automatik blive brugt i $valueForCom
        $indexForOs = $database->Insert($tableForOs, $valueForOs, true);
        $indexForCpu = $database->Insert($tableForCpu, $valueForCpu, true);
        $indexForType = $database->Insert($tableForType, $valueForType, true);
        $indexForMother = $database->Insert($tableForMother, $valueForMother, true);
        
        
        
        //upload
        $indexForCom = $database->Insert($tableForCom, $valueForCom, true);
        
        //sæt index for document
        $this->index = $indexForCom;
        
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
        
        
        $this->Message = "Er uploaded nu";
        $this->Success = true;
        return true;
    }
    
    public function RemoveFromSQL() {
        $database = new Database();
        
        $indexExist = $database->Exist($database->Prefix."computers", array("com_id"=> $this->index));
        
        if(!$indexExist){
            $this->Success = false;
            return false;
        }
        
        $deleteFrom = array(
            $database->Prefix."computersram",
            $database->Prefix."computersvideo",
            $database->Prefix."computersaudio",
            $database->Prefix."computers"
        );
        
        foreach ($deleteFrom as $table) {
            $database->Delete($table, array("com_id"=> $this->index));
        }
        
        return true;
        
    }
}
