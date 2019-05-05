<?php
namespace App\Classes;
require_once(resource_path("util/util.php"));

class GoogleAPIRequest {
  private $validFunctions = [
      'createSpreadsheet'=> [
        'required' => [],
        'optional' => ['optParams'],
      ],
      'setGoogleSpreadsheetPermissions' => [
        'required' => [],
        'optional' => ['optParams'],
      ],
      'getSpreadsheet' => [
        'required' => ['id'],
        'optional' => [],
      ],
      'getValues' => [
        'required' => ['valueRange'],
        'optional' => [],
      ],
      'setValues' => [
        'required' => ['valueRange','values'],
        'optional' => ['optParams'],
      ],
      'refreshValues' => [
        'required' => [],
        'optional' => ['sheetId'],
      ],
      'setBackgroundColor' => [
        'required' => ['range','color'],
        'optional' => ['sheetId']
      ],
      'disableCells' => [
        'required' => ['range','email'],
        'optional' => ['sheetId'],
      ],
      'setFrozenRow' => [
        'required' => ['rows'],
        'optional' => ['sheetId'],
      ],
      'setHorizontalAlignment' => [
        'required' => ['range','alignment'],
        'optional' => ['sheetId'],
      ],
      'setCellFormat' => [
        'required' => ['range','type'],
        'optional' => ['optParams'],
      ]
  ];

  private $paramValidators;

  public $errors = [];
  public $warnings = [];
  public $requestName;
  public $requestVariables = [];
  public $functionName;
  public $functionVariables = [];

  public function __construct() {
    $this->paramValidators = (object)[
      'alignment' => [function($r) { return validateHorizontalAlignment($r);}],
      'color' => [function($r) { return validateColor($r);}],
      'email' => [function($r) { return $r;}],
      'id' => [function($r) { return $r;}],
      'optParams' => [function($r) { return $r;}],
      'range' => [function($r) { return converToRangeObject($r);}],
      'rows' => [function($r) { return isPositiveInteger($r);}],
      'sheetId' => [function($r) { return $r;}],
      'type' => [function($r) { return validateCellType($r);}],
      'valueRange' => [function($r) { return validateRange($r);}],
      'values' => [function($r) { return $r;}]
    ];
    $a = func_get_args();
    $i = func_num_args();
    if (method_exists($this,$f='__construct'.$i)) {
        call_user_func_array(array($this,$f),$a);
    } else {
      $this->errors = ['You must pass a request.'];
    }
  }

  public function __construct2($func, $params) {
    $this->requestName = $func;
    $this->requestVariables = $params;

    if(!isset($this->validFunctions[$func])) {
      $this->errors[] = "'$func' is not a valid function";
      return;
    } else {
      $this->functionName = $func;
    }


    if($this->validateParameterNames($func, $params)===false) {
      return;
    }

    //\Log::info("Params (GAR): ".json_encode($params));

    if(sizeof($params)!=0) {
      foreach($params as $key => $value) {
        //\Log::info("Key : $key");
        //\Log::info("Value : ".json_encode($value));
        $result = $this->paramValidators->{$key}[0]($value);
        if(is_object($result)) {
          if(isset($result->error) && $result->error!==null) {
            $this->errors[] = $result->error;
          } else {
            if(isset($result->value)) {
              $this->functionVariables[$key] = $result->value;
            } else { //only for 'optParam' variable
              $this->functionVariables[$key] = $result;
            }
          }
        } else {
          $this->functionVariables[$key] = $result;
        }
      }
    } else {
      return;
    }

  }

  /* Determine if all required parameters are present. And any optionals match.
   * @param $func - string - name of a function to call
   * @param $params - object/array - name=>value
   * @return bool - true if all parameters are correct / false if it doesnt match up
   */
  private function validateParameterNames($func, &$params) {
    //make sure all the parameters required for the function are set
    $function = (object)($this->validFunctions[$func]);
    $requiredParams = (array)$function->required;
    $optionalParams = (array)$function->optional;

    //to many parameters given!!!
    //\Log::info($this->requestName.' Params : '.json_encode($params).' '.sizeof($params));
    if(sizeof($params)> (sizeof($requiredParams)+sizeof($optionalParams))) {
      $this->errors[] = "Parameters - required: ".sizeof($requiredParams)." , optional : ".
          sizeof($optionalParams)." - parameters given : ".sizeof($params);
      return false;
    }

    //if there are required parameters - make sure all of them have been listed
    if(sizeof($requiredParams)>0 || sizeof($optionalParams)>0) {
      foreach($params as $key => $value) {
        //a required parameter was found
        if(($index = array_search($key, $requiredParams)) !== false) {
          //remove it from the 'required' parameter list
          unset($requiredParams[$index]);
        } else if(($index = array_search($key, $optionalParams)) !== false) { //an optional parameter was found
          //remove it from the 'optional' parameter list
          unset($optionalParams[$index]);
        } else {
          //invalid parameter for this function. Remove it from the main list!
          unset($params->{$key});
          $this->warnings[] = "$key is not a valid parameter!";
        }
      }
    }

    //If array isnt empty - there are missing required parameters - return an error
    if(sizeof($requiredParams)>0) {
      $this->errors[] = "Missing paramters (".sizeof($requiredParams).") : ".printArray($requiredParams);
      return false;
    } else {
      return true;
    }
  }
}
