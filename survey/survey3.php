<link rel="stylesheet" type="text/css" href="../../project/styles/survey.css">
<?php
session_start();
?>
<html>
<body>
<div class="background"></div>
<div class="square"></div>
<form  method ="POST" action = "">
<?php
    $GLOBALS['q1']='';
    $GLOBALS['q2']='';
    $GLOBALS['q3']='';
    ///
    $usrn=$_SESSION['ID'];
    ///
    echo "<span class = 'background2'><h1> Help us improve our website... </h1></span>";
    
    $conn=new mysqli("localhost","root","","project");
    if($conn->connect_error)
    die ("cannot connect to the database");

    $surveytype='satisfaction survey';
    $questionType='website feedback';
    $possibleans='other';

    $sql1 = "SELECT questionText from Question where questionType = '$questionType' or questionType = '$possibleans'";
    $resultq=mysqli_query($conn,$sql1) or die (mysqli_error($conn));
    $qs=mysqli_fetch_all($resultq,MYSQLI_NUM) or die("Error: ".$conn->error);

    $sql2 = "SELECT offeredAnswerText from offeredAnswer where offeredAnswerType = '$questionType' or offeredAnswerType = '$possibleans'";
    $resulta=mysqli_query($conn,$sql2) or die (mysqli_error($conn));
    $ans=mysqli_fetch_all($resulta,MYSQLI_ASSOC) or die("Error: ".$conn->error);

    echo "<span class = 'ques'></b><br><br>".implode($qs[0])."</b><br><br></span>";
    echo "<span class = 'ans'><p style='display:inline'><i>Not well at all | </i></p></span>";
    for($i=2;$i<7;$i++){
        $j=implode($ans[$i]);
        echo  "<span class = 'ans'>"."<input type='radio' name='q1' value='$j'> $j | "."</span>";
    }
    echo "<span class = 'ans'><p style='display:inline'><i>  Extremely well</i></p></span>";

    echo "<span class = 'ques'></b><br><br>".implode($qs[1])."</b><br><br></span>";
    for($i=0;$i<2;$i++){
        $j=implode($ans[$i]);
        echo  "<span class = 'ans'>"."<input type='radio' name='q2' value='$j'> $j | "."</span>";
    }
    
    echo "<span class = 'ques'></b><br><br>".implode($qs[2])."</b><br><br></span>";
    for($i=0;$i<2;$i++){
        $j=implode($ans[$i]);
        echo  "<span class = 'ans'>"."<input type='radio' name='q3' value='$j'> $j | "."</span>";
    }  
    
    echo "<span class = 'ques'></b><br><br>".implode($qs[3])."</b><br><br></span>";
    echo "<span class = 'ans'>"."<input type='text' name='issuesfaced' value=''>"."</span>";
    echo  "<span class = 'B3'>"."<input type='submit' name='submit' value='Next'></span>";
    
    if(isset($_POST['Submitt'])===TRUE){ 
        if(isset($_POST['q1'])&&isset($_POST['q2'])&&isset($_POST['q3'])){
            $GLOBALS['q1']=$_POST['q1'];
            $GLOBALS['q2']=$_POST['q2'];
            $GLOBALS['q3']=$_POST['q3'];
        }
        if(isset($_POST['issuesfaced'])){
            $GLOBALS['issuesfaced']=$_POST['issuesfaced'];
        }
      for($i=0;$i<count($qs)-1;$i++){
        $qv=implode($qs[$i]);
        $ansv=($i==0)?$GLOBALS['q1']:(($i==1)?$GLOBALS['q2']:(($i==2)?$GLOBALS['q3']:$GLOBALS['issuesfaced']));
        $move=true;
        if($ansv==null&&$i!=3){
            echo "<h4> Please submit the required fields. <h4>";
            ?><script>
                document.getElementById("form").reset();
            </script><?php
            $move=false;
            break;
        }
        else if(($i==0||$i==1||$i==2)&& $ansv!=null){
            $query="INSERT INTO answer(surveyID,questionID,offeredAnswerID,userID) 
            SELECT surveyID, questionID, offeredAnswerID, userID
            FROM survey,question,offeredAnswer,Users WHERE surveyType='$surveytype' 
            AND questiontext='$qv' AND 
            offeredAnswerText='$ansv' AND userID='$usrn'
            AND NOT EXISTS (SELECT * from answer WHERE questionID = (SELECT questionID from question where questionText = '$qv') 
            and (userID = $usrn) and (surveyID = (SELECT surveyID from survey where surveyType = '$surveytype')) );";
            $rs=$conn->query($query);
            if(!$rs)
            die("Error: ".$conn->error);

            $query1="UPDATE answer SET offeredAnswerID =
            (SELECT offeredAnswerID from offeredAnswer where offeredAnswerText='$ansv'), completionStatus='1'
            WHERE (surveyID = (SELECT surveyID from survey where surveyType = '$surveytype')) and
            (questionID = (SELECT questionID from question where questionText = '$qv'))and
            (userID = $usrn);";
            $rs1=$conn->query($query1);
            if(!$rs1)
            die("Error: ".$conn->error);
        }
       else if($i==3&&$ansv!=null){
        $query="INSERT IGNORE INTO answer(otherText,surveyID,questionID,offeredAnswerID,userID,completionStatus)
        VALUES( '$ansv',(SELECT surveyID FROM survey where surveyType='$surveytype'),
        (SELECT questionID FROM question WHERE questiontext='$qv' ),
        (SELECT offeredAnswerID FROM offeredAnswer WHERE offeredAnswerText='none'),
        (SELECT userID FROM Users WHERE userID ='$usrn'),'1');";
        
        $rs=$conn->query($query);
        if(!$rs) die("Error: ".$conn->error);

        $query1="UPDATE answer SET otherText = '$ansv'
        WHERE (offeredAnswerID=(SELECT offeredAnswerID from offeredAnswer where offeredAnswerText='none')) and
        (surveyID = (SELECT surveyID from survey where surveyType = '$surveytype')) and
        (questionID = (SELECT questionID from question where questionText = '$qv'))and
        (userID = $usrn);";
        $rs1=$conn->query($query1);
        if(!$rs1)
        die("Error: ".$conn->error);
       }
    }
    if($move==true) header("Location: survey4.php?surveytype=$surveytype", true, 301);

}
    
    $conn->close();
?>
</form>
</body>
</html>