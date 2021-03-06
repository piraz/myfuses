<?php
/**
 * MyFuses Framework (http://myfuses.candango.org)
 *
 * This product includes software developed by the Fusebox Corporation
 * (http://www.fusebox.org/).
 *
 * @link      http://github.com/candango/myfuses
 * @copyright Copyright (c) 2006 - 2017 Flavio Garcia
 * @license   https://www.apache.org/licenses/LICENSE-2.0  Apache-2.0
 */

require_once 'myfuses/util/security/MyFusesAbstractSecurityManager.php';

/**
 * MyFusesAbstractSecurityPlugin  - MyFusesAbstractSecurityPlugin.php
 * 
 * Plugin that controls all authentication and authorization workflows over an
 * application.
 *
 * @category   plugins
 * @package    myfuses.plugins
 * @author     Flavio Garcia <piraz at candango.org>
 * @since      a10e2e12abf0f387df778bf633b4dfa4efd37515
 */
abstract class MyFusesAbstractSecurityPlugin extends AbstractPlugin
{
    /**
     * Application login fuseaction
     *
     * @var string
     */
    private static $loginAction = "";

    /**
     * Application logout fuseaction
     *
     * @var string
     */
    private static $logoutAction = "";

    /**
     * Action that plugin will redirect when susseful login ends
     *
     * @var string
     */
    private static $nextAction = "";

    /**
     * Plugin listeners path
     *
     * @var array
     */
    private static $listenersPath = array('plugins/');

    /**
     * Application authentication fuseaction
     *
     * @var string
     */
    private static $authenticationAction = "";

    /**
     * Return application login action
     *
     * @return string
     */
    private static function getLoginAction()
    {
        return self::$loginAction;
    }

    /**
     * Set application login action
     *
     * @param string $loginAction
     */
    private static function setLoginAction($loginAction)
    {
        self::$loginAction = $loginAction;
        MyFuses::getInstance()->getRequest()->getAction()->addXFA(
                "goToLoginAction", $loginAction);
    }

    /**
     * Return application logout action
     *
     * @return string
     */
    private static function getLogoutAction()
    {
        return self::$logoutAction;
    }

    /**
     * Set application logout action
     *
     * @param string $logoutAction
     */
    private static function setLogoutAction($logoutAction)
    {
        self::$logoutAction = $logoutAction;
        MyFuses::getInstance()->getRequest()->getAction()->addXFA(
                "goToLogoutAction", $logoutAction);
    }

    /**
     * Return next action
     *
     * @return string
     */
    private static function getNextAction()
    {
        return self::$nextAction;
    }

    /**
     * Set next action
     *
     * @param string $nextAction
     */
    private static function setNextAction($nextAction)
    {
        self::$nextAction = $nextAction;
        MyFuses::getInstance()->getRequest()->getAction()->addXFA(
                "goToNextAction", $nextAction);
    }

    /**
     * Return application authentication action
     *
     * @return string
     */
    private static function getAuthenticationAction()
    {
        return self::$authenticationAction;
    }

    /**
     * Set application authentication action
     *
     * @param string $authenticationAction
     */
    private static function setAuthenticationAction($authenticationAction)
    {
        self::$authenticationAction = $authenticationAction;
        MyFuses::getInstance()->getRequest()->getAction()->addXFA(
                "goToAuthenticationAction", $authenticationAction);
    }

    /**
     * Return listeners path array
     *
     * @return array
     */
    public static function getListenersPath()
    {
        return self::$listenersPath;
    }

    /**
     * Add one path to listeners path array if the path doesn't exists 
     *
     * @param string $path
     */
    public static function addListenerPath($path)
    {
        if (!in_array($path, self::$listenersPath))
        {
            self::$listenersPath[] = $path;    
        }
    }

	/**
	 * Verify if the session was started. If not start the session
	 */
	protected function checkSession()
    {
        if(!isset($_SESSION))
        {
            session_start();
        }
	}

	/**
	 * Run pre process actions
	 *
	 */
    protected function runPreProcess()
    {
        $manager = MyFusesAbstractSecurityManager::getInstance();
        $manager->createCredential();

        $this->configurePlugin();
        $this->configureSecurityManager($manager);
        $this->authenticate($manager);

        // TODO: SO!? What to do with this credential
        $credential = $_SESSION['MYFUSES_SECURITY_CREDENTIAL'];
    }

    /**
     * Run pre process fuseaction
     *
     */
    protected function runPreFuseaction()
    {
    	$manager = MyFusesAbstractSecurityManager::getInstance();
        if($manager->isAuthenticated())
        {
    	    foreach ($manager->getAuthorizationListeners() as $listener)
    	    {
                $listener->authorize($manager);
            }
        }
    }

