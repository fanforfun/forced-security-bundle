<?php
namespace Fanforfun\ForcedSecurityBundle\EventListener\Security;

use Fanforfun\ForcedSecurityBundle\Model\User;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Router;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\HttpFoundation\Request;

class LoginSuccessHandler implements  AuthenticationSuccessHandlerInterface
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
     * @param  TokenInterface   $token
     * @return RedirectResponse $response
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token)
    {
        $date = new \DateTime('now');
        $user = $this->container->get('security.context')->getToken()->getUser();
        $hash = md5($_SERVER['HTTP_USER_AGENT'] . $date->format('dd-mm-YYYY H:i:s'));

        /**
         * @var User $user
         */
        $user->setLastLogin($date);
        $user->setLocked(true);
        $user->setSessionId($hash);

        $userManager = $this->container->get('fos_user.user_manager');
        $userManager->updateUser($user);

        //FIXME !!!
        $redirectUri = $this->container->get('router')->generate(User::$mainRoutes[$user->getMaxRole()]);
        $response = new RedirectResponse($redirectUri);
        $response->headers->setCookie(new Cookie('fingerPrint', $hash));

        return $response;
    }
}
