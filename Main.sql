-- =============================================
-- Origem Brasil — Banco de Dados (arquivo único)
-- =============================================
-- Este arquivo substitui: OrigemBanco.sql + migracao_produtor.sql +
-- migracao_produtor_v2.sql. Todas as colunas que antes eram criadas
-- via UPDATE/ALTER já nascem certas nos CREATE TABLE/INSERT abaixo
-- (categoria já como ENUM, imagens já com subpasta, senha do admin
-- já com o hash final, produtores/solicitacoes já com usuario_id).
-- Para recriar o banco do zero, basta rodar:
--   mysql -u root < OrigemBanco.sql
-- =============================================

CREATE DATABASE IF NOT EXISTS br CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE br;

-- Tabela de usuários
CREATE TABLE IF NOT EXISTS usuarios (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    nome                VARCHAR(100) NOT NULL,
    email               VARCHAR(100) NOT NULL UNIQUE,
    senha               VARCHAR(255) NOT NULL,
    tipo                ENUM('cliente','produtor','admin') DEFAULT 'cliente',
    foto                VARCHAR(255) DEFAULT NULL,
    telefone            VARCHAR(20) DEFAULT NULL,
    codigo_recuperacao  VARCHAR(6) NULL,
    codigo_expira       DATETIME NULL,
    criado_em           TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela de endereços do usuário
CREATE TABLE IF NOT EXISTS enderecos (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id  INT NOT NULL,
    apelido     VARCHAR(50) DEFAULT 'Casa',
    cep         VARCHAR(10),
    rua         VARCHAR(150),
    numero      VARCHAR(20),
    complemento VARCHAR(100),
    bairro      VARCHAR(100),
    cidade      VARCHAR(100),
    estado      VARCHAR(2),
    principal   TINYINT(1) DEFAULT 0,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Tabela de produtores
CREATE TABLE IF NOT EXISTS produtores (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id  INT NULL UNIQUE,
    nome        VARCHAR(100) NOT NULL,
    foto        VARCHAR(255),
    historia    TEXT,
    fazenda     VARCHAR(100),
    regiao      VARCHAR(100),
    estado      VARCHAR(50),
    latitude    DECIMAL(10,8),
    longitude   DECIMAL(11,8),
    criado_em   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
);

-- Categorias dinâmicas do site (alimenta produtos.php, menu e admin)
CREATE TABLE IF NOT EXISTS categorias (
    slug        VARCHAR(50) PRIMARY KEY,
    label       VARCHAR(100) NOT NULL,
    descricao   VARCHAR(200) DEFAULT NULL,
    cor         VARCHAR(20)  DEFAULT '#2C4A2E',
    criado_em   TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT IGNORE INTO categorias (slug, label, descricao, cor) VALUES
('cafe',    'Cafés Especiais',    'Grãos selecionados direto dos produtores brasileiros', '#6B3A2A'),
('mel',     'Méis Artesanais',    'Mel puro de abelhas nativas e europeias do Brasil',     '#D4622A'),
('pimenta', 'Pimentas Especiais', 'Pimentas cultivadas com técnicas artesanais brasileiras','#EF4444'),
('farinha', 'Farinhas Naturais',  'Farinhas orgânicas e funcionais da agricultura familiar','#D97706');

-- Tabela de produtos
CREATE TABLE IF NOT EXISTS produtos (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    nome         VARCHAR(100) NOT NULL,
    descricao    TEXT,
    historia     TEXT,
    preco        DECIMAL(10,2) NOT NULL,
    categoria    VARCHAR(50) NOT NULL DEFAULT 'cafe',
    imagem       VARCHAR(255),
    estoque      INT DEFAULT 0,
    produtor_id  INT DEFAULT NULL,
    regiao       VARCHAR(100),
    origem       VARCHAR(100),
    peso         VARCHAR(30),
    destaque     TINYINT(1) DEFAULT 0,
    criado_em    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (produtor_id) REFERENCES produtores(id) ON DELETE SET NULL,
    FOREIGN KEY (categoria) REFERENCES categorias(slug) ON UPDATE CASCADE
);

-- Tabela de avaliações
CREATE TABLE IF NOT EXISTS avaliacoes (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id  INT NOT NULL,
    produto_id  INT NOT NULL,
    nota        TINYINT NOT NULL CHECK (nota BETWEEN 1 AND 5),
    comentario  TEXT,
    criado_em   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_avaliacao (usuario_id, produto_id),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (produto_id) REFERENCES produtos(id) ON DELETE CASCADE
);

-- Tabela de pedidos
CREATE TABLE IF NOT EXISTS pedidos (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id  INT,
    total       DECIMAL(10,2),
    frete       DECIMAL(10,2) DEFAULT 0,
    desconto    DECIMAL(10,2) DEFAULT 0,
    status      ENUM('pendente','pago','enviado','entregue','cancelado') DEFAULT 'pendente',
    pagamento   VARCHAR(30) DEFAULT 'cartao',
    endereco_id INT DEFAULT NULL,
    observacao  TEXT,
    data_pedido TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
);

-- Tabela de itens do pedido
CREATE TABLE IF NOT EXISTS pedido_itens (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    pedido_id   INT NOT NULL,
    produto_id  INT,
    quantidade  INT NOT NULL,
    preco       DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (pedido_id)  REFERENCES pedidos(id)  ON DELETE CASCADE,
    FOREIGN KEY (produto_id) REFERENCES produtos(id) ON DELETE SET NULL
);

-- Tabela de carrinho
CREATE TABLE IF NOT EXISTS carrinho (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id    INT NOT NULL,
    produto_id    INT NOT NULL,
    quantidade    INT NOT NULL DEFAULT 1,
    adicionado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_carrinho (usuario_id, produto_id),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (produto_id) REFERENCES produtos(id) ON DELETE CASCADE
);

-- Tabela de favoritos
CREATE TABLE IF NOT EXISTS favoritos (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id  INT NOT NULL,
    produto_id  INT NOT NULL,
    UNIQUE KEY uniq_fav (usuario_id, produto_id),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (produto_id) REFERENCES produtos(id) ON DELETE CASCADE
);

-- =============================================
-- Usuário admin padrão  (senha: admin123)
-- =============================================
INSERT IGNORE INTO usuarios (nome, email, senha, tipo) VALUES
('Administrador', 'admin@origembrasil.com',
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uHwHWn36G', 'admin');
-- Hash já atualizado (equivalente ao 2º UPDATE que existia no arquivo antigo)

-- =============================================
-- Produtores de exemplo
-- =============================================
INSERT INTO produtores (nome, foto, historia, fazenda, regiao, estado, latitude, longitude) VALUES
('João da Silva Santos',
 'uploads/produtores/produtor1.jpg',
 'João é a terceira geração de cafeicultores da família Santos. Nascido e criado no Cerrado Mineiro, aprendeu com seu avô os segredos da torra artesanal. Hoje comanda 80 hectares de café arábica certificado, aliando técnicas tradicionais a práticas sustentáveis modernas.',
 'Fazenda Boa Esperança', 'Cerrado Mineiro', 'MG', -18.5122, -46.5130),

('Maria Aparecida Oliveira',
 'uploads/produtores/produtor2.jpg',
 'Maria transformou a pequena propriedade herdada da família em referência nacional em grãos especiais. Pioneira no movimento de mulheres cafeicultoras de Minas Gerais, ela exporta para mais de 12 países e mantém um projeto de preservação da Mata Atlântica em sua fazenda.',
 'Sítio Flor da Serra', 'Serra da Canastra', 'MG', -20.3827, -46.5553),

('Pedro Alves Ferreira',
 'uploads/produtores/produtor3.jpg',
 'Pedro dedicou 20 anos ao desenvolvimento de granolas funcionais usando ingredientes 100% brasileiros. Parceiro de universidades de nutrição, seus produtos são referência em saúde e sustentabilidade. Cada lote é rastreável desde o campo até a embalagem.',
 'Cooperativa Sementes do Brasil', 'Planalto Central', 'GO', -15.7801, -47.9292);

-- =============================================
-- Cafés
-- =============================================

INSERT INTO produtos
(nome, descricao, historia, preco, categoria, imagem, estoque, produtor_id, regiao, origem, peso, destaque)
VALUES

('Café Bourbon Amarelo',
'Café especial com notas de chocolate e caramelo.',
'Cultivado em pequenas propriedades da Mantiqueira de Minas, reconhecida pela produção de cafés especiais.',
42.90,
'cafe',
'imagens/cafe/cafe-bourbon-amarelo.jpg',
30,
1,
'Mantiqueira de Minas',
'Minas Gerais',
'500g',
1),

('Café Catuaí Vermelho',
'Café de acidez equilibrada e aroma intenso.',
'Produzido por agricultores familiares do Cerrado Mineiro.',
39.90,
'cafe',
'imagens/cafe/cafe-catuai-vermelho.jpg',
25,
2,
'Cerrado Mineiro',
'Minas Gerais',
'500g',
0),

('Café Mundo Novo',
'Café encorpado com notas de castanhas.',
'Variedade tradicional brasileira cultivada em altitude.',
44.90,
'cafe',
'imagens/cafe/cafe-mundo-novo.jpg',
20,
3,
'Alta Mogiana',
'São Paulo',
'500g',
1),

('Café Acaiá',
'Café suave e adocicado.',
'Produzido em fazendas familiares do Sul de Minas.',
46.90,
'cafe',
'imagens/cafe/cafe-acaia.jpg',
18,
3,
'Sul de Minas',
'Minas Gerais',
'500g',
0),

('Café Arara',
'Café premiado de perfil frutado.',
'Uma das variedades mais valorizadas dos cafés especiais brasileiros.',
54.90,
'cafe',
'imagens/cafe/cafe-arara.jpg',
15,
1,
'Cerrado Mineiro',
'Minas Gerais',
'500g',
1),

('Café Mantiqueira de Minas',
'Café com indicação geográfica reconhecida.',
'Produzido em montanhas com condições ideais para cafés especiais.',
49.90,
'cafe',
'imagens/cafe/cafe-mantiqueira-minas.jpg',
22,
3,
'Mantiqueira de Minas',
'Minas Gerais',
'500g',
1),

('Café Cerrado Mineiro',
'Café equilibrado e aromático.',
'Originário da primeira região cafeeira do Brasil com denominação de origem.',
47.90,
'cafe',
'imagens/cafe/cafe-cerrado-mineiro.jpg',
20,
2,
'Cerrado Mineiro',
'Minas Gerais',
'500g',
1);

-- =============================================
-- Mel
-- =============================================

INSERT INTO produtos
(nome, descricao, historia, preco, categoria, imagem, estoque, produtor_id, regiao, origem, peso, destaque)
VALUES

('Mel de Tubuna',
'Mel raro produzido por abelhas nativas.',
'Obtido por meliponicultores que preservam espécies brasileiras sem ferrão.',
49.90,
'mel',
'imagens/mel/mel-tubuna.jpg',
15,
2,
'Mata Atlântica',
'Santa Catarina',
'300g',
1),

('Mel de Aroeira',
'Mel encorpado e de sabor marcante.',
'Produzido durante a florada da aroeira no norte de Minas.',
39.90,
'mel',
'imagens/mel/mel-aroeira.jpg',
20,
3,
'Norte de Minas',
'Minas Gerais',
'300g',
0),

('Mel de Bracatinga',
'Mel de melato raro e valorizado.',
'Produzido em florestas de araucária do Sul do Brasil.',
54.90,
'mel',
'imagens/mel/mel-bracatinga.jpg',
10,
1,
'Planalalto Sul',
'Santa Catarina',
'300g',
1),

('Mel Silvestre',
'Mel obtido de diversas floradas nativas.',
'Representa a biodiversidade brasileira.',
34.90,
'mel',
'imagens/mel/mel-silvestre.jpg',
25,
2,
'Campos Gerais',
'Paraná',
'300g',
0),

('Mel de Jataí',
'Mel delicado de abelhas sem ferrão.',
'Produzido por comunidades que preservam espécies nativas.',
44.90,
'mel',
'imagens/mel/mel-jatai.jpg',
18,
3,
'Mata Atlântica',
'São Paulo',
'250g',
1),

('Mel de Mandaçaia',
'Mel suave com aroma floral.',
'Resultado de manejo sustentável da meliponicultura.',
46.90,
'mel',
'imagens/mel/mel-mandacaia.jpg',
15,
1,
'Chapada Diamantina',
'Bahia',
'250g',
0),

('Mel de Uruçu',
'Mel nobre e aromático.',
'Produzido por abelhas nativas do Nordeste brasileiro.',
47.90,
'mel',
'imagens/mel/mel-urucu.jpg',
12,
1,
'Sertão',
'Pernambuco',
'250g',
1),

('Mel de Guaraipo',
'Mel raro e muito apreciado.',
'Produzido em regiões preservadas do Sul do Brasil.',
52.90,
'mel',
'imagens/mel/mel-guaraipo.jpg',
10,
3,
'Serra Catarinense',
'Santa Catarina',
'250g',
1),

('Mel de Manduri',
'Mel de sabor delicado e adocicado.',
'Produção artesanal em pequena escala.',
48.90,
'mel',
'imagens/mel/mel-manduri.jpg',
12,
2,
'Interior',
'Paraná',
'250g',
0);
-- =============================================

-- =============================================
-- Pimentas Especiais — INSERT produtos
-- =============================================

INSERT INTO produtos
(nome, descricao, historia, preco, categoria, imagem, estoque, produtor_id, regiao, origem, peso, destaque)
VALUES

('Pimenta Malagueta',
'Pimenta pequena e extremamente ardida, essencial na culinária brasileira.',
'A malagueta é símbolo da tradição culinária baiana e nordestina. Cultivada por pequenos agricultores familiares, é colhida ainda fresca e comercializada artesanalmente.',
18.90,
'pimenta',
'imagens/pimenta/pimenta-malagueta.jpg',
40, 1, 'Nordeste', 'Bahia', '100g', 1),

('Pimenta Dedo-de-Moça',
'Pimenta alongada e amarelada de ardência suave e sabor adocicado.',
'Muito apreciada em conservas e molhos artesanais, a dedo-de-moça é cultivada em pequenas propriedades do interior de Minas Gerais e São Paulo.',
16.90,
'pimenta',
'imagens/pimenta/pimenta-dedo-de-moca.jpg',
35, 2, 'Sudeste', 'Minas Gerais', '100g', 0),

('Pimenta de Cheiro Verde',
'Pimenta nativa brasileira sem ardência, com aroma marcante e sabor único.',
'Muito utilizada na culinária amazônica e nordestina. Cultivada por comunidades ribeirinhas que preservam técnicas tradicionais de cultivo.',
14.90,
'pimenta',
'imagens/pimenta/pimenta-de-cheiro-verde.jpg',
30, 3, 'Norte', 'Pará', '100g', 0),

('Pimenta Rosa',
'Pimenta-rosa brasileira com sabor suave e levemente adocicado.',
'Originária da Mata Atlântica, a pimenta-rosa (aroeira) é colhida de forma extrativista sustentável e muito valorizada na gastronomia gourmet.',
34.90,
'pimenta',
'imagens/pimenta/pimenta-rosa.jpg',
20, 1, 'Mata Atlântica', 'Santa Catarina', '50g', 1),

('Pimenta Biquinho',
'Pimenta docinha e sem ardência, ideal para conservas e petiscos.',
'A biquinho conquistou o Brasil pelo sabor marcante sem o ardor. Produzida em Minas Gerais por agricultores familiares que a cultivam em pequena escala.',
19.90,
'pimenta',
'imagens/pimenta/pimenta-biquinho.jpg',
45, 2, 'Sudeste', 'Minas Gerais', '150g', 1),

('Pimenta Cambuci',
'Pimenta em formato de chapéu, levemente picante e muito aromática.',
'Variedade tradicional brasileira, muito usada na culinária caipira. Cultivada em quintais e pequenas roças do interior paulista.',
17.90,
'pimenta',
'imagens/pimenta/pimenta-cambuci.jpg',
25, 3, 'Sudeste', 'São Paulo', '150g', 0),

('Pimenta Cayenne',
'Pimenta vermelha alongada de ardência intensa e sabor marcante.',
'Muito usada em pós e temperos artesanais. Produzida por agricultores orgânicos do Nordeste brasileiro que cultivam em secas programadas para intensificar o sabor.',
22.90,
'pimenta',
'imagens/pimenta/pimenta-cayenne.jpg',
30, 1, 'Nordeste', 'Pernambuco', '100g', 1),

('Pimenta Habanero',
'Uma das pimentas mais ardidas do mundo, com aroma frutal inconfundível.',
'Cultivada com cuidado artesanal no Nordeste brasileiro, onde o clima semiárido potencializa sua ardência e aroma. Produto raro e muito valorizado.',
29.90,
'pimenta',
'imagens/pimenta/pimenta-habanero.jpg',
15, 2, 'Nordeste', 'Ceará', '50g', 1),

('Pimenta Ardida Colorida',
'Mix de pimentas ardidas em diferentes estágios de maturação.',
'Colhida manualmente por produtores familiares do interior de Goiás, essa mistura colorida reúne sabores e ardências variadas em um único produto artesanal.',
15.90,
'pimenta',
'imagens/pimenta/pimenta-ardida-colorida.jpg',
35, 3, 'Centro-Oeste', 'Goiás', '100g', 0);
-- =============================================
-- Farinhas
-- =============================================
	INSERT INTO produtos
	(nome, descricao, historia, preco, categoria, imagem, estoque, produtor_id, regiao, origem, peso, destaque)
	VALUES
	('Farinha de Mandioca Branca',
	'Farinha fina e clara produzida a partir da mandioca fresca, versátil e essencial na mesa brasileira.',
	'Símbolo da alimentação nacional, a farinha de mandioca branca é produzida artesanalmente em casas de farinha espalhadas pelo Norte e Nordeste do Brasil. Cada lote carrega a tradição de comunidades que cultivam e processam a mandioca há gerações.',
	18.90,
	'farinha',
	'imagens/farinha/farinha-mandioca-branca.jpg',
	50, 1, 'Norte', 'Amazonas', '500g', 1),

	('Farinha de Mandioca Amarela',
	'Farinha de cor amarela intensa, produzida com variedades de mandioca pigmentada, rica em betacaroteno.',
	'Muito consumida no Norte do Brasil, especialmente no Pará e Amazonas, a farinha amarela é resultado de variedades tradicionais de mandioca com polpa colorida. Produzida em casas de farinha familiares que preservam técnicas ancestrais de torração.',
	21.90,
	'farinha',
	'imagens/farinha/farinha-mandioca-amarela.jpg',
	40, 2, 'Norte', 'Pará', '500g', 1),

	('Farinha de Tapioca',
	'Grânulos brancos e crocantes extraídos da fécula da mandioca, leves e de sabor delicado.',
	'A farinha de tapioca, conhecida também como tapioca granulada, é produzida a partir da fécula úmida da mandioca peneirada e torrada. Muito usada como acompanhamento de açaí e pratos regionais no Norte e Nordeste brasileiro.',
	24.90,
	'farinha',
	'imagens/farinha/farinha-tapioca.jpg',
	35, 3, 'Norte', 'Pará', '400g', 0),

	('Farinha de Puba',
	'Farinha produzida a partir da mandioca fermentada (pubada), com sabor levemente azedo e textura úmida.',
	'A farinha de puba é um produto tradicional da Amazônia e do Nordeste, obtida pela fermentação natural da mandioca na água. Esse processo milenar, herdado dos povos indígenas, confere à farinha sabor único e propriedades especiais muito valorizadas na culinária regional.',
	26.90,
	'farinha',
	'imagens/farinha/farinha-puba.jpg',
	30, 1, 'Norte', 'Amazonas', '500g', 1),

	('Farinha de Babaçu',
	'Farinha obtida do coco babaçu, com sabor suave e levemente adocicado, rica em fibras.',
	'O babaçu é uma palmeira nativa do cerrado e da pré-Amazônia, e sua farinha é produzida por comunidades quebradeiras de coco que vivem do extrativismo sustentável. Alimento ancestral e fonte de renda de famílias do Maranhão e Piauí.',
	32.90,
	'farinha',
	'imagens/farinha/farinha-babacau.jpg',
	25, 2, 'Nordeste', 'Maranhão', '300g', 1),

	('Farinha de Milho Crioulo',
	'Farinha rústica de milho crioulo moído em moinho de pedra, com sabor intenso e aroma marcante.',
	'O milho crioulo é uma variedade tradicional cultivada por agricultores familiares que preservam sementes passadas de geração em geração. Moída em moinhos artesanais de pedra, essa farinha carrega sabor e história da agricultura camponesa brasileira.',
	19.90,
	'farinha',
	'imagens/farinha/farinha-milho-crioulo.jpg',
	45, 3, 'Sul', 'Santa Catarina', '500g', 0);

-- =============================================
-- Sistema de Solicitações (cadastro de produtores/produtos)
-- =============================================

CREATE TABLE IF NOT EXISTS solicitacoes_produtores (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id       INT NULL,
    nome             VARCHAR(100) NOT NULL,
    email            VARCHAR(100) NOT NULL,
    senha            VARCHAR(255) NOT NULL DEFAULT '',
    telefone         VARCHAR(30),
    cpf_cnpj         VARCHAR(30),
    fazenda          VARCHAR(100),
    estado           VARCHAR(50),
    regiao           VARCHAR(100),
    municipio        VARCHAR(100),
    especialidade    VARCHAR(200),
    mensagem         TEXT,
    historia         TEXT,
    foto             VARCHAR(255),
    status           ENUM('pendente','aprovado','rejeitado') DEFAULT 'pendente',
    motivo_rejeicao  TEXT,
    produtor_id      INT DEFAULT NULL,
    analisado_em     DATETIME DEFAULT NULL,
    criado_em        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (produtor_id) REFERENCES produtores(id) ON DELETE SET NULL,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS solicitacoes_produtos (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    produtor_id      INT NOT NULL,
    nome             VARCHAR(100) NOT NULL,
    descricao        TEXT,
    historia         TEXT,
    preco            DECIMAL(10,2),
    categoria        VARCHAR(50),
    nova_categoria_nome VARCHAR(100) NULL,
    foto             VARCHAR(255),
    regiao           VARCHAR(100),
    origem           VARCHAR(100),
    peso             VARCHAR(30),
    estoque_inicial  INT DEFAULT 0,
    status           ENUM('pendente','aprovado','rejeitado') DEFAULT 'pendente',
    motivo_rejeicao  TEXT,
    produto_id       INT DEFAULT NULL,
    analisado_em     DATETIME DEFAULT NULL,
    criado_em        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (produtor_id) REFERENCES produtores(id) ON DELETE CASCADE,
    FOREIGN KEY (produto_id)  REFERENCES produtos(id)   ON DELETE SET NULL
);

