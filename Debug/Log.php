<?php

namespace Ria\Debug {

    /**
     * Description of Config
     *
     * @author AGvin
     */
    class Log {

        /**
         * HTTP status codes
         * @var array
         */
        private $_codes = array(
            100 => 'Continue',
            101 => 'Switching Protocols',
            200 => 'OK',
            201 => 'Created',
            202 => 'Accepted',
            203 => 'Non-Authoritative Information',
            204 => 'No Content',
            205 => 'Reset Content',
            206 => 'Partial Content',
            300 => 'Multiple Choices',
            301 => 'Moved Permanently',
            302 => 'Found',
            303 => 'See Other',
            304 => 'Not Modified',
            305 => 'Use Proxy',
            306 => '(Unused)',
            307 => 'Temporary Redirect',
            400 => 'Bad Request',
            401 => 'Unauthorized',
            402 => 'Payment Required',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            406 => 'Not Acceptable',
            407 => 'Proxy Authentication Required',
            408 => 'Request Timeout',
            409 => 'Conflict',
            410 => 'Gone',
            411 => 'Length Required',
            412 => 'Precondition Failed',
            413 => 'Request Entity Too Large',
            414 => 'Request-URI Too Long',
            415 => 'Unsupported Media Type',
            416 => 'Requested Range Not Satisfiable',
            417 => 'Expectation Failed',
            500 => 'Internal Server Error',
            501 => 'Not Implemented',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
            504 => 'Gateway Timeout',
            505 => 'HTTP Version Not Supported'
        );
        private static $_instance = null;
//        private $_config = array();

        private $_logger = null;
//        private $_restler_obj = null;

        private $_debug_enabled = false;
        private $_debug_section = 'main';
        private $_already_prepared = false;
        private $_trace_time_point_id = 0;
        private $_trace_time_point_name_prefix = 'point_';
        private $_logs_separator = "\n=====================================================\n";
        private $_logs_parts_separator = "\n-----------------------------------------------------\n";
        private $_logs_path = '/tmp';
        private $_logs_name = 'debug_main';

        public static function getInstance() {
            if (null === self::$_instance) self::$_instance = new self();
            return self::$_instance;
        }

        public function prepare($conf) {
//            $this->_restler_obj = $_restler_obj;
            try {
                if ($this->_already_prepared) throw new \RestException(501, 'debug was already prepared! (' . __METHOD__ . ')');
                $this->_already_prepared = true;
                $this->_debug_enabled = (bool) ((isset($_SERVER['HTTP_DEBUG']) && $_SERVER['HTTP_DEBUG'] == 'true') || (isset($conf['enabled']) && $conf['enabled']));
                if ($this->_debug_enabled) {

                    $this->_logger = \Ria\Log::getInstance();
                    if (isset($_SERVER['HTTP_DEBUG_SECTION']) && $_SERVER['HTTP_DEBUG_SECTION']) $this->_debug_section = $_SERVER['HTTP_DEBUG_SECTION'];
                    elseif (isset($conf['section']) && $conf['section']) $this->_debug_section = $conf['section'];

                    $this->_logs_name = 'rest_debug_' . $this->_debug_section;
                    if (isset($conf['logs_path']) && $conf['logs_path']) $this->_logs_path = $conf['logs_path'];

                    $GLOBALS['trace'] = array();
                    $GLOBALS['trace']['start'] = microtime(true);
                    
                    $this->_log($this->_logs_separator . date('Y-m-d H:i:s') . $this->_logs_parts_separator);
                    // pecl install pecl_http
                    $this->log(\http_get_request_headers(), 'request_headers');
                    $this->log($_SERVER, '$_SERVER');
                    $this->log($_REQUEST, '$_REQUEST');
                }
                return $this;
            } catch (\RestException $e) {
                //temporary plug =(
                $this->_sendData($e->getCode(), json_encode(array('error' => $e->getMessage())));
                die;
//                $this->_restler_obj->handleError($e->getCode(), $e->getMessage());
            }
        }

        private function _log($_data) {
            $this->_logger->log($_data, $this->_logs_name, $this->_logs_path);
        }

        private function _getTraceTimePointName() {
            return $this->_trace_time_point_name_prefix . $this->_trace_time_point_id;
        }

        private function _makeTraceTimePoint() {
            $this->_trace_time_point_id++;
            return $GLOBALS['trace'][$this->_getTraceTimePointName()] = microtime(true);
        }

        public function log($_data, $_name = '') {
            if ($this->_debug_enabled) {
                $microtime = $this->_makeTraceTimePoint();
                $pdata = ($_name ? $_name . ' | ' : ' ') . $this->_getTraceTimePointName() . ': ' . $microtime . $this->_logs_parts_separator . var_export($_data, true) . $this->_logs_parts_separator;
                $this->_log($pdata."\n");
            }
            return $this;
        }

        public function finish() {
            if ($this->_debug_enabled) {
                $start = $GLOBALS['trace']['start'];
                unset($GLOBALS['trace']['start']);
                $msg = 'microtime trace result' . $this->_logs_parts_separator . 'script time : ' . (microtime(true) - $start) . "\n\n";
                foreach ($GLOBALS['trace'] as $key => $value) $msg .= $key . ' : ' . ($value - $start) . "\n";
                $msg .= $this->_logs_parts_separator;
                $this->_log($msg);
            }
            return $this;
        }

//--- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- ---
        //temporary plug for \RestException style support
        private function _setStatus($_code) {
            header($_SERVER['SERVER_PROTOCOL'] . ' ' . $_code . ' ' . $this->_codes[strval($_code)]);
        }

        private function _sendData($_code, $_data) {
            $this->_setStatus($_code);
            \Ria\Headers::getInstance()->handle();
            die($_data);
        }

    }

//--- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- ---
}