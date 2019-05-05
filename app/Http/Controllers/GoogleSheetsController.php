<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Classes\GoogleSheets;

require_once(resource_path("util/util.php"));

class GoogleSheetsController extends Controller
{
   /** getGoogleSheets($url_string)
    * @param $url_string - The actual {id} from Router. Used to specify which view to return
    * Uses Google API to create a fresh, empty spreadsheet
    * Sets permissions to "everybody"
    *
    * @return view (with the spreadsheet object as parameter)
    */
    public function getGoogleSheets($url_string) {
        //$google_sheet = new GoogleSheets; //Connects the object to Google API
        //$google_sheet->createSpreadsheet(); //creates a brand new spreadsheet
        //$google_sheet->setGoogleSpreadsheetPermissions(); //sets default permissions : everyone, read/write

        return view($url_string);//->with('results', $google_sheet->spreadsheet);
    }

   /** refreshSheetValues(Request $request)
    * @param $id - string. {id} from the Route (It is the spreadsheet's ID)
    * Refresh all volatile functions on a spreadsheet
    *
    * @return null (effects the spreadsheet directly)
    */
    public function refreshValuesRequest($id) {
      $spreadSheetId = $id;
      $google_sheet = new GoogleSheets; //establish a connection to Google API
      $google_sheet->getSpreadsheet(['id'=>$spreadSheetId]); //fill objects member variables for the spreadsheet with ID : $spreadSheetId);
      $response = $google_sheet->refreshValues([]);
      return json_encode($response);
    }

    public function setBackgroundColorRequest($id) {
      //json_decode($color)---change the string into object, $r, $g, $b, $a = 1.0,
      $spreadsheetId = $id;
      $status = (object)[];
      $status->location = "setBackgroundColorRequest";

      $sheetId = (isset($_GET['sheetId'])) ? $_GET['sheetId'] : '0';
      $range = (isset($_GET['range'])) ? $_GET['range'] : null;
      if($range===null) { $status->error = "Error: 'range' is not set"; return json_encode($status);}
      $color = (isset($_GET['color'])) ? $_GET['color'] : null;
      if($range===null) { $status->error = "Error: 'color' is not set"; return json_encode($status);}
      $range = converToRangeObject($range);
      if(isset($range->error)) { $status->error = $range->error; return json_encode($status);}
      $color = validateColor($color);
      if(isset($color->error)) { $status->error = $color->error; return json_encode($status);}


      $google_sheet = new GoogleSheets; //establish a connection to Google API
      $google_sheet->getSpreadsheet(['id'=>$spreadsheetId]);
      $response = $google_sheet->setBackgroundColor(['range'=>$range->value, 'color'=>$color->value, 'sheetId'=>$sheetId]);
      return json_encode($response);
    }

    public function disableCellsRequest($id) {
      $spreadsheetId = $id;
      $status = (object)[];
      $status->location = "disableCellsRequest";

      $sheetId = (isset($_GET['sheetId'])) ? $_GET['sheetId'] : '0';
      $range = (isset($_GET['range'])) ? $_GET['range'] : null;
      if($range===null) { $status->error = "Error: 'range' is not set"; return json_encode($status);}
      $range = converToRangeObject($range);
      if(isset($range->error)) { $status->error = $range->error; return json_encode($status);}

      $google_sheet = new GoogleSheets;
      $google_sheet->getSpreadsheet(['id'=>$spreadsheetId]);
      $email = "testing@email.com";

      $response = $google_sheet->disableCells(['range'=>$range->value, 'email'=>$email, 'sheetId'=>$sheetId]);
      return json_encode($response);
    }

    public function addFrozenRowRequest($id) {
      $spreadsheetId = $id;
      $status = (object)[];
      $status->location = "addFrozenRowRequest";

      $sheetId = (isset($_GET['sheetId'])) ? $_GET['sheetId'] : '0';
      $rows = (isset($_GET['rows'])) ? $_GET['rows'] : null;
      if($rows===null) { $status->error = "Error: 'rows' is not set"; return json_encode($status);}
      if(!isPositiveInteger($rows)) { $status->error = "Error: 'rows' must be a positive integer."; return json_encode($status);}

      $google_sheet = new GoogleSheets;
      $google_sheet->getSpreadsheet(['id'=>$spreadsheetId]);

      $response = $google_sheet->setFrozenRow(['rows'=>$rows, 'sheetId'=>$sheetId]);
      return json_encode($response);
    }

    public function setHorizontalAlignmentRequest($id) {
      $spreadsheetId = $id;
      $status = (object)[];
      $status->location = "setHorizontalAlignmentRequest";

      $sheetId = (isset($_GET['sheetId'])) ? $_GET['sheetId'] : '0';
      $range = (isset($_GET['range'])) ? $_GET['range'] : null;
      if($range===null) { $status->error = "Error: 'range' is not set"; return json_encode($status);}
      $range = converToRangeObject($range);
      if(isset($range->error)) { $status->error = $range->error; return json_encode($status);}
      $alignment = (isset($_GET['alignment'])) ? $_GET['alignment'] : null;
      if($alignment===null) { $status->error = "Error: 'alignment' is not set"; return json_encode($status);}
      $alignment = validateHorizontalAlignment($alignment);
      if(isset($alignment->error)) { $status->error = $alignment->error; return json_encode($status);}

      $google_sheet = new GoogleSheets;
      $google_sheet->getSpreadsheet(['id'=>$spreadsheetId]);

      $response = $google_sheet->setHorizontalAlignment(['range'=>$range->value, 'alignment'=>$alignment->value, 'sheetId'=>$sheetId]);
      return json_encode($response);
    }

