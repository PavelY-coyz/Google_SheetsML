@extends('layouts.app')

@section('content')
<?php
  require_once(resource_path("util/util.php"));

  $values = [
    ["=RANDBETWEEN(0,9)", "=RANDBETWEEN(0,9)", "=RANDBETWEEN(0,9)", "=RANDBETWEEN(0,9)", 
      "=RANDBETWEEN(0,9)", "=RANDBETWEEN(0,9)", "=RANDBETWEEN(0,9)", "=RANDBETWEEN(0,9)",
      "=RANDBETWEEN(0,9)", "=RANDBETWEEN(0,9)", "", "occurrences of 0 ", '=COUNTIF(A1:J10, "=0")'],
    ["=RANDBETWEEN(0,9)", "=RANDBETWEEN(0,9)", "=RANDBETWEEN(0,9)", "=RANDBETWEEN(0,9)", 
      "=RANDBETWEEN(0,9)", "=RANDBETWEEN(0,9)", "=RANDBETWEEN(0,9)", "=RANDBETWEEN(0,9)",
      "=RANDBETWEEN(0,9)", "=RANDBETWEEN(0,9)", "", "occurrences of 1 ", '=COUNTIF(A1:J10, "=1")'],
    ["=RANDBETWEEN(0,9)", "=RANDBETWEEN(0,9)", "=RANDBETWEEN(0,9)", "=RANDBETWEEN(0,9)", 
      "=RANDBETWEEN(0,9)", "=RANDBETWEEN(0,9)", "=RANDBETWEEN(0,9)", "=RANDBETWEEN(0,9)",
      "=RANDBETWEEN(0,9)", "=RANDBETWEEN(0,9)", "", "occurrences of 2 ", '=COUNTIF(A1:J10, "=2")'],
    ["=RANDBETWEEN(0,9)", "=RANDBETWEEN(0,9)", "=RANDBETWEEN(0,9)", "=RANDBETWEEN(0,9)", 
      "=RANDBETWEEN(0,9)", "=RANDBETWEEN(0,9)", "=RANDBETWEEN(0,9)", "=RANDBETWEEN(0,9)",
      "=RANDBETWEEN(0,9)", "=RANDBETWEEN(0,9)", "", "occurrences of 3 ", '=COUNTIF(A1:J10, "=3")'],
    ["=RANDBETWEEN(0,9)", "=RANDBETWEEN(0,9)", "=RANDBETWEEN(0,9)", "=RANDBETWEEN(0,9)", 
      "=RANDBETWEEN(0,9)", "=RANDBETWEEN(0,9)", "=RANDBETWEEN(0,9)", "=RANDBETWEEN(0,9)",
      "=RANDBETWEEN(0,9)", "=RANDBETWEEN(0,9)", "", "occurrences of 4 ", '=COUNTIF(A1:J10, "=4")'],
    ["=RANDBETWEEN(0,9)", "=RANDBETWEEN(0,9)", "=RANDBETWEEN(0,9)", "=RANDBETWEEN(0,9)", 
      "=RANDBETWEEN(0,9)", "=RANDBETWEEN(0,9)", "=RANDBETWEEN(0,9)", "=RANDBETWEEN(0,9)",
      "=RANDBETWEEN(0,9)", "=RANDBETWEEN(0,9)", "", "occurrences of 5 ", '=COUNTIF(A1:J10, "=5")'],
    ["=RANDBETWEEN(0,9)", "=RANDBETWEEN(0,9)", "=RANDBETWEEN(0,9)", "=RANDBETWEEN(0,9)", 
      "=RANDBETWEEN(0,9)", "=RANDBETWEEN(0,9)", "=RANDBETWEEN(0,9)", "=RANDBETWEEN(0,9)",
      "=RANDBETWEEN(0,9)", "=RANDBETWEEN(0,9)", "", "occurrences of 6 ", '=COUNTIF(A1:J10, "=6")'],
    ["=RANDBETWEEN(0,9)", "=RANDBETWEEN(0,9)", "=RANDBETWEEN(0,9)", "=RANDBETWEEN(0,9)", 
      "=RANDBETWEEN(0,9)", "=RANDBETWEEN(0,9)", "=RANDBETWEEN(0,9)", "=RANDBETWEEN(0,9)",
      "=RANDBETWEEN(0,9)", "=RANDBETWEEN(0,9)", "", "occurrences of 7 ", '=COUNTIF(A1:J10, "=7")'],
    ["=RANDBETWEEN(0,9)", "=RANDBETWEEN(0,9)", "=RANDBETWEEN(0,9)", "=RANDBETWEEN(0,9)", 
      "=RANDBETWEEN(0,9)", "=RANDBETWEEN(0,9)", "=RANDBETWEEN(0,9)", "=RANDBETWEEN(0,9)",
      "=RANDBETWEEN(0,9)", "=RANDBETWEEN(0,9)", "", "occurrences of 8 ", '=COUNTIF(A1:J10, "=8")'],
    ["=RANDBETWEEN(0,9)", "=RANDBETWEEN(0,9)", "=RANDBETWEEN(0,9)", "=RANDBETWEEN(0,9)", 
      "=RANDBETWEEN(0,9)", "=RANDBETWEEN(0,9)", "=RANDBETWEEN(0,9)", "=RANDBETWEEN(0,9)",
      "=RANDBETWEEN(0,9)", "=RANDBETWEEN(0,9)", "", "occurrences of 9 ", '=COUNTIF(A1:J10, "=9")'],
    [""],
    ["", "", "", "", "", "", "", "", "", "", "", "mode= ", "=MODE(A1:J10)"]
  ];

  //Calling the same function multiple times will override each other
  $params = [
    //createSpreadsheet/setGoogleSpreadsheetPermissions technically dont have any parameters.
    //currently unable to call them without having a null parameter....
    'createSpreadsheet' => (object)['optParams' => null],
    'setGoogleSpreadsheetPermissions' => (object)['optParams' => null],
    'setValues' => (object)['valueRange' => 'A1:M12', 'values' => $values],
    'refreshValues' => (object)['sheetId' => 0],
    "setBackgroundColor" => (object)['range' => 'A1:J10', 'color' => 'r=255,g=255,b=255', 'sheetId'=>0],
    'disableCells' => (object)['range' => "A1:M12", 'email'=>'testmail@test.com', 'sheetId'=>0],
    'setHorizontalAlignment' => (object)['alignment'=>"LEFT", "range"=>"A1:J10",'sheetId'=>0],
    //When calling setCellFormat over a range of multiple columns/rows(A1:J10) with a custom pattern, an issue will occur where
    //random cells that contains the formula will be blank desipte clicking the cell and containing a function.
    //current workaround is to call the setCellFormat by per row or per column individually or no customized pattern over the range
    //if there are still blanks afterwards, call the refresh function to have them reappear
    'setCellFormat' => (object)['range'=>"A1:J10", "type"=>"NUMBER", "optParams"=>[""]],
  ];
