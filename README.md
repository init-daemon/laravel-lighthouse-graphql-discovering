-   `composer install`
-   `php artisan octane:install --server=frankenphp`
-   `php artisan octane:start`

# GraphQL : Laravel lighthouse

## installation

-   `php artisan vendor:publish --tag=lighthouse-schema`
    -   un fichier sera créé dans graphql/
-   `php artisan lighthouse:ide-helper`
-   `composer require mll-lab/laravel-graphiql`

## exemple de requete

-   apres l'installation de graphiql, on peut aller à l'endpoint `/graphiql` pour tester l'application

```php
//il faut assurer qu'on a la ligne dans la base
//si vous n'avez pas encore de ligne creez-en un: php artisan tinker, puis User::factory()->create()
{
  user(id: "1") {
    name
  }
}
```

## configuration

-   publier la configuration de lighthouse à l'aide de la commande au dessous, la configuration se placera dans: `config/lighthouse.php`.

```console
php artisan vendor:publish --tag=lighthouse-config
```

-   dans le fichier config/lighthouse.php, on peut modifier l'endpoint de graphql, par exemple modifier /graphl en /api, ce dernier sera utilisé par graphql

## schema

-   est utilisé pour definir le type de retour
-   lorsque vous avez publier le schema à l'aide de `php artisan vendor:publish --tag=lighthouse-schema`, l'exemple se placera dans graphql/schema.graphql

### type

#### Object type

-   Directement lié à votre modèle, permet de definir le type de ressource de l'api. Ils sont composé de nom unique et d'ensemble de champ.

```graphql
type User {
    id: ID!
    name: String!
    email: String!
    created_at: String!
    updated_at: String
}

type Query {
    users: [User!]!
    user(id: ID!): User
}
```

#### scalar

-   element basic et ou primitif(String, Int). Utilisé généralement pour les champs d'un type d'objet
-   pour definir un scalar:

```graphql
"A datetime string with format `Y-m-d H:i:s`, e.g. `2018-05-23 13:43:32`."
scalar DateTime  #definition
    @scalar(class: "Nuwave\\Lighthouse\\Schema\\Types\\Scalars\\DateTime") #indication de la classe lié, @scalar est un directive permettant de modifier la valeur de l'element(parsing)
```

-   la création d'un Scalar peut être fait en utilisant la commande `php artisan lighthouse:scalar <Scalar name>`, le fichier sera créé dans GraphQL/Scalars, exemple pour montré comment on modifie les donnée de l'intgerieur vers l'exterieur:

```console
php artisan lighthouse:scalar Upper #crééra GraphQL/Scalars/Upper
```

```php GraphQL/Scalars/Upper
//GraphQL/Scalars/Upper
<?php declare(strict_types=1);

namespace App\GraphQL\Scalars;

use GraphQL\Language\AST\Node;
use GraphQL\Type\Definition\ScalarType;

final class Upper extends ScalarType
{
    public function serialize(mixed $value): mixed
    {
        return strtoupper($value);
    }

    public function parseValue(mixed $value): mixed
    {
        return $value;
    }
    public function parseLiteral(Node $valueNode, ?array $variables = null): mixed
    {
        return $valueNode;
    }
}

```

#### enum

-   commes son nom l'indique, ca permet de definir l'element n'accepte qu'une liste de valeur possible
-   condition de nommage en uppercase
-   defintion:

```graphql
enum EmploymentStatus {
    INTERN @enum(value: 0)
    EMPLOYEE @enum(value: 1)
    TERMINATED @enum(value: 2)
}

type Employee {
    id: ID!
    name: String
    status: EmploymentStatus!
}

type Query {
    employees: [Employee!]! @all
}
```

-   dans l'exemple précedent, les valeurs récupéré de la base de donnée pour le status de l'employé sont des integer(0/1/2), lorsqu'on retourne ces valeur à l'utilisateur ils seront converti en string(INTERN/EMPLOYEE/TERMINATED), exemple:

```php
//pour le tableau:
[
  ['name' => 'Hans', 'status' => 0],
  ['name' => 'Pamela', 'status' => 1],
  ['name' => 'Gerhard', 'status' => 2],
];
```

avec query de type:

```graphql
{
    employees {
        name
        status
    }
}
```

on aura comme response:

```json
{
    "data": {
        "employees": [
            { "name": "Hans", "status": "INTERN" },
            { "name": "Pamela", "status": "EMPLOYEE" },
            { "name": "Gerhard", "status": "TERMINATED" }
        ]
    }
}
```

#### Input

-   ressemble à peu près à Object Type mais ils sont traité comme des paramètres

```graphql
input CreateUserInput {
    name: String!
    email: String
}

type User {
    id: ID!
    name: String!
    email: String
}

type Mutation {
    createUser(input: CreateUserInput! @spread): User @create
}
```

#### interface (https://lighthouse-php.com/6/the-basics/types.html#input)

#### Union (https://lighthouse-php.com/6/the-basics/types.html#union)

### query

-

### mutation

### subscription
