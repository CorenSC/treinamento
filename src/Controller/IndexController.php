<?php

namespace App\Controller;

use App\Util\LdapService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;

class IndexController extends AbstractController
{

    private LdapService $ldapService;

    /**
     * @param LdapService $ldapService
     */
    public function __construct(LdapService $ldapService)
    {
        $this->ldapService = $ldapService;
    }

    #[Route('/', name: 'index')]
    public function home()
    {
        return $this->render('inicial/inicial.html.twig');
    }

    #[Route('ldap', name: 'ldap')]
    public function ldap() {
        $ldap = $this->ldapService->obterTodosUsersLdap();

        return $this->render('inicial/inicial.html.twig', [
            'ldap' => $ldap
        ]);
    }
}