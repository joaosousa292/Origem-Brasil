# Origem Brasil — E-commerce Completo

## ⚡ Instalação Rápida (XAMPP/WAMP)

### 1. Copiar o projeto
Coloque a pasta `origem_brasil` dentro de:
- **XAMPP**: `C:\xampp\htdocs\`
- **WAMP**: `C:\wamp64\www\`

### 2. Criar o banco de dados
1. Abra o **phpMyAdmin** em `http://localhost/phpmyadmin`
2. Crie um banco chamado `origem_brasil` (ou execute o SQL abaixo)
3. Importe o arquivo `OrigemBanco.sql`

### 3. Configurar conexão
Edite o arquivo `conexao.php` se necessário:
```php
$host    = "localhost";
$usuario = "root";
$senha   = "";       // sua senha do MySQL
$banco   = "origem_brasil";
```

### 4. Acessar
- **Loja**: `http://localhost/origem_brasil/`
- **Admin**: `http://localhost/origem_brasil/adm.php`

---

## 🔑 Credenciais Padrão

| Tipo | E-mail | Senha |
|------|--------|-------|
| Admin | admin@origembrasil.com | admin123 |

---

## 📁 Estrutura do Projeto

```
origem_brasil/
├── api/                    ← APIs PHP (carrinho, favoritos, checkout, avaliações)
├── admin/                  ← Pasta reservada para futuras extensões do admin
├── css/
│   └── style.css           ← CSS principal unificado
├── js/
│   └── carrinho.js         ← Lógica JS do carrinho + favoritos + toast
├── includes/
│   ├── header.php          ← Header compartilhado
│   ├── footer.php          ← Footer compartilhado
│   └── carrinho_sidebar.php← Sidebar do carrinho
├── imagens/                ← Imagens estáticas (café, hero, etc.)
├── uploads/
│   ├── produtos/           ← Upload de imagens de produtos
│   └── produtores/         ← Upload de fotos de produtores
├── conexao.php             ← Conexão com banco + session_start
├── index.php               ← Página inicial
├── cafes.php               ← Catálogo de cafés com filtros
├── cereais.php             ← Catálogo de cereais com filtros
├── produto.php             ← Página individual do produto
├── produtor.php            ← Página do produtor
├── busca.php               ← Busca global
├── checkout.php            ← Finalização de compra
├── pedidos.php             ← Histórico de pedidos do usuário
├── perfil.php              ← Perfil + endereços + senha
├── favoritos.php           ← Página de favoritos
├── sobre.php               ← Sobre a empresa
├── login.php               ← Login
├── cadastro.php            ← Cadastro de usuário
├── validar_login.php       ← Processador de login
├── salvar_usuario.php      ← Processador de cadastro
├── logout.php              ← Logout
├── esquece.php             ← Recuperação de senha
├── adm.php                 ← Painel administrativo completo
└── OrigemBanco.sql         ← SQL completo do banco de dados
```

---

## 🛒 Funcionalidades Implementadas

### Sistema de Usuários
- ✅ Cadastro com senha bcrypt
- ✅ Login com sessão PHP
- ✅ Logout
- ✅ Perfil editável
- ✅ Alteração de senha
- ✅ Endereços múltiplos
- ✅ Recuperação de senha (fluxo base)

### Produtos
- ✅ Página individual com tabs (Descrição / História / Produtor)
- ✅ Avaliações com estrelas
- ✅ QR Code de origem (modal)
- ✅ Produtos relacionados
- ✅ Filtros e ordenação
- ✅ Badge de estoque

### Carrinho
- ✅ Persistência no banco de dados
- ✅ Sidebar animada
- ✅ Adicionar / remover / ajustar quantidade
- ✅ Validação de estoque em tempo real
- ✅ Toast notifications

### Checkout Real
- ✅ Salva pedido no banco
- ✅ Salva itens com preço no momento da compra
- ✅ Decrementa estoque automaticamente
- ✅ Limpa carrinho após finalizar
- ✅ Transação MySQL (rollback em caso de erro)
- ✅ Suporte a cupons (ORIGEM10 = 10% de desconto)

### Admin
- ✅ Dashboard com métricas
- ✅ CRUD completo de produtos
- ✅ Upload de imagens
- ✅ Gestão de pedidos com troca de status
- ✅ Lista de usuários
- ✅ Proteção por tipo 'admin'

### Diferenciais
- ✅ QR Code de origem do produto
- ✅ Storytelling do produto e produtor
- ✅ Mapa da fazenda (coordenadas GPS)
- ✅ Página completa do produtor

---

## 🔒 Segurança
- `password_hash()` / `password_verify()`
- Prepared Statements em todas as queries
- `htmlspecialchars()` em todos os outputs
- Validação de sessão em rotas protegidas
- Proteção de tipo admin no painel
- `session_start()` centralizado em `conexao.php`
