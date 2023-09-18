<?php
// use PHPMailer\PHPMailer\PHPMailer;
// use PHPMailer\PHPMailer\Exception;

// require 'phpmailer/src/PHPMailer.php';
// require 'phpmailer/src/Exception.php';
// require 'phpmailer/src/SMTP.php';
require 'PHPMailer/PHPMailerAutoload.php';

require 'includes/PHPMailer.php';
require 'includes/SMTP.php';
require 'includes/Exception.php';

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;


function sendToURL($status, $message){
  header('location: http://' . $_SERVER['HTTP_HOST'] . '?sent=' . $status . '&msg=' . $message);
  //$_SERVER['HTTP_HOST']  ==> http://localhost  / http://vanititi.com/?sent=1&msg=????
}

//Create an instance; passing `true` enables exceptions
$email = new PHPMailer(true);

function sendMail($email){
  try {
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    // $mail->SMTPDebug = 2;
    $mail->CharSet = 'UTF-8';
    $mail->Host = 'node6884.myfcloud.com';
    $mail->SMTPAuth = 'ture';
    $mail->Username = 'no-reply@vanititi.com'; // Your email
    $mail->Password = 'elu!Wk-soQtR'; // gmail password
    $mail->Port = 465;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->setFrom( 'no-reply@vanititi.com' , 'Vanititi'); // Your email
    $mail->addAddress($email);
    $mail->Subject = 'Vanititi - Your are welcome #' . time();
    $mail-> isHTML(true);
    $mail->Body = '<div dir="rtl" lang="ar">شكراً لتسجيلكم!<br>يمكنكم استخدام كوبون الخصم 35% عند إطلاق الموقع، قريبا جداً.<br>كوبون الخصم هو:<br>Vanititi35 </div>';
    $mail-> send();
    } catch (Exception $e) {}
}

// try {
//   //Server settings
//   $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
//   $mail->isSMTP();                                            //Send using SMTP
//   $mail->Host       = 'smtp.example.com';                     //Set the SMTP server to send through
//   $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
//   $mail->Username   = 'user@example.com';                     //SMTP username
//   $mail->Password   = 'secret';                               //SMTP password
//   $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
//   $mail->Port       = 465;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

//   //Recipients
//   $mail->setFrom('from@example.com', 'Mailer');
//   $mail->addAddress('joe@example.net', 'Joe User');     //Add a recipient
//   $mail->addAddress('ellen@example.com');               //Name is optional
//   $mail->addReplyTo('info@example.com', 'Information');
//   $mail->addCC('cc@example.com');
//   $mail->addBCC('bcc@example.com');

//   //Attachments
//   $mail->addAttachment('/var/tmp/file.tar.gz');         //Add attachments
//   $mail->addAttachment('/tmp/image.jpg', 'new.jpg');    //Optional name

//   //Content
//   $mail->isHTML(true);                                  //Set email format to HTML
//   $mail->Subject = 'Here is the subject';
//   $mail->Body    = 'This is the HTML message body <b>in bold!</b>';
//   $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

//   $mail->send();
//   echo 'Message has been sent';
// } catch (Exception $e) {
//   echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
// }


// connect db
try{
  //CONNECT TO DATABASE
  $dbHost = "localhost";
  $dbName = "coming_soon";
  $dbChar = "utf8";
  $dbUser = "root";
  $dbPass = "";
  $error = null;
  $conn = null;
  try{
    $pdo = new PDO(
      "mysql:host=$dbHost; charset=$dbChar; dbname=$dbName;", $dbUser, $dbPass,
      [ 
        PDO::ATTR_ERRMODE => PDO::ERRMODE_SILENT,
        PDO::ATTR_PERSISTENT => false,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
      ]
    );
  }catch(PDOException $e){
    sendToURL(0, "الطلب غير صالح الآن! الرجاء المحاولة لاحقاً.");
  }
  // check post data
  if( isset($_POST['email']) && !empty($_POST['email']) ){
  // validate post data [ any issue or missing data will back to Homepage ]
    $email = $_POST['email'];
    $data = ['email' => $email];
    if(filter_var($email, FILTER_VALIDATE_EMAIL)){
      // check if duplicate email on db
      $query = $pdo->prepare("SELECT * FROM subscribers WHERE email = :email");
      $query->execute($data);
      $result = $query->fetch();  // true if we have the same email on our DB, false if not.
      if( !$result ){
        // if not, insert new record to db
        $query = $pdo->prepare("INSERT INTO subscribers (email) VALUES (:email) ");
        $insert = $query->execute($data);
        if( $insert ){
          //send email  <----
          // -- sendMail($email);
          //
          sendToURL(1, "شكرا لانضمامكم! ستصلكم رسالة تحتوي على كود الخصم قريبا");
        }else{
          sendToURL(0, "الطلب غير صالح الآن! الرجاء المحاولة لاحقاً.");
        }
      }else{
        sendToURL(0, "تم إدخال هذا الإيميل من قبل.");
      }
    }else{
      //back to homepage with error
      sendToURL(0, "الإيميل غير صالح.");
    }
  }else{
    //back to homepage with error
    sendToURL(0, "الرجاء إدخال الإيميل أولاً.");
  }
 }catch(Exception $e){
  sendToURL(0, "الطلب غير صالح الآن! الرجاء المحاولة لاحقاً.");
}
