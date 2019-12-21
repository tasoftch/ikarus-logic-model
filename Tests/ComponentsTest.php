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

/**
 * ComponentsTest.php
 * ikarus-logic-model
 *
 * Created on 2019-12-21 10:55 by thomas
 */

namespace Ikarus\Logic\Model\Test;

use Ikarus\Logic\Model\Component\Socket\InputSocketComponentInterface;
use Ikarus\Logic\Model\Component\Socket\OutputSocketComponentInterface;
use Ikarus\Logic\Model\Test\Component\DuplicateSocketsComponent;
use Ikarus\Logic\Model\Test\Component\OneInputAndOneOutputComponent;
use Ikarus\Logic\Model\Test\Component\OneInputComponent;
use Ikarus\Logic\Model\Test\Component\OneOutputComponent;
use Ikarus\Logic\Model\Test\Component\SocketLessComponent;
use Ikarus\Logic\Model\Test\Component\WrongSocketClassComponent;
use PHPUnit\Framework\TestCase;

class ComponentsTest extends TestCase
{
    public function testComponentWithoutSockets() {
        $comp = new SocketLessComponent();
        $this->assertCount(0, $comp->getInputSockets());
        $this->assertCount(0, $comp->getOutputSockets());
    }

    public function testSingleInputComponent() {
        $comp = new OneInputComponent();
        $this->assertCount(1, $comp->getInputSockets());
        $this->assertCount(0, $comp->getOutputSockets());

        /** @var InputSocketComponentInterface $input */
        $input = $comp->getInputSockets()["input"];
        $this->assertInstanceOf(InputSocketComponentInterface::class, $input);
        $this->assertEquals("input", $input->getName());
        $this->assertEquals("String", $input->getSocketType());
    }

    public function testSingleOutptComponent() {
        $comp = new OneOutputComponent();
        $this->assertCount(0, $comp->getInputSockets());
        $this->assertCount(1, $comp->getOutputSockets());

        /** @var InputSocketComponentInterface $input */
        $input = $comp->getOutputSockets()["output"];
        $this->assertInstanceOf(OutputSocketComponentInterface::class, $input);
        $this->assertEquals("output", $input->getName());
        $this->assertEquals("Boolean", $input->getSocketType());
    }

    /**
     * @expectedException \Ikarus\Logic\Model\Exception\InconsistentComponentModelException
     * @expectedExceptionCode 77
     */
    public function testWrongSocketComponentClass() {
        $comp = new WrongSocketClassComponent();
        $this->assertCount(0, $comp->getInputSockets());
    }

    public function testMultipleComponent() {
        $comp = new OneInputAndOneOutputComponent();

        $this->assertCount(1, $comp->getInputSockets());
        $this->assertCount(1, $comp->getOutputSockets());

        /** @var InputSocketComponentInterface $input */
        $input = $comp->getOutputSockets()["output"];
        $this->assertInstanceOf(OutputSocketComponentInterface::class, $input);
        $this->assertEquals("output", $input->getName());
        $this->assertEquals("Boolean", $input->getSocketType());

        /** @var InputSocketComponentInterface $input */
        $input = $comp->getInputSockets()["input"];
        $this->assertInstanceOf(InputSocketComponentInterface::class, $input);
        $this->assertEquals("input", $input->getName());
        $this->assertEquals("String", $input->getSocketType());
    }

    /**
     * @expectedException \Ikarus\Logic\Model\Exception\DuplicateNameException
     * @expectedExceptionCode 88
     */
    public function testDuplicateSocketName() {
        $comp = new DuplicateSocketsComponent();
        $this->assertCount(0, $comp->getInputSockets());
    }
}
