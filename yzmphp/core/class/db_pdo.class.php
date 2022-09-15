<?php
/**
 * db_pdo.class.php	 PDO数据库类
 * 
 * @author           袁志蒙  
 * @license          http://www.yzmcms.com
 * @lastmodify       2018-08-15
 */

class db_pdo{
	
	private static $link = null;       		 //数据库连接资源句柄
	private static $db_link = array();  	 //数据库连接资源池
	private $config = array();  	  		 //数据库配置信息
	private $tablename;                      //数据库表名,不包含表前缀
	private $key = array();           		 //存放条件语句
	private $lastsql = '';            		 //存放sql语句
	private static $params = array(
		PDO::ATTR_CASE              => PDO::CASE_NATURAL,
        PDO::ATTR_ERRMODE           => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_ORACLE_NULLS      => PDO::NULL_NATURAL,
        PDO::ATTR_STRINGIFY_FETCHES => false,
        PDO::ATTR_EMULATE_PREPARES  => false,
	);
		
		
	/**
	 * 初始化链接数据库
	 */
	public function __construct($config, $tablename){
		$this->config = $config;
		$this->tablename = $tablename;

		if(is_null(self::$link)) $this->db(0, $config);	
	}
	

	/**
	 * 真正开启数据库连接
	 * 			
	 * @return object pdo
	 */	
	public function connect(){ 
		try { 
			$dns = 'mysql:host='.$this->config['db_host'].';dbname='.$this->config['db_name'].';port='.intval($this->config['db_port']);   
			self::$link = new PDO($dns, $this->config['db_user'], $this->config['db_pwd'], self::$params);
			self::$link -> exec("SET names utf8, sql_mode=''");
			return self::$link;
		}catch(PDOException $e) {
			self::$link = null;
			$mysql_error = APP_DEBUG ? $e->getMessage() : 'Can not connect to MySQL server!';
			application::halt($mysql_error, 550);
		}		
	}
	
	
	/**
	 * 切换当前的数据库连接
	 *
	 * @param $linknum 	数据库编号	
	 * @param $config 	array	
	 * @参数说明		array('db_host'=>'127.0.0.1', 'db_user'=>'root', 'db_pwd'=>'', 'db_name'=>'yzmcms', 'db_port'=>3306, 'db_prefix'=>'yzm_')
	 *					[服务器地址, 数据库用户名, 数据库密码, 数据库名, 服务器端口, 数据表前缀]
	 * 						
	 * 使用方法(添加一个编号为1的数据库连接，并自动切换到当前的数据库连接):  
	 * D('tablename')->db(1, array('db_host'=>'127.0.0.1', 'db_user'=>'root', 'db_pwd'=>'', 'db_name'=>'test', 'db_port'=>3306, 'db_prefix'=>'yzm_'))->select();
	 * 
	 * 当第二次切换到相同的数据库的时候，就不需要传入数据库连接信息了，可以直接使用：D('tablename')->db(1)->select();
	 * 如果需要切换到默认的数据库连接，只需要调用：D('tablename')->db(0)->select();
	 *
	 */		
	public function db($linknum = 0, $config = array()){
		if(isset(self::$db_link[$linknum])){
			self::$link = self::$db_link[$linknum]['db'];
			$this->config = self::$db_link[$linknum]['config'];
		}else{
			if(empty($config)) $this->geterr('Database number to '.$linknum.' Not existent!'); 
			$this->config = $config;
			self::$db_link[$linknum]['db'] = self::$link = self::connect();
			self::$db_link[$linknum]['config'] = $config;
		}
		return $this;
	}
	
	

