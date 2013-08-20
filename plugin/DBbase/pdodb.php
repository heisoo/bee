<?php

class PdoDb {

    protected $hostname = '';
    protected $username = '';
    protected $password = '';
    protected $database = '';
    protected $conn_id = FALSE;
    protected $result_id = FALSE;   
    protected $affected_rows = 0;
    protected $error_no = FALSE;
    protected $error = FALSE;
    protected $char_set = 'UTF8';
    protected $db_debug = FALSE;

    function __construct($params = '') {
        $this->initialize($params);
    }

    /**
     * initialize configuration
     *
     * @access  public
     * @param   array, boolean
     */
    protected function initialize($params) {

        if (is_array($params) AND ! empty($params)) {
            $i = 0;
            foreach ($params as $key => $val) {
                $this->$key = ( ! isset($params[$i])) ? $val : $params[$i];
                $i++;
            }
        }
        $auto_connection = isset($params['auto_connection']) ? $params['auto_connection'] : TRUE;
        $auto_connection AND $this->connection($params['options']);
    }

    /**
     * contection
     *
     * @access  public
     * @return  boolean
     */

    protected function connection($options) {
        empty($options) and $options = array(
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
            PDO::ATTR_PERSISTENT => true,
        ); 

        try {
            $this->conn_id= new PDO('mysql:host='.$this->hostname.';dbname=ticket', $this->username, $this->password ,$options);
            $this->conn_id->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            echo 'ERROR: ' . $e->getMessage();

        }
        return TRUE;
    }


    /**
     * query select
     *
     * @access  public
     * @param   sql string and option ( array_num, array_assoc, aray_both, object )
     * @return  array
     */

    protected function query($sql = '', $opt = 'array_assoc' , $hash = array()) {

        ($this->conn_id === FALSE) AND $this->connection();

        if (empty($sql)) return FALSE;
        $result_id = $this->conn_id->query($sql);
        $res = array();
        $i=0;                                   

        if($opt != 'assign_key'){
            $mode = 'PDO::'.strtoupper($opt);
            while($row = $result_id ->fetch($$mode)){   
                array_push($res, $row);
            }
        }else{

            while($row = $result_id->fetch(PDO::FETCH_ASSOC)){   
                $res_name='res';   
                $res[$row[$hash]]=$row;                               

                $i++;                              
            }
        }
        return $res;
    }

    public function exec($data = '') {

        ($this->conn_id === FALSE) AND $this->connection();

        if( !is_array($data) ){

            $this->conn_id->exec($data);

        }else{

            // you have to fill something
        }

    }

    /**
     * explain select
     *
     * @access  public
     * @param   sql string and option
     * @return  array
     */

    public function explain($sql, $opt = 'array_assoc') {

        ($this->conn_id === FALSE) AND $this->connection();

        if (empty($sql)) return FALSE;

        $sql = "EXPLAIN $sql";
        return current($this->query($sql, $opt));
    }

    /**
     * get last_insert_id
     *
     * @access  public
     * @return  integer
     */

    // no test 大错特错
    public function last_insert_id() {
        ($this->conn_id === FALSE) AND $this->connection();
        return $this->conn_id->lastInsertId();
    }

    public function prepare($sql){ 
        ($this->conn_id === FALSE) AND $this->connection();

        $args = func_get_args();
        array_shift($args); 

        $reponse = $this->conn_id->prepare($sql);
        $reponse->execute($args);
        return $reponse;
    }

    /**
     * escape string
     *
     * @access  public
     * @return  integer
     */

    public function escape($str) {
        ($this->conn_id === FALSE) AND $this->connection();
        return $this->conn_id->quote($str);
    }

    /**
     * get affected_rows
     *
     * @access  public
     * @return  integer
     */

    protected function affected_rows() {
        return $this->conn_id->rowCount();
    }

    protected function close() {
         $this->conn_id  = null;
    }
}
?>
