Lunax Framework 1.0 Alpha
===================

Um framework de php orientado a objetos com mvc baseado em restful para pequenas aplicações Suporte PHP `5.3+`.

----------

Estrutura da aplicação
--------------------
````
project
│   index.php
│   LICENSE
│   README.md
│   robots.txt
│   TODO
│
├───lunax
|    │
|    │   .htaccess
|    │
|    ├───configs
|    │   │   database.json
|    │   │   application.json
|    │   │
|    │
|    ├───core
|    │   │   Bootstrap.class.php
|    │   │   Configs.class.php
|    │   │   Controller.class.php
|    │   │   Model.class.php
|    │   │   RequestURL.class.php
|    │   │   Template.class.php
|    │   │   Utils.class.php
|    │   │   View.class.php
|    │   │
|    │
|    ├───models
|    │   │   ...
|    │   │
|
├───app
|    │   .htaccess
|    │
|    ├───configs
|    │   |   database.json
|    │   |   application.json
|    │   |
|    │
|    ├───log
|    │   │   ...
|    │   │
|    │
|    ├───models
|    │   |   ...
|    │   │
|    │
|    ├───views
|    │   |
|    │   ├───logical
|    │   |      ...
|    │   │
|    │   ├───output
|    │   |      ...
|    │   │
|    │
|    ├───controllers
|    │   │   ...
|    │   │
|    │
|    ├───layouts
|    │   |
|    │   ├───logical
|    │   |      ...
|    │   │
|    │   ├───output
|    │   |      ...
|    │   │
````
----------

Classe  de configuração
--------------------
Classe que permite configurar a aplicação baseada nos arquivos pré configurados

````php
interface configs
{
    // Pega o valor de uma configuração
    public static function get($name);

    // Salva um novo valor em tempo de execução para determinada configuração
    public static function set($name, $value);

    // Carrega as configurações dos arquivos
    public static function load();
}
````

----------

Arquivo global e local
--------------------

* **Arquivos globais** podem ser acessados em qualquer sub-aplicação, dessa forma arquivos comuns não precisam ser repetidos.

* **Arquivos locais** pertecem *somente* a aplicação atual, esse arquivo tem prioridade em relação a um arquivo global.

----------

Configurações da aplicação
--------------------

Os dados do arquivo de configuração seguem a seguinte prioridade:
1. Configurações padrão
2. Configurações globais em `lunax/configs/application.json`
3. Configurações da aplicação em `app/configs/application.json`

A prioridade maior sobrescreve a menor!
Abaixo segue o conteúdo em application.json:

```javascript
{
  /*
   * Nome da aplicação, útil para usar em Utils::appName();
   * Dessa forma não fica repetindo o mesmo nome
   */
  "name": "Application name",

  /*
  * Quando ativo pode usar o módulo LunAjax e assim utilizando
  * uma requizição ajax será apresentado apenas o view da aplicação
  */
  "lunajax": false,

  // Nome do que será passado na URL para identificar
  // uma requisição ajax
  "lunajax_controller": "view_only",

  // A página terá um template ou será baseada nos views
  "template": true,

  // A aplicação irá usar os métodos http para
  // formar a url da ação
  "use_restful": true,

  // Permite o restful nos controllers citados
  "allow_restful": [
    "controller1",
    "controller1",
  ],

  // Bloqueia o restful nos controllers citados
  // Prioridade maior que autorização
  "denny_restful": [
    "controller3",
    "controller4",
  ],

  // Nome do controller se não encontrar
  "not_found_controller": "not_found",

  // Rota das urls amigáveis
  "router_url": {
    "controller1/action1": "variable",
    "controller1/action2": "variable1/variable2",

    // Equivalente a controller/index
    "controller1": "variable",
    "controller2": "variable1/variable2/",
    "controller3": "variable1/variable2/variable3/variable4"
  },

  // Nome do template referente ao controlelr
  "template_map": {
    "controller": "template"
  },

  // Mostra erros do Lunax se estiver abilitado (não interfere nos erros do próprio php)
  "display_errors": true,

  // É necessário autenticação para acessar o site
  "auth": true,

  // Nome da classe usada para verificar a autenticação
  "auth_class": "test_auth",

  // Nome do controller chamado para realizar a autenticação
  "auth_controller": "auth_controller_name",

  // Nome da ação utilizada para fazer a autenticação
  "auth_action": "index",

  // Nome dos controllers que não precisam estar autenticados
  "not_auth": [
    "controller_not_auth"
  ]
}
```


----------


Configurações do banco de dados
--------------------

Dados necessários para o **Model** conectar com o banco de dados mysql.
Os dados do arquivo de configuração do banco de dados seguem a seguinte prioridade:

