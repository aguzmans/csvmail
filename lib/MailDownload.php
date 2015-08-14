<?php
/**
 * Description of MailDownload
 * This class handles the csv attachments download from a mailbox.
 * @author abel
 */
class MailDownload {
    /*Connection to server*/
    private $server;
    /*Create connection to e-mail server*/
    public function __construct($server, $port=993, $user, $password){
        $this->server = new \Fetch\Server($server, $port);
        $this->server->setAuthentication($user, $password);        
    }
    public function download1Mail($messages, $fileMetadata){
        foreach ($messages as $key => $message) {
            echo '#: '.$key.PHP_EOL;
            $fileMetadata[$key][1] = $this->imap_utf8_fix($message->getSubject());
            $fileMetadata[$key][2] = $message->getAddresses('from')["address"];            
            $attachments = $message->getAttachments();
            $filename = FALSE;
            if(is_array($attachments)){
                $filename = $this->saveAttachment($attachments,$savedirpath);
            }
            if($filename !== FALSE){
                $fileMetadata[$key][0] = $filename;
            }
        }
        return $fileMetadata;        
    }
    /*Download attachment message by message*/
    public function downloadAttachment($savedirpath){
        $messages = $this->server->getMessages();
        //Files Data for DB
        $fileMetadata = array(array());
        $fileMetadata = $this->download1Mail($messages, $fileMetadata);
        $fileMetadata = $this->removeSmallArrayMultidim($fileMetadata, 3);
        return $fileMetadata;
    }
    private function imap_utf8_fix($string) {
        return iconv_mime_decode($string,0,"UTF-8");
    }     
    /*save attachments only if they are csv files*/
    private function saveAttachment($attachments,$savedirpath){
        foreach ($attachments as $attachement){
            $filename = $savedirpath."/".$attachement->getFileName();
            if (self::endsWith($filename, '.csv')){
                $filename .= ".".rand(1000, 9999);
                $attachement->saveAs($filename);                    
            } else {
                $filename = FALSE;                
            }
        }
        return $filename;
    }
    /* Determine if a string ends with another string*/
    private function endsWith($haystack, $needle) {
        // search forward starting from end minus needle length characters
        return $needle === "" || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== FALSE);
    }
    private function removeSmallArrayMultidim($array,$small){
        $aux = $array;
        foreach ($array as $key => $arr){
            if(count($arr) < $small){
                unset($aux[$key]);                
            }            
        }
        $aux = array_values($aux);
        return $aux;
    }
}
