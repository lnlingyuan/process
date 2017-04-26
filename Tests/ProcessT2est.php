<?php
namespace Slince\Process\Tests;

use PHPUnit\Framework\TestCase;
use Slince\Process\Process;

class ProcessTest extends TestCase
{
    public function testStart()
    {
        $process = new Process(function(){
            sleep(1);
        });
        $this->assertFalse($process->isRunning());
        $process->start();
        $this->assertTrue($process->isRunning());
        $process->wait();
    }

    public function testWait()
    {
        $process = new Process(function(){
            sleep(2);
        });
        $process->start();
        $process->wait();
        $this->assertFalse($process->isRunning());
    }

    public function testGetExitCode()
    {
        $process = new Process(function(){
            usleep(100);
        });
        $process->start();
        $process->wait();
        $this->assertEquals(0, $process->getExitCode());

        $process = new Process(function(){
            exit(255);
        });
        $process->run();
        $this->assertEquals(255, $process->getExitCode());
    }

    public function testIfSignaled()
    {
        $process = new Process(function(){
            sleep(100);
        });
        $process->start();
        $process->stop();
        $this->assertTrue($process->isSignaled());
        $this->assertTrue($process->isStopped());
    }


    public function testGetPid()
    {
        $process = new Process(function(){
            sleep(1);
        });
        $this->assertNull($process->getPid());
        $process->start();
        $this->assertGreaterThan(0, $process->getPid());
        $process->wait();
    }
}