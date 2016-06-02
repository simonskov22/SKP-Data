<?php

class MultiTable{
    
    public $headerUsed = array();
    private $displayHeader = array();
    private $subheaderLines = array();
    public $tableArr = array();
    
    /**
     * vil tilføje en start værdi
     * vis valgte key ikke er oprettet
     * 
     * 
     * @param string/int $key
     * @param array &$array
     * @param mixed $value start værdi
     */
    private function keyNotExistsAdd($key, &$array, $value){        
                
        if(!key_exists($key, $array)){
            $array[$key] = $value;
        }
        
        
    }
    
    public function SetLinesForSubheader($header, $lines) {
        $this->subheaderLines[$header] = $lines;
    }
    
    /**
     * tæller hvormange gange en subheader
     * er blivet brugt
     * 
     * index &&tableMax&& inde holder den
     * højte værdi der er blivet brugt
     * 
     * 
     * @param int $index
     * @param string $header
     * @param string $subheader
     */
    private function AddToUsed($index, $header, $subheader) {
        
        //giv default værdier
        $this->keyNotExistsAdd("&&tableMax&&", $this->headerUsed, array());        
        $this->keyNotExistsAdd($index, $this->headerUsed, array());
        
        $this->keyNotExistsAdd($header, $this->headerUsed["&&tableMax&&"], array());
        
        $this->keyNotExistsAdd($header, $this->headerUsed[$index], array());        
        
        $this->keyNotExistsAdd($subheader, $this->headerUsed["&&tableMax&&"][$header], 1);        
        $this->keyNotExistsAdd($subheader, $this->headerUsed[$index][$header], 1);
        
        //tjek om der er kommet en ny højeste værdi
        $valueCurrent = &$this->headerUsed["&&tableMax&&"][$header][$subheader];
        $valueNew = count($this->tableArr[$index][$header][$subheader]);
        $this->headerUsed[$index][$header][$subheader] = $valueNew;
        
        if($valueCurrent < $valueNew){
            $valueCurrent = $valueNew;
        }
    }
    
    /**
     * gør så alle tabler har de samme
     * header, subheader selv og de ikke
     * er sat
     */
    private function UpdateTableHeaders() {
        
        //antal kolonner
        $tableCount = count($this->tableArr);
        
        //kør igennem alle header og subhheader og at se om den har 
        //nok værdier
        foreach ($this->headerUsed["&&tableMax&&"] as $header => $subheaderArr) {
            
            foreach ($subheaderArr as $subheader => $valNeed) {
                
                for($i = 0; $i < $tableCount; $i++){
                    
                    //tjek om de er sat ellers giv en værdi
                    $this->keyNotExistsAdd($header, $this->tableArr[$i], array());
                    $this->keyNotExistsAdd($subheader, $this->tableArr[$i][$header], array());
                    
                    
                    //tjek om de har nok værdier
                    $valNow = count($this->tableArr[$i][$header][$subheader]);
                    
                    for($a = $valNow; $a < $valNeed ; $a++){
                        $this->tableArr[$i][$header][$subheader][] = "&nbsp;";
                    }
                }
            }            
        }
    }
    
    /**
     * giver mulighed for at tilføj
     * en teskt eller html i toppen
     * af values colomn
     * 
     * @param int $index
     * @param string $text
     */
    public function AddValueDisplayedHeader($index, $text) {
        $this->displayHeader[$index] = $text;
    }
            
    /**
     * tilføj en værdi til en kolonne
     * 
     * @param int $index
     * @param string $header
     * @param string $subheader
     * @param string $value
     */
    public function AddValue($index, $header, $subheader, $value) {
        
        $header = $header == "" ? "&nbsp;": $header;
        $subheader = $subheader == "" ? "&nbsp;": $subheader;
        $value = $value == "" ? "&nbsp;": $value;
        
        
        //tjek om de er oprettet hvis ikke opret dem
        $this->keyNotExistsAdd($index, $this->tableArr, array());
        $this->keyNotExistsAdd($header, $this->tableArr[$index], array());
        $this->keyNotExistsAdd($subheader, $this->tableArr[$index][$header], array());
        
        
        //tilføj ny værdi
        $this->tableArr[$index][$header][$subheader][] = $value;
        
        //opdatere header brugt
        $this->AddToUsed($index, $header, $subheader);
    }
    
