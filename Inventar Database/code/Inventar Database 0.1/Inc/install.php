<?php

    class install{
    
    public $Styles = array();
    public $Scripts = array();
    
    private $Success = false;
    private $AllowInstall = false;
    private $SetpId = 0;

    // <editor-fold desc="$Page - Side title og function">
    
    private $Page = array(
        array(
            "subtitle" =>"Database Login",
            "pageFunction" =>"Step_DatabaseLogin",
            ),
        array(
            "subtitle" =>"Database Tjek",
            "pageFunction" =>"Step_DatabaseCheck",
            ),
        array(
            "subtitle" =>"Installer",
            "pageFunction" =>"Step_MakeInstall",
            )
    );
    // </editor-fold>
    
    public function __construct() {
        
        $this->Scripts[] = "page_install.js";
        $this->Styles[] = "page_install.css";
        $this->Styles[] = "buttons.css";
        
        $this->RemoveSession(); //tjek om man skal starte forfra
        $this->UpdateSetId();//så vi ved at session er sat        
        $this->Check_DatabasePost();//sæt session med database var
        $this->StopFromContinue();//om den skal forsætte med at kunne køre andre functioner
    }
    
    public function PerformeStep() {
        
        $stepId = $this->GetStepId();
        
        //$stepIdTitle = $stepId +1;
        $currentPage = $stepId +1;
        $maxPages = count($this->Page);
        
        //$allowMoveNext = $stepId != count($this->Page)-1;
        $allowMoveBack = $stepId != 0;
        $isLastPage = $stepId == $maxPages-2;
        $isPageBeforeInstall = $stepId < $maxPages-2;
        $isInstallPage = $stepId == $maxPages-1;
        
        
        $title = "Installation - Trin $currentPage/$maxPages";
        $subtitle = $this->Page[$stepId]["subtitle"];
        
        ob_start();        
        call_user_func(array($this, $this->Page[$stepId]["pageFunction"]));        
        $content = ob_get_contents();
        ob_end_clean();
        
        $buttons = "";
        
        if($isPageBeforeInstall){

            $lockBack = $allowMoveBack ? "" : "lock";

            $buttons .= $this->Template_Button("Tilbage", "blue $lockBack", "MoveStep(this,\"back\")");
            $buttons .= $this->Template_Button("Videre", "blue", "MoveStep(this,\"next\")", "float:right;");
        }
        else if($isLastPage){

            $lockInstall = $this->AllowInstall ? "" : "lock";

            $buttons .= $this->Template_Button("Tilbage", "blue", "MoveStep(this,\"back\")");
            $buttons .= $this->Template_Button("Installer", "green $lockInstall", "MoveStep(this,\"next\")", "float:right;");
        }
        else if($isInstallPage){
            if($this->Success){
                $buttons .= $this->Template_Button("Gå til forside", "green", "GoToStartPage();");
            }
            else{
                $buttons .= $this->Template_Button("Prøv igen", "blue", "TryAgain();");
            }
        }
        
        $pageContent = $this->Template_Page($title, $subtitle, $content, $buttons);
        
        //$buttonHasLockNext = $allowMoveNext ? "" : "lock";
        $buttonHasLockBack = $allowMoveBack ? "" : "lock";
        
        ob_start();
        
        ?>
<div class="install">
    <h2 class="title"><?php echo "Installation - Trin $stepIdTitle/$maxPages"; ?></h2>
    <h4 class="subtitle"><?php echo $this->Page[$stepId]["subtitle"]; ?></h4>
    <div class="output">
        <?php
        
        call_user_func(array($this, $this->Page[$stepId]["pageFunction"]));
        
        ?>

    </div>
    <div class="controlButtons" <?php echo $isInstallPage ? "style='text-align: center'" : ""; ?>>
        <?php         
            
            if($isPageBeforeInstall){
                echo "<button class='button blue noInstallStyle $buttonHasLockBack' "
                        . "onclick='MoveStep(this,\"back\")'>Tilbage</button>";
                
                echo "<button class='button blue noInstallStyle' style='float:right;' "
                . "onclick='MoveStep(this,\"next\")'>Videre</button>";
            }
            else if($isLastPage){
                
                $isInstallLockC = !$this->AllowInstall ? "lock" : "";
                
                echo "<button class='button blue noInstallStyle $buttonHasLockBack' "
                        . "onclick='MoveStep(this,\"back\")'>Tilbage</button>";
                
                echo "<button class='button green noInstallStyle $isInstallLockC' style='float:right;' "
                        . "onclick='MoveStep(this,\"next\")'>Installer</button>";
            }
            else if($isInstallPage){
                if($this->Success){
                    echo "&nbsp;<button class='button green noInstallStyle' "
                    . "onclick='GoToStartPage();'>Gå til forside</button>";
                }
                else{
                    echo "&nbsp;<button class='button red noInstallStyle' "
                    . "onclick='TryAgain();'>Prøv igen</button>";
                }
            }
            
        ?>
    </div>
</div>
        <?php
        
        //$content = ob_get_contents();
        ob_end_clean();
        
        return $pageContent;
    }
    
    private function UpdateSetId(){
        if(session_id() == '') {session_start();}
        
        //tjek om variabler er sat
        $hasSession = isset($_SESSION["INSTALL"]) && !empty($_SESSION["INSTALL"]);
        $hasMove = isset($_POST["move"]) && !empty($_POST["move"]);
        
        
        //hvilken vej man skal gå
        $moveNext = $hasMove && $_POST["move"] == "next";
        $moveBack = $hasMove && $_POST["move"] == "back";

        
        //hvis session ikke er oprettet
        //opret den her med database
        if(!$hasSession){ 
            
            $_SESSION["INSTALL"] = array(
                "step" => 0,
                "database" => array(
                    "host"=> "",
                    "user"=> "",
                    "pass"=> "",
                    "database"=> "",
                    "prefix"=> ""
                )
            );
        }
        else {
            
            //sæt trin
            if($moveNext){ $_SESSION["INSTALL"]["step"]++; }
            else if($moveBack){ $_SESSION["INSTALL"]["step"]--; }

            //tjek om trin er ude af index
            if($_SESSION["INSTALL"]["step"] < 0)
                { $_SESSION["INSTALL"]["step"] = 0;}
            else if($_SESSION["INSTALL"]["step"] > count($this->Page)-1)
                { $_SESSION["INSTALL"]["step"] = count($this->Page)-1;}
        }
    }
    
    private function GetStepId() {
        
        if(session_id() == '') {session_start();}
        
        //hvis session ikke er sat sæt den
        $hasSession = isset($_SESSION["INSTALL"]) && !empty($_SESSION["INSTALL"]);
        if(!$hasSession){
            $this->UpdateSetId(); 
        }
        return $_SESSION["INSTALL"]["step"];
    }
    
    private function RemoveSession() {
        if(session_id() == '') {session_start();}
        //tjek om variable er sat
        $hasOption = isset($_POST["option"]) && !empty($_POST["option"]);
        
        $removeSession = false;
        
        //tjek om den indeholder flere værdier
        if($hasOption && is_array($_POST["option"])){
            $removeSession = in_array("removeSession", $_POST["option"]);
        }
        else if($hasOption){
            $removeSession = $_POST["option"] == "removeSession";      
        } 
        
        if($removeSession){
            unset($_SESSION["INSTALL"]);
        }
    }
    
    private function StopFromContinue(){
        
        //tjek om variable er sat
        $hasOption = isset($_POST["option"]) && !empty($_POST["option"]);
        
        $stopFromShow = false;
        
        //tjek om den indeholder flere værdier
        if($hasOption && is_array($_POST["option"])){
            $stopFromShow = in_array("stop", $_POST["option"]);
        }
        else if($hasOption){
            $stopFromShow = $_POST["option"] == "stop";      
        } 
        
        if($stopFromShow){
            die();
        }
    }
    
    public function Install_CreateTables(){
        
        //<editor-fold desc="Tabel opbygning $tables">
        
        $tables = array(
            array(
                "table" => "operatingSystem",
                "columns"=> array(
                    array("os_id","int", "not null", "AUTO_INCREMENT"),
                    array("osName","varchar(64)", "not null"),
                    array("servicePack","varchar(32)", "not null")
                ),
                "primary" => "os_id"
            ),
            array(
                "table" => "cpu",
                "columns"=> array(
                    array("cpu_id","int", "not null", "AUTO_INCREMENT"),
                    array("cpuName","varchar(64)", "not null")                  
                ),
                "primary" => "cpu_id"
            ),
            array(
                "table" => "computerType",
                "columns"=> array(
                    array("comType_id","int", "not null", "AUTO_INCREMENT"),
                    array("comTypeName","varchar(32)", "not null")
                ),
                "primary" => "comType_id"
            ),
            array(
                "table" => "motherboard",
                "columns"=> array(
                    array("mother_id","int", "not null", "AUTO_INCREMENT"),
                    array("motherName","varchar(64)", "not null"),
                    array("chipset","varchar(64)", "not null"),
                    array("bios","varchar(32)", "not null")
                ),
                "primary" => "mother_id"
            ),
            array(
                "table" => "computers",
                "columns"=> array(
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
                "primary" => "com_id", 
                "foreign" => array(
                    "os_id"=>"operatingSystem(os_id)",
                    "cpu_id"=>"cpu(cpu_id)",
                    "comType_id"=>"computerType(comType_id)",
                    "mother_id"=>"motherboard(mother_id)"
                )
            ),
            array(
                "table" => "ram",
                "columns"=> array(
                    array("ram_id","int", "not null", "AUTO_INCREMENT"),
                    array("ramName","varchar(64)", "not null"),
                    array("size","varchar(12)", "not null"),
                    array("fFactor","varchar(12)", "not null"),
                    array("type","varchar(12)", "not null")
                ),
                "primary" => "ram_id"
            ),
            array(
                "table" => "computersRam",
                "columns"=> array(
                    array("comR_ram","int", "not null", "AUTO_INCREMENT"),
                    array("ram_id","int", "not null"),
                    array("com_id","int", "not null")
                ),
                "primary" => "comR_ram", 
                "foreign" => array(
                    "ram_id"=>"ram(ram_id)",
                    "com_id"=>"computers(com_id)"
                )
            ),
            array(
                "table" => "video",
                "columns"=> array(
                    array("video_id","int", "not null", "AUTO_INCREMENT"),
                    array("videoName","varchar(64)", "not null")
                ),
                "primary" => "video_id"
            ),
            array(
                "table" => "computersVideo",
                "columns"=> array(
                    array("comVid_id","int", "not null", "AUTO_INCREMENT"),
                    array("video_id","int", "not null"),
                    array("com_id","int", "not null")
                ),
                "primary" => "comVid_id", 
                "foreign" => array(
                    "video_id"=>"video(video_id)",
                    "com_id"=>"computers(com_id)"
                )
            ),
            array(
                "table" => "audio",
                "columns"=> array(
                    array("audio_id","int", "not null", "AUTO_INCREMENT"),
                    array("audioName","varchar(128)", "not null")
                ),
                "primary" => "audio_id"
            ),
            array(
                "table" => "computersAudio",
                "columns"=> array(
                    array("comAud_id","int", "not null", "AUTO_INCREMENT"),
                    array("audio_id","int", "not null"),
                    array("com_id","int", "not null")
                ),
                "primary" => "comAud_id", 
                "foreign" => array(
                    "audio_id"=>"audio(audio_id)",
                    "com_id"=>"computers(com_id)"
                )
            )
        );
        
        //</editor-fold>
       
        $dbConfig = $_SESSION["INSTALL"]["database"];
        
        $database = new Database($dbConfig["host"],$dbConfig["user"],$dbConfig["pass"],$dbConfig["database"],$dbConfig["prefix"]);
        
        
        
        //tilføj prefix til table navne
        for ($i = 0; $i < count($tables); $i++){
            $tables[$i]["table"] = $database->Prefix.$tables[$i]["table"];
            
           
            
            //tilføj prefix til foregin key table navne
            if(array_key_exists("foreign", $tables[$i]))
            {
                $fKeys = array_keys($tables[$i]["foreign"]);
                
                for ($f = 0; $f < count($fKeys); $f++){
                    $tables[$i]["foreign"][$fKeys[$f]] = $database->Prefix.$tables[$i]["foreign"][$fKeys[$f]];
                }
            }
            else{
                $tables[$i]["foreign"] = null;
            }
        }
        
        //opret tabellerne i databasen
        foreach ($tables as $table) {
            $result = $database->Create($table["table"], $table["columns"], $table["primary"], $table["foreign"]);
            
            if(!$result){ return false;}
        }
        
        return true;
    }
    private function Install_WriteToDbConfig(){
        
        $dbConfig = $_SESSION["INSTALL"]["database"];        
        $dbConfigF = fopen("Inc/dbconfig.php", "w");
        
        if(!$dbConfigF){ return false;}
        
        
        fwrite($dbConfigF, "<?php".PHP_EOL);
        fwrite($dbConfigF, "define('DB_HOST', '{$dbConfig["host"]}');".PHP_EOL);
        fwrite($dbConfigF, "define('DB_USER', '{$dbConfig["user"]}');".PHP_EOL);
        fwrite($dbConfigF, "define('DB_PASS', '{$dbConfig["pass"]}');".PHP_EOL);
        fwrite($dbConfigF, "define('DB_DATABASE', '{$dbConfig["database"]}');".PHP_EOL);
        fwrite($dbConfigF, "define('DB_PREFIX', '{$dbConfig["prefix"]}');".PHP_EOL);
        fclose($dbConfigF);
        
        return true;
    }
    
    private function Template_Button($text = "", $class = "", $click = "", $style = ""){
        ob_start();
        ?>
        <button class='button QCLASSQ noInstallStyle'
            @STYLE@ @CLICK@>
            @TEXT@
        </button>
                
        <?php
        $template = ob_get_contents();
        ob_end_clean();
        
        if($click != ""){
            $click = "onclick='$click'";
        }
        if($style != ""){
            $style = "style='$style'";
        }
        
        $values = array(
            "@TEXT@" => $text, "QCLASSQ" => $class,
            "@CLICK@" => $click, "@STYLE@" => $style,
        );
        
        $this->Template_Replace($values, $template);
        
        return $template;
    }
    private function Template_Page($title = "", $subtitle = "", $content = "", $buttons = ""){
        ob_start();
        ?>
            <div class="install">
                <h2 class="title">@TITLE@</h2>
                <h4 class="subtitle">@SUBTITLE@</h4>
                <div class="output">@CONTENT@</div>
                <div class="controlButtons">@BUTTON@</div>
            </div>
        <?php
        $template = ob_get_contents();
        ob_end_clean();
        
        
        $values = array(
            "@TITLE@" => $title, "@SUBTITLE@" => $subtitle,
            "@CONTENT@" => $content, "@BUTTON@" => $buttons,
        );
        
        $this->Template_Replace($values, $template);
        return $template;
    }
    
    private function Template_Replace($Values, &$Template){
        foreach ($Values as $search => $replace) {
            $Template = str_replace($search, $replace, $Template);
        }
    }
    
    private function Check_DatabasePost() {
        
        $dbLogin = &$_SESSION["INSTALL"]["database"];
                
        foreach ($dbLogin as $key => $value) {
            
            $keyIsSet = isset($_POST[$key]) && !empty($_POST[$key]);
            
            $keyInSession = array_key_exists($key, $dbLogin);
            
            if($keyIsSet && $keyInSession){
                $dbLogin[$key] = $_POST[$key];
            }
        }
    }
    
    
    
    private function Step_DatabaseLogin() {
        
        $dbConfig = $_SESSION["INSTALL"]["database"];
        
        ?>
        <form action="?page=install" method="post">
            <input name="move" type="hidden" value="next"/>
            <input name="host" type="text" placeholder="Host" value="<?php echo $dbConfig["host"]; ?>"  onkeyup="this.setAttribute('value', this.value);">
            <input name="database" type="text" placeholder="Database" value="<?php echo $dbConfig["database"]; ?>" onkeyup="this.setAttribute('value', this.value);">
            <input name="user" type="text" placeholder="Username" value="<?php echo $dbConfig["user"]; ?>" onkeyup="this.setAttribute('value', this.value);">
            <input name="pass" type="text" placeholder="Password" value="<?php echo $dbConfig["pass"]; ?>" onkeyup="this.setAttribute('value', this.value);">
            <input name="prefix" type="text" placeholder="Prefix" value="<?php echo $dbConfig["prefix"]; ?>" onkeyup="this.setAttribute('value', this.value);">
        </form>
        <?php
    }
    private function Step_DatabaseCheck() {
        
        $this->Check_DatabasePost();
        
        $status_Disllowed = 0;
        $status_Allowed = 1;
        $status_Unknown = 2;
        
        // <editor-fold desc="$control - tabel for hvad der skal tjekkes">
        
        $control = array(
            array(
                "title" => "Connecte til databasen.",
                "status" => $status_Unknown
            ),
            array(
                "title" => "Oprette tabeller.",
                "status" => $status_Unknown
            ),
            array(
                "title" => "Indsætte værdier.",
                "status" => $status_Unknown
            ),
            array(
                "title" => "Opdatere værdier.",
                "status" => $status_Unknown
            ),
            array(
                "title" => "Vælge værdier.",
                "status" => $status_Unknown
            ),
            array(
                "title" => "Slette værdier.",
                "status" => $status_Unknown
            ),
            array(
                "title" => "Slette tabeller.",
                "status" => $status_Unknown
            ),
            array(
                "title" => "Indeholder ikke tabeller<br> med samme prefix.",
                "status" => $status_Unknown
            )
        );
        
        // </editor-fold>
        
        $dbLogin = $_SESSION["INSTALL"]["database"];
        
        
        $database = new Database($dbLogin["host"], $dbLogin["user"], $dbLogin["pass"], $dbLogin["database"], $dbLogin["prefix"]);
        $table = substr("inventar_Check_055_".str_shuffle("0P2bcdef3WX8LMQRlmn7STU149agzABCDYkEFopqrstuvwxyGHIJK56hijNOVZ"), 0, 64);
        
        for($i = 0; $i < count($control); $i++){
                        
            $statusCheck = false;
            switch ($i) {
                case 0:
                    $statusCheck = $database->CanConnect && $dbLogin["database"] != "";
                    
                    break;
                case 1:
                    $statusCheck = $database->Create($table, array(array("check","int")), "check");
                    
                    break;
                case 2:
                    $statusCheck = $database->Insert($table, array("check"=>1));
                    break;
                case 3:
                    $statusCheck = $database->Update($table, array("check"=>2), array("check"=>1));
                    break;
                case 4:
                    $statusCheck = $database->GetResult("SELECT * FROM `$table`;");
                    break;
                case 5:
                    $statusCheck = $database->Delete($table,array("check"=>2));
                    break;
                case 6:
                    $tables = $database->GetResults("show tables like 'inventar_Check_055_%';");
                    foreach ($tables as $row) {
                        $rowTable = array_values($row)[0];
                        $statusCheck = $database->Drop($rowTable);
                    }
                    break;
                case 7:
                    $tables = $database->GetResults("show tables like '{$database->Prefix}%';");
                    $statusCheck = count($tables) == 0;
                    break;
            }
            
            $control[$i]["status"] = $statusCheck ? 
                $status_Allowed : $status_Disllowed;
            
            if(!$statusCheck) {break;}
        }
        
        //om man skal kunne installere hjemmesiden
        $this->AllowInstall = true;
        foreach ($control as $value) {
            
            if($value["status"] !== $status_Allowed ){
                $this->AllowInstall = false;
                break;
            }
        }
        
        echo "<ul class='checkdb'>";
        foreach ($control as $value) {
            
            $statusText = "<span class='status @COlOR@'>@TEXT@</span>";
            
            switch ($value["status"]) {
                case $status_Allowed:        
                    $statusText = str_replace("@TEXT@","Okay", $statusText);
                    $statusText = str_replace("@COlOR@","green", $statusText);

                    break;
                case $status_Disllowed:
                    $statusText = str_replace("@TEXT@","Fejl", $statusText);
                    $statusText = str_replace("@COlOR@","red", $statusText);

                    break;
                case $status_Unknown:
                    $statusText = str_replace("@TEXT@","Ved ikke", $statusText);
                    $statusText = str_replace("@COlOR@","yellow", $statusText);

                    break;
            }
            
            echo "<li>";
            echo "<span>{$value["title"]}</span>";
            echo "$statusText";
            echo "</li>";
        }
        echo "</ul>";
    }
    private function Step_MakeInstall(){
        
        
        $canWriteToFile = $this->Install_WriteToDbConfig();
        $canWrtieToDB = $this->Install_CreateTables();
        
        $content = "";
        
        if(!$canWriteToFile){
            $content .= "Kunne ikke gemme database login.<br>";
        }
        
        if(!$canWrtieToDB){
            $content .= "Kunne ikke oprette tabellerne i databasen.<br>";
        }
        
        if($canWriteToFile && $canWrtieToDB){
            $this->Success = true;
            
            $content = "Hjemmesiden er nu installeret<br>";
            $content .= "Husk at slette <b>/Inc/Intall.php</b>";
        }
        
        echo $content;
        
        echo "<script>RemoveSession();</script>";
    }
    
    
}