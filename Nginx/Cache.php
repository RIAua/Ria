<?php

namespace Ria\Nginx {

    /**
     *
     * @author AGvin
     */
    class Cache {

        private $_section = '';
//        private $_section_prefix = 'cache_del_';
        private $_domain = '';

        /**
         * setup section for deleting cache
         * 
         * @param string $_section
         * @return $this 
         */
        public function setSection($_section = '') {
            if ($_section) $this->_section = $_section;
            return $this;
        }

//        /**
//         * setup section for deleting cache
//         * 
//         * @param string $_section_prefix
//         * @return $this 
//         */
//        public function setSectionPrefix($_section_prefix = '') {
//            if ($section) $this->_section_prefix = $_section_prefix;
//            return $this;
//        }

        /**
         * setup domain
         * 
         * @param string $_domain
         * @return $this 
         */
        public function setDomain($_domain = '') {
            if ($_domain) $this->_domain = $_domain;
            return $this;
        }

        /**
         * delete cahce 
         * 
         * @param array $uri 
         */
        public function removeCache($uri) {
            if ($this->_section) {
                if (!is_array($uri)) $uri = array($uri);
                $all_uries = '';
//                foreach ($uri as $item) $all_uries .= '"' . $this->_domain . $this->_section_prefix.$this->_section . '/'. $item . '" kd,cv' . "\n";
                foreach ($uri as $item) $all_uries .= '"' . $this->_domain . $this->_section . '/'. $item . '" kd,cv' . "\n";
                $all_uries = trim($all_uries);
                
//                echo "\n<hr/><pre>\n";
//                print_r($all_uries);
//                echo "\n</pre><hr/>\n";
//                
                exec("wget -q -b -nv -o /dev/null -O /dev/null $all_uries >/dev/null 2>&1");
                return true;
            }
            return false;
        }

    }

}