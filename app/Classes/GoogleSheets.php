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


  /**
   * Create a new GoogleSheets instance.
   *
   * @return void
   */
  public function __construct()
  {
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

  /**
   * Tell Google API to create a new spreadsheet.
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

  /**
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

  /**
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
public function getSpreadsheet($id) {
    $this->spreadsheet = $this->service->spreadsheets->get($id);
    $this->spreadsheetId = $this->spreadsheet->spreadsheetId;
    $this->innerSpreadsheet = $this->spreadsheet->getSheets()[0];
    $this->mainSpreadsheetTitle = $this->innerSpreadsheet->getProperties()->title;
  }
    
 public function getValues($spreadSheetId, $range) {
    return $this->service->spreadsheets_values->get($spreadSheetId, $range);
}
    
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
 
public function converToRangeObject($r) {
    $range = new \StdClass();
    $splitRange = explode(":", $r);
    
    $characterValues = array("A" => 1, "B" => 2, "C"=>3, "D"=>4, "E"=>5, "F"=>6, "G"=>7, "H"=>8, "I"=>9, "J"=>10, "K"=>11, "L"=>12, "M"=>13, "N"=>14, "O"=>15, "P"=>16, "Q"=>17, "R"=>18, "S"=>19, "T"=>20, "U"=>21, "V"=>22, "W"=>23, "X"=>24, "Y"=>25, "Z"=>26);
    
    $countArray = array();
    $startingStr = str_split($splitRange[0]); 
    $endingStr = str_split($splitRange[1]); 
    
    foreach ($startingStr as $char) {
        //isset to test if there is not
    if(isset($characterValues[$char])) {
                $countArray[] = $characterValues[$char]; //countArray[0]=1
        } else {
                $firstOccur = strpos($splitRange[0], $char);//when it is 17, firstoccur=1
                $startingRow = substr($splitRange[0], $firstOccur);//it substr, then become 17
                break;
      }
    }
    
    $power = 0;//check the range if there is extra letter for startingStr
    for($i=(count($countArray)-1); $i>=0; $i--) {
      if($i==(count($countArray)-1)) {
        $startingColumn = $countArray[$i];//this for only one letter
      } else {
        $startingColumn = $startingColumn + ($countArray[$i]*pow(26,$power));
      }
      $power++;
    }
    
    $countArray2 = array();
    foreach ($endingStr as $char) {
    if(isset($characterValues[$char])) {
                 $countArray2[] = $characterValues[$char];
        } else {
                $firstOccur = strpos($splitRange[1], $char);
                $endingRow = substr($splitRange[1], $firstOccur);
                break;
      }
    }
    
    //check the range if there is extra letter for endingStr 
    for($i=(count($countArray2)-1); $i>=0; $i--) {
      if($i==(count($countArray2)-1)) {
        $endingColumn = $countArray2[$i];//this for only one letter
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

public function backgroundColor($format, $myRange) {
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
        
    $batchUpdateRequest = new \Google_Service_Sheets_BatchUpdateSpreadsheetRequest([
        'requests' => $requests
    ]);
    $response = $this->service->spreadsheets->batchUpdate($this->spreadsheetId,
        $batchUpdateRequest);
    }
    
public function colorTest($str) {
    
        $color = new \stdClass();
            $pattern = '/^(?:{|\(|\[|)(?:(?:\'|"|)r(?:\'|"|)(?::|=|=>|->)(?<r>[01]?[0-9]?[0-9]|2[0-4][0-9]|25[0-5]),*()|(?:\'|"|)g(?:\'|"|)(?::|=|=>|->)(?<g>[01]?[0-9]?[0-9]|2[0-4][0-9]|25[0-5]),*()|(?:\'|"|)b(?:\'|"|)(?::|=|=>|->)(?<b>[01]?[0-9]?[0-9]|2[0-4][0-9]|25[0-5]),*()){3}\2\4\6(?:}|\)|\]|)$/m';
            $colorArray = array("red" => "{'r':1,'g':0,'b':0}", "green" => "{'r':0,'g':1,'b':0}",
            "blue" => "{'r':0,'g':0,'b':1}");
            $str = str_replace(' ', '', $str);  //remove all white spaces from the color string

            if(isset($colorArray[$str])) {
                $color = json_decode($colorArray[$str]);

            } else {
              if(preg_match($pattern, $str,$matches)) {
                  //\Log::info('matches: '.json_encode($matches));
                  $color->r = $matches["r"];
                  $color->g = $matches["g"];
                  $color->b = $matches["b"];

              } else {
                  $color->error= "Error color input!";
              }
            }
            if(!isset($color->error)) {
              $color->r /= 255;
              $color->g /= 255;
              $color->b /=255;
            } else {
              $color->error = "No Matches Found.";
            }

            return $color;
}

  }

 ?>