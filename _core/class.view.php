<?php
/**
 * View
 */

/**
 * View
 */
final class View
{
    /**
     * display content of view
     *
     * @param string $view_name assign view file (do not need .php)
     * @param array  $data      data binded
     * @param bool   $return    needs return result
     *
     * @access public
     * @return string
     */
    public static function display($view_name, $data = array(), $return = false)
    {
        // output array each item into a single variable.
        foreach ($data as $key => $item) {
            $$key = $item;
        }

        $filename = preg_replace('/(\/)?([\w-]+)$/', '$1view.$2.php', $view_name);
        $fullpath = APP_ROOT . '/_views/' . $filename;

        if (true === is_readable($fullpath)) {

            if (true === is_bool($return) && false === $return) {
                include $fullpath;
            }
            else {
                ob_start();
                include $fullpath;
                $buffer = ob_get_contents();
                ob_end_clean();

                return $buffer;
            }

        }
        else {
            $ex = Config::exception()->action->ex5001;
            throw new ActionException($ex, $view_name);
        }
    }
}
?>
