# Sistema de Gerenciamento de Dispositivos Celulares

## Backend

Com o código fonte disponível no diretório ./backend, uma API REST desenvolvida em **Laravel 12** para gerenciamento de dispositivos celulares, com autenticação de usuários e operações CRUD, utilizando **PDO diretamente** para acesso ao banco de dados, sem uso de Eloquent ORM no domínio das regras de negócio aplicação.

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
Para a configuração do ambiente deste projeto foi utilizado o `Laravel Sail`, uma interface de linha de comando que ajuda a interagir com o ambiente de desenvolvimento Docker padrão do Laravel.

### 1. Configurar `.env`
Copie o .env.example:
```bash
cp .env.example .env
```

Preencha os campos com o padrão Laravel Sail

```env
DB_PASSWORD=password
```


### 1. Instalar dependências
```bash
composer install
```

### 2. Inicializar ambiente Docker/Sail
```bash
./vendor/bin/sail up -d
```
### 3. Criar `APP_KEY`:
```bash
./vendor/bin/sail artisan key:generate
```

### 4. Rodar migrations
```bash
 ./vendor/bin/sail artisan migrate
```

### Pronto!
Seu ambiente de desenvolvimento está totalmente configurado e a aplicação está rodando em `localhost:8080`

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

### 📄 Listar dispositivos (com filtros a partir de Query Strings)
```
GET /api/devices
```

Filtros opcionais:
- `in_use` (0 ou 1)
- `location` (localização)
- `from` (data inicial)
- `to` (data final)
- `page` (número da página)

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

### 🗑️ Excluir dispositivo (Soft Delete)
```
DELETE /api/devices/{id}
```

---

## ❌ Respostas de erro

### 422 – Erro de validação
```json
{
  "message": "Erro de validação",
  "errors": {
    "purchase_date": [
      "The purchase date field must be a date before or equal to today."
    ]
  }
}
```

### 401 – Não autenticado
```json
{
  "message": "Unauthenticated."
}
```

### 404 – Recurso não encontrado
```json
{
  "message": "Dispositivo não encontrado"
}
```

---

## 🧪 Testes Automatizados

Os testes foram escritos usando **PHPUnit**, cobrindo:
- Autenticação
- Validações
- Regras de negócio
- Proteção de rotas
- Soft delete
- Isolamento por usuário

### Executar os testes
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

- Implementação de **Soft Delete manual**, utilizando o campo:
  ```
  deleted_at
  ```

- **AuthController** é usado para as operações de autenticação da API (registro, login e logout).

---

## 📌 Observações finais

Este projeto foi desenvolvido com foco em:
- Clareza arquitetural
- Separação de responsabilidades
- Código testável
- Boas práticas do Laravel moderno (v12)

---
