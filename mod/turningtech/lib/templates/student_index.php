<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
/**
 * Template to display student page
 * @package   mod_turningtech
 * @copyright 2012 Turning Technologies
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
$turningtechdevice       = false;
$turningtechresponseware = false;
$disableflag             = false;

if (strpos($device_list, 'ttdevice')) {
    $turningtechdevice = true;
}

if (strpos($device_list, 'ttresponseware')) {
    $turningtechresponseware = true;
}
if ($CFG->turningtech_device_selection == TURNINGTECH_DISABLE_RESPONSEWARE) {
    $turningtechresponseware = true;
    $disableflag             = true;
}

?>

<div id='turningtech-device-page'>

<?php
echo turningtech_show_messages();
    ?>

    <p>
<?php
echo get_string('toreceivecredit', 'turningtech');
    ?>
   </p>

    <div class="rw-image-container">

        <div class="responsecard-container">
<?php
if (!$turningtechdevice || $disableflag) {
    ?>
               <h3>
                    <a onclick="javascript:unhidett('divResponseCard');" href='#divResponseCard'>
    <?php
    echo get_string('responsecard', 'turningtech');
    ?>
                     </a>
                </h3>
                <p>
    <?php
    echo get_string('handheldclickerdevice', 'turningtech');
    ?>
               </p>
                <a  onclick="javascript:unhidett('divResponseCard');" href='#divResponseCard'>
                    <img src='http://www.turningtechnologies.com/images/rcard1and2_varient.jpg' />
                </a>
    <?php
} else {
    ?>
               <h3>
    <?php
    echo get_string('responsecard', 'turningtech');
    ?>
               </h3>
                <p>
    <?php
    echo get_string('handheldclickerdevice', 'turningtech');
    ?>
               </p>
                <img src='http://www.turningtechnologies.com/images/rcard1and2_varient.jpg' />
    <?php
}
    ?>
       </div>
    <?php
if ($CFG->turningtech_device_selection != TURNINGTECH_DISABLE_RESPONSEWARE) {
    ?>
       <div class="responseware-container">
    <?php
    if (!$turningtechresponseware) {
        ?>
               <h3>
                  <a onclick="javascript:unhidett('divResponseWare');" href='#divResponseWare'>
    <?php
        echo get_string('responseware', 'turningtech');
        ?>
                 </a>
                </h3>
                <p>
    <?php
        echo get_string('websystem', 'turningtech');
        ?>
               </p>
                <a onclick="javascript:unhidett('divResponseWare');" href='#divResponseWare'>
                    <img src='http://www.turningtechnologies.com/images/rware.jpg' />
                </a>
    <?php
    } else {
        ?>
               <h3>
    <?php
        echo get_string('responseware', 'turningtech');
        ?>
               </h3>
                <p>
    <?php
        echo get_string('websystem', 'turningtech');
        ?>
               </p>
                <img src='http://www.turningtechnologies.com/images/rware.jpg' />
    <?php
    }
    ?>
       </div>
                <?php
}
    ?>
   </div>


    <div class="clear-both"></div>

  <!--  <hr class="device-divider" />  -->

    <div class="my-devices-container">
      <h3>
    <?php
echo get_string('myregdevice', 'turningtech');
    ?>
     </h3>
    <p id="errormessage" style="paddin-bottom:10px;"></p>
    <?php
echo $device_list;
    ?>
   </div>
<!--    <hr class="device-divider" />    -->


    <script type="text/javascript">
        var leaveOpen = false;
    <?php
if ($leaverescardfrmopen || $disableflag) {
    ?>
       var leaveResCardFrmOpen = true;
    <?php
} else {
    ?>
       var leaveResCardFrmOpen = false;
    <?php
}
if ($leavereswarefrmopen) {
    ?>
       var leaveResWareFrmOpen = true;
    <?php
} else {
    ?>
       var leaveResWareFrmOpen = false;
    <?php
}
    ?>
   </script>

    <div class="form-container">
  <a id="responsecard-anchor" name="responsecard"></a>
      <div xid="responsecard-collapse-group">
            <h3 xclass="uncollapsed" style="text-align:left;">&nbsp;
    <?php
if (!$turningtechdevice || $disableflag) {
    ?>
             <a onclick="javascript:unhidett('divResponseCard');" href='#divResponseCard'>
    <?php
    echo get_string('ifyouareusingresponsecard', 'turningtech');
    ?>
             </a>
    <?php
} else {
    ?>

    <?php
    echo get_string('ifyouareusingresponsecard', 'turningtech');
    ?>
             
    <?php
}
    ?>
           </h3>
            <div xclass="collapsed" id="divResponseCard" class="hiddens" style="text-align:left;">
    <?php
if (!$turningtechdevice || $disableflag) {
    ?>
               <h3 style="text-align:center;">
    <?php
    echo get_string('registeradevice', 'turningtech');
    ?>
    </h3>
    <p style='text-align:center;'>
    <?php
    echo get_string('forhelp', 'turningtech');
    ?>
    </p>
              <p  style='text-align:left; margin-left:15%'>
    <?php
    echo get_string('responsecardheadertext', 'turningtech');
    ?>
           </p>
              <div class="responsecard-group" id="responsecardgroupid">
                  <table>
                      <tr>
                          <td>
    <?php
    $editform->display();
    ?>
                       </td>
                          <td><img class="enterid" src="http://www.turningtechnologies.com/images/RCRF_StudentID3.jpg" /></td>
                      </tr>
                  </table>
                <div class="clear-both"></div>
            </div>
    <?php
}
    ?>           </div>
        </div>


        <a id="responseware-anchor" name="responseware"></a>
        <div xid="responseware-collapse-group">
            <h3 xclass="uncollapsed"  style="text-align:left;">&nbsp;
    <?php
if (!$turningtechresponseware) {
    ?>
               <a onclick="javascript:unhidett('divResponseWare');" href='#divResponseWare'>
    <?php
    echo get_string('ifyouareusingresponseware', 'turningtech');
    ?>
               </a>
    <?php
} else {
    if ($CFG->turningtech_device_selection != TURNINGTECH_DISABLE_RESPONSEWARE) {
        echo get_string('ifyouareusingresponseware', 'turningtech');
    }
}
    ?>

            </h3>
            <div xclass="collapsed" id="divResponseWare" class="hiddens">
    <?php
if (!$turningtechresponseware) {
    ?>
             <p>&nbsp;
    <?php
    echo get_string('responsewareheadertext', 'turningtech');
    ?>
           </p>
              <div class="responseware-group" id="responsewaregroupid">
    <?php
    if ($CFG->turningtech_device_selection != TURNINGTECH_CUSTOM_RESPONSEWARE) {
        $rwform->display();
        if ($rurl = TurningTechTurningHelper::getresponsewareurl()) {
            $url = $rurl;
        } else {
            $url = "http://www.rwpoll.com/";
        }
        $joinlink = get_string('tocreateanaccount1', 'turningtech');
        $joinlink .= "<a href='" . $url . "'>" . $url . "</a>";
        $joinlink .= get_string('tocreateanaccount2', 'turningtech');
    } else {
        $customform->display();
    }
    ?>
            <!-- <p class="responseware-join-link"> -->
       <!--     </p> -->
              <div class="clear-both"></div>
            </div> 
    <?php
}
    ?></div>
      </div>

    </div> <!--  /form-container -->

</div><!--  /turningtech-device-page -->