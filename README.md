# API de Gestão de Usuários (Backend)

Esta é uma API RESTful construída com Laravel, projetada para gerenciar usuários e endereços.

## Funcionalidades Principais

* **CRUD de Usuários**: API completa para Criar, Ler, Atualizar e Deletar usuários.
* **Gestão de Perfis**: Endpoint para listar perfis (ex: Admin, Cliente).
* **Gestão de Endereços**: Gerenciamento de múltiplos endereços por usuário (relacionamento N:N).
* **Filtros Avançados**: Filtre usuários por nome, CPF, data de criação (`created_at`) ou data de edição (`updated_at`).
* **Validação Segura**: Uso de Form Requests (`StoreUserRequest`) para garantir a integridade dos dados.
* **Respostas Padronizadas**: Uso de API Resources (`UserResource`, `ProfileResource`) para formatar as saídas JSON.

## Tecnologias Utilizadas

* **PHP**
* **Laravel**
* **MySQL** (ou o banco de dados de sua preferência)

## Instalação

1.  **Clone o repositório:**
    ```bash
    git clone https://github.com/guisheide/evaluation-back.git
    
    ```

2.  **Instale as dependências:**
    ```bash
    composer install
    ```

3.  **Configure o ambiente:**
    * Copie o arquivo de exemplo: `cp .env.example .env`
    * Abra o `.env` e configure suas credenciais do banco de dados ( `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`).

4.  **Gere a chave da aplicação:**
    ```bash
    php artisan key:generate
    ```

5.  **Rode as migrações:**
    ```bash
    php artisan migrate
    ```

6.  **(Opcional) Popule o banco com dados de teste:**
    ```bash
    php artisan db:seed
    ```

## Executando o Projeto

Para iniciar o servidor de desenvolvimento do Laravel, rode:

```bash
php artisan serve
