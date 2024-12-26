<?php
/**
  * ApiEmailResend
  *
  *
  * Survloop - All Our Data Are Belong
  * @package  rockhopsoft/survloop
  * @author   Morgan Lesko <rockhoppers@runbox.com>
  * @since v0.4.0
  */
namespace RockHopSoft\ApiConnect;

use Auth;
use Resend\Resend;
use RockHopSoft\ApiConnect\ApiEmail;

class ApiEmailResend extends ApiEmail
{
    /**
     * Pull the Census API key from the software's installation environment.
     *
     * @return void
     */
    protected function load_key_from_env()
    {
        $this->set_key(env('RESEND_KEY', ''));
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
//echo 'sendEmail, emailRepTo: ' . $emailRepTo . '<br />'; exit;
        if (sizeof($to) == 0) {
            $success = false;
        } else {
            $resend = Resend::client($this->apiKey);
            foreach ($to as $i => $emailTo) {
                $emailSend = trim(sl()->emailArrToStr($emailTo));
if (Auth::user() && Auth::user()->id == 1) { echo 'sendEmail apiKey: ' . $this->apiKey . '<br />emailRepTo: ' . $emailRepTo . '<br />emailSend: ' . $emailSend . '<br />subject: ' . $subject . '<br />'; }
                $result = $resend->emails->send([
                    'from'    => $emailRepTo,
                    'to'      => [ $emailSend ],
                    'subject' => $subject,
                    'html'    => $this->getBodyHtml($subject, $body)
                ]);
                if (!$result || $result === false) {
                    $success = false;
                }
            }
        }
        return $success;
    }

}