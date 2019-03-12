<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Classes\GoogleSheets;

class GoogleSheetsController extends Controller
{
    public function getGoogleSheets() {
        // TODO . Use route parameters to select the paths we chose...
        $basePath = 'Google_Sheets_batchUpdates\\';
        $exercise = 'firstExample\\';
        $paths = ['values_batch' => resource_path($basePath.$exercise.'spreadsheets.values.batchUpdate.json'),
                  'cellBackgroundColor_batch' => resource_path($basePath.$exercise.'spreadsheets.cell.backgroundColor.batchUpdate.json'),
                  'cellFormat_batch' => resource_path($basePath.$exercise.'spreadsheets.cell.format.batchUpdate.json'),
                  'chart_batch' => resource_path($basePath.$exercise.'spreadsheets.chart.batchUpdate.json'),
                  'protectedRange_batch' => resource_path($basePath.$exercise.'spreadsheets.cell.protectedRange.batchUpdate.json')];

        $google_sheet = new GoogleSheets;
        $google_sheet->createSpreadsheet();
        $google_sheet->populateGoogleSpreadsheet($paths);
        $google_sheet->setGoogleSpreadsheetPermissions();

        return view('google_sheets')->with('results', $google_sheet->spreadsheet);
    }

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





    }
}
