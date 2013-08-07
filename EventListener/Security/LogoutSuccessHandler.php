<?php
namespace Fanforfun\ForcedSecurityBundle\EventListener\Security;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Http\Logout\LogoutSuccessHandlerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Router;
use Symfony\Component\HttpFoundation\Request;

class LogoutSuccessHandler implements LogoutSuccessHandlerInterface
{
    protected $router;
    protected $security;
    protected $container;
    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->router = $container->get('router');
        $this->security = $container->get('security.context');

        $this->container = $container;
    }

    /**
     * @param  Request          $request
     * @return RedirectResponse $response
     */
    public function onLogoutSuccess(Request $request)
    {
        $token = $this->security->getToken();

        $user = isset($token) ? $token->getUser() : null;

        $response = new RedirectResponse($this->router->generate('fos_user_security_login'));

        if (isset($user)) {
            $user->setLocked(false);
            $user->setSessionId(null);
            $userManager = $this->container->get('fos_user.user_manager');
            $userManager->updateUser($user);
        }

        return $response;
    }
}
