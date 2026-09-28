# Origem Brasil

E-commerce em PHP e MySQL para venda de produtos brasileiros, com catalogo, produtores, carrinho, checkout, pedidos, favoritos, perfil de usuario e painel administrativo.

## Documentacao

Este README e a fonte oficial para instalar, configurar e rodar o projeto.

Nao e necessario executar arquivos de migracao separados. O arquivo `OrigemBanco (1).sql` ja cria o banco `br` com a estrutura atual do sistema.

## Tecnologias

- PHP 8+
- MySQL ou MariaDB
- Apache pelo XAMPP/WAMP, ou servidor embutido do PHP
- Composer
- PHPMailer
- HTML, CSS e JavaScript

## Requisitos

Antes de rodar o projeto, instale:

- XAMPP para Windows: https://www.apachefriends.org/
- Git: https://git-scm.com/
- Composer: https://getcomposer.org/

No XAMPP, inicie estes servicos:

- Apache
- MySQL

## Como baixar o projeto

Abra o terminal na pasta `htdocs` do XAMPP:

```powershell
cd C:\xampp\htdocs
git clone URL_DO_REPOSITORIO Origem-Brasil
cd Origem-Brasil
```

Se voce ja recebeu a pasta pronta, apenas coloque a pasta em:

```text
C:\xampp\htdocs\Origem-Brasil
```

## Instalar dependencias

O projeto usa o PHPMailer via Composer. Na raiz do projeto, rode:

```powershell
composer install
```

Se o comando `composer` nao for reconhecido, instale o Composer ou use o instalador oficial.

## Criar o banco de dados

O arquivo SQL principal e:

```text
OrigemBanco (1).sql
```

Ele cria automaticamente o banco chamado:

```text
br
```

### Opcao 1: importar pelo phpMyAdmin

Use esta opcao se o Apache do XAMPP estiver abrindo normalmente no navegador.

1. Abra o painel do XAMPP.
2. Inicie `Apache` e `MySQL`.
3. Clique no botao `Admin` do servico Apache para abrir o endereco correto no navegador.
4. Acesse o phpMyAdmin pelo caminho `/phpmyadmin`.

Exemplos de endereco:

```text
http://localhost/phpmyadmin
http://localhost:8080/phpmyadmin
```

Se `localhost` nao abrir, use o endereco com a porta configurada no seu Apache. No XAMPP, essa porta aparece no painel ou no arquivo:

```text
C:\xampp\apache\conf\httpd.conf
```

Procure pela linha `Listen`. Exemplos:

```text
Listen 80
Listen 8080
```

Dentro do phpMyAdmin:

1. Clique em `Importar`.
2. Escolha o arquivo `OrigemBanco (1).sql`.
3. Clique em `Executar`.

Nao e necessario criar o banco manualmente antes da importacao. O proprio SQL cria o banco `br`.

### Opcao 2: importar pelo terminal

No PowerShell, dentro da pasta do projeto:

```powershell
Get-Content -LiteralPath "OrigemBanco (1).sql" | C:\xampp\mysql\bin\mysql.exe -uroot
```

Se o seu MySQL tiver senha, use:

```powershell
Get-Content -LiteralPath "OrigemBanco (1).sql" | C:\xampp\mysql\bin\mysql.exe -uroot -p
```

## Configurar conexao com o banco

A conexao fica em:

```text
conexao.php
```

Configuracao padrao:

```php
$host    = "localhost";
$usuario = "root";
$senha   = "";
$banco   = "br";
```

Se o seu MySQL usa senha, altere `$senha`.

## Como rodar o sistema

### Opcao 1: rodar pelo Apache do XAMPP

Com Apache e MySQL ligados no XAMPP, acesse:

```text
http://localhost/Origem-Brasil/
```

Painel administrativo:

```text
http://localhost/Origem-Brasil/adm.php
```

### Opcao 2: rodar pelo servidor embutido do PHP

