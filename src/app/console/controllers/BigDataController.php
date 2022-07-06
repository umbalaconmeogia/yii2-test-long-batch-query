<?php
namespace console\controllers;

use common\models\BigData;
use Faker\Provider\Lorem;
use yii\console\Controller;

class BigDataController extends BaseLongRunningController
{
    const RECORD_NUM_TO_ECHO = 1000;

    protected static $cacheKeyLastCompleteId = 'LAST_COMPLETE_BIG_DATA_ID';

    private $loremHolder = NULL;

    private function loremText(): string
    {
        if ($this->loremHolder === NULL) {
            $this->loremHolder = Lorem::text(10000);
        }
        return $this->loremHolder;
    }

    /**
     * Action to create number of data record of BigData.
     * Syntax
     * ```shell
     *   php yii big-data/create-data [record_num]
     * ```
     * @param int $recordNum Number of records to be created.
     */
    public function actionCreateData(int $recordNum = 1000000): void
    {
        BigData::getDb()->transaction(function() use ($recordNum) {
            $lastId = BigData::find()->max('id');
            for ($recordCount = 1; $recordCount <= $recordNum; $recordCount++) {
                $lastId++;
                $bigData = new BigData([
                    'big_text' => $this->generateBigTextWithPrefix($lastId),
                ]);
                $bigData->saveThrowError();
                if ($recordCount % self::RECORD_NUM_TO_ECHO == 0) {
                    echo "Create record id $lastId\n";
                }
            }
        });
        echo "DONE\n";
    }

    /**
     * Action to update BigData#big_text of all records.
     * Syntax
     * ```shell
     *   php yii big-data/update-big-text
     * ```
     */
    public function actionUpdateBigText()
    {
        $db = $this->openUnbufferedDb('db');
        try {
            $query = BigData::find();
            foreach ($query->batch(static::$QUERY_BATCH_SISE, $db) as $batchRecords) {
                $this->processOnBatchRecords($batchRecords, [$this, 'updateBigText']);
            }
        } finally {
            $db->close();
        }
    }

    public function updateBigText(BigData $bigData = NULL): void
    {
        $bigData->big_text = $this->generateBigTextWithPrefix($bigData->id);
        $bigData->saveThrowError();
    }

    private function generateBigTextWithPrefix($prefix): string
    {
        return "$prefix " . $this->loremText();
    }

    /**
     * Syntax:
     * ```shell
     *   php yii big-data/test-batch-query
     * ```
     */
    public function actionTestBatchQuery()
    {
        $db = $this->openUnbufferedDb('db');
        try {
            $query = BigData::find();
            foreach ($query->batch(static::$QUERY_BATCH_SISE, $db) as $batchRecords) {
                $this->processOnBatchRecords($batchRecords, [$this, 'processOneRecord']);
            }
        } finally {
            $db->close();
        }
    }

    public function processOneRecord(BigData $bigData)
    {
        // Do nothing.
    }
}
