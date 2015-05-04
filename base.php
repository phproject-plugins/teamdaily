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
                $f3->route("GET /teamdaily/@id", "Plugin\Teamdaily\Controller->dashboard");
                $f3->route("GET /teamdaily", "Plugin\Teamdaily\Controller->dashboard");
        }

        /**
         * Check if plugin is installed
         * @return bool
         */
        public function _installed() {
                return true;
        }

}
