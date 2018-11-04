<?php
/**
 * Created by PhpStorm.
 * User: giovani
 * Date: 11/4/18
 * Time: 7:06 PM
 */

namespace GmailApi;

use Google_Client;

class Gmail
{

    public $client;
    public $gmail;

    public function __construct($tokenPath)
    {
        $this->getClient($tokenPath);
        $this->gmail = $this->getGmail();
        return $this;
    }

    public function getUser(): \Google_Service_Gmail_Profile
    {
        return $this->getGmail()->users->getProfile("me");
    }

    /**
     * @param $to
     * @param $subject
     * @param $body
     * @return \Google_Service_Gmail_Message
     * @throws \PHPMailer\PHPMailer\Exception
     */
    public function send($to, $subject, $body): \Google_Service_Gmail_Message
    {
        $message = $this->createMessage($to, $subject, $body);
        return $this->gmail->users_messages->send("me", $message);
    }

    /**
     * Get client
     * @param string $tokenPath
     * @return Google_Client
     * @throws \Google_Exception
     */
    private function getClient(string $tokenPath): Google_Client
    {
        $client = new Google_Client();
        $client->setApplicationName('Gmail API PHP Quickstart');
        $client->setScopes(\Google_Service_Gmail::MAIL_GOOGLE_COM);
        $client->setAuthConfig('credentials.json');
        $client->setAccessType('offline');
        $client->setPrompt('select_account consent');

        // Load previously authorized token from a file, if it exists.
        if (file_exists($tokenPath)) {
            $accessToken = json_decode(file_get_contents($tokenPath), true);
            $client->setAccessToken($accessToken);
        }
        return $this->client = $client;
    }

    /**
     * @return \Google_Service_Gmail
     */
    private function getGmail(): \Google_Service_Gmail
    {
        $service = new \Google_Service_Gmail($this->client);
        return $this->gmail = $service;
    }

    /**
     * @param $to
     * @param $subject
     * @param $body
     * @param array $attachment
     * @return \Google_Service_Gmail_Message
     * @throws \PHPMailer\PHPMailer\Exception
     */
    private function createMessage($to, $subject, $body, $attachment = array()): \Google_Service_Gmail_Message
    {
        $mail = new \PHPMailer\PHPMailer\PHPMailer();
        $user = $this->getUser();
        $mail->CharSet = 'UTF-8';
        $mail->From = $user->getEmailAddress();
        $mail->FromName = $user->getEmailAddress();
        $mail->addAddress($to);
        $mail->Subject = $subject;
        $mail->Body = $body;
        // set this dinamically
        $mail->IsHTML(true);
        if (!empty($attachment)) {
            foreach ($attachment as $key => $value) {
                $attachmentParams = array_combine(['name', 'tmpName'], $value);
                $mail->addAttachment($attachmentParams['tmpName'], $attachmentParams['name']);
            }
        }
        $mail->preSend();
        $mime = $mail->getSentMIMEMessage();
        $raw = $this->Base64UrlEncode($mime);
        $message = new \Google_Service_Gmail_Message();
        $message->setRaw($raw);
        return $message;
    }

    private function base64UrlDecode($string)
    {
        return base64_decode(str_replace(array('-', '_'), array('+', '/'), $string));
    }

    /**
     * Returns a web safe base64 encoded string, used for encoding
     * @param String $string The string to be encoded
     * @return String Encoded string
     */
    private function Base64UrlEncode($string)
    {
        return rtrim(strtr(base64_encode($string), '+/', '-_'), '=');
    }
}