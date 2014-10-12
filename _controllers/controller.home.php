<?php
/*
 * home controller
 */

/*
 * home controller
 */
class ControllerHome extends controller
{
    /**
     * method index
     *
     * @param array $args - request variables
     *
     * @return null
     */
    public function index($args)
    {
        $data = array(
            'lang' => i18n::line('home'),
        );

        View::display('index', $data);
    }

}
?>