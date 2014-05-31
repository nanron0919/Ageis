<?php
/*
 * home controller
 */

/*
 * home controller
 */
class Controller_Home extends controller
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