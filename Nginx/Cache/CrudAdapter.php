<?php

namespace Ria\Nginx\Cache {

    /**
     *
     * @author AGvin
     */
    class CrudAdapter extends \Ria\Nginx\Cache {
        private $_module;
        public function __construct($_module) {
//            echo "\n<hr/><pre>\n";
//            print_r($_section);
//            echo "\n</pre><hr/>\n";
            $this->_module = $_module;
            $this->setDomain('http://'.$_SERVER['HTTP_HOST'].'/')->setSection('cache_del_'.$_module);
        }
        /**
         *
         * @param mixed $_id_data - can be single id or ods arr
         * @return bool - true if success
         */
        public function deleteItemCache($_id_data) {
            return $this->removeCache($this->_module.'/'.$_id_data);
        }
    }

}