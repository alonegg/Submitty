<?php
require_once("controller/controller_functions.php");

print('<!-- Course Container -->');
require_once("view/".$semester."_".$course."_container.php");

print('<!-- Course CSS -->');
print('<link href="resources/'.$semester."_".$course.'_main.css" rel="stylesheet"></link>');
?>
<link href='https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic,700italic' rel='stylesheet' type='text/css'>
<link href='https://fonts.googleapis.com/css?family=PT+Sans:700,700italic' rel='stylesheet' type='text/css'>
<link href="resources/bootmin.css" rel="stylesheet"></link>
<link href="resources/badge.css" rel="stylesheet"></link>
<script src="resources/script/main.js"></script>

<!-- DIFF VIEWER STUFF -->
<script src='diff-viewer/jquery.js'></script>
<script src='diff-viewer/underscore.js'></script>
<script src='diff-viewer/highlight.js'></script>
<script src='diff-viewer/diff.js'></script>
<script src='diff-viewer/diff_queue.js'></script>
<link href="diff-viewer/diff.css" rel="stylesheet"></link>
<link href='https://fonts.googleapis.com/css?family=Inconsolata' rel='stylesheet' type='text/css'>
<?php $user = $_SESSION["id"]; ?>

<!-- FUNCTIONS USED BY THE PULL-DOWN MENUS -->
<script type="text/javascript">
function assignment_changed(){

   var php_course = "<?php echo $course; ?>";
   var php_semester = "<?php echo $semester; ?>";
  window.location.href="?semester="+php_semester+"&course="+php_course+"&assignment_id="+document.getElementById('hwlist').value+'#scroll=' + window.scrollY;
}
function version_changed(){
   var php_course = "<?php echo $course; ?>";
   var php_semester = "<?php echo $semester; ?>";
  window.location.href="?semester="+php_semester+"&course="+php_course+"&assignment_id="+document.getElementById('hwlist').value+"&assignment_version="+document.getElementById('versionlist').value+'#scroll=' + window.scrollY;
}

window.addEventListener('load', function() {
    // Do we have a #scroll in the URL hash?
    if(window.location.hash && /#scroll/.test(window.location.hash)) {
        // Scroll to the #scroll value
        window.scrollTo(0, window.location.hash.replace('#scroll=', ''));
    }
});
</script>

<!--- IDENTIFY USER & SELECT WHICH HOMEWORK NUMBER -->
<div id="HWsubmission">
    <h2 class="label">Homework Submission for <em> <?php echo $user;?> </em>
          <?php
          if (on_dev_team($user)) {
             echo "&nbsp;&nbsp;<font color=\"ff0000\"> [ dev team ]</font>";
            }

            // //Logout button to reminds users to close the browser to logout.(Only way for HTTP BSAIC AUTHENTICATION)
            // echo "<button onclick='myFunction()'>logout</button><script>function myFunction() {alert('Please close the browser to log out');}</script>";

          ?>
    </h2>

    <!-- PRIORITY HELP QUEUE-->
    <?php
    $path_front = get_path_front_course($semester,$course);;
    $priority_path = "$path_front/reports/summary_html/".$username."_priority.html";
    if (file_exists($priority_path)){
        $priority_file = file_get_contents($priority_path);
        echo $priority_file;
    }
    ?>

    <div class="sub"> <!-- sub -->
        <form class="form_submit" action="">
            <label class="label">Select Assignment:</label>
            <select id="hwlist" name="assignment_id" onchange="assignment_changed();">
                <?php
                for ($i = 0; $i < count($all_assignments); $i++)
                {
                    $FLAG = "";
                    if ($all_assignments[$i]["released"] != true)
                    {
                        if (on_dev_team($user)) {
                            $FLAG = " NOT RELEASED";
                        } else {
                            continue;
                        }
                    }
                    echo "<option value=".$all_assignments[$i]["assignment_id"];
                    if ($all_assignments[$i]["assignment_id"] == $assignment_id)
                    {
                        echo " selected";
                    }
                    echo '>'.$all_assignments[$i]["assignment_name"].$FLAG;
                    echo "</option>";
                }
                ?>
            </select>
        </form>
    </div> <!-- end sub -->