/*  //$params = [];
  Log::info("Line 18 executed");
  $response = (curlRequest("/api/Sheets_API/batchUpdate/".$results->spreadsheetId, $params));
  Log::info("Line 20 executed");
  Log::info("VIEW response = ".json_encode($response));
 ?>

<pre>
  <?php
      $requests = $response->requests;
      for($i=0;$i<sizeof($requests);$i++) {
        print 'VIEW FOR LOOP : '.json_encode($requests[$i])."<br />";
      }
   ?>
</pre> */
?>
<h1 class="text-center">Google Sheets (PHP5.6)</h1>
<div class="container">
  <div class="card">
    <div class="card-header">Lets import a google sheet</div>
    <div class="card-body">
      Here we will use the Google Sheets API v4 to create, edit and share spreadsheets
      <br  /><br  />
      <iframe id="spreadsheet" src="" height='600px' width='100%' scrolling="yes"></iframe>
      <br  /><br  />
      <pre>
        spreadsheetId = <span id="spreadsheetId"></span>
      </pre>
    </div>
  </div>
  <div class="text-center">
    <div class="card" style="display:inline-block">
      <div class="card-header">Range</div>
      <div class="card-body"><input type="text" id="RANGE" size=10/></div>
    </div>
    <div class="card" style="display:inline-block">
      <div class="card-header">Color</div>
      <div class="card-body"><input type="text" id="COLOR" size=20/></div>
    </div>
    <div class="card" style="display:inline-block">
      <div class="card-header">Alignment</div>
      <div class="card-body"><input type="text" id="ALIGNMENT" size=10/></div>
    </div>
    <div class="card" style="display:inline-block">
      <div class="card-header">Rows to freeze</div>
      <div class="card-body"><input type="text" id="ROWS_TO_FREEZE" size=5/></div>
    </div>
    <br /><br />
    <div class="card">
      <div class="card-header">Cell Format Variables</div>
      <div class="card-body">
        <div class="card" style="display:inline-block">
          <div class="card-header">Cell Type</div>
          <div class="card-body"><input type="text" id="TYPE" size=5/></div>
        </div>
        <div class="card" style="display:inline-block">
          <div class="card-header">Cell Format</div>
          <div class="card-body"><input type="text" id="PATTERN" size=5/></div>
        </div>
      </div>
    </div>
    <button class="btn btn-primary center-block" type="button" id="refresh_rnd_vals_btn" onclick="refresh_random_values();">Refresh Random Values</button>
    &nbsp;&nbsp;
    <button class="btn btn-primary center-block" type="button" id="pop_btn" onclick="populateSpreadsheet();">Populate The Spreadsheet</button>
    &nbsp;&nbsp;
    <button class="btn btn-primary center-block" type="button" id="frozen_row_btn" onclick="addFrozenRow();">Frozen Row</button>
    <br /><br />
    <button class="btn btn-primary center-block" type="button" id="bgc_btn" onclick="backgroundColor();"> Background Color</button>
    &nbsp;&nbsp;
    <button class="btn btn-primary center-block" type="button" id="dc_btn" onclick="disableCells();"> Protected Cells</button>
    &nbsp;&nbsp;
    <button class="btn btn-primary center-block" type="button" id="align_btn" onclick="setHorizontalAlignment();"> Set Alignment</button>
    &nbsp;&nbsp;
    <button class="btn btn-primary center-block" type="button" id="format_btn" onclick="setCellFormat();"> Set Cell Format</button>
  </div>

  <br />
  <div class="text-center">
    <button class="btn btn-primary center-block" type="button" id="test_btn" onclick="test();">Testing Stuff!</button>
  </div>
  <br /><br />
  <pre id="responseText">
  </pre>
