<?php
   // This page sends tests to the database on a click of the Publish button.
   // Cancel and Save buttons are not handled on this page.

   @session_start();
	include_once 'db_connection.php';

   //Modifing Test
   if( is_numeric($_POST['save'] ) ) {
      mysqli_query($connection, "CALL delete_test(". $_POST['save'] .");");
   }
   //Creating New Test - Set Flag to Which Class Test is Made
   else {
      $_SESSION['section_id'] = $_POST['sectionNo'];
   }

	
   // Get the section id associated with the course and section numbers the user put in
	$cNo = isset($_POST['courseNo']) == true ? $_POST['courseNo'] : '';
	$sNo = isset($_POST['sectionNo']) == true ? $_POST['sectionNo'] : '';
   //$sqlComm = "select section_id from section where course_no = '".$cNo."' and section_no = ".$sNo;
   //$row = mysqli_fetch_row(mysqli_query($conn, $sqlComm));
   
   // Get test information, and format the timeLimit, startDate, and endDate to be compatible with the DB
	$sectionId   = $_POST['sectionNo'];//$_POST['sectionNo'];//$row[0];
	$testName    = addslashes(strlen($_POST['testName']) != 0 ? $_POST['testName'] : "Test ".date("F j, Y, g:i a"));
   $published   = (isset($_POST['publish'])) ? "1" : "0";
	$hourLimit   = strlen($_POST['hours'])   != 0 ? $_POST['hours']   : "1";
	$minuteLimit = strlen($_POST['minutes']) != 0 ? $_POST['minutes'] : "0";
   $timeLimit   = ($hourLimit < 10 ? "0" : "") . $hourLimit . ":" . ($minuteLimit < 10 ? "0" : "") . $minuteLimit . ":00" ;
	$startDate   = strlen($_POST['startDate']) != 0 ? $_POST['startDate'] : date("Y-m-d");
	$startTime   = strlen($_POST['startTime']) != 0 ? $_POST['startTime'] : "00:00";
   $startDate   = $startDate . " " . $startTime;
	$endDate     = strlen($_POST['endDate'])   != 0 ? $_POST['endDate']   : date("y-m-d");
	$endTime     = strlen($_POST['endTime'])   != 0 ? $_POST['endTime']   : "23:59";
   $endDate     = $endDate .   " " . $endTime;
   $pledge      = addslashes(strlen($_POST['pledge']) != 0 ? $_POST['pledge'] : "");
   
   // Add the test to the database
	$sqlComm = "insert into test (section_id, test_name, published, time_limit, start_date, end_date, created_date, pledge)".
              " values (".$sectionId.", '".$testName."', ".$published.", '".$timeLimit."', '".$startDate."', '".$endDate."', '".date("Y-m-d")."', '".$pledge."')";
   mysqli_query($connection, $sqlComm);

   // Get the test id for the test that was just created.
   $testID = mysqli_insert_id($connection);
   
   echo "<hr/>";
   echo "<br/> <b>Success!</b>";
   echo "<br/>";
   echo "<br/> SQL Command generated: <br/>";        
   echo "<br/> $sqlComm <br/>";
   
   
   // Add questions to the database
   $queNum = 1;
   while(isset($_POST['Q'.$queNum.'T']))
   {
      $queText = addslashes(isset($_POST['Q'.$queNum.'T']) ? $_POST['Q'.$queNum.'T'] : "");
      //$quePoints = (isset($_POST['Q'.$queNum.'P']) ? $_POST['Q'.$queNum.'P'] : 1);
      $quePoints = (strlen(@$_POST['Q'.$queNum.'P'] != 0) ? $_POST['Q'.$queNum.'P'] : 1);
      
      echo "<hr/>";
      echo "question ".$queNum."<br/>";
      echo "<hr/>";

      // If True/False
      if( isset($_POST['Q'.$queNum.'O']) )
      {
         $sqlComm = "insert into question (test_id, ques_no, ques_type, ques_text, points)".
                    " values ($testID, $queNum, 'True/False', '$queText', $quePoints)";
         mysqli_query($connection, $sqlComm);
         $quesID = mysqli_insert_id($connection);
         
         echo $sqlComm."<br/><br/>";
         
         $optText = isset($_POST['Q'.$queNum.'O']) ? $_POST['Q'.$queNum.'O'] : "";
         
         $sqlComm = "insert into answer (ques_id, ans_text, correct)".
                    " values (".$quesID.", '".$optText."', 1)";
         mysqli_query($connection, $sqlComm);
         
         echo $sqlComm;
      }
      // If Multiple Choice
      elseif( isset($_POST['Q'.$queNum.'C']) )
      {
         $sqlComm = "insert into question (test_id, ques_no, ques_type, ques_text, points)".
                    " values ($testID, $queNum, 'Multiple Choice', '$queText', $quePoints)";
         mysqli_query($connection, $sqlComm);
         $quesID = mysqli_insert_id($connection);
         
         echo $sqlComm."<br/><br/>";
         
         for($optNum = 1; $optNum <= 4; $optNum++)
         {
            if(isset($_POST['Q'.$queNum.'C'.$optNum.'T']))
            {
               $optText = addslashes($_POST['Q'.$queNum.'C'.$optNum.'T']);
               if( isset($_POST['Q'.$queNum.'C']) )
               {
                  $optIsCorrect = ($_POST['Q'.$queNum.'C'] == $optNum) ? "1" : "0";
               }
               
               $sqlComm = "insert into answer (ques_id, ans_text, correct)".
                 " values (".$quesID.", '".$optText."', ".$optIsCorrect.")";
               mysqli_query($connection, $sqlComm);
               
               echo $sqlComm."<br/>";
            }
         }
      }
      // If Many Choice
      elseif( @is_string($_POST['Q'.$queNum.'C1T']) )
      {
         // YC - Answer stored here
         // $_POST['Q'.$queNum.'C']

         $sqlComm = "insert into question (test_id, ques_no, ques_type, ques_text, points)".
                    " values ($testID, $queNum, 'Many Choice', '$queText', $quePoints)";
         mysqli_query($connection, $sqlComm);
         $quesID = mysqli_insert_id($connection);
         
         echo $sqlComm."<br/><br/>";
         
         for($optNum = 1; $optNum <= 4; $optNum++)
         {
            if(isset($_POST['Q'.$queNum.'C'.$optNum.'T']))
            {
               $optText = addslashes($_POST['Q'.$queNum.'C'.$optNum.'T']);
               $optIsCorrect = isset($_POST['Q'.$queNum.'C'.$optNum]) ? "1" : "0";
               
               $sqlComm = "insert into answer (ques_id, ans_text, correct)".
                 " values (".$quesID.", '".$optText."', ".$optIsCorrect.")";
               mysqli_query($connection, $sqlComm);
               
               echo $sqlComm."<br/>";
            }
         }
      }
      // If Short Answer
      elseif( isset($_POST['Q'.$queNum.'A']) )
      {
         $sqlComm = "insert into question (test_id, ques_no, ques_type, ques_text, points)".
                    " values ($testID, $queNum, 'Short Answer', '$queText', $quePoints)";
         mysqli_query($connection, $sqlComm);
         $quesID = mysqli_insert_id($connection);
         
         echo $sqlComm."<br/><br/>";
         
         $optText = addslashes(isset($_POST['Q'.$queNum.'A']) ? $_POST['Q'.$queNum.'A'] : "");
         
         $sqlComm = "insert into answer (ques_id, ans_text, correct)".
              " values (".$quesID.", '".$optText."', 1)";

         mysqli_query($connection, $sqlComm);
         
         echo $sqlComm."<br/>";
      }
      // If Instruction
      elseif( isset($_POST['Q'.$queNum.'I']) )
      {
         $sqlComm =
            "insert into question (test_id, ques_no, ques_type, ques_text, points)".
            " values ($testID, $queNum, 'Instruction', '$queText', 0)";
         mysqli_query($connection, $sqlComm);
         echo $sqlComm;
      }
      // If Essay
      else
      {
         $sqlComm = "insert into question (test_id, ques_no, ques_type, ques_text, points)".
           " values ($testID, $queNum, 'Essay', '$queText', $quePoints)";

         mysqli_query($connection, $sqlComm);
         
         echo $sqlComm."<br/>";
      }
      
      $queNum++;
   }
   $queNum--;
   
   // Update number of questions
   mysqli_query($connection, "update test set no_of_q = (select count(ques_id) from question where test_id = ".$testID.") where test_id = ".$testID);
   
    
   mysqli_close($connection);

   //header("Location: ./teacherHomePage.php");
?>