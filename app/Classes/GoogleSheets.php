<?php

namespace App\Classes;
//require_once(base_path('vendor/google/apiclient/src/Google/autoload.php'));
require(base_path('vendor/autoload.php'));

class GoogleSheets {

  private $credentials;
  private $client_secret;

  //https://github.com/google/google-api-php-client
  public $client;
  //https://developers.google.com/resources/api-libraries/documentation/sheets/v4/php/latest/class-Google_Service_Sheets.html
  public $service;
  //https://developers.google.com/resources/api-libraries/documentation/sheets/v4/php/latest/class-Google_Service_Sheets_Spreadsheet.html
  public $requestBody;

  //spreadsheet object
  public $spreadsheet;
  //spreadsheet ID
  public $spreadsheetId;
  //main spreadsheet within the spreadsheet object
  public $innerSpreadsheet;
  //The title of the main spreadsheet
  public $mainSpreadsheetTitle;


  //Useful classes.
  //$this->service->spreadsheets_values is of class Google_Service_Sheets_Resource_SpreadsheetsValues;

  /** CONSTRUCTOR
   * @param null (TODO: add spreadsheetId optional parameter)
   * Create a new GoogleSheets instance.
   * Connects the object to Google Sheets API
   *
   * @return void
   */
  public function __construct()
  {
    //TODO: Add option to use spreadsheetId as a parameter. And add the contents of getSpreadsheet() if spreadsheetId is not null
    $this->credentials = resource_path('\php_libraries\Google_Sheets_API\credentials\credentials.json');
    $this->client_secret = resource_path('\php_libraries\Google_Sheets_API\credentials\client_secret.json');

    $this->client = new \Google_Client();
    $this->client->setApplicationName('crypto-symbol-205814');
    $this->client->setScopes(['https://www.googleapis.com/auth/spreadsheets',
                        'https://www.googleapis.com/auth/drive',
                        'https://spreadsheets.google.com/feeds']);
    $this->client->setAccessType('online');
    //$client->setAuthConfig($client_secret);
    $this->client->setAuthConfig($this->credentials);

    $this->service = new \Google_Service_Sheets($this->client);
  }

  /** createSpreadsheet()
   * @param null
   * Tell Google API to create a new spreadsheet.
   * Records the spreadsheetId, and saves the spreadsheet object in this (member variables)
   *
   * @return void
   */
  public function createSpreadsheet() {
    // TODO: Assign values to desired properties of `requestBody`:
    $this->requestBody = new \Google_Service_Sheets_Spreadsheet();

    $this->spreadsheet = $this->service->spreadsheets->create($this->requestBody);
    $this->spreadsheetId = $this->spreadsheet->spreadsheetId;
    //get the ID of the individual sheet inside of the Google Spreadsheet
    //$this->spreadSheet->getSheets()[0]->getProperties()->getSheetId();
    $this->innerSpreadsheet = $this->spreadsheet->getSheets()[0];
  }

  /** populateGoogleSpreadsheet($requestPaths) SOON TO BE DEPRICATED!!!
   * @param $requestPaths - Array(key=>value).
   * Populate the spreadsheet with defined values and options.
   *
   * @return void
   */
  public function populateGoogleSpreadsheet($requestPaths) {
    // The ID of the spreadsheet to update.
    $spreadsheetId = $this->spreadsheetId;

    //Populate Values
    $valuesData = (file_get_contents($requestPaths['values_batch'], FILE_USE_INCLUDE_PATH));
    $valuesData = str_replace("\r\n",'', $valuesData);
    $valuesData = json_decode($valuesData);
    foreach($valuesData->requests as $request) {
      $valueInputOption = $request->valueInputOption;
      $data = $request->data;
      $includeValuesInResponse = $request->includeValuesInResponse;
      $responseValueRenderOption = $request->responseValueRenderOption;

      $requestBody = new \Google_Service_Sheets_BatchUpdateValuesRequest();

      $requestBody->setData($request->data);
      $requestBody->setIncludeValuesInResponse($request->includeValuesInResponse);
      $requestBody->setResponseValueRenderOption($request->responseValueRenderOption);
      $requestBody->setValueInputOption($request->valueInputOption);

      $this->service->spreadsheets_values->batchUpdate($spreadsheetId, $requestBody);
    }
    unset($requestPaths['values_batch']);

    $requestArray = [];
    foreach($requestPaths as $requestsPath) {
      $requests = (file_get_contents($requestsPath, FILE_USE_INCLUDE_PATH));
      $requests = str_replace("\r\n",'', $requests);
      $requests = str_replace("sourceSheetId" , ''.$this->innerSpreadsheet->getProperties()->getSheetId() , $requests);
      $requests = json_decode($requests);

      foreach($requests->requests as $request) {
        $requestArray[] = $request;
      }

    }
    $batchUpdateRequest = new \Google_Service_Sheets_BatchUpdateSpreadsheetRequest([
      'requests' => $requestArray
    ]);

    $this->service->spreadsheets->batchUpdate($spreadsheetId, $batchUpdateRequest);
  }

