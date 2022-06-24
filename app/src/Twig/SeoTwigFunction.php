<?php

/*
 *  (c) Rogério Adriano da Silva <rogerioadris.silva@gmail.com>
 */

namespace Naitzel\SilexCrud\Twig;

/**
 * Class AssetTwigFunction.
 *
 * http://twig.sensiolabs.org/doc/advanced.html#creating-an-extension
 */
class SeoTwigFunction extends TwigContainerAware
{
    public function getName()
    {
        return 'seo';
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('seo', array($this, 'getSeo')),
            new \Twig_SimpleFunction('seo_title', array($this, 'getTitle')),
            new \Twig_SimpleFunction('seo_description', array($this, 'getDescription')),
            new \Twig_SimpleFunction('seo_keyword', array($this, 'getKeyword')),
            new \Twig_SimpleFunction('seo_h1', array($this, 'getH1')),
        );
    }

    /**
     * @param  string $type
     * @param  string $default
     * @return string
     */
    public function getSeo($type, $default = null)
    {
        $data = $this->get('get_seo');
        if (is_array($data) && array_key_exists($type, $data)) {
            return $data[$type] ?: $default;
        }

        return $default;
    }

    /**
     * @param  string $default
     * @return string
     */
    public function getTitle($default = null)
    {
        return $this->getSeo('title', $default);
    }

    /**
     * @param  string $default
     * @return string
     */
    public function getDescription($default = null)
    {
        return $this->getSeo('description', $default);
    }

    /**
     * @param  string $default
     * @return string
     */
    public function getKeyword($default = null)
    {
        return $this->getSeo('keyword', $default);
    }

    /**
     * @param  string $default
     * @return string
     */
    public function getH1($default = null)
    {
        return $this->getSeo('h1', $default);
    }
}