    /**
     * 获取当前的数据表
     * @return string
     */
    private function get_tablename() {
        $alias = isset($this->key['alias']) ? ' '.$this->key['alias'].' ' : '';
        return '`'.$this->config['db_name'].'` . `'.$this->config['db_prefix'].$this->tablename.'`' .$alias;
    }


		
	/**
	 * 内部方法：过滤函数
	 * @param $value
	 * @param $chars
	 * @return string
	 */	
	private function safe_data($value, $chars = false){
		if(!MAGIC_QUOTES_GPC) $value = addslashes($value);
		if($chars) $value = htmlspecialchars($value);

		return $value;
	}
	
	
	/**
	 * 内部方法：过滤非表字段
	 * @param $arr
	 * @param $primary 是否过滤主键
	 * @return array
	 */
	private function filter_field($arr, $primary = true){	
		$fields = $this->get_fields();	
		foreach($arr as $k => $v){
			if(!in_array($k, $fields)) unset($arr[$k]);
		}
		if($primary){
			$p = $this->get_primary();
			if(isset($arr[$p])) unset($arr[$p]);
		}
		return $arr;
	}

	
	/**
	 * 内部方法：数据库执行方法
	 * @param $sql 要执行的sql语句
	 * @return 查询资源句柄
	 */
	private function execute($sql) {
		try{
			$statement = self::$link->prepare($sql);
			if(isset($this->key['where']['bind'])) { 
				foreach($this->key['where']['bind'] as $key => $val){
					$statement->bindValue($key+1, $val);
					//组装预处理SQL，便于调试
					$sql = substr_replace($sql, '\''.$val.'\'', strpos($sql, '?'), 1);
				}
			}
			$statement ->execute();
			$this->lastsql = $sql;
			debug::addmsg($sql, 1);
			$this->key = array();
			return $statement;
		}catch (PDOException $e){
			$this->geterr('Execute SQL error, message : '.$e->getMessage(), $sql);
		}
	}
	
	
	
	/**
	 * 组装where条件，将数组转换为SQL语句
	 * @param array $where  要生成的数组,参数可以为数组也可以为字符串，建议数组。
	 * return string
	 */
	public function where($arr = ''){
		if(empty($arr))  return $this;		
		if(is_array($arr)) {
			$args = func_get_args();
			$str = '(';
			foreach ($args as $value){
				foreach($value as $kk => $vv){
					if(!is_array($vv)){
						$vv = !is_null($vv) ? $vv : '';
						if(!strpos($kk,'>') && !strpos($kk,'<') && !strpos($kk,'=') && substr($vv, 0, 1) != '%' && substr($vv, -1) != '%'){   
							$str .= $kk.' = ? AND ';
						}else if(substr($vv, 0, 1) == '%' || substr($vv, -1) == '%'){
							$str .= $kk.' LIKE ? AND '; 
						}else{
							$str .= $kk.' ? AND ';     
						}
						
						$this->key['where']['bind'][] = $this->safe_data($vv);
					}else{
						
						$exp_arr = array('eq'=>'=','neq'=>'<>','gt'=>'>','egt'=>'>=','lt'=>'<','elt'=>'<=','notlike'=>'NOT LIKE','like'=>'LIKE','in'=>'IN','notin'=>'NOT IN','not in'=>'NOT IN','between'=>'BETWEEN','not between'=>'NOT BETWEEN','notbetween'=>'NOT BETWEEN');
						
						$exp = isset($vv[0])&&isset($exp_arr[strtolower($vv[0])]) ? $exp_arr[strtolower($vv[0])] : '';
						$rule = isset($vv[1]) ? $vv[1] : '';
						$fun = isset($vv[2]) ? $vv[2] : '';
						if(!$exp) $this->geterr('The expression '.$vv[0].' does not exis!'); 
						
						if(is_array($rule)) {
							if($fun) $rule = array_map($fun, $rule);
							$rule = strpos($exp, 'BETWEEN') === false ? "('".join("','", $rule)."')" : "'".join("' AND '", $rule)."'";
						}else{
							$this->key['where']['bind'][] = $fun ? $fun($rule) : $this->safe_data($rule);
							$rule = '?';
						}
						$str .= $kk.' '.$exp.' '.$rule.' AND ';
					}
				}
				$str = rtrim($str,' AND ').')';
				$str .= ' OR (';
			}
			$str = rtrim($str,' OR (');
			$this->key['where']['str'] = $str;
			return $this;
		}else{
			$this->key['where']['str'] = str_replace('yzmcms_', $this->config['db_prefix'], $arr);	
			return $this;
		}
	}
	
	
	/**
	 * 内部方法：查询部分，开始组装SQL
	 * @param $name
	 * @param $value
	 * @return object
	 */
	public function __call($name, $value){
		if(in_array($name, array('alias', 'field', 'order', 'limit', 'group', 'having'))){
			if(isset($value[0])) $this->key[$name] = $value[0];
			return $this;
		}else{
			$this->geterr('Call to '.$name.' function not exist!'); 
		}
	}
	
	
	/**
	 * 执行添加记录操作
	 * @param $data         要增加的数据，参数为数组。数组key为字段值，数组值为数据取值
	 * @param $filter       如果为真值[1为真] 则开启实体转义
	 * @param $primary 		是否过滤主键
	 * @param $replace 		是否为replace
	 * @return int/boolean  成功：返回自动增长的ID，失败：false
	 */
	public function insert($data, $filter = false, $primary = true, $replace = false){
		if(!is_array($data)) {
		    $this->geterr('insert function First parameter Must be array!'); 
			return false;
		}
		$data = $this->filter_field($data, $primary); 
		$fields = $values = array();
		foreach ($data AS $key => $val){
			$fields[] = '`'. $key .'`';
			$values[] = "'" . $this->safe_data($val, $filter) . "'";
		}		
		
		if(empty($fields)) return false;
		$sql = ($replace ? 'REPLACE' : 'INSERT').' INTO '.$this->get_tablename().' ('. implode(', ', $fields) .') VALUES ('. implode(', ', $values) .')';
		$this->execute($sql);
		$id = self::$link->lastInsertId();
		return is_numeric($id) ? (int) $id : $id;
	}


