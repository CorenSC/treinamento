<?php

// Configurações
$repoUrl = 'https://github.com/CorenSC/treinamento.git';
$branch = 'master';
$deployDir = '/var/www/aprendeAi';
$logFile = '/var/log/deploy.log';
$remoteUser = 'root';
$remoteHost = '172.17.60.63';

$currentUser = get_current_user();

function logMessage($message, $status = 'info')
{
    global $logFile;
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[$timestamp] $message\n";

    // Grava no arquivo de log
    file_put_contents($logFile, $logEntry, FILE_APPEND);

    // Define cores de fundo para o terminal
    switch ($status) {
        case 'success':
            $color = "\033[42m"; // Fundo verde
            break;
        case 'error':
            $color = "\033[41m"; // Fundo vermelho
            break;
        case 'info':
        default:
            $color = "\033[44m"; // Fundo azul
            break;
    }

    // Limpa cor após a mensagem
    $resetColor = "\033[0m";

    // Exibe no terminal com a cor apropriada
    echo $color . $logEntry . $resetColor;
}

function runRemoteCommand($command)
{
    global $remoteUser, $remoteHost;
    $sshCommand = "ssh {$remoteUser}@{$remoteHost} '{$command}'";
    exec($sshCommand, $output, $return_var);
    return [$output, $return_var];
}

// Verificar se o diretório existe e atualizar ou clonar o repositório
list($output, $return_var) = runRemoteCommand("[ -d {$deployDir} ] && echo 'exists' || echo 'not exists'");

if (trim(implode("\n", $output)) === 'exists') {
    logMessage("Atualizando repositório...", 'info');
    list($output, $return_var) = runRemoteCommand("cd {$deployDir} && git fetch origin && git reset --hard origin/{$branch}");
} else {
    logMessage("Diretório não existe. Criando o diretório...", 'info');
    list($output, $return_var) = runRemoteCommand("mkdir -p {$deployDir}");
    logMessage("Clonando repositório...", 'info');
    list($output, $return_var) = runRemoteCommand("git clone {$repoUrl} {$deployDir}");
}

if ($return_var !== 0) {
    logMessage("Erro ao atualizar/clonar o repositório: " . implode("\n", $output), 'error');
    exit(1);
}

logMessage("Repositório atualizado com sucesso.", 'success');

// Definir APP_ENV=prod no arquivo .env
logMessage("Definindo APP_ENV=prod no arquivo .env...", 'info');
list($output, $return_var) = runRemoteCommand("echo 'APP_ENV=prod' > {$deployDir}/.env");

if ($return_var !== 0) {
    logMessage("Erro ao definir APP_ENV=prod no arquivo .env: " . implode("\n", $output), 'error');
    exit(1);
}

logMessage("APP_ENV=prod definido no arquivo .env com sucesso.", 'success');

// Instalar dependências do Composer
logMessage("Instalando dependências do Composer...", 'info');
list($output, $return_var) = runRemoteCommand("cd {$deployDir} && COMPOSER_ALLOW_SUPERUSER=1 composer install --no-interaction --no-dev");

if ($return_var !== 0) {
    logMessage("Erro ao instalar dependências do Composer: " . implode("\n", $output), 'error');
    exit(1);
}

logMessage("Dependências do Composer instaladas com sucesso.", 'success');

// Otimizar o autoloader do Composer
logMessage("Otimizando o autoloader do Composer...", 'info');
list($output, $return_var) = runRemoteCommand("cd {$deployDir} && composer dump-autoload --optimize --no-dev");

if ($return_var !== 0) {
    logMessage("Erro ao otimizar o autoloader do Composer: " . implode("\n", $output), 'error');
    exit(1);
}

logMessage("Autoloader do Composer otimizado com sucesso.", 'success');

// Ajustar permissões de arquivos
logMessage("Ajustando permissões de arquivos...", 'info');
list($output, $return_var) = runRemoteCommand("chown -R www-data:www-data {$deployDir}/var && chmod -R 775 {$deployDir}/var");

if ($return_var !== 0) {
    logMessage("Erro ao ajustar permissões: " . implode("\n", $output), 'error');
    exit(1);
}

logMessage("Permissões ajustadas com sucesso.", 'success');

