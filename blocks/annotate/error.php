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
 * Annotate block error page to be displayed when problem transferring doc to Annotate
 *
 * @package   block_annotate
 * @copyright Textensor Ltd.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname ( __FILE__ ) . '/../../config.php');
?>

<html>
<head>
</head>
<body style="padding: 60px">
    <h2><?php print get_string("transfer_error", "block_annotate"); ?></h2>
    <p><?php print get_string("transfer_error_msg", "block_annotate"); ?></p>
    <p><i><?php print optional_param('msg', "", PARAM_TEXT);; ?></i></p>
</body>
</html>
