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
/*1 Create connection to the mail server and download mails.*/
$server = new MailDownload($mailServerShort,993,$email,$emailPassword);
$attachmentPathNames = $server->downloadAttachment($savedirpath);
//var_dump($attachmentPathNames);
/*
 * 1.1take information header information (Date,Group) from csv files.
 */
include_once './lib/FileWorks.php';
foreach ($attachmentPathNames as $key =>$attachmentPathName){
    $dateAndGroup = array();
    foreach ($subjectFilters as $key1 => $subjectFilter){
//        echo 'Path: '.$attachmentPathName[1].' Subject: '.$subjectFilter.' Poss: '.strpos($attachmentPathName[1], $subjectFilter).PHP_EOL;
        if (strpos($attachmentPathName[1], $subjectFilter) > 0 || strpos($attachmentPathName[1], $subjectFilter) === 0){
            $attachmentPathNames[$key][1] = $subjectFilter;
            $files = new FileWorks();
            echo 'File: '.$attachmentPathName[0].' Line Date: '.$lineStart[$key1][0].' Line Group: '.$lineStart[$key1][1].PHP_EOL;
            $dateAndGroup = $files->getDateAndGroup($attachmentPathName[0], $key1, $lineStart);
        }
    }
    if(isset($dateAndGroup[1])){
        if ($dateAndGroup[0] != ""){
            $attachmentPathNames[$key][3] =$dateAndGroup[0];
        }
        if ($dateAndGroup[1] != ""){
            $attachmentPathNames[$key][4] =$dateAndGroup[1];
        }
    }
}
/*
 * Remove the files that have no date or do not have the right subject
 * **/
foreach ($attachmentPathNames as $key => $value ){
    if (count($value)<5){
        unset($attachmentPathNames[$key]);
    }
}
$attachmentPathNames = array_values($attachmentPathNames);

/* 2.1- Check that those files have not been received already*/
/* 2.2- Save data to MySQL */
$dbh = $utils->dbConnection($dbHost, $dbName, $dbUsername, $dbPass);
$doInsets = $utils->checkIfExists($attachmentPathNames, $dbh);

var_dump($doInsets);

/* 3- Open files one by one. */
/* 4- Read using the content; use the variable $CSVOrderedColumns. */
/* 5- Once read, put the content line by line in the MySQL DB, use variable $MySQLOrderedColumns */
require_once './lib/CsvFileInsert.php';
$CsvWorks = new CsvFileInsert();
$CsvWorks->classifyFile($doInsets, $attachmentPathNames, $CSVOrderedColumns,
            $subjectFilters, $lineStart, $dbh ,$MySQLTables, $MySQLOrderedColumns);


//require_once './lib/FileCleaner.php';
//$files = new FileCleaner();
//$files->deleteDownloadedFiles($savedirpath);