  /** setGoogleSpreadsheetPermissions()
   * @param null
   * Set permissions for our google spreadsheet to be viewed/edited by anyone with a link.
   *
   * @return void
   */
  public function setGoogleSpreadsheetPermissions() {
    $driveService = new \Google_Service_Drive($this->client);
    $driveService->getClient()->setUseBatch(true);

    try {
        $batch = $driveService->createBatch();

        $userPermission = new \Google_Service_Drive_Permission(array(
            'type' => 'anyone',
            'role' => 'writer',
            'notify' => false
        ));
        $request = $driveService->permissions->create(
            $this->spreadsheetId, $userPermission, array('fields' => 'id'));
        $batch->add($request, 'user');
        $domainPermission = new \Google_Service_Drive_Permission(array(
            'type' => 'anyone',
            'role' => 'writer',
            'notify' => false
        ));
        $request = $driveService->permissions->create(
            $this->spreadsheetId, $domainPermission, array('fields' => 'id'));
        $batch->add($request, 'domain');
        $results = $batch->execute();

        foreach ($results as $result) {
            if ($result instanceof \Google_Service_Exception) {
                // Handle error
                printf($result);
            } else {
                //printf("Permission ID: %s\n", $result->id);
            }
        }
    } finally {
        $driveService->getClient()->setUseBatch(false);
    }
  }


  //Function to get Spreadsheet with id
  /** getSpreadsheet($id)
  * @param $id - string. The id of the spreadsheet you wish to access
  * Saves the spreadsheet object with the spreadsheet id of $id in $this
  *
  * @return void
  */
  public function getSpreadsheet($id) {
    $this->spreadsheet = $this->service->spreadsheets->get($id);
    $this->spreadsheetId = $this->spreadsheet->spreadsheetId;
    $this->innerSpreadsheet = $this->spreadsheet->getSheets()[0];
    $this->mainSpreadsheetTitle = $this->innerSpreadsheet->getProperties()->title;
  }

  /*Function to retrieve a value
  public function getSingleValue() {
    return $this->service->spreadsheets_value->get($spreadsheetId, $range)->$getValues;
  }
  
  public function getSingleValue($spreadsheetId, $range) {
    return $this->service->spreadsheets_values->get($this->spreadsheetId, $range);
  }

  public function setSingleValue($spreadsheetId, $range, $value) {
    $body = new \Google_Service_Sheets_ValueRange([
    'values' => $value
    ]);

    $params = [
    'valueInputOption' => "USER_ENTERED"
    ];
    $result = $this->service->spreadsheets_values->update($this->spreadsheetId, $range, $body, $params);
    
  }
  
  */


/** getValues($spreadSheetId, $range)
  * @param $spreadSheetId - string. The id of the spreadsheet you wish to access
  * @param $range - string. The range you wish to access. Example: "A1:A3";
  * Obtain spreadsheet cell values across a single range
  *
  * @return Instance of ValueRange : https://developers.google.com/sheets/api/reference/rest/v4/spreadsheets.values#ValueRange
  */
  public function getValues($spreadSheetId, $range) {
    return $this->service->spreadsheets_values->get($spreadSheetId, $range);
  }

/** setValues($spreadSheetId, $range, $values)
  * @param $spreadSheetId - string. The id of the spreadsheet you wish to access
  * @param $range - string. The range you wish to set. Example: "A1:A3";
  * @param $values - 2D Array : [[],[],.]. Holds the values you wish to set
  * @param [optional] $params - Array(key=>value) : https://developers.google.com/sheets/api/reference/rest/v4/spreadsheets.values/update
  *                             Default is set to fill in a REQUIRED parameter of 'valueInputOptions'
  * @param [optional] $majorDimension - string : https://developers.google.com/sheets/api/reference/rest/v4/spreadsheets.values#ValueRange
  *                             Default is set to "ROW". you may change this incase you want to fill in values by columns instead of rows
  * Fill in values across a continuous range (no breaks in between)
  *
  * @return void : Changes the content of the actual Spreadsheet
  */
  public function setValues($spreadSheetId, $range, $values, $params=['valueInputOption' => 'USER_ENTERED'],
                                                             $majorDimension="ROWS")
  {
    $requestBody = new \Google_Service_Sheets_ValueRange(); //create a ValueRange object
    $requestBody->majorDimension = $majorDimension;
    $requestBody->range = $range;
    $requestBody->values = $values;

    //make sure that $params['valueInputOption'] is set
    if(!isset($params['valueInputOption'])) {
      $params['valueInputOption'] = 'USER_ENTERED'; //if it isnt; set it here to the default value
    }

    $this->service->spreadsheets_values->update($spreadSheetId, $range, $requestBody, $params); //run the update
    //https://developers.google.com/sheets/api/guides/values#writing_to_a_single_range
  }

