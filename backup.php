<?php

// Included aws/aws-sdk-php via Composer's autoloader
require 'vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$client = new Aws\S3\S3Client([
        'version' => 'latest',
        'region'  => $_ENV["BUCKET_REGION"],
        'endpoint' => 'https://'.$_ENV["BUCKET_REGION"].'.digitaloceanspaces.com',
        'use_path_style_endpoint' => false, // Configures to use subdomain/virtual calling format.
        'credentials' => [
                'key'    => $_ENV['SPACES_KEY'],
                'secret' => $_ENV['SPACES_SECRET'],
            ],
]);

$db_conn = mysqli_connect($_ENV["DB_SERVER"],$_ENV["DB_USER"],$_ENV["DB_PASS"]);
$notdbs=explode(',',$_ENV["NOT_BK_DBS"]);

$dbs = mysqli_query($db_conn,"SHOW DATABASES"); 
while ($row = mysqli_fetch_array($dbs)) { 
    if(!in_array($row[0],$notdbs)){
        $dbname=$row[0];

        $filename=time().'_'.$dbname.'_backup.sql.zip';

        $result=exec("mysqldump ".$dbname." --password=".$_ENV["DB_PASS"]." --user=".$_ENV["DB_USER"]." --no-tablespaces --single-transaction | gzip >".dirname(__FILE__)."/".$filename,$output);
        if(empty($output)){            
            $client->putObject(array(
                'Bucket'     => $_ENV['BUCKET_NAME'],
                'SourceFile' => $filename,
                'Key'        => $_ENV['STORE_PATH'].'/'.date("Y-m-d").'/' . basename($filename)
            ));
            unlink($filename);
        }else {
            /* we have something to log the output here*/
        }
    }                
}