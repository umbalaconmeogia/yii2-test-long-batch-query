<?php
namespace console\controllers;

use common\models\User;
use giannisdag\yii2CheckLoginAttempts\models\LoginAttempt;
use yii\console\Controller;

class UserController extends Controller
{
    /**
     * @var array
     */
    protected $actionOptions = [
        'create-user' => [
            'username',
            'password',
            'email',
            'name',
            'privilege'
        ],
    ];

    /**
     * @var string
     */
    public $username;

    /**
     * @var string
     */
    public $password;

    /**
     * @var string
     */
    public $email;

    /**
     * @var string
     */
    public $name;

    /**
     * @var int
     */
    public $privilege;

    /**
     * {@inheritDoc}
     * @see \yii\console\Controller::options()
     */
    public function options($actionID)
    {
        $result = [];
        if (isset($this->actionOptions[$actionID])) {
            $result = $this->actionOptions[$actionID];
        }
        return $result;
    }

    /**
     * Syntax
     * ```shell
     *   php yii user/create-user --username=<username> --password=<password> --email=<email> --name=<name>
     * ```
     * Example
     * Create new User
     *   ```shell
     *     php yii user/create-user --password=password --email=thanhtt@hybrid-technologies.co.jp --privilege=1
     *   ```
     * Create new User
     *   ```shell
     *     php yii user/create-user --email=thanhtt@hybrid-technologies.co.jp --name=ThanhTT
     *   ```
     */
    public function actionCreateUser()
    {
        if (!$this->username && $this->email) {
            $this->username = $this->email;
        }
        User::getDb()->transaction(function() {
            User::createUser([
                'username' => $this->username,
                'password' => $this->password,
                'email' => $this->email,
                'name' => $this->name,
                'privilege' => $this->privilege
            ]);
        });
        echo "DONE\n";
    }

    /**
     * After three times of login failure, user will be locked for a while.
     * To clear all user locking, run
     * ```shell
     * php yii user/clear-login-failure
     * ```
     * Syntax:
     *   php yii user/clear-login-failure
     */
    public function actionClearLoginFailure($username = NULL)
    {
        LoginAttempt::deleteAll();
        echo "DONE\n";
    }

    /**
     * Syntax:
     *   php yii user/create-default-users
     */
    public function actionCreateDefaultUsers()
    {
        User::getDb()->transaction(function() {
            $emails = [
                'thanhtt@hybrid-technologies.co.jp' => 'ThanhTT',
                'vietnd@hybrid-technologies.co.jp' => 'VietND',
                'n.nakagawa@hybrid-technologies.co.jp' => '中川',
                'haitt@hybrid-technologies.co.jp' => 'HaiTT',
                'vinhnt@hybrid-technologies.co.jp' => 'vinhNT',
            ];
            foreach ($emails as $email => $name) {
                User::createUser([
                    'username' => $email,
                    'email' => $email,
                    'name' => $name,
                ]);
            }
        });
        echo "DONE\n";
    }
}
