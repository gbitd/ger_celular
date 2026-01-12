*Sistema de Gerenciamento de Dispositivos Celulares*

**Decisões de design**
 - Não foi usada Eloquent ORM para as regras de negócio da aplicação. Apenas para a autenticação de usuário.
 - Apesar do uso direto de PDO, foram usadas as migrations do Laravel para a criação das tabelas
