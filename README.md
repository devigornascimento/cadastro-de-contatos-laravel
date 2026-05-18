# API de Gestão de Contatos Seguro (Laravel + Docker Sail)

Esta é uma solução back-end robusta e segura construída em Laravel para o gerenciamento de contatos, integrada a uma interface de usuário otimizada que se comunica nativamente via requisições assíncronas (API REST). O projeto foi desenvolvido em um ambiente totalmente isolado utilizando contêineres Docker via Laravel Sail dentro do subsistema Linux (WSL/Ubuntu).

---

## Tecnologias Utilizadas

* **Back-End:** Laravel 11 (PHP 8.2+)
* **Banco de Dados:** MySQL 8.0
* **Ambiente de Desenvolvimento:** Docker & Laravel Sail (WSL/Ubuntu)
* **Autenticação de Máquina:** Laravel Sanctum (Tokens Bearer)
* **Front-End:** HTML5 / CSS3 / JavaScript Moderno (Fetch API & Live Server)

---

## O que foi feito no Laravel (Criado e Modificado)

1.  **Infraestrutura Isolada:** Configuração completa do ambiente através do Laravel Sail, garantindo que o servidor web e o banco de dados rodem isolados em contêineres, mantendo o sistema operacional hospedeiro limpo.
2.  **Estrutura de Dados Personalizada:**
    * Customização da tabela nativa `users` com as colunas: `telefone`, `cpf` e `data_nascimento`.
    * Criação da tabela e do modelo `Contact`, implementando um relacionamento de chave estrangeira (`user_id`) atrelado à tabela de usuários.
    * Restauração e manutenção da tabela nativa de `sessions` no banco de dados para integridade de estado do ecossistema.
3.  **Segurança de Nível Pleno/Sênior:**
    * **Criptografia em Banco:** Ativação de criptografia automática (`'cpf' => 'encrypted'`) no modelo de Usuário para proteger dados sensíveis em repouso no MySQL.
    * **Proteção contra Vazamento de Dados:** Inclusão do campo `cpf` na propriedade `$hidden` do modelo Eloquent, impedindo que o documento seja vazado em respostas JSON ou logs de API.
4.  **Controladores e Validação Rígida:**
    * `RegisteredUserController`: Modificado para atuar como escudo de segurança. Foram criadas regras regex rígidas que impedem caracteres especiais nos nomes, validam o domínio corporativo do e-mail, forçam regras de complexidade de senha (letras maiúsculas, minúsculas e símbolos) e barram anos futuros em datas de nascimento.
    * `ContactController`: Criado do zero com métodos index, store e destroy protegidos por autenticação de máquina.
5.  **Políticas de CORS e Middleware:** Ajuste estrutural em `bootstrap/app.php` para aceitar requisições de origens locais controladas (`FRONTEND_URL`) e remoção da barreira de estado CSRF para os endpoints puros de API (`register` e `login`).

---

## Desafios Enfrentados e Soluções

Durante a construção do ecossistema, nos deparamos com desafios técnicos reais de arquitetura back-end, resolvidos seguindo padrões de engenharia:

* **Desafio 1: Tabela de Sessões Inexistente (`SQLSTATE[42S02]`)**
    * *Sintoma:* O sistema retornava um erro informando que a tabela `sessions` não existia no banco de dados durante a tentativa de registro.
    * *Solução:* Correção no arquivo de migração base, restaurando o blueprint original do Laravel para as tabelas de sessão e reexecutando a limpeza estrutural com `php artisan migrate:fresh`.
* **Desafio 2: Bloqueio por Política de CSRF (`CSRF token mismatch`)**
    * *Sintoma:* Requisições via terminal ou clientes de API eram barradas com erro 419.
    * *Solução:* Como o back-end atua como uma API desacoplada, incluímos as rotas de autenticação na lista de exceções do middleware `validateCsrfTokens` dentro do arquivo central de inicialização (`bootstrap/app.php`).
* **Desafio 3: Exceção de Atribuição em Massa (`MassAssignmentException`)**
    * *Sintoma:* O banco de dados acusava que campos customizados não possuíam valor padrão, pois o Laravel os descartava silenciosamente.
    * *Solução:* Proteção de integridade resolvida adicionando explicitamente as colunas customizadas (`cpf`, `telefone`, `data_nascimento`, `nome`, `user_id`) dentro do array seguro `$fillable` nos modelos `User` and `Contact`.
* **Desafio 4: Múltiplos Valores de CORS no Navegador**
    * *Sintoma:* O navegador bloqueava as requisições porque o cabeçalho continha múltiplos endereços de origem permitidos.
    * *Solução:* Ajustamos a variável `FRONTEND_URL` no arquivo `.env` para apontar exclusivamente para o IP e porta ativos do cliente Web (`http://127.0.0.1:5500`), limpando a memória do framework com `artisan config:clear`.

---

## Como Utilizar o Projeto

### Pré-requisitos
* Windows 11 com WSL2 (Ubuntu) instalado.
* Docker Desktop configurado e rodando.

### Inicialização do Ambiente
1.  Abra o terminal do Ubuntu e navegue até a pasta do projeto.
2.  Suba os contêineres Docker em segundo plano:
    ```bash
    ./vendor/bin/sail up -d
    ```
3.  Execute as migrações para preparar o banco de dados MySQL:
    ```bash
    ./vendor/bin/sail artisan migrate
    ```
---

### Validando a API visualmente 
Para facilitar os testes de integração sem depender de ferramentas como o Postman, um arquivo `index.html` foi incluído na raiz do projeto. 
Basta abri-lo utilizando a extensão **Live Server** (na porta 5500) para visualizar a comunicação com os endpoints de autenticação e gestão de contatos em tempo real.

* **Interface de Teste:** Arquivo `index.html` estático utilizando Fetch API nativo, criado exclusivamente para homologação e testes de integração com o back-end.

### Executando o Front-End
1.  Abra o arquivo `index.html` na sua IDE (VS Code / Antigravity).
2.  Utilize a extensão **Live Server** para rodar a aplicação sob o protocolo HTTP em `http://127.0.0.1:5500`.


---

## Como Criar um Usuário para o Cadastro de Pessoas

O sistema possui duas maneiras estruturadas para realizar a criação de um novo usuário administrador (quem gerenciará os contatos):

### Método 1: Pela Interface Web (Front-End)
Basta abrir a página de registro da sua aplicação na interface visual, preencher o formulário respeitando as seguintes regras rígidas adotadas:
* **Nome:** Apenas letras e espaços (máximo 50 caracteres).
* **E-mail:** Deve ser válido e terminar obrigatoriamente com o domínio `.com`.
* **Senha:** Mínimo de 8 caracteres, contendo obrigatoriamente pelo menos uma letra maiúscula, uma minúscula e um caractere especial (ex: `@`, `#`).
* **CPF:** Deve ser enviado com pontuação regular (será limpo automaticamente pelo back-end).

### Método 2: Via Terminal (Requisição Pura)
Caso queira efetuar um cadastro administrativo rápido diretamente pelo terminal do Ubuntu sem abrir o navegador, execute o comando de automação abaixo:

```bash
curl -X POST http://localhost/register \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Igor Nascimento",
    "email": "igor.nunes@empresa.com",
    "password": "SenhaSegura@2026",
    "password_confirmation": "SenhaSegura@2026",
    "cpf": "123.456.789-00",
    "telefone": "11999999999",
    "data_nascimento": "15/05/1998"
  }'
