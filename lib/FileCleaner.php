<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of FileCleaner
 *
 * @author abel
 */
class FileCleaner {
    public function deleteDownloadedFiles($savedirpath){
        $files = glob($savedirpath.'/*'); // get all file names
        foreach($files as $file){ // iterate files
          if(is_file($file))
            unlink($file); // delete file
        }
    }
}
