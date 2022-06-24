<?php

/*
 *  (c) Rogério Adriano da Silva <rogerioadris.silva@gmail.com>
 */

namespace Naitzel\SilexCrud\Twig;

use Symfony\Component\HttpFoundation\Request;

/**
 * Class AssetTwigFunction.
 *
 * http://twig.sensiolabs.org/doc/advanced.html#creating-an-extension
 */
class AssetTwigFunction extends TwigContainerAware
{
    public function getName()
    {
        return 'asset';
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('asset', array($this, 'find')),
        );
    }

    public function find($asset)
    {
        $request = $this->get('request');
        $url = '';
        if ($request instanceof Request) {
            $url = $request->getBaseUrl();
        }

        try {
            $parameters = $this->get('composer');

            $version = $parameters['version'];
        } catch (\InvalidArgumentException $e) {
            $version = '0.0.1';
        }

        return sprintf('%s/%s%sv=%s', $url, $asset, strpos($asset, '?') === false ? '?' : '&', $version);
    }
}
