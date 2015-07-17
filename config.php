<?php
// E-mail configuration

// Define E-mail server data.
$mailServerChain = '{stu.stuff4less.ca:993/novalidate-cert/ssl}INBOX';
$mailServerShort = 'stu.stuff4less.ca/novalidate-cert';
$email = 'test@lasercorp.ca';
$emailPassword = 'CSVmysql063015';

// Software configuration
// Base directory
$baseDir = "/var/www/php/csvmail";
$savedirpath = "download";


//MySQL configuration
//$dbHost = "198.57.196.229";
//$dbUsername = "lcca_abel";
//$dbPass = "Abel063015";
//$dbName = "lcca_dataimport";
$dbHost = "localhost";
$dbUsername = "root";
$dbPass = "root";
$dbName = "lcca_dataimport01";


//Files and MySQL relationship
/* Add comma separated values */
$MySQLTables = ["tblSupplyData", "tblDeviceInventory"];
// Define different filter phrases for e-mails
$subjectFilters = ["HP Web Jetadmin Report - Supply Usage","Device Inventory"];
//LineStart
$lineStart = [[4,9,12],[4,7,10]];

/* Add comma separated valies between brackets for instace [["a","b","c"]
 ,["e","d","a"]] */
$MySQLOrderedColumns = [
    ["Device Model", "IP Hostname", "IP Address","MAC",
        "Port","PrinterID","Black Avg Coverage","Black Low Threshold",
        "Black Very Low Action","Page Count","Serial Number", "Status",
        "Supply Type","Supply Part Number","Supply Status","Supply Serial Number",
        "Supply Installation Date","Last Collection Date","Initial Supply Level",
        "Estimated Supply Level", "Supply Consumption","Average Supply Coverage",
        "Estimated Printed","Estimated Remaining","Supply Manufacturing Date"],
    ["DeviceModel","SerialNumber","AssetNumber","TotalColorImpress",
        "TotalMonoImpress","CopyPages","DigitalSendPages","EngineCycleCount",
        "DuplexImpress","OutgoingAnalogFaxCount","InventoryStatus","InventoryDate"]
    ];
$CSVOrderedColumns = [
    ["Device Model","IP Hostname","IP Address",
        "Hardware Address (MAC)","Port (Any)","Asset Number",
        "Black Average Coverage","Black Cartridge Low Threshold",
        "Black Cartridge Very Low Action","Engine Cycle Count",
        "Serial Number","Status","Supply","Supply Part Number",
        "Supply Status","Supply Serial Number","Supply Installation Date",
        "Last Collection Date","Initial Supply Level (%)",
        "Estimated Supply Level (%)","(%) Supply Consumption",
        "Average Supply Coverage %","Estimated Printed",
        "Estimated Remaining","Supply Manufacturing Date"],
    ["Device Model","Serial Number","Asset Number",
        "Total Color Impressions (Print Usage Counters.Any Size)",
        "Total Mono Impressions (Print Usage Counters.Any Size)",
        "Copy Pages","Digital Send Pages","Engine Cycle Count",
        "Duplex Impressions","Outgoing Analog Fax Count","Inventory Status",
        "Inventory Date"]
    ];

//require_once 'lib/firldsList.php';
