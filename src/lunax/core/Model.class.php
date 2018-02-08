<?php

abstract class Model
{
    protected $pKey = 'id';

    # Vars SELECT
    private $_selectCols        = '*';
    private $_selectDistinct    = false;
    private $_selectLimitCount  = 0;
    private $_selectLimitOffset = 0;
    private $_selectOrderBy     = [];
    private $_selectOrderType;
    private $_selectQuery;
    private $_selectFrom;

    # Vars WHERE
    private $dataWhere  = [];
    private $paramWhere = [];
    private $param      = [];

    # Reset defaults values
    private function reset()
    {
        $_selectCols        = '*';
        $_selectDistinct    = false;
        $_selectLimitCount  = 0;
        $_selectLimitOffset = 0;
        $_selectOrderType   = null;
        $_selectQuery       = null;
        $_selectFrom        = null;
        $this->dataWhere    = [];
        $this->paramWhere   = [];
        $this->param        = [];
    }

    # Retorna as partes da query
    private function getQueryParts($data = [])
    {
        # Ajustes básicos
        $strColumns = '()';
        $strValues = '';

        $columns = [];

        $numItems = count($data);
        $i = 0;

        foreach ($data as $column => $value) {
            array_push($columns, "`$this->table`.`$column`");
            array_push($this->param, $value);
        }

        return $columns;

    }

    private function makeWhere()
    {
        if (count($this->dataWhere)) {
            $baseRegex = '/^([\w<>+\-]+)\s*([+\-*\/><!=\w]+)\s*';
            $strWhere = '';

            # Salva as colunas que serão alteradas
            foreach ($this->dataWhere as $i => $where) {
                # Se for o primeiro elemento não adiciona 'AND' ou 'OR'
                if ($i > 0) $strWhere .= (' ' . $where[0] . ' ');

                # Conteúdo do where
                $contWhere = trim($where[1]);

                # foo LIKE ? para `foo` LIKE ?
                $contWhere = preg_replace(
                    $baseRegex . "(\?|[+\-\s])$/",
                    '(`$1` $2 $3)',
                    $contWhere
                );

                # foo LIKE bar para `foo` like `bar`
                $contWhere = preg_replace(
                    $baseRegex . "([\w\"']+)$/",
                    '(`$1` $2 `$3`)',
                    $contWhere
                );

                # Converte foo.bar em `foo`.`bar`
                $strWhere .= preg_replace(
                    "/(\w+)\.(\w+)/",
                    '`$1`.`$2`',
                    $contWhere
                );
            }

            # Insere os valores no fom dos parâmetros
            foreach ($this->paramWhere as $value) {
                array_push($this->param, $value);
            }

            return ' WHERE ' . (
                count($this->dataWhere) > 1 ?
                "($strWhere)" : $strWhere
            );
        } else {
            return " WHERE 1";
        }
    }

    private function makeDestinct()
    {
        return $this->_selectDistinct ? 'DISTINCT ' : '';
    }

    private function makeOrderBy()
    {
        if (count($this->_selectOrderBy)) {

            foreach ($this->_selectOrderBy as $i => $order) {
                $this->_selectOrderBy[$i] = preg_replace(
                    '/^([^*]\w*)$/',
                    '`$1`',
                    trim($order)
                );
            }

            $orderStr = implode($this->_selectOrderBy , ', ');

            $this->_selectOrderType = !empty($this->_selectOrderType) ?
                $this->_selectOrderType : 'ASC';

            return " ORDER BY $orderStr $this->_selectOrderType";
        } else return '';
    }

    private function makeLimit()
    {
        if (!is_null($this->_selectLimitCount)) {
            return " LIMIT $this->_selectLimitOffset, $this->_selectLimitCount";
        } else return '';
    }

    private function makeFrom()
    {
        if (empty($this->_selectFrom)) {
            $this->from($this->table);
        }
        return " FROM $this->_selectFrom";
    }

    private function prepareSelect()
    {
        $query = 'SELECT ' .
            $this->makeDestinct() .
            $this->_selectCols .
            $this->makeFrom().
            $this->makeWhere() .
            $this->makeOrderBy() .
            $this->makeLimit();
        return  $query;
    }

    public function where($col, $value = null)
    {
        array_push($this->dataWhere, ['AND', $col]);
        if (!is_null($value)) array_push($this->paramWhere, $value);
        return $this;
    }

    public function orWhere($col, $value = null)
    {
        array_push($this->dataWhere, ['OR', $col]);
        if (!is_null($value)) array_push($this->paramWhere, $value);
        return $this;
    }

