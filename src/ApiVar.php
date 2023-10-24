<?php
/**
  * CensusVar
  *
  * census.gov/data/developers/guidance/api-user-guide.Core_Concepts.html
  * You can include up to 50 variables in a single API query and
  * can make up to 500 queries per IP address per day.
  *
  * Survloop - All Our Data Are Belong
  * @package  rockhopsoft/survloop
  * @author   Morgan Lesko <rockhoppers@runbox.com>
  * @since v1.0.0
  */
namespace RockHopSoft\ApiConnect;

class ApiVar
{
    public $id = '';
    public $label = '';

    public function __construct($id = '', $label = '')
    {
        $this->id = $id;
        $this->label = $label;
    }

    public function print_label()
    {
        return $this->label;
    }

    public function field_name()
    {
        $fld = str_replace('!!', '_', strtolower($this->label));
        $fld = str_replace(' ', '_', $fld);
        $fld = str_replace(':', '', $fld);
        $fld = str_replace('\\', '', $fld);
        $fld = str_replace(':', '', $fld);
        return $fld;
    }

}