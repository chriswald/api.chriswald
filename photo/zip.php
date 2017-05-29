<?php
set_time_limit(300);
$zip = new ZipArchive();
$filename = "zip/trip_photos.zip";

if ($zip->open($filename, ZipArchive::CREATE || ZipArchive::OVERWRITE) === TRUE)
{
    $directory = realpath($_SERVER["DOCUMENT_ROOT"] ."/../content.chriswald.com/photot/images/");
    $files = scandir($directory);
    foreach ($files as $file)
    {
        if (!is_dir("$directory/$file"))
        {
            $zip->addFile("images/$file", $file);
        }
    }
    
    $zip->close();
}
else
{
    die("Couldn't open " . $filename);
}
?>