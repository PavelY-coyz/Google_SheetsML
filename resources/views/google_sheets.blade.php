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
      <button class="btn btn-primary center-block" type="button" id="refresh_rnd_vals_btn" onclick="refresh_random_values();">Refresh Random Values</button>
      &nbsp;&nbsp;
      <button class="btn btn-primary center-block" type="button" id="pop_btn" onclick="populateSpreadsheet();">Populate The Spreadsheet</button>
      <button class="btn btn-primary center-block" type="button" id="frozen_row_btn" onclick="addFrozenRow();">Frozen Row</button>
      </div>
    <br />
    <div class="text-center">
    <button class="btn btn-primary center-block" type="button" id="test_btn" onclick="test();">Testing Stuff!</button>
    <button class="btn btn-primary center-block" type="button" id="bgc_btn" onclick="backgroundColor();"> Background Color</button>
    <button class="btn btn-primary center-block" type="button" id="dc_btn" onclick="disableCells();"> Protected Cells</button>
    <button class="btn btn-primary center-block" type="button" id="align_btn" onclick="setHorizontalAlignment();"> Set Alignment</button>
    </div>
    <br /><br />
    <pre id="responseText">
    </pre>
  </div>



<script type="text/javascript">
  function refresh_random_values() {
    $("#refresh_rnd_vals_btn").attr("disabled", true);
    console.log("we are in the refresh function");
    $.ajax({
      type: "GET",
      url: "/api/Sheets_API/refreshSheetValues/<?php print $results->spreadsheetId ?>",
      async: true,
      cache: false,
      success: function(result) {
        $("#refresh_rnd_vals_btn").attr("disabled", false);
        console.log("success on ajax");
        console.log(result);
        //$("#responseText").html(result);
      },
      error: function(data, etype) {
        $("#refresh_rnd_vals_btn").attr("disabled", false);
        console.log("error on ajax");
        console.log(data);
        $("#responseText").html(data.responseText);
        console.log(etype);
      }
    });
  }


function populateSpreadsheet() {
  $("#pop_btn").attr("disabled", true);
  console.log("we are in the populateSpreadsheet function");
  $.ajax({
    type: "GET",
    url: "/api/Sheets_API/populateSpeadsheet/<?php print $results->spreadsheetId ?>",
    async: true,
    cache: false,
    success: function(result) {
      $("#pop_btn").attr("disabled", false);
      console.log("success on ajax");
      console.log(result);
      //$("#responseText").html(result);
    },
    error: function(data, etype) {
      $("#pop_btn").attr("disabled", false);
      console.log("error on ajax");
      console.log(data);
      $("#responseText").html(data.responseText);
      console.log(etype);
    }
  });
}
    
function test() {
  $("#test_btn").attr("disabled", true);
  console.log("we are in the test function");
  $.ajax({
    type: "GET",
    url: "/api/Sheets_API/test/<?php print $results->spreadsheetId ?>",
    async: true,
    cache: false,
    success: function(result) {
      $("#test_btn").attr("disabled", false);
      console.log("success on ajax");
      console.log(result);
      $("#responseText").html(result);
    },
    error: function(data, etype) {
      $("#test_btn").attr("disabled", false);
      console.log("error on ajax");
      console.log(data);
      $("#responseText").html(data.responseText);
      console.log(etype);
    }
  });
}


  function backgroundColor() {
    $("#bgc_btn").attr("disabled", true);
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
        $("#bgc_btn").attr("disabled", false);
        console.log("success on ajax");
        console.log(result);
        //$("#responseText").html(result);
      },
      error: function(data, etype) {
        $("#bgc_btn").attr("disabled", false);
        console.log("error on ajax");
        console.log(data);
        $("#responseText").html(data.responseText);
        console.log(etype);
      }
    });
  }
    
    function disableCells() {
    $("#dc_btn").attr("disabled", true);
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
        $("#dc_btn").attr("disabled", false);
        console.log("success on ajax");
        console.log(result);
        //$("#responseText").html(result);
      },
      error: function(data, etype) {
        $("#dc_btn").attr("disabled", false);
        console.log("error on ajax");
        console.log(data);
        $("#responseText").html(data.responseText);
        console.log(etype);
      }
    });
  }
    
    function addFrozenRow() {
    $("#frozen_row_btn").attr("disabled", true);
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
        $("#frozen_row_btn").attr("disabled", false);
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
    $("#align_btn").attr("disabled", true);
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
        $("#align_btn").attr("disabled", false);
        console.log("success on ajax");
        console.log(result);
        //$("#responseText").html(result);
      },
      error: function(data, etype) {
        $("#align_btn").attr("disabled", false);
        console.log("error on ajax");
        console.log(data);
        $("#responseText").html(data.responseText);
        console.log(etype);
      }
    });
    }
    
</script>
@endsection