<?php

namespace App\EventListener;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class RequestListener
{
    use TargetPathTrait;

    public function onKernelRequest(GetResponseEvent $event)
    {
        $this->saveCurrentRouteInSession($event);
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

        if ($route !== "app_login" &&
            $route !== "home_ajax_get_page" &&
            $route !== "registration_route" &&
            $route[0] !== '_'
        ) {
            $uri = $request->getRequestUri();
            $this->saveTargetPath($request->getSession(), "main", $uri);
        }
    }
}
