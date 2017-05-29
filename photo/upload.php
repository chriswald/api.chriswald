<?php

function AllowedExtension($extension)
{
    $extension = strtoupper($extension);
    return ($extension == "JPG" ||
        $extension == "PNG" ||
        $extension == "JPEG" ||
        $extension == "WMV" ||
        $extension == "MOV" ||
        $extension == "MP4");
}

function GetFullFileName($filename, $extension)
{
	$uploaddir = realpath($_SERVER["DOCUMENT_ROOT"] ."/../content.chriswald.com/photot/images/");
	$md5name = md5($filename . time());
    $uploadfile = $uploaddir . $md5name . "." . $extension;

    return $uploadfile;
}

function FileSizeOK()
{
	// File has 10kb min size and 500mb max size
	$maxSize = 500.0 * 1024.0 * 1024.0;
	$minSize = 10.0 * 1024.0;

	$size = $_FILES["file"]["size"];
	return ($size <= $maxSize &&
			$size >= $minSize);
}

function FileTypeOK()
{
	$filename = basename($_FILES["file"]["name"]);
	$path_parts = pathinfo($filename);
	
	return (AllowedExtension($path_parts["extension"]));
}

function PerformUpload()
{
	if (!FileSizeOK())
	{
		header("HTTP/1.0 400 Bad Request");
		exit;
	}
	
	if (!FileTypeOK())
	{
		header("HTTP/1.0 400 Bad Request");
		exit;
	}
	
	$filename = basename($_FILES["file"]["name"]);
	$path_parts = pathinfo($filename);
	
	$fullfilename = GetFullFileName($filename, $path_parts["extension"]);
	
	move_uploaded_file($_FILES["file"]["tmp_name"], $fullfilename);
}

PerformUpload();

?>