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
    $this->credentials = resource_path('/php_libraries/Google_Sheets_API/credentials/credentials.json');
    $this->client_secret = resource_path('/php_libraries/Google_Sheets_API/credentials/client_secret.json');

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
  public function createSpreadsheet($params=[]) {
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
  public function setGoogleSpreadsheetPermissions($params=[]) {
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
  public function getSpreadsheet($params) {
    $id = $params['id'];
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
  public function getValues($params) {
    $spreadsheetId = $this->spreadsheetId;
    $range = $params['valueRange'];
    return $this->service->spreadsheets_values->get($spreadsheetId, $range);
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
  public function setValues($params)
  {
    $spreadsheetId = $this->spreadsheetId;
    $range = $params['valueRange'];
    $values = $params['values'];
    $optParams = (!isset($params['optParams'])) ? [] : $params['optParams'];
    $requestBody = new \Google_Service_Sheets_ValueRange(); //create a ValueRange object

    if(!isset($optParams['majorDimension'])) {
      $majorDimension = "ROWS";
    } else {
      $majorDimension = $optParams['majorDimension'];
      unset($optParams['majorDimension']);
    }
    $requestBody->majorDimension = $majorDimension;
    $requestBody->range = $range;
    $requestBody->values = $values;

    //make sure that $params['valueInputOption'] is set
    if(!isset($optParams['valueInputOption'])) {
      $optParams['valueInputOption'] = 'USER_ENTERED'; //if it isnt; set it here to the default value
    }

    $this->service->spreadsheets_values->update($spreadsheetId, $range, $requestBody, $optParams); //run the update
    //https://developers.google.com/sheets/api/guides/values#writing_to_a_single_range
  }

  public function refreshValues($params) {
    $sheetId = (!isset($params['sheetId'])) ? 0 : $params['sheetId'];
    $requests = [
      new \Google_Service_Sheets_Request([
        'copyPaste' => [
          'source' => [
            'sheetId' => $sheetId,
            'startRowIndex' => 0,
            'endRowIndex' => 1,
            'startColumnIndex' => 0,
            'endColumnIndex' => 1,
          ],
          'destination' => [
            'sheetId' => $sheetId,
            'startRowIndex' => 0,
            'endRowIndex' => 1,
            'startColumnIndex' => 0,
            'endColumnIndex' => 1,
          ],
          'pasteType' => 'PASTE_FORMAT',
          'pasteOrientation' => 'NORMAL'
        ]
      ])
    ];
    $location = "refreshValues";
    return $this->request($requests, $location);
  }

  public function setBackgroundColor($params) {
    $range = $params['range'];
    $color = $params['color'];
    $sheetId = (!isset($params['sheetId'])) ? 0 : $params['sheetId'];
    $location = "setBackgroundColor";
    $myRange = [
      'sheetId' => $sheetId,
      'startRowIndex' => $range->startRowIndex,
      'endRowIndex' => $range->endRowIndex,
      'startColumnIndex' => $range->startColumnIndex,
      'endColumnIndex' => $range->endColumnIndex,
    ];

    $format = [
      "backgroundColor" => [
        "red" => $color->r,
        "green" => $color->g,
        "blue" => $color->b,
        //"alpha" => $color->a,
      ],
    ];

    $requests = [
      new \Google_Service_Sheets_Request([
        'repeatCell' => [
          'fields' => 'userEnteredFormat.backgroundColor',
          'range' => $myRange,
          'cell' => [
            'userEnteredFormat' => $format,
          ],
        ],
      ])
    ];

    return $this->request($requests, $location);
  }

  public function disableCells($params) {
    $range = $params['range'];
    $email = $params['email'];
    $sheetId = (!isset($params['sheetId'])) ? 0 : $params['sheetId'];
    $location = "disableCells";
    $requests = [
      new \Google_Service_Sheets_Request([
        "addProtectedRange"=> [
          "protectedRange"=> [
            //"protectedRangeId": 540122706,
            "range"=> [
              'sheetId' => $sheetId,
              'startRowIndex' => $range->startRowIndex,
              'endRowIndex' => $range->endRowIndex,
              'startColumnIndex' => $range->startColumnIndex,
              'endColumnIndex' => $range->endColumnIndex,
            ],
            "warningOnly" => false,
            'description'=> "Protecting",
            'requestingUserCanEdit'=> false,
            'editors' => [
              'users' => [
                $email
              ],
            ],
          ],
        ],
      ])
    ];

    return $this->request($requests, $location);
  }

  public function setFrozenRow($params) {
    $rows = $params['rows'];
    $sheetId = (!isset($params['sheetId'])) ? 0 : $params['sheetId'];
    $location = "setFrozenRow";
    $requests = [
      new \Google_Service_Sheets_Request([
        "updateSheetProperties"=> [
          "properties"=> [
            "sheetId"=> $sheetId,
            "gridProperties"=> [
              "frozenRowCount"=> $rows
            ]
          ],
          "fields"=> "gridProperties.frozenRowCount"
        ]
      ])
    ];

    return $this->request($requests, $location);
  }

  public function setHorizontalAlignment($params) {
    $range = $params['range'];
    $alignment = $params['alignment'];
    $sheetId = (!isset($params['sheetId'])) ? 0 : $params['sheetId'];

    $location = "setHorizontalAlignment";

    $range = [
      'sheetId' => $sheetId,
      'startRowIndex' => $range->startRowIndex,
      'endRowIndex' => $range->endRowIndex,
      'startColumnIndex' => $range->startColumnIndex,
      'endColumnIndex' => $range->endColumnIndex,
    ];

    $requests = [
      new \Google_Service_Sheets_Request([
        'repeatCell' => [
          'cell'=> [
            "userEnteredFormat"=> [
               "horizontalAlignment" => $alignment,
             ]
           ],
          'fields'=> "userEnteredFormat.horizontalAlignment",
          'range' => $range,
        ],
      ])
    ];

    return $this->request($requests, $location);
  }

  public function setCellFormat($params) {
    $range = $params['range'];
    $type = $params['type'];
    $optParams = (array)$params['optParams'];
    $location = "setCellFormat";
    //\Log::info("optParams : ".json_encode($optParams));
    $range = [
      'sheetId' => (isset($optParams['sheetId'])) ? $optParams['sheetId'] : 0,
      'startRowIndex' => $range->startRowIndex,
      'endRowIndex' => $range->endRowIndex,
      'startColumnIndex' => $range->startColumnIndex,
      'endColumnIndex' => $range->endColumnIndex,
    ];

    $numberFormat = [
      "numberFormat" => [
        "type" => $type,
        "pattern" => (isset($optParams['pattern'])) ? $optParams['pattern'] : null
      ],
    ];

    $requests = [
      new \Google_Service_Sheets_Request([
        'repeatCell' => [
          'cell'=> [
            "userEnteredFormat"=> $numberFormat
           ],
          "fields"=> "userEnteredFormat.numberFormat",
          'range' => $range,
        ],
      ])
    ];

    return $this->request($requests, $location);
  }

  private function request($requests, $location) {
    $batchUpdateRequest = new \Google_Service_Sheets_BatchUpdateSpreadsheetRequest([
        'requests' => $requests
    ]);

    $status = (object)[];
    $status->location = $location;

    try{
      $response = $this->service->spreadsheets->batchUpdate($this->spreadsheetId,
        $batchUpdateRequest);
      $status->error = false;
      $status->message = "$location request : successful";
      $status->response = $response;
      return $status;
    } catch(\Google_Service_Exception $e) {
      $status->error = true;
      $status->message = $e->getMessage();
      return $status;
    }
  }

  //just a test function
  public function test() {
    /*
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
    echo ("The spec contains".json_encode($spec)."\n"); */
  }

}
 ?>
