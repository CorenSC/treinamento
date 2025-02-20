<?php

use Symfony\Component\Ldap\Ldap;

require_once __DIR__.'/../vendor/autoload.php';

$dn = 'CN=AprendeAi AUTH,OU=Serviços,OU=Florianópolis - Sede,OU=Usuários,OU=COREN-SC,DC=coren,DC=local';
$password = '14l5Y_9m)_7Z';

$ldap = Ldap::create('ext_ldap', [
    'host' => '101.44.196.135',
]);

$ldap->bind($dn, $password);

$query = $ldap->query('DC=coren,DC=local', '(&(objectCategory=person)(objectClass=user)(!(userAccountControl:1.2.840.113556.1.4.803:=2))(!(userAccountControl:1.2.840.113556.1.4.803:=16))(!(userAccountControl:1.2.840.113556.1.4.803:=8388608))(!(userAccountControl:1.2.840.113556.1.4.803:=514))(!(userAccountControl:1.2.840.113556.1.4.803:=546))(!(userAccountControl:1.2.840.113556.1.4.803:=66050))(!(userAccountControl:1.2.840.113556.1.4.803:=66082))(!(sAMAccountName=*Sophos*))(!(sAMAccountName=*impr*))(!(sAMAccountName=*trein*))(!(sAMAccountName=*temporario*))(!(sAMAccountName=krbtgt))(!(sAMAccountName=globaltti))(!(sAMAccountName=*telecom*))(!(sAMAccountName=sum))(!(sAMAccountName=*sysprep*))(!(sAMAccountName=*tarefa*))(!(sAMAccountName=*appl*))(!(sAMAccountName=Elifelren))(!(sAMAccountName=Convidado))(!(sAMAccountName=*usr*))(!(sAMAccountName=*cofen*))(!(sAMAccountName=*script*))(!(sAMAccountName=*wg))(!(sAMAccountName=*teste*))(!(sAMAccountName=*0*))(!(sAMAccountName=*1*))(!(sAMAccountName=*srv*))(!(sAMAccountName=*adm*))(!(sAMAccountName=*.sup))(!(sAMAccountName=*coren*))(!(sAMAccountName=*monitor*))(!(sAMAccountName=*aprendeai*))(!(sAMAccountName=*seprol*))(!(sAMAccountName=*veeam*))(!(sAMAccountName=*sigma*))(!(sAMAccountName=*plenaria*))(!(sAMAccountName=*scanner*))(!(sAMAccountName=*alex.barbieri*))(!(sAMAccountName=*rafael.conceicao*))(!(sAMAccountName=*itscon*)))');

$results = $query->execute();

foreach ($results as $entry) {
    $samAccountName = $entry->getAttribute('sAMAccountName')[0] ?? 'N/A';
    $cn = $entry->getAttribute('cn')[0] ?? 'N/A';
    $description = $entry->getAttribute('description')[0] ?? 'N/A';

    // Exibe os atributos
    echo "sAMAccountName: " . $samAccountName . "\n";
    echo "cn: " . $cn . "\n";
    echo "description: " . $description . "\n";
    echo "-------------------------\n";
}