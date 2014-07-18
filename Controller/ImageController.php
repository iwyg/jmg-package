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
use \Thapp\JitImage\Response\ImageResponse;
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
        ImageControllerTrait::getResource as private getResourceAction;
        ImageControllerTrait::getCached as private getCachedAction;
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
     * @param string $path
     * @param string $params
     * @param string $source
     * @param string $filter
     *
     * @return ImageResponse
     */
    public function getImage(Request $request, $path, $params, $source, $filter = null)
    {
        $this->setRequest($request);

        return $this->getImageAction($path, $params, $source, $filter);
    }

    /**
     * getAlias
     *
     * @param Request $request
     * @param string $path
     * @param string $alias
     * @param string $source
     *
     * @return Response
     */
    public function getAlias(Request $request, $path, $alias, $source)
    {
        $this->setRequest($request);
        list ($params, $filter) = $this->recipes->resolve($alias);

        return $this->getImageAction($path, $params, $source, $filter);
    }

    /**
     * getAlias
     *
     * @param Request $request
     * @param string $path
     * @param string $id
     *
     * @return Response
     */
    public function getCached(Request $request, $key, $path, $suffix)
    {
        $this->setRequest($request);

        return $this->getCachedAction($path, $key);
    }
}
