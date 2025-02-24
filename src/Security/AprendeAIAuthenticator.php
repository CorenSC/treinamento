<?php

namespace App\Security;

use App\Util\LdapService;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Ldap\Ldap;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\SecurityRequestAttributes;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class AprendeAIAuthenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait;

    public const LOGIN_ROUTE = 'app_login';

    public function __construct(private UrlGeneratorInterface $urlGenerator, private readonly LdapService $ldapService)
    {
    }

    public function authenticate(Request $request): Passport
    {
        $username = $request->getPayload()->getString('username');
        dump($username);
        $password = $request->getPayload()->getString('password');

        $ldapUsers = $this->ldapService->obterTodosUsersLdap();
        $userFound = false;

        foreach ($ldapUsers as $ldapUser) {
            if($ldapUser['sAMAccountName'] === $username) {
                $cn = $ldapUser['cn'];
                dump($cn);
                $userFound = true;
                break;
            }
        }

        if(!$userFound) {
            throw new \Exception("Usuário não encontrado");
        }

        try {
            $dn = "CN={$cn},OU=Florianópolis - Sede,OU=Usuários,OU=COREN-SC,DC=coren,DC=local";
            dump($dn);
            $ldap = Ldap::create('ext_ldap', ['host' => '101.44.196.135']);
            dump($ldap);
            $ldap->bind($dn, $password);
        } catch (\Exception $e) {
            dump('Erro na autenticação LDAP: ' . $e->getMessage());

            throw new \Exception('Credenciais inválidas');
        }

        $request->getSession()->set(SecurityRequestAttributes::LAST_USERNAME, $username);

        return new Passport(
            new UserBadge($username),
            new PasswordCredentials($password),
            [
                new CsrfTokenBadge('authenticate', $request->getPayload()->getString('_csrf_token')),
            ]
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        if ($targetPath = $this->getTargetPath($request->getSession(), $firewallName)) {
            return new RedirectResponse($targetPath);
        }

        return new RedirectResponse($this->urlGenerator->generate('home'));
    }

    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }
}
