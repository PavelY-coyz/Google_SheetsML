<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Classes\GoogleSheets;

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
    public function refreshSheetValues($id) {
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

    /* CISC 4900 refresh func
    public function refreshSheetValues(Request $request) {
      echo "we are in refreshPage() function";
      
      $spreadsheetId = $request->input('spreasheetId'); 
      $google_sheet = new GoogleSheets;
      $google_sheet->getSpreadsheet($request->input('spreadsheetId')); 

      $range = "Z100:Z100";
      $originalValue = $google_sheet->getSingleValue($spreadsheetId, $range)->getValues();
        do{
            $tempValue = rand(1,100);
          } while($tempValue==$originalValue[0][0]);
            
      $google_sheet->setSingleValue($spreadsheetId, $range, [[$tempValue]]);
      //$google_sheet->setSingleValue($spreadsheetId, $range, $originalValue);
      echo json_encode($originalValue);
    */



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

  
      //function that will set the number format on a givenr range of cells
      public function setNumberFormat(Request $request, $id) {
      $spreadsheetId = $id;
      $sheetId = 0;
      $google_sheet = new GoogleSheets;
      $google_sheet ->getSpreadsheet($spreadsheetId);

      $tempRange = $request->input('range');
      $tempRange =  $google_sheet->convertToRangeObject($tempRange);
      
      $formatType = $request->input('type');
      $formatType = $google_sheet->cellNumberFormatTest($formatType);

      $patternType = $request->input('format');
      $patternType = $google_sheet->cellPatternTest($patternType);

      //You can comment out endRowIndex if you wish to have new rows be have the same cell format,every other cell row will follow 
      $myRange = [
        'sheetId' => $sheetId,
        'startRowIndex' => $tempRange->startRowIndex,
        'endRowIndex' => $tempRange->endRowIndex,
        'startColumnIndex' => $tempRange->startColumnIndex,
        'endColumnIndex' => $tempRange->endColumnIndex,
      ];

      //Check log to see if range is set
      \Log::info("Range: ".json_encode($myRange));

      //check if user puts custom pattern,will check if $patternType is set to a pattern
      //else just set the type
      if(isset($patternType) == true){
        $type = [
          "numberFormat" => [
            "type" => $formatType,
            "pattern" => $patternType
          ],
      ];
      }
      else {
        $type = [
          "numberFormat" => [
            "type" => $formatType
          ],
      ];
    }
      
      \Log::info("Pattern Type : ".json_encode($patternType));
      \Log::info("Format Type : ".json_encode($formatType));
      //\Log::info(json_last_error());
      
      $google_sheet->numberFormat($myRange,$type);
    }

}