1. Dados padrão
2. Configurações globais em "lunax/configs/database.json"
3. Configurações da aplicação em "app/configs/database.json"

````javascript
{
  // Host da hospedagem do banco de dados
  "host":     "localhost",

  // Nome do banco de dados
  "db":       "application_database",

  // Usuário para conectar com o banco de dados
  "user":     "myUsername",

  // Senha do banco de dados
  "pass":     "myPassword"
}
````


----------


RequestURL
--------------------
Classe que trabalha com as requisições ao servidor, traduzindo URL's e restful.

````php
interface RequestURL {
  // Requisição completa:
  public static function getFullRequest();

  // Raiz do servidor:
  public static function getServerRoot();

  // URL absoluta
  public static function getAbsoluteUrl();

  // URL base
  public static function getBaseUrl();

  // Requisição atual:
  public static function getRequest();

  // Nome da url padrão:
  public static function getDefaultUrlName();

  // Rota das URL's:
  public static function getRouterUrl();

  // Verifica se está usando restful por padrão:
  public static function getUseRestful();

  // Verifica se o restful está permitodo para certo controller:
  public static function getAllowRestful($name);

  // Verifica se o restful está danido para certo controller:
  public static function getDennyRestful($name);

  // Controller:
  public static function getController();

  // Nome do controller:
  public static function getControllerName();

  // Ação (action do controller):
  public static function getAction();

  // Nome da ação (action do controller):
  public static function getActionName();

  // Parâmetors da requisição:
  public static function getParameters();

  // Valor do parâmetros através do nome:
  public static function getParameter($name);
}
````

----------


Utils
--------------------

Uma classe com algumas funções que são comuns na hora do desenvolvimento das aplicações.

````php

Interface Utils
{
  // Nome da aplicação nas configurações
  public function appName();

  // Datetime atual
  public function now();

  // Converte os caracteres especiais do HTML
  public function escape($str);

  /*
   * Converte os caracteres especiais do HTML
   * preservando as quebras de linha
   */
  public function escapeLong($str);

  /* Mostra um erro se tiver abilitado nos parâmetros
   * string  $message  Mensagem que será salva
   * boolean $break    Forçar a parar a aplicação
   */
  public function error($message, $break = false);

  // Salva no log se estiver abilitado
  public function log($message);

  /*
   * Transforma o texto em uma url
   * Exemplo: public function getURL('/'); -> http://example.co
   * Quando deseja subir um diretório mostra a url real
   * Exemplo:
   * Se tiver no diretório http://example.com/mydir
   * public function getURL('../'); -> http://example.co
   */
  public function getURL($url);

  // Converte um número em valor R$
  public function parseValue($value);

  /*
   * Mostra quandos porcentos deve de desconto baseado
   * no valor anterior e no atual
   */
  public function getPercentDiscount($lastValue, $newValue);

  // Redireciona a página para a url passada
  public function location($url);


  /*
   * Para capturar o conteúdo de um arquivo com as variáveis
   * compiladas em vez de texto simples utilize o método
   * `dataIncludeFile($filename)` onde `filename` é o arquivo que
   * será incluído.
   */
  public function dataIncludeFile($filename);
}

````


----------


Controllers
--------------------

**Para aplicações não mapeadas:**

O controller é baseado na primeira parte da url, ou seja, quando se acessa `http://example.com/news/today` o controller é "news", assim, a classe será NewsController, o nome do arquivo será NewsController.class.php, se acessar a raiz do site o controller será o "index", dessa forma ficará IndexController o nome da classe e o arquivo será IndexController.class.php

O nome da ação é baseado na segunda parte da url, ou seja, quando se acessa `http://example.com/news/today`, irá acessar o controller **news** e a ação será **today**, a requisição utiliza restful e por isso o nome do método chamado será baseado no método utilizado pelo http e o incremento da palavra "action", nesse caso utilizando o método *GET* ficará `getTodayAction`, se não tiver uma segunda parte na URL será chamado o método "index", dessa forma ficará `getIndexAction`.

Para criar um controller é necessário criar um arquivo com a seguinte estrutura: **ClassName**Controller.class.php Esse arquivo deve ficar dentro da pasta *controllers* no diretório da aplicação. Esta classe pode extender `Controller` para que funcione corretamente. A estrutura da classe é a seguinte:

**Arquivo:** `app/controller/FooController.class.php`

````php

/*
 * Conteúdo de demostração em
 * IndexController.class.php
 */
class IndexController extends Controller
{
  // Normal action, not using restful
  public function indexAction()
  {
    // Code...
  }

  // HTTP GET action
  public function getIndexAction()
  {
    // Code...
  }

  // HTTP POST action
  public function postIndexAction()
  {
    // Code...
  }

