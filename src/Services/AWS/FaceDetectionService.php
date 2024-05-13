<?php

namespace Stanliwise\CompreParkway\Services\AWS;

use GuzzleHttp\Utils;
use Illuminate\Http\File;
use Stanliwise\CompreParkway\Contract\FaceTech\FaceDetectionService as FaceTechFaceDetectionService;
use Stanliwise\CompreParkway\Exceptions\NoFaceWasDetected;

class FaceDetectionService extends BaseService implements FaceTechFaceDetectionService
{
    protected function handleHttpResponse(\Aws\Result $response)
    {
        $toArray = $response->toArray();

        $faceDetails = data_get($toArray, 'FaceDetails');
        $confidence = data_get($toArray, 'FaceDetails.0.Confidence');

        if (!$faceDetails)
            throw new NoFaceWasDetected;

        if ($confidence <  (config('compreFace.trust_threshold') * 100))
            throw new NoFaceWasDetected;

        return $toArray;
    }

    public function detectFileImage(File $file)
    {
        $response = $this->getHttpClient()->detectFaces([
            "Image" => [
                'Bytes' => $file->getContent(),
            ]
        ]);

        return $this->handleHttpResponse($response);
    }

    public function detectBase64Image(string $file)
    {
    }
}