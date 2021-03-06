<?php

require_once "util.php";
session_start();

$text = 
"// true/false
::Q1:: 1+1=2 {T}

// multiple choice with specified feedback for right and wrong answers
::Q2:: What's between orange and green in the spectrum? 
{ =yellow # right; good! ~red # wrong, it's yellow ~blue # wrong, it's yellow }

// fill-in-the-blank
::Q3:: Two plus {=two =2} equals four.

// matching
::Q4:: Which animal eats which food? { =cat -> cat food =dog -> dog food }

// math range question
::Q5:: What is a number from 1 to 5? {#3:2}

// math range specified with interval end points
::Q6:: What is a number from 1 to 5? {#1..5}
// translated on import to the same as Q5, but unavailable from Moodle question interface

// multiple numeric answers with partial credit and feedback
::Q7:: When was Ulysses S. Grant born? {#
         =1822:0      # Correct! Full credit.
         =%50%1822:2  # He was born in 1822. Half credit for being close.
}

// essay
::Q8:: How are you? {}
";

if ( isset($_POST['text']) ) $text = $_POST['text'];

?>
<!DOCTYPE html>
<html>
<head>
<title>GIFT2QTI - Quiz format convertor</title>
<!-- Latest compiled and minified CSS -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap.min.css">
<!-- Optional theme -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap-theme.min.css">

</head>
<body>
<p>Please enter your <a href="https://docs.moodle.org/28/en/GIFT_format" target="_blank">GIFT</a> 
formatted quiz text below so it can be converted to 
<a href="http://www.imsglobal.org/question/" target="_blank">QTI 1.2.1</a>.
</p><p>
This is still a <a href="https://github.com/csev/gift2qti" target="_blank">work in progress</a>
and currently only supports single-answer multiple-choice, true/false, and essay questions.
The sample text below has some GIFT formats that this tool does not yet support so some of the questions
below will not be converted.  Feel free to send me a Pull request on gitHub :).
</p>
<form method="post" action="convert.php" target="working" style="margin:20px;">
<p style="float:right">
<input type="submit" name="submit" class="btn btn-primary" value="Convert GIFT to QTI"
onclick="$('#myModal').modal('show');"></p>
<p>Quiz Title: <input type="text" name="title" size="60"/></p>
<p>Quiz File Name (no suffix): <input type="text" name="name" size="30"/> (optional)</p>
<textarea rows="30" style="width: 98%" name="text">
<?= htmlent_utf8($text); ?>
</textarea>
</form>
<!-- Modal -->
<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog" style="width:80%">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close" 
            onclick="$('#working').attr('src', 'waiting.php');" ><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="myModalLabel">Converting to QTI...</h4>
      </div>
      <div class="modal-body">
        <iframe id="working" name="working" src="waiting.php" style="width:90%; height: 400px"></iframe>
      </div>
    </div>
  </div>
</div>

<!-- Latest compiled and minified JavaScript -->
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js" ></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/js/bootstrap.min.js"></script>
</body>
</html>
