<?php

namespace Matthias\BundlePlugins;

class MainBundleService {

    private $loaderType;

    private $path;

    private $file;

    /**
     * @return mixed
     */
    public function getLoaderType()
    {
        return $this->loaderType;
    }

    /**
     * @param mixed $loaderType
     */
    public function setLoaderType($loaderType)
    {
        $this->loaderType = $loaderType;
    }

    /**
     * @return mixed
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param mixed $path
     */
    public function setPath($path)
    {
        $this->path = $path;
    }



    /**
     * @return mixed
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @param mixed $file
     */
    public function setFile($file)
    {
        $this->file = $file;
    }

}