*Sistema de Gerenciamento de Dispositivos Celulares*

**Decisões de design**
 - Não foi usada Eloquent ORM para consultas da aplicação. Para isso, foi usado o padrão Repository com PDO;
 - O padrão de autenticação de usuário foi mantido. Ele usa Eloquent;
 - Migrations foram usadas para a criação das tabelas. Elas usam Eloquent;
 - Apesar do uso direto de PDO, foram usadas as migrations do Laravel para a criação das tabelas
