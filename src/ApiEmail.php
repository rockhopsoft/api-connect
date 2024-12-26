<?php
/**
  * ApiEmail
  *
  *
  * Survloop - All Our Data Are Belong
  * @package  rockhopsoft/survloop
  * @author   Morgan Lesko <rockhoppers@runbox.com>
  * @since v0.4.0
  */
namespace RockHopSoft\ApiConnect;

use Auth;
use RockHopSoft\ApiConnect\ApiConnect;

class ApiEmail extends ApiConnect
{
    // Default email address to send emails from
    protected $defFromEmail = '';

    // Default name to send emails from
    protected $defFromName  = '';

    // Email body [cache]
    protected $emailBody    = '';

    /**
     * Pull more info from the software's installation environment.
     *
     * @return void
     */
    protected function load_more_from_env()
    {
        $this->defFromEmail = env('MAIL_FROM_ADDRESS', '');
        $this->defFromName  = env('MAIL_FROM_NAME',    '');
    }

    /**
     * Pull more info from the software's installation environment.
     *
     * @return void
     */
    protected function getReplyTo(&$repTo = [])
    {
        /*
        $emailRepTo = trim($this->defFromEmail);
        if (sizeof($repTo) > 0 && trim($repTo[0]) != '') {
            $emailRepTo = trim(sl()->emailArrToStr($repTo));
        } else {
            $repTo = [ $emailRepTo ];
        }
        return $emailRepTo;
        */
        $emailRepTo = trim($this->defFromEmail);
        if (sizeof($repTo) > 0 && trim($repTo[0]) != '') {
            $emailRepTo = $repTo[0];
            if (isset($repTo[1]) && $repTo[1] != '') {
                $emailRepTo = $repTo[1] . ' <' . $emailRepTo . '>';
            }
//if (Auth::user() && Auth::user()->id == 1) { echo 'repTo:<pre>'; print_r($repTo); echo '</pre>'; }
        } else {
            $repTo = [ $emailRepTo, trim($this->defFromName) ];
            if ($this->defFromName != '') {
                $emailRepTo = trim($this->defFromName) . ' <'. $emailRepTo .'>';
            }
//if (Auth::user() && Auth::user()->id == 1) { echo 'defFromEmail: ' . $this->defFromEmail . ', defFromName: ' . $this->defFromName . '<br />'; }
        }
//if (Auth::user() && Auth::user()->id == 1) { echo '--- emailRepTo: <pre>' . $emailRepTo . '</pre><br />'; }
        return $emailRepTo;
    }

    /**
     * 
     *
     * @return void
     */
    protected function getReplyToEmail($repTo = [])
    {
        if (sizeof($repTo) > 0 && trim($repTo[0]) != '') {
            return trim(sl()->emailArrToStr($repTo));
        }
        return trim($this->defFromEmail);
    }

    /**
     * Generate the HTML version of the email's body.
     *
     * @return string
     */
    protected function getBodyHtml($subject, $body)
    {
        $this->emailBody = trim(view(
            'vendor.survloop.emails.master',
            [
                'emaSubj'    => $subject,
                'emaContent' => $body
            ]
        )->render());
        return $this->emailBody;
    }

    /**
     * Generate the text version of the email's body.
     *
     * @return string
     */
    protected function getBodyText($subject, $body)
    {
        if ($this->emailBody == '') {
            $this->getBodyHtml($subject, $body);
        }
        $text = str_replace("<br />", "\n", $this->emailBody);
        $text = str_replace("<br/>", "\n", $text);
        $text = str_replace("<br>", "\n", $text);
        $text = str_replace("</p>", "\n", $text);
        return trim(strip_tags($text));
    }
}