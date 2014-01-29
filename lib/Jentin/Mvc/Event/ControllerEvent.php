<?php
/*
 * This file is part of the Jentin framework.
 * (c) Steffen Zeidler <sigma_z@sigma-scripts.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Jentin\Mvc\Event;

use Jentin\Mvc\Controller\ControllerInterface;

/**
 * ControllerEvent.php
 * @author Steffen Zeidler <sigma_z@sigma-scripts.de>
 */
class ControllerEvent extends MvcEvent
{

    /**
     * @var ControllerInterface
     */
    protected $controller;


    /**
     * constructor
     *
     * @param ControllerInterface $controller
     */
    public function __construct(ControllerInterface $controller)
    {
        $this->controller = $controller;
    }


    /**
     * sets controller
     *
     * @param ControllerInterface $controller
     */
    public function setController(ControllerInterface $controller)
    {
        $this->controller = $controller;
    }


    /**
     * gets controller
     *
     * @return ControllerInterface
     */
    public function getController()
    {
        return $this->controller;
    }

}
