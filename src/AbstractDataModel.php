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


use Ikarus\Logic\Model\Data\Connection\ConnectionDataModelInterface;
use Ikarus\Logic\Model\Data\DataModelInterface;
use Ikarus\Logic\Model\Data\IdentifiedDataModelInterface;
use Ikarus\Logic\Model\Data\Node\NodeDataModelInterface;
use Ikarus\Logic\Model\Data\Scene\SceneDataModelInterface;
use Ikarus\Logic\Model\Exception\DuplicateIdentifierException;
use Ikarus\Logic\Model\Exception\InvalidReferenceException;

abstract class AbstractDataModel implements DataModelInterface
{
    /** @var SceneDataModelInterface[] */
    private $sceneDataModels = [];

    /** @var NodeDataModelInterface[][] */
    private $nodes = [];

    /** @var ConnectionDataModelInterface[][] */
    private $connections = [];

    /** @var array */
    protected $nodeSceneMap = [];

    /** @var array  */
    private $idRegister = [];

    /**
     * Checks, if an object with a given identifier already exists
     *
     * @param $identifier
     * @return bool
     */
    public function hasIdentifier($identifier) {
        return isset($this->idRegister[$identifier]);
    }

    /**
     * @param $identifier
     * @return SceneDataModelInterface|NodeDataModelInterface|null
     */
    public function getDataModelByIdentifier($identifier) {
        return $this->idRegister[$identifier] ?? NULL;
    }

    /**
     * Adds a new scene data model
     *
     * @param SceneDataModelInterface $model
     */
    public function addSceneDataModel(SceneDataModelInterface $model) {
        if($existingModel = $this->getDataModelByIdentifier($model->getIdentifier())) {
            $e = new DuplicateIdentifierException("Scene with id %s already exists", DuplicateIdentifierException::CODE_DUPLICATE_SYMBOL, NULL, $model->getIdentifier());
            $e->setModel($this);
            $e->setProperty( $existingModel );
            $e->setDuplicate( $model );
            throw $e;
        }
        $this->sceneDataModels[ $model->getIdentifier() ] = $model;
        $this->idRegister[$model->getIdentifier()] = $model;
    }

    /**
     * Removes a scene data model
     *
     * @param $model
     */
    public function removeSceneDataModel($model) {
        if($model instanceof SceneDataModelInterface)
            $model = $model->getIdentifier();

        if(isset($this->sceneDataModels[$model]))
            unset($this->sceneDataModels[$model]);
        if(isset($this->nodes[$model]))
            unset($this->nodes[$model]);
        if(isset($this->connections[$model]))
            unset($this->connections[$model]);
        if(isset($this->idRegister[$model]))
            unset($this->idRegister[$model]);
    }

    /**
     * @inheritDoc
     */
    public function getSceneDataModels(): array
    {
        return $this->sceneDataModels;
    }

    /**
     * Adds a new node to the data model
     *
     * @param NodeDataModelInterface $model
     * @param $scene
     */
    public function addNodeModel(NodeDataModelInterface $model, $scene) {
        if($scene instanceof SceneDataModelInterface)
            $scene = $scene->getIdentifier();

        if($existingModel = $this->getDataModelByIdentifier( $model->getIdentifier() )) {
            $e = new DuplicateIdentifierException("Node with id %s already exists", DuplicateIdentifierException::CODE_DUPLICATE_SYMBOL, NULL, $model->getIdentifier());
            $e->setModel($this);
            $e->setProperty($model);
            $e->setDuplicate($existingModel);
            throw $e;
        }

        if(!isset($this->sceneDataModels[$scene])) {
            $e = new InvalidReferenceException("Scene %s does not exist", InvalidReferenceException::CODE_SYMBOL_NOT_FOUND, NULL, $scene);
            $e->setModel($this);
            $e->setProperty($model);
            throw $e;
        }

        $this->nodes[$scene][ $model->getIdentifier() ] = $model;
        $this->nodeSceneMap[ $model->getIdentifier() ] = $scene;
        $this->idRegister[$model->getIdentifier()] = $model;
    }

    /**
     * Removes a node from data model
     *
     * @param $model
     */
    public function removeNodeModel($model) {
        if($model instanceof NodeDataModelInterface)
            $model = $model->getIdentifier();

        foreach($this->nodes as &$nodes) {
            $nodes = array_filter($nodes, function(NodeDataModelInterface $m) use ($model) {
                return $model != $m->getIdentifier();
            });
        }

        if(isset($this->idRegister[$model]))
            unset($this->idRegister[$model]);
        if(isset($this->nodeSceneMap[$model]))
            unset($this->nodeSceneMap[$model]);
    }

    /**
     * @inheritDoc
     */
    public function getNodesInScene($scene): array
    {
        return $this->nodes[$scene instanceof IdentifiedDataModelInterface ? $scene->getIdentifier() : $scene] ?? [];
    }

    /**
     * Adds a connection to the data model
     *
     * @param ConnectionDataModelInterface $model
     * @param $scene
     */
    public function addConnectionModel(ConnectionDataModelInterface $model, $scene) {
        if($scene instanceof SceneDataModelInterface)
            $scene = $scene->getIdentifier();

        if(!isset($this->sceneDataModels[$scene])) {
            $e = new InvalidReferenceException("Scene %s does not exist", InvalidReferenceException::CODE_SYMBOL_NOT_FOUND, NULL, $scene);
            $e->setModel($this);
            $e->setProperty($model);
            throw $e;
        }

        $this->connections[$scene][] = $model;
    }

    /**
     * Removes a connection from data model
     *
     * @param ConnectionDataModelInterface $model
     */
    public function removeConnectionModel(ConnectionDataModelInterface $model) {
        foreach($this->connections as &$connections) {
            $connections = array_filter($connections, function(ConnectionDataModelInterface $c) use ($model) {
                return $model !== $c;
            });
        }
    }

    /**
     * @inheritDoc
     */
    public function getConnectionsInScene($scene): array
    {
        return $this->connections[$scene instanceof IdentifiedDataModelInterface ? $scene->getIdentifier() : $scene] ?? [];
    }
}