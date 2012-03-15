<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Ria {

    /**
     *
     * @author AGvin
     */
    class Log {

        private static $_instance = null;
//        private $_config = array();

        public static function getInstance() {
            if (null === self::$_instance) self::$_instance = new self();
            return self::$_instance;
        }
        
        public function log($message,$log_name = 'main',$log_path = '/tmp' ) {
            file_put_contents($log_path.'/'.$log_name.'.log', $message , FILE_APPEND);
        }

    }

}