<?php

namespace App\Security;

use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\PassportInterface;

class LoginFormAuthenticator extends AbstractAuthenticator
{
    private $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * supports.
     *
     * @param mixed $request
     *
     * @return bool
     */
    public function supports(Request $request): ?bool
    {
        return 'app_login' == $request->attributes->get('_route')
            && $request->isMethod('POST');
    }

    /**
     * authenticate.
     *
     * @param mixed $request
     */
    public function authenticate(Request $request): PassportInterface
    {
        $user = $this->userRepository->findOneByUsername($request->request->get('username'));

        // Save de l'username si erreur
        $request->getSession()->set(
            'app_login_form_old_username',
            $request->request->get('username')
        );

        if (!$user) {
            throw new CustomUserMessageAuthenticationException('Username non valide');
        }

        return new Passport(
            new UserBadge($request->request->get('username')),
            new PasswordCredentials($request->request->get('password')), [
                new CsrfTokenBadge('login_form', $request->request->get('csrf_token')),
            ]);
    }

    /**
     * onAuthenticationSuccess.
     *
     * @param mixed $request
     * @param mixed $token
     * @param mixed $firewallName
     *
     * @return Response
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return new RedirectResponse('vacances/calendrier');
    }

    /**
     * onAuthenticationFailure.
     *
     * @param mixed $request
     * @param mixed $exception
     *
     * @return Response
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        return new RedirectResponse('/');
    }
}
