<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Classes\GoogleSheets;

class GoogleSheetsController extends Controller
{
    
    public function getGoogleSheets($url_string) {
        $google_sheet = new GoogleSheets; //Connects the object to Google API
        $google_sheet->createSpreadsheet(); //creates a brand new spreadsheet
        $google_sheet->setGoogleSpreadsheetPermissions();//sets default permissions : everyone, read/write
        return view($url_string)->with('results', $google_sheet->spreadsheet);
    } 
   
   
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
/**
    public function refreshSheetValues($id) {
      echo "we are in refreshPage() function";
        
        $spreadsheetId = $id; 
        $google_sheet = new GoogleSheets;
        $google_sheet->getSpreadsheet($spreadsheetId);
        
        $range = "A1:A1";
            $originalValue = $google_sheet->getSingleValue($spreadsheetId, $range)->getValues();
            do{
                $tempValue = rand(1,100);
            }while($tempValue==$originalValue[0][0]);
            
            $google_sheet->setSingleValue($spreadSheetId, $range, Array(Array($rand_value))); //Lets replace the value in A1 with our random value
            $google_sheet->setSingleValue($spreadsheetId, $range, [[$tempValue]]);
            //$google_sheet->setSingleValue($spreadsheetId, $range, $originalValue);
        echo json_encode($originalValue);
        


      do{ //lets generate a random number that isnt equal to the saved_value
        $rand_value = mt_rand(1,100);
      } while($rand_value==$saved_value[0][0]);

      $google_sheet->setValues($spreadSheetId, $range, Array(Array($rand_value))); //Lets replace the value in A1 with our random value
      $google_sheet->setValues($spreadSheetId, $range, $saved_value); //Lets place back the original contents of A1
      //This will cause the cells with '=RAND(...)' to be refreshed twice.

    }
   */

                
    public function populateSpreadsheet($id) {
      $spreadsheetId = $id;

      $google_sheet = new GoogleSheets; //establish a connection to Google API
      $google_sheet->getSpreadsheet($spreadsheetId); //fill objects member variables for the spreadsheet with ID : $spreadSheetId);

      //set paths for the json files
      $basePath = 'Google_Sheets_batchUpdates/';
      $exercise = 'firstExample/';
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
    
    public function setBackgroundColor(Request $request, $id) {
    //json_decode($color)---change the string into object, $r, $g, $b, $a = 1.0,

    $spreadsheetId = $id;
    $sheetId =0;
    $google_sheet = new GoogleSheets; //establish a connection to Google API
    $google_sheet->getSpreadsheet($spreadsheetId);
    $myRange2 = $request->input('range');
    $myRange2 = $google_sheet->converToRangeObject($myRange2);
    $color = $request->input('color');
    $color = $google_sheet->colorTest($color);
    
    $myRange = [
        'sheetId' => $sheetId,
        'startRowIndex' => $myRange2->startRowIndex,
        'endRowIndex' => $myRange2->endRowIndex,
        'startColumnIndex' => $myRange2->startColumnIndex,
        'endColumnIndex' => $myRange2->endColumnIndex,
    ];
    
    $format = [
       "backgroundColor" => [
            "red" => $color->r,
            "green" => $color->g,
            "blue" => $color->b,
            //"alpha" => $color->a,
        ],
    ];
    
   
    $google_sheet->backgroundColor($format, $myRange);
    }   

       
}