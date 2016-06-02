<?php

class Database{
    
    public $CanConnect = false;

    private $CouldRun = false;
    private $IsConnected = false;
    private $Connection;
    
    private $Hostname = "";
    private $Username = "";
    private $Password = "";
    private $Database = "";
    public $Prefix = "";
    
    
    public function __construct($Host = null, $User = null, $Password = null, $Database = null, $Prefix = null) {
        
        $paramIsNotSet = $Host == null && $User == null && $Password == null && $Database == null && $Prefix == null;
        $dbConfigExist = file_exists("Inc/dbconfig.php");
        
        if($paramIsNotSet && $dbConfigExist){
            
            
                $this->Hostname = DB_HOST;
                $this->Username = DB_USER;
                $this->Password = DB_PASS;
                $this->Database = DB_DATABASE;
                $this->Prefix = DB_PREFIX;
            }
        else if(!$paramIsNotSet){
            $this->Hostname = $Host;
            $this->Username = $User;
            $this->Password = $Password;
            $this->Database = $Database;
            $this->Prefix = $Prefix;
        }
        
        
            $checkCon = @new mysqli($this->Hostname, $this->Username, $this->Password);
            $this->CanConnect = $checkCon->connect_errno == 0;
        
    }
    
    /**
     * laver kommandoen om med hjælp af ? for
     * at undgå injection
     * 
     * og køre til sidst kommandoen
     * 
     * @param string $CMD SELECT ?, ? FORM `?`;
     * @param array $Values1Arr array("column1","column2")
     * @param array $Values2Arr  array("table")
     * @return mixed
     * 
     * @var $stmt mysqli_stmt
     * @var $result mysqli_result
     */
    private function RunCMD($CMD, $Values1Arr = null, $Values2Arr = null){
        
        //hvis der ikke er nogen connection start en
        if(!$this->IsConnected){
            $this->IsConnected = true;
            $this->Connection = new mysqli($this->Hostname, $this->Username, $this->Password, $this->Database);
        }
        
        
        //gør klar til at oprette sql commando
        $stmt = $this->Connection->prepare($CMD);
        
        /*
         * Sæt $Values1Arr og $Values2Arr
         * sammen til en array
         * og samtidlig gør dem til
         * en ref variable
         */
        $parmRef = array();
        $value1Ref = $this->GetParmRefAndType($Values1Arr);
        $value2Ref = $this->GetParmRefAndType($Values2Arr);
        $valueType = $value1Ref["type"] . $value2Ref["type"];
        
        //loop til at gøre dem ref varibaler
        for($i = 0; $i < count($value1Ref["parmRef"]); $i++){
            $parmRef[] = &$value1Ref["parmRef"][$i];
        }
        for($i = 0; $i < count($value2Ref["parmRef"]); $i++){
            $parmRef[] = &$value2Ref["parmRef"][$i];
        }
        
        //echo "$CMD <br>";
        //tjek om der ver bliver brugt ? i $CMD
        if($valueType != ""){
            call_user_func_array(array($stmt, "bind_param"), array_merge(array($valueType), $parmRef));
        }
        
        //udfør commando og tjek om der skette fejl
        $statusResult = $stmt->execute();
        if(!$statusResult && true){
            echo "Error Found: {$CMD}<br>";
            echo $stmt->error ."<br>";
            
            echo "Parm needed: ".substr_count($CMD,"?").". Found: ". strlen($valueType)."<br>";
            
            print_array($Values1Arr);
            print_array($Values2Arr);
        }
        
        
        $this->CouldRun = $statusResult;
        
        //hent result og send det tilbage
        $result = $stmt->get_result();
        return $result;
    }
    
    public function Drop($table) {
        
        $this->RunCMD("drop table `$table`;");
        return $this->CouldRun;
    }
    
    /**
     * Opretter en table i databasen
     * 
     * @param string $Table "table"
     * @param array $Columns array(array("id","int", "AUTO_INCREMENT"))
     * @param string $pKey "id"
     * @param array $fKey array("id" => "table2(id2)")
     */
    public function Create($Table, $Columns, $pKey, $fKey = null) {
        
        //start på kommandoen
        $command = "CREATE TABLE `$Table`(";
        $loopRun = 0;
        
        foreach ($Columns as $oneColumn) {
            $oneColumn[0] = $loopRun == 0 ? "`$oneColumn[0]`" : $oneColumn[0];
            //lav array om til en lang string
            //og tilføj det til kommandoen
            $columnCmd = implode(" ", $oneColumn);            
            $command .= $columnCmd . ", ";
            $loopRun++;
        }
        
        //opret index
        $command .= "PRIMARY KEY (`{$pKey}`)";
        
        //tjek om foreign er sat
        if(is_array($fKey)){
            $fKeyCmd = ",";
            foreach ($fKey as $key => $value) {
                $fKeyCmd .= "FOREIGN KEY ({$key}) REFERENCES {$value}, ";
            }
            //fjern ", " tilsidst
            $fKeyCmd = strshort($fKeyCmd, -2);
            $command .= $fKeyCmd;
        }
        
        //afslut kommanden og kør den
        $command .= ");";
        
        $result = $this->RunCMD($command);
        
        //var_dump($result);
        return $this->CouldRun;
    }
    
