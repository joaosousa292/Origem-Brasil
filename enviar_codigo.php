<?php

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/config_email.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function enviarCodigo($destinatario, $codigo)
{
    try {

        $mail = new PHPMailer(true);

        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USER;
        $mail->Password = SMTP_PASS;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = SMTP_PORT;

        $mail->CharSet = 'UTF-8';

        $mail->setFrom(
            SMTP_USER,
            'Origem Brasil'
        );

        $mail->addAddress($destinatario);

        $mail->isHTML(true);

        $mail->Subject = 'Código de recuperação de senha';

        $mail->isHTML(true);

        $mail->Body = "
        
        <div style='
        max-width:600px;
        margin:auto;
        font-family:Poppins,Arial,sans-serif;
        background:#ffffff;
        border-radius:20px;
        overflow:hidden;
        border:1px solid #E5E7EB;
        '>
        
        <div style='
        background:#2C4A2E;
        padding:30px;
        text-align:center;
        color:white;
        '>
        
        <h1 style='margin:0'>
        Origem Brasil
        </h1>
        
        </div>
        
        <div style='padding:40px'>
        
        <h2 style='color:#1C1208'>
        Recuperação de Senha
        </h2>
        
        <p style='color:#666'>
        Recebemos uma solicitação para redefinir sua senha.
        </p>
        
        <p style='color:#666'>
        Utilize o código abaixo:
        </p>
        
        <div style='
        background:#2C4A2E;
        color:white;
        font-size:34px;
        font-weight:bold;
        letter-spacing:10px;
        padding:20px;
        border-radius:12px;
        text-align:center;
        margin:25px 0;
        '>
        $codigo
        </div>
        
        <p style='color:#666'>
        Este código expira em 10 minutos.
        </p>
        
        <p style='color:#999;font-size:13px'>
        Caso você não tenha solicitado esta alteração, ignore este e-mail.
        </p>
        
        </div>
        
        </div>
        
        ";

        return $mail->send();

    } catch (Exception $e) {

        return false;

    }
}