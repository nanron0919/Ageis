<?php
/**
 * model exception
 */

/**
 * model exception
 */
class ModelException extends BaseException
{
    /**
     * constructor
     *
     * @param object $setting        - error setting
     * @param string $custom_message - custom message
     *
     * @return null
     */
    public function __construct($setting, $custom_message = '')
    {
        parent::__construct($setting, $custom_message);
    }
}

?>