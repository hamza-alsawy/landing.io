<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/Exception.php';
require 'phpmailer/src/SMTP.php';

function sendToURL($status, $message){
  header('location: http://' . $_SERVER['HTTP_HOST'] . '?sent=' . $status . '&msg=' . $message);
  //$_SERVER['HTTP_HOST']  ==> http://localhost  / http://vanititi.com/?sent=1&msg=????
}

function sendMail($email){
  try {
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->SMTPDebug = 2;
    $mail->CharSet = 'UTF-8';
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = 'ture';
    $mail->Username = 'hamza.loftry@gmail.com'; // Your email
    $mail->Password = 'getto_Hamza@loftry22'; // gmail password
    $mail->Port = 465;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->setFrom( 'no-reply@vanititi.com' , 'Vanititi'); // Your email
    $mail->addAddress($email);
    $mail->Subject = 'Vanititi - Your are welcome #' . time();
    $mail-> isHTML(true);
    $mail->Body = '<div dir="rtl" lang="ar">شكراً لتسجيلكم!<br>يمكنكم استخدام كوبون الخصم 35% عند إطلاق الموقع، قريبا جداً.<br>كوبون الخصم هو:<br>Vanititi35 </div>';
    $mail-> send();
    } catch (Exception $ex) {}
}

// connect db
try{
  //CONNECT TO DATABASE
  $dbHost = "localhost";
  $dbName = "turkishfurniture_vanititi";
  $dbChar = "utf8";
  $dbUser = "turkishfurniture_vanititi";
  $dbPass = "sDNypQHOO6Ig";
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
          sendMail($email);
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