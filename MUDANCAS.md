# Mudanças — Login de Produtor + Categorias Dinâmicas

## ⚠️ Passo obrigatório antes de testar
Rode o arquivo **`migracao_produtor.sql`** no seu banco `origem` (phpMyAdmin → aba SQL → cole o conteúdo do arquivo → Executar, ou `mysql -u root origem < migracao_produtor.sql`).

Ele faz, sem apagar nada que já existe:
1. Adiciona o tipo `'produtor'` ao campo `tipo` da tabela `usuarios`.
2. Liga a tabela `produtores` a um login (`usuario_id`).
3. Adiciona a coluna `senha` em `solicitacoes_produtores` (senha de acesso escolhida no cadastro).
4. Cria a tabela `categorias` (já populada com Café, Mel, Pimenta, Farinha).
5. Adiciona a coluna `nova_categoria_nome` em `solicitacoes_produtos`.

## O que foi corrigido
- **Bug encontrado:** o link "Solicitar Produto" no rodapé do site aparecia para **qualquer usuário logado**, inclusive clientes comuns. Agora só aparece para quem tem login de produtor.
- Também foi adicionado o mesmo link, com destaque, no menu da conta (ícone de usuário no topo) — só para produtores.
- Revisão geral de todo o código (`php -l` em 100% dos arquivos + verificação de todos os `bind_param`): nenhum outro erro de sintaxe ou de parâmetros encontrado nos arquivos existentes. Durante a implementação eu mesmo cometi e corrigi 2 erros de contagem de parâmetros antes de entregar (testados e validados).

## Como o login de produtor funciona agora
Antes, o cadastro em "Seja um Produtor" (`seja_produtor.php`) só criava uma solicitação — não existia login de produtor algum, e por isso a área de "Solicitar Produto" não tinha como ser restrita de verdade.

Agora:
1. No formulário **Seja um Produtor**, a pessoa também cria uma **senha de acesso**.
2. A solicitação cai no admin (`Solicitações de Produtores`) como antes.
3. Quando o admin **aprova**, o sistema cria automaticamente:
   - uma conta de login com `tipo = 'produtor'` (e-mail + senha que a pessoa escolheu);
   - o perfil de produtor (`produtores`), já vinculado a essa conta.
4. O produtor faz login normalmente em `login.php` com e-mail/senha. Como o `tipo` é `produtor`, o menu da conta passa a mostrar **"Solicitar Produto"**.
5. Clientes comuns (`tipo = 'cliente'`) e visitantes não veem essa opção em nenhum lugar do site.

## Área "Solicitar Produto" (`solicitar_produto.php`)
- Totalmente redesenhada para seguir a identidade visual do site (verde escuro/terracota, Playfair Display, ícones SVG, sem emoji).
- Não pede mais para digitar o ID do produtor — o produtor é identificado automaticamente pela sessão de login.
- Se um cliente comum tentar acessar a URL direto, vê uma tela explicando que a área é exclusiva de produtores, com botão para se cadastrar.
- Categoria do produto agora é um campo obrigatório com:
  - todas as categorias já existentes no site (puxadas do banco, sempre atualizado);
  - uma opção extra **"+ Outra Categoria"** — ao selecioná-la, aparece um campo de texto para o produtor digitar o nome da nova categoria.

## Fluxo de categoria nova
1. Produtor escolhe "+ Outra Categoria" e digita, por exemplo, "Castanhas".
2. A solicitação do produto vai para o admin com um aviso destacado: **"Nova categoria sugerida: Castanhas"**.
3. Se o admin **aprovar** o produto:
   - a categoria "Castanhas" é criada automaticamente no site (tabela `categorias`);
   - o produto é publicado já nessa nova categoria;
   - a categoria passa a aparecer junto com as outras 4 em `produtos.php` (página pública), no menu "Produtos" do cabeçalho e no painel admin (lista de categorias e formulário de produtos).
4. Se o admin **rejeitar**, nada é criado — fica só registrado o motivo da rejeição, como já acontecia antes.

## Arquivos alterados
- `migracao_produtor.sql` *(novo — execute primeiro)*
- `OrigemBanco.sql` *(atualizado para refletir o novo schema em instalações novas)*
- `seja_produtor.php`, `api/sol_produtor.php` — campo de senha de acesso
- `adm/sol_produtores.php` — criação automática do login do produtor na aprovação
- `includes/header.php`, `includes/footer.php` — restrição da área de solicitação + categorias novas no menu
- `solicitar_produto.php` — reescrito (login de produtor + categoria dinâmica + "Outra Categoria")
- `api/sol_produto.php` — reescrito (produtor identificado pela sessão, suporte a categoria nova)
- `adm/sol_produtos.php` — aviso de nova categoria + criação automática ao aprovar
- `produtos.php`, `adm/categorias.php`, `adm/produtos.php` — categorias dinâmicas (DB) em vez de listas fixas no código
