<?php

// Configurações
$repoUrl = 'https://github.com/CorenSC/treinamento.git';
$branch = 'master';
$deployDir = '/var/www/aprendeAi';
$logFile = '/var/log/deploy.log';

// Função para logar mensagens
function logMessage($message)
{
    global $logFile;
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND);
}

// Definir o APP_ENV como 'prod'
putenv('APP_ENV=prod');
logMessage("APP_ENV definido como 'prod'.");

// Verificar se o diretório de deploy já existe
if (is_dir($deployDir)) {
    logMessage("Atualizando repositório...");
    chdir($deployDir);
    exec("git fetch origin 2>&1", $output, $return_var);
    exec("git reset --hard origin/$branch 2>&1", $output, $return_var);
} else {
    // Se não existir, clonar o repositório
    logMessage("Clonando repositório...");
    exec("git clone $repoUrl $deployDir 2>&1", $output, $return_var);
}

// Verificar se o comando foi executado com sucesso
if ($return_var !== 0) {
    logMessage("Erro ao atualizar/clonar o repositório: " . implode("\n", $output));
    exit(1);
}

logMessage("Repositório atualizado com sucesso.");

logMessage("Ajustando permissões de arquivos...");
exec("chown -R www-data:www-data $deployDir/var 2>&1", $output, $return_var);
exec("chmod -R 775 $deployDir/var 2>&1", $output, $return_var);

if ($return_var !== 0) {
    logMessage("Erro ao ajustar permissões: " . implode("\n", $output));
    exit(1);
}

logMessage("Permissões ajustadas com sucesso.");

// Instalar dependências do Composer
logMessage("Instalando dependências do Composer...");
chdir($deployDir);
exec("composer install --no-dev --optimize-autoloader 2>&1", $output, $return_var);

if ($return_var !== 0) {
    logMessage("Erro ao instalar dependências do Composer: " . implode("\n", $output));
    exit(1);
}

logMessage("Dependências do Composer instaladas com sucesso.");

// Instalar dependências do npm
logMessage("Instalando dependências do npm...");
exec("npm install 2>&1", $output, $return_var);

if ($return_var !== 0) {
    logMessage("Erro ao instalar dependências do npm: " . implode("\n", $output));
    exit(1);
}

logMessage("Dependências do npm instaladas com sucesso.");

// Rodar o build com npm
logMessage("Rodando o build com npm...");
exec("npm run build 2>&1", $output, $return_var);

if ($return_var !== 0) {
    logMessage("Erro ao rodar o build com npm: " . implode("\n", $output));
    exit(1);
}

logMessage("Build com npm concluído com sucesso.");

// Limpar o cache do Symfony
logMessage("Limpando o cache do Symfony...");
exec("php bin/console cache:clear --env=prod 2>&1", $output, $return_var);

if ($return_var !== 0) {
    logMessage("Erro ao limpar o cache do Symfony: " . implode("\n", $output));
    exit(1);
}

logMessage("Cache do Symfony limpo com sucesso.");

// Rodar migrações de banco de dados (se necessário)
logMessage("Rodando migrações de banco de dados...");
exec("php bin/console doctrine:migrations:migrate --no-interaction --env=prod 2>&1", $output, $return_var);

if ($return_var !== 0) {
    logMessage("Erro ao rodar migrações: " . implode("\n", $output));
    exit(1);
}

logMessage("Migrações de banco de dados concluídas com sucesso.");

// Reiniciar o Nginx (opcional, se necessário)
logMessage("Reiniciando o Nginx...");
exec("sudo systemctl restart nginx 2>&1", $output, $return_var);

if ($return_var !== 0) {
    logMessage("Erro ao reiniciar o Nginx: " . implode("\n", $output));
    exit(1);
}

logMessage("Nginx reiniciado com sucesso.");

logMessage("Deploy concluído com sucesso.");