<?php

namespace App\Http\Controllers;

use Aws\S3\S3Client;
use Exception;

class S3Controller extends Controller {

  protected $s3Client;
  protected $s3ClientPut;

  public function __construct() {
    $this->initializeS3Clients();
  }

  protected function initializeS3Clients() {
    $this->s3Client = new S3Client([
      'version' => 'latest',
      'region' => env('AWS_DEFAULT_REGION'),
      'endpoint' => env('AWS_ENDPOINT'),
      'use_path_style_endpoint' => true,
      'credentials' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY')
      ],
    ]);
    $this->s3ClientPut = new S3Client([
      'version' => 'latest',
      'region' => env('AWS_DEFAULT_REGION'),
      'endpoint' => env('AWS_ENDPOINT_PUT'),
      'use_path_style_endpoint' => true,
      'credentials' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY')
      ],
    ]);
  }

  public function uploadFile($file, $bucket, $dir) {
    try {
      $this->s3ClientPut->putObject([
        'Bucket' => $bucket,
        'Key'    => $dir,
        'Body'   => fopen($file, 'r'),
      ]);

      return true;
    } catch (Exception $e) {
      return response()->json([
        'message' => 'Error al subir el archivo',
        'error' => $e->getMessage(),
      ], 500);
    }
  }
}
