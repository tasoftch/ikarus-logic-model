<?php
/**
 * BSD 3-Clause License
 *
 * Copyright (c) 2019, TASoft Applications
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 *  Redistributions of source code must retain the above copyright notice, this
 *   list of conditions and the following disclaimer.
 *
 *  Redistributions in binary form must reproduce the above copyright notice,
 *   this list of conditions and the following disclaimer in the documentation
 *   and/or other materials provided with the distribution.
 *
 *  Neither the name of the copyright holder nor the names of its
 *   contributors may be used to endorse or promote products derived from
 *   this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
 * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
 * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 */

namespace Ikarus\Logic\Model;


use Ikarus\Logic\Model\Component\NodeComponentInterface;
use Ikarus\Logic\Model\Component\Socket\Type\TypeInterface;
use Ikarus\Logic\Model\Exception\ComponentNotFoundException;
use Ikarus\Logic\Model\Exception\SocketComponentNotFoundException;
use Ikarus\Logic\Model\Package\PackageInterface;
use TASoft\Collection\PriorityCollection;

class PriorityComponentModel extends AbstractComponentModel
{
    private $components;
    private $socketTypes;

    public function __construct()
    {
        $this->components = new PriorityCollection();
        $this->socketTypes = new PriorityCollection();
    }

    /**
     * Adds a component to the model
     *
     * @param NodeComponentInterface $component
     * @param int $priority
     */
    public function addComponent(NodeComponentInterface $component, int $priority = 0) {
        $this->components->add($priority, $component);
    }

    /**
     * Removes a component from model
     * NOTE: If later a node uses the removed component, any compilation or runtime will fail.
     *
     * @param $component
     */
    public function removeComponent($component) {
        if(is_object($component) && method_exists($component, 'getName'))
            $component = $component->getName();

        try {
            $this->components->remove(is_string($component) ? $this->getComponent($component) : $component);
        } catch (ComponentNotFoundException $exception) {
        }
    }

    /**
     * Adds a socket type to the model
     *
     * @param TypeInterface $socketType
     * @param int $priority
     */
    public function addSocketType(TypeInterface $socketType, int $priority = 0) {
        $this->socketTypes->add($priority, $socketType);
    }

    /**
     * Removes a socket type from model
     * NOTE: If later a node uses the removed socket type, any compilation or runtime will fail.
     *
     * @param $socketType
     */
    public function removeSocketType($socketType) {
        if(is_object($socketType) && method_exists($socketType, 'getName'))
            $socketType = $socketType->getName();
        try {
            $this->socketTypes->remove(is_string($socketType) ? $this->getSocketType($socketType) : $socketType);
        } catch (SocketComponentNotFoundException $exception) {
        }
    }

    /**
     * Adds components and socket types from a package to the model
     *
     * @param PackageInterface $package
     * @param int $priority
     */
    public function addPackage(PackageInterface $package, int $priority = 0) {
        foreach($package->getComponents() as $component)
            $this->addComponent($component, $priority);
        foreach($package->getSocketTypes() as $socketType)
            $this->addSocketType($socketType, $priority);
    }

    /**
     * Removes all components and socket types of a package from model
     *
     * @param PackageInterface $package
     */
    public function removePackage(PackageInterface $package) {
        foreach($package->getComponents() as $component)
            $this->removeComponent($component);
        foreach($package->getSocketTypes() as $socketType)
            $this->removeSocketType($socketType);
    }

    /**
     * @inheritDoc
     */
    public function getComponents(): array
    {
        return $this->components->getOrderedElements();
    }

    /**
     * @inheritDoc
     */
    public function getSocketTypes(): array
    {
        return $this->socketTypes->getOrderedElements();
    }
}