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


use Ikarus\Logic\Model\Data\Connection\ConnectionDataModel;
use Ikarus\Logic\Model\Data\Node\AttributedNodeDataModel;
use Ikarus\Logic\Model\Data\Node\NodeDataModel;
use Ikarus\Logic\Model\Data\Node\NodeDataModelInterface;
use Ikarus\Logic\Model\Data\Scene\AttributedSceneDataModel;
use Ikarus\Logic\Model\Data\Scene\SceneDataModel;
use Ikarus\Logic\Model\Data\Scene\SceneDataModelInterface;
use Ikarus\Logic\Model\Exception\InconsistentDataModelException;
use Ikarus\Logic\Model\Exception\InvalidReferenceException;

class DataModel extends AbstractDataModel
{
    /**
     * Adds a new scene to the data model
     *
     * @param $identifier
     * @param array|NULL $attributes
     */
    public function addScene($identifier, array $attributes = NULL) {
        if($attributes)
            $this->addSceneDataModel( new AttributedSceneDataModel($identifier, $attributes) );
        else
            $this->addSceneDataModel( new SceneDataModel($identifier) );
    }

    /**
     * Adds a new node to a scene in the data model
     *
     * @param $identifier
     * @param string $componentName
     * @param string|int|SceneDataModelInterface $scene
     * @param array|NULL $attributes
     */
    public function addNode($identifier, string $componentName, $scene, array $attributes = NULL) {
        if($attributes)
            $this->addNodeModel( new AttributedNodeDataModel($identifier, $componentName, $attributes), $scene );
        else
            $this->addNodeModel( new NodeDataModel($identifier, $componentName), $scene );
    }

    /**
     * Establish connection between nodes and sockets
     *
     * @param $inputNode
     * @param string $inputName
     * @param $outputNode
     * @param string $outputName
     */
    public function connect($inputNode, string $inputName, $outputNode, string $outputName) {
        $inid = $this->nodeSceneMap[$inputNode instanceof NodeDataModelInterface ? $inputNode->getIdentifier() : $inputNode] ?? NULL;
        if(!$inid) {
            $e = new InvalidReferenceException("No input node $inputNode found", InvalidReferenceException::CODE_SYMBOL_NOT_FOUND);
            $e->setModel($this);
            $e->setProperty($inputNode);
            throw $e;
        }

        $onid = $this->nodeSceneMap[$outputNode instanceof NodeDataModelInterface ? $outputNode->getIdentifier() : $outputNode] ?? NULL;
        if(!$onid) {
            $e = new InvalidReferenceException("No output node $outputNode found", InvalidReferenceException::CODE_SYMBOL_NOT_FOUND);
            $e->setModel($this);
            $e->setProperty($outputNode);
            throw $e;
        }

        if($onid != $inid) {
            $e = new InconsistentDataModelException("Input and output node must be in the same scene", InconsistentDataModelException::CODE_INVALID_PLACEMENT);
            $e->setModel($this);
            $e->setProperty([$inputNode, $outputNode]);
            throw $e;
        }

        $this->addConnectionModel( new ConnectionDataModel(
            $inputNode instanceof NodeDataModelInterface ? $inputNode->getIdentifier() : $inputNode,
            $inputName,
            $outputNode instanceof NodeDataModelInterface ? $outputNode->getIdentifier() : $outputNode,
            $outputName
        ), $onid );
    }
}