// Ajustar permissões de diretórios públicos
logMessage("Ajustando permissões de diretórios públicos...", 'info');
list($output, $return_var) = runRemoteCommand("chown -R www-data:www-data {$deployDir}/public && chmod -R 775 {$deployDir}/public");

if ($return_var !== 0) {
    logMessage("Erro ao ajustar permissões de diretórios públicos: " . implode("\n", $output), 'error');
    exit(1);
}

logMessage("Permissões de diretórios públicos ajustadas com sucesso.", 'success');

// Instalar dependências do npm
logMessage("Instalando dependências do npm...", 'info');
list($output, $return_var) = runRemoteCommand("cd {$deployDir} && npm install --production");

if ($return_var !== 0) {
    logMessage("Erro ao instalar dependências do npm: " . implode("\n", $output), 'error');
    exit(1);
}

logMessage("Dependências do npm instaladas com sucesso.", 'success');

// Rodar o build com npm
logMessage("Rodando o build com npm...", 'info');
list($output, $return_var) = runRemoteCommand("cd {$deployDir} && npm run build");

if ($return_var !== 0) {
    logMessage("Erro ao rodar o build com npm: " . implode("\n", $output), 'error');
    exit(1);
}

logMessage("Build com npm concluído com sucesso.", 'success');

// Remover arquivos desnecessários
logMessage("Removendo arquivos desnecessários...", 'info');
list($output, $return_var) = runRemoteCommand("cd {$deployDir} && rm -rf node_modules .git tests");

if ($return_var !== 0) {
    logMessage("Erro ao remover arquivos desnecessários: " . implode("\n", $output), 'error');
    exit(1);
}

logMessage("Arquivos desnecessários removidos com sucesso.", 'success');

// Limpar o cache do Symfony
logMessage("Limpando o cache do Symfony...", 'info');
list($output, $return_var) = runRemoteCommand("cd {$deployDir} && php bin/console cache:clear --env=prod");

if ($return_var !== 0) {
    logMessage("Erro ao limpar o cache do Symfony: " . implode("\n", $output), 'error');
    exit(1);
}

logMessage("Cache do Symfony limpo com sucesso.", 'success');

// Executar warmup do cache
logMessage("Executando warmup do cache...", 'info');
list($output, $return_var) = runRemoteCommand("cd {$deployDir} && php bin/console cache:warmup --env=prod");

if ($return_var !== 0) {
    logMessage("Erro ao executar warmup do cache: " . implode("\n", $output), 'error');
    exit(1);
}

logMessage("Warmup do cache concluído com sucesso.", 'success');

// Verificar diferenças nas migrações
logMessage("Verificando diferenças nas migrações...", 'info');
list($output, $return_var) = runRemoteCommand("cd {$deployDir} && php bin/console doctrine:migrations:diff --no-interaction --env=prod");

if ($return_var !== 0) {
    logMessage("Não há diferença entre o projeto e o banco de dados: " . implode("\n", $output), 'error');
} else {
    list($migrationFiles, $return_var) = runRemoteCommand("ls {$deployDir}/migrations/*.php | wc -l");

    if (trim(implode("\n", $migrationFiles)) > 0) {
        logMessage("Diferenças encontradas. Rodando migrações de banco de dados...", 'info');
        list($output, $return_var) = runRemoteCommand("cd {$deployDir} && php bin/console doctrine:migrations:migrate --no-interaction --env=prod");

        if ($return_var !== 0) {
            logMessage("Erro ao rodar migrações: " . implode("\n", $output), 'error');
        } else {
            logMessage("Migrações de banco de dados concluídas com sucesso.", 'success');
        }
    } else {
        logMessage("Nenhuma diferença encontrada. Pulando migrações.", 'info');
    }
}

// Reiniciar o Nginx (opcional, se necessário)
logMessage("Reiniciando o Nginx...", 'info');
list($output, $return_var) = runRemoteCommand("systemctl restart nginx");

if ($return_var !== 0) {
    logMessage("Erro ao reiniciar o Nginx: " . implode("\n", $output), 'error');
    exit(1);
}

logMessage("Nginx reiniciado com sucesso.", 'success');

logMessage("Deploy concluído com sucesso.", 'success');