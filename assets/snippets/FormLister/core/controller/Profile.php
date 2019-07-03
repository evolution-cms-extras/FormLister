<?php namespace FormLister;

use DocumentParser;

/**
 * Контроллер для редактирования профиля
 * Class Profile
 * @package FormLister
 */
class Profile extends Core
{
    /**
     * @var \modUsers
     */
    public $user = null;

    /**
     * Profile constructor.
     * @param DocumentParser $modx
     * @param array $cfg
     */
    public function __construct(DocumentParser $modx, $cfg = array())
    {
        parent::__construct($modx, $cfg);
        $this->lexicon->fromFile('profile');
        $this->log('Lexicon loaded', array('lexicon' => $this->lexicon->getLexicon()));
        $uid = $modx->getLoginUserId('web');
        if ($uid) {
            /* @var $user \modUsers */
            $user = $this->loadModel(
                $this->getCFGDef('model', '\modUsers'),
                $this->getCFGDef('modelPath', 'assets/lib/MODxAPI/modUsers.php')
            );
            $this->user = $user->edit($uid);
            $this->config->setConfig(array(
                'userdata' => $this->user->toArray()
            ));
        }
    }

    /**
     * Загружает в formData данные не из формы
     * @param string $sources список источников
     * @param string $arrayParam название параметра с данными
     * @return $this
     */
    public function setExternalFields ($sources = 'array', $arrayParam = 'defaults')
    {
        parent::setExternalFields($sources, $arrayParam);
        parent::setExternalFields('array', 'userdata');

        return $this;
    }


    /**
     * @return string
     */
    public function render()
    {
        if (is_null($this->user) || !$this->user->getID()) {
            $this->redirect('exitTo');
            $this->renderTpl = $this->getCFGDef('skipTpl', $this->translate('profile.default_skipTpl'));
            $this->setValid(false);
        }

        return parent::render();
    }


    /**
     * @param string $param
     * @return array|mixed|\xNop
     */
    public function getValidationRules($param = 'rules')
    {
        $rules = parent::getValidationRules($param);
        $password = $this->getField('password');
        if (empty($password) || !is_scalar($password)) {
            $this->forbiddenFields[] = 'password';
            if (isset($rules['password'])) {
                unset($rules['password']);
            }
            if (isset($rules['repeatPassword'])) {
                unset($rules['repeatPassword']);
            }
        } else {
            if (isset($rules['repeatPassword']['equals'])) {
                $rules['repeatPassword']['equals']['params'] = $this->getField('password');
            }
        }

        return $rules;
    }

    /**
     * @param $fl
     * @param $value
     * @return bool
     */
    public static function uniqueEmail($fl, $value)
    {
        $result = true;
        if (is_scalar($value) && !is_null($fl->user) && ($fl->user->get("email") !== $value)) {
            /* @var $user \modUsers */
            $user = clone($fl->user);
            $user->set('email', $value);
            $result = $user->checkUnique('web_user_attributes', 'email', 'internalKey');
        }

        return $result;
    }

    /**
     * @param $fl
     * @param $value
     * @return bool
     */
    public static function uniqueUsername($fl, $value)
    {
        $result = true;
        if (is_scalar($value) && !is_null($fl->user) && ($fl->user->get("email") !== $value)) {
            /* @var $user \modUsers */
            $user = clone($fl->user);
            $user->set('username', $value);
            $result = $user->checkUnique('web_users', 'username');
        }

        return $result;
    }

    /**
     *
     */
    public function process()
    {
        if ($this->user->get('username') == $this->user->get('email') && !empty($this->getField('email')) && empty($this->getField('username'))) {
            $this->setField('username', $this->getField('email'));
            if (!empty($this->allowedFields)) {
                $this->allowedFields[] = 'username';
            }
            if (!empty($this->forbiddenFields)) {
                $_forbidden = array_flip($this->forbiddenFields);
                unset($_forbidden['username']);
                $this->forbiddenFields = array_keys($_forbidden);
            }
        }

        $newpassword = $this->getField('password');
        $password = $this->user->get('password');
        if (!empty($newpassword) && ($password !== $this->user->getPassword($newpassword))) {
            if (!empty($this->allowedFields)) $this->allowedFields[] = 'password';
            if (!empty($this->forbiddenFields)) {
                $_forbidden = array_flip($this->forbiddenFields);
                unset($_forbidden['password']);
                $this->forbiddenFields = array_keys($_forbidden);
            }
        }
        $fields = $this->filterFields($this->getFormData('fields'), $this->allowedFields, $this->forbiddenFields);
        if (isset($fields['username'])) {
            $fields['username'] = is_scalar($fields['username']) ? $fields['username'] : '';
        }
        if (isset($fields['email'])) {
            $fields['email'] = is_scalar($fields['email']) ? $fields['email'] : '';
        }
        $result = $this->user->fromArray($fields)->save(true);
        $this->log('Update profile', array('data' => $fields, 'result' => $result, 'log' => $this->user->getLog()));
        if ($result) {
            $this->setFormStatus(true);
            $this->user->close();
            $this->setFields($this->user->edit($result)->toArray());
            $this->setField('user.password', $newpassword);
            $this->runPrepare('preparePostProcess');
            if (!empty($newpassword) && ($password !== $this->user->getPassword($newpassword))) {
                $this->user->logOut('WebLoginPE', true);
                $this->redirect('exitTo');
            }
            $this->redirect();
            if ($successTpl = $this->getCFGDef('successTpl')) {
                $this->renderTpl = $successTpl;
            } else {
                $this->addMessage($this->translate('profile.update_success'));
            }
        } else {
            $this->addMessage($this->translate('profile.update_failed'));
        }
    }
}
