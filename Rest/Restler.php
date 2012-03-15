<?php

namespace Ria\Rest {

    /**
     * Description of RestlerEx
     *
     * @author AGvin
     */
    class Restler extends \Restler {

        /**
         * Parses the request data and returns it
         * @return array php data
         */
        protected function getRequestData() {
            try {
                $r = file_get_contents('php://input');

                \Ria\Debug\Log::getInstance()->log($r, 'php_input');

                if (is_null($r)) return $_GET;
                $r = $this->request_format->decode($r);
                return is_null($r) ? array() : $r;
            } catch (RestException $e) {
                $this->handleError($e->getCode(), $e->getMessage());
            }
        }

        /**
         * Encodes the response in the prefered format
         * and sends back
         * @param $data array php data
         */
        public function sendData($data) {
            $data = $this->response_format->encode($data, !$this->production_mode);
            $post_process = '_' . $this->service_method . '_' . $this->response_format->getExtension();
            if (isset($this->service_class_instance) &&
                method_exists($this->service_class_instance, $post_process)) {
                $data = call_user_func(array($this->service_class_instance,
                    $post_process), $data);
            }
//            header("Cache-Control: no-cache, must-revalidate");
//            header("Expires: 0");
//            header('Content-Type: ' . $this->response_format->getMIME());
//            header("X-Powered-By: Luracast Restler v" . self::VERSION);
            \Ria\Headers::getInstance()->handle();
            \Ria\Debug\Log::getInstance()->log($data, 'response data')->finish();
            die($data);
        }

    }

}