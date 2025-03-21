<?php

namespace App\Security;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Util\LdapService;
use Doctrine\DBAL\Exception\ConnectionException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Ldap\Ldap;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\CustomCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\SecurityRequestAttributes;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use Symfony\Component\Uid\Uuid;

class AprendeAIAuthenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait;

    public const LOGIN_ROUTE = 'app_login';

    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private readonly LdapService $ldapService,
        private UserRepository $userRepository,
        private EntityManagerInterface $entityManager
    ) {
    }

    public function authenticate(Request $request): Passport
    {
        $username = $request->getPayload()->getString('username');
        $password = $request->getPayload()->getString('password');

        // Verifica as credenciais no LDAP
        $ldapUsers = $this->ldapService->obterTodosUsersLdap();
        $userFound = false;

        foreach ($ldapUsers as $ldapUser) {
            if ($ldapUser['sAMAccountName'] === $username) {
                $cn = $ldapUser['cn'];
                $description = $ldapUser['description'];
                $userFound = true;
                break;
            }
        }

        if (!$userFound) {
            $request->getSession()->set('login_error', 'Usuário não encontrado');

            // Retornar o Passport, pois é o que o Symfony espera
            return new Passport(
                new UserBadge($username),
                new CustomCredentials(function () {
                    return false; // Credential inválida para manter o fluxo
                }, $password)
            );
        }

        try {
            $dn = "CN={$cn},OU=Florianópolis - Sede,OU=Usuários,OU=COREN-SC,DC=coren,DC=local";
            $ldap = Ldap::create('ext_ldap', ['host' => '192.168.1.17']);
            $ldap->bind($dn, $password);
        } catch (\Exception $e) {
            $request->getSession()->set('login_error', 'Credenciais inválidas');

            // Retornar o Passport, pois é o que o Symfony espera
            return new Passport(
                new UserBadge($username),
                new CustomCredentials(function () {
                    return false; // Credential inválida para manter o fluxo
                }, $password)
            );
        }

        // Tenta acessar o banco de dados
        $databaseAvailable = true;
        try {
            $this->entityManager->getConnection()->connect();
        } catch (ConnectionException $e) {
            $databaseAvailable = false;
        }

        if ($databaseAvailable) {
            // Banco de dados disponível: cria ou atualiza o usuário no banco
            $user = $this->userRepository->findOneBy(['username' => $username]);

            if (!$user) {
                $user = new User();
                $user->setName($cn);
                $user->setActive(true);
            }

            $user->setUsername($username);
            $user->setDepartament($description);

            $this->entityManager->persist($user);
            $this->entityManager->flush();
        } else {
            // Banco de dados indisponível: cria uma sessão temporária
            $user = [
                'username' => $username,
                'name' => $cn,
                'departament' => $description,
                'is_temporary' => true, // Marca a sessão como temporária
            ];

            $request->getSession()->set('temporary_user', $user);
        }

        $token = Uuid::v4()->toRfc4122();
        $request->getSession()->set('session_token', $token);
        $request->getSession()->set(SecurityRequestAttributes::LAST_USERNAME, $username);

        $passport = new Passport(
            new UserBadge($username),
            new CustomCredentials(function () {
                return true;
            }, $password),
            [
                new CsrfTokenBadge('authenticate', $request->getPayload()->getString('_csrf_token')),
            ]
        );

        return $passport;
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