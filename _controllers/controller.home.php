<?php
/*
 * home controller
 */

namespace Ageis;

/*
 * home controller
 */
class ControllerHome extends Controller
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