<?php
use App\Classes\GoogleAPIRequest;

function converToRangeObject($r) {
  $range = new \StdClass();
  $r = str_replace(' ', '', $r);  //remove all white spaces from the range string
  $r = strtoupper($r); //change string to lowercase.
  $pattern = '/^(?:(?<start>[A-Z]+[1-9]+[0-9]*):(?<end>[A-Z]+[1-9]+[0-9]*))$/m';
  if(preg_match($pattern, $r,$matches)) {
    //\Log::info('matches: '.json_encode($matches));
    $splitRange = [];
    $splitRange[] = $matches["start"];
    $splitRange[] = $matches["end"];
  } else {
    $range->error = "Error: Invalid Range given. Please use the \"A1:B10\" range notation (row must be &gt 0).";
    return $range;
  }

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

  $range->value = new \StdClass();
  $range->value->startRowIndex = ($startingRow <= $endingRow) ? $startingRow-1 : $endingRow-1;
  $range->value->endRowIndex = ($startingRow > $endingRow) ? $startingRow : $endingRow;
  $range->value->startColumnIndex = ($startingColumn <= $endingColumn) ? $startingColumn-1 : $endingColumn-1;
  $range->value->endColumnIndex = ($startingColumn > $endingColumn) ? $startingColumn : $endingColumn;

  return $range;
}

function validateColor($usrColor) {
  $color = new \stdClass();
  $pattern = '/^(?:{|\(|\[|)(?:(?:\'|"|)r(?:\'|"|)(?::|=|=>|->)(?<r>[01]?[0-9]?[0-9]|2[0-4][0-9]|25[0-5]),*()|(?:\'|"|)g(?:\'|"|)(?::|=|=>|->)(?<g>[01]?[0-9]?[0-9]|2[0-4][0-9]|25[0-5]),*()|(?:\'|"|)b(?:\'|"|)(?::|=|=>|->)(?<b>[01]?[0-9]?[0-9]|2[0-4][0-9]|25[0-5]),*()){3}\2\4\6(?:}|\)|\]|)$/m';
  $colorArray = array("red" => (object)['r'=>255,'g'=>0,'b'=>0],
                      "green" => (object)['r'=>0,'g'=>255,'b'=>0],
                      "blue" => (object)['r'=>0,'g'=>0,'b'=>255],
                      "black" => (object)['r'=>0,'g'=>0,'b'=>0],
                      "white" => (object)['r'=>255,'g'=>255,'b'=>255]
  );
  $usrColor = str_replace(' ', '', $usrColor);  //remove all white spaces from the color string
  $usrColor = strtolower($usrColor); //change string to lowercase.

  $color->value = new \StdClass();
  if(isset($colorArray[$usrColor])) {
      $color->value = $colorArray[$usrColor];
  } else {
    if(preg_match($pattern, $usrColor,$matches)) {
      //\Log::info('matches: '.json_encode($matches));
      $color->value->r = $matches["r"];
      $color->value->g = $matches["g"];
      $color->value->b = $matches["b"];

    } else {
      $color->error= "Error: Invalid Color given.";
      return $color;
    }
  }

  $color->value->r /= 255;
  $color->value->g /= 255;
  $color->value->b /=255;

  return $color;
}

function validateHorizontalAlignment($align) {
  $alignment =  new \stdClass();
  $align = str_replace(' ', '', $align);  //remove all white spaces from the align string
  $align = strtoupper($align); //change string to uppercase.

  $validAlignments = array("CENTER", "LEFT", "RIGHT");
  if(in_array($align, $validAlignments)) {
    $alignment->value = $align;
    return $alignment;
  } else{
    $alignment->error = "Error: Invalid Horizontal Alignment given.";
    return $alignment;
  }
}

function validateCellType($usrType) {
  $type = new \stdClass();
  $usrType = str_replace(' ', '', $usrType);
  $usrType = strtoupper($usrType);

  $validTypes = array("TEXT", "NUMBER", "PERCENT", "CURRENCY", "DATE", "TIME", "DATE_TIME", "SCIENTIFIC", "NUMBER_FORMAT_TYPE_UNSPECIFIED");
  if(in_array($usrType, $validTypes)) {
    $type->value = $usrType;
    return $type;
  } else{
    //$type->error = "Error: Invalid Cell Type given.";
    return $type->error = "NUMBER_FORMAT_TYPE_UNSPECIFIED";
  }
}

function validateRange($r) {
  $range = new \StdClass();
  $r = str_replace(' ', '', $r);  //remove all white spaces from the range string
  $r = strtoupper($r); //change string to lowercase.
  $pattern = '/^(?:(?<start>[A-Z]+[1-9]+[0-9]*):(?<end>[A-Z]+[1-9]+[0-9]*))$/m';
  if(preg_match($pattern, $r,$matches)) {
    //\Log::info('matches: '.json_encode($matches));
    $range->value = $r;
    return $range;
  } else {
    $range->error = "Error: Invalid Range given. Please use the \"A1:B10\" range notation (row must be &gt 0).";
    return $range;
  }
}

function isPositiveInteger($var) {
  $result = new \StdClass();
  $var = "".$var;
  $var = str_replace(' ', '', $var);
  $pattern = '/^[0-9]+$/m';
  if(preg_match($pattern, $var,$matches)) {
    $result->value = $var;
    return $result;
  } else {
    $result->error = "$var is not a positive integer.";
    return $result;
  }
}

function printArray($arr) {
  if(!is_array($arr)) {
    return "(variable is not an array)";
  } else {
    $ps = "[";
    $i = 0;
    foreach($arr as $key => $value) {
      $ps.= ($i>0) ? ", ".$value : $value;
      $i++;
    }
    $ps.= "]";
  }
  return $ps;
}

function validateBatchUpdateParameters($params) {
  $requests = [];
  foreach($params as $value) {
    $requests[] = new GoogleAPIRequest($value['function'], $value['parameters']);
  }
  \Log::info(json_encode($requests));
  return $requests;
}

//$uri - uri of the request
//  -> ex: /api/Sheets_API/batchUpdate/{id}
//$params - array
function curlRequest($uri, $params) {
  //$_SERVER['SERVER_NAME'] will need to change to API server's base URL
  //This function will be used by the server that is making a call to the API
  $url = "http://" . $_SERVER['SERVER_NAME'] . $uri;
  $ch = curl_init($url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_TIMEOUT, 30);
  curl_setopt($ch, CURLOPT_POSTFIELDS, ["params" => json_encode((Array)$params)]);

  // execute!
  $response = curl_exec($ch);

  // close the connection, release resources used
  curl_close($ch);

  //curl_exec returns an encoded string - decode before returning
  return json_decode($response);
}