  //just a test function
  public function test() {
    echo ("service->spreadsheets_values is of class: ".get_class($this->service->spreadsheets_values)."\n");
    //This gives us: service->spreadsheets_values is of class: Google_Service_Sheets_Resource_SpreadsheetsValues
    echo ("service->spreadsheets is of class: ".get_class($this->service->spreadsheets)."\n");
    //This gives us: service->spreadsheets is of class: Google_Service_Sheets_Resource_Spreadsheets
    $sheets = new \Google_Service_Sheets_Sheet($this->service);
    echo ("sheets is of class: ".get_class($sheets)."\n");
    //This gives us: sheets is of class: Google_Service_Sheets_Sheet
    echo ("innerSpreadsheet is of class: ".get_class($this->innerSpreadsheet)."\n");
    //This gives us: innerSpreadsheet is of class: Google_Service_Sheets_Sheet
    echo ('getCharts on innerSpreadsheet returns :'.json_encode($this->innerSpreadsheet->getCharts())."\n");
    //This gives us : getCharts on innerSpreadsheet returns :[{"chartId":1454796111},{"chartId":596160616}] if there are two charts
    echo ("The class type of the first chart is : ".get_class($this->innerSpreadsheet->getCharts()[0])."\n");
    //This gives us: The class type of the first chart is : Google_Service_Sheets_EmbeddedChart

    $chart = $this->innerSpreadsheet->getCharts()[0];
    $spec = $chart->getSpec();
    echo ("The class type of chart's specs is : ".get_class($spec)."\n");
    //This gives us: The class type of chart's specs is : Google_Service_Sheets_ChartSpec
    echo ("The spec contains".json_encode($spec)."\n");
  }



  //function to separate the range
    // public function convertToRangeObject($r){
    // $range = "AA5:A15";
    // $splitRange = explode(':', $range);
    // echo json_encode($splitRange);
    // // Array( "A1", "AZ15);
    // $characterValues = Array("A" => 1, "B" => 2, "C"=>3, "D"=>4, "E"=>5,
    //   "F"=>6, "G"=>7, "H"=>8, "I"=>9, "J"=>10, "K"=>11, "L"=>12, "M"=>13,
    //   "N"=>14, "O"=>15, "P"=>16, "Q"=>17, "R"=>18, "S"=>19, "T"=>20, "U"=>21,
    //   "V"=>22, "W"=>23, "X"=>24, "Y"=>25, "Z"=>26);

    // $startingRow = 0;
    // $startingColumn = 0;
    // $endingRow = 0;
    // $endingColumn = 0;

    // $countingArray = Array();

    // $startingString = str_split($splitRange[0]); //A1
    // echo "startingString - split : ".json_encode($startingString)."\n\n\n";
    // $endingString = str_split($splitRange[1]); //AZ15
    // foreach ($startingString as $char) {
    //   if( isset($characterValues[$char])){
    //     $countingArray[] = $characterValues[$char]; 
    //     //$countingArray.push($characterValues[$char]); // (1, 26)
    //   } else { //number
    //     $firstOccurance = strpos($splitRange[0], $char); //find occurance
    //     echo "found first occ : $firstOccurance \n\n\n";
    //     $startingRow = substr($splitRange[0], $firstOccurance); //1
    //     break;
    //   }
    // }
    // $power = 0;
    // for($i=(count($countingArray)-1); $i>=0; $i--) {
    //   if($i==(count($countingArray)-1)) {
    //     $startingColumn = $countingArray[$i];
    //   } else {
    //     $startingColumn = $startingColumn + ($countingArray[$i]*pow(26,$power));
    //   }
    //   $power++;
    // }
    // echo ("startingColumn = $startingColumn \n");
    // echo ("startingRow = $startingRow \n");
    // }

