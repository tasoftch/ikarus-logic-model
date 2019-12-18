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

namespace Ikarus\Logic\Model\Element\Scene;


use Ikarus\Logic\Model\Component\ComponentInterface;
use Ikarus\Logic\Model\Component\DefaultComponent;
use Ikarus\Logic\Model\Element\AbstractElement;
use Ikarus\Logic\Model\Element\Connection\ConnectionElement;
use Ikarus\Logic\Model\Element\Connection\ConnectionElementInterface;
use Ikarus\Logic\Model\Element\Node\NodeElementInterface;
use Ikarus\Logic\Model\Element\Socket\InputSocketElement;
use Ikarus\Logic\Model\Element\Socket\OutputSocketElement;
use Ikarus\Logic\Model\Element\Socket\SocketElementInterface;
use Ikarus\Logic\Model\ProjectInterface;

class SceneElement extends AbstractElement implements SceneElementInterface
{
    /** @var NodeElementInterface[] */
    protected $nodes = [];

    /** @var ConnectionElementInterface[] */
    protected $connections = [];

    private $singleConnections = [];

    public function __construct(ProjectInterface $project, ComponentInterface $component = NULL, $identifier = NULL)
    {
        if(!$component)
            $component = new DefaultComponent("scene");
        parent::__construct($component, $project, $identifier);
    }

    /**
     * @return NodeElementInterface[]
     */
    public function getNodes(): array
    {
        return $this->nodes;
    }

    /**
     * @return ConnectionElementInterface[]
     */
    public function getConnections(): array
    {
        return $this->connections;
    }

    /**
     * Adds a node to the scene
     * @param NodeElementInterface $object
     */
    public function addNode(NodeElementInterface $object) {
        $this->nodes[$object->getIdentifier()] = $object;
    }

    /**
     * Removes a node from scene
     * @param $object
     */
    public function removeNode($object) {
        $node = $this->getNode($object);
        if($node) {
            $inputs = $node->getInputSocketElements();
            $outputs = $node->getOutputSocketElements();

            foreach(array_values($this->connections) as $connection) {
                if(in_array($connection->getInputSocketElement(), $inputs) || in_array($connection->getOutputSocketElement(), $outputs))
                    $this->removeConnection($connection);
            }

            unset($this->nodes[$node->getIdentifier()]);
        }
    }

    /**
     * Get node
     *
     * @param $object
     * @return NodeElementInterface|null
     */
    public function getNode($object): ?NodeElementInterface {
        return $this->nodes[$object] ?? NULL;
    }

    /**
     * Removes a connection between two nodes
     *
     * @param ConnectionElementInterface $connection
     */
    public function removeConnection(ConnectionElementInterface $connection) {
        if(($idx = array_search($connection, $this->connections)) !== false)
            unset($this->connections[$idx]);
    }

    public function addConnection(ConnectionElementInterface $connectionElement) {
        $checkSingleton = function(SocketElementInterface $socket) use ($connectionElement) {
            if($c = $this->singleConnections[ $socket->getIdentifier() ] ?? NULL) {
                $this->removeConnection($c);
                unset($this->singleConnections[ $socket->getIdentifier() ]);
            }

            if($socket->getComponent()->allowsMultiple() == false)
                $this->singleConnections[ $socket->getIdentifier() ] = $connectionElement;
        };

        $checkSingleton( $connectionElement->getInputSocketElement() );
        $checkSingleton( $connectionElement->getOutputSocketElement() );

        $this->connections[] = $connectionElement;
    }

    /**
     * Connects two sockets
     *
     * @param SocketElementInterface $socket1
     * @param SocketElementInterface $socket2
     * @return bool
     */
    public function connect(SocketElementInterface $socket1, SocketElementInterface $socket2) {
        $input = $output = NULL;
        if($socket1 instanceof InputSocketElement)
            $input = $socket1;
        if($socket2 instanceof InputSocketElement)
            $input = $socket2;

        if($socket1 instanceof OutputSocketElement)
            $output = $socket1;
        if($socket2 instanceof OutputSocketElement)
            $output = $socket2;

        if($input && $output) {
            if($input->getNode()->getScene() === $output->getNode()->getScene()) {
                $c = new ConnectionElement($input, $output);
                return $this->addConnection($c);
            }
        }
        return false;
    }
}