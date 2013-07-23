<?php
/* Copyright Panopto 2009 - 2013 / With contributions from Spenser Jones (sjones@ambrose.edu)
 * 
 * This file is part of the Panopto plugin for Moodle.
 * 
 * The Panopto plugin for Moodle is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * The Panopto plugin for Moodle is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with the Panopto plugin for Moodle.  If not, see <http://www.gnu.org/licenses/>.
 */
?>

<div class='block_panopto'>
<div class='courseProvisionResult'>
    <div class='attribute'>Course Name</div>
    <div class='value'><?php echo $provisioning_data->ShortName . ": " . $provisioning_data->LongName ?></div>

    <div class='attribute'>Instructors</div>
    <div class='value'>
        <?php
        if(!empty($provisioning_data->Instructors)) {
            $instructors = $provisioning_data->Instructors;
            // Single-element return set comes back as scalar, not array (?)
            if(!is_array($instructors)) {
                $instructors = array($instructors);
            }    
            $instructor_info = array();
            foreach($instructors as $instructor) {
                array_push($instructor_info, "$instructor->UserKey ($instructor->FirstName $instructor->LastName &lt;$instructor->Email&gt;)");
            }

            echo join("<br />", $instructor_info);
        } else {
            ?><div class='errorMessage'>No instructors.</div><?php
        }
        ?>
    </div>
    <div class='attribute'>Students</div>
    <div class='value'>
        <?php
        if(!empty($provisioning_data->Students)) {
            $students = $provisioning_data->Students;
            // Single-element return set comes back as scalar, not array (?)
            if(!is_array($students)) {
                $students = array($students);
            }
            $student_info = array();
            foreach($students as $student) {
                array_push($student_info, $student->UserKey);
            }

            echo join(", ", $student_info);
        } else {
            ?><div class='errorMessage'>No students.</div><?php
        }
        ?>
    </div>
    <div class='attribute'>Result</div>
    <div class='value'>
    <?php 
    if(!empty($provisioned_data)) {
    ?>
        <div class='successMessage'>Successfully provisioned course {<?php echo $provisioned_data->PublicID ?>}</div>
    <?php 
    } else {
    ?>
        <div class='errorMessage'>Error provisioning course.</div>
    <?php 
    }
    ?>
    </div>
</div>
</div>