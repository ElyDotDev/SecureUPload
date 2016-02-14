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
 * Class Config
 *
 * Config common properties and methods.
 * Note: Just indexes defined in $config will be set.
 *
 * @author Alireza Dabiri Nejad <me@allii.ir>
 */
class Config
{
    /**
     * Used default config when given config value is'nt valid
     *
     * @var array Store defaults config
     */
    protected $default_config = array();

    /**
     * @var array Store main config values
     */
    protected $config = array();

    /**
     * @var array Store validation rules for each config values
     */
    protected $config_validation_rules = array();

    /**
     * Config constructor.
     *
     * @param array $config_array Optional give an array of config id and values
     */
    public function __construct($config_array = array())
    {
        $this->setArray($config_array);
    }

    /**
     * Set a config index with it's id and value
     *
     * @param string $config_id
     * @param string $config_value
     *
     * @return $this
     */
    public function set($config_id, $config_value)
    {
        if (isset($this->config[$config_id])) {
            $this->config[$config_id] = $config_value;
        }

        return $this;
    }

    /**
     *  Set an array as config
     *
     * @param array $config_array
     *
     * @return $this
     */
    public function setArray($config_array)
    {
        if (is_array($config_array) && ! empty($config_array)) {
            foreach ($config_array as $config_id => $config_value) {
                if (isset($this->config[$config_id])) {
                    $this->config[$config_id] = $config_value;
                }
            }
        }

        return $this;
    }

    /**
     * Get a config index by it's id
     *
     * @param string $config_id
     *
     * @return string
     */
    public function get($config_id)
    {
        if (isset($this->config[$config_id])) {
            return $this->config[$config_id];
        }

        return '';
    }

    /**
     * Parse config.
     *
     * Parse means, Check config indexes validation and if not valid replace with default config
     *
     * @return $this
     */
    public function parse()
    {
        $Parser       = new Parser($this->config, $this->config_validation_rules, $this->default_config);
        $this->config = $Parser->parse();

        return $this;
    }

    /**
     * Just pretty print config
     *
     * @codeCoverageIgnore
     */
    public function printAll()
    {
        echo '<pre>';
        print_r($this->config);
        echo '</pre>';
    }
}