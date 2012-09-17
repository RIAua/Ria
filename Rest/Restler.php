<?php

namespace Ria\Rest {

    /**
     * Description of RestlerEx
     *
     * @author AGvin
     */
    class Restler extends \Restler {

        /**
         * Items ranges if headers contain Range header (http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.35)
         * @var string
         * @example '0-24' if header contain 'Range: items=0-24'
         */
        public $request_range_items;

        /**
         * An initialize function to allow use of the restler error generation
         * functions for pre-processing and pre-routing of requests.
         */
        public function init() {
            if (empty($this->format_map)) {
                $this->setSupportedFormats('JsonFormat');
            }
            $this->url = $this->getPath();
            $this->request_method = $this->getRequestMethod();
            $this->response_format = $this->getResponseFormat();
            $this->request_format = $this->getRequestFormat();
            $this->request_range_items = $this->getRequestRangeItems();
            if (is_null($this->request_format)) {
                $this->request_format = $this->response_format;
            }
            if ($this->request_method == 'PUT' || $this->request_method == 'POST') {
                $this->request_data = $this->getRequestData();
            }
        }

        protected function mapUrlToMethod() {
            if (!isset($this->routes[$this->request_method])) {
                return array();
            }
            $urls = $this->routes[$this->request_method];
            if (!$urls) {
                return array();
            }

            $found = false;
            $this->request_data += $_GET;
            $params = array(
                    'request_data' => $this->request_data
                    'request_range_items' => $this->request_range_items
            );
            $params += $this->request_data;
            $lc = strtolower($this->url);
            foreach ($urls as $url => $call) {
                //echo PHP_EOL.$url.' = '.$this->url.PHP_EOL;
                $call = (object) $call;
                if (strstr($url, ':')) {
                    $regex = preg_replace('/\\\:([^\/]+)/', '(?P<$1>[^/]+)', preg_quote($url));
                    if (preg_match(":^$regex$:i", $this->url, $matches)) {
                        foreach ($matches as $arg => $match) {
                            if (isset($call->arguments[$arg])) {
                                //flog("$arg => $match $args[$arg]");
                                $params[$arg] = $match;
                            }
                        }
                        $found = true;
                        break;
                    }
                } else if ($url == $lc) {
                    $found = true;
                    break;
                }
            }
            if ($found) {
                //echo PHP_EOL."Found $url ";
                //print_r($call);
                $p = $call->defaults;
                foreach ($call->arguments as $key => $value) {
                    //echo "$key => $value \n";
                    if (isset($params[$key])) {
                        $p[$value] = $params[$key];
                    }
                }
                $call->arguments = $p;
                return $call;
            }
        }

        /**
         * Return items ranges if headers contain Range header
         * @return string items range (http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.35)
         * @example 'Range: items=0-24' will return string '0-24'
         */
        protected function getRequestRangeItems() {
            if (isset($_SERVER['HTTP_RANGE'])) {
                $items = explode('=', $_SERVER['HTTP_RANGE']);
                if (strtolower(trim($items[0])) == 'items') return trim($items[1]);
            } else return null;
        }

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