    public function setCellFormatRequest($id) {
      $spreadsheetId = $id;
      $status = (object)[];
      $status->location = "setCellFormatRequest";

      $sheetId = (isset($_GET['sheetId'])) ? $_GET['sheetId'] : '0';
      $range = (isset($_GET['range'])) ? $_GET['range'] : null;
      if($range===null) { $status->error = "Error: 'range' is not set"; return json_encode($status);}
      $range = converToRangeObject($range);
      if(isset($range->error)) { $status->error = $range->error; return json_encode($status);}
      $type = (isset($_GET['type'])) ? $_GET['type'] : null;
      if($type===null) { $status->error = "Error: 'type' is not set"; return json_encode($status);}
      $type = validateCellType($type);
      if(isset($type->error)) { $status->error = $type->error; return json_encode($status);}
      $pattern = (isset($_GET['pattern'])) ? $_GET['pattern'] : null;

      $google_sheet = new GoogleSheets;
      $google_sheet->getSpreadsheet(['id'=>$spreadsheetId]);

      $response = $google_sheet->setCellFormat(['range'=>$range->value, 'type'=>$type->value, 'optParams'=>["sheetId" => $sheetId, "pattern"=>$pattern]]);
      return json_encode($response);
    }

    /** populateSpreadsheet(Request $request)
     * @param $id - string. {id} from the Route (It is the spreadsheet's ID)
     * Do a batch update on the spreadsheet; filling in values, setting cell formats, drawing charts, etc.,
     * This uses json files
     *
     * @return null (effects the spreadsheet directly)
     */
    public function populateSpreadsheet($id) {
      $spreadsheetId = $id;

      $google_sheet = new GoogleSheets; //establish a connection to Google API
      $google_sheet->getSpreadsheet(['id'=>$spreadsheetId]); //fill objects member variables for the spreadsheet with ID : $spreadSheetId);

      //set paths for the json files
      $basePath = 'Google_Sheets_batchUpdates\\';
      $exercise = 'firstExample\\';
      $paths = ['values_batch' => resource_path($basePath.$exercise.'spreadsheets.values.batchUpdate.json'),
               'cellBackgroundColor_batch' => resource_path($basePath.$exercise.'spreadsheets.cell.backgroundColor.batchUpdate.json'),
               'cellFormat_batch' => resource_path($basePath.$exercise.'spreadsheets.cell.format.batchUpdate.json'),
               'chart_batch' => resource_path($basePath.$exercise.'spreadsheets.chart.batchUpdate.json'),
               'protectedRange_batch' => resource_path($basePath.$exercise.'spreadsheets.cell.protectedRange.batchUpdate.json')];
      //call the batch update function
      $google_sheet->populateGoogleSpreadsheet($paths);
    }

    public function batchUpdate() {
      //\Log::info("param :".json_encode($_POST['params']));
      $response = (object)["errors"=>[]];
      if(isset($_POST['params'])) {
        try{
          $params = (gettype($_POST['params'])=="string") ? json_decode($_POST['params']) : $_POST['params']; //params should be an object

          if( (!is_object($params) && !is_array($params)) || sizeof($params)==0) {
            $response->errors[] = "batchUpdate : Error - parameters must be an associative array.";
            return json_encode($response);
          }
          $requestList = validateBatchUpdateParameters($params);
          $requests = [];
          foreach($requestList as $key => $value) {
            if(sizeof($value->errors)!=0) {
              $response->errors[] = $value->errors;
            } else {
              $requests[] = $value;
            }
          }

          if(sizeof($requests)!=0) {
            $google_sheet = new GoogleSheets; //establish a connection to Google API
            foreach($requests as $key => $value) {
              //\Log::info(json_encode($value->functionVariables));
              $requests[$key]->response = $google_sheet->{$value->functionName}($value->functionVariables);
            }
          }
          $response->requests = $requests;
          if(isset($google_sheet->spreadsheetId)) {
            $response->spreadsheetUrl = $google_sheet->spreadsheet->spreadsheetUrl;
            $response->spreadsheetId = $google_sheet->spreadsheetId;
          } else {
            $response->spreadsheetId = "SpreadsheetId is not set";
          }
          return json_encode($response);

        } catch(Exception $e) {
          $response->errors[] = "batchUpdate : Error - parameters must be in [key => value] association";
          return json_encode($response);
        }
      } else {
        $response->errors[] = 'batchUpdate : Error - no parameters given.';
        return json_encode($response);
      }
    }

    //Just a testing function
    public function test($id) {
      $spreadsheetId = $id;

      $google_sheet = new GoogleSheets;
      $google_sheet->getSpreadsheet(['id'=>$spreadsheetId]);

      $google_sheet->test();

    }
}
