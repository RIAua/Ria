<?php

/*
 * http://ru.wikipedia.org/wiki/%D0%A1%D0%BF%D0%B8%D1%81%D0%BE%D0%BA_%D0%B7%D0%B0%D0%B3%D0%BE%D0%BB%D0%BE%D0%B2%D0%BA%D0%BE%D0%B2_HTTP#.D0.97.D0.B0.D0.B3.D0.BE.D0.BB.D0.BE.D0.B2.D0.BA.D0.B8_.D0.B7.D0.B0.D0.BF.D1.80.D0.BE.D1.81.D0.B0
 */

namespace Ria {

    /**
     * Description of Config
     *
     * @author AGvin
     */
    class Headers {

        private static $_instance = null;
        private $_response_headers_arr = array();
        private $_cache_headers_was_setted = false;

        /**
         * [environment].config.php
         */
        public static function getInstance() {
            if (null == self::$_instance) self::$_instance = new self();
            return self::$_instance;
        }

//        public function __construct() {
//            $this->_request_headers_arr = \http_get_request_headers();
//        }
        /**
         * get headers list
         * @return array
         */
//        // pecl install pecl_http
        public function getRequestHeaders() {
            return \http_get_request_headers();
        }

        public function setCacheHeaders($_time_sec = 86400) {
            $this->_cache_headers_was_setted = false;
            if ($_time_sec == 0) {
                $this->setHeader('Expires', 'Mon, 26 Jul 1997 05:00:00 GMT');
                $this->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
                $this->setHeader('Pragma', 'no-cache');
            } else {
                $this->setHeader('Expires', gmdate('D, d M Y H:i:s', time() + $_time_sec) . ' GMT');
                $this->setHeader('Cache-Control', 'public, max-age=' . $_time_sec);
                $this->setHeader('Pragma', '');
            }
            $this->_cache_headers_was_setted = true;
            return $this;
        }

        /**
         *
         * @return \Ria\Headers 
         */
        public function cleanHeaders() {
            $this->_response_headers_arr = array();
            return $this;
        }

        /**
         *
         * @param string $_name
         * @param string $_value
         * @return \Ria\Headers
         * @throws \RestException 
         */
        public function setHeader($_name, $_value) {
            $_name_low = trim(strtolower($_name));
            if ($this->_cache_headers_was_setted && ($_name_low == 'expires' || $_name_low == 'cache-control' || $_name_low == 'pragma')) throw new \RestException(501, 'Headers:  header [' . $_name . '] cannot change');
            else $this->_response_headers_arr[$_name_low] = $_name . ': ' . $_value;
            return $this;
        }

//        // pecl install pecl_http
//        public function getHeader($_name) {
//            return isset($this->_request_headers_arr[$_name]) ? $this->_request_headers_arr[$_name] : null;
//        }
        /**
         * setup response headers
         */
        public function handle() {
            if (!$this->_cache_headers_was_setted) $this->setCacheHeaders(0);
            foreach ($this->_response_headers_arr as $header) header($header);
        }

    }

}