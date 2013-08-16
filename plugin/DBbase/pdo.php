<?php

class DB_base {

    protected $dbdriver = 'mysqli';
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

    // --------------------------------------------------------------------

    protected function __construct($params = '') {/*{{{*/
        $this->initialize($params);
    }/*}}}*/

    /**
     * initialize configuration
     *
     * @access  public
     * @param   array, boolean
     */

    protected function initialize($params) {/*{{{*/

        if (is_array($params) AND ! empty($params)) {

            $i = 0;
            foreach ($params as $key => $val) {
                $this->$key = ( ! isset($params[$i])) ? $val : $params[$i];
                $i++;
            }
        }

        $auto_connection = isset($params['auto_connection']) ? $params['auto_connection'] : TRUE;

        $auto_connection AND $this->connection();

    }/*}}}*/

    /**
     * contection
     *
     * @access  public
     * @return  boolean
     */

    protected function connection() {/*{{{*/

        ($this->conn_id === FALSE) AND 
            $this->conn_id= new mysqli($this->hostname, $this->username, $this->password);
            //new PDO(DB_DSN, DB_USER, DB_PASS); 
            //new PDO(DB_TYPE.':host='.DB_HOST.';dbname='.DB_NAME, DB_USER, DB_PASSWD);

        if ( ! $this->conn_id) {
            echo 'Unable to connect to the database';
            return FALSE;
        }

        $this->exec('SET NAMES '.$this->char_set);

        !empty($this->database) AND $this->select_db($this->database);

        return TRUE;

    }/*}}}*/

    /**
     * select database
     *
     * @access  public
     * @param   string
     * @return  boolean
     */

    protected function select_db($db_name) {/*{{{*/
        if ( ! mysqli_select_db($this->conn_id, $db_name)) {
            echo 'Unable to select database ' . $db_name;
            return FALSE;
        }        
    }/*}}}*/

    /**
     * query select
     *
     * @access  public
     * @param   sql string and option ( array_num, array_assoc, array_both, object )
     * @return  array
     */

    protected function query($sql = '', $opt = 'array_assoc' , $hash = array()) {/*{{{*/

        ($this->conn_id === FALSE) AND $this->connection();

        if (empty($sql)) return FALSE;

        $this->result_id = mysqli_query($this->conn_id, $sql);

        if ( ! $this->result_id) {
            $this->error_no = mysqli_errno($this->conn_id);
            $this->error = mysqli_error($this->conn_id);

            if ($this->db_debug) {
                echo $this->error;
            }

            return array();
        }

        $arr_opts = array (
            'array_num'     =>  array( 'func' => 'fetch_array',  'paras' => MYSQLI_NUM),
            'array_assoc'   =>  array( 'func' => 'fetch_array',  'paras' => MYSQLI_ASSOC),
            'array_both'    =>  array( 'func' => 'fetch_array',  'paras' => MYSQLI_BOTH),
            'object'        =>  array( 'func' => 'fetch_object', 'paras' => ''),
            'single'        =>  array( 'func' => 'fetch_single', 'paras' => MYSQLI_ASSOC),
            'hash'          =>  array( 'func' => 'fetch_hash'  , 'paras' => $hash ),
            'assign_key'    =>  array( 'func' => 'fetch_assign_key'  , 'paras' => $hash ),
        );

        $call_fetch_fun = isset($arr_opts[$opt]) ? $arr_opts[$opt] : $arr_opts['array_num'];

        return $this->$call_fetch_fun['func']($call_fetch_fun['paras']); 
    }/*}}}*/

    /**
     * exec insert, update, delete etc.
     *
     * @access  public
     * @param   string
     * @return  integer
     */

    public function exec($sql = '') {/*{{{*/

        ($this->conn_id === FALSE) AND $this->connection();

        if (empty($sql)) return FALSE;

        if ( ! mysqli_query($this->conn_id, $sql)) {
            $this->error_no = mysqli_errno($this->conn_id);
            $this->error = mysqli_error($this->conn_id);

            if ($this->db_debug) {
                echo $this->error;
            }

            return $this->affected_rows;
        }

        $this->affected_rows = mysqli_affected_rows($this->conn_id);
        return $this->affected_rows;
    }/*}}}*/

    /**
     * explain select
     *
     * @access  public
     * @param   sql string and option
     * @return  array
     */

