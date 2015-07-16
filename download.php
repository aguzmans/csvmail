<?php
/* 
 * Author: Abel Guzman 
 * Project Mail CSV Mysql converter
 * Version 0.0.01  * 2015
 */
require_once 'config.php';
require_once './vendor/autoload.php';
require_once './lib/MailDownload.php';
require_once './lib/Utils.php';
$utils = new Utils();
// Create connection to the mail server and download mails.
$server = new MailDownload($mailServerShort,993,$email,$emailPassword);
$attachmentPathNames = $server->downloadAttachment($savedirpath);

//take information header information from csv files.
include_once './lib/FileWorks.php';
foreach ($attachmentPathNames as $key =>$attachmentPathName){
    $dateAndGroup = array();
    foreach ($subjectFilters as $key1 => $subjectFilter){
        if (strpos($attachmentPathName[1], $subjectFilter) > 0 || strpos($attachmentPathName[1], $subjectFilter) === 0){
            $files = new FileWorks();
            echo 'File: '.$attachmentPathName[0].' Line Date: '.$lineStart[$key1][0].' Line Group: '.$lineStart[$key1][1].PHP_EOL;
            $dateAndGroup = $files->getDateAndGroup($attachmentPathName[0], $key1, $lineStart);
        }
    }
    if ($dateAndGroup[0] != ""){
        $attachmentPathNames[$key][3] =$dateAndGroup[0];
    }
    if ($dateAndGroup[1] != ""){
        $attachmentPathNames[$key][4] =$dateAndGroup[1];
    }
}
var_dump($attachmentPathNames);
//$files = new FileWorks($savedirpath);
//$filenames = $files->getFilenames();





