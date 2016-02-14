<?php
/**
 * This file is part of the SecureUPload package.
 *
 * (c) Alireza Dabiri Nejad <me@allii.ir>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Alirdn\SecureUPload\Config;

/**
 * Config Parser
 *
 * Parse a config. This means, every config index value will be validated and if
 * not valid, replace it value with default given value.
 *
 * @author Alireza Dabiri Nejad <me@allii.ir>
 */
class Parser
{
    /**
     * @var array Config array
     */
    private $config = array();

    /**
     * @var array Config Validation Rule
     */
    private $validation_rules = array();

    /**
     * @var array Default config
     */
    private $default_config = array();

    /**
     * Parser constructor.
     *
     * @param array $config           Array of given config
     * @param array $validation_rules Array of default config
     * @param array $default_config   Array of config validation rules
     */
    public function __construct($config, $validation_rules, $default_config)
    {
        if (is_array($config)) {
            $this->config = $config;
        }

        if (is_array($validation_rules)) {
            $this->validation_rules = $validation_rules;
        }

        if (is_array($default_config)) {
            $this->default_config = $default_config;
        }
    }

    /**
     * Parse an Config and return parsed config as array
     *
     * @return array
     */
    public function parse()
    {
        $invalids = $this->check();
        if ( ! empty($invalids)) {
            foreach ($invalids as $config_id => $value) {
                $this->config[$config_id] = $this->default_config[$config_id];
            }
        }

        return $this->config;
    }

    /**
     * Just add check_ to uppercase first character of each validation rule name
     *
     * @param string $validation_rule Validation rule given in validation rules
     *
     * @return string Validation Method
     */
    private function getValidationMethod($validation_rule)
    {
        return 'check' . str_replace(' ', '', ucwords(str_replace('_', ' ', $validation_rule)));
    }

    /**
     * Check validation of every config index value
     *
     * Multiple validation rules supported
     * Validation rules could be separated by `|`
     * Validation rules extra data could be separated by `:`
     *
     * @return array Array of invalid config indexes
     */
    public function check()
    {
        $invalids = array();
        foreach ($this->config as $config_id => $config_value) {
            if (isset($this->validation_rules[$config_id])) {
                if (strpos($this->validation_rules[$config_id], '|') !== false) {
                    $validation_rules = explode('|', $this->validation_rules[$config_id]);
                    foreach ($validation_rules as $validation_rule) {
                        if (strpos($validation_rule, ':') !== false) {
                            $validation_method_array = explode(':', $validation_rule);
                            $validation_method       = $this->getValidationMethod($validation_method_array[0]);
                            $is_valid                = $this->$validation_method($config_value, $validation_method_array[1]);
                        } else {
                            $validation_method = $this->getValidationMethod($validation_rule);
                            $is_valid          = $this->$validation_method($config_value);
                        }
                        if ( ! ($is_valid === true)) {
                            $invalids[$config_id][] = $this->validation_rules[$config_id];
                        }
                    }
                } else {
                    if (strpos($this->validation_rules[$config_id], ':') !== false) {
                        $validation_method_array = explode(':', $this->validation_rules[$config_id]);
                        $validation_method       = $this->getValidationMethod($validation_method_array[0]);
                        $is_valid                = $this->$validation_method($config_value, $validation_method_array[1]);
                    } else {
                        $validation_method = $this->getValidationMethod($this->validation_rules[$config_id]);
                        $is_valid          = $this->$validation_method($config_value);
                    }
                    if ( ! ($is_valid === true)) {
                        $invalids[$config_id] = $this->validation_rules[$config_id];
                    }
                }

            }
        }

        return $invalids;
    }

    /**
     * Validation Method: Required
     *
     * @param string $value
     *
     * @return bool Validation status
     */
    private function checkRequired($value)
    {
        return ! empty($value);
    }

    /**
     * Validation Method: In Array
     *
     * @param string $value
     * @param string $string_array
     *
     * @return bool Validation status
     */
    private function checkInArray($value, $string_array)
    {
        $array = explode(',', $string_array);

        return in_array($value, $array);
    }

    /**
     * Validation Method: Array
     *
     * @param string $value
     *
     * @return bool Validation status
     */
    private function checkArray($value)
    {
        return is_array($value);
    }

    /**
     * Validation Method: Numeric
     *
     * @param string $value
     *
     * @return bool Validation status
     */
    private function checkNumeric($value)
    {
        return is_numeric($value);
    }
}