<?php

class PdoDb {

    protected $hostname = '';
    protected $username = '';
    protected $password = '';
    protected $database = '';
    protected $conn_id = FALSE;
//  protected $result_id = FALSE;   
    protected $affected_rows = 0;
    protected $error_no = FALSE;
    protected $error = FALSE;
    protected $char_set = 'UTF8';
    protected $db_debug = FALSE;

    // --------------------------------------------------------------------




    function __construct($params = '') {/*{{{*/
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
        $options = array(
             PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
             PDO::ATTR_PERSISTENT => true,
        ); 

        try {

            $this->conn_id= new PDO('mysql:host='.$this->hostname.';dbname=ticket', $this->username, $this->password ,$options);
            //$this->conn_id = new PDO('mysql:host=localhost;dbname=myDatabase', $username, $password);
            $this->conn_id->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    
        } catch(PDOException $e) {
            echo 'ERROR: ' . $e->getMessage();
            
        }

        // Q: remove or ?
        return TRUE;

    }/*}}}*/


    /**
     * query select
     *
     * @access  public
     * @param   sql string and option ( array_num, array_assoc, aray_both, object )
     * @return  array
     */

    function query($sql = '', $opt = 'array_assoc' , $hash = array()) {/*{{{*/

        ($this->conn_id === FALSE) AND $this->connection();

        if (empty($sql)) return FALSE;
        $result_id = $this->conn_id->query($sql);
        $res=array();

        //echo "$sql\n $opt\n$hash\n";

        $i=0;                                   
        if($opt != 'assign_key'){
            $mode='PDO::'.strtoupper($opt);
            while($row=$result_id->fetch($$mode)){   
        //PDO::FETCH_OBJ 指定取出資料的型態
                array_push($res, $row);
            }
        }else{

            while($row=$result_id->fetch(PDO::FETCH_ASSOC)){   
                $res_name='res';   
                $res[$row[$hash]]=$row;                               
/*                                                          
            foreach($hash['value'] as $v){                 
                $eval_str='$'.$res_name."[$i]['$v']='".$$v."';";
                eval($eval_str);                               
                                                              
            }                                                
*/                                                     
            $i++;                              
        }
    }


    return $res;

/*

        $arr_opts = array (
            'array_num'     =>  array( 'func' => 'fetch_array',  'paras' => MYSQLI_NUM),
            'array_assoc'   =>  array( 'func' => 'fetch_array',  'paras' => MYSQLI_ASSOC),
            'array_both'    =>  array( 'func' => 'fetch_array',  'paras' => MYSQLI_BOTH),
            'object'        =>  array( 'func' => 'fetch_object', 'paras' => ''),
            'single'        =>  array( 'func' => 'fetch_single', 'paras' => MYSQLI_ASSOC),
            'hash'          =>  array( 'func' => 'fetch_hash'  , 'paras' => $hash ),
            'assign_key'    =>  array( 'func' => 'fetch_assign_key'  , 'paras' => $hash ),
        );
x
        $call_fetch_fun = isset($arr_opts[$opt]) ? $arr_opts[$opt] : $arr_opts['array_num'];

        return $this->$call_fetch_fun['func']($call_fetch_fun['paras']);
        */

    }/*}}}*/

    /**
     * exec insert, update, delete etc.
     *
     * @access  public
     * @param   string
     * @return  integer
     */

    public function exec($data = '') {/*{{{*/

        ($this->conn_id === FALSE) AND $this->connection();
        
        if(!is_array($data)){

            $this->conn_id->exec($data);

        }else{

            // you have to fill something
        }


//        $this->affected_rows = mysqli_affected_rows($this->conn_id);
//        return $this->affected_rows;
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

    // no test 大错特错
    public function last_insert_id() {/*{{{*/

        ($this->conn_id === FALSE) AND $this->connection();
        //$result = mysqli_query($this->conn_id, "select LAST_INSERT_ID() as `last_insert_id`");
        return $this->conn_id->lastInsertId();

    }/*}}}*/

     /**
     * escape string
     *
     * @access  public
     * @return  integer
     */

     public function escape($str) {/*{{{*/
        ($this->conn_id === FALSE) AND $this->connection();
        return $this->conn_id->quote($str);
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


  
}

?>
