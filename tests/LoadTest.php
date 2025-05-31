<?php

use PHPUnit\Framework\TestCase;
use Loader\Load;

class LoadTest extends TestCase
{
    public function testAddClassStoresModel()
    {
        $load = new Load();
        $dummyModel = new stdClass();
        $load->addClass('model', 'dummy', $dummyModel);

        $this->assertSame($dummyModel, $load->model->dummy);
    }

    public function testAddClassStoresService()
    {
        $load = new Load();
        $dummyService = new stdClass();
        $load->addClass('service', 'dummy', $dummyService);

        $this->assertSame($dummyService, $load->service->dummy);
    }

    public function testAddClassStoresLibrary()
    {
        $load = new Load();
        $dummyLibrary = new stdClass();
        $load->addClass('library', 'dummy', $dummyLibrary);

        $this->assertSame($dummyLibrary, $load->library->dummy);
    }
}