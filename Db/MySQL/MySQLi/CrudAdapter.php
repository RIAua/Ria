<?php

namespace Ria\Db\MySQL\MySQLi {

    /**
     * Description of CrudAdapter
     *
     * @author AGvin
     */
    class CrudAdapter {

        /**
         *
         * @var MySQLi 
         */
        private $_connection_name = 'main';
        private $_connection = null;
        private $_table = null;
        private $_table_id_field = 'id';
        private $_config = array();

        /**
         *
         * @param string $table - table name
         * @param type $_connection_name - connection name
         * @param type $_config  - config for connection
         */
        public function __construct($table, $_connection_name = 'main', $_config) {
            $this->setTable($table);
            $this->_setConnectionName($_connection_name);
            $this->_setConfig($_config);
            $this->_connection = new \Ria\Db\MySQL\MySQLi($this->_getConnectionName(), $this->_getConfig());
        }

        /**
         * Change connection by connection name
         *
         * @access public
         * @param string $connection_name - Name of config section ( $conf['db']['connections']['<$_connection_name>'])
         */
        public function _setConnectionName($_connection_name) {
            $this->_connection_name = $_connection_name;
        }

        public function _getConnectionName() {
            return $this->_connection_name;
        }

        /**
         *
         * @param type $_config 
         */
        private function _setConfig($_config) {
            $this->_config = $_config;
        }

        private function _getConfig() {
            return $this->_config;
        }

        private function _getConnection() {
            return $this->_connection;
        }

        public function setTable($_table) {
            $this->_table = $_table;
        }

        public function getTable() {
            return $this->_table;
        }

        public function getTableIdFieldName() {
            return $this->_table_id_field;
        }
        public function setTableIdFieldName($_id_field_name) {
            $this->_table_id_field = $_id_field_name;
        }

        private function _fixStringForSQL($str, $_wrap = '\'') {
            return $_wrap . htmlspecialchars(addslashes($str)) . $_wrap;
        }

        private function _prepareIncommingData($_data) {
            $ret_data = array();
            foreach ($_data AS $field => $value) {
                $ret_data[] = $this->_fixStringForSQL($field, '') . '=' . (is_string($value) ? $this->_fixStringForSQL($value) : $value);
            }
            return join(',', $ret_data);
        }

        #######
        /**
         *
         * @param string $_id
         * @return type 
         */

        public function getItem($_id) {
            return $this->_getConnection()->getOneArray('SELECT * FROM ' . $this->getTable() . ' WHERE ' . $this->getTableIdFieldName() . '=' . $this->_fixStringForSQL($_id));
        }

        /**
         *
         * @param int $_page
         * @param int $_items_per_page
         * @return array 
         */
        public function getItemsList($_page = 0, $_items_per_page = 100) {
            return $this->_getConnection()->getArray('SELECT * FROM ' . $this->getTable() . ' LIMIT ' . $_items_per_page . ' OFFSET ' . ($_page * $_items_per_page));
        }

        /**
         *
         * @param array $_data - assoc array field => value
         * @return type 
         */
        public function insertItem($_data) {
            if ($this->_getConnection()->execQuery('INSERT ' . $this->getTable() . ' SET ' . $this->_prepareIncommingData($_data))) return $this->getLastInsertId();
        }

        /**
         *
         * @param string $_id
         * @param array $_data - assoc array field => value
         * @return type 
         */
        public function updateItem($_id, $_data) {
            if ($this->_getConnection()->execQuery('UPDATE ' . $this->getTable() . ' SET ' . $this->_prepareIncommingData($_data) . ' WHERE ' . $this->getTableIdFieldName() . '=' . $this->_fixStringForSQL($_id))) {
                $affected_rows = $this->getAffectedRows();
                if ($affected_rows==1) return true;
                elseif ($affected_rows==0) return false;
                else throw new \RestException(500, 'More than one item was changed!');
            } else return false;
        }

        /**
         *
         * @param string $_id
         * @return type 
         */
        public function deleteItem($_id) {
            return $this->_getConnection()->execQuery('DELETE FROM ' . $this->getTable() . ' WHERE ' . $this->getTableIdFieldName() . '=' . $this->_fixStringForSQL($_id));
        }

        public function getAffectedRows() {
            return $this->_getConnection()->getAffectedRows();
        }

        public function getLastInsertId() {
            return $this->_getConnection()->getLastInsertId();
        }
    }

}