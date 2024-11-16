<?php
function handleImage( $action, $format, $entity, $id)
{
$filename=DATADIR."{$entity}/{$id}.png";
$thumbfilename=DATADIR."{$entity}/{$id}_thumb.png";


    error_log("*** handleImage({$action},{$format},{$entity},{$id}) - ".$filename);
    checkAndCreateDirectory(DATADIR."{$entity}");

    switch ($action) {
        case "read":
            if(file_exists($filename)) {
                switch($format) {
                    case 'dataURI':
                        return 'data:image/png;base64,'.base64_encode(file_get_contents($filename));
                    case 'dataURI_thumb':
                        return 'data:image/png;base64,'.base64_encode(file_get_contents($thumbfilename));
                    break;
                    case 'base64':
                        return base64_encode(file_get_contents($filename));
                    break;
                    case 'stdout':
                        readfile($filename);
                        break;
                    case 'png':
                        return file_get_contents($filename);
                        break;
                    case 'png_thumb':
                        return file_get_contents($thumbfilename);
                        break;
                default:
                        return null;
                    break;
                };
            }
            break;
        case "store":
            switch($format) {
                case 'png':
                    if ( array_key_exists('photo', $_FILES)) {
                        $ret = move_uploaded_file($_FILES['photo']['tmp_name'], $filename);
                        //$ret = move_uploaded_file($_FILES['photo']['tmp_name'], "{$id}.png");
                    }
                    make_thumb($filename, $thumbfilename, 80); 
                    return true;
                break;
                case 'zip':
                    if ( array_key_exists('photo', $_FILES)) {
                        $zip = new ZipArchive();
                        if ($zip->open( PHOTODIR."photo/{$entity}/{$id}.zip", ZipArchive::CREATE)!==TRUE) {
                            return false;
                        }
                        $zip->addFile ($_FILES['photo']['tmp_name'] );
                        $zip->close();
                    }
                    return true;
                break;
                case 'base64':
                    return false;
                break;
                case 'dataURI':
                    return false;
                default:
                    return false;
                break;
            };
            break;
            case "remove":
                if(file_exists($filename)) {
                    unlink($filename);
                }
                break;
        default:
            break;
    }
}

function checkAndCreateDirectory($directory) {
    try {
        if (!is_dir($directory)) {
            if (!mkdir($directory, 0777, true))
            {
                throw new Exception("Error creating the directory.");
            }

    
        }
        else
        {
            
        }
    }
    catch (Exception $e)
    {
        
    }
}

function make_thumb($src, $dest, $desired_width) {

    /* read the source image */
    $source_image = imagecreatefrompng($src);
    $width = imagesx($source_image);
    $height = imagesy($source_image);

    /* find the "desired height" of this thumbnail, relative to the desired width  */
    $desired_height = floor($height * ($desired_width / $width));

    /* create a new, "virtual" image */
    $virtual_image = imagecreatetruecolor($desired_width, $desired_height);

    /* copy source image at a resized size */
    imagecopyresampled($virtual_image, $source_image, 0, 0, 0, 0, $desired_width, $desired_height, $width, $height);

    /* create the physical thumbnail image to its destination */
    imagepng($virtual_image, $dest);
}
?>