    public function explain($sql, $opt = 'array_assoc') {/*{{{*/

        ($this->conn_id === FALSE) AND $this->connection();

        if (empty($sql)) return FALSE;

        $sql = "EXPLAIN $sql";
        return current($this->query($sql, $opt));
    }/*}}}*/

    /**
     * get last_insert_id
     *
     * @access  public
     * @return  integer
     */

    public function last_insert_id() {/*{{{*/

        ($this->conn_id === FALSE) AND $this->connection();

        $result = mysqli_query($this->conn_id, "select LAST_INSERT_ID() as `last_insert_id`");
        $row = mysqli_fetch_object($result);
        return $row->last_insert_id;
    }/*}}}*/

     /**
     * escape string
     *
     * @access  public
     * @return  integer
     */

    public function escape($str) {/*{{{*/

        ($this->conn_id === FALSE) AND $this->connection();
        return mysqli_real_escape_string($this->conn_id, $str);

    }/*}}}*/

   

    /**
     * free query result
     *
     * @access  public
     */

    protected function free_result() {/*{{{*/
        if (is_resource($this->result_id))
            mysqli_free_result($this->result_id);
    }/*}}}*/

    /**
     * get affected_rows
     *
     * @access  public
     * @return  integer
     */

    protected function affected_rows() {/*{{{*/
        return $this->affected_rows;
    }/*}}}*/

    /**
     * fetch result array
     *
     * @access  public
     * @param   paras ( MYSQLI_NUM, MYSQLI_ASSOC, MYSQLI_BOTH )
     * @return  array
     */

    protected function fetch_array($param = MYSQLI_NUM) {/*{{{*/
        $res = array();
        while ($row = mysqli_fetch_array($this->result_id, $param)) { 
            array_push($res, $row);
        }
        return $res;
    }/*}}}*/


    /**
     * fetch result array
     *
     * @access  public
     * @param   paras ( MYSQLI_NUM, MYSQLI_ASSOC, MYSQLI_BOTH )
     * @return  array
     */

    protected function fetch_single($param) {/*{{{*/
        $res = array();

        while ($row = mysqli_fetch_array($this->result_id, $param)) { 
            list($a,$b)=each($row);
            $res[$row['classid']]=$row['name'];
        }
        return $res;
    }/*}}}*/

    /**
     * fetch result object
     *
     * @access  public
     * @return  array
     */

    protected function fetch_object() {/*{{{*/
        $res = array();
        while ($row = mysqli_fetch_object($this->result_id)) {
            array_push($res, $row);
        }
        return $res;
    }/*}}}*/

    /**
     * fetch result hash  ( usage for like menu tree ) 
     *
     * @access  public
     * @return  array
     */

    protected function fetch_hash($hash= array()) {/*{{{*/
        $res=array();
        $res_name='res';
        foreach($hash['node'] as $v){
            $res_name.='[$'.$v.']';

        }
        $i=0;
        while ($row = mysqli_fetch_array($this->result_id, MYSQLI_ASSOC)){
            extract($row);
            $var_name='';
            foreach($hash['value'] as $v){
                $eval_str='$'.$res_name."[$i]['$v']='".$$v."';";
                eval($eval_str);
                
            }
            $i++;
        }
        return $res;
    }/*}}}*/
    
    /**
     * close contection
     *
     * @access  public
     */

    public function close() {/*{{{*/
        if (is_resource($this->conn_id))
            return mysqli_close($this->conn_id);
    }/*}}}*/


    /**
     * fetch  and assign key to it
     *    
     * @access  public
     * @return  array 
     */              
                    
    protected function fetch_assign_key($key) {/*{{{*/ 
        $res=array();                              
        $res_name='res';                          
        //print_r($hash);                        
        $i=0;                                   
        while ($row = mysqli_fetch_array($this->result_id, MYSQLI_ASSOC)){
            $var_name='';                                               
                                                                     
            $res[$row[$key]]=$row;                               
            
/*                                                          
            foreach($hash['value'] as $v){                 
                $eval_str='$'.$res_name."[$i]['$v']='".$$v."';";
                eval($eval_str);                               
                                                              
            }                                                
*/                                                          
                                                           
            $i++;                              
        }                                     
        return $res;  
    }/*}}}*/ 
                    
}

?>
