<?php

/**
 * This File is part of the Thapp\Jmg\Controller package
 *
 * (c) Thomas Appel <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\Jmg\Controller;

use \Symfony\Component\HttpFoundation\Request;
use \Thapp\JitImage\Resolver\ResolverInterface;
use \Selene\Components\Routing\Controller\Controller;
use \Thapp\JitImage\Resolver\ParameterResolverInterface;
use \Thapp\JitImage\Controller\Traits\ImageControllerTrait;

/**
 * @class Controller
 * @package Thapp\Jmg\Controller
 * @version $Id$
 */
class ImageController extends Controller
{
    use ImageControllerTrait {
        ImageControllerTrait::getImage as private getImageAction;
    }

    /**
     * Constructor.
     *
     * @param ResolverInterface $pathResolver
     * @param ParameterResolverInterface $imageResolver
     */
    public function __construct(ResolverInterface $pathResolver, ParameterResolverInterface $imageResolver)
    {
        $this->setPathResolver($pathResolver);
        $this->setImageResolver($imageResolver);
    }

    /**
     * getImage
     *
     * @param Request $request
     * @param mixed $path
     * @param mixed $params
     * @param mixed $source
     * @param mixed $filter
     *
     * @return ImageResponse
     */
    public function getImage(Request $request, $path, $params, $source, $filter = null)
    {
        $this->setRequest($request);

        return $this->getImageAction($path, $params, $source, $filter);
    }
}
