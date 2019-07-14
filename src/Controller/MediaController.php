<?php

namespace App\Controller;


use App\Repository\ImageRepository;
use App\Repository\TrickRepository;
use App\Security\MediaVoter;
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
        $filePath = $rootDirectory . "/public" . $imageUrl;

        if (strpos($imageUrl, "/img") !== 0 || !file_exists($filePath)) {
            return new JsonResponse("The file $filePath should not be deleted or does not exist");
        } elseif (unlink($filePath)) {
            return new JsonResponse("Image $imageUrl deleted");
        }

        return new JsonResponse("Error when deleting image " . $imageUrl);
    }

    /**
     * @Route("/delete-unused-images", name="delete_unused_images")
     */
    public function deleteUnusedImages(ImageRepository $repository)
    {
        $this->denyAccessUnlessGranted(MediaVoter::DELETE_UNUSED_IMAGES);

        $imagesUrls = $repository->getUrls();
        $rootDirectory = dirname(dirname(__DIR__));
        $imagesDirectory = "$rootDirectory/public/img/tricks";
        $filesNames = array_diff(scandir($imagesDirectory, SCANDIR_SORT_DESCENDING), [".", ".."]);
        $deletedFiles = [];

        foreach ($filesNames as $fileName) {
            if (!in_array("/img/tricks/$fileName", $imagesUrls)) {
                $deletedFiles[] = $fileName;
                unlink("$imagesDirectory/$fileName");
            }
        }

        if (!empty($deletedFiles)) {
            $deletedFilesCount = count($deletedFiles);
            if ($deletedFilesCount > 1) {
                $this->addFlash("notice", "$deletedFilesCount fichiers ont été supprimés");
            } else {
                $this->addFlash("notice", "$deletedFilesCount fichier a été supprimé");
            }
        } else {
            $this->addFlash("notice", "Aucun fichier n'a été supprimé");
        }

        return $this->redirectToRoute("member_profile", [
            "id" => $this->getUser()->getId()
        ]);
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