	/**
	 * 批量执行添加记录操作
	 * @param $data         要增加的数据，参数为二维数组
	 * @param $filter       如果为真值[1为真] 则开启实体转义
	 * @param $replace 		是否为replace
	 * @return int/boolean  成功：返回首个自动增长的ID，失败：false
	 */
	public function insert_all($datas, $filter = false, $replace = false){
		if(!is_array($datas) || !current($datas)) {
		    $this->geterr('insert all function First parameter Must be array!'); 
			return false;
		}
		$fields = array_keys(current($datas));
		$values = array();
		foreach ($datas as $data){
			$value = array();
			foreach ($data as $key => $val) {
				$value[] = "'" . $this->safe_data($val, $filter) . "'";
			}
			$values[] = '('.implode(',', $value).')';
		}		
		
		if(empty($fields)) return false;
		$sql = ($replace ? 'REPLACE' : 'INSERT').' INTO '.$this->get_tablename().' ('. implode(', ', $fields) .') VALUES '. implode(', ', $values);
		$this->execute($sql);
		$id = self::$link->lastInsertId();
		return is_numeric($id) ? (int) $id : $id;
	}
	
	
	/**
	 * 执行删除记录操作
	 * @param $where 		参数为数组，删除数据条件,不充许为空。
	 * @param $many 		是否删除多个，多用在批量删除，取的主键在某个范围内，例如 $admin->delete(array(3,4,5), true);
	 *                      结果为： DELETE FROM `yzmcms_admin` WHERE id IN (3,4,5);
	 *
	 * @return int          返回影响行数
	 */
	public function delete($where, $many = false){	
		if(is_array($where) && !empty($where)){
            if(!$many){
				$this->where($where);   
			}else{
				$where = array_map('intval', $where);
				$sql = implode(', ', $where);
				$this->key['where']['str'] = $this->get_primary().' IN ('.$sql.')';
			}			
			$sql = 'DELETE FROM '.$this->get_tablename().' WHERE '.$this->key['where']['str'];
		}else{
			$this->geterr('delete function First parameter Must be array Or cant be empty!'); 
			return false;
		}
		$statement = $this->execute($sql);
		return $statement->rowCount();
	}

	
	/**
	 * 执行更新记录操作
	 * @param $data 		要更新的数据内容，参数可以为数组也可以为字符串，建议数组。
	 * 						为数组时数组key为字段值，数组值为数据取值
	 * 						为字符串时[例：`name`='myname',`hits`=`hits`+1]。
	 *						为数组时[例: array('name'=>'php','password'=>'123456')]						
	 * @param $where 		更新数据时的条件,参数为数组类型或者字符串
	 * @param $filter 		第三个参数选填 如果为真值[1为真] 则开启实体转义
	 * @param $primary 		是否过滤主键
	 * @return int          返回影响行数
	 */	
	public function update($data, $where = '', $filter = false, $primary = true){	
		$this->where($where);
		if(is_array($data)){
			$data = $this->filter_field($data, $primary);				
			$sets = array();
			foreach ($data AS $key => $val){
				$sets[] = '`'. $key .'` = \''. $this->safe_data($val, $filter) .'\'';
			}
			$value = implode(', ', $sets);
		}else{
			$value = $data;		
		}	

		if(empty($value)) return false;
		$sql = 'UPDATE '.$this->get_tablename().' SET '.$value.' WHERE '.$this->key['where']['str'];
		$statement = $this->execute($sql);
		return $statement->rowCount();
	}

	
	/**
	 * 获取查询多条结果，返回二维数组
	 * @return array
	 */	
	public function select(){
        $rs = array();		
		$field = isset($this->key['field']) ? str_replace('yzmcms_', $this->config['db_prefix'], $this->key['field']) : ' * ';
		$join = isset($this->key['join']) ? ' '.implode(' ', $this->key['join']) : '';
		$where = isset($this->key['where']['str']) ? ' WHERE '.$this->key['where']['str'] : '';
		$group = isset($this->key['group']) ? ' GROUP BY '.$this->key['group'] : '';
		$having = isset($this->key['having']) ? ' HAVING '.$this->key['having'] : '';
		$order = isset($this->key['order']) ? ' ORDER BY '.$this->key['order'] : '';
		$limit = isset($this->key['limit']) ? ' LIMIT '.$this->key['limit'] : '';				
		
		$sql = 'SELECT '.$field.' FROM '.$this->get_tablename().$join.$where.$group.$having.$order.$limit;
		$selectquery = $this->execute($sql);
	    return $selectquery->fetchAll(PDO::FETCH_ASSOC);
	}
	
	
	/**
	 * 获取查询一条结果，返回一维数组
	 * @return array or false
	 */	
	public function find(){
		$field = isset($this->key['field']) ? str_replace('yzmcms_', $this->config['db_prefix'], $this->key['field']) : ' * ';
		$join = isset($this->key['join']) ? ' '.implode(' ', $this->key['join']) : '';
		$where = isset($this->key['where']['str']) ? ' WHERE '.$this->key['where']['str'] : '';
		$group = isset($this->key['group']) ? ' GROUP BY '.$this->key['group'] : '';
		$having = isset($this->key['having']) ? ' HAVING '.$this->key['having'] : '';
		$order = isset($this->key['order']) ? ' ORDER BY '.$this->key['order'] : '';
		$limit = ' LIMIT 1';		
		
		$sql = 'SELECT '.$field.' FROM '.$this->get_tablename().$join.$where.$group.$having.$order.$limit;
		$findquery = $this->execute($sql);
	    return $findquery->fetch(PDO::FETCH_ASSOC);
	}
	
	
	
