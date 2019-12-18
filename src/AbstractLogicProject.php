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


use Ikarus\Logic\Model\Component\ComponentInterface;
use Ikarus\Logic\Model\Component\NodeComponentInterface;
use Ikarus\Logic\Model\Component\Socket\Type\TypeInterface;
use Ikarus\Logic\Model\Element\Scene\SceneElementInterface;
use Ikarus\Logic\Model\IdGen\IdentifierGeneratorInterface;
use Ikarus\Logic\Model\IdGen\UniqueStringIdentifierGenerator;
use Ikarus\Logic\Model\Package\PackageInterface;

abstract class AbstractLogicProject implements ProjectInterface
{
    /** @var IdentifierGeneratorInterface */
    private $identifierGenerator;
    /** @var TypeInterface[] */
    protected $socketTypes = [];
    /** @var NodeComponentInterface[] */
    protected $components = [];
    /** @var SceneElementInterface[] */
    protected $scenes = [];

    protected $topLevelScenes = [];

    /**
     * AbstractLogicProject constructor.
     * @param IdentifierGeneratorInterface $identifierGenerator
     */
    public function __construct(IdentifierGeneratorInterface $identifierGenerator = NULL)
    {
        $this->identifierGenerator = $identifierGenerator ?: new UniqueStringIdentifierGenerator();
    }


    /**
     * @inheritDoc
     */
    public function getIdentifierGenerator(): IdentifierGeneratorInterface
    {
        return $this->identifierGenerator;
    }

    /**
     * @inheritDoc
     */
    public function getSocketTypes(): array
    {
        return $this->socketTypes;
    }

    /**
     * Adds a new socket type to the project
     *
     * @param TypeInterface $type
     */
    public function addSocketType(TypeInterface $type) {
        $this->socketTypes[ $type->getName() ] = $type;
    }

    /**
     * Gets a socket type by string
     *
     * @param $type
     * @return TypeInterface|null
     */
    public function getSocketType($type): ?TypeInterface {
        return $this->socketTypes[(string)$type] ?? NULL;
    }

    /**
     * Removes a socket type from project.
     * Please note that removing socket types from the project may invalidate the model, if sockets with this type are in use!
     *
     * @param $type
     */
    public function removeSocketType($type) {
        if(isset($this->socketTypes[(string)$type]))
            unset($this->socketTypes[(string) $type]);
    }

    /**
     * Adds a package to the project
     *
     * @param PackageInterface $package
     */
    public function addPackage(PackageInterface $package) {
        foreach($package->getSocketTypes() as $socketType) {
            if($socketType instanceof TypeInterface)
                $this->addSocketType($socketType);
        }

        foreach($package->getComponents() as $component) {
            if($component instanceof ComponentInterface)
                $this->addComponent($component);
        }
    }

    /**
     * Removes a package
     *
     * @param PackageInterface $package
     */
    public function removePackage(PackageInterface $package) {
        foreach($package->getSocketTypes() as $socketType) {
            if($socketType instanceof TypeInterface)
                $this->removeSocketType( $socketType->getName() );
        }
        foreach($package->getComponents() as $component) {
            if($component instanceof ComponentInterface)
                $this->removeComponent($component->getName());
        }
    }

    /**
     * @inheritDoc
     */
    public function getComponents(): array
    {
        return $this->components;
    }

    /**
     * Adds a new component to the project
     *
     * @param ComponentInterface $component
     */
    public function addComponent(ComponentInterface $component) {
        $this->components[ $component->getName() ] = $component;
    }

    /**
     * Removes a component from project
     * Please note that this action may invalidate your model if there exists a node using the component
     *
     * @param $component
     */
    public function removeComponent($component) {
        if(is_string($component) && isset($this->components[$component]))
            unset($this->components[$component]);
        elseif(($idx = array_search($component, $this->components)) !== false)
            unset($this->components[$idx]);
    }

    /**
     * Gets the component
     *
     * @param $component
     * @return ComponentInterface|null
     */
    public function getComponent($component): ?ComponentInterface {
        return $this->components[ $component ] ?? NULL;
    }

    /**
     * @inheritDoc
     */
    public function getScenes(): array
    {
        return $this->scenes;
    }

    /**
     * Adds a scene to the project
     *
     * @param SceneElementInterface $newScene
     * @param bool $topLevel
     */
    public function addScene(SceneElementInterface $newScene, bool $topLevel = true) {
        if($newScene->getProject() === $this) {
            $this->scenes[ $newScene->getIdentifier() ] = $newScene;
            if($topLevel)
                $this->topLevelScenes[] = $newScene;
        }
    }

    /**
     * Gets a scene
     *
     * @param $scene
     * @return SceneElementInterface|null
     */
    public function getScene($scene): ?SceneElementInterface {
        return $this->scenes[$scene] ?? NULL;
    }

    /**
     * Removes a whole scene from the project
     *
     * @param SceneElementInterface|string $scene
     * @param bool $topLevelOnly
     */
    public function removeScene($scene, bool $topLevelOnly = false) {
        if($scene instanceof SceneElementInterface)
            $scene = $scene->getIdentifier();

        if(($idx = array_search($this->getScene($scene), $this->topLevelScenes)) !== false)
            unset($this->topLevelScenes[$idx]);

        if(!$topLevelOnly) {
            if(isset($this->scenes[$scene]))
                unset($this->scenes[$scene]);
        }
    }

    /**
     * @inheritDoc
     */
    public function isTopLevelScene(SceneElementInterface $sceneElement): bool
    {
        return in_array($sceneElement, $this->topLevelScenes);
    }
}