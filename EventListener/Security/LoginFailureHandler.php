<?php
namespace Fanforfun\ForcedSecurityBundle\EventListener\Security;

use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\LockedException;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Routing\Router;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class LoginFailureHandler implements AuthenticationFailureHandlerInterface
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
     * @param  Request                 $request
     * @param  AuthenticationException $exception
     * @return Response                $response
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        $forcedIn = $request->request->get('_forcedIn');

        if ($forcedIn AND $exception instanceof LockedException) {
            $username = $request->request->get('_username');
            $password = $request->request->get('_password');

            $userManager = $this->container->get('fos_user.user_manager');
            /**
             * @var \Fanforfun\ForcedSecurityBundle\Entity\User $user
             */
            $user = $userManager->findUserByUsername($username);

            $encoderService = $this->container->get('security.encoder_factory');
            $encoder = $encoderService->getEncoder($user);
            $encoded_pass = $encoder->encodePassword($password, $user->getSalt());

            if ($encoded_pass == $user->getPassword()) {

                $providerKey = $this->container->getParameter('fos_user.firewall_name');
                $token = new UsernamePasswordToken($user, null, $providerKey, $user->getRoles());
                $this->container->get('security.context')->setToken($token);

                $loginSuccessHandler = new LoginSuccessHandler($this->container);

                return $loginSuccessHandler->onAuthenticationSuccess($request, $token);
            }
        }

        $request = $this->container->get('request');
        /* @var \Symfony\Component\HttpFoundation\Request $request */

        /* @var \Symfony\Component\HttpFoundation\Session $session */
        $session = $request->getSession();

        $lastUsername = (null === $session) ? '' : $session->get(SecurityContext::LAST_USERNAME);

        $csrfToken = $this->container->get('form.csrf_provider')->generateCsrfToken('authenticate');

        return $this->container->get('templating')->renderResponse('FOSUserBundle:Security:login.html.'.$this->container->getParameter('fos_user.template.engine'), array(
            'last_username' => $lastUsername,
            'error'         => true,
            'error_message' => $exception instanceof LockedException ? 'Пользователь уже в сети' : 'Неверная пара логин-пароль',
            'csrf_token'    => $csrfToken,
        ));
    }
}
