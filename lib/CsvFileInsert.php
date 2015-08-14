<?php
/**
 * Description of CsvFileInsert
 * @author abel
 */
class CsvFileInsert extends Utils {
    protected $filePaths;
    public function __construct($afilePath = NULL){
        if ($afilePath != NULL){
            $this->filePaths = $afilePath;
        }            
    }    
    /**
     * Clasify files to be inserted
     */
    public function classifyFile($doInsets, $afilePaths, $CSVOrderedColumns,
            $subjectFilters, $lineStart, $dbh, $MySQLTables, $MySQLOrderedColumns){
        $count = 1;
        foreach ($doInsets as $key => $status){
            if ($status == "ToDo"){
                $ColumnLine = $this->determineColumnAndLine($afilePaths, 
                        $CSVOrderedColumns, $subjectFilters, $key, $lineStart,$MySQLTables, $MySQLOrderedColumns);
                $afilePaths = $this->selectEqualColumns($ColumnLine, $afilePaths, $key); 
                $afilePaths[$key][6]=$ColumnLine[2];
                if($afilePaths[$key][5] == "Equals"){
                    $this->getDataToInsert($ColumnLine, $dbh, $afilePaths[$key]);
                } elseif ($afilePaths[$key][5] == "Different") {
                    $this->getDataToInsert($ColumnLine, $dbh, $afilePaths[$key],'different');  
                }
            }
            $count++;
        }
        $this->filePaths = $afilePaths;
    }
    private function getDataToOrderArry($data, $Columns){
        foreach ($data as $key => $value){
            $poss[] = array_search($value, $realColumnsCSV);
        }
        //Order array and then insert. use $poss and $data.
        $data = $this->orderArray($poss, $data);
        return $data;
    }

    /**
     * Order arrays when they are not equal
     * **/
    public function orderArray($poss, $data){
        $countPoss = count($poss);
        $countData = count($data);
        if ($countPoss === $countData){
            $merged_array = array_combine($poss, $data);
            // then sort array by keys and your words are in correct position
            ksort($merged_array);

            // update $array1 with sorted values 
            $data = array_values($merged_array);

        }else{
            return FALSE;
        }
        return $data;
    }
    private function getDifValues($poss, $data){
        $keys = array_keys($data);
        var_dump($keys);
        $different = array_diff($poss, $keys);
        return $different;
    }

