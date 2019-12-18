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

namespace Ikarus\Logic\Model\Loader;


use Ikarus\Logic\Model\AbstractLogicProject;
use Ikarus\Logic\Model\Component\NodeComponentInterface;
use Ikarus\Logic\Model\Element\Node\NodeElement;
use Ikarus\Logic\Model\Element\Node\NodeElementInterface;
use Ikarus\Logic\Model\Element\Scene\SceneElement;
use Ikarus\Logic\Model\Element\Socket\InputSocketElement;
use Ikarus\Logic\Model\Element\Socket\OutputSocketElement;
use Ikarus\Logic\Model\Exception\LogicException;
use Ikarus\Logic\Model\ProjectInterface;

abstract class AbstractPlainArrayDataLoader extends AbstractDataParseLoader
{
    const SCENES_KEY = 'scenes';
    const NODES_KEY = 'nodes';
    const CONNECTIONS_KEY = 'connections';

    const TOP_LEVEL_KEY = 'topLevel';
    const ID_KEY = 'id';
    const NAME_KEY = 'name';
    const DATA_KEY = 'data';

    const CONNECTION_SRC_NODE_KEY = 'src';
    const CONNECTION_INPUT_KEY = 'input';
    const CONNECTION_DST_NODE_KEY = 'dst';
    const CONNECTION_OUTPUT_KEY = 'output';

    protected function parseData($data, ?ProjectInterface &$project)
    {
        if(!($project instanceof AbstractLogicProject))
            throw new LogicException("Can not load elements without project");

        if(is_iterable($scenes = $data[ static::SCENES_KEY ] ?? NULL)) {
            $getID = function($array) use ($project) {
                return $array[ static::ID_KEY ] ?? $project->getIdentifierGenerator()->makeUniqueIdentifier();
            };

            $getName = function($array) {
                return $array[ static::NAME_KEY ] ?? $array[ static::ID_KEY] ?? NULL;
            };

            $getCmp = function($name, $throw = false) use ($project) {
                foreach($project->getComponents() as $component) {
                    if($component->getName() == $name)
                        return $component;
                }
                if($throw)
                    throw new LogicException("No component $name found");
                else
                    return NULL;
            };

            $getInput = function(NodeElementInterface $nodeElement, $key): ?InputSocketElement {
                foreach($nodeElement->getInputSocketElements() as $inputSocketElement) {
                    if($inputSocketElement->getComponent()->getName() == $key)
                        return $inputSocketElement;
                }
                return NULL;
            };

            $getOutput = function(NodeElementInterface $nodeElement, $key): ?OutputSocketElement {
                foreach($nodeElement->getOutputSocketElements() as $outputSocketElement) {
                    if($outputSocketElement->getComponent()->getName() == $key)
                        return $outputSocketElement;
                }
                return NULL;
            };

            foreach($scenes as $scene) {
                $id = $getID($scene);
                $cmp = $getCmp( $getName($scene) );
                $SCENE = new SceneElement($project, $cmp, $id);

                if($nodes = $scene[ static::NODES_KEY ] ?? NULL) {
                    foreach($nodes as $node) {
                        $id = $getID($node);
                        $name = $getName($node);

                        $cmp = $getCmp($name, true);

                        $NODE = new NodeElement($cmp, $project, $id);
                        $NODE->setScene($SCENE);

                        $this->buildNode($NODE, $node, $project);
                        $SCENE->addNode($NODE);
                    }
                }

                if($connections = $scene[ static::CONNECTIONS_KEY ] ?? NULL) {
                    foreach($connections as $connection) {
                        $srcNode = $SCENE->getNode( $connection[ static::CONNECTION_SRC_NODE_KEY ] );
                        $output = $getOutput($srcNode, $connection[ static::CONNECTION_OUTPUT_KEY ]);

                        $dstNode = $SCENE->getNode( $connection[ static::CONNECTION_DST_NODE_KEY ] );
                        $input = $getInput($dstNode, $connection[ static::CONNECTION_INPUT_KEY ]);

                        if($input && $output) {
                            $SCENE->connect($input, $output);
                        } else
                            trigger_error("Could not connect", E_USER_WARNING);
                    }
                }

                $project->addScene( $SCENE, ($scene[ static::TOP_LEVEL_KEY ] ?? true) ? true : false );
            }
        } else
            trigger_error("No scenes found in data", E_USER_WARNING);
    }

    protected function buildNode(NodeElement $nodeElement, $nodeModel, AbstractLogicProject $project) {
        $cmp = $nodeElement->getComponent();
        if($cmp instanceof NodeComponentInterface) {
            $inputs = [];
            foreach($cmp->getInputSockets() as $socket) {
                $SOCK = new InputSocketElement($nodeElement, $project->getSocketType( $socket->getSocketType() ), $socket, $project);
                $inputs[ $SOCK->getComponent()->getName() ] = $SOCK;
            }
            $nodeElement->setInputs($inputs);

            $outputs = [];
            foreach($cmp->getOutputSockets() as $socket) {
                $SOCK = new OutputSocketElement($nodeElement, $project->getSocketType( $socket->getSocketType() ), $socket, $project);
                $outputs[ $SOCK->getComponent()->getName() ] = $SOCK;
            }
            $nodeElement->setOutputs($outputs);
        }
    }
}