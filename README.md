# Sistema de Gerenciamento de Dispositivos Celulares

## Frontend

O frontend da aplicação é uma **SPA desenvolvida em Angular**, responsável por consumir a API REST do backend e fornecer a interface de gerenciamento dos dispositivos celulares.

### Principais características

- Desenvolvido com **Angular (standalone components)**
- Uso de **Angular Material** para layout e componentes de UI
- Consumo da API via **HttpClient** centralizado em serviços
- Autenticação baseada em **Bearer Token**, persistido no `localStorage`
- Proteção de rotas com **Auth Guard**
- Formulários reativos (**Reactive Forms**) com validações
- Listagem de dispositivos com:
  - filtros combináveis (localização, status, período de compra)
  - ações de marcar como em uso e exclusão
- Persistência dos filtros no `localStorage`
- Testes unitários com **Jasmine/Karma**

### Execução em ambiente de desenvolvimento

Entre no diretório do frontend:

```bash
cd frontend-app
```

Instale as dependências:

```bash
npm install
```

Inicie o servidor de desenvolvimento:

```bash
ng serve
```

A aplicação estará disponível em:

```
http://localhost:4200
```

> ⚠️ O frontend espera que a API esteja rodando localmente (via Laravel Sail), conforme descrito na seção de Backend.

---

## Backend

Com o código fonte disponível no diretório ./backend-app, uma API REST desenvolvida em **Laravel 12** para gerenciamento de dispositivos celulares, com autenticação de usuários e operações CRUD, utilizando **PDO diretamente** para acesso ao banco de dados, sem uso de Eloquent ORM no domínio das regras de negócio aplicação.

---

## 📋 Funcionalidades

- Autenticação de usuários via API (Laravel Sanctum)
- CRUD de dispositivos celulares
- Soft Delete de dispositivos
- Filtros e ordenações na listagem
- Isolamento de dados por usuário autenticado
- Validações de entrada
- Testes automatizados com PHPUnit
- Documentação da API via Postman Collection

---

## ⚙️ Setup do Projeto

Para a configuração do ambiente de desenvolvimento deste projeto foi utilizado o `Laravel Sail`, uma interface de linha de comando que ajuda a interagir com o ambiente de desenvolvimento Docker padrão do Laravel.

Por hora ainda não configurei um deploy com docker-compose, por isso ainda se faz necessário simular o ambiente de desenvolvimento para executar o projeto.

### Entre no diretório ./backend-app

```bash
cd backend-app
```

### 1. Configurar `.env`

Copie o .env.example:

```bash
cp .env.example .env
```

Preencha os campos com o padrão Laravel Sail:

```env
DB_PASSWORD=password
```

### 2. Instalar dependências

```bash
composer install
```

### 3. Inicializar ambiente Docker/Sail

```bash
./vendor/bin/sail up -d
```

### 4. Criar `APP_KEY`

```bash
./vendor/bin/sail artisan key:generate
```

### 5. Rodar migrations

```bash
./vendor/bin/sail artisan migrate
```

### Pronto!

Seu ambiente de desenvolvimento está totalmente configurado e a API está rodando em `localhost:8080`

---

## 🔐 Autenticação

A autenticação é feita via **Laravel Sanctum**, usando **Bearer Token**.

### Endpoints públicos

- `POST /api/register`
- `POST /api/login`
- `POST /api/logout`

### Endpoints protegidos

- Todos os endpoints de `/api/devices`

O token retornado no login deve ser enviado no header:

```
Authorization: Bearer {TOKEN}
```

---

## 📡 Endpoints da API

### ➕ Criar dispositivo

```
POST /api/devices
```

```json
{
  "name": "iPhone 16",
  "location": "Escritório",
  "purchase_date": "2026-01-01"
}
```

---

### 📄 Listar dispositivos

```
GET /api/devices
```

Filtros opcionais:
- `in_use` (0 ou 1)
- `location`
- `from`
- `to`
- `page`

---

### ✏️ Atualizar dispositivo

```
PUT /api/devices/{id}
```

---

### 🔁 Marcar / desmarcar como em uso

```
PATCH /api/devices/{id}/use
```

---

### 🗑️ Excluir dispositivo

```
DELETE /api/devices/{id}
```

---

## 🧪 Testes Automatizados

Os testes da API foram escritos usando **PHPUnit**.

```bash
./vendor/bin/sail artisan test
```

---

## 📘 Documentação da API (Postman)

A documentação da API está disponível em uma **Postman Collection**.

Arquivo:
```
./ger_celular API.postman_collection.json
```

### Como usar
1. Importar a collection no Postman
2. Executar o endpoint **Register** e/ou **Login**
3. O token será salvo automaticamente. Caso não aconteça, copie o token resultante e cole nas Headers (Authorization: Bearer {{Token}}) dos endpoints protegidos que deseja executar.
4. Executar os endpoints protegidos

---

## 🧠 Decisões de Design

- Não foi usada **Eloquent ORM** para consultas da aplicação.  
  Para isso, foi utilizado o padrão **Repository** com **PDO** e SQL explícito.

- O padrão de autenticação de usuário do Laravel foi mantido, utilizando **Eloquent** no model `User`.

- **Migrations** foram usadas para a criação das tabelas. Elas utilizam o Schema Builder do Laravel.

- Apesar do uso direto de PDO, as migrations do Laravel foram mantidas para garantir versionamento e portabilidade do banco de dados.

- Implementação de **Soft Delete**, utilizando o campo:
  ```
  deleted_at
  ```

- **AuthController** é usado para as operações de autenticação da API (registro, login e logout).

---

## 📌 Roadmap

- Finalizar testes automatizados para o Frontend;
- Implementar deploy com docker-compose;
- Melhorar a UX com mensagens de sucesso e erro com MatSnackBar;
- Separar os components do frontend em diferentes endpoints;
