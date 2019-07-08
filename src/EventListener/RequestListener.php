<?php

namespace App\EventListener;

use App\Controller\MediaController;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class RequestListener
{
    use TargetPathTrait;

    public function onKernelRequest(GetResponseEvent $event)
    {
        // To get back to the last visited page after login
        $this->saveCurrentRouteInSession($event);

        // To delete unused uploaded images when leaving the trick editor without saving the trick
        $this->deleteUnusedUploadedImages($event);
        $this->setLastRoute($event);
    }

    // Private

    /**
     * Save the current route in the session to retrieve it after a successful login
     *
     * @param GetResponseEvent $event
     */
    private function saveCurrentRouteInSession(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        $route = $request->attributes->get('_route');

        if (
            $route !== "app_login" &&
            $route !== "home_ajax_get_page" &&
            $route !== "registration_route" &&
            $route[0] !== '_'
        ) {
            $uri = $request->getRequestUri();
            $this->saveTargetPath($request->getSession(), "main", $uri);
        }
    }

    /**
     * Save the last used route
     *
     * @param GetResponseEvent $event
     */
    private function setLastRoute(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        $route = $request->attributes->get('_route');
        $session = $request->getSession();

        if (
            $route[0] !== '_' &&
            $route !== "image_upload" &&
            $route !== "image_delete"
        ) {
            $session->set("last_route", $route);
        }
    }

    /**
     * Delete the unused uploaded images when the user leaves the trick editor without saving
     */
    private function deleteUnusedUploadedImages(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        $route = $request->attributes->get('_route');
        $session = $event->getRequest()->getSession();
        $lastRoute = $session->get("last_route");
        $uploadedImages = $session->get(MediaController::KEY_UPLOADED_IMAGES);

        if (
            $lastRoute &&
            $uploadedImages &&
            ($lastRoute === "edit_trick" || $lastRoute === "add_trick") &&
            $route !== "image_upload" &&
            $route !== "image_delete" &&
            $route !== "add_trick" &&
            $route !== "edit_trick" &&
            $route[0] !== '_'
        ) {
            $rootDirectory = dirname(dirname(__DIR__));

            foreach ($uploadedImages as $uploadedImage) {
                if (file_exists($rootDirectory . "/public" . $uploadedImage)) {
                    unlink($rootDirectory . "/public" . $uploadedImage);
                }
            }

            $session->set(MediaController::KEY_UPLOADED_IMAGES, null);
        }
    }
}
