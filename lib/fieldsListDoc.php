<?php
/**
 * Description of firldsList
 * @author Abel Guzman
 */
class fieldsListDoc {
    private $mysqlTable;
    private $csvKey;
    private $mysqlColumns;
    private $csvColumns;
    public function __construct() {
        $this->mysqlColumns = array();
        $this->csvColumns = array();
    }
    public function setValues($aMysqlTable, $aCsvKey, $aMysqlColumns, $aCsvColumns){
        $this->mysqlColumns = $aMysqlColumns;
        $this->csvColumns = $aCsvColumns;
        $this->mysqlTable = $aMysqlTable;
        $this->csvKey = $aCsvKey;
    }
    public function getMysqlTable(){
        return $this->mysqlTable;
    }
    public function getCsvKey(){
        return $this->csvKey;
    }
    public function getCsvColumns(){
        return $this->csvColumns;
    }
    public function getMysqlColumns (){
        return $this->mysqlColumns;
    }
}
