<?php

namespace Ria\Headers {

    /**
     * Some base pagination (now using for building sql limit & offset )
     *
     * @author AGvin
     */
    class Pagination {

        /**
         *
         * @var \Ria\Headers\Pagination 
         */
        private static $_instance = null;

        /**
         * items limit
         * @var int 
         */
        private $_limit = 10;

        /**
         * items offset
         * @var int 
         */
        private $_offset = 0;

        /**
         * Get class instance
         * @return \Ria\Headers\Pagination 
         */
        public static function getInstance() {
            if (null == self::$_instance) self::$_instance = new self();
            return self::$_instance;
        }

        /**
         * parse range like as 1-101 (from 1 to 101 item)
         * @param string $_items_range "(<int items_from>-<int items_to>)"
         * @return \Ria\Headers\Pagination 
         */
        public function parseRange($_items_range) {
//            (\d)?(?:-)?(\d)?
            if (false === strpos($_items_range, ',')) {
                $params = explode('-', $_items_range);
                if (isset($params[0]) && $params[0]) $this->_offset = (int) $params[0];
                if (isset($params[1]) && $params[1]) $this->_limit = ((int) $params[1]) - $this->_offset;
                if ($this->_limit < 0) $this->_limit = 0;
            }
            return $this;
        }

        /**
         * get items offset
         * @return int
         */
        public function getOffset() {
            return $this->_offset;
        }

        /**
         * get items limit
         * @return int
         */
        public function getLimit() {
            return $this->_limit;
        }

        /**
         * Return sql Limit-offset ending
         * 
         * @return string sql limit && offset ending
         */
        public function getSqlPagination() {
            return ($this->_offset ? ' OFFSET ' . $this->_offset : '')
                . ($this->_limit ? ' LIMIT ' . $this->_limit : '');
        }

    }

}