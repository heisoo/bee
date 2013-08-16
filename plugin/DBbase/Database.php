<?php

include('pdo.php');


class DB_active_record extends DB_base {

    private $table  = '';
    private $sql    = '';
    private $column = '*';
    private $cond   = '';
    private $order  = '';
    private $group  = '';
    private $having = '';
    private $debug  = false;

    public function __construct($params = '') {/*{{{*/
        parent::__construct($params);
    }/*}}}*/

    public function table($name = 'name') {/*{{{*/
        $this->clear();
        $this->table = $name;
        return $this;
    }/*}}}*/

    public function getList($getField=false, $opt = 'array_assoc') {/*{{{*/
        $this->sql = "SELECT {$this->column} FROM {$this->table} ";
        empty($this->cond)      || ( $this->sql .= $this->cond );
        empty($this->group)     || ( $this->sql .= $this->group );
        empty($this->having)    || ( $this->sql .= $this->having );
        empty($this->order)     || ( $this->sql .= $this->order );

        $res = $this->query($this->sql, $opt);
        if ($getField && $opt == 'array_assoc') { $res = $this->getField($res); }

        return $res;
    }/*}}}*/

    public function getNum($num = 1, $start = 0, $getField = false, $opt = 'array_assoc') {/*{{{*/
        if ((int)$num <= 0) {
            return array();
        }

        $this->sql = "SELECT {$this->column} FROM {$this->table} ";
        empty($this->cond)      || ( $this->sql .= $this->cond );
        empty($this->group)     || ( $this->sql .= $this->group );
        empty($this->having)    || ( $this->sql .= $this->having );
        empty($this->order)     || ( $this->sql .= $this->order );
        $this->sql .= " LIMIT {$start}, {$num}";

        $res = $this->query($this->sql, $opt);
        if ($getField && $opt == 'array_assoc') { $res = $this->getField($res); }

        return $res;
    }/*}}}*/

    public function getCount() {/*{{{*/
        $this->sql = "SELECT count(*) as count FROM {$this->table} ";
        empty($this->cond)      || ( $this->sql .= $this->cond );
        empty($this->group)     || ( $this->sql .= $this->group );
        empty($this->having)    || ( $this->sql .= $this->having );
        empty($this->order)     || ( $this->sql .= $this->order );

        $count = current($this->query($this->sql, 'object'))->count;

        return $count;
    }/*}}}*/

    public function getItem($id_name, $id, $opt = 'array_assoc') {/*{{{*/
        if (empty($this->table)) {
            echo 'table name is empty';
            return 0;
        }

        if (!is_numeric($id)) {
            echo 'id need numeric';
            return 0;
        }

        $this->sql = "SELECT {$this->column} FROM {$this->table} WHERE `{$id_name}`={$id}";
        $item = current($this->query($this->sql, $opt));

        return $item;
    }/*}}}*/

    public function select($params = '*') {/*{{{*/
        if (is_string($params)) {
            if ($this->column == '*') {
                $this->column = $params;
            } else {
                $this->column .= ', '.$params;
            }
        } else {
            $this->column = implode(',', $params);
        }

        return $this;
    }/*}}}*/

    public function where($params = '') {/*{{{*/
        if (empty($params)) return $this;

        if (func_num_args() >= 2) {
            $args = array_slice(func_get_args(), 1);
            foreach($args as $arg) {
                $arg = $this->escape($arg);
                $params = $this->str_replace_once('?', $arg, $params);
            }
        }

        $statement = 'AND';
        if (in_array(strtoupper(end(func_get_args())), array('AND', 'OR'))) {
            $statement = end(func_get_args());
        }

        if (empty($this->cond)) {
            $this->cond = "WHERE $params";
        } else {
            $this->cond .= " $statement $params";
        }

        return $this;
    }/*}}}*/

    public function debug($l="\n", $r="\n", $break=false) {/*{{{*/
        $this->debug = array(
            'break' =>  $break,
            'l'     =>  $l,
            'r'     =>  $r,
        );
        return $this;
    }/*}}}*/

    public function order($fields, $order = '') {/*{{{*/

        if (empty($this->order)) {
            $this->order = " ORDER BY {$fields} {$order} ";
        } else {
            $this->order .= ", {$fields} {$order} ";
        }

        return $this;
    }/*}}}*/

    public function group($fields) {/*{{{*/
        if (is_string($fields)) {
            $this->group = " GROUP BY {$fields} ";
        }

        return $this;
    }/*}}}*/

    public function having($params) {/*{{{*/
        if (is_string($params)) {
            $this->having = " having {$params} ";
        }

        return $this;
    }/*}}}*/

