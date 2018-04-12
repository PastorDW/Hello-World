<?php
include("../php/common.php");
include("common.php");
include("connection.php");
include("../PHPMailer/class.phpmailer.php");
include("wwfprocess.php");
$debug = 0;
$test = $_GET['test'];

$DOWNLOADS = array();


checkInbox("wwf");

function checkInbox($list)
{
  global $test, $DOWNLOADS, $uid;
  
  $hostname = "{localhost:995/pop3/ssl/novalidate-cert}";
  $password = 'prodigy23@#';
  
  $inbox = imap_open($hostname,"$list@dwwd.me",$password) or die('Cannot connect: ' . imap_last_error());
  $emails = imap_search($inbox,'ALL');

  if($emails) 
  {
	
	  /* for every email... */
	  foreach($emails as $email_number) 
	  {
		
			/* get information specific to this email */
			$overview = imap_fetch_overview($inbox,$email_number,0);
			$from = $overview[0]->from;
			$subj = $overview[0]->subject;
			
			if(strpos($from, "<"))
			{
			  $from = substr($from, strpos($from, "<")+1);
			  $from = str_replace(">", "", $from);
			  $fromType = "email";
			}
			else
			  $fromType = "text";

			//var_dump($overview);
		
			//record all incoming for troubleshooting unanticipated differences
			$bodyall = addslashes(imap_body($inbox, $email_number));
			$body1 = addslashes(imap_fetchbody($inbox, $email_number, 1));
			$body2 = addslashes(imap_fetchbody($inbox, $email_number, 2));	
			if(strpos($bodyall, 'BASE64') || strpos($bodyall, 'base64')) 
			  $body1 = addslashes(imap_base64($body1));	
			  
			$fbody = getBody($from, $body1, $body2);
			
			
      decho("<hr></hr>");
			decho("From: $from");
			decho("Subj: $subj");
			//decho("Body1: $body1");
			//decho("Body2: $body2");
			//decho("Body:$fbody");
			
			processAttachments($inbox, $email_number, $from); 		
								
			//delete the email
			 imap_delete($inbox, $email_number);
	  }
	  
	//clear the inbox
	  imap_expunge($inbox);
	
  } 

/* close the connection */
imap_close($inbox);

foreach($DOWNLOADS as $D)
{
  //0 = uid
  //1 = from
  $uid = $D[0];
  $from = $D[1];  
  processImage();
	processBoard(5);
	
	if(file_exists("wwfwords/$uid.txt"))
	{
	  //$file = fopen("wwfwords/$uid.txt", "r");
	  
	  //for($i = 0; $i < 5; $i++)
	  //{
	    $words = file_get_contents("wwfwords/$uid.txt");
	    sendWordImages($words, $from, $uid);
	    
	    for($i = 0; $i < 5; $i++)	    
	      unlink("wwfplays/$uid-$i.jpg");
	  //}
	  
	  //fclose($file);
	}
	
	unlink("wwfuploads/$uid.jpg");

	
	
}

}

function sendWordImages($words, $to, $uid)
{
  try{
  $email = new PHPMailer();

	$email->AddAddress($to);	
	$email->setFrom("wwf@dwwd.me");
	$email->Body = $words;
	
	
	for($i = 0; $i < 5; $i++)
    $email->AddAttachment("wwfplays/$uid-$i.jpg");
	//$email->AddEmbeddedImage($file_to_attach, "myimg");
	//$email->Body = '<img src="cid:myimg" />';
	
	return $email->send();
	
	}catch(phpmailerException $e){
	  echo sprintf('<p>A phpmailer error occurred: <code>%s</code></p>',
	      htmlspecialchars($e->getMessage()));
	}

}

//decho("</pre>");


//read all attachments and process as a video if they meet the file extension criteria
function processAttachments($inbox, $email_number, $from)
{
  
  global $DOWNLOADS, $uid;	
	
  
  $structure = imap_fetchstructure($inbox, $email_number);

  $attachments = array();
  if(isset($structure->parts) && count($structure->parts)) {

      for($i = 0; $i < count($structure->parts); $i++) {

          $attachments[$i] = array(
              'is_attachment' => false,
              'filename' => '',
              'name' => '',
              'attachment' => ''
          );

          if($structure->parts[$i]->ifdparameters) {
              foreach($structure->parts[$i]->dparameters as $object) {
                  if(strtolower($object->attribute) == 'filename') {
                      $attachments[$i]['is_attachment'] = true;
                      $attachments[$i]['filename'] = $object->value;
                  }
              }
          }

          if($structure->parts[$i]->ifparameters) {
              foreach($structure->parts[$i]->parameters as $object) {
                  if(strtolower($object->attribute) == 'name') {
                      $attachments[$i]['is_attachment'] = true;
                      $attachments[$i]['name'] = $object->value;
                  }
              }
          }

          if($attachments[$i]['is_attachment']) {
              $attachments[$i]['attachment'] = imap_fetchbody($inbox, $email_number, $i+1);
              if($structure->parts[$i]->encoding == 3) { // 3 = BASE64
                  $attachments[$i]['attachment'] = base64_decode($attachments[$i]['attachment']);
              }
              elseif($structure->parts[$i]->encoding == 4) { // 4 = QUOTED-PRINTABLE
                  $attachments[$i]['attachment'] = quoted_printable_decode($attachments[$i]['attachment']);
              }
          }
      }
  }
  

  foreach ($attachments as $key => $attachment) {
      $name = strtolower($attachment['name']);
      $contents = $attachment['attachment'];
      if($name)
      {
        decho($name);
        $ext = substr($name, strrpos($name, "."));
        decho($ext);
        
        $uid = uniqid();
        
        file_put_contents("wwfuploads/$uid.jpg", $contents);
          
        
        //echo "$uid<br /><br />";
        $DOWNLOADS[] = array($uid, $from);
        
        
            
      }
  }
  
}

?>