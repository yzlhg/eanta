<?php
/*
 *PDO链式调用的封装
 *使用预处理方式真正防止SQL注入
 *简化常用查询，要使用PDO高级功能， 可以通过GetConnecttion()返回PDO对象自己实现
 *@auther SEVEN
 *@version1.0
 */
/**
 * USAGE：
 * //单例实例化对象
 * $db = pdoModel::getInstance();
 * //多行插入
 * $db->insert('table',array(array('cid'=>$cid,'content'=>c1),array('cid'=>$cid,'content'=>c2)))->execute();
 * //单行插入并获得ID
 * $id = $db->insert('table',array('cid'=>$cid,'content'=>$content))->lastId();
 * //删除
 * $count = $db->delete('table')->where('id=?',$id)->AffectedRows();
 * //更新
 * $count = $db->update('table',array('id'=>$id,'cid'=>$cid,'content'=>$content))->where('id=?',$id)->AffectedRows();
 * //查询
 * //最简单查询
 * $resutl = $db->select('table')->fetchAll();
 * //带条件查询
 * $result = $db->select('table',array('id','cid','content'))->where('cid=? and id>?',array($cid,$id))->Order('id desc')->limit(1)->fetchRow();
 * //in用法查询
 * $where_data[] = $cid;
 * $ids = array(1,2,3);
 * $where_data += $ids;
 * $result = $db->select('table')->where('cid=? and id in(?)',$where_data )->fetchAll();
 * //sql语句查询
 * $result = $db->sql('select * from `table` where id>?,$id')->fetchAll();
 * //通过自定义来使用事务
 * $pdo = $db->GetConnecttion();
 * $pdo->beginTransaction();
 */
class pdoModel {
	protected static $_instance = null;
	protected $dbname = '';
	protected $dsn;
	protected $dbh;
	protected $mPrefix;
	protected $mQueryType;
	protected $mSql;
	protected $mWhere;
	protected $mOrder;
	protected $mLimit;
	protected $mData;
	protected $mPDOStatement;

	//构造
	private function __construct($db_config) {
		try {
			$this->dbname = $db_config['database'];
			$this->mPrefix = isset($db_config['prefix']) ? $db_config['prefix'] : '';
			if ($db_config['type'] == 'mysql') {
				$this->dsn = 'mysql:host=' . $db_config['host'] . ';dbname=' . $db_config['database'];
			} else if ($db_config['type'] == 'sqlsrv') {
				$this->dsn = 'sqlsrv:Server=' . $db_config['host'] . ';Database=' . $db_config['database'];
			}
			$this->dbh = new PDO($this->dsn, $db_config['username'], $db_config['password']);
			if ($this->dbh) {
				$this->dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
				$this->dbh->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
				$this->dbh->exec('SET character_set_connection=utf8,character_set_results=utf8,character_set_client=binary');
			}
		} catch (PDOException $e) {
			$this->Err($e->getMessage());
		}
	}

	//防止克隆
	private function __clone() {}

	//单例模式
	public static function getInstance($db_config = null) {
		if (empty($db_config)) {
			$db_config = Yaf\Registry::get('config')->db;
		}
		if (self::$_instance === null) {
			self::$_instance = new self($db_config);
		}
		return self::$_instance;
	}

	/**
	 * 初始化链式调用的缓存
	 */
	private function init() {
		$this->mQueryType = '';
		$this->mSql = '';
		$this->mWhere = '';
		$this->mOrder = '';
		$this->mLimit = '';
		$this->mData = array();
	}

	/**
	 * 查询链select部分
	 *
	 * @param string $table
	 * @param string|array $field
	 * @return PDO
	 */
	public function select($table, $field = '*') {
		$this->init();
		$this->mQueryType = 's';
		$field_str = is_array($field) ? implode(',', $field) : $field;
		$this->mSql = 'SELECT ' . $field_str . ' FROM ' . $this->mPrefix . $table . '';
		return $this;
	}

	/**
	 * 查询链insert部分
	 *
	 * @param string $talbe
	 * @param array $data
	 * @return PDO
	 */
	public function insert($talbe, $data) {
		$this->init();
		$first = current($data);
		if (is_array($first)) {
			// 多行插入
			$fields = array_keys($first);
			$values = substr(str_repeat('?,', count($fields)), 0, -1);
			$values_all = substr(str_repeat('(' . $values . '),', count($data)), 0, -1);
			$this->mSql = 'INSERT INTO ' . $this->mPrefix . $talbe . '(' . implode(',', $fields) . ') VALUES' . $values_all;
			foreach ($this->mData as $item) {
				$this->mData += $item;
			}
		} else {
			// 单行插入
			$fields = array_keys($data);
			$values = substr(str_repeat('?,', count($fields)), 0, -1);
			$this->mSql = 'INSERT INTO ' . $this->mPrefix . $talbe . '(' . implode(',', $fields) . ') VALUES(' . $values . ')';
			$this->mData = $data;
		}
		return $this;
	}

	/**
	 * 查询链update部分
	 *
	 * @param string $talbe
	 * @param array $data
	 * @return PDO
	 */
	public function update($talbe, $data) {
		$this->init();
		$this->mQueryType = 'u';
		$fields = array_keys($data);
		$this->mSql = 'UPDATE ' . $this->mPrefix . $talbe . ' SET ' . implode('=?,', $fields) . '=?';
		$this->mData = $data;
		return $this;
	}

