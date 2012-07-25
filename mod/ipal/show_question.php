<html>
<head>
</head>
<body>
<?php
include '../../config.php';
 require_once("lib.php");


function ipal_get_answers($question_id){
        global $DB;
        $answerarray=array();
        $answers=$DB->get_records('question_answers',array('question'=>$question_id));
                        foreach($answers as $answers){
                                $answerarray[$answers->id]=$answers->answer;
                        }

        return($answerarray);
}


function ipal_get_questions($qid){
        global $DB;
                $pagearray2=array();

        $aquestions=$DB->get_record('question',array('id'=>$qid));
                        if($aquestions->questiontext != ""){

        $pagearray2[]=array('id'=>$qid, 'question'=>$aquestions->questiontext, 'answers'=>ipal_get_answers($qid));
                                                        }
        return($pagearray2);
}


function ipal_display_standalone_question(){
                $qid=$_GET['qid'];
                $myFormArray= ipal_get_questions($qid);
                echo "<form action=\"\">\n";
                echo $myFormArray[0]['question'];
                echo "<br>";
                        foreach($myFormArray[0]['answers'] as $k=>$v){
                        echo "<input type=\"radio\" name=\"answer_id\" value=\"$k\"/> ".strip_tags($v)."<br />\n";
                        }
        echo "</form>";
}
ipal_display_standalone_question();

?>
</body>
</html>
