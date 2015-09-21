Lunax Framework Alpha 2.0
===================

Um framework de php orientado a objetos com mvc baseado em restful para pequenas aplicações Suporte PHP `5.3+`.

----------

Arquivo global e local
--------------------

* **Arquivos globais** podem ser acessados em qualquer sub-aplicação, dessa forma arquivos comuns não precisam ser repetidos.

* **Arquivos locais** pertecem *somente* a aplicação atual, esse arquivo tem prioridade em relação a um arquivo global.

----------

Configurações da aplicação
--------------------

Os dados do arquivo de configuração seguem a seguinte prioridade:
1. Dados padrão
2. Configurações globais em `lunax/configs/application.json`
3. Configurações da aplicação em `app/configs/application.json`

A prioridade maior sobrescreve a menor!
Abaixo segue o conteúdo em application.json:

```javascript
{
  /*
   * Nome da aplicação, útil para usar em Utils::getName();
   * Dessa forma não fica repetindo o mesmo nome
   */
  "name": "Application name",

  // A página terá um template ou será baseada nos views
  "template": true,

  // A aplicação irá usar os métodos http para formar a url
  "use_restful": true,

  // Nome do controller se não encontrar
  "not_found_controller": "not_found",

  // Mapeamento de URL
  "url_get_map": {
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
  "host":    "localhost",

  // Nome do banco de dados
  "db":      "application_database",

  // Usuário para conectar com o banco de dados
  "user":    "myUsername",

  // Senha do banco de dados
  "pass":    "myPassword"
}
````


----------


Utils
--------------------

Uma classe com algumas funções que são comuns na hora do desenvolvimento das aplicações.

````php

// Nome da aplicação nas configurações
Utils::appName();

// Datetime atual
Utils::now();

// Converte os caracteres especiais do HTML
Utils::escape($str);

/*
 * Converte os caracteres especiais do HTML
 * preservando as quebras de linha
 */
Utils::escapeLong($str);

// Mostra um erro se tiver abilitado nos parâmetros
Utils::error($message);

/*
 * Transforma o texto em uma url
 * Exemplo: Utils::getURL('/'); -> http://example.com
 * Quando deseja subir um diretório mostra a url real
 * Exemplo:
 * Se tiver no diretório http://example.com/mydir
 * Utils::getURL('../'); -> http://example.com
 */
Utils::getURL($url);

// Converte um número em valor R$
Utils::parseValue($value);

/*
 * Mostra quandos porcentos deve de desconto baseado
 * no valor anterior e no atual
 */
Utils::getPercentDiscount($lastValue, $newValue);

// Redireciona a página para a url passada
Utils::location($url);


/*
 * Para capturar o conteúdo de um arquivo com as variáveis
 * compiladas em vez de texto simples utilize o método
 * `dataIncludeFile($filename)` onde `filename` é o arquivo que
 * será incluído.
 */
Utils::dataIncludeFile('./foo.phtml');

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
  // HTTP GET action...
  public function getIndexAction()
  {
    // Code...
  }

  // HTTP POST action...
  public function postIndexAction()
  {
    // Code...
  }

  // HTTP PUT action...
  public function putIndexAction()
  {
    // Code...
  }

  // HTTP DELETE action...
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
$model->select(...$columns);

// Valores podem ser repetidos sim/não
$model->distinct($val = true);

// Tabelas que serão selecionadas
$model->from(...$table);

// Ordena de forma ascendente pela coluna
$model->orderAsc($col);

// Ordena de forma descendente pela coluna
$model->orderDesc($col);

// Limita a seleção
$model->limit($count, $offset = 0);

// Seleciona o primeiro encontrado
$model->fetch();

// Seleciona todos
$model->fetchAll();

// Seleciona a linha que o primarykey seja igual ao passado
$model->find($pk);

````


#### UPDATE

````php

/*
 * Atualiza os valores no banco de dados
 * Exemplo:
 * $model->update([
 *     'name' => 'value'
 * ]);
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
 * $model->insert([
 *     'name' => 'value'
 * ]);
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
$model->delete(...$primaryKey = null);

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

Responsável por mostrar a parte onde o usuário terá contato, ou seja, essa parte designarar o usuário para o controller correto e passará os valores necessários para funcionar.
O view mostra o conteúdo da página baseado no `controller/action` se tiver mostrando um template ele aparece quando chama o método `$this->content()`. Aqui está o diretório de um view de baseado no controller **news** e na ação **today**: `views/news/today.phtml`.


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
  'page 1',
  'page 2',
  'page 3',
  'page 4',
  'page 5'
]);
````

Caso haja algum template para a página, usando como exemplo o arquivo `layout/sub_template.page.phtml`:

````php
$this->onePage('sub_template', [
  'page-1',
  'page-2',
  'page-3',
  'page-4',
  'page-5'
]);
````
Para que o `sub_template` mostre o conteúdo da página utiliza o seguinte método `$this->content();`.
Os arquivos do onePage ficam diretamente no diretório `views`, nesse caso seria `views/page-1.phtml`.
