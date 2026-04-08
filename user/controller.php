<?php

session_start();
error_reporting(E_ALL & ~ E_NOTICE);
include_once dirname(__DIR__) . '/config.php';
require_once __DIR__ . '/class.user.php';

class Controller
{
    private $user;

    function __construct() {
        $this->user = new USER();
        $this->processEmailVerification();
    }
    function processEmailVerification()
    {
        global $frname, $from;
        switch ($_POST["action"]) {
            
            case "get_otp":
                $email = trim((string)($_POST['email'] ?? ''));
                $accNo = trim((string)($_SESSION['acc_no'] ?? ''));
                $purpose = $accNo !== '' ? 'transfer' : 'email_verify';
                $otp = $this->user->createOtp($accNo, $email, $purpose, 10);
                $_SESSION['otp_email'] = $email;
                $_SESSION['otp_purpose'] = $purpose;
                $_SESSION['otp_acc_no'] = $accNo;
               
                try{
                    $retval = false;
                    if ($otp !== '') {
                        $retval = $this->user->send_mail($email, '', 'Email verification from Essence Capital Bank', 'otp_code', [
                            'otp' => $otp,
                            'expiry_min' => 10,
                        ]);
                    }
                    if($retval)
                    {
                        require_once('otp-verification.php');
                    }
                }
                
                catch(Exception $e)
                {
                    die('Error: '.$e->getMessage());
                }
 
                break;
                
            case "verify_otp":
                     $otp = trim((string)($_POST['otp'] ?? ''));
                     $email = trim((string)($_SESSION['otp_email'] ?? ''));
                     $purpose = trim((string)($_SESSION['otp_purpose'] ?? 'email_verify'));
                     $accNo = trim((string)($_SESSION['otp_acc_no'] ?? ''));
                
                     if ($this->user->verifyOtp($accNo, $email, $purpose, $otp)) 
                {
                         unset($_SESSION['otp_email'], $_SESSION['otp_purpose'], $_SESSION['otp_acc_no']);
                   echo json_encode(array("type"=>"success", "message"=>"Your Email is verified!"));
                } 
                else {
                    echo json_encode(array("type"=>"error", "message"=>"Mobile Email verification failed"));
                }
                break;
        }
    }
}
$controller = new Controller();
?>