    /**
     * 
     * @param string $Table "table1"
     * @param array $Values array("colum1"=>"value")
     * @return mixed hvis flere resultater send array ellers int
     */
    public function Exist($Table, $ValuesCheck, $returnPkey = true){
        
        //
        $whereValue = $this->WhereConverter($ValuesCheck);
        
        $resultPkey = $this->GetResult("SHOW INDEX FROM `{$Table}`;");
       // print_r($resultPkey);
        
        $result = $this->GetResults("SELECT `{$resultPkey["Column_name"]}` FROM `{$Table}` {$whereValue["nonValues"]};",$whereValue["values"]);
        $resultCount = count($result);
        
        if($returnPkey && $resultCount != 0){
            return $result[$resultCount-1][$resultPkey["Column_name"]];
        }
        else{
            return $resultCount != 0;
        }
    }


    public function Insert($Table, $Values, $isUnique = false){
       
        if($isUnique){
            if(($index = $this->Exist($Table, $Values))){
                //echo "<br>return-->$index<br>";
                return $index; 
            }
        }
        
        $valueReady = $this->ValueConverterInsert($Values);
        
        $command = "INSERT INTO `{$Table}` ({$valueReady["columns"]}) VALUES({$valueReady["nonValues"]});";
        $this->RunCMD($command, $valueReady["values"]);
        
        //$result = $this->GetResult("SELECT LAST_INSERT_ID();");
       
        return $this->Exist($Table, $Values);
    }
    public function Update($Table, $Values, $WhereArr){
        $whereReady = $this->WhereConverter($WhereArr);
        $valueReady = $this->ValueConverterUpdate($Values);
        
        $command = "UPDATE `{$Table}` SET {$valueReady["nonValues"]} {$whereReady["nonValues"]};";
        $this->RunCMD($command, $valueReady["values"], $whereReady["values"]);
        
        return $this->CouldRun;
    }
    
    
    public function Delete($Table, $WhereArr){
        
        $whereReady = $this->WhereConverter($WhereArr);
        $command = "DELETE FROM `{$Table}` {$whereReady["nonValues"]};";
        
        //echo "$command<br>";
        $this->RunCMD($command, null, $whereReady["values"]);
        
        return $this->CouldRun;
    }
    public function GetResult($CMD, $Values = null){
             
        /* @var $result mysqli_result */   
        $result = $this->RunCMD($CMD, $Values);
        if(!$result){
            return false;
        }
        
        $rowData = $result->fetch_assoc();
        //$result->fetch_field()
        
        $result->free();
        
        return $rowData;
    }
    public function GetResults($CMD, $Values = null){     
        /* @var $result mysqli_result */   
        $result = $this->RunCMD($CMD, $Values);
        if(!$result){
            return false;
        }
        $rowData = array();
        while($row = $result->fetch_assoc()){
            $rowData[] = $row;
        }       
        $result->free();
        
        return $rowData;
        
    }
    
    public function Close(){
        $this->Connection->close();
        $this->IsConnected = false;
    }
    
    
    private function GetParmRefAndType($value){
        $valueReturn = array(
            "parmRef" => array(),
            "type" => ""
        );
        
        
        for ($i = 0; $i < count($value); $i++) {

            $valueReturn["parmRef"][] = &$value[$i];

            if(is_string($value[$i])){
                $valueReturn["type"] .= "s";                
            }
            else if (is_int($value[$i])) {
                $valueReturn["type"] .= "i";
            }
            else if(is_double($value[$i])){
                $valueReturn["type"] .= "d";
            }
        }
        
        
        return $valueReturn;
    }
    private function ValueConverterInsert($Values){
        
        $valueReturn = array(
            "columns" => "",
            "values" => array(),
            "nonValues" => ""
        );
        
        foreach ($Values as $column => $value) {
            $valueReturn["columns"] .= "`{$column}`, ";
            $valueReturn["nonValues"] .= "?, ";
            $valueReturn["values"][] = $value;            
        }
        
        //fjern komma og mellemrum til sidst
        $valueReturn["columns"] = substr($valueReturn["columns"], 0, strlen($valueReturn["columns"]) -2);
        $valueReturn["nonValues"] = substr($valueReturn["nonValues"], 0, strlen($valueReturn["nonValues"]) -2);
        
        return $valueReturn;
    }
    private function ValueConverterUpdate($Values){
        
        $valueReturn = array(
            "values" => array(),
            "nonValues" => ""
        );
        
        foreach ($Values as $column => $value) {
            $valueReturn["nonValues"] .= "`{$column}` = ?, ";
            $valueReturn["values"][] = $value;            
        }
        
        //fjern komma og mellemrum til sidst
        $valueReturn["nonValues"] = substr($valueReturn["nonValues"], 0, strlen($valueReturn["nonValues"]) -2);
        
        return $valueReturn;
    }
    private function WhereConverter($WhereArr, $Type = "AND"){
        $valueReturn = array(
            "nonValues" => "WHERE ",
            "values" => array()
        );
        
        foreach ($WhereArr as $column => $value) {
            
            $valueReturn["nonValues"] .= "`{$column}` = ?";
                        
            $valueReturn["nonValues"] .= " {$Type} ";
            $valueReturn["values"][] = $value;
        }
        $valueReturn["nonValues"] = substr($valueReturn["nonValues"], 0, strlen($valueReturn["nonValues"]) - 4);
        
        return $valueReturn;
    }
}
