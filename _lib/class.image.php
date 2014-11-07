<?php
/**
 * class Image
 */

namespace Ageis;

/**
 * class Image
 */
class Image
{
    const ORIENTATION_LANDSCAPE = 'landscape';
    const ORIENTATION_PORTRAIT  = 'portrait';

    public $image;
    public $basename;
    public $image_type;
    public $extension;
    protected $map_extensions = array(
        IMAGETYPE_JPEG => 'jpg',
        IMAGETYPE_GIF  => 'gif',
        IMAGETYPE_PNG  => 'png'
    );

    /**
     * load image
     *
     * @param string $filename - source filename
     *
     * @return this
     */
    public function load($filename)
    {
        if (is_readable($filename) && is_file($filename)) {
            $image_info = getimagesize($filename);
            $this->image_type = $image_info[2];
            $this->extension = $this->map_extensions[$this->image_type];
            $basename = basename($filename);
            $this->basename = preg_replace('/(.*)\.\w{1,4}$/', '$1', $basename);

            if ($this->image_type == IMAGETYPE_JPEG) {
                $this->image = imagecreatefromjpeg($filename);
            }
            else if ($this->image_type == IMAGETYPE_GIF) {
                $this->image = imagecreatefromgif($filename);
            }
            else if ($this->image_type == IMAGETYPE_PNG) {
                $this->image = imagecreatefrompng($filename);
            }
        }

        return $this;
    }

    /**
     * save image
     *
     * @param string $filename    - destination filename
     * @param int    $compression - compression ratio
     *
     * @return none
     */
    public function save($filename, $compression = 100)
    {
        $dir = dirname($filename);

        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        if (isset($this->image)) {
            if ($this->image_type == IMAGETYPE_JPEG) {
                imagejpeg($this->image, $filename, $compression);
            }
            elseif ($this->image_type == IMAGETYPE_GIF) {
                imagegif($this->image, $filename);
            }
            elseif ($this->image_type == IMAGETYPE_PNG) {
                imagepng($this->image, $filename);
            }
        }

        return $this;
    }

    /**
     * output image
     *
     * @return null
     */
    public function output()
    {
        if (isset($this->image)) {

            if ($this->image_type == IMAGETYPE_JPEG) {    // 2
                imagejpeg($this->image);
            }
            elseif ($this->image_type == IMAGETYPE_GIF) { // 1
                imagegif($this->image);
            }
            elseif ($this->image_type == IMAGETYPE_PNG) { // 3
                imagepng($this->image);
            }
        }
    }

    /**
     * get current image width
     *
     * @return int
     */
    public function getWidth()
    {
        if ($this->image) {
            return imagesx($this->image);
        }
        else {
            return 0;
        }
    }

    /**
     * get current image height
     *
     * @return int
     */
    public function getHeight()
    {
        if ($this->image) {
            return imagesy($this->image);
        }
        else {
            return 0;
        }

    }

    /**
     * resize to a fixed height
     *
     * @param int $height - height
     *
     * @return this
     */
    public function resizeToHeight($height)
    {

        $ratio = $height / $this->getHeight();
        $width = $this->getWidth() * $ratio;

        return $this->resize($width, $height);
    }

    /**
     * resize to a fixed width
     *
     * @param int $width - width
     *
     * @return this
     */
    public function resizeToWidth($width)
    {
        $ratio = $width / $this->getWidth();
        $height = $this->getheight() * $ratio;

        return $this->resize($width, $height);
    }

    /**
     * scale
     *
     * @param float $scale - scale
     *
     * @return this
     */
    public function scale($scale)
    {
        $width = $this->getWidth() * $scale / 100;
        $height = $this->getheight() * $scale / 100;

        return $this->resize($width, $height);
    }

    /**
     * resize
     *
     * @param int $width  - image width
     * @param int $height - image height
     *
     * @return this
     */
    public function resize($width, $height)
    {
        if (isset($this->image)) {
            $new_image = imagecreatetruecolor($width, $height);

            imagecopyresampled($new_image, $this->image, 0, 0, 0, 0, $width, $height, $this->getWidth(), $this->getHeight());
            $this->image = $new_image;
        }

        return $this;
    }

    /**
     * get orientation
     *
     * @return string - portrait/landscape
     */
    public function getOrientation()
    {
        $h = $this->getHeight();
        $w = $this->getWidth();

        if ($h > $w) {
            return self::ORIENTATION_PORTRAIT;
        }
        else {
            return self::ORIENTATION_LANDSCAPE;
        }
    }
}
?>