</div>

<script type="text/javascript">

$(document).ready(function() {
  console.log(".ready fired!");
  var start_time = new Date().getTime();
  console.log("Start time : "+start_time);
  $("#responseText").html(".ready fiiiiired");
  $.ajax({
    type: "POST",
    url: "/api/Sheets_API/batchUpdate",
    async: true,
    cache: false,
    data: <?php print json_encode(["params" => (array)$params]); ?>,
    success: function(result) {
      var request_time = new Date().getTime() - start_time;
      tmp = JSON.parse(result);
      $("#spreadsheet").attr("src", tmp.spreadsheetUrl);
      $("#spreadsheetId").text(tmp.spreadsheetId);
      $("button").attr("disabled", false);
      console.log("success on ajax");
      console.log(result);
      $("#responseText").html(result);
      console.log("Request time : "+(request_time/1000).toFixed(2)+" seconds");
    },
    error: function(data, etype) {
      $("button").attr("disabled", false);
      console.log("error on ajax");
      console.log(data);
      $("#responseText").html(data.responseText);
      console.log(etype);
    }
  });

});
//call to internal API to refresh all volatile functions
//only needs to send the spreadsheet id in data :{}
//return : null 
function refresh_random_values() {
  $("button").attr("disabled", true);
  console.log("we are in the startAjax function");
  $.ajax({
    type: "GET",
    url: "/api/Sheets_API/refreshValuesRequest/"+$("#spreadsheetId").text(),
    async: true,
    cache: false,
    success: function(result) {
      $("button").attr("disabled", false);
      console.log("success on ajax");
      console.log(result);
      $("#responseText").html(result);
    },
    error: function(data, etype) {
      $("button").attr("disabled", false);
      console.log("error on ajax");
      console.log(data);
      $("#responseText").html(data.responseText);
      console.log(etype);
    }
  });
}

//call to internval API to populate the spreadsheet. (values, formats, charts)
//only needs to send the spreadsheet id in data: {}
//return : null
function populateSpreadsheet() {
  $("button").attr("disabled", true);
  console.log("we are in the populateSpreadsheet function");
  $.ajax({
    type: "GET",
    url: "/api/Sheets_API/populateSpeadsheet/"+$("#spreadsheetId").text(),
    async: true,
    cache: false,
    success: function(result) {
      $("button").attr("disabled", false);
      console.log("success on ajax");
      console.log(result);
      $("#responseText").html(result);
    },
    error: function(data, etype) {
      $("button").attr("disabled", false);
      console.log("error on ajax");
      console.log(data);
      $("#responseText").html(data.responseText);
      console.log(etype);
    }
  });
}