    # $lastId = $this->insert(['foo' => 'bar']);
    public function insert(array $dataInsert)
    {
        # Pega os dados que serão inseridos
        $insert = $this->getQueryParts($dataInsert);

        # Ajustes básicos
        $strColumns = "()";
        $strValues = "";

        $numItems = count($dataInsert);
        $i = 0;

        foreach ($dataInsert as $column => $value) {

            $strValues .= '?';

            if(++$i < $numItems) {
                $strValues .= ',';
            }
        }

        $strColumns =  implode($insert, ', ');
        $this->runQuery(
            "INSERT INTO `$this->table` ($strColumns) VALUES ($strValues)"
        );
        return $this->__PDO->lastInsertId();
    }

    # $this->update(['foo' => 'bar']);
    public function update(array $dataUpdate)
    {
        # Pega os dados que serão inseridos
        $update = $this->getQueryParts($dataUpdate);

        # Ajustes básicos
        $numItems = count($dataUpdate);
        $strUpdate = '';
        $i = 0;

        # Mixando os dados que serão alterados
        foreach ($update as $col) {

            $strUpdate .= $col . ' = ?';

            if(++$i < $numItems) {
                $strUpdate .= ',';
            }
        }

        $this->runQuery(
            "UPDATE `$this->table` SET $strUpdate " . $this->makeWhere()
        );
    }

    public function increment($col, $value)
    {
        $this->runQuery(
            "UPDATE `$this->table` SET `$col` = `$col` + $value" .
            $this->makeWhere()
        );
    }

    public function decrement($col, $value)
    {
        $this->runQuery(
            "UPDATE `$this->table` SET `$col` = `$col` - $value" .
            $this->makeWhere()
        );
    }

    # $this->delete(1);
    public function delete($primaryKey = null)
    {
        if (!is_null($primaryKey)) {
            $this->where("$this->pKey = ?", $primaryKey);
        }
        $this->runQuery("DELETE FROM `$this->table`" . $this->makeWhere());
    }

    public function select($columns)
    {
        $columns = func_get_args();
        foreach ($columns as $i => $data) {

            # a -> `a` ignorando o * de todos
            $columns[$i] = preg_replace(
                '/^([^*]\w*)$/',
                '`$1`',
                trim($data));

            # a.a -> `a`.`a`
            $columns[$i] = preg_replace(
                '/(\w*)\.(\w*)/',
                '`$1`.`$2`',
                $columns[$i]);

            # a(b) -> a(`b`)
            $columns[$i] = preg_replace(
                '/(\b[^()]+)\((.*)\)/',
                '$1(`$2`)',
                $columns[$i]
            );

            # a as a -> `a` as `a`
            # a a -> `a` `a`
            # a + a -> `a` + `a`
            $columns[$i] = preg_replace(
                '/^\s*(\w+)(?:\s*([-+*\/]|AS)\s*((?:\s[-+])?\w+)\s*)+$/i',
                '`$1` $2 `$3`',
                $columns[$i]
            );

        }
        $this->_selectCols = implode($columns, ', ');
    }

    public function distinct($val = true)
    {
        $this->_selectDistinct = $val;
    }

    public function from($table)
    {
        if (gettype($table) != 'array') {
            $table = func_get_args();
        }

        foreach ($table as $i => $tb) {
            $table[$i] = preg_replace(
                '/^(\w*)$/',
                '`$1`',
                trim($tb)
            );

            $table[$i] = preg_replace(
                '/^(\w*)\s(\w*)$/',
                '`$1` `$2`',
                $table[$i]
            );
        }

        $this->_selectFrom = implode($table, ', ');
    }

    public function orderAsc($col)
    {
        $this->_selectOrderBy = func_get_args();
        $this->_selectOrderType = 'ASC';
    }

    public function orderDesc($col)
    {
        $this->_selectOrderBy = func_get_args();
        $this->_selectOrderType = 'DESC';
    }

    public function limit($count, $offset = 0)
    {
        $this->_selectLimitCount = $count;
        $this->_selectLimitOffset = $offset;
    }

    public function fetch()
    {
        $this->limit(1);
        $exec = $this->runQuery($this->prepareSelect());
        return $exec->fetch(PDO::FETCH_OBJ);
    }

    public function fetchAll()
    {
        if ($this->_selectLimitCount == 0) {
            $this->limit(null);
        }
        $exec = $this->runQuery($this->prepareSelect());
        return $exec->fetchAll(PDO::FETCH_OBJ);
    }

    public function find($pk)
    {
        $args = func_get_args();
        $rtn = [];

        foreach ($args as $pk) {
            $this->where("$this->pKey = ?", $pk);
            array_push($rtn, $this->fetch());
        }

        # Verifica se é para retornar tudo ou só o primeiro
        return func_num_args() > 1 ? $rtn : $rtn[0];
    }

    private function runQuery($query)
    {
        $res = DBConnect::run($query, $this->param);
        $this->reset();
        return $res;
    }

    function __construct()
    {
        DBConnect::connect();
    }
}
