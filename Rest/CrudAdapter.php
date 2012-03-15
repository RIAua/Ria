<?php

namespace Ria\Rest {

    /**
     *
     * @author AGvin
     */
    class CrudAdapter {

        /**
         * restler instance 
         */
        public $restler;
        private $_module_name;
        private $_module_conf = array();
        private $_db;

        public function __construct($module_name, $_environment = 'production') {
            $this->_module_name = $module_name;
            $common_conf = \Ria\Config::getInstance()->getPreparedConfig();
            $this->_setupModuleConfig(APPLICATION_PATH . '/modules/' . $module_name, $_environment);
            $this->_db = new \Ria\Db\MySQL\MySQLi\CrudAdapter($this->_module_conf['crud']['storage']['table'], $this->_module_conf['crud']['storage']['connection_name'], $common_conf['db']);
            $this->_db->setTableIdFieldName($this->_module_conf['crud']['data']['key']);
        }

        private function _setupModuleConfig($module_path, $_environment) {
            $this->_module_conf = \Ria\Config::getInstance()->getExternalConfig($_environment, $module_path . '/config/');
        }

        private function _getRequestDataIncommingFieldsRules() {
            return isset($this->_module_conf['crud']['data']['fields']) ? $this->_module_conf['crud']['data']['fields'] : array();
        }

        private function _validate($_validate_data, $_validate_incomming_fields_rules = array()) {
            $_validate_request_data_incomming_fields_rules = $this->_getRequestDataIncommingFieldsRules();
//            $this->_validate_request_data_incomming_fields_rules
            $_validate_incomming_fields_rules = $this->_patch_array($_validate_request_data_incomming_fields_rules, $_validate_incomming_fields_rules);
            foreach ($_validate_data as $field_name => $field_data) {
                if (!isset($_validate_incomming_fields_rules[$field_name])) throw new \RestException(417, '[' . $field_name . '] field was not accepted');
            }
            foreach ($_validate_incomming_fields_rules as $field_name => $field_is_required) {
                if ($field_is_required && !isset($_validate_data[$field_name])) throw new \RestException(417, '[' . $field_name . '] field missing');
            }
            return $_validate_data;
        }

        private function _patch_array($_base_arr, $_patch_arr = array()) {
            foreach ($_patch_arr as $key => $value) {
                $_base_arr[$key] = (array_key_exists($key, $_base_arr) && is_array($value)) ? $this->_patch_array($_base_arr[$key], $_patch_arr[$key]) : $_base_arr[$key] = $value;
            }
            return $_base_arr;
        }

        private function _deleteItemCache($_id) {
            if (isset($this->_module_conf['crud']['cache_headers'])) {
                $cache_cleaner = new \Ria\Nginx\Cache\CrudAdapter($this->_module_name);
                $cache_cleaner->deleteItemCache($_id);
            }
        }

        //--- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- ---

        public function get($_id = null) {
//            \Ria\Debug\Log::getInstance()->finish();
            
            if (isset($this->_module_conf['crud']['cache_headers'])) \Ria\Headers::getInstance()->setCacheHeaders($this->_module_conf['crud']['cache_headers']);
            return is_null($_id) ? $this->_db->getItemsList() : $this->_db->getItem($_id);
        }

        public function post($request_data = null) {
            $ret_data = array('id' => $this->_db->insertItem($this->_validate($request_data)));
            if ($ret_data['id'])  $this->_deleteItemCache($ret_data['id']);
            return $ret_data;
//            return array('id' => $this->_db->insertItem($this->_validate($request_data)));
        }

        public function put($_id = null, $request_data = null) {
            if (is_null($_id)) return $this->post($request_data);
            else {
                if ($this->_db->updateItem($_id, $this->_validate($request_data))) {
                    $this->_deleteItemCache($_id);
                    return array('id' => $_id);
                } else throw new \RestException(500, 'update failed');
            }
        }

        public function delete($_id = null) {
            $ret_data = $this->_db->deleteItem($_id);
            $this->_deleteItemCache($_id);
            return $ret_data;
        }

    }

}