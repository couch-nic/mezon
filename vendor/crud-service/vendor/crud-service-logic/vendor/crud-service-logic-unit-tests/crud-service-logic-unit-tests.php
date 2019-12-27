<?php
/**
 * Class CRUDServiceLogicUnitTests
 *
 * @package     CRUDServiceLogic
 * @subpackage  CRUDServiceLogicUnitTests
 * @author      Dodonov A.A.
 * @version     v.1.0 (2019/08/17)
 * @copyright   Copyright (c) 2019, aeon.org
 */

/**
 * Fake securoity provider
 */
class FakeSecurityProvider
{
}

/**
 * Fake patrameters fetched
 */
class FakeParametersFetcher implements \Mezon\Service\ServiceRequestParams
{

    /**
     * Method returns request parameter
     *
     * @param string $Param
     *            parameter name
     * @param mixed $Default
     *            default value
     * @return mixed Parameter value
     */
    public function getParam($Param, $Default = false)
    {
        return (false);
    }
}

/**
 * Fake service model
 */
class FakeServiceModel extends \Mezon\CRUDService\CRUDServiceModel
{

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct([
            'id' => [
                'type' => 'integer'
            ]
        ], 'record');
    }

    /**
     * Method returns amount of records in table, grouped by the specified field
     *
     * @param integer|bool $DomainId
     *            Domain id
     * @param string $FieldName
     *            Grouping field
     * @param array $Where
     *            Filtration conditions
     * @return array Records with stat
     */
    public function recordsCountByField($DomainId, string $FieldName, array $Where): array
    {
        return ([
            [
                'id' => 1,
                'records_count' => 1
            ],
            [
                'id' => 2,
                'records_count' => 2
            ]
        ]);
    }

    /**
     * Method returns last $Count records
     *
     * @param integer|boolean $DomainId
     *            Id of the domain
     * @param integer $Count
     *            Amount of records to be returned
     * @param array $Where
     *            Filter conditions
     * @return array List of the last $Count records
     */
    public function lastRecords($DomainId, $Count, $Where)
    {
        return ([
            []
        ]);
    }
}

/**
 * Common CRUDServiceLogic unit tests
 */
