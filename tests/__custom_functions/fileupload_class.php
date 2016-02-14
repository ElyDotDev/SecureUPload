<?php

namespace Alirdn\SecureUPload\UploadFolder;

function move_uploaded_file($source, $destination)
{
    if (file_exists($source)) {
        return rename($source, $destination);
    }

    return false;
}