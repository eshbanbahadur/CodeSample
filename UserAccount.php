<?php
/**
 * Class UserAccount
 *
 * Manages user record
 *
 * @package controllers\common
 * @category Application Controller
 * @author  Eshban Bahadur - eshban@gmail.com
*/
namespace controllers\common;

use framework\Controller;
use framework\Model;
use framework\User;
use framework\View;
use models\beans\BeanUser;
use models\common\UserAccount as UserModel;
use views\common\UserAccount as UserView;
use framework\components\Record;
use framework\BeanAdapter;


class UserAccount extends Controller
{
    protected $view;
    protected $model;
    private $currentLoggedUser;
    private $currentEditingUser;
    private $onlyCurrent = false;
    private $isAdmin = false;
    /**
    * Object constructor.
    *
    * @param View $view
    * @param Model $mode
    */
    public function __construct(View $view=null, Model $model=null)
    {
        // Computes variables to store current edited record
        // and current logged user
        $user = new User();
        $this->currentLoggedUser = $user->getId();
        $this->currentEditingUser = $_GET["id_user"];

        // Grants access to non admin only on editing its own record
        if ($this->currentLoggedUser == $this->currentEditingUser ) {
            
            $nonAdminRoles = $user->query("SELECT " . USER_ROLE . " FROM ". USER_TABLE . " WHERE " . USER_ROLE . "!=" . ADMIN_ROLE_ID);
            if ($nonAdminRoles){
                while ($role=$nonAdminRoles->fetch_array()){
                    $this->grantRole($role[0]);
                }
            }
            $this->onlyCurrent = true;
        }

        // Computes if current user is admin
        if ($user->getRole()==ADMIN_ROLE_ID)
            $this->isAdmin = true;

        $this->grantRole(ADMIN_ROLE_ID);
        $this->restrictToRBAC(null,"common/user_accounts",LoginRBACWarningMessage);
        $this->view = empty($view) ? $this->getView() : $view;
        $this->model = empty($model) ? $this->getModel() : $model;
        parent::__construct($this->view,$this->model);
    }

    /**
    * Autorun method. 
    * @param mixed|null $parameters Parameters to manage
    *
    */
    protected function autorun($parameters = null)
    {
        $options = $this->model->getAccessLevelOptionsList();
        $this->view->renderAccessLevelOptionsList($options);
        $this->buildRecord();
        if ($this->onlyCurrent && !$this->isAdmin){
            $this->view->setVar("NoUpdate","hide");
        } else {
            $this->view->setVar("NoUpdate","");
        }
    }

   
}