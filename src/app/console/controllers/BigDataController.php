<?php
namespace console\controllers;

use common\models\BigData;
use yii\console\Controller;

class BigDataController extends Controller
{
    const RECORD_NUM_TO_ECHO = 1000;
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
            $text = "This is big_text content";
            for ($recordCount = 1; $recordCount <= $recordNum; $recordCount++) {
                $lastId++;
                $bigData = new BigData([
                    'big_text' => "$lastId $text",
                ]);
                $bigData->saveThrowError();
                if ($recordCount % self::RECORD_NUM_TO_ECHO == 0) {
                    echo "Create record id $lastId\n";
                }
            }
        });
        echo "DONE\n";
    }
}
