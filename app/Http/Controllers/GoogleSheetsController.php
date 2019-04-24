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
        $google_sheet = new GoogleSheets; //Connects the object to Google API
        $google_sheet->createSpreadsheet(); //creates a brand new spreadsheet
        $google_sheet->setGoogleSpreadsheetPermissions(); //sets default permissions : everyone, read/write

        return view($url_string)->with('results', $google_sheet->spreadsheet);
    }

   /** refreshSheetValues(Request $request)
    * @param $id - string. {id} from the Route (It is the spreadsheet's ID)
    * Refresh all volatile functions on a spreadsheet
    *
    * @return null (effects the spreadsheet directly)
    */
    public function refreshSheetValuesRequest($id) {
      //TODO: Find a way to do this w/o having to refresh the values twice.
      //We need to be able to trigger the refresh of volatile functions just once; and without effecting the rest of the spreadSheet

      $spreadSheetId = $id;
      $google_sheet = new GoogleSheets; //establish a connection to Google API
      $google_sheet->getSpreadsheet($spreadSheetId); //fill objects member variables for the spreadsheet with ID : $spreadSheetId);

      $range = "A1:A1"; //lets select the value we want to extract. We will use cell A1 for this.
      $savedValueRange = $google_sheet->getValues($spreadSheetId, $range); //save the current valueRange that is in the cell
      $saved_value = $savedValueRange->values; //$savedValueRange->getValues() can also be used
      //$saved_value comes in the format [[value]]

      do{ //lets generate a random number that isnt equal to the saved_value
        $rand_value = mt_rand(1,100);
      } while($rand_value==$saved_value[0][0]);

      $google_sheet->setValues($spreadSheetId, $range, Array(Array($rand_value))); //Lets replace the value in A1 with our random value
      $google_sheet->setValues($spreadSheetId, $range, $saved_value); //Lets place back the original contents of A1
      //This will cause the cells with '=RAND(...)' to be refreshed twice.
    }

    public function setBackgroundColorRequest($id) {
      //json_decode($color)---change the string into object, $r, $g, $b, $a = 1.0,
      $spreadsheetId = $id;
      $status = (object)[];
      $status->location = "setBackgroundColor";

      $sheetId = (isset($_GET['sheetId'])) ? $_GET['sheetId'] : '0';
      $range = (isset($_GET['range'])) ? $_GET['range'] : null;
      if($range===null) return "Error: Range is not set";
      $color = (isset($_GET['color'])) ? $_GET['color'] : null;
      if($range===null) return "Error: Color is not set";
      $range = converToRangeObject($range);
      if(isset($range->error)) { $status->error = $range->error; return json_encode($status);}
      $color = validateColor($color);
      if(isset($color->error)) { $status->error = $color->error; return json_encode($status);}


      $google_sheet = new GoogleSheets; //establish a connection to Google API
      $google_sheet->getSpreadsheet($spreadsheetId);
      $response = $google_sheet->setBackgroundColor($sheetId, $range, $color);
      return json_encode($response);
    }

    public function disableCellsRequest(Request $request, $id) {
      $spreadsheetId = $id;
      $sheetId =0;
      $google_sheet = new GoogleSheets;
      $google_sheet->getSpreadsheet($spreadsheetId);

      $myRange = $request->input('range');
      $myRange = $google_sheet->converToRangeObject($myRange);
      $string = "testing@email.com";

      $google_sheet->disableCell($myRange, $sheetId, $string);
    }

    public function addFrozenRowRequest(Request $request, $id) {
      $spreadsheetId = $id;
      $sheetId =0;
      $google_sheet = new GoogleSheets;
      $google_sheet->getSpreadsheet($spreadsheetId);

      $myRange = $request->input('range');
      $myRange = $google_sheet->converToRangeObject($myRange);

      $count = 0;
      $count = $myRange->endRowIndex - $myRange->startRowIndex +1;
      if($count >= 1) {
        echo("count = " .$count."\n");
        echo("start row= " .$myRange->startRowIndex. "\n");
        echo("end row= " .$myRange->endRowIndex. "\n");
        $google_sheet->FrozenRow($count, $sheetId);
      }
      else {
        echo ("Error: Wrong Range!");
      }
    }

    public function setHorizontalAlignmentRequest(Request $request, $id) {
      $spreadsheetId = $id;
      $sheetId =0;
      $google_sheet = new GoogleSheets;
      $google_sheet->getSpreadsheet($spreadsheetId);

      $range = $request->input('range');
      $range = $google_sheet->converToRangeObject($range);
      $alignment = $request->input('align');
      $alignment = $google_sheet->testAlign($alignment);


      $range = [
        'sheetId' => $sheetId,
        'startRowIndex' => $range->startRowIndex,
        'endRowIndex' => $range->endRowIndex,
        'startColumnIndex' => $range->startColumnIndex,
        'endColumnIndex' => $range->endColumnIndex,
      ];

      $google_sheet->HorizontalAlignment($range, $alignment);
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
      $google_sheet->getSpreadsheet($spreadsheetId); //fill objects member variables for the spreadsheet with ID : $spreadSheetId);

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

    //Just a testing function
    public function test($id) {
      $spreadsheetId = $id;

      $google_sheet = new GoogleSheets;
      $google_sheet->getSpreadsheet($spreadsheetId);

      $google_sheet->test();
    }
}