    public function getFirst() {/*{{{*/
        return current($this->getNum(1));
    }/*}}}*/

    public function insert($params = '', $replace_flag = false) {/*{{{*/
        if (empty($this->table)) {
            echo 'table name is empty';
            return 0;
        }

        if ( ! is_array($params)) return 0;

        $fields = array();
        $values = array();
        foreach($params as $k=>$v) {
            if ( is_numeric($v) || is_string($v) ) {
                $fields[] = $k;
                $values[] = $v;
            }
        }

        $fields = implode('`,`', $fields);

        foreach($values as $k=>$v) {
            $values[$k] = $this->escape($v);
        }
        $values = implode('","', $values);
        $statement = $replace_flag ? 'REPLACE' : 'INSERT';
        $this->sql = "{$statement} INTO {$this->table} (`$fields`) VALUES (\"$values\")";

        return $this->exec($this->sql);
    }/*}}}*/

    public function batch_insert($params, $replace_flag = false) {/*{{{*/
        if ( ! is_array($params)) {
            return false;
        }

        $res = 0;
        foreach ( $params as $Item ) {
            $res += $this->insert($Item, $replace_flag);
        }

        return $res;
    }/*}}}*/
    
    public function update($params = '') {/*{{{*/
        if (empty($this->table)) {
            echo 'table name is empty';
            return 0;
        }

        if ( ! is_array($params) || empty($params)) return 0;

        $sets = array();
        foreach($params as $key=>$value) {
            //if ( is_numeric($value)) continue;

            if ( ! is_array($value)) {
                $value = $this->escape($value);
                $sets[] = <<<EOF
                `{$key}` = "{$value}"
EOF;
            } else {
                if (count($value, COUNT_RECURSIVE) === 1) {
                    $sets[] = "`{$key}` = " . current($value);
                }
            }
        }
        $sets = implode(" , ", $sets);

        $this->sql = "UPDATE {$this->table} SET {$sets} ";
        empty($this->cond)      || ( $this->sql .= $this->cond );
        
        $res = $this->exec($this->sql);

        return $res;
    }/*}}}*/

    public function save($params = '', $primary_key = '') {/*{{{*/
        if (empty($this->table)) {
            echo 'table name is empty';
            return 0;
        }

        if ( ! is_array($params) || empty($primary_key) || empty($params[$primary_key]) ) {
            return 0;
        }

        $table = $this->table;
        $existence = $this->where("$primary_key=\"{$params[$primary_key]}\"")->getCount();
        $this->table = $table;

        $affected_rows = 0;
        if ( $existence == 0 ) {
            $affected_rows = $this->insert($params);
        } else if ( $existence == 1 ) {
            $affected_rows = $this
                ->where("$primary_key=\"{$params[$primary_key]}\"")
                ->update($params);
        }

        return $affected_rows;
    }/*}}}*/

    public function delete() {/*{{{*/
        if (empty($this->table)) {
            echo 'table name is empty';
            return 0;
        }

        $this->sql = "DELETE FROM {$this->table} ";
        empty($this->cond)      || ( $this->sql .= $this->cond );

        return $this->exec($this->sql);

    }/*}}}*/

    private function getField($res, $field=''){/*{{{*/

        $data = array();
        if (empty($res)) return $data;

        foreach ($res as $v){
            if( isset($v[$field]) ) {
                $data[] = $v[$field];
            } else {
                $data[] = current($v);
            }
        }
        return $data;
    }/*}}}*/

    private function str_replace_once($needle, $replace, $haystack) {
        $pos = strpos($haystack, $needle);
        if ($pos === false) {
            return $haystack;
        }
        return substr_replace($haystack, $replace, $pos, strlen($needle));
    }

    public function query($sql = '', $opt = 'array_assoc' , $hash = array()) {/*{{{*/

        if ($d = $this->debug) {
            echo "{$d['l']} " . $this->sql . " {$d['r']}";
            if ($d['break']) return;
        }

        return parent::query($sql, $opt, $hash);
    }

    public function exec($sql = '') {/*{{{*/

        if ($d = $this->debug) {
            echo "{$d['l']} " . $this->sql . " {$d['r']}";
            if ($d['break']) return;
        }

        return parent::exec($sql);
    }

    private function clear() {/*{{{*/
        $this->table    = '';
        $this->column   = '*';
        $this->cond     = '';
        $this->group    = '';
        $this->having   = '';
        $this->order    = '';
        $this->debug    = false;
    }/*}}}*/

}


?>
