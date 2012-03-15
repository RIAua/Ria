<?php

// requre php-mysqlnd

namespace Ria\Db\MySQL {

    /**
     * Description of MySQLi driver
     *
     * @author AGvin
     */
    class MySQLi {

        /**
         * @static
         * @var array 
         */
        private static $_instances = array();

        /**
         *
         * @var MySQLi 
         */
        private $_connection = null;

        /**
         * @access public
         * @param string $_connection_name - Name of config section ( $conf['db']['connections']['<$_connection_name>'])
         * 
         */
        public function __construct($_connection_name = 'master', $config) {
            $this->changeConnection($_connection_name, $config);
        }

        /**
         * Change connection by connection name
         *
         * @access public
         * @param string $connection_name - Name of config section ( $conf['db']['connections']['<$_connection_name>'])
         */
        private function changeConnection($connection_name, $config) {
            $this->_connection = self::_getConnectionInstance($connection_name, $config);
        }

        /**
         * get instance of MuSQLi driver
         * 
         * @access private
         * @static
         * @param string $_connection_name - Name of config section ( $conf['db']['connections']['<$_connection_name>'])
         */
        private static function _getConnectionInstance($_connection_name, $conf_data) {
            if (isset($conf_data['connections'][$_connection_name])) {
                self::$_instances[$_connection_name] = new \mysqli(
                        $conf_data['connections'][$_connection_name]['host'],
                        $conf_data['connections'][$_connection_name]['username'],
                        $conf_data['connections'][$_connection_name]['password'],
                        $conf_data['connections'][$_connection_name]['dbname'],
                        $conf_data['connections'][$_connection_name]['port'],
                        $conf_data['connections'][$_connection_name]['socket']
                );
                if (!self::$_instances[$_connection_name]) throw new \RestException(500, 'MySQL: No Mysql connection error');
                if (self::$_instances[$_connection_name]->connect_error) throw new \RestException(500, 'MySQL: Connect Error. ' . self::$_instances[$_connection_name]->connect_error);
                else {
                    if ($conf_data['connections'][$_connection_name]['charset']) {
                        self::$_instances[$_connection_name]->query('SET CHARACTER SET ' . $conf_data['connections'][$_connection_name]['charset']);
                        self::$_instances[$_connection_name]->query('SET character_set_results="' . $conf_data['connections'][$_connection_name]['charset'] . '"');
                        self::$_instances[$_connection_name]->query('SET character_set_database="' . $conf_data['connections'][$_connection_name]['charset'] . '"');
                        self::$_instances[$_connection_name]->query('SET character_set_connection="' . $conf_data['connections'][$_connection_name]['charset'] . '"');
                    }
//                    die('mysqli_connect_error:' . mysqli_connect_error(self::$_instances[$_connection_name]));
                }
                return self::$_instances[$_connection_name];
            } else throw new \RestException(500, 'MySQL: Connection was not configured!');
        }

        /**
         *
         * @param string $qs - query
         * @return boolean
         * @throws \RestException 
         */
        private function _checkQueryResulrOnErrors($qs) {
            if ($this->_connection->errno) {
                throw new \RestException(500, 'MySQL: - ' . $this->_connection->error . '; SQL: [' . $qs . ']; ' . __CLASS__);
                return false;
            } else return true;
        }

        /**
         *
         * @param string $qs
         * @return mixed  
         */
        private function _query($qs) {
//            if ($qs) {
            $ret_data = & $this->_connection->query($qs);
            if ($this->_checkQueryResulrOnErrors($qs)) return $ret_data;
            else return null;
//            }
        }

        /**
         * get array result for this query
         * 
         * @param string $qs
         * @return mixed 
         */
        public function getArray($qs) {
            $result = & $this->_query($qs);
            if ($result) {
                $ret_data = $result->fetch_all(MYSQLI_ASSOC);
                $result->close();
                return $ret_data;
            }
            return array();
        }

        /**
         * get "one" array result for this query
         * 
         * @param str $qs - SQL запрос
         * @return null or array result
         */
        public function getOneArray($qs) {
            $result = & $this->_query($qs);
            if ($result) {
                $ret = $result->fetch_assoc();
                $result->close();
                return (array) $ret;
            } else $ret = array();
            return array();
        }

        /**
         * exec query
         * 
         * @param string $qs - query
         * @return mixed - descriptor or null
         */
        public function execQuery($qs) {
            return $this->_query($qs);
//            $result = $this->_query($qs);
//            if ($result && is_object($result)) $result->close();
//            return (boolean) $result;
        }

//        /**
//         * Return last insert into DB id.
//         *
//         * @return int
//         */
//        public function getLastInsert() {
//            return $this->_connection->insert_id;
//        }
        public function getAffectedRows() {
            return $this->_connection->affected_rows;
        }

        public function getLastInsertId() {
            return $this->_connection->insert_id;
        }

        /**
         *
         * @param string $qs
         * @return integer 
         */
        public function getCount($qs) {
            return (int) $this->getParam($qs, 'count');
        }

        /**
         *
         * @param string $qs
         * @param string $param_name
         * @return mixed 
         */
        public function getParam($qs, $param_name) {
            $tmp_data = $this->getOneArray($qs);
            return isset($tmp_data[$param_name]) ? $tmp_data[$param_name] : null;
        }

        /**
         * 
         */
        public function __clone() {
            
        }

    }

}