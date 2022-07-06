<?php
namespace console\controllers;

use Error;
use ErrorException;
use Exception;
use Yii;
use yii\console\Controller;

class BaseLongRunningController extends Controller
{
    /**
     * SQL batch query size.
     */
    static $QUERY_BATCH_SISE = 100;

    /**
     * Set to 0 to not show log.
     */
    static $SHOW_LOG_SIZE = 100;

    /**
     * The key of cache to store las completed element's id.
     *
     * Sub class should set this id.
     *
     * @internal Never call this static variable directly. Use lastCompleteIdCacheKey() to retrieve the key.
     * @var string
     */
    protected static $cacheKeyLastCompleteId = NULL;

    /**
     * @var boolean Set to true to break (crash) after a batch of process.
     */
    protected $makeTestError = false;

    protected $lastCompleteWebdocId = 0;

    protected $totalIndex = 0;

    protected $batchNo = 0;

    protected $batchIndex = 0;

    protected $errorCount = 0;

    /**
     * @var int
     */
    protected $afterId = NULL;

    /**
     * @var int
     */
    protected $maxId = NULL;

    /**
     * @var int
     */
    protected $processNo = NULL;

    /**
     * @var int
     */
    protected $targetId = NULL;

    public function options($actionID)
    {
        return [
            'processNo',
            'afterId',
            'maxId',
            'targetId',
        ];
    }

    /**
     * Syntax
     * ```shell
     *   php yii <controller>/last-complete-id [processNo]
     * ```
     */
    public function actionLastCompleteId($processNo)
    {
        $this->initiateAfterIdMaxId($processNo);
        echo "Key = " . $this->lastCompleteIdCacheKey() . "\n";
        echo "Last complete id " . Yii::$app->cache->get($this->lastCompleteIdCacheKey()) . "\n";
    }

    /**
     * @return DbConnection
     */
    protected function openUnbufferedDb($dbAlias)
    {
        $unbufferedDb = new \yii\db\Connection([
            'dsn' => Yii::$app->$dbAlias->dsn,
            'username' => Yii::$app->$dbAlias->username,
            'password' => Yii::$app->$dbAlias->password,
            'charset' => Yii::$app->$dbAlias->charset,
        ]);
        $unbufferedDb->open();
        $unbufferedDb->pdo->setAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false);
        return $unbufferedDb;
    }

    protected function nextBatch()
    {
        $this->batchNo++;
        $this->batchIndex = 0;
    }

    protected function nextBatchElement()
    {
        $this->batchIndex++;
        $this->totalIndex++;
    }

    protected function logWebdocBegin($webdoc)
    {
        if (static::$SHOW_LOG_SIZE && $this->batchIndex % static::$SHOW_LOG_SIZE == 0) {
            echo "{$this->totalIndex} ({$this->batchNo} - $this->batchIndex) - {$webdoc->id} ";
        }
    }

    protected function logWebdocEnd($webdoc)
    {
        if (static::$SHOW_LOG_SIZE && $this->batchIndex % static::$SHOW_LOG_SIZE == 0) {
            echo "completed\n";
        }
    }

    protected function storeLastCompleteWebdocId()
    {
        Yii::$app->cache->set($this->lastCompleteIdCacheKey(), $this->lastCompleteWebdocId);
    }

    /**
     * @param array $batchRecords
     * @param callable $callback
     */
    protected function processOnBatchRecords(&$batchRecords, $callback, $batchRecordToNull = TRUE)
    {
        $this->nextBatch();
        foreach ($batchRecords as $record) {
            $this->nextBatchElement();
            $this->logWebdocBegin($record);

            call_user_func($callback, $record);

            $this->lastCompleteWebdocId = $record->id;

            $this->logWebdocEnd($record);
        }
        $this->storeLastCompleteWebdocId();

        // Free memory.
        $record = NULL;
        if ($batchRecordToNull) {
            $batchRecords = NULL;
        }

        if ($this->makeTestError) {
            throw new ErrorException("Error made intendedly.");
        }
    }

    /**
     * @param callable $findItemFunction
     * @param int $processNo
     * @param int $fromId
     * @param int $maxId
     */
    protected function startProcess($findItemFunction, $processNo, $fromId, $maxId)
    {
        $this->initiateAfterIdMaxId($processNo, $fromId, $maxId);
        $this->batchNo = 0;
        $this->totalIndex = 0;
        do {
            $normalStop = true;
            try {
                call_user_func($findItemFunction, $this->lastCompleteWebdocId);
            } catch (Exception $e) {
                $normalStop = false;
                $this->errorCount++;
                echo "Error {$this->errorCount} times.\n";
                throw $e;
                // echo "Stop by error\n";
                // echo "  To debug, run\nphp yii wannago-spot-migrate " . $this->lastCompleteWebdocId . " " . $this->maxId . "\n";
                // break;

                // Stop explicitly.
                if ($this->makeTestError) {
                    throw $e;
                }
            }
        } while (!$normalStop);
        echo "DONE\n";

    }

    protected function lastCompleteIdCacheKey()
    {
        if (!static::$cacheKeyLastCompleteId) {
            throw new Error('Please set self::$cacheKeyLastCompleteId');
        }
        return static::$cacheKeyLastCompleteId . $this->processNo;
    }

    /**
     * Get command line arguments and options of `processNo`, `afterId`, `maxId`, `targetId`.
     *
     * The options have more priority than the arguments.
     *
     * A command line can be called in two type using arguemnt or options
     * ```shell
     * php yii wannago-spot-migrate 1 1000 2000
     * # or
     * php yii wannago-spot-migrate --processId=1 --afterId=1000 --maxId=4000 --targetId=54
     * ```
     *
     * @param int $processNo
     * @param int $afterId
     * @param int $maxId
     */
    protected function initiateAfterIdMaxId($processNo = null, $afterId = null, $maxId = null)
    {
        $this->processNo = ($this->processNo === NULL) ? $processNo : $this->processNo;
        $this->afterId = ($this->afterId === NULL) ? $afterId : $this->afterId;
        $this->maxId = ($this->maxId === NULL) ? $maxId : $this->maxId;
        if ($afterId === null) {
            $this->lastCompleteWebdocId = Yii::$app->cache->getOrSet($this->lastCompleteIdCacheKey(), function() use ($afterId) {
                return $afterId ? $afterId : 0;
            });
        } else {
            $this->lastCompleteWebdocId = $afterId;
        }
    }

    /**
     * Generate SQL where condition
     * @return string|string[]
     */
    protected function sqlWhereWithAfterAndMaxId($conditions = [])
    {
        // Ensure that $conditions is an array.
        if ($conditions && !is_array($conditions)) {
            $conditions = [$conditions];
        }

        $sqlWhere = $conditions;

        if ($this->afterId) {
            $sqlWhere[] = "{$this->afterId} < id";
        }
        if ($this->maxId) {
            $sqlWhere[] = "id <= {$this->maxId}";
        }
        if ($this->targetId) {
            $sqlWhere[] = "target_id = {$this->targetId}";
        }
        $sqlWhere = join(' AND ', $sqlWhere);
        return $sqlWhere;
    }
}
