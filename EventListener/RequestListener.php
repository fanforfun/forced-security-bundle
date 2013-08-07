<?php

namespace Fanforfun\ForcedSecurityBundle\EventListener;

use Fanforfun\ForcedSecurityBundle\Entity\User;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\RedirectResponse;

class RequestListener
{
    protected $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function onLoginPage(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        $refererUri = $request->server->get('HTTP_REFERER');
        if ($refererUri AND $refererUri != $request->getUri() AND !preg_match('~login~', $refererUri)) {
            $redirectUri = $refererUri;
        } else {
            $context = $this->container->get('security.context');
            $redirectRoute = ($context->getToken() AND is_object($user = $context->getToken()->getUser()))
                ? User::$mainRoutes[$user->getMaxRole()]
                : 'fos_user_security_login';
            $redirectUri = $this->container->get('router')->generate($redirectRoute);
        }

        $event->setResponse(new RedirectResponse($redirectUri, $status = 301));
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        $context = $this->container->get('security.context');

        //check hash in user
        if ($context->getToken() && is_object($context->getToken()->getUser()) && $request->cookies->get('fingerPrint')) {
            if ($context->getToken()->getUser()->getSessionId() != $request->cookies->get('fingerPrint')) {
                $this->container->get('security.context')->getToken()->setAuthenticated(false);
                $this->container->get('request')->getSession()->invalidate();
                $this->container->get('session')->clear();

                if ($this->container->get('request')->get('_route') != 'fos_user_security_login') {
                    $event->setResponse(new RedirectResponse($this->container->get('router')->generate('fos_user_security_login')));
                }

                return;
            }
        }

        if (
            $this->container->get('request')->get('_route') == 'fos_user_security_login'
            AND
            $context->isGranted('IS_AUTHENTICATED_FULLY')
        )
        {
            $this->onLoginPage($event);
        }

    }
}
