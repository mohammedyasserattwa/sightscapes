<?php
session_start();
?>
<html>
<body>
<form  method ="POST" action = "">
<?php
    $q2='';
    $q3='';
    $usrn=$_SESSION['ID'];
    
    echo "<h1> About your experiences with our services and staff... </h1>";
    
    $conn=new mysqli("localhost","root","","project");
    if($conn->connect_error)
    die ("cannot connect to the database");

    $surveytype='satisfaction survey';
    $questionType='services and staff';
    $possibleans='other';

    $sql1 = "SELECT questionText from Question where questionType = '$questionType'";
    $resultq=mysqli_query($conn,$sql1) or die (mysqli_error($conn));
    $qs=mysqli_fetch_all($resultq,MYSQLI_NUM) or die("Error: ".$conn->error);

    $sql2 = "SELECT offeredAnswerText from offeredAnswer where offeredAnswerType = '$questionType' or offeredAnswerType = '$possibleans'";
    $resulta=mysqli_query($conn,$sql2) or die (mysqli_error($conn));
    $ans=mysqli_fetch_all($resulta,MYSQLI_ASSOC) or die("Error: ".$conn->error);

    echo "<br><b>".implode($qs[0])."</b><br><br>";
    for($i=2;$i<7;$i++){
        $j=implode($ans[$i]);
        echo "<input type='checkbox' name='chlist[]' value= '$j'> $j";
    }
    
    echo "<br><br><b>".implode($qs[1])."</b><br><br>";
    echo "<p style='display:inline'><i>Not well at all | </i></p>";
    for($i=7;$i<12;$i++){
        $j=implode($ans[$i]);
        echo "<input type='radio' name='q2' value='$j'> $j | ";
    }
    echo "<p style='display:inline'><i>  Extremely well</i></p>";

    echo "<br><br><b>".implode($qs[2])."</b><br><br>";
    echo "<p style='display:inline'><i>Not well at all | </i></p>";
    for($i=7;$i<12;$i++){
        $j=implode($ans[$i]);
        echo "<input type='radio' name='q3' value='$j'> $j | ";
    }
    echo "<p style='display:inline'><i>  Extremely well</i></p>";
    echo "<br><br> <input type='submit' name='Submit' value='Next '>";

    if(isset($_POST['Submit'])===TRUE){ 
            if(isset($_POST['q2'])&&isset($_POST['q3'])){
                $q2=$_POST['q2'];
                $q3=$_POST['q3'];
            }
        for($i=0;$i<count($qs);$i++){
                $qv=implode($qs[$i]);
                $ansv=($i==1)?$q2:$q3;

            if($ansv==null||(empty($_POST['chlist']))){
                echo "<h4> Please submit the required fields. <h4>";
                ?><script>
                    document.getElementById("form").reset();
                </script><?php
                break;
            }

            else if($i==0 && (!empty($_POST['chlist']))){
                $query1="DELETE FROM answer
                WHERE (surveyID = (SELECT surveyID from survey where surveyType = '$surveytype')) and
                (questionID = (SELECT questionID from question where questionText = '$qv')) and
                (hikerID = $usrn);";
                $rs1=$conn->query($query1);
                if(!$rs1)
                die("Error: ".$conn->error);

                foreach($_POST['chlist'] as $ch) {
                    $query="INSERT IGNORE INTO answer(surveyID,questionID,offeredAnswerID,hikerID) 
                    SELECT surveyID, questionID, offeredAnswerID, hikerID
                    FROM survey,question,offeredAnswer,Hikers WHERE surveyType='$surveytype' 
                    AND questiontext='$qv' AND 
                    offeredAnswerText='$ch' AND hikerID='$usrn';";
                    $rs=$conn->query($query);
                    if(!$rs)
                    die("Error: ".$conn->error);

                } 
            }

            else if (($i==1||$i==2) && $ansv!=null){
                    $query="INSERT INTO answer(surveyID,questionID,offeredAnswerID,hikerID) 
                    SELECT surveyID, questionID, offeredAnswerID, hikerID
                    FROM survey,question,offeredAnswer,Hikers WHERE surveyType='$surveytype' 
                    AND questiontext='$qv' AND 
                    offeredAnswerText='$ansv' AND hikerID='$usrn'
                    AND NOT EXISTS (SELECT * from answer WHERE questionID = (SELECT questionID from question where questionText = '$qv') 
                    and (hikerID = $usrn) and (surveyID = (SELECT surveyID from survey where surveyType = '$surveytype')) );";
                    $rs=$conn->query($query);
                    if(!$rs)
                    die("Error: ".$conn->error);

                    $query1="UPDATE answer SET offeredAnswerID =
                    (SELECT offeredAnswerID from offeredAnswer where offeredAnswerText='$ansv')
                    WHERE (surveyID = (SELECT surveyID from survey where surveyType = '$surveytype')) and
                    (questionID = (SELECT questionID from question where questionText = '$qv'))and
                    (hikerID = $usrn);";
                    $rs1=$conn->query($query1);
                    if(!$rs1)
                    die("Error: ".$conn->error);

                    header("Location: survey3.php", true, 301);
            }
        }
    }
    
    $conn->close();
?>
</form>
</body>
</html>