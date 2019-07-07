<?php

namespace App\Controller;


use App\Service\FileUploader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class MediaController extends AbstractController
{
    public const IMAGE_INPUT_NAME = "image_upload";
    public const KEY_UPLOADED_IMAGES = "uploaded_images";

    /**
     * @Route("/image-upload", name="image_upload")
     */
    public function upload(Request $request, FileUploader $fileUploader, SessionInterface $session)
    {
        if ($this->getUser() === null) {
            throw new AccessDeniedException("User must be connected to upload an image");
        }

        $imageFile = $request->files->get(self::IMAGE_INPUT_NAME);

        if ($imageFile) {
            $imageUrl = "/img/tricks/" . $fileUploader->upload($imageFile);
            $this->addImageUrlToSession($imageUrl, $session);

            return new JsonResponse($imageUrl);
        }

        return new JsonResponse("Error with the form or with the uploader", 500);
    }

    /**
     * @Route("/image-delete", name="image_delete")
     */
    public function delete(Request $request)
    {
        if ($this->getUser() === null) {
            throw new AccessDeniedException("User must be connected to delete an image");
        }

        $rootDirectory = dirname(dirname(__DIR__));
        $imageUrl = $request->request->get("imageUrl");

        if (unlink($rootDirectory . "/public" . $imageUrl)) {

            return new JsonResponse("Image $imageUrl deleted");

        } else {

            return new JsonResponse("Error when deleting image " . $imageUrl);
        }
    }

    private function addImageUrlToSession(string $imageUrl, SessionInterface $session)
    {
        $uploadedFiles = $session->get('uploaded_images');

        if ($uploadedFiles === null) {
            $session->set(self::KEY_UPLOADED_IMAGES, [$imageUrl]);

        } else {
            $uploadedFiles[] = $imageUrl;
            $session->set(self::KEY_UPLOADED_IMAGES, $uploadedFiles);
        }
    }
}
