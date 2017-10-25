<?php
namespace Soluti\V1\Rest\Inserir;

class InserirResourceFactory
{
    public function __invoke($services)
    {
        return new InserirResource();
    }
}