  // HTTP PUT action
  public function putIndexAction()
  {
    // Code...
  }

  // HTTP DELETE action
  public function deleteIndexAction()
  {
    // Code...
  }
}

````

Antes de chamar qualquer método é chamado `beforeAction()` se tiver instanciado.

Após de chamar todos métodos necessários e mostrar o template ou o view é chamado `afterAction()` se tiver instanciado.

Para **aplicações mapeadas** nas configurações segue o que foi passado.

Para passar algum valor para o view usa o atributo `$this->view`, exemplo:

```php
public function GetBarAction()
{
  $this->view->foo = "bar";
}
```

Agora é possível acessar o atributo **foo** no view da seguinte forma:

`echo $this->foo`


----------


Models
--------------------

São classes que podem estar relacionadas com o banco de dados ou não, a classe `Model` prepara toda a conexão com o banco de dados.


#### SELECT

````php

// Colunas que serão selecionadas
$model->select($columns, ...);

// Valores podem ser repetidos sim/não
$model->distinct($val = true);

// Tabelas que serão selecionadas
$model->from($table, ...);

// Ordena de forma ascendente pela coluna
$model->orderAsc($col);

// Ordena de forma descendente pela coluna
$model->orderDesc($col);

/* Limita a seleção
 * int $count    Máximo de resultados
 * int $offset   A partir de onde começa os resultados
 */
$model->limit($count, $offset = 0);

// Seleciona o primeiro encontrado
$model->fetch();

// Seleciona todos
$model->fetchAll();

// Seleciona a linha que o primary key seja igual ao passado
$model->find($pk);
$model->find($pk1, $pk2, $pk3, ...);

````


#### UPDATE

````php

/*
 * Atualiza os valores no banco de dados
 * Exemplo:
 * $model->update(['name' => 'value', ...]);
 */
$model->update(array $dataUpdate);

// Incrementa valores a determinada coluna
$model->increment($col, $value);

// Decrementa valores a determinada coluna
$model->decrement($col, $value);

````


#### INSERT

````php

/*
 * Insere conteúdo no banco de dados
 * Exemplo:
 * $model->insert(['name' => 'value', ...]);
 */
$model->insert(array $dataInsert);

````


#### DELETE

````php

/*
 * Deleta valores no banco de dados podendo ser baseado nos primary keys
 * passados ou pelo where criado anteriormente  ou todos os registros
 * Exemplo:
 * $model->delete(10);
 * $model->delete(10, 75, 31);
 * $model->delete();
 */
$model->delete($primaryKey = null, ...);

````


#### AND e OR

````php

// Adiciona um AND
$model->where($col, $value = null);

// Adiciona um OR
$model->orWhere($col, $value = null);

````


----------


Views
--------------------

Responsável por mostrar a parte onde o usuário terá contato, o view mostra o conteúdo da página baseado no `controller/action` se tiver mostrando um template ele aparece quando chama o método `$this->content()`.

Os views são divididos em duas partes:
1. Arquivos de lógica (não obrigatório)
2. Arquivos de saída

Os arquivos de lógica ficam em `/views/logical/` e utiliza o formato do arquivo é *php*, são chamados antes dos arquivos de saída, com eles é possível gerar funções que serão usadas na saída.

Os arquivos de saída ficam em `/views/output/` e o utiliza formato do arquivo é *phtml*, são onde fica o resultado que será apresentado, utiliza **SOMENTE** as chamadas de funções, código em HTML, resultado em json ou algo parecido.

Exemplo de um view que possui arquivo de lógica e um output, chamado a partir de `news/today`:

````
views
│
├───logical
|    |
|    ├───news
|    │   |   today.php
|    │   |
│
├───output
|    |
|    ├───news
|    │   |   today.phtml
|    │   |
````

----------


Template
--------------------

Um template é uma parte da aplicação que será comum em diveresos momentos, para não haver a repetição de código cria um template.
Para mostrar o `view` da página atual utiliza o método:

````php
<?php $this->content(); ?>
````

Para criar um site onePage utiliza o método de mesmo nome se não tiver um template base para cada página faz da seguinte forma:

````php
$this->onePage([
  'page-1',
  'page-2',
  'page-3',
  'page-4',
  'page-5',
  ...
]);
````

Caso haja algum template para cada página (usando como exemplo o arquivo `layout/sub_template.page.phtml`):

````php
$this->onePage('sub_template', [
  'page-1',
  'page-2',
  'page-3',
  'page-4',
  'page-5',
  ...
]);
````
Para que o `sub_template` mostre o conteúdo da página utiliza o método `$this->content();`.
Os arquivos do onePage ficam diretamente no diretório `views`, nesse caso seria `views/page-1.phtml`.
