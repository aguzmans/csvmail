<?php
/**
 * Description of CsvFileInsert
 * @author abel
 */
class CsvFileInsert {
    public function testCsvFile($doInsets,$filePaths, $CSVOrderedColumns, 
            $subjectFilters, $lineStart, $dbh, $MySQLOrderedColumns, $MySQLTables){        
        foreach ($doInsets as $key => $status){
            if ($status == "ToDo"){
                if (($handle = fopen($filePaths[$key][0].".bak.1", "r")) !== FALSE) {
                    $count=1;
                    $CommandInsert = FALSE;
                    $poss = array();
                    $order = FALSE;
                    while (($data = fgetcsv($handle, 0, ",")) !== FALSE) {   
//                        echo '-'.$count.PHP_EOL;
                        if ($data[count($data)-1] ==''){
                            array_pop($data);
                        }
//                        var_dump($filePaths);
                        $realColumnsCSV = $CSVOrderedColumns[
                                        array_search($filePaths[$key][1], 
                                            $subjectFilters)];
//                        echo $filePaths[$key][1].PHP_EOL;
                        $thisLine = $lineStart[array_search($filePaths[$key][1], 
                                            $subjectFilters)];
//                        echo $thisLine."-".$count.PHP_EOL;
                        if ($count == $thisLine) {          
                            //var_dump($data,$realColumnsCSV);
                            if ($data === $realColumnsCSV) {
                                echo "Equals".PHP_EOL;
                                // Command insert to MySQL
                                $CommandInsert = TRUE;
                            } else {
//                                var_dump($data, $realColumnsCSV);
                                echo 'The CSV columns are not in the right order the file or is corrupted.'
                                .PHP_EOL. 'Trying to fix...'.PHP_EOL;
                                
                                //var_dump($realColumnsCSV);
                                foreach ($data as $key => $value){
                                    $poss[] = array_search($value, $realColumnsCSV);
                                }
                                //Order array and then insert. use $poss and $data.
                                $data = $this->orderArray($poss, $data);
                                $order = TRUE;                                
                            }    
                        } elseif ($count > $thisLine && $CommandInsert === TRUE) {
//                            var_dump($data);
                            //MySQL Insert
                            $this->insertToMySQL($data, $count, $dbh, 
                                $MySQLOrderedColumns[array_search($filePaths[$key][1], 
                                    $subjectFilters)], $MySQLTables[array_search($filePaths[$key][1], 
                                    $subjectFilters)]);
                        } elseif ($count > $thisLine && $order === TRUE) {
                            $data = $this->orderArray($poss, $data);
                            //MySQL Insert
                            $this->insertToMySQL($data, $count, $dbh, 
                                $MySQLOrderedColumns[array_search($filePaths[$key][1], 
                                    $subjectFilters)], $MySQLTables[array_search($filePaths[$key][1], 
                                    $subjectFilters)]);                            
                        }
                        $count +=1;                                
                    }   
                }             
            }

        }
    }
    public function insertToMySQL($data, $count, $dbh, $OrderedColumns, $tableMySQL){
        $data = $this->filterData($data,$dbh,$OrderedColumns, $tableMySQL); 
        if (count($data) > 5){
            $fielsNamesMySQL = implode("`, `", $OrderedColumns);
            $sql = "INSERT INTO tblSupplyData (id,`".$fielsNamesMySQL."`) VALUES (NULL,";
            foreach($data as $f=>$v){
                  $values[]=$v;                  
            } 
            try{
                $qs=str_repeat("?,",count($values)-1);
                $sql.="${qs}?);";
                $q=$dbh->prepare($sql);
                $q->execute($values);            
            } catch (PDOException $e){
                echo 'Error: '.$e->getMessage();
            }
        } else {
//            var_dump($data);
        }
    }
    public function filterData($data,$dbh,$MySQLOrderedColumns, $tableMySQL ){
        $types = $this->columnTypes($dbh, $MySQLOrderedColumns, $tableMySQL);
        array_shift($types);
        foreach ($data as $key => $value){
            if($types[$key] == "LONG" || $types[$key] == "TINY" || $types[$key] == "DOUBLE" || $types[$key] == "SHORT")
                    $data[$key] = $this->stringToNumber($data[$key]);
            if($types[$key] == "DATETIME"){
                    $data[$key] = $this->stringToDatetime($data[$key]);
            }
        }
        return $data;
    }
    public function columnTypes($dbh,$MySQLOrderedColumns, $tableMySQL){
        $count = count($MySQLOrderedColumns);
        $aux = array();
        for ($i = 0; $i <= $count; $i++){
            $select = $dbh->query('SELECT * FROM '.$tableMySQL.' LIMIT 1;');            
            $aux[] = $select->getColumnMeta($i)['native_type'];
        }        
        return $aux;
    }
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
    public function stringToDatetime($string){
        $string = trim($string);
        $string = str_replace(' ', '', $string);
        if(!$this->checkIsAValidDate($string)) {
            $string = "0/0/0000";    
        }

        $time = DateTime::createFromFormat('m/d/Y', $string)->format('Y-m-d h:i:s');
        return $time;
    }
    public function checkIsAValidDate($myDateString){
        return (bool)DateTime::createFromFormat('m/d/Y', $myDateString);
    }
    public function orderArray($poss, $data){
//        var_dump($data);
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