    private function getDataToInsert($ColumnLine, $dbh, $filePath, $different = ""){
        
        if (($handle = fopen($filePath[0], "r")) !== FALSE) {
            $count=1;
            $poss = array();
            while (($data = fgetcsv($handle, 0, ",")) !== FALSE) {
                if ($different === ""){
                    if($count > $ColumnLine[1]){
                        $this->insertToMySQL($data, $dbh, $ColumnLine[3], $filePath[6],$filePath[3]);
                    }
                } else {
                    if($count == $ColumnLine[1]){
//                        var_dump($data,$ColumnLine[0]);
                        foreach ($data as $key => $value){
                                $poss[] = array_search($value, $ColumnLine[0]);
                        }
                        $diff = $this->getDifValues($poss, $data);
                        var_dump($diff);
                        if ($diff[0]>0){
                            foreach ($poss as $key => $value1){
                                if ($value1 == '' || $value1 == FALSE){
                                    $poss[$key] = $key;
                                    array_shift($diff);
                                }
                            }
                            var_dump($value1);
                        }
                    }
                    if($count > $ColumnLine[1]){
                        $data = $this->orderArray($poss, $data);
                        $this->insertToMySQL($data, $dbh, $ColumnLine[3], $filePath[6],$filePath[3]);
                    }
                }
                $count++;
            }            
        }
    }
/**
 *  Insert to MySQL table
 */
    private function insertToMySQL($data, $dbh, $OrderedColumns, $tableMySQL,$dateCreated){
        $data = $this->filterData($data,$dbh,$OrderedColumns, $tableMySQL);
        if (count($data) > 5){
            $fielsNamesMySQL = implode("`, `", $OrderedColumns);
            $sql = "INSERT INTO `$tableMySQL` (id,`".$fielsNamesMySQL."`, `DateCreated`) VALUES (NULL,";
            foreach($data as $f=>$v){
                  $values[]=$v;                  
            }
            $dateCreatedTime=  new DateTime($dateCreated);
            $values[] = $dateCreatedTime->format('Y-m-d h:i:s');
            try{
                $qs=str_repeat("?,",count($values)-1);
                $sql.="${qs}?);";
                $q=$dbh->prepare($sql);
                $q->execute($values);            
            } catch (PDOException $e){
                echo 'Error: '.$e->getMessage();
            }
        } 
    }  
    public function filterData($data,$dbh,$MySQLOrderedColumns, $tableMySQL ){
        $types = $this->columnTypes($dbh, $MySQLOrderedColumns, $tableMySQL);
        array_shift($types);
        if (is_array($data) && $data[count($data)-1]== NULL){
            array_pop($data);
        }
//        var_dump($data,$types);
        if (count($types) == count($data)){
            foreach ($types as $key => $value){
                //echo 'Field type: '.$value." Data: ".$data[$key].PHP_EOL;
                if($value == "LONG" || $value == "TINY" ||  $value == "DOUBLE" ||  $value == "SHORT" ||  $value == "SMALLINT" ){
//                                        if ($value == "SHORT") echo 'Number Before: '.$data[$key].PHP_EOL;
                    $data[$key] = $this->stringToNumber($data[$key]);
//                                        if ($value == "SHORT") echo 'Number After : '.$data[$key].PHP_EOL;

                }
                if($value == "DATETIME"){
                    //echo 'DateTime: '.$data[$key].PHP_EOL;
                    $data[$key] = $this->stringToDatetime($data[$key]);
                }
            }
        }
        return $data;
    }    
    /**
     * Mark file headers as equal to the config array or not
     */
    private function selectEqualColumns($ColumnLine,$afilePaths, $key){
        if (($handle = fopen($afilePaths[$key][0], "r")) !== FALSE) {
            $count=1;
            while (($data = fgetcsv($handle, 0, ",")) !== FALSE) {
                if ( $count == $ColumnLine[1]) {
                    array_pop($data);
                    if ($data === $ColumnLine[0]) {
                        $afilePaths[$key][5] = "Equals";
                        break;
                    }  else {
                        $afilePaths[$key][5] = "Different";
                        break;                        
                    }
                }
                $count++;
            }
        }
        return $afilePaths;
    }    
    /**
     * Determine columns for this file as well as start line, 
     */
    private function determineColumnAndLine($filePaths, $CSVOrderedColumns, 
            $subjectFilters, $key, $lineStart,$MySQLTables, $MySQLOrderedColumns){
        $ColumnLine = array();
        $aux = array_search($filePaths[$key][1], $subjectFilters);
        $realColumnsCSV = $CSVOrderedColumns[$aux];
        $aux1 = array_search($filePaths[$key][1], $subjectFilters);
        $thisLine = $lineStart[$aux1][2];

        $ColumnLine[0] = $realColumnsCSV;
        $ColumnLine[1] = $thisLine;
        $ColumnLine[2] = $MySQLTables[$aux];
        $ColumnLine[3] = $MySQLOrderedColumns[$aux];
        return $ColumnLine;
    }
    /**
     * Get the column types from the current MySQL table
     *      */
    public function columnTypes($dbh,$MySQLOrderedColumns, $tableMySQL){
        $count = count($MySQLOrderedColumns);
        $aux = array();
        for ($i = 0; $i <= $count; $i++){
            $select = $dbh->query('SELECT * FROM '.$tableMySQL.' LIMIT 1;');            
            $aux[] = $select->getColumnMeta($i)['native_type'];
        }        
        return $aux;
    }
    /** 
     * Convert a string to number
     */
    public function stringToNumber($string){
        $string = str_replace(',', '', $string);
        $string = str_replace('"', '', $string);
        if(isset($string) && $string !=''){
            if(strpos('%', $string)){
                if(strpos('<OK>', $string)){
                    $string = 100;
                }
            }
        }
        $string = str_replace('%', '', $string);
        switch ($string){
            case '':
                $string = 0;
            case '<No Value>':
                $string = 0;
            case '<Not supported>':
                $string = 0;
            case '--':
                $string = 0;
            default :
                $string;
        }
        return $string;
    }
    /** 
     *  Convert a string to a date
     *     */
    public function stringToDatetime($string){
        $string = trim($string);
        $string = str_replace(' ', '', $string);
        if(!$this->checkIsAValidDate($string)) {
            $string = "0/0/0000";    
        }

        $time = DateTime::createFromFormat('m/d/Y', $string)->format('Y-m-d h:i:s');
        return $time;
    }
    /**

     * Ends with is not used here now, reffer to MailDownload.php
     *      */
    public function checkIsAValidDate($myDateString){
        return (bool)DateTime::createFromFormat('m/d/Y', $myDateString);
    }
    public function insertArrayIndex($array, $new_element, $index) {
         /*** get the start of the array ***/
         $start = array_slice($array, 0, $index); 
         /*** get the end of the array ***/
         $end = array_slice($array, $index);
         /*** add the new element to the array ***/
         $start[] = $new_element;
         /*** glue them back together and return ***/
         return array_merge($start, $end);
     }
}
