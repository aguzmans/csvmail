<?php
/**
 * Description of FileWorks
 *
 * @author abel
 */
class FileWorks {
    private $filenames;

    public function __construct($savedirpath=NULL) {
        if(!is_null($savedirpath)){
             $scan = scandir($savedirpath);
             $scan = $this->removeFromArray($scan, array(".",".."));    
             $this->filenames = $scan;
        }
    }
    public function getDateAndGroup($filePath,$key1, $lineStart){
        $handle = fopen($filePath, "r");
        $count = 1;
        $array = array();
        while (!feof($handle)) {
            if ($handle == NULL){
                break;
            }
            $line = fgetcsv($handle,'',',');
            if ($count == $lineStart[$key1][0]){
                $array[0]=$line[1];

            }
            if ($count == $lineStart[$key1][1]){
                $array[1]=$line[1];
                break;
            }
            $count++;
        }
        fclose($handle);
        return $array;
    }
    
    private function removeFromArray($arr, $removes){
        foreach ($removes as $remove){
            $poss = array_search($remove, $arr);
            if ($poss >= 0){
                unset($arr[$poss]);
                $arr = array_values($arr);
            }
        }
        return $arr;
    }    
    public function getFilenames(){
        return $this->filenames;
    }
}
