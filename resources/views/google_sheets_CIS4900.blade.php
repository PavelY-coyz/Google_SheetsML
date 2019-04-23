@extends('layouts.app')

@section('content')
<h1 class="text-center">Google Sheets (PHP5.6)</h1>
<div class="container">
  <div class="card">
    <div class="card-header">Lets import a google sheet</div>
    <div class="card-body">
      Here we will use the Google Sheets API v4 to create, edit and share spreadsheets
      <br  /><br  />
      <iframe src="{{ rtrim($results->spreadsheetUrl) }}" height='600px' width='100%' scrolling="yes"></iframe>
      <br  /><br  />
      <pre>
        <?php //print_r($results);
        ?>
        spreadsheetId = <?php print $results->spreadsheetId ?>
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
      <div class="card-body"><input type="text" id="COLOR" size=10/></div>
    </div>
    <br /><br />
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
//call to internal API to refresh all volatile functions
//only needs to send the spreadsheet id in data :{}
//return : null
function refresh_random_values() {
  $("button").attr("disabled", true);
  console.log("we are in the startAjax function");
  $.ajax({
    type: "GET",
    url: "/api/Sheets_API/refreshSheetValues/<?php print $results->spreadsheetId ?>",
    async: true,
    cache: false,
    success: function(result) {
      $("button").attr("disabled", false);
      console.log("success on ajax");
      console.log(result);
      //$("#responseText").html(result);
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
    url: "/api/Sheets_API/populateSpeadsheet/<?php print $results->spreadsheetId ?>",
    async: true,
    cache: false,
    success: function(result) {
      $("button").attr("disabled", false);
      console.log("success on ajax");
      console.log(result);
      //$("#responseText").html(result);
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
    url: "/api/Sheets_API/setBackgroundColor/<?php print $results->spreadsheetId ?>",
    async: true,
    cache: false,
    data: ({
      'range' : "A1:B7",
      'color' : "{r:112,g:1,b:2}",
    }),
    success: function(result) {
      $("button").attr("disabled", false);
      console.log("success on ajax");
      console.log(result);
      //$("#responseText").html(result);
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
    url: "/api/Sheets_API/disableCells/<?php print $results->spreadsheetId ?>",
    async: true,
    cache: false,
    data: ({
      'range' : "A1:B7",
    }),
    success: function(result) {
      $("button").attr("disabled", false);
      console.log("success on ajax");
      console.log(result);
      //$("#responseText").html(result);
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
    url: "/api/Sheets_API/addFrozenRow/<?php print $results->spreadsheetId ?>",
    async: true,
    cache: false,
    data: ({
      'range' : "B2:B4",
    }),
    success: function(result) {
      $("button").attr("disabled", false);
      console.log("success on ajax");
      console.log(result);
      //$("#responseText").html(result);
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
    url: "/api/Sheets_API/setHorizontalAlignment/<?php print $results->spreadsheetId ?>",
    async: true,
    cache: false,
    data: ({
      'range' : "B2:B5",
      'align' : "CENTER",
    }),
    success: function(result) {
      $("button").attr("disabled", false);
      console.log("success on ajax");
      console.log(result);
      //$("#responseText").html(result);
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
    url: "/api/Sheets_API/test/<?php print $results->spreadsheetId ?>",
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
