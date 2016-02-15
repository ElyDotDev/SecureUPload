# SecureUPload – PHP secure file upload package [![Build Status](https://travis-ci.org/alirdn/SecureUPload.svg?branch=master)](https://travis-ci.org/alirdn/SecureUPload)
![SecureUPload – PHP secure file upload package banner](resources/banner-big.png?raw=true "SecureUPload – PHP secure file upload package banner")
**SecureUPload** is a PHP composer package to securely upload files. SecureUPload uses best practices for uploading files in PHP, so you can use it without any file upload security headaches. Furthermore, it’s flexible enough that fits to most of different projects structures.

## Why SecureUPload?
File upload is a risky part in all web applications. There are multiple ways that an attacker could attack web application by file upload feature. So whenever a web application wants to add this feature, developers needs to write lots of code for make it risk free. But file uploads could be secure, if we don’t trust users provided data (including HTTP headers and files) and check everything carefully. For more information about file upload risks please see: [OWASP Unrestricted File Upload](https://www.owasp.org/index.php/Unrestricted_File_Upload)

SecureUPload uses best practices steps for making a file upload secure. By using SecureUpload package, developer can focuses on other aspects of project and be sure about file uploads.

## Features
- Single and multiple input file/files upload support
- Support storing uploaded files in different location. For more info see storage_type SecureUPloadConfig section
- Support different uploaded files organization
- Configure accepted upload file types and minimum/maximum file size globally or upload specific
- Zero dependency for production
- Different error codes for invalid uploaded files for better error handling

## Installation
Because SecureUPload has zero dependency, it can be installed as a composer package, or without composer and as a PHP library.
 
### Install as composer package
```bash
$ composer install alirdn/secureupload
```
 
### Install as PHP library
You must download it from project github page. Then unzip it and include src/autoloader.php file in your PHP project. All done!

## Basic Usage
```php
<?php

use Alirdn\SecureUPload\Config\SecureUPloadConfig;
use Alirdn\SecureUPload\SecureUPload;

// Create SecureUPloadConfig and set Uploaded files folder
$SecureUPloadConfig = new SecureUPloadConfig;
$SecureUPloadConfig->set( 'upload_folder', 'uploads' . DIRECTORY_SEPARATOR );

// Create SecureUPload and give previously created config to it
$SecureUPload = new SecureUPload( $SecureUPloadConfig );

// Upload a file
$Upload = $SecureUPload->uploadFile( 'file' );

// Check uploaded file
if ( $Upload->status ) {
// File has been set in <input type="file" name="file"/>
 if ( $Upload->status == 1 ) {
  echo 'File uploaded successfully. Id: ' . $Upload->id;
  // Save $Upload->id for future uses.
 } else {
  echo 'File didn\'t uploaded. Error code: ' . $Upload->error;
  // Show error
 }
} else {
 // No file is selected in input field
}
```

## Documentation
- See [Documentation](http://projects.allii.ir/project/secureupload/documentation/)

## Requirements
SecureUPload works with PHP 5.3.0 and above. HHVM is also tested and worked as well.

## Bugs & feature requests
For submitting bugs or feature requests, use [Github repository issues](https://github.com/alirdn/SecureUPload/issues).

## Todos
- Add virus scan services API
- Add save to database feature

## License
SecureUPload is licensed under MIT License. see the LICENSE file for details.

## Author
- Alireza Dabiri Nejad – Me@allii.ir

## Acknowledgements
- [OWASP Unrestricted File Upload](https://www.owasp.org/index.php/Unrestricted_File_Upload)


