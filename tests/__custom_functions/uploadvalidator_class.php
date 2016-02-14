<?php

namespace Alirdn\SecureUPload\Validators;

function is_uploaded_file($file_path)
{
    return file_exists($file_path);
}