	/**
	 * 查询链delete部
	 * *
	 * * @param string $talbe
	 * * @return PDO
	 * */
	public function delete($talbe) {
		$this->init();
		$this->mQueryType = 'd';
		$this->mSql = 'DELETE FROM ' . $this->mPrefix . $talbe;
		return $this;
	}

	/**
	 * * 查询链where部分
	 * *
	 * * @param string $str
	 * * @param mixed $parameter
	 * * @return PDO
	 * */
	public function where($str, $parameter = null) {
		if ($parameter !== null) {
			if (is_array($parameter)) {
				$this->mData += $parameter;
				// 根据实际传递的参数数目，替换in语句中的？，只能有一个in语句
				$c1 = substr_count($str, '?');
				$c2 = count($parameter);
				$replace = 'in(' . substr(str_repeat('?,', $c2 - $c1 + 1), 0, -1) . ')';
				$str = str_replace('in(?)', $replace, $str);
			} else {
				$this->mData[] = $parameter;
			}
		}
		$this->mWhere = " WHERE $str";
		return $this;
	}

	/**
	 * * 查询链order部分
	 * *
	 * * @param string $str
	 * * @return PDO
	 * */
	public function order($str) {
		$this->mOrder = " ORDER BY $str";
		return $this;
	}
	/**
	 *  * 查询链limit部分
	 *  *
	 *  * @param number $length
	 *  * @param number $begin
	 *  * @return PDO
	 *  */
	public function limit($length = 10, $begin = 0) {
		$this->mLimit = " LIMIT $begin,$length";
		return $this;
	}

	/**
	 * * 直接Sql语句查询
	 * *
	 * * @param string $sql
	 * * @param mixed $parameter
	 * * @return PDO
	 * */
	public function sql($sql, $parameter = null) {
		$this->init();
		if ($parameter !== null) {
			if (is_array($parameter)) {
				$this->mData = $parameter;
				// 根据实际传递的参数数目，替换in语句中的？，只能有一个in语句
				$c1 = substr_count($sql, '?');
				$c2 = count($parameter);
				$replace = 'in(' . substr(str_repeat('?,', $c2 - $c1 + 1), 0, -1) . ')';
				$sql = str_replace('in(?)', $replace, $sql);
			} else {
				$this->mData[] = $parameter;
			}
		}
		// 自动为表名加前缀，需要时，请在表名前面加下划线并用反单引号括起来
		$sql = str_replace('`_', '`' . $this->mPrefix, $sql);
		$this->mSql = $sql;
		return $this;
	}
	/**
	 * * 执行查询
	 * *
	 * * @return boolean
	 * */
	public function execute() {
		if ($this->dbh) {
			switch ($this->mQueryType) {
			case 's':
				$this->mSql .= $this->mWhere . $this->mOrder . $this->mLimit;
				break;
			case 'u':
				$this->mSql .= $this->mWhere;
				break;
			case 'd':
				$this->mSql .= $this->mWhere;
				break;
			}
			if (empty($this->mSql)) {
				$this->Err('can not find SQL statement<br/>');
				return false;
			}
			if ($this->mPDOStatement = $this->dbh->prepare($this->mSql)) {
				$i = 1;
				foreach ($this->mData as $data) {
					if (!$this->mPDOStatement->bindValue($i, $data)) {
						$this->Err('Error:mPDOStatement::bindValue' . $i . '/' . count($this->mData) . '<br/>');
						return false;
					}
					++$i;
				}
				if ($this->mPDOStatement->execute()) {
					return true;
				}
				$this->Err('Error:mPDOStatement::execute()<br/>');
				return false;
			}
			$this->Err('Error:mPDOStatement::prepare()<br/>');
		}
		return false;
	}
	/**
	 * 返回数据列表的二维关联数组
	 *
	 * @return array(array{}) | empty array | false
	 */
	public function fetchAll() {
		if ($this->execute()) {
			return $this->mPDOStatement->fetchAll();
		} else {
			return false;
		}
	}

	/**
	 * * 返回数据行的一维关联数组
	 * *
	 * * @return array{} | empty array | false
	 * */
	public function fetchRow() {
		if ($this->execute()) {
			$rs = $this->mPDOStatement->fetch();
			return $rs === false ? array() : $rs;
		} else {
			return false;
		}
	}
	/**
	 * * 返回第1行第1列的值
	 * *
	 * * @return mixed | false
	 * */
	public function fetchCell() {
		if ($this->execute()) {
			$rs = $this->mPDOStatement->fetchColumn();
			return $rs === false ? null : $rs;
		} else {
			return false;
		}
	}
	/**
	 * * 返回插入数据的id
	 * *
	 * * @return string boolean
	 * */
	public function lastId() {
		if ($this->execute()) {
			return $this->dbh->lastinsertId();
		} else {
			return false;
		}
	}
	/**
	 * * 返回实际受影响的行数
	 * *
	 * * @return number boolean
	 * */
	public function affectedRows() {
		if ($this->execute()) {
			return $this->mPDOStatement->rowCount();
		} else {
			return false;
		}
	}
	/**
	 * * 调试模式下，显示错误信息
	 * *
	 * * @param string $msg
	 * */
	private function Err($msg) {
		echo $msg;
	}

	/**
	 * * destruct 关闭数据库连接
	 * */
	public function destruct() {
		$this->dbh = null;
	}

}