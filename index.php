<?php

/**
* @author     Arda GEZEK
* @website    www.gezek.net
* @datetime   15 June 2020
* @purpose    "Extending railways"
*/
error_reporting(0);
include 'Extending_railways.php';

use extending_Railways\Calculate;

$questions = [
  "1+2"  					, // 3   ✓
  "2+8*2" 				, // 18  ✓
  "2*(3+2)"				, // 10  ✓
  "2+sin(30)"		  , // 2.5 ✓
  "4*pow(2,3)"		, // 32  ✓
  "pow(4^3,sin(cos(60)*40*3/2))) / abs(3-5)" ,	// 4 ✓
  "e^pi"							, // 23.1307 ✓
  "3*-2"							, // -6      ✓
  //"x=30"          ,  // 30     ✓
  //"59.2*cos(x*2)"  , // 29.6     ✓

    ];

    foreach ($questions as $k => $question)
    {
      echo $question.' ==========> ',Calculate::it($question), '<br>';
    }


    $question = isset($_POST['question']) ? $_POST['question'] : null;

?>

<form method="POST">
        <input id="question" class="form-field" name="question" type="text">
</form>

<?php


if ($question!==NULL){
    echo $question.' ==========> ',Calculate::it($question), '<br>';
    }

 ?>