	/**
	 * 获取查询一条结果的一个字段
	 * @return string
	 */	
	public function one(){
		$field = isset($this->key['field']) ? str_replace('yzmcms_', $this->config['db_prefix'], $this->key['field']) : ' * ';
		$join = isset($this->key['join']) ? ' '.implode(' ', $this->key['join']) : '';
		$where = isset($this->key['where']['str']) ? ' WHERE '.$this->key['where']['str'] : '';
		$group = isset($this->key['group']) ? ' GROUP BY '.$this->key['group'] : '';
		$having = isset($this->key['having']) ? ' HAVING '.$this->key['having'] : '';
		$order = isset($this->key['order']) ? ' ORDER BY '.$this->key['order'] : '';
		$limit = ' LIMIT 1';		
		
		$sql = 'SELECT '.$field.' FROM '.$this->get_tablename().$join.$where.$group.$having.$order.$limit;
		$findquery = $this->execute($sql);
		$data = $findquery->fetch(PDO::FETCH_NUM);
	    return $data ? $data[0] : '';
	}	
	
		
		
	/**
	 * 连接查询
	 * @param $join 	string SQL语句，如yzmcms_admin ON yzmcms_admintype.id=yzmcms_admin.id
	 * @param $type 	可选参数,默认是inner
	 * @return object
	 */	
	public function join($join, $type = 'INNER'){
		$join = str_replace('yzmcms_', $this->config['db_prefix'], $join);   
        $this->key['join'][] = stripos($join,'JOIN') !== false ? $join : $type.' JOIN '.$join;
	    return $this;
	}	
	
	
	/**
	 * 用于调试程序，输入SQL语句
	 * @param $echo 	可选参数,默认是输出
	 * @return string
	 */	
	public function lastsql($echo = true){
		$sql = $this->lastsql;
		if($echo)
			echo '<div style="font-size:14px;text-align:left; border:1px solid #9cc9e0;line-height:25px; padding:5px 10px;color:#000;font-family:Arial, Helvetica,sans-serif;"><p><b>SQL：</b>'.$sql.'</p></div>'; 	
		else
			return $sql;		
	}
	

	/**
	 * 自定义SQL语句
	 * @param  $sql sql语句
	 * @param  $fetch_all 查询时是否返回二维数组
	 * @return mixed
	 */		
	public function query($sql = '', $fetch_all = true){
		$sql = str_replace('yzmcms_', $this->config['db_prefix'], $sql);  
		if(preg_match("/^(?:UPDATE|DELETE|TRUNCATE|ALTER|DROP|FLUSH|INSERT|REPLACE|SET|CREATE)\\s+/i", $sql)){
			return $this->execute($sql);	 
		} 
		return $fetch_all ? $this->fetch_all($this->execute($sql)) : $this->fetch_array($this->execute($sql));
	}


