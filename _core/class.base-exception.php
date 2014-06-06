<?php
/**
 * class BaseException
 */

/**
 * class BaseException
 */
abstract class BaseException extends Exception
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
        $message = sprintf($setting->message, $custom_message);
        parent::__construct($message, $setting->code);

        // always returns 500
        http_response_code(500);

        set_exception_handler(array('BaseException', 'errorHandler'));
    }

    /**
     * errorHandler - error handler
     *
     * @param object $ex - exception
     *
     * @return null
     */
    public static function errorHandler($ex)
    {
        $logger = new Logger;
        $content = sprintf(
            '(%s) File: (%s), Line (%s): %s',
            $ex->getCode(),
            $ex->getFile(),
            $ex->getLine(),
            $ex->getMessage()
        );
        $content .= "\n" . $ex->getTraceAsString();
        $logger->notice($content);

        if ('production' !== Application::getEnv()) {
            echo '<pre>' . $content . '</pre>';
        }

        // TODO: redirect to error page
    }
}
?>