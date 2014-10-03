<?php
function get_repomanager_instance($repoi, $course)
{
    switch ($repoi->get_meta()->type)
    {
        case "coursefilearea":
            require_once("coursefilearea.php");
            return new block_repofile_coursefilearea($course);

        case "local":
        case "recent":
        case "user":
            require_once("generic.php");
            return new block_repofile_generic($repoi);

        case "filesystem":
            require_once("filesystem.php");
            return new block_repofile_filesystem($repoi);

        /*
        case "user":
            require_once("mdlinternal.php");
            return new block_repofile_mdlinternal($repoi);

        case "youtube":
            require_once("youtube.php");
            return new block_repofile_youtube($repoi);
        */
    }

    require_once('block_repofile_type.php');
    return new block_repofile_type();
}
?>
