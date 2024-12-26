<?php
/**
  * ApiEmailMailerSend
  *
  *
  * Survloop - All Our Data Are Belong
  * @package  rockhopsoft/survloop
  * @author   Morgan Lesko <rockhoppers@runbox.com>
  * @since v0.4.0
  */
namespace RockHopSoft\ApiConnect;

use Auth;
use MailerSend\MailerSend;
use MailerSend\Helpers\Builder\Recipient;
use MailerSend\Helpers\Builder\EmailParams;
use RockHopSoft\ApiConnect\ApiEmail;

class ApiEmailMailerSend extends ApiEmail
{
    /**
     * Pull the Census API key from the software's installation environment.
     *
     * @return void
     */
    protected function load_key_from_env()
    {
        $this->set_key(env('MAILERSEND_API_KEY', ''));
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
//echo '<script type="text/javascript"> alert(\'sendEmail defFromEmail: "' . $emailRepTo . '"\'); </script>';
//if (Auth::user() && Auth::user()->id == 1) { echo 'sendEmail, emailRepTo: ' . $emailRepTo . '<br />to: <pre>'; print_r($to); echo '</pre>'; }
        if (sizeof($to) == 0) {
            $success = false;
        } else {
            $mailersend = new MailerSend([ "api_key" => $this->apiKey ]);
            foreach ($to as $i => $emailTo) {
//if (Auth::user() && Auth::user()->id == 1) { echo 'sendEmail apiKey: ' . $this->apiKey . '<br />emailRepTo: ' . $emailRepTo . '<br />subject: ' . $subject . '<br />'; }
                $recipients = [];
                if (isset($emailTo[1])) {
                    $recipients[] = new Recipient($emailTo[0], $emailTo[1]);
                } else {
                    $recipients[] = new Recipient($emailTo[0], '');
                }
                $emailParams = (new EmailParams())
                    ->setFrom((trim($repTo[0]) != '')
                        ? $repTo[0] : $this->defFromEmail)
                    ->setFromName((sizeof($repTo) == 2
                        && trim($repTo[1]) != '')
                            ? $repTo[1] : $this->defFromName)
                    ->setRecipients($recipients)
                    ->setSubject($subject)
                    ->setHtml($this->getBodyHtml($subject, $body))
                    ->setText($this->getBodyText($subject, $body));
                $result = $mailersend->email->send($emailParams);
                if (!$result || $result === false) {
                    $success = false;
                }
            }
        }
        return $success;
    }

}