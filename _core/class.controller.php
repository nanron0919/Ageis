<?php
/**
 * base controller
 */
abstract class Controller
{
    const DEFAULT_METHOD = 'index';

    public $route;
    public $request_vars = array();

    /**
     * constructor
     *
     * @param object $app - application
     */
    public function __construct($route)
    {
        $this->route = $route;
        $this->request_vars = array(
            'params' => $route['params']
        );

        $this->_getRequest();
        // active language
        i18n::setActiveLanguage();
    }

    /**
     * load library
     *
     * @param string $name - module name
     */
    public function loadModule($name)
    {
        $fullpath = sprintf(FRAMEWORK_ROOT . '/_lib/class.%s.php');

        // check file exists and readable
        if (true === file_exists($fullpath)
            && true === is_readable($fullpath)
            && true === is_file($fullpath)
        ) {
            require_once($fullpath);
        }
        else {
            // TODO: throw exception
        }
    }

    /**
     * run this controller
     *
     * @param string $name - module name
     */
    final public function run()
    {
        $active_method = (false === empty($this->route['params']['method'])
            ? $this->route['params']['method']
            : self::DEFAULT_METHOD);

        try {
            if (true === method_exists($this, $active_method)) {
                $this->$active_method($this->request_vars);
            }
            else {
                $this->index($this->request_vars);
            }
        }
        catch (Exception $e) {
            Application::debug('something wrong');
        }
    }

    /**
     * display involve master page
     *
     * @param string $view_name - view namn
     * @param array  $data      - data
     *
     * @return null
     */
    protected function displayMaster($view_name, $data = array())
    {
        $data['master']  = i18n::line('master');
        Application::loadModel('account');
        $account = ModelAccount::getAccountSession();
        // check the user who is login
        $is_login = ModelAccount::isLogin();
        $data['master']['header']['menu'][3]['display'] = !$is_login;
        $data['master']['header']['menu'][4]['display'] = $is_login;
        $data['account'] = array(
            'id' => (false === empty($account->account_id) ? $account->account_id : ''),
            'lang' => I18N_ACTIVE
        );
        $data['content'] = View::display($view_name, $data, true);
        View::display('master', $data);
    }

    //////////////////////
    // abstract methods //
    //////////////////////
    abstract public function index($args);

    /////////////////////
    // private methods //
    /////////////////////

    /**
     * get request parameters
     *
     * @return null
     */
    private function _getRequest()
    {
        $map = array(
            'get'    => $_GET,
            'post'   => $_POST,
            'cookie' => $_COOKIE
        );

        foreach ($map as $key => $request) {
            foreach ($request as $name => $val) {
                $this->request_vars[$key][$name] = call_user_func('Http::' . $key, $name);
            }
        }
    }
}
?>