	/**
	 * Configure plugin reading the his parameters
	 *
	 */
    private function configurePlugin()
    {
        foreach($this->getParameter("ListenersPath") as $path)
        {
            self::addListenerPath($path);
        }
    }

    /**
     * Configure the security manager plugin
     * 
     * @param $manager
     */
    public function configureSecurityManager(MyFusesSecurityManager$manager)
    {
        $this->configureParameters($manager);

        $authenticationListeners = $this->getParameter(
            "AuthenticationListener");

        $authorizationListeners = $this->getParameter("AuthorizationListener");

        foreach ($this->getListenersPath() as $path)
        {
            if (!MyFusesFileHandler::isAbsolutePath($path))
            {
                $path = $this->getApplication()->getPath() . $path;
            }

            foreach ($authenticationListeners as $listener)
            {
                if(file_exists($path . $listener . ".php"))
                {
                    require_once $path . $listener . ".php";

                    $manager->addAuthenticationListener(new $listener());
                }
            }

            foreach ($authorizationListeners as $listener)
            {
                if (file_exists($path . $listener . ".php"))
                {
                    require_once $path . $listener . ".php";

                    $manager->addAuthorizationListener(new $listener());
                }
            }
        }
    }

    /**
     * Configure all plugins plugins parameters
     * 
     * @param $manager
     */
    public function configureParameters(MyFusesSecurityManager $manager)
    {
    	// getting next action
        $nextAction = $this->getParameter("NextAction");

        if (count($nextAction))
        {
            $nextAction = $nextAction[0];
        } else {
            $nextAction = $this->getApplication()->getDefaultFuseaction();
        }

        self::setNextAction($nextAction);

        // getting login action
        $loginAction = $this->getParameter("LoginAction");

        if (count($loginAction))
        {
            $loginAction = $loginAction[0];
        } else {
            $loginAction = $this->getApplication()->getDefaultFuseaction();
        }

        self::setLoginAction($loginAction);

        // getting logout action
        $logoutAction = $this->getParameter("LogoutAction");

        if (count($logoutAction))
        {
            $logoutAction = $logoutAction[0];
        } else {
            $logoutAction = "";
        }

        self::setLogoutAction($logoutAction);

        // getting login action    
        $authenticationAction = $this->getParameter("AuthenticationAction");

        if (count($logoutAction))
        {
            $authenticationAction = $authenticationAction[0];
        } else {
            $authenticationAction = "";
        }

        self::setAuthenticationAction($authenticationAction);
    }

    /**
     * Authenticating user
     *
     * @param MyFusesSecurityManager $manager
     */
    public function authenticate(MyFusesSecurityManager $manager)
    {
    	MyFuses::getInstance()->getRequest()->getAction()->addXFA(
    	    "goToIndexAction",
            $this->getApplication()->getDefaultFuseaction()
        );

    	// getting next action
        $nextAction = self::getNextAction();

        // getting login action
        $loginAction = self::getLoginAction();

        // getting logout action
        $logoutAction = self::getLogoutAction();

        // getting login action    
        $authenticationAction = self::getAuthenticationAction();

        $currentAction = MyFuses::getInstance()->getRequest()->
            getFuseActionName();

        if ($logoutAction == $currentAction)
        {
            if ($manager->isAuthenticated())
            {
                $manager->logout();
            } else {
                MyFuses::sendToUrl(MyFuses::getMySelfXfa("goToLoginAction"));
        	}
        }

        if ((strtolower( MyFuses::getInstance()->getRequest()->getAction()->
            getCustomAttribute("security", "enabled")) != "false"))
        {
            if ($loginAction != $currentAction && $authenticationAction !=
                $currentAction)
            {
                if (!$manager->isAuthenticated())
                {
                    MyFuses::sendToUrl(MyFuses::getMySelfXfa(
                        "goToLoginAction"));
                }
            }

            if(!$manager->isAuthenticated())
            {
                if (MyFuses::getInstance()->getRequest()->getFuseActionName()
                    == $this->getAuthenticationAction())
                {
                    $manager->clearMessages();

                    $error = false;

                    foreach ($manager->getAuthenticationListeners() as
                        $listener)
                    {
                        $listener->authenticate($manager);
                    }

                    if(!$manager->isAuthenticated())
                    {
                        MyFuses::sendToUrl( MyFuses::getMySelfXfa(
                            "goToLoginAction"));
                    } else {
                        MyFuses::sendToUrl( MyFuses::getMySelfXfa(
                            "goToNextAction"));
                    }
                }
            } else {
            	$currentAction =
            	   MyFuses::getInstance()->getRequest()->getFuseActionName();

            	if ($currentAction == $this->getAuthenticationAction() ||
            	    $currentAction == $this->getLoginAction())
            	{
                    MyFuses::sendToUrl(MyFuses::getMySelfXfa(
                        "goToNextAction"));
                }
            }
        }
    }
}