  public function convertToRangeObject($r) {
      $range = new \StdClass();
      $splitRange = explode(":", $r);

      $characterValues = array("A" => 1, "B" => 2, "C"=>3, "D"=>4, "E"=>5, "F"=>6, "G"=>7, "H"=>8,
      "I"=>9, "J"=>10, "K"=>11, "L"=>12, "M"=>13, "N"=>14, "O"=>15, "P"=>16, "Q"=>17, "R"=>18,
      "S"=>19, "T"=>20, "U"=>21, "V"=>22, "W"=>23, "X"=>24, "Y"=>25, "Z"=>26);

      $countArray = Array();
      $startingStr = str_split($splitRange[0]);
      $endingStr = str_split($splitRange[1]);

      foreach ($startingStr as $char) {
      if(isset($characterValues[$char])) {
                  $countArray[] = $characterValues[$char]; //countArray[0]=1
          } else {
                  $firstOccur = strpos($splitRange[0], $char);
                  $startingRow = substr($splitRange[0], $firstOccur);
                  break;
        }
      }
      $power = 0;
      for($i=(count($countArray)-1); $i>=0; $i--) {
        if($i==(count($countArray)-1)) {
          $startingColumn = $countArray[$i];//this for only one letter
        } else {
          $startingColumn = $startingColumn + ($countArray[$i]*pow(26,$power));
        }
        $power++;
      }

      $countArray2 = Array();
      foreach ($endingStr as $char) {
      if(isset($characterValues[$char])) {
                   $countArray2[] = $characterValues[$char];
          } else {
                  $firstOccur = strpos($splitRange[1], $char);
                  $endingRow = substr($splitRange[1], $firstOccur);
                  break;
        }
      }

      for($i=(count($countArray2)-1); $i>=0; $i--) {
        if($i==(count($countArray2)-1)) {
          $endingColumn = $countArray2[$i];
        } else {
          $endingColumn = $endingColumn + ($countArray2[$i]*pow(26,$power));
        }
        $power++;
      }
      $range->startRowIndex = $startingRow-1;
      $range->endRowIndex = $endingRow;
      $range->startColumnIndex = $startingColumn-1;
      $range->endColumnIndex = $endingColumn;
      return $range;
  }
  
  /* @param $range  - (string) - in the format "<start_row_name><start_column_name>:<end_row_name><end_column_name>
     * example: $range = "A1:C15"
     * @param $type  - (string) - "number"/"percent" - like I have in the cell spreadsheets.cell.format.batchUpdate.json 
     * @param $format - (string) - format pattern https://developers.google.com/sheets/api/guides/formats 
     */
  public function numberFormat($range, $type, $format = ["text", '#0', '##0.0#%', "$#,##0.00", "yyyy-mm-dd", 
                                                       "hh:mm:ss.00 a/p", "dddd, m/d/yy at h:mm", "0.00e+00"]) 
  {
    $requests = [
        new \Google_Service_Sheets_Request([
          'repeatCell' => [
              'range' => $range,
              'cell' => [
                'userEnteredFormat' => $type     //$format, this line may needs some tweaking
              ],
              'fields' => 'userEnteredFormat.numberFormat'
          ],
        ])
      ];
    
    $batchUpdateRequest = new \Google_Service_Sheets_BatchUpdateSpreadsheetRequest(['requests' => $requests]);
    $response = $this->service->spreadsheets->batchUpdate($this->spreadsheetId, $batchUpdateRequest);
  }


  //  ("TEXT" => "text","NUMBER" => "#0", "PERCENT" => "##0.0#%", "CURRENCY" => "$#,##0.00", 
  //   "DATE" => "yyyy-mm-dd", "TIME" => "hh:mm:ss.00 a/p", "DATE_TIME" => "dddd, m/d/yy at h:mm", "SCIENTIFIC" => "0.00e+00");
  // For more patterns   https://developers.google.com/sheets/api/guides/formats
  // Will set the given user pattern
  public function cellPatternTest($testPattern){
    $patternType = new \stdClass();

    echo("\nPattern: ". $testPattern);
    return $patternType->pattern = $testPattern;

  }

  
  //will check the type and set it if its right
  public function cellNumberFormatTest($testType){
    $formatType = new \stdClass();
    $formatDefault = array("TEXT", "NUMBER", "PERCENT", "CURRENCY", "DATE", "TIME", "DATE_TIME", "SCIENTIFIC");

    //Checks $formatDefault array  if $test is inside the array
    if(in_array($testType,$formatDefault)) {
      echo("Number format: ". $testType);
      return $formatType->type = $testType;
    }
    else {
      echo("Error: Number format not set correctly");
    }
    
    \Log::info("Format Type : ".json_encode($formatType));
  }

}
 ?>