    /**
     * vil lave en bedre table order
     * som vil kunne blive udskrvet ved hjælp af et loop
     * 
     * dette kan være nødvendigt da $this->tableArr ikke 
     * nødvendigt er samme rækkefølge
     */
    private function BetterTableOrder() {
        //opdatere kolonner så de har de samme header og subheader
        $this->UpdateTableHeaders();
        
        
        //vil indeholde hele tabellen som man vil kunne
        //udskriv ved hjælp af foreach loops
        $multiTable = array();
        
        //hvor mange kolonner der er (uden subheader kolonne)
        $columnCount = count($this->tableArr);
        
        
        //kør igennem for vær header og subheader
        foreach ($this->headerUsed["&&tableMax&&"] as $header => $subheaderArr) {
            
            //opret header
            $multiTable[$header] = array();
            
            foreach ($subheaderArr as $subheader => $value) {
                
                //opret subheader
                $multiTable[$header][$subheader] = array();
                
                //giv værdig til alle kolonner 
                //(ud over subheader kolonne da denne er i keys navne)
                for($colmunId = 0; $colmunId < $columnCount; $colmunId++){
                    //opret kolonne
                    $multiTable[$header][$subheader][$colmunId] = array();
                    
                    //giv værdier til subhead
                    foreach ($this->tableArr[$colmunId][$header][$subheader] as $value) {
                        $multiTable[$header][$subheader][$colmunId][] = $value;
                    }
                }
            }
        }
        
        //send tilbage den nye tabel order
        return $multiTable;
    }
    
    /**
     * Vil udskrive hele tabellen
     */
    public function PrintTable(){
       
        //hent table værdierne
        $multiTable = $this->BetterTableOrder();
        //antal kolonner (uden subheader kolonne)
        $columnCount = count($this->tableArr);
        //vil blive brugt til at kunne sætte display header på
        $loopMulti = -1;
        
        
        
        //giv start værdi og samtid oprette kolonnerne
        $elementBuild = array();
        
        for($i = 0; $i < $columnCount +1; $i++){
            $elementBuild[] = "<ul>";
        }
        
        
        foreach ($multiTable as $header => $subheaderArr) {
            //antal gange foreach har kørt
            $loopMulti++;
            
            $subheaderRun = 0;
            
            $subCount = count($subheaderArr);
            
            //find udaf hvormange værdier der er i subheader
            $subheaderKeys = key($subheaderArr);                        
            $subValCount = count($subheaderArr[$subheaderKeys][0]);
                        
            //så der kan komme små header med samme subheader
            for($extra = 0; $extra < $subValCount; $extra++){
                
                //skal ikke gentag
                if($subCount == 1 && $extra > 0){
                    break;
                }
            
                //opretter header og gør klar til at modtag <li> værdier
                for($i = 0; $i < $columnCount +1; $i++){
                    //hvad der skal står i header og class
                    if($i == 0){
                        $eHeader = $extra == 0 ? $header : "&nbsp;";
                        $eClassHead = $extra == 0 ? "header" : "header small";
                        $eClass = "subheader";
                    }
                    else{
                        
                        $hasHeader = $loopMulti == 0 && key_exists($i -1, $this->displayHeader);
                        
                        $eHeader = !$hasHeader ? "&nbsp;" : $this->displayHeader[$i-1];                        
                        $eClass = "values";

                    }

                    //tilføj teksten til at start på 
                    $elementBuild[$i] .= "<li class='$eClassHead'>$eHeader</li>";
                    $elementBuild[$i] .= "<li class='$eClass'>";
                    $elementBuild[$i] .= "<ul>";
                }
                
                //tilføj <li> værdier til kolonnerne
                foreach ($subheaderArr as $subheader => $value) {
                    
                    //lav ny linje i samme kategori
                    if(array_key_exists($header, $this->subheaderLines)){
                        if($subheaderRun == $this->subheaderLines[$header]){
                            $subheaderRun = 0;
                            
                            for($i = 0; $i < $columnCount +1; $i++){
                                $eClass = $i == 0 ? "subheader" : "values";
                                
                                $elementBuild[$i] .= "</ul>";
                                $elementBuild[$i] .= "</li>";
                                
                                $elementBuild[$i] .= "<li class='header small'>&nbsp;</li>";
                                $elementBuild[$i] .= "<li class='$eClass'>";
                                $elementBuild[$i] .= "<ul>";
                            }                            
                        }
                    }
                    
                    for($column = 0; $column < $columnCount; $column++){
                        
                        for($valId = 0; $valId < $subValCount; $valId++){
                            if($column == 0){
                                $elementBuild[0] .= $valId == 0 ? "<li>$subheader</li>" : "<li>&nbsp;</li>";
                            }
                            
                            $elementBuild[$column +1] .= "<li>{$value[$column][$valId]}</li>";
                            
                            //hvis der er flere subheader i en header skal
                            //skal den ikke udskrive dem på samme "box"
                            if($subCount != 1){
                                break;
                            }
                        }
                    }
                    
                    $subheaderRun++;
                }

                //afslut header
                for($i = 0; $i < $columnCount +1; $i++){
                    $elementBuild[$i] .= "</ul>";
                    $elementBuild[$i] .= "</li>";
                }
            }
        }
        //afslut kolonnerne
        for($i = 0; $i < $columnCount +1; $i++){
            $elementBuild[$i] .= "</ul>";            
        }
        
        //udskriv tabellen
        echo "<div class='multiTable'>";
        foreach ($elementBuild as $element) {
            echo $element;
        }
        echo "</div>";
        
    }
    
}