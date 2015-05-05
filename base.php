<?php
/**
 * @package  teamdaily
 * @author   Ryan Bouche <rightbit@gmail.com>
 * @version  1.0.0
 */

namespace Plugin\Teamdaily;

class Base extends \Plugin {

        /**
         * Initialize the plugin
         */
        public function _load() {
                $f3 = \Base::instance();
                $f3->config(__DIR__ . DIRECTORY_SEPARATOR . "config.ini");
                $f3->route("GET /teamdaily/@id", "Plugin\Teamdaily\Controller->dashboard");
                $f3->route("GET /teamdaily", "Plugin\Teamdaily\Controller->dashboard");
                $this->_addNav('teamdaily', 'Daily Team Status', null, 'user');
        }

        /**
         * Check if plugin is installed
         * @return bool
         */
        public function _installed() {
                return true;
        }

}