Na raiz do projeto:

```powershell
C:\xampp\php\php.exe -S 127.0.0.1:8001 -t C:\xampp\htdocs\Origem-Brasil
```

Depois acesse:

```text
http://127.0.0.1:8001/
```

## Acesso administrativo

O SQL inclui um usuario administrador:

```text
E-mail: admin@origembrasil.com
```

Se a senha informada no material do projeto nao funcionar, redefina a senha do admin pelo terminal:

```powershell
$hash = C:\xampp\php\php.exe -r "echo password_hash('admin123', PASSWORD_DEFAULT);"
C:\xampp\mysql\bin\mysql.exe -uroot -e "USE br; UPDATE usuarios SET senha='$hash', tipo='admin' WHERE email='admin@origembrasil.com';"
```

Depois entre com:

```text
E-mail: admin@origembrasil.com
Senha: admin123
```

## Fluxo basico para testar

1. Acesse a loja.
2. Crie uma conta ou faca login.
3. Abra um produto do catalogo.
4. Adicione o produto ao carrinho.
5. Va para o checkout.
6. Preencha os dados obrigatorios.
7. Clique em finalizar compra.
8. Acesse `Meus pedidos` para ver o pedido criado.

## Estrutura principal

```text
Origem-Brasil/
  api/                 APIs de carrinho, checkout, favoritos e solicitacoes
  adm/                 Telas internas do painel administrativo
  css/                 Arquivos de estilo
  js/                  Scripts do carrinho e interacoes
  includes/            Header, footer, cards e componentes comuns
  imagens/             Imagens estaticas do site
  uploads/             Imagens enviadas pelo sistema
  recuperar-senha/     Fluxo de recuperacao de senha
  sql/                 Copia do script SQL
  vendor/              Dependencias do Composer
  conexao.php          Conexao com banco e inicializacao de sessao
  index.php            Pagina inicial
  produtos.php         Catalogo de produtos
  produto.php          Detalhe do produto
  produtores.php       Lista de produtores
  checkout.php         Finalizacao de compra
  pedidos.php          Pedidos do usuario
  perfil.php           Perfil do usuario
  adm.php              Entrada do painel admin
```

## Configuracao de e-mail

O envio de codigo de recuperacao usa PHPMailer.

Copie o arquivo de exemplo:

```text
recuperar-senha/config_email_example.php
```

Crie:

```text
recuperar-senha/config_email.php
```

Preencha os dados SMTP:

```php
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'seu_email@gmail.com');
define('SMTP_PASS', 'sua_senha_de_app');
```

Para Gmail, use uma senha de app, nao a senha normal da conta.

## Problemas comuns

### Pagina nao abre

Verifique se o Apache esta ligado no XAMPP e se a pasta esta em:

```text
C:\xampp\htdocs\Origem-Brasil
```

### Erro de conexao com banco

Confira se o MySQL esta ligado e se o banco `br` foi importado.

Tambem confira os dados em `conexao.php`.

### `php`, `mysql` ou `composer` nao sao reconhecidos

Use os caminhos completos:

```powershell
C:\xampp\php\php.exe -v
C:\xampp\mysql\bin\mysql.exe --version
```

### Erro de sessao ou permissao

O projeto salva sessoes em:

```text
tmp/sessions
```

Se a pasta nao existir, ela e criada automaticamente pelo `conexao.php`.

### Checkout falha

Confira:

- usuario esta logado
- carrinho tem produtos
- produto tem estoque
- banco `br` foi importado
- tabelas `carrinho`, `pedidos`, `pedido_itens` e `produtos` existem

## Observacoes para desenvolvimento

- Nao versionar arquivos temporarios da pasta `tmp/`.
- Nao versionar senhas reais de e-mail.
- Ao alterar o banco, atualize tambem o arquivo SQL principal.
- Sempre teste login, carrinho e checkout depois de mudancas em sessao ou banco.
