<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Ria {

    /**
     * Description of Config
     *
     * @author AGvin
     */
    class Config {

        private static $_instance = null;
        private $_config = array();

        /**
         * [environment].config.php
         */
        /**
         * 
         */

        /**
         * merge all configs data
         * 
         * @param string $_environment
         * @param string $_conf_path
         * @return \Config
         * @example Config::getInstance()->prepareConf('production', '/config/somedir');
         */
        public function prepareConf($_environment = 'production', $_conf_path = 'config') {
            $this->_config = $this->getExternalConfig($_environment, $_conf_path);
            return $this;
        }

        public function getExternalConfig($_environment = 'production', $_conf_path = 'config') {
            $_environment = ($_environment? : 'production');
            //put at this, some cache, like xcache
            return $this->_patchConfigRecursive($_environment, $_conf_path);
        }

        private function _patch_array($_base_arr, $_patch_arr = array()) {
            foreach ($_patch_arr as $key => $value) {
                $_base_arr[$key] = (array_key_exists($key, $_base_arr) && is_array($value)) ? $this->_patch_array($_base_arr[$key], $_patch_arr[$key]) : $_base_arr[$key] = $value;
            }
            return $_base_arr;
        }

        private function _patchConfigRecursive($_environment, $_conf_path = 'config', $_config_patch = array()) {
            $ret_conf = require_once $_conf_path . '/' . $_environment . '.config.php';
            if (isset($ret_conf['extends'])) $ret_conf = $this->_patchConfigRecursive($ret_conf['extends'], $_conf_path, $ret_conf);
            if ($_config_patch) $ret_conf = $this->_patch_array($ret_conf, $_config_patch);
            unset($ret_conf['extends']);
            return $ret_conf;
        }

        public static function getInstance() {
            if (null === self::$_instance) self::$_instance = new self();
            return self::$_instance;
        }

        public function getPreparedConfig() {
            return $this->_config;
        }

//    public function __clone() {
//        
//    }
    }

}