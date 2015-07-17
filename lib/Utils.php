<?php
/**
 * Description of utils
 *  General tools to achieve general tasks.
 * @author abel
 */
class Utils {
    //Connecto to DB.
    public function dbConnection ($dbHost, $dbName, $dbUsername, $dbPass){
        try{
            $dbChain = 'mysql:host='.$dbHost.';dbname='.$dbName;
            $dbh = new PDO($dbChain, $dbUsername, $dbPass);
            $dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e){
                echo $e->getMessage();
                $errorCode = $e->getCode();
        }
        return $dbh;
    }
    //Check if the file was downloaded alredy and download if is not downloaded.
    public function checkIfExists($filePaths, $dbh){
        $doInser = array();
        foreach ($filePaths as $key => $filePath){
            try{
                $sql = "SELECT * FROM cfg_filesDownload WHERE email = :sender "
                        . "AND date = :dateSent "
                        . "AND group_name = :groupName";
                $senderMail = $filePath[2];
                $dateSent = new DateTime($filePath[3]);
                $group = $filePath[4];
                $fileName = $filePath[0];
                $filterPhrase = $filePath[1];
                $stmt = $dbh->prepare($sql);
                $stmt->bindParam(':sender', $senderMail);
                $stmt->bindParam(':dateSent', $dateSent->format('Y-m-d h:i:s'));
                $stmt->bindParam(':groupName', $group);
                $stmt->execute();
                if (isset($stmt)){
                    if ($stmt->rowCount() < 1) {
                        $this->writeFilesToDb($stmt, $dbh, $fileName, $filterPhrase, 
                            $senderMail, $dateSent, $group);
                            $doInser[] = "ToDo";
                    } else {
                        $doInser[] = "Done";
                    }
                }
            } catch (Exception $e){
                return 'Error: '.$e->getMessage();
            }
        }
        return $doInser;
    }
    //Inser file data 
    public function writeFilesToDb($stmt, $dbh, $fileName, $filterPhrase, 
                        $senderMail, $dateSent, $group){
                try{
                    $stmt = $dbh->prepare('INSERT INTO cfg_filesDownload(id, path, filter, '
                            . 'email, date, group_name) VALUES (NULL, :path, :filter, :email, '
                            . ':date, :groupName)');
                    $stmt->bindParam(':path', $fileName);
                    $stmt->bindParam(':filter', $filterPhrase);
                    $stmt->bindParam(':email', $senderMail);
                    $stmt->bindParam(':date', $dateSent->format('Y-m-d h:i:s'));
                    $stmt->bindParam(':groupName', $group);        
                    $stmt->execute();

                } catch (Exception $e){
                    echo 'Error: '.$e->getMessage();
                }
    }
}

    
//    public function replace3DEndHeader($filePaths, $subjectFilters, $lineStart){
//        $line = array();
//        $k=0;
//        foreach ($filePaths as $key => $value){
//            $sh=fopen($value[0], 'r');
//            $th=fopen($value[0].".bak", 'w');
//            $flag = 1;
//            $provisional = "";
//            $lineCount=1;            
//            while (!feof($sh)) {
//                $line=fgets($sh);
//                //$line = utf8_encode($line);
////                echo $line.PHP_EOL;
//                if($flag == 2){
//                    $line = $provisional." ".$line;
//                    $flag = 1;
//                }
//                if (strpos($line, "=3D")!==false) {
//                    $provisional = trim(str_replace(array("=3D"), '', $line));
//                    $flag = 2;
//                }
//                if ($flag == 1) fwrite($th, $line);
//                if($lineCount==3){
//                   $dateCreated = fgetcsv($sh, '', ',');
//                   if(isset($dateCreated)){
//                       if(isset($dateCreated[1])) $filePaths[$key][3]=$dateCreated[1];
//                   }
//                }
//                $thisLine = $lineStart[array_search($filePaths[$key][1], $subjectFilters)];
//                if ($lineCount == $thisLine-1){
//                   $groupSender = fgetcsv($sh, '', ',');
////                           var_dump($groupSender);
//                   if (isset($groupSender) && $groupSender[1]){
//                       $filePaths[$key][4]=$groupSender[1];
//                   }
//                }                    
//                $lineCount +=1;
//            }
//            fclose($sh);
//            fclose($th);
//            $sh=fopen($value[0].'.bak', 'r');
//            $th=fopen($value[0].".bak.1", 'w');
//            $flag = 1;
//            $provisional1 = "";
//            $lineCount=1;            
//            while (!feof($sh)) {
//                $line=  fgets($sh);
////                echo $line;
//                if($flag == 2){                    
////                    echo $provisional1.PHP_EOL;
////                    echo $line;
//                    $line = $provisional1." ".trim($line);
//                    $line = trim(preg_replace('/\s\s+/', ' ', $line)).PHP_EOL;
////                    echo $line;
//                    $flag =1;
////                    fwrite($th, $line);
//                } 
//                if ($flag == 1 && strpos($line, "=")!==FALSE){
////                    echo $line;
//                    $line = trim(str_replace(array("=", PHP_EOL), '', $line));                    
//                    $provisional1 = $line;
//                    $flag = 2;
//                }
////                echo $line;
//                if ($flag == 1) {
//                    fwrite($th, $line);
//                }
//            }
//        }  
//        return $filePaths;
//    }