	/**
	 * 返回一维数组，与query方法结合使用
	 * @param  object
	 * @return array
	 */		
    public function fetch_array($query, $result_type = PDO::FETCH_ASSOC) {
		if(!is_object($query))   return $query;
		return $query->fetch($result_type);
	}	
	

	/**
	 * 返回二维数组，与query方法结合使用
	 * @param  object
	 * @return array
	 */		
    public function fetch_all($query, $result_type = PDO::FETCH_ASSOC) {
		if(!is_object($query))   return $query;
		return $query->fetchAll($result_type);
	}
	
	
	/**
	 * 获取错误提示
	 */		
	private function geterr($msg, $sql=''){
		if(APP_DEBUG){
			if(is_ajax()) return_json(array('status'=>0, 'message'=>'MySQL Error: '.$msg.' | '.$sql));
			if(PHP_SAPI == 'cli') exit('MySQL Error: '.$msg.' | '.$sql);
			application::fatalerror($msg, $sql, 2);	
		}else{
			write_error_log(array('MySQL Error', $msg, $sql));
			if(is_ajax()) return_json(array('status'=>0, 'message'=>'MySQL Error!'));
			application::halt('MySQL Error!', 500);
		}
	}

	
	/**
	 * 返回记录行数。
	 * @return int 
	 */	
	public function total(){
		$join = isset($this->key['join']) ? ' '.implode(' ', $this->key['join']) : '';
		$where = isset($this->key['where']['str']) ? ' WHERE '.$this->key['where']['str'] : '';		
		$sql = 'SELECT COUNT(*) AS total FROM '.$this->get_tablename().$join.$where;
		$totquery = $this->execute($sql);
		$total = $totquery->fetch(PDO::FETCH_ASSOC);
        return $total['total'];		
	}


    /**
     * 启动事务
     * @return boolean
     */
    public function start_transaction() {
        return self::$link->beginTransaction();
    }

	
    /**
     * 提交事务
     * @return boolean
     */
    public function commit() {
        self::$link->commit();
    }

	
    /**
     * 事务回滚
     * @return boolean
     */
    public function rollback() {
		return self::$link->rollback();
    }

		
	/**
	 * 获取数据表主键
	 * @param $table 		数据表 可选
	 * @return array
	 */
	public function get_primary($table = '') {
		$table = empty($table) ? $this->get_tablename() : $table;
		$sql = "SHOW COLUMNS FROM $table";
		$r = self::$link->query($sql);
		$data = $r->fetchAll(PDO::FETCH_ASSOC);	
		foreach ($data as $key => $value) {
			if($value['Key'] == 'PRI') { 
				return $value['Field'];
			}
		}
		return $data[0]['Field'];
	}
	

	/**
	 * 获取数据库 所有表
	 * @return array 
	 */		
	public function list_tables() {
		$tables = array();
		$listqeury = $this->execute('SHOW TABLES');
		$data = $listqeury->fetchAll(PDO::FETCH_NUM);	
		foreach ($data as $key => $value) {
			$tables[] = $value[0];
		}
		return $tables;
	}	


	/**
	 * 获取表字段
	 * @param $table 		数据表 可选
	 * @return array
	 */
	public function get_fields($table = '') {
		$table = empty($table) ? $this->get_tablename() : $table;
		$fields = array();
		$sql = "SHOW COLUMNS FROM $table";
		$r = self::$link->query($sql);
		$data = $r->fetchAll(PDO::FETCH_ASSOC);	
		foreach ($data as $key => $value) {
			$fields[] = $value['Field'];
		}
		return $fields;
	}

	
	/**
	 * 检查表是否存在
	 * @param $table 表名
	 * @return boolean
	 */
	public function table_exists($table) {
		$tables = $this->list_tables();
		return in_array($table, $tables);
	}


	/**
	 * 检查字段是否存在
	 * @param $table 表名
	 * @param $field 字段名
	 * @return boolean
	 */
	public function field_exists($table, $field) {
		$fields = $this->get_fields($table);
		return in_array($field, $fields);
	}
	
	
	/**
	 * 返回 MySQL 服务器版本信息
	 * @return string 
	 */	
	public function version(){
	    return self::$link->getAttribute(PDO::ATTR_SERVER_VERSION);	
	}
	

	/**
	 * 关闭数据库连接
	 */	
	public function close(){
		self::$link = null;
	    return true;
	}
	
}