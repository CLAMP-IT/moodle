(function($){
    $.inboxTable = {
    init: function( uid, displayusi, turnitintool_datatables_strings ) {
        $.fn.dataTableExt.oStdClasses.sSortable = "header sort";
        $.fn.dataTableExt.oStdClasses.sSortableNone = "header nosort";
        $.fn.dataTableExt.oStdClasses.sSortAsc = "header asc";
        $.fn.dataTableExt.oStdClasses.sSortDesc = "header desc";
        $.fn.dataTableExt.oStdClasses.sWrapper = "submissionTable";
        $.fn.dataTableExt.oStdClasses.sStripeOdd = "row r0";
        $.fn.dataTableExt.oStdClasses.sStripeEven = "row r1";
        var oTable = $("#inboxTable").dataTable( {
            "aoColumns": [
                    { "bSearchable": false, "bSortable": true, "bVisible": false }, // [0] Sort ID Column, Unique to preserver sorting when filtering
                    { "bSearchable": true, "bSortable": true, "bVisible": false }, // [1] Sort Display title, is shown in the sort header row
                    { "bSearchable": true, "bSortable": true, "bVisible": true, "aDataSort": [ 1, 2 ] }, // [2] The first column shown, contains the file name, user name, part name, anon button
                    { "bSearchable": true, "bSortable": true, "bVisible": false }, // [3] Column to store the USI (Unique Student Identifier), used with Rosetta Pseudo behaviour
                    { "bSearchable": false, "bSortable": true, "bVisible": displayusi, "iDataSort": [ 3 ] }, // [4] USI field, blank but will store the USI in row header if enabled
                    { "bSearchable": true, "bSortable": true, "sType": "numeric", "bVisible": true }, // [5] Paper ID Column
                    { "bSearchable": false, "bSortable": true, "sType": "date", "bVisible": true }, // [6] The Submitted Date
                    { "bSearchable": false, "bSortable": true, "asSorting": [ "desc" ], "sType": "numeric", "bVisible": false }, // [7] Raw data for the Similarity score
                    { "bSearchable": false, "bSortable": true, "sType": "numeric", "bVisible": true, "iDataSort": [ 7 ] }, // [8] Similarity Display Column, the HTML for the originality report launch
                    { "bSearchable": true, "bSortable": true, "asSorting": [ "desc" ], "bVisible": false }, // [9] Overall Grade Column, Used to sort and to Display above the Grade Display Column
                    { "bSearchable": false, "bSortable": true, "sType": "numeric", "bVisible": false }, // [10] Raw data for the submission grade, Used to sort
                    { "bSearchable": false, "bSortable": true, "sType": "numeric", "bVisible": true, "aDataSort": [ 10 ] }, // [11] Grade Display Column, the HTML for the grademark launch
                    { "bSearchable": false, "bSortable": false, "bVisible": true }, // [12] Student View Indicator column
                    { "bSearchable": false, "bSortable": false, "bVisible": true }, // [13] Comments / Feed Back column
                    { "bSearchable": false, "bSortable": false, "bVisible": true }, // [14] Download Button column
                    { "bSearchable": false, "bSortable": false, "bVisible": true }, // [14] Refresh Row column
                    { "bSearchable": false, "bSortable": false, "bVisible": true } // [15] Delete Button column
                ],
            "fnDrawCallback": function ( oSettings ) {

                if ( typeof oTable == "undefined" ) {
                    return;
                }

                if ( oSettings.aiDisplay.length == 0 ) {
                    return;
                }

                var nTrs = $("#inboxTable tbody tr");
                var sLastGroup = "";
                for ( var i=0 ; i < nTrs.length; i++ ) {
                    
                    colspan = ( displayusi ) ? 13 : 12;
                    studentcol = 2;
                    usicol = ( displayusi ) ? 3 : 99;
                    gradecol = ( displayusi ) ? 7 : 6;
                    idcol = gradecol - 3;
                    datecol = gradecol - 2;
                    similaritycol = gradecol - 1;
                    
                    var iDisplayIndex = oSettings._iDisplayStart + i;
                    var sort = oSettings.aoData[ oSettings.aiDisplay[iDisplayIndex] ]._aData[0];
                    var sort_table = oSettings.aoData[ oSettings.aiDisplay[iDisplayIndex] ]._aData[1];
                    var usi = oSettings.aoData[ oSettings.aiDisplay[iDisplayIndex] ]._aData[3];
                    var paperid = oSettings.aoData[ oSettings.aiDisplay[iDisplayIndex] ]._aData[5];
                    var overallgrade = oSettings.aoData[ oSettings.aiDisplay[iDisplayIndex] ]._aData[9];

                    if ( sort != sLastGroup ) {
                        var nGroup = document.createElement( "tr" );

                        for ( n = 2; n < colspan; n++ ) {
                            var nCell = document.createElement( "td" );
                            switch ( n ) {
                                case studentcol:
                                    // Student / Part Col
                                    nCell.setAttribute ("class", "namecell group" );
                                    nCell.innerHTML = sort_table;
                                    break;
                                case usicol:
                                    // USI col
                                    nCell.setAttribute ("class", "markscell group");
                                    nCell.innerHTML = usi;
                                    break;
                                case idcol:
                                    // Paper ID col
                                    nCell.setAttribute ("class", "markscell group");
                                    nCell.innerHTML = "&nbsp;";
                                    break;
                                case datecol:
                                    // Date col
                                    nCell.setAttribute ("class", "datecell group");
                                    nCell.innerHTML = "&nbsp;";
                                    break;
                                case similaritycol:
                                    // Sililarity col
                                    nCell.setAttribute ("class", "markscell group");
                                    nCell.innerHTML = "&nbsp;";
                                    break;
                                case gradecol:
                                    // Grade col
                                    nCell.setAttribute ("class", "markscell group");
                                    overallgrade = ( overallgrade < 0 ) ? '' : overallgrade; 
                                    nCell.innerHTML = overallgrade;
                                    break;
                                default:
                                    // Default col
                                    nCell.setAttribute ("class", "iconcell group");
                                    nCell.innerHTML = "&nbsp;";
                                    break;
                            }
                            nGroup.appendChild( nCell );
                        }

                        nTrs[i].parentNode.insertBefore( nGroup, nTrs[i] );
                        sLastGroup = sort;
                        
                    }
                    
                    // Hide the emtpy rows
                    if ( paperid == "&nbsp;&nbsp;" ) {
                        nTrs[i].style.display = 'none';
                    }

                }

                $("#turnitintool_style .paginate_disabled_previous").css( "background", "url(pix/prevdisabled.png) no-repeat left center" );
                $("#turnitintool_style .paginate_enabled_previous").css( "background", "url(pix/prevenabled.png) no-repeat left center" );
                $("#turnitintool_style .paginate_disabled_next").css( "background", "url(pix/nextdisabled.png) no-repeat right center" );
                $("#turnitintool_style .paginate_enabled_next").css( "background", "url(pix/nextenabled.png) no-repeat right center" );
                $("#turnitintool_style .dataTables_processing").css( "background", "url(pix/loaderanim.gif) no-repeat center center" );
                $("#inboxTable th.sort").css( "background", "url(pix/sortnone.png) no-repeat right center" );
                $("#inboxTable th.asc").css( "background", "url(pix/sortdown.png) no-repeat right center" );
                $("#inboxTable th.desc").css( "background", "url(pix/sortup.png) no-repeat right center" );
                $("#inboxTable a.fileicon").css( "background", "url(pix/fileicon.gif) no-repeat left center" ).css( "padding", "0px 0px 0px 16px" );

            },
            "aaSorting": [[ 9, "desc" ],[ 7, "desc"],[ 1, "asc" ]],
            "sPaginationType": "full_numbers",
            "sDom": "r<\"top navbar\"lf><\"dt_page\"pi>t<\"bottom\"><\"dt_page\"pi>",
            "oLanguage": turnitintool_datatables_strings,
            "bStateSave": true,
            "fnStateSave": function (oSettings, oData) {
                try {
                    localStorage.setItem( uid+'DataTables', JSON.stringify(oData) );
                } catch ( e ) {
                }
            },
            "fnStateLoad": function (oSettings) {
                try {
                    return JSON.parse( localStorage.getItem(uid+'DataTables') );
                } catch ( e ) {
                }
            }
        } );
        oTable.fnStandingRedraw();
        $("#inboxTable").css( "display", "table" );
        oTable.fnSetFilteringDelay(1000);
        return oTable;
    }
};

    $('.refreshrow').bind('click', function( ev ) {
        element = $(ev.currentTarget);
        id = element.prop('id').split('-')[1];
        objectid = element.prop('id').split('-')[4];
        jQuery('#inboxNotice').css( 'display', 'block' );
        $.ajax({
            url: 'view.php?do=allsubmissions&reloadrow='+objectid+'&id='+id,
            success: function() {
                window.location.href = 'view.php?do=allsubmissions&id='+id;
            }
        });

    });
    
})(jQuery);