<?php
      
    //assignment name links to the assignment instructions
    echo '<h2 class="label">Assignment: ';
    if($assignment_link != '#'){
        echo' <a href="'.$assignment_link.'" target = "_blank">';
        echo $assignment_name;
        echo '</a>';
    }
    else{
        echo $assignment_name;

    }
    echo '</h2>';
?>
    <div class="panel-body"> <!-- panel-body -->
        <?php
        if ($status && $status != "") {
            echo '  <div class="outer_box">';
            echo '  <h3 class="label2">';
            echo $status;
            echo '  </h3>';
            echo '</div>';
        }
        ?>
        <!--- UPLOAD NEW VERSION -->
        <div class="outer_box"> <!-- outer_box -->
            <h3 class="label">Upload New Version</h3>
            <p class="sub">
                <?php require_once("view/".$semester."_".$course."_upload_message.php"); ?>
            </p>
            <form class="form_submit" action="<?php echo '?page=upload&semester='.$semester.'&course='.$course.'&assignment_id='.$assignment_id; ?>"
                    method="post" enctype="multipart/form-data"
                    onsubmit="return check_for_upload('<?php echo $assignment_name.', '.$highest_version.', '.$max_submissions;?>')">
                    <label for="file" class="label">Select File:</label>
                    <input type="file" name="file" id="file" />
                    <input type="submit" name="submit" value="Submit File" class="btn btn-primary">
                </form>
        </div> <!-- end outer_box -->

        <!--Description-->
        <?php
        if($assignment_description != '#'){
            echo '  <div class="outer_box">';
            echo '  <h3 class="label">Description</h3>';
            echo '<p class="sub">';
            echo $assignment_description;
            echo '</p>';
            echo '  </div>';
        }
        ?>
        

        <!------------------------------------------------------------------------>
        <!-- "IF AT LEAST ONE SUBMISSION... " -->
            <!-- INFO ON ALL VERSIONS -->
            <?php
            if ($assignment_version >= 1)
            {
                ?>
                <div class="outer_box">

                    <h3 class="label">Review Submissions</h3>

                    <!-- ACTIVE SUBMISION INFO -->

                    <div class="sub-text">
                        <div class="split-row">

                            <!-- <div>
                                <b>Active  Submission Version #
                                    <?php
                                    echo $submitting_version." of ".$highest_version.": </b> ";

                                    if ($submitting_version_in_grading_queue)
                                    {
                                        echo " is currently being graded.";
                                    }
                                    else if ($points_visible != 0)
                                    {
                                        echo "  Score: ".$submitting_version_score;
                                    }
                                    ?>
                            </div> -->

                            <!-- SELECT A PREVIOUS SUBMISSION -->

                            <form class="form_submit" action="">
                                <label class="label"><em>Select Submission Version:</em></label>
                                <input type="input" readonly="readonly" name="assignment_id" value="<?php echo $assignment_id;?>" style="display: none">
                                <select id="versionlist" name="assignment_version" onchange="version_changed();">
                                    <?php
                                    for ($i = 1; $i <= $highest_version; $i++) {
                                        echo '<option value="'.$i.'"';
                                        if ($i == $assignment_version)
                                        {
                                            echo 'selected';
                                        }
                                        echo ' > ';
                                        $field_text = array();
                                        $field_text[] = 'Version #'.$i;
                                        if ($points_visible > 0){
                                            $score_text = 'Score: ';
                                            $score_text .= $select_submission_data[$i-1]["score"];
                                            $field_text[] = $score_text;
                                        }
                                        if ($select_submission_data[$i-1]["days_late"] != "")
                                        {
                                            $days_late = 'Days Late: ';
                                            $days_late .= $select_submission_data[$i-1]["days_late"];
                                            $field_text[] = $days_late;
                                        }
                                        if ($i == $submitting_version) {
                                            $field_text[] = 'ACTIVE';
                                        }
                                        echo implode("&nbsp;&nbsp;",$field_text);
                                        echo ' </option>';
                                    }
                                    ?>
                                </select>
                            </form>
                        </div> <!-- class="split-row" -->
                        <div class="split-row" style="margin-left: 15px;"> <!-- class="sub-text" -->
                            <!-- CHANGE ACTIVE VERSION -->
                            <?php
                                if ($assignment_version != $submitting_version) {
                                    echo '&nbsp;&nbsp;&nbsp;&nbsp;';
                                    echo '<a href="?page=update&semester='.$semester.'&course='.$course.'&assignment_id='.$assignment_id.'&assignment_version='.$assignment_version.'" ';
                                    echo 'style="text-align:center;"><input type="submit" class="btn btn-primary" value="Set Version '.$assignment_version.' as ACTIVE Submission Version"></input></a>';
                                }
                                else {
                                    echo '<em>Note: This is your "ACTIVE" submission version, which will be graded by the TAs and the score recorded in the gradebook.</em>';
                                }
                            ?>
                        </div> <!-- class="split-row" -->
                    </div>
                    <!-- <?php
                    //$date_submitted = get_submission_time($user,$semester,$course,$assignment_id,$assignment_version);
                    //echo "<p><b>Date Submitted = ".$date_submitted."</b></p>";
                    ?> -->
                    <!-- SUBMITTED FILES -->
                    <div class="row sub-text">
                        <h4>Submitted Files:
                            <?php
                                if (isset($download_files) && $download_files == true){
                                    echo '<a class = "view_file"  href="?page=viewfile&semester='.$semester.'&course='.$course.'&assignment_id='.$assignment_id.'&assignment_version='.$assignment_version.'&file_name=all">Download All (as zip)</a>';
                                }
                            ?>
                        </h4>
                        <?php
                        echo '<div class="box">';
                              foreach($submitted_files as $file) {
                                  if ($file === end($submitted_files)){
                                      echo '<div>';
                                  }
                                  else{
                                      echo '<div style="border-bottom: 1px solid #dddddd;">';
                                  }
                                    //implementation of README display
                                    //ex. "code1.cpp (3kb)"
                                    $file_desc = $file["name"].' ('.$file["size"].'kb)';

                                    echo '<p class="file-header"'; 

                                        if (isset($download_files) && $download_files == true){
                                            echo '>'.$file_desc.'<a class = "view_file" href="?page=viewfile&semester='.$semester.'&course='.$course.'&assignment_id='.$assignment_id.'&assignment_version='.$assignment_version.'&file_name='.$file["name"].'">Download</a>';
                                            echo '<a class = "view_file" href="?page=viewfile&semester='.$semester.'&course='.$course.'&assignment_id='.$assignment_id.'&assignment_version='.$assignment_version.'&file_name='.$file["name"].'">Download</a>';
                                        }

                                        else if (isset($download_readme) && $download_readme == true && strtolower($file["name"]) == "readme.txt"){
                                            //extend the file-header class to listen to user clicks so that it expands
                                            echo 'href="#" onclick="return toggleDiv('."'".'filedisplay'."'".');" style="cursor:pointer;" >'.$file_desc.'<a class = "view_file" href="#">View</a>';
                                            ?>

                                            <!-- create the outermost div that will appear if display is set to "block" -->
                                            <div id="filedisplay" class="diff-block" style="display:block">

                                                <div class="file-display">
                                                    <?php //read the readme file and print out on rm-block div, line-by-line
                                                    $frontpath = get_path_front_course($semester,$course);;
                                                    $readme_fp = $frontpath.'/submissions/'.$assignment_id.'/'.$username.'/'.$assignment_version.'/'.$file["name"];
                                                    $readmfile = fopen($readme_fp,"r") or die("Unable to open file!");
                                                    while (!feof($readmfile)){
                                                        echo htmlentities(fgets($readmfile))."<br>";
                                                    }
                                                    fclose($readmfile);
                                                    ?> 

                                                </div>

                                            </div>

                                            <script> 
                                                //set readme block to display=none 
                                                toggleDiv('filedisplay');
                                            </script>

                                            <?php

                                        }
                                        else{
                                            echo '>'.$file_desc; //close off beginning tag if its a normal, non-readme and non-downloadable, file.
                                        }
                                    echo '</p>';
                                    echo '</div>';
                                }
                            echo '</div>';

                        ?>
                    </div>
                    <?php
                    if ($assignment_version_in_grading_queue) {
                    ?>
                        <span>Version <?php echo $assignment_version;?> is currently being graded.</span>
                    <?php
                    }
                    else {


//	   echo "debuga: $points_visible";


                        //Box with grades, outputs and diffs
                        render("homework_graded_display",array(
                            "assignment_message"=>$assignment_message,
                            "homework_tests"=>$homework_tests,
                            "viewing_version_score"=>$viewing_version_score,
                            "points_visible"=>$points_visible,
                            "view_points"=>$view_points,
                            "view_hidden_points"=>$view_hidden_points,
                        ));
                    }
                    ?>

                    <!-- END OF "IS GRADED?" -->
                </div>  <!-- end outer_box -->

                <?php
                if (!isset($ta_grades) || (isset($ta_grades) && $ta_grades == true)){
                    if ($ta_grade_released == true) {
                        echo '<div class="outer_box"> <!-- outer_box -->';

                        //<!--- TA GRADE -->
                            $path_front = get_path_front_course($semester,$course);;
                            $gradefile_path = "$path_front/reports/$assignment_id/".$username.".txt";
                            if (!file_exists($gradefile_path)) {
                                echo '<h3 class="label2">TA grade not available</h3>';
                            }
                            else
                            {
                                $grade_file = file_get_contents($gradefile_path);
                                echo '<h3 class="label">TA grade</h3>';
                               // echo "<em><p>Please see the <a href=\"https://www.cs.rpi.edu/academics/courses/fall14/csci1200/announcements.php\">Announcements</a> page for the curve for this homework.</p></em>";
                                    echo "<pre>".$grade_file."</pre>";
                            }
                        echo "</div> <!-- end outer_box -->";
                    }
                    else
                    {
                    //echo '<div class="outer_box"> <!-- outer_box -->';
                    //echo '<h3 class="label2">TA grades for this homework not released yet</h3>';
                    //echo "</div> <!-- end outer_box -->";

                    }
                    //<!-- END OF "IF AT LEAST ONE SUBMISSION... " -->
                }

            }

            if (!isset($grade_summary) || (isset($grade_summary) && $grade_summary == true)){
                $path_front = get_path_front_course($semester,$course);;
                $gradefile_path = "$path_front/reports/summary_html/".$username."_summary.html";
                if (!file_exists($gradefile_path))
                {
                    // echo '<div class="outer_box"> <!-- outer_box -->';
                    // echo '<h3 class="label2">Grade Summary not available</h3>';
                    // echo "</div> <!-- end outer_box -->";

                }
                else
                {
                    echo '<div class="outer_box"> <!-- outer_box -->';
                    $grade_file = file_get_contents($gradefile_path);
                    echo $grade_file;
                    echo "</div> <!-- end outer_box -->";

                }
            }
            ?>
            <!------------------------------------------------------------------------>
    </div> <!-- end panel-body -->
</div> <!-- end HWsubmission -->

</body>
<script>
    function check_for_upload(assignment, versions_used, versions_allowed) {
        versions_used = parseInt(versions_used);
        versions_allowed = parseInt(versions_allowed);
        if (versions_used >= versions_allowed) {
            var message = confirm("Are you sure you want to upload for " + assignment + " ?  You have already used up all of your free submissions (" + versions_used + " / " + versions_allowed + ").  Uploading may result in loss of points.");
            return message;
        }
        return true;
    }
    // Go through diff queue and run viewer
    loadDiffQueue();

    //Set time between asking server if the homework has been graded
    //Last argument in ms
    //TODO: Set time between server requests (currently at 5 seconds = 5000ms)
    //                                                     (previously at 1 minute = 60000ms)
    <?php
    if ($assignment_version_in_grading_queue || $submitting_version_in_grading_queue) {
        echo 'init_refresh_on_update("'.$semester.'", "'.$course.'", "'.$assignment_id.'", "'.$assignment_version.'", "'.$submitting_version.'", "'.!$assignment_version_in_grading_queue.'", "'.!$submitting_version_in_grading_queue.'", 5000);';
    }
    ?>
    </script>
</div>

</html>
