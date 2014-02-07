YUI.add('moodle-report_roster-roster', function(Y) {
    M.report_roster = {
        init : function() {
            //Y.all("ul.roster-report li span").setStyle("display", "none");
            var toggle = Y.one('button#report-roster-toggle');
            toggle.on('click', function () {
                if (toggle.get('innerHTML') == M.util.get_string('learningmodeoff' ,'report_roster')) {
                    Y.all('ul.report-roster li span').setStyle('display', 'none');
                    toggle.set('innerHTML', M.util.get_string('learningmodeon', 'report_roster'));
                } else {
                    Y.all('ul.report-roster li span').setStyle('display', 'block');
                    toggle.set('innerHTML', M.util.get_string('learningmodeoff', 'report_roster'));
                }
            });
        }
    }
});
