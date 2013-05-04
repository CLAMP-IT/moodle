<?php
require_once("lib/panopto_data.php");

// parses query string vars into PHP vars of the same name
parse_str($_SERVER['QUERY_STRING']);

if(empty($course_ids))
{
	print_header("Provision Courses", "Provision Courses");
	?>
	<div id="provisionContents">
		<form onsubmit="return joinCourseIDs(this)">
			<input type="hidden" name="return_url" value="<?php echo $return_url ?>" />
			<input type="hidden" name="course_ids" />
	
			<div id="provisionInstructions">
				Select courses to add to Panopto Focus.<br />
				Multiple selections are possible by Ctrl-clicking (Windows) or Cmd-clicking (Mac).
			</div>
			<select id="courseMultiSelect" multiple="multiple">
				<?php 
					echo panopto_data::get_moodle_course_options_html();
				?>
			</select>
	
			<div>		
				<input type="submit" value="Submit" />
			</div>	
		</form>
	</div>
	<script type="text/javascript">
		function joinCourseIDs(form)
		{
			var options = form.courseMultiSelect.options;

			var selectedOptions = Array();
			for(var i=0; i<options.length; i++)
			{
				var option = options[i];
				if(option.selected)
				{
					selectedOptions.push(option.value);
				}
			}

			form.course_ids.value = selectedOptions.join(",");

			return form.course_ids.value;
		}
	</script>
<?php
}
else
{
	print_header("Provisioning Results", "Provisioning Results");
	?>
	<div id="provisionContents">
	<?php 
	// Use one panopto_data object for all provisioning calls to avoid instantiating multiple soap clients.
	// Construct without $moodle_course_id, will be set per course later.
	$panopto_data = new panopto_data(null);

	$provision_courses = split(",", $course_ids);
	foreach($provision_courses as $provision_course_id)
	{
		if(empty($provision_course_id)) continue;
		
		// Set the current Moodle course to retrieve info for / provision.
		$panopto_data->moodle_course_id = $provision_course_id;
		$provisioning_data = $panopto_data->get_provisioning_info();
		?>
		<div class='courseProvisionResult'>
			<div class='attribute'>Course Name</div>
			<div class='value'><?php echo $provisioning_data->ShortName . ": " . $provisioning_data->LongName ?></div>
							  
			<div class='attribute'>Instructors</div>
			<div class='value'>
				<?php
				if(!empty($provisioning_data->Instructors))
				{
					$instructors = $provisioning_data->Instructors;
					// Single-element return set comes back as scalar, not array (?)
					if(!is_array($instructors))
					{
						$instructors = array($instructors);
					}	
					$instructor_info = array();
					foreach($instructors as $instructor)
					{
						array_push($instructor_info, "$instructor->UserKey ($instructor->FirstName $instructor->LastName &lt;$instructor->Email&gt;)");
					}
					
					echo join("<br />", $instructor_info);
				}
				else
				{
					?><div class='errorMessage'>No instructors.</div><?php
				}
				?>
			</div>
			<div class='attribute'>Students</div>
			<div class='value'>
				<?php
				if(!empty($provisioning_data->Students))
				{
					$students = $provisioning_data->Students;
					// Single-element return set comes back as scalar, not array (?)
					if(!is_array($students))
					{
						$students = array($students);
					}
					$student_info = array();
					foreach($students as $student)
					{
						array_push($student_info, $student->UserKey);
					}
					
					echo join(", ", $student_info);
				}
				else
				{
					?><div class='errorMessage'>No students.</div><?php
				}
				?>
			</div>
			<?php $provisioned_data = $panopto_data->provision_course($provisioning_data); ?>
			<div class='attribute'>Result</div>
			<div class='value'>
			<?php 
			if(!empty($provisioned_data))
			{
			?>
				<div class='successMessage'>Successfully provisioned course {<?php echo $provisioned_data->PublicID ?>}</div>
			<?php 
			}
			else
			{
			?>
				<div class='errorMessage'>Error provisioning course.</div>
			<?php 
			}
			?>
			</div>
	</div>
	<?php
	}
}
?>
</div>

<div id="provisionBackLink">
	<a href="<?php echo urldecode($return_url) ?>">Back to config</a>
</div>

<?php print_footer() ?>
