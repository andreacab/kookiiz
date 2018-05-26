<?php
    /*******************************************************
    Title: API handler
    Authors: Kookiiz Team
    Purpose: Manage calls to Kookiiz API
    ********************************************************/

    //Mandatory dependencies
    require_once '../api/authorizations.php';
    require_once '../class/dblink.php';
    require_once '../class/exception.php';
    require_once '../class/globals.php';
    require_once '../class/lang_db.php';
    require_once '../class/request.php';
    require_once '../class/session.php';
    require_once '../class/user.php';

    //Represents a factory pattern to return an instance of a given Kookiiz API module
    class KookiizAPIFactory
    {
        //Static function to return a given Kookiiz API module
        //@module (string): API module name
        //->api (object): API module handler
        public static function getHandler($module)
        {
            if(include_once "../api/api_$module.php")
            {
                $API = ucfirst(strtolower($module)) . 'API';  //e.g. "SessionAPI"
                return new $API;
            }
            else 
                return null;
        }
    }

    //Represents a generic handler for calls made to Kookiiz API
    class KookiizAPIHandler
    {
        /**********************************************************
        PROPERTIES
        ***********************************************************/

        //API authorizations structure
        protected $AUTH;

        //Private objects
        protected $DB;          //Database connection
        protected $Lang;        //Language handler
        protected $Request;     //Request handler
        protected $User;        //API user

        //Private properties
        protected $action   = '';   //API action
        protected $response = null; //Ajax response
        protected $error    = array('code' => 0, 'type' => '');

        /**********************************************************
        CONSTRUCTOR
        ***********************************************************/

        //Class constructor
        //-> (void)
        public function __construct()
        {
            //Start session
            Session::start();

            //Set-up handlers
            $this->DB       = new DBLink('kookiiz');
            $this->Request  = new RequestHandler();
            $this->Lang     = LangDB::getHandler(Session::getLang());

            //Define response array
            $this->response = array(
                'content'       => array(),
                'parameters'    => array(),
                'key'           => ''
            );
        }

        /**********************************************************
        DESTRUCTOR
        ***********************************************************/

        //Class destructor
        //-> (void)
        public function __destruct()
        {
            $this->DB = null;
            Session::release();
        }

        /**********************************************************
        ACTION
        ***********************************************************/

        //Take appropriate action
        //-> (void)
        protected function action()
        {            
        }

        /**********************************************************
        AUTHORIZE
        ***********************************************************/

        //Check if current user has authorization for API action
        //-> (void)
        final protected function authorize()
        {
            //Check if action is authorized
            $action_auth = $this->AUTH[$this->action];
            $user_auth   = $this->User->isAdminSup()
                            ? API_AUTH_ADMINSUP
                            : ($this->User->isAdmin()
                                ? API_AUTH_ADMIN
                                : ($this->User->isLogged()
                                        ? API_AUTH_MEMBER
                                        : API_AUTH_PUBLIC));
            if($action_auth > $user_auth)
                throw new KookiizException('session', Error::SESSION_UNAUTHORIZED);
        }

        /**********************************************************
        CLOSE
        ***********************************************************/

        //Prepare and send request response
        //-> (void)
        final protected function close()
        {
            $this->response['key'] = Session::getKey();
            $this->responseSetParam('action', $this->action);
            $this->responseSetParam('error', $this->error);
            echo json_encode($this->response);

            //Terminate script
            exit();
        }

        /**********************************************************
        EXCEPTIONS
        ***********************************************************/

        //Catch API exceptions
        //@code (int):      error code
        //@type (string):   error category (defaults to default type)
        //-> (void)
        final protected function errorCatch(KookiizException $e)
        {
            $this->errorSet($e->getType(), $e->getCode());
            $this->close();
        }

        //Set error type and code
        //@type (string): error type
        //@code (int):    error code
        //-> (void)
        final protected function errorSet($type, $code)
        {
            $this->error['code'] = $code;
            $this->error['type'] = $type;
        }

        /**********************************************************
        HANDLE
        ***********************************************************/

        //Start API call handling
        //-> (void)
        final public function handle()
        {
            try
            {
                //Store API action
                $this->action = $this->Request->get('action');

                //If a session key is specified and there is no open session
                $key = $this->Request->get('key');
                if($key && Session::getStatus() == Session::STATUS_NONE)
                {
                    //Try to login using provided session key
                    if(!Session::loginKey())
                        //Session has expired (key is not valid)
                        throw new KookiizException('session', Error::SESSION_EXPIRED);
                }

                //Set-up connected user profile
                $this->User = new User($this->DB);
                $this->User->visit();   //Attest for user's visit               
                $this->authorize();     //Check authorization for current API action

                //Take appropriate action depending on module
                if($this->action)   
                    $this->action();
                else                
                    $this->close();             

                //Terminate API call
                $this->close();
            }
            catch(KookiizException $e)
            {
                $this->errorCatch($e);
            }
        }

        /**********************************************************
        RESPONSE
        ***********************************************************/

        //Set response content
        //@content (mixed): response content data
        //-> (void)
        final protected function responseSetContent($content)
        {
            $this->response['content'] = $content;
        }

        //Set value of a response param
        //@key (string):    param name
        //@value (mixed):   param value
        //-> (void)
        final protected function responseSetParam($key, $value)
        {
            $this->response['parameters'][$key] = $value;
        }

        //Set a list of response params
        //@params (array): list of name/value pairs
        //-> (void)
        final protected function responseSetParams(array $params)
        {
            foreach($params as $name => $value)
                $this->responseSetParam($name, $value);
        }
    }
?>
