<?php
namespace tests\traits;

trait arraysTrait
{
    /** @var array|array[]  */
    private array $jsonApiDocumentSimple = [
        'data' => [
            'type' => 'user',
            'id' => '1',
            'attributes' => [
                'name' => 'carlo'
            ]
        ]
    ];
}