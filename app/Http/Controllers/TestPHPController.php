<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TestPHPController extends Controller
{
  public function test() {
    $pS = "";

    function colorTest($str) {

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

    $color = colorTest("['b\":0\"r\"->255,g=100)");

    $pS.= ("color = ".json_encode($color)." \n");
    //$pS.= ("red = ".$color->r."\n");
    //$pS.= ("green = ".$color->g."\n");
    //$pS.= ("blue = ".$color->b."\n");
    return view('testPHP')->with("results", $pS);
  }
}