class CRUDServiceLogicUnitTests extends ServiceLogicUnitTests
{

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct('\Mezon\CRUDService\CRUDServiceLogic');
    }

    /**
     * Method returns service model
     *
     * @param array $Methods
     *            Methods to be mocked
     * @return object Service model
     */
    protected function getServiceModelMock(array $Methods = [])
    {
        $Model = $this->getMockBuilder('\Mezon\CRUDService\CRUDServiceModel')
            ->setConstructorArgs([
            [
                'id' => [
                    'type' => 'integer'
                ],
                'domain_id' => [
                    'type' => 'integer'
                ],
                'creation_date' => [
                    'type' => 'date'
                ]
            ],
            'record'
        ])
            ->setMethods($Methods)
            ->getMock();

        return ($Model);
    }

    /**
     * Returning json file content
     *
     * @param string $FileName
     *            File name
     * @return array json decoded countent of the file
     */
    protected function jsonData(string $FileName): array
    {
        return (json_decode(file_get_contents(__DIR__ . '/conf/' . $FileName . '.json'), true));
    }

    /**
     * Method creates full functional CRUDServiceLogic object
     *
     * @param mixed $Model
     *            List of models or single model
     * @return \Mezon\CRUDService\CRUDServiceLogic object
     */
    protected function getServiceLogic($Model): \Mezon\CRUDService\CRUDServiceLogic
    {
        $Transport = new \Mezon\Service\ServiceConsoleTransport();

        $Logic = new \Mezon\CRUDService\CRUDServiceLogic($Transport->ParamsFetcher, new \Mezon\Service\ServiceMockSecurityProvider(), $Model);

        return ($Logic);
    }

    /**
     * Method creates service logic for list methods testing
     */
    protected function setupLogicForListMethodsTesting()
    {
        $Connection = $this->getMockBuilder(\Mezon\PDOCRUD::class)
            ->disableOriginalConstructor()
            ->setMethods([
            'select'
        ])
            ->getMock();
        $Connection->method('select')->willReturn([
            [
                'field_name' => 'balance',
                'field_value' => 100
            ]
        ]);

        $ServiceModel = $this->getServiceModelMock([
            'getSimpleRecords',
            'getConnection'
        ]);
        $ServiceModel->method('getSimpleRecords')->willReturn($this->jsonData('get-simple-records'));
        $ServiceModel->method('getConnection')->willReturn($Connection);

        $ServiceLogic = $this->getServiceLogic($ServiceModel);

        return ($ServiceLogic);
    }

    /**
     * Testing getting amount of records
     */
    public function testRecordsCount1()
    {
        // setup
        $ServiceModel = $this->getServiceModelMock();
        $ServiceModel->method('recordsCount')->willReturn(1);

        $ServiceLogic = $this->getServiceLogic($ServiceModel);

        // test body
        $Count = $ServiceLogic->recordsCount();

        // assertions
        $this->assertEquals(1, $Count, 'Records count was not fetched');
    }

    /**
     * Testing getting amount of records
     */
    public function testRecordsCount0()
    {
        // setup
        $ServiceModel = $this->getServiceModelMock();
        $ServiceModel->method('recordsCount')->willReturn(0);

        $ServiceLogic = $this->getServiceLogic($ServiceModel);

        // test body
        $Count = $ServiceLogic->recordsCount();

        // assertions
        $this->assertEquals(0, $Count, 'Records count was not fetched');
    }

    /**
     * Method tests last N records returning
     */
    public function testLastRecords()
    {
        // setup
        $ServiceModel = new FakeServiceModel();

        $ServiceLogic = $this->getServiceLogic($ServiceModel);

        // test body
        $Records = $ServiceLogic->lastRecords(1);

        // assertions
        $this->assertEquals(1, count($Records), 'Invalid amount of records was returned');
    }

    /**
     * Testing getting amount of records
     */
    public function testRecordsCountByExistingField()
    {
        // setup
        $ServiceModel = new FakeServiceModel();

        $ServiceLogic = $this->getServiceLogic($ServiceModel);

        global $argv;
        $argv['field'] = 'id';

        // test body
        $Counters = $ServiceLogic->recordsCountByField();

        // assertions
        $this->assertEquals(2, count($Counters), 'Records were not fetched. Params:  ' . serialize($argv));
        $this->assertEquals(1, $Counters[0]['records_count'], 'Records were not counted');
        $this->assertEquals(2, $Counters[1]['records_count'], 'Records were not counted');
    }

    /**
     * Testing getting amount of records.
     */
    public function testRecordsCountByNotExistingField()
    {
        // setup
        $ServiceModel = new FakeServiceModel();

        $ServiceLogic = $this->getServiceLogic($ServiceModel);

        global $argv;
        $argv['field'] = 'unexisting';

        // test body and assertions
        try {
            $ServiceLogic->recordsCountByField();

            $this->fail('Exception must be thrown, but it was not ' . serialize($argv));
        } catch (Exception $e) {
            $this->addToAssertionCount(1);
        }
    }

    /**
     * Testing constructor.
     */
    public function testConstruct()
    {
        $ServiceLogic = new \Mezon\CRUDService\CRUDServiceLogic(new FakeParametersFetcher(), new FakeSecurityProvider());

        $this->assertInstanceOf('FakeParametersFetcher', $ServiceLogic->ParamsFetcher);
        $this->assertInstanceOf('FakeSecurityProvider', $ServiceLogic->SecurityProvider);
    }

    /**
     * Testing records list generation
     */
    public function testListRecord()
    {
        // setup
        $ServiceLogic = $this->setupLogicForListMethodsTesting();

        // test body
        $RecordsList = $ServiceLogic->listRecord();

        // assertions
        $this->assertEquals(2, count($RecordsList), 'Invalid records list was fetched');
    }

    /**
     * Testing domain_id fetching
     */
    public function testGetDomainIdCrossDomainDisabled()
    {
        // setup
        $ServiceModel = $this->getServiceModelMock([
            'get_connection'
        ]);

        $ServiceLogic = $this->getServiceLogic($ServiceModel);

        unset($_GET['cross_domain']);

        // test body
        $Result = $ServiceLogic->getDomainId();

        // assertions
        $this->assertEquals(1, $Result, 'Invalid getDomainId result. Must be 1');
    }

    /**
     * Testing domain_id fetching
     */
    public function testGetDomainIdCrossDomainEnabled()
    {
        // setup
        $ServiceModel = $this->getServiceModelMock();

        $ServiceLogic = $this->getServiceLogic($ServiceModel);

        $_GET['cross_domain'] = 1;

        // test
        $Result = $ServiceLogic->getDomainId();

        $this->assertEquals(false, $Result, 'Invalid getDomainId result. Must be false');
    }

    /**
     * Testing newRecordsSince method for invalid
     */
    public function testNewRecordsSinceInvalid()
    {
        // setup
        $ServiceModel = $this->getServiceModelMock();

        $ServiceLogic = $this->getServiceLogic($ServiceModel);

        // test body
        try {
            $ServiceLogic->newRecordsSince();
            $this->fail('Exception must be thrown');
        } catch (Exception $e) {
            $this->addToAssertionCount(1);
        }
    }

    /**
     * Testing newRecordsSince method
     */
    public function testNewRecordsSince()
    {
        // setup
        $ServiceModel = $this->getServiceModelMock([
            'newRecordsSince'
        ]);
        $ServiceModel->method('newRecordsSince')->willReturn([
            []
        ]);

        $ServiceLogic = $this->getServiceLogic($ServiceModel);

        // test body
        $Result = $ServiceLogic->newRecordsSince();

        // assertions
        $this->assertCount(1, $Result);
    }

    /**
     * Testing 'updateRecord' method
     */
    public function testUpdateRecord()
    {
        // setup
        $FieldName = 'record-title';
        $ServiceModel = $this->getServiceModelMock([
            'updateBasicFields',
            'setFieldForObject'
        ]);
        $ServiceModel->method('updateBasicFields')->willReturn([
            $FieldName => 'Record title'
        ]);

        $ServiceLogic = $this->getServiceLogic($ServiceModel);

        global $argv;
        $argv[$FieldName] = 'Some title';
        $argv['custom_fields']['record-balance'] = 123;

        // test body
        $Record = $ServiceLogic->updateRecord();

        // assertions
        $this->assertEquals('Record title', $Record[$FieldName], 'Invalid update result' . serialize($argv));
        $this->assertEquals(123, $Record['custom_fields']['record-balance'], 'Invalid update result' . serialize($argv));
        $this->assertTrue(isset($Record['id']), 'Id was not returned' . serialize($argv));
    }

    /**
     * Method tests filtered deletion
     */
    public function testDeleteFltered()
    {
        // setup
        $ServiceModel = $this->getServiceModelMock();
        $ServiceModel->expects($this->once())
            ->method('deleteFiltered');

        $Mock = $this->getServiceLogic($ServiceModel);

        // test body and assertions
        $Mock->deleteFiltered();
    }

    /**
     * Method tests deletion
     */
    public function testDeleteRecord()
    {
        // setup
        $ServiceModel = $this->getServiceModelMock();
        $ServiceModel->expects($this->once())
            ->method('deleteFiltered');

        $Mock = $this->getServiceLogic($ServiceModel);

        // test body and assertions
        $Mock->deleteRecord();
    }

    /**
     * Testing all records generation
     */
    public function testAll()
    {
        // setup
        $ServiceLogic = $this->setupLogicForListMethodsTesting();

        // test body
        $RecordsList = $ServiceLogic->all();

        // assertions
        $this->assertEquals(2, count($RecordsList), 'Invalid records list was fetched');
    }

    /**
     * Method tests creation
     */
    public function testCreateRecord()
    {
        // setup
        $ServiceModel = $this->getServiceModelMock();
        $ServiceModel->expects($this->once())
            ->method('insertBasicFields');

        $Mock = $this->getServiceLogic($ServiceModel);

        // test body and assertions
        $Mock->createRecord();
    }
}

?>