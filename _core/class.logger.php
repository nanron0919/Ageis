<?php
/**
 * class Logger
 */

namespace Ageis;

/**
 * class Logger
 */
final class Logger
{
    public function __call($name, $arguments)
    {
        // get config
        $config_logger = Config::logger();
        $level = (true === in_array($name, $config_logger->levels) ? $name : 'notice');

        // format content
        $content = date('H:i:s') . ' - ' . (false === empty($arguments[0]) ? $arguments[0] : '');

        // format file name
        $today = date('Ymd');
        $filename = $level . '.log';
        $file_path = sprintf('%s/%s/%s', $config_logger->root, $today, $filename);
        File::write($file_path, $content);
    }
}
?>