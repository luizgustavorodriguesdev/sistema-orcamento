🛍️ OrçaBrindes - Sistema de Orçamentos Online
📖 Sobre o Projeto

O OrçaBrindes é uma solução web completa e robusta, desenhada para modernizar e otimizar o processo de orçamentos para lojas de brindes e produtos personalizados. A plataforma oferece um painel de controlo administrativo poderoso para a gestão completa do negócio e uma vitrine pública interativa para que os clientes possam montar os seus próprios pedidos de orçamento.

Construído com uma stack de tecnologias modernas, o sistema foca-se numa experiência de utilizador fluida, tanto para o vendedor como para o cliente final.
✨ Funcionalidades Principais

O sistema é dividido em duas frentes principais, repletas de funcionalidades profissionais:
Painel Administrativo (Dashboard)

    ✅ Gestão de Produtos Avançada:

        CRUD completo de produtos.

        Categorias de Produtos: Organize os seus produtos de forma intuitiva.

        Preço Promocional: Defina preços "de/por" para criar ofertas.

        Editor de Texto Rico: Crie descrições de produtos detalhadas e formatadas.

        Galeria de Imagens: Carregue uma imagem principal e várias imagens secundárias para cada produto.

        Preços por Quantidade: Defina escalas de preços dinâmicas (ex: 10-20 unidades = RX,21−50unidades=RY).

    ✅ Gestão Comercial Completa:

        CRUD de Clientes: Mantenha uma base de dados dos seus clientes.

        CRUD de Vendedores: Gira os utilizadores do sistema com diferentes níveis de acesso (admin/vendedor).

        CRUD de Formas de Pagamento: Cadastre contas bancárias, PIX e condições de pagamento para reutilizar nos orçamentos.

    ✅ Criação de Orçamentos Inteligente:

        Interface interativa para montar orçamentos.

        Seleção de Clientes: Pesquise e selecione clientes existentes ou adicione um novo sem sair da tela.

        Cálculo Automático: O preço dos itens é ajustado automaticamente com base na quantidade e nas escalas de preços definidas.

        Geração de Link Público: Crie e copie um link único para enviar o orçamento ao cliente.

    ✅ Configurações Gerais da Loja:

        Personalize o sistema com os dados da sua empresa (nome, CNPJ, morada, etc.).

        Configure informações de contacto, redes sociais e o domínio da aplicação.

Vitrine Pública (Cliente)

    ✅ Layout de E-commerce Profissional:

        Cabeçalho com logo e menu de categorias.

        Banner promocional.

        Grelha de produtos com paginação.

        Rodapé com informações da empresa.

    ✅ Carrinho de Orçamento Interativo:

        Clientes podem adicionar produtos ao seu pedido de orçamento.

        O ícone do carrinho atualiza em tempo real.

    ✅ Finalização de Orçamento Self-Service:

        Página de carrinho onde o cliente pode rever os itens e ajustar as quantidades.

        O preço é recalculado dinamicamente com base na quantidade.

        O cliente preenche os seus dados e gera o seu próprio link de orçamento, que fica registado no sistema para o vendedor.

🚀 Tecnologias Utilizadas

Tecnologia
	

Descrição

Backend
	

Laravel - Framework PHP robusto para toda a lógica de negócio, APIs e gestão de dados.

Frontend
	

Vue.js + Inertia.js - Para criar uma interface de utilizador reativa e moderna, com a performance de uma SPA.

Estilização
	

Tailwind CSS - Framework CSS utility-first para um design rápido, consistente e totalmente personalizável.

Editor de Texto
	

Tiptap - Biblioteca moderna e extensível para a funcionalidade de editor de texto rico.

Banco de Dados
	

MySQL - Sistema de gestão de base de dados relacional para armazenar todos os dados da aplicação.
🗃️ Estrutura do Banco de Dados

Tabela
	

Descrição

users
	

Armazena os dados dos vendedores/administradores.

clients
	

Armazena os dados dos clientes da loja.

categories
	

Guarda as categorias dos produtos.

products
	

Catálogo de todos os produtos disponíveis.

product_images
	

Armazena o caminho das imagens de cada produto.

price_tiers
	

Guarda as diferentes escalas de preços por quantidade para cada produto.

payment_methods
	

Armazena as formas de pagamento pré-definidas.

settings
	

Guarda as configurações gerais da loja no formato chave-valor.

quotes
	

Armazena os dados principais de cada orçamento.

quote_product
	

Tabela pivot que relaciona os produtos e as suas quantidades a um orçamento.
⚙️ Instalação e Execução

Para rodar este projeto localmente, siga os passos abaixo:

    Clone o repositório:

    git clone https://github.com/luizgustavorodriguesdev/sistema_orcamento.git
    cd sistema_orcamento

    Instale as dependências do Composer:

    composer install

    Configure o ambiente:

        Copie .env.example para .env.

        Gere a chave da aplicação: php artisan key:generate.

        Configure as credenciais do seu banco de dados no ficheiro .env.

    Crie o link de armazenamento:

    php artisan storage:link

    Execute as migrations:

    php artisan migrate

    Instale as dependências do NPM:

    npm install

    Inicie os servidores:

    # Num terminal
    npm run dev

    # Noutro terminal
    php artisan serve

Acesse http://localhost:8000 no seu navegador e explore a aplicação!