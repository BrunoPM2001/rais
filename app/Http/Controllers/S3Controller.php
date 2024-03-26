<?php

namespace App\Http\Controllers;

use Aws\S3\S3Client;

class S3Controller extends Controller {

  protected $s3Client;

  public function __construct() {
    $this->initializeS3Client();
  }

  protected function initializeS3Client() {
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
  }
}
