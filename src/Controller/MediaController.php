<?php

namespace App\Controller;


use App\Service\FileUploader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class MediaController extends AbstractController
{
    public const IMAGE_INPUT_NAME = "image_upload";

    /**
     * @Route("/image-upload", name="image_upload")
     */
    public function upload(Request $request, FileUploader $fileUploader)
    {
        $imageFile = $request->files->get(self::IMAGE_INPUT_NAME);

        if ($imageFile) {
            $imageUrl = "/img/tricks/" . $fileUploader->upload($imageFile);

            return new JsonResponse($imageUrl);
        }

        return new JsonResponse("Error with the form or with the uploader", 500);
    }

    /**
     * @Route("/image-delete", name="image_delete")
     */
    public function delete(Request $request)
    {
        $rootDirectory = dirname(dirname(__DIR__));
        $imageUrl = $request->request->get("imageUrl");

        if (unlink($rootDirectory . "/public" . $imageUrl)) {

            return new JsonResponse("Image $imageUrl deleted");

        } else {

            return new JsonResponse("Error when deleting image " . $imageUrl);
        }
    }
}
