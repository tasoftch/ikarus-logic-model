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

namespace Ikarus\Logic\Model\Data\Loader;


use ArrayAccess;
use Ikarus\Logic\Model\Data\DataModelInterface;
use Ikarus\Logic\Model\DataModel;
use Ikarus\Logic\Model\Exception\InconsistentDataModelException;

class PHPArrayLoader extends AbstractLoader implements ArrayAccess
{
    const SCENES_KEY = 'scenes';
    const NODES_KEY = 'nodes';
    const CONNECTIONS_KEY = 'connections';

    const TOP_LEVEL_KEY = 'topLevel';
    const ID_KEY = 'id';
    const NAME_KEY = 'name';
    const DATA_KEY = 'data';

    const CONNECTION_INPUT_NODE_KEY = 'src';
    const CONNECTION_INPUT_KEY = 'input';
    const CONNECTION_OUTPUT_NODE_KEY = 'dst';
    const CONNECTION_OUTPUT_KEY = 'output';

    /** @var array */
    private $data;

    /**
     * If set to true, the loader will use the array index of scenes and nodes as their identifier
     *
     * @var bool
     */
    public $useIndicesAsIdentifiers = false;

    /**
     * PHPArrayLoader constructor.
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @param array $data
     */
    public function setData(array $data): void
    {
        $this->data = $data;
        $this->model = NULL;
    }


    protected function makeModel(): DataModelInterface
    {
        $model = new DataModel();

        try {
            if(is_iterable($scenes = $this->getData()[ static::SCENES_KEY ] ?? NULL)) {
                foreach($scenes as $sid => $scene) {
                    $sid = $this->useIndicesAsIdentifiers ? $sid : $this->getIdentifier($scene);

                    if($nodes = $scene[ static::NODES_KEY ] ?? NULL) {
                        $model->addScene($sid, $scene[static::DATA_KEY] ?? NULL);

                        foreach($nodes as $nid => $node) {
                            $nid = $this->useIndicesAsIdentifiers ? $nid : $this->getIdentifier($node);
                            $name = $this->getName($node, false, false);

                            $model->addNode($nid, $name, $sid, $node[ static::DATA_KEY ] ?? NULL);
                        }

                        foreach(($scene[ static::CONNECTIONS_KEY ] ?? []) as $connection) {
                            $model->connect(
                                $connection[ static::CONNECTION_INPUT_NODE_KEY ],
                                $connection[ static::CONNECTION_INPUT_KEY ],
                                $connection[ static::CONNECTION_OUTPUT_NODE_KEY ],
                                $connection[ static::CONNECTION_OUTPUT_KEY ]
                            );
                        }
                    } else
                        trigger_error("No nodes found in scene $sid", E_USER_NOTICE);
                }
            } else
                trigger_error("No scenes found in data", E_USER_NOTICE);
        } catch (InconsistentDataModelException $exception) {
            $exception->setModel($model);
            throw $exception;
        }

        return $model;
    }

    protected function getIdentifier($fromData) {
        if(is_array($fromData) || $fromData instanceof ArrayAccess) {
            if($id = $fromData[ static::ID_KEY ] ?? NULL) {
                return $id;
            }
        }
        $e = new InconsistentDataModelException("Could not fetch identifier from data", InconsistentDataModelException::CODE_SYMBOL_NOT_FOUND);
        $e->setProperty($fromData);
        throw $e;
    }

    protected function getName($fromData, bool $optional = false, bool $useIDInstead = true) {
        if(is_array($fromData) || $fromData instanceof ArrayAccess) {
            if($id = $fromData[ static::NAME_KEY ] ?? NULL) {
                return $id;
            }
            if($useIDInstead) {
                if($id = $fromData[ static::ID_KEY ] ?? NULL) {
                    return $id;
                }
            }
        }
        if(!$optional) {
            $e = new InconsistentDataModelException("Could not fetch name from data", InconsistentDataModelException::CODE_SYMBOL_NOT_FOUND);
            $e->setProperty($fromData);
            throw $e;
        }
        return NULL;
    }

    /**
     * Whether a offset exists
     * @link https://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset <p>
     * An offset to check for.
     * </p>
     * @return boolean true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     * @since 5.0.0
     */
    public function offsetExists($offset)
    {
        return isset($this->data[$offset]);
    }

    /**
     * Offset to retrieve
     * @link https://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset <p>
     * The offset to retrieve.
     * </p>
     * @return mixed Can return all value types.
     * @since 5.0.0
     */
    public function offsetGet($offset)
    {
        return $this->data[$offset] ?? NULL;
    }

    /**
     * Offset to set
     * @link https://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset <p>
     * The offset to assign the value to.
     * </p>
     * @param mixed $value <p>
     * The value to set.
     * </p>
     * @return void
     * @since 5.0.0
     */
    public function offsetSet($offset, $value)
    {
        if($offset !== NULL) {
            $this->data[$offset] = $value;
            $this->model = NULL;
        }
    }

    /**
     * Offset to unset
     * @link https://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset <p>
     * The offset to unset.
     * </p>
     * @return void
     * @since 5.0.0
     */
    public function offsetUnset($offset)
    {
        unset($this->data[$offset]);
        $this->model = NULL;
    }
}