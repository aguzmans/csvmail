<?php
class ReadAttachment {
    function getdecodevalue($message,$coding) {
        echo $coding.PHP_EOL;
        switch($coding) {
            case 0:                    
            case 1:
                $message = imap_8bit($message);
                break;
            case 2:
                $message = imap_binary($message);
                break;
            case 3:                
            case 5:
                $message=imap_base64($message);
                break;
            case 4:
                $message = imap_qprint($message);
                break;
        }
        return $message;
    }
    function getdata($host,$login,$password,$savedirpath,$subjectFilters,$delete_emails=false) {
        // make sure save path has trailing slash (/)
        $savedirpath = str_replace('\\', '/', $savedirpath);
        if (substr($savedirpath, strlen($savedirpath) - 1) != '/') {
                $savedirpath .= '/';
        }		
        $mbox = imap_open ($host, $login, $password) or die("can't connect: " . imap_last_error());
        $message = array();
        $message["attachment"]["type"][0] = "text";
        $message["attachment"]["type"][1] = "multipart";
        $message["attachment"]["type"][2] = "message";
        $message["attachment"]["type"][3] = "application";
        $message["attachment"]["type"][4] = "audio";
        $message["attachment"]["type"][5] = "image";
        $message["attachment"]["type"][6] = "video";
        $message["attachment"]["type"][7] = "other";
        $filepath = array(array());
        $ki = 0;
        foreach ($subjectFilters as $subjectFilter){
            for ($jk = 1; $jk <= imap_num_msg($mbox); $jk++) {
                echo "---".$jk.PHP_EOL;
                $structure = imap_fetchstructure($mbox, $jk);
                if (isset($structure->parts) && $structure->parts !=''){
                    $parts = $structure->parts;
                    $fpos=2;
                    for($i = 1; $i < count($parts); $i++) {
                        $message["pid"][$i] = ($i);
                        $part = $parts[$i];
                        if(isset($part->disposition)){
                            if($part->disposition == "attachment" || $part->disposition == "ATTACHMENT") {
                                $message["type"][$i] = $message["attachment"]["type"][$part->type] . "/" . strtolower($part->subtype);
                                $message["subtype"][$i] = strtolower($part->subtype);
                                $ext=$part->subtype;
                                $params = $part->dparameters;
                                $filename=$part->dparameters[0]->value;
                                if (self::endsWith($filename, '.csv')){
                                    $mege="";
                                    $data="";
                                    $mege = imap_fetchbody($mbox,$jk,$fpos);
                                    $headerMail = imap_headerinfo($mbox, $jk);
                                    $filename="$filename";
                                    $currentSubject = $headerMail->subject;
                                    $currentSender = $headerMail->from[0]->mailbox . "@" . $headerMail->from[0]->host;
                                    if(strpos($currentSubject, $subjectFilter)!==FALSE){
                                        $filepath[$ki][0] = $savedirpath.$filename;
                                        $filepath[$ki][1] = $subjectFilter;
                                        $filepath[$ki][2] = $currentSender;
                                    } else {
                                        break;
                                    }
                                    $ki+=1;
                                    echo $savedirpath.$filename." - ".$ki.PHP_EOL;
                                    $fp=fopen($savedirpath.$filename,'w');
                                    fputs($fp,$mege);
                                    fclose($fp);
                                    $fpos+=1;
                                }
                            }
                        }
                    }
                }
                if ($delete_emails) {
                        // imap_delete tags a message for deletion
                        imap_delete($mbox,$jk);
                }
            }
        }
        // imap_expunge deletes all tagged messages
        if ($delete_emails) {
                imap_expunge($mbox);
        }
        imap_close($mbox);
        return $filepath;
    }

    private function endsWith($haystack, $needle) {
        // search forward starting from end minus needle length characters
        return $needle === "" || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== FALSE);
    }
    public function utf8_fopen_read($fileName) { 
        $fc = iconv('windows-1250', 'utf-8', file_get_contents($fileName)); 
        $handle=fopen("php://memory", "rw"); 
        fwrite($handle, $fc); 
        fseek($handle, 0); 
        return $handle; 
    } 
}