function backgroundColor() {
  $("button").attr("disabled", true);
  console.log("we are in the backgroundColor function");
  $.ajax({
    type: "GET",
    url: "/api/Sheets_API/setBackgroundColorRequest/"+$("#spreadsheetId").text(),
    async: true,
    cache: false,
    data: ({
      'range' : $("#RANGE").val(),
      'color' : $("#COLOR").val(),
    }),
    success: function(result) {
      $("button").attr("disabled", false);
      console.log("success on ajax");
      console.log(result);
      $("#responseText").html(result);
    },
    error: function(data, etype) {
      $("button").attr("disabled", false);
      console.log("error on ajax");
      console.log(data);
      $("#responseText").html(data.responseText);
      console.log(etype);
    }
  });
}

function disableCells() {
  $("button").attr("disabled", true);
  console.log("we are in the disableCells function");
  $.ajax({
    type: "GET",
    url: "/api/Sheets_API/disableCellsRequest/"+$("#spreadsheetId").text(),
    async: true,
    cache: false,
    data: ({
      'range' : $("#RANGE").val(),
    }),
    success: function(result) {
      $("button").attr("disabled", false);
      console.log("success on ajax");
      console.log(result);
      $("#responseText").html(result);
    },
    error: function(data, etype) {
      $("button").attr("disabled", false);
      console.log("error on ajax");
      console.log(data);
      $("#responseText").html(data.responseText);
      console.log(etype);
    }
  });
}

function addFrozenRow() {
  $("button").attr("disabled", true);
  console.log("we are in the frozen row function");
  $.ajax({
    type: "GET",
    url: "/api/Sheets_API/addFrozenRowRequest/"+$("#spreadsheetId").text(),
    async: true,
    cache: false,
    data: ({
      "rows" : $("#ROWS_TO_FREEZE").val(),
    }),
    success: function(result) {
      $("button").attr("disabled", false);
      console.log("success on ajax");
      console.log(result);
      $("#responseText").html(result);
    },
    error: function(data, etype) {
      $("#frozen_row_btn").attr("disabled", false);
      console.log("error on ajax");
      console.log(data);
      $("#responseText").html(data.responseText);
      console.log(etype);
    }
  });
}

function setHorizontalAlignment() {
  $("button").attr("disabled", true);
  console.log("we are in the set alignment function");
  $.ajax({
    type: "GET",
    url: "/api/Sheets_API/setHorizontalAlignmentRequest/"+$("#spreadsheetId").text(),
    async: true,
    cache: false,
    data: ({
      'range' : $("#RANGE").val(),
      'alignment' : $("#ALIGNMENT").val(),
    }),
    success: function(result) {
      $("button").attr("disabled", false);
      console.log("success on ajax");
      console.log(result);
      $("#responseText").html(result);
    },
    error: function(data, etype) {
      $("button").attr("disabled", false);
      console.log("error on ajax");
      console.log(data);
      $("#responseText").html(data.responseText);
      console.log(etype);
    }
  });
}

function setCellFormat() {
  $("button").attr("disabled", true);
  console.log("we are in the set cell format function");
  $.ajax({
    type: "GET",
    url: "/api/Sheets_API/setCellFormatRequest/"+$("#spreadsheetId").text(),
    async: true,
    cache: false,
    data: ({
      'range' : $("#RANGE").val(),
      'type' : $("#TYPE").val(),
      'pattern' : $("#PATTERN").val()
    }),
    success: function(result) {
      $("button").attr("disabled", false);
      console.log("success on ajax");
      console.log(result);
      $("#responseText").html(result);
    },
    error: function(data, etype) {
      $("button").attr("disabled", false);
      console.log("error on ajax");
      console.log(data);
      $("#responseText").html(data.responseText);
      console.log(etype);
    }
  });
}

//Test function for debugging
function test() {
  $("button").attr("disabled", true);
  console.log("we are in the test function");
  $.ajax({
    type: "GET",
    url: "/api/Sheets_API/test/"+$("#spreadsheetId").text(),
    async: true,
    cache: false,
    success: function(result) {
      $("button").attr("disabled", false);
      console.log("success on ajax");
      console.log(result);
      $("#responseText").html(result);
    },
    error: function(data, etype) {
      $("button").attr("disabled", false);
      console.log("error on ajax");
      console.log(data);
      $("#responseText").html(data.responseText);
      console.log(etype);
    }
  });
}

</script>
@endsection
