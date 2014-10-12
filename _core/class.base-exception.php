<?php
/**
 * class BaseException
 */

/**
 * class BaseException
 */
abstract class BaseException extends Exception
{
    protected $level;

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
        $message = sprintf($setting->message, $custom_message);
        $level = (false === empty($setting->level) ? $setting->level : 'notice');
        $this->setLevel($level);

        parent::__construct($message, $setting->code);

        set_exception_handler(array($this, 'errorHandler'));
    }

    /**
     * set exception level
     *
     * @param string $level - level
     *
     * @return null
     */
    public function setLevel($level)
    {
        $this->level = $level;
    }

    /**
     * get exception level
     *
     * @return string
     */
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * get messages
     *
     * @return null
     */
    public function getMessages()
    {
        $content = sprintf(
            '(%s) File: (%s), Line (%s): %s',
            $this->getCode(),
            $this->getFile(),
            $this->getLine(),
            $this->getMessage()
        );
        $content .= "\n" . $this->getTraceAsString();

        return $content;
    }

    /**
     * errorHandler - error handler
     *
     * @param object $ex - exception
     *
     * @return null
     */
    public function errorHandler($ex)
    {
        $logger = new Logger;

        call_user_func_array(
            array($logger, $this->level),
            array($this->getMessages())
        );
    }
}
?>