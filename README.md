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

## récupération des données

### Collection d'element

```graphql graphql/schema.graphql
type Query {
    users: [User] @all //récupération de toute les utilisateurs//mais un ou plusieurs utilisateurs peut être null dans la collection (peut etre [null, {...user1}]) et que la reponse elle même aussi peut être null (null)
    //si Users: [User]! @all //la reponse ne doit pas être null(reponse null) mais les elements dans la collection peuvent l'etre (peut être [null, {...user1}])
    //si Users: [User!]! @all //la response doit strictement être une collection de type User(la reponse ne doit pas être null ni contenir des elements null)
}

type User { //here we can define all field we want to get
    id!: String //! doit contenir une valeur sinon erreur
    email!: String //! doit contenir une valeur sinon erreur
    email_verified_at: String //n'est pas obligatoire
}
```

### Un element

-   `@find` permet de preciser qu'on ne veut récupérer qu'un element en utilisant le paramètre(ici id: ID @eq)
-   `@eq` directive opérateur equal

```graphql
type Query {
    users: [User] @all
    user(id: ID @eq): User @find
}

enum numEnum {
    ONE @enum(value: 1)
    TWO @enum(value: 2)
    THREE @enum(value: 3)
}

type User {
    id: String
    email: String
    num: numEnum
}
```

## pagination

-   utilisation de la directive `@paginate`
-   basiquement, voici l'usage:

```graphiql
type Query {
    users: [User] @paginate
    user(num: String @eq): User @find
}
```

requete: first est utiliser pour definir le nombre de résultat et page pour la page courant

```graphql
{
  users(first: 3, page: 5){#first and page parameters are required
    data {#required
      id
    },
    paginatorInfo{#optional response, paginate info we can get
        "Number of items in the current page."
        count # type Int!

        "Index of the current page."
        currentPage # type Int!

        "Index of the first item in the current page."
        firstItem # type Int

        "Are there more pages after this one?"
        hasMorePages # type Boolean!

        "Index of the last item in the current page."
        lastItem # type Int

        "Index of the last available page."
        lastPage # type Int!

        "Number of items per page."
        perPage # type Int!

        "Number of total available items."
        total # type Int!
    }
  }
}
```

## Mutation: creation d'utilisateur

```.graphql
//.....
type Mutation {
    createUser(
        name: String!
        num: numEnum!
        email: String!
        password: String!
    ): User! @create
}
//.....
```

```request
mutation {
  createUser(
    name: "user",
    num: ONE,
    email: "user@host.com",
    password: "password"
  ) {
    name
    num
    email
    password
  }
}

```

## Mutation: mettre à jour

-   meme que creation mais
    -   utiliser `@udpate` à place de `@create`
    -   id est requis
    -   mettre les autres champs optionnel
    -   utilise updateUser pour user

```graphql
type Mutation {
    updateUser(
        id: ID!
        name: String
        num: numEnum
        email: String
        password: String
    ): User! @update
}
```

```request
mutation {
  updateUser(
    id: 22
    name: "Twenty two"
  ) {
    id
    name
    num
  }
}
```

## Mutation: suppression

-   meme que update mais
    -   utilise `@delete` au lieu de `@update`
    -   préciser les elements à supprimer dans le paramètre

### supprimer un utilisateur

```graphql
type Mutation {
    deleteUser(id: ID! @whereKey): User! @delete
}
```

```request
deleteUser(id: 1) {
    name
}
```

### supprimer des utilisateurs

```graphql
type Mutation {
    deleteUsers(ids: [ID!] @whereKey): [User!]! @delete
}
```

```request
deleteUsers(ids: [2,3]) {
    name
}
```

## recupération des elements en relation avec User

-   utilisation des directives de relation(`@hasMany`, `@belongsTo`)

```php
//app/Model/Post.php
//...
    public function user()
    {
        return $this->belongsTo(User::class);
    }
//...
```

```php
//app/Model/User.php
//...
    public function posts()
    {
        return $this->hasMany(Post::class);
    }
//...
```

```graphql
type User {
    id: String
    name: String
    num: numEnum
    email: String
    posts: [Post!] @hasMany
}

type Post {
    user_id: Int
    title: String
    content: String
    user: User @belongsTo
}
```

```request
{
  users(first: 5, page: 5){
    data {
      id
      name
      posts {
        title
        content
      }
    },
    paginatorInfo{
      firstItem
      currentPage
      hasMorePages
    }
  }
}
```

## Règle de validation

-   utilisation de @rules pour les paramètres

```graphql
type Mutation {
    updateUser(
        id: ID!
        name: String @rules(apply: ["min:3"])
        num: numEnum @rules(apply: ["in:1,2,3,4"])
        email: String @rules(apply: ["email"])
        password: String @rules(apply: ["min:8"])
    ): User! @update
}
```
