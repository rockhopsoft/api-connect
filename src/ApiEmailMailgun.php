<?php
/**
  * ApiEmailMailgun
  *
  *
  * Survloop - All Our Data Are Belong
  * @package  rockhopsoft/survloop
  * @author   Morgan Lesko <rockhoppers@runbox.com>
  * @since v0.4.0
  */
namespace RockHopSoft\ApiConnect;

use Auth;
use Mailgun\Mailgun;
use RockHopSoft\ApiConnect\ApiEmail;

class ApiEmailMailgun extends ApiEmail
{
    private $mailgunDomain   = '';
    private $mailgunEndpoint = '';
    private $mailgunUser     = '';

    /**
     * Pull the Census API key from the software's installation environment.
     *
     * @return void
     */
    protected function load_key_from_env()
    {
        $this->set_key(env('MAILGUN_SECRET', ''));
        $this->mailgunDomain   = env('MAILGUN_DOMAIN',    '');
        $this->mailgunEndpoint = env('MAILGUN_ENDPOINT', 'api.mailgun.net');
        $this->mailgunUser     = env('MAIL_USERNAME',     '');
        $this->apiBaseUrl      = "https://api.mailgun.net/v3/"
            . $this->mailgunDomain . "/messages";
    }

    /**
     * Get list of variables to import through this API.
     *
     * @return boolean
     */
    public function sendEmail($body = '', $subject = '', 
        $to = [], $cc = [], $bcc = [], $repTo = [], $attach = [])
    {
        $success = true;
        $emailRepTo = $this->getReplyTo($repTo);
        //$emailRepTo = $this->getReplyToEmail($repTo);
//echo '<script type="text/javascript"> alert(\'sendEmail defFromEmail: "' . $emailRepTo . '"\'); </script>';
//echo 'sendEmail, emailRepTo: ' . $emailRepTo . '<br />'; exit;
        if (sizeof($to) == 0) {
            $success = false;
        } else {
            $endpoint = 'https://' . $this->mailgunEndpoint;
            $mg = Mailgun::create($this->apiKey, $endpoint);
            foreach ($to as $i => $emailTo) {
                $emailSend = trim(sl()->emailArrToStr($emailTo));
                //$emailSend = $emailTo[0];
if (Auth::user() && Auth::user()->id == 1) { echo 'sendEmail mailgunDomain: ' . $this->mailgunDomain . '<br />apiKey: ' . $this->apiKey . '<br />mailgunEndpoint: https://' . $this->mailgunEndpoint . '<br />emailRepTo: ' . $emailRepTo . '<br />emailSend: ' . $emailSend . '<br />subject: ' . $subject . '<br />to:<pre>'; print_r($to); echo '</pre>'; }
                $params = [
                    'from'    => $emailRepTo,
                    'to'      => $emailSend,
                    'subject' => $subject,
                    'html'    => $this->getBodyHtml($subject, $body)
                    //, 'text'    => $this->getBodyText($subject, $body)
                ];
if (Auth::user() && Auth::user()->id == 1) { echo 'mailgunDomain: ' . $this->mailgunDomain . ', params:<pre>'; print_r($params); echo '</pre>'; }
                $result = $mg->messages()->send($this->mailgunDomain, $params);
                if (!$result || $result === false) {
                    $success = false;
                }
            }
            /*
            // Manual API call CURL 
            curl_setopt_array($this->curl, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CUSTOMREQUEST => "POST",
            ]);
            $headers = [
                "Content-Type: multipart/form-data",
                "Authorization: Basic " . base64_encode('api:' . $this->apiKey)
            ];
            foreach ($to as $i => $emailTo) {
                $payload = [
                    'from'    => $emailRepTo,
                    'to'      => trim(sl()->emailArrToStr($emailTo)),
                    'subject' => $subject,
                    'html'    => view(
                        'vendor.survloop.emails.master',
                        [
                            'emaSubj'    => $subject,
                            'emaContent' => $body
                        ]
                    )->render()
                ];
                list($response, $error) = $this->basic_api_post_request_err(
                    $this->apiBaseUrl,
                    $payload,
                    $headers
                );
                if ($error) {
                    $success = false;
                    echo "cURL Error #:" . $error;
                    if (Auth::user() && Auth::user()->hasRole('administrator')){
                        echo 'cURL Error #' . $error . '<br />payload: <pre>'; 
                        print_r($payload);
                        echo '</pre>response: <pre>'; 
                        print_r($response);
                        echo '</pre>';
                    }
                }
            }
            */
        }
        return $success;
    }

}