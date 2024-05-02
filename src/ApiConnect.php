<?php
/**
  * ApiConnect
  *
  * Survloop - All Our Data Are Belong
  * @package  rockhopsoft/survloop
  * @author   Morgan Lesko <rockhoppers@runbox.com>
  * @since v1.0.0
  */
namespace RockHopSoft\ApiConnect;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller as Controller;

class ApiConnect extends Controller
{
    // Copy of user's authorization key for this API
    protected $apiKey = '';

    // Used to manage cURL requests
    protected $curl = null;

    // Capture any messages to pass back from page load
    private $loadMessages = '';

    // Standardizes the base URL for connection, including version
    private $apiBaseUrl = '';

    // Time zone abbreviation for printing
    private $timeZoneLabel = 'EST';

    // Array to catch to JSON results from API request
    public $jsonRes = [];

    // A list of variables with machine and human-friendly labels
    public $vars = [];

    // Specify name of mysql table for data import
    public $mysqlTbl  = '';

    // Specify a prefix for all mysql table fields
    public $mysqlPrfx = '';

    /**
     * Initialize this API connection object.
     *
     * @param  string  $key
     * @return void
     */
    public function __construct($key = '')
    {
        $this->load_curl();
        if ($key != '') {
            $this->set_key($key);
        } else {
            $this->load_key_from_env();
        }
    }

    /**
     * Pull the Census API key from the software's installation environment.
     *
     * @return void
     */
    protected function load_key_from_env()
    {
        $this->set_key(env('API_KEY_CONNECT', ''));
    }

    /**
     * Set key for the Census API.
     *
     * @param  string  $key
     * @return void
     */
    public function set_key($key = '')
    {
        $this->apiKey = $key;
    }

    /**
     * Run a basic API GET request, given a URL.
     *
     * @param  array  $headIn
     * @return array
     */
    protected function get_default_header($headIn = [])
    {
        $httpHeader = [
            'Content-Type:application/json'
        ];
        if (sizeof($headIn) > 0) {
            $httpHeader = $headIn;
        }
        return $httpHeader;
    }

    /**
     * Run a basic API GET request, given a URL.
     *
     * @param  string  $url
     * @return array
     */
    public function basic_api_get_request($url = '')
    {
        curl_setopt($this->curl, CURLOPT_URL, $url);
        curl_setopt($this->curl, CURLOPT_HTTPHEADER, []);
        $ret = json_decode(curl_exec($this->curl), true);
        $this->curl_reset();
        return $ret;
    }

    /**
     * Run a basic API GET request, given a URL.
     *
     * @param  string  $url
     * @param  array  $data
     * @return array
     */
    public function basic_api_post_request_core($url = '', $data = [], $headIn = [])
    {
        $httpHeader = $this->get_default_header($headIn);
        curl_setopt($this->curl, CURLOPT_URL, $url);
        curl_setopt($this->curl, CURLOPT_HTTPHEADER, $httpHeader);
        curl_setopt($this->curl, CURLOPT_POSTFIELDS, $data);
    }

    /**
     * Run a basic API GET request, given a URL.
     *
     * @param  string  $url
     * @param  array  $data
     * @return array
     */
    public function basic_api_post_request($url = '', $data = [], $headIn = [])
    {
        $ret = [];
        $this->basic_api_post_request_core($url, $data, $headIn);
        $ret = json_decode(curl_exec($this->curl), true);
        $this->curl_reset();
        return $ret;
    }

    /**
     * Run a basic API GET request, given a URL.
     *
     * @param  string  $url
     * @param  array  $data
     * @return array
     */
    public function basic_api_post_request_err($url = '', $data = [], $headIn = [])
    {
        $this->basic_api_post_request_core($url, $data, $headIn);
        $response = curl_exec($this->curl);
        $error = curl_error($this->curl);
        $this->curl_reset();
        return [ json_decode($response, true), $error ];
    }

    /**
     * Performs core API connection required for requests.
     *
     * @return void
     */
    protected function load_curl()
    {
        $this->curl = curl_init();
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
    }

    /**
     * Performs core API connection required for requests.
     *
     * @return void
     */
    protected function curl_reset()
    {
        curl_close($this->curl);
        $this->load_curl();
    }

    /**
     * Indicates whether or not this page load
     * includes messages to pass back.
     *
     * @return boolean
     */
    public function has_messages()
    {
        return (trim($this->loadMessages) != '');
    }

    /**
     * Provides messages passed back from this page load.
     *
     * @return string
     */
    public function get_messages()
    {
        return trim($this->loadMessages);
    }

    /**
     * success response method.
     *
     * @return \Illuminate\Http\Response
     */
    public function send_response($result, $message)
    {
        $response = [
            'success' => true,
            'data'    => $result,
            'message' => $message,
        ];
        return response()->json($response, 200);
    }

    /**
     * return error response.
     *
     * @return \Illuminate\Http\Response
     */
    public function send_error($error, $errorMessages = [], $code = 404)
    {
        $response = [
            'success' => false,
            'message' => $error,
        ];
        if(!empty($errorMessages)){
            $response['data'] = $errorMessages;
        }
        return response()->json($response, $code);
    }


    /**
     * Run a basic API GET request, given a URL.
     *
     * @param  string  $id
     * @return array
     */
    protected function get_var_name($id = '')
    {
        if (sizeof($this->load_api_vars()) > 0) {
            foreach ($this->load_api_vars() as $var) {
                if ($var->id == $id) {
                    return $var->print_label();
                }
            }
        }
        return '';
    }

    /**
     * Get list of variables to import through this API.
     *
     * @return array
     */
    protected function load_api_vars()
    {
        if (sizeof($this->vars) > 0) {
            return $this->vars;
        }
        /* // Load variables to import

            $this->vars[] = new ApiVar("var_slug", "Variable Label");

        */
        return $this->vars;
    }

    /**
     *
     *
     * @param  string  $tblName
     * @param  string  $prfx
     * @return array
     */
    public function mysql_data_dump($tblName = 'census_by_age', $prfx = 'census_')
    {
        return $this->mysql_new_table($tblName, $prfx)
            . $this->mysql_insert_dump();
    }

    /**
     *
     *
     * @param  string  $tblName
     * @param  string  $prfx
     * @return array
     */
    public function mysql_new_table($tblName = 'census_by_age', $prfx = 'census_')
    {
        $this->mysqlTbl  = $tblName;
        $this->mysqlPrfx = $prfx;
        $ret = "CREATE TABLE `" . $tblName . "` \n"
            . "  (`" . $prfx . "id` int(10) unsigned NOT NULL AUTO_INCREMENT, \n"
            . "  `" . $prfx . "year` int(11) DEFAULT NULL, \n"
            . "  `" . $prfx . "state` varchar(50) DEFAULT NULL, \n";
        foreach ($this->load_api_vars() as $var) {
            $ret .= "  `" . $prfx . $var->field_name()
                . "` float DEFAULT NULL, \n";
        }
        $ret .= "  `created_at` timestamp NULL DEFAULT NULL,\n"
            . "  `updated_at` timestamp NULL DEFAULT NULL,\n"
            . "  PRIMARY KEY (`" . $prfx . "id`) );\n\n";
        return $ret;
    }

    /**
     *
     *
     * @param  string  $url
     * @return array
     */
    public function mysql_insert_dump()
    {
        $ret = "INSERT INTO `" . $this->mysqlTbl . "` \n  (`"
            . $this->mysqlPrfx . "id`, `" . $this->mysqlPrfx . "year`, `"
            . $this->mysqlPrfx . "state`";
        foreach ($this->load_api_vars() as $var) {
            $ret .= ", `" . $this->mysqlPrfx . $var->field_name() . "`";
        }
        $ret .= ", `created_at`, `updated_at`) \n VALUES ";
        $recCnt = 0;
        foreach ($this->rawData as $yr => $yearData) {
            foreach ($yearData as $state => $stateData) {
                $recCnt++;
                $ret .= (($recCnt > 1) ? ", \n  " : "")
                    . "(NULL, '" . $yr . "', '" . $state . "'";
                foreach ($this->load_api_vars() as $v => $var) {
                    $ret .= ", '";
                    foreach ($stateData as $id => $val) {
                        if ($id == $var->id) {
                            $ret .= $val;
                        }
                    }
                    $ret .= "'";
                }
                $ret .= ", '" . date("Y-m-d H:i:s")
                    . "', '" . date("Y-m-d H:i:s") . "')";
            }
        }
        return $ret . ";\n\n";
    }
}