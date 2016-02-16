"use_strict";
/*jsline browser: true*/
/*global M*/
/*global Y*/

var FCLA;

M.block_filtered_course_list = M.block_filtered_course_list || {};
FCLA = M.block_filtered_course_list.accordion = {};

FCLA.init = function () {
  Y.on('domready', function () {
    var sectionTitles = Y.all('.block_filtered_course_list .course-section');
    sectionTitles.each(function (title) {
      if (!(title.hasClass('expanded'))) {
        title.addClass('collapsed');
        title.setAttribute('aria-expanded', 'false');
        title.getDOMNode().nextSibling.setAttribute('aria-hidden', 'true');
      }
      var html = title.getHTML();
      title.setHTML('<a href="#">' + html + '</a>');
      title.on('click', function (e) {
        e.preventDefault();
        sectionTitles.each(function (title) {
          title.setAttribute('aria-selected', 'false');
        });
        title.setAttribute('aria-selected', 'true');
        FCLA.toggle(this);
      });
    });
  });
};

FCLA.toggle = function (title) {
  if (title.hasClass('collapsed')) {
    title.removeClass('collapsed');
    title.addClass('expanded');
    title.setAttribute('aria-expanded', 'true');
    title.getDOMNode().nextSibling.setAttribute('aria-hidden', 'false');
  }
  else if (title.hasClass('expanded')) {
    title.removeClass('expanded');
    title.addClass('collapsed');
    title.setAttribute('aria-expanded', 'false');
    title.getDOMNode().nextSibling.setAttribute('aria-hidden', 'true');
  }
};
