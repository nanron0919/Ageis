<?php
/**
 * class uploader
 */

/**
 * class uploader
 */
class Uploader
{
    protected $config;

    /**
     * costructor
     */
    public function __construct()
    {
        $this->config = Config::upload();
    }

    /**
     * post - post file
     *
     * @param string $name - file post key name
     *
     * @return string - file name
     */
    public function post($name)
    {
        $upload_file_name = '';

        if (false === empty($_FILES[$name])) {
            // definition
            $upload_file = $_FILES[$name];
            $filename = $this->nameFile($upload_file['name']);

            // mkdir
            File::mkdir(APP_ROOT . '/' . $this->config->root);

            // checking file is acceptable
            $filter = $this->checkAcceptable($upload_file);

            // combine new file name and move to new home
            $app_tmp_root = sprintf('%s/%s/%s', APP_ROOT, $this->config->root, $filter->folder);
            $old_file = $upload_file['tmp_name'];
            $new_file = $app_tmp_root . '/' . $filename;

            if (true === File::move($old_file, $new_file)) {
                $upload_file_name = $new_file;
            }
        }
        else {
            throw new ActionException(Config::exception()->upload->ex6001);
        }

        return $upload_file_name;
    }

    /**
     * checkAcceptable - check this file is acceptable
     *
     * @param string $upload_file - upload file
     *
     * @return object
     */
    public function checkAcceptable($upload_file)
    {
        $type_parts = explode('/', $upload_file['type']);
        $file_type  = end($type_parts);
        $file_name  = $upload_file['name'];
        $result     = new stdClass;

        foreach ($this->config->file_filter as $key => $filter) {
            $keys = explode('|', $key);
            $filter_name = '';

            if (true === in_array($file_type, $keys)) {
                $filter_name = $filter;
            }

            if (false === empty($filter_name) && false === empty($this->config->filters->$filter_name)) {
                $accept_file_types = $this->config->filters->$filter_name->options->accept_file_types;

                if (0 < preg_match($accept_file_types, $file_name, $matches)) {
                    $result = $this->config->filters->$filter_name;
                    break;
                }
            }
        }

        if (true === empty($result)) {
            throw new ActionException(Config::exception()->upload->ex6002);
        }

        return $result;
    }

    /**
     * nameFile - name file
     *
     * @param string $filename - file name
     *
     * @return string
     */
    public function nameFile($filename)
    {
        $parts = explode('.', $filename);
        $name = $this->getRandomName();
        $extension = end($parts);

        return sprintf('%s.%s', $name, $extension);
    }

    /**
     * get random name
     *
     * @return string - name
     */
    public function getRandomName()
    {
        return $this->getRandomString(10) . sha1(time() . rand(1, 1000));
    }

    /**
     * get random string
     *
     * @param int $len - how long of string what you get
     *
     * @return string - name
     */
    public function getRandomString($len)
    {
        $str = '';

        for ($i = 0; $i < $len; $i++) {
            $upper_case = (1 === rand(1, 3));
            $temp_str = chr(rand(97, 122));
            $str .= (true === $upper_case ? strtoupper($temp_str) : $temp_str);
        }

        return $str;
    }
}

?>