// function to search for a given query string
function findCourseOnM1BySearch(query) {

	searchLink=document.getElementById('wes-course-search');
	searchLink.href="https://moodle1.wesleyan.edu/course/search.php?search="+query;
	searchLink.innerHTML="Find this course on Moodle 1"

}

// function to extract the HTML <title> and search for it
// NOT currently used, but retained for reference
function findCourseOnM1ByTitle() {

	var titles;
	var title;
	var searchString;
	var searchLink;

	titles=document.getElementsByTagName('title');
	title=titles[0];
	searchString=title.innerHTML;
	searchString=searchString.replace(/^Course: /, "");
	searchString=searchString.replace(/ \(link\)$/, "");
	searchString=searchString.replace(/ /g,"+");

	searchLink=document.getElementById('wes-course-search');
	searchLink.href="https://moodle1.wesleyan.edu/course/search.php?search="+searchString;
	searchLink.innerHTML="Find this course on Moodle 1"

}

// function to extract shortname and create a link directly to 
// the corresponding course
function findCourseOnM1() {

	var header;
	var headerDivs;
	var breadcrumb;
	var breadcrumbAs;
	var shortnameA;
	var shortname

		header=document.getElementById('page-header');
	headerDivs=header.getElementsByTagName('div');

	  for (var i=0; i<headerDivs.length; i++) {
		    if (headerDivs[i].className=="breadcrumb") {
				  breadcrumb = headerDivs[i];
				    }
			  }
	    
	  breadcrumbAs=breadcrumb.getElementsByTagName('A');
	  shortnameA=breadcrumbAs[breadcrumbAs.length-1];
	  shortname=shortnameA.innerHTML;
	  shortname=shortname.replace(/_link$/,'');

	  // fall back to a search for E&ES courses
	  if (shortname.match(/E&amp;/)) {
		  shortname=shortname.replace("E&amp;","");
		  findCourseOnM1BySearch(shortname);
		  return;
	  }

	  searchLink=document.getElementById('wes-course-search');
	  searchLink.href="https://moodle1.wesleyan.edu/course/view.php?name="+shortname;
	  searchLink.innerHTML="Go to this course on Moodle 1"

}

findCourseOnM1();
