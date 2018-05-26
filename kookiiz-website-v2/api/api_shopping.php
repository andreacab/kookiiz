<?php
    /*******************************************************
    Title: API shopping
    Authors: Kookiiz Team
    Purpose: API module for shopping-related actions
    ********************************************************/

    //Dependencies
    require_once '../api/api.php';
    require_once '../class/email.php';

    //Represents an API handler for shopping-related actions
    class ShoppingAPI extends KookiizAPIHandler
    {
        /**********************************************************
        PROPERTIES
        ***********************************************************/

        const MODULE = 'shopping';

        /**********************************************************
        CONSTRUCTOR
        ***********************************************************/

        //Class constructor
        //-> (void)
        public function __construct()
        {
            global $API_AUTHORIZATIONS;

            //Construct generic API handler
            parent::__construct();

            //Define authorizations for current API module
            $this->AUTH = $API_AUTHORIZATIONS[self::MODULE];
        }

        /**********************************************************
        DESTRUCTOR
        ***********************************************************/

        //Class destructor
        //-> (void)
        public function __destruct()
        {
            parent::__destruct();
        }

        /**********************************************************
        ACTION
        ***********************************************************/

        //Take appropriate action
        //-> (void)
        protected function action()
        {
            switch($this->action)
            {
                case 'market_create':
                    $this->market_create();
                    break;
                case 'market_delete':
                    $this->market_delete();
                    break;
                case 'market_save':
                    $this->market_save();
                    break;
                case 'market_select':
                    $this->market_select();
                    break;
                case 'send':
                    $this->send();
                    break;
            }
        }

        /**********************************************************
        MARKETS
        ***********************************************************/

        //Request to create a new market configuration
        //-> (void)
        private function market_create()
        {
            //Load and store parameters
            $name   = $this->Request->get('name');
            $order  = json_decode($this->Request->get('order'), true);
            $this->responseSetParam('name', $name);
            $this->responseSetParam('order', $order);

            //Create market configuration and return its ID
            $market_id = $this->User->markets_create($name, $order);
            $this->responseSetParam('market_id', $market_id);
            $this->responseSetParam('time', $this->User->updates_get('markets'));
        }

        //Request to delete an existing market
        //-> (void)
        private function market_delete()
        {
            //Load and store parameters
            $market_id = (int)$this->Request->get('market_id');
            $this->responseSetParam('market_id', $market_id);

            //Delete market configuration and store new timestamp
            $time = $this->User->markets_delete($market_id);
            $this->responseSetParam('time', $this->User->updates_get('markets'));
        }

        //Request to save a market configuration
        //-> (void)
        private function market_save()
        {
            //Load and store parameters
            $market_id = (int)$this->Request->get('market_id');
            $order  = json_decode($this->Request->get('order'), true);
            $this->responseSetParam('market_id', $market_id);
            $this->responseSetParam('order', $order);

            //Select market configuration and store new timestamp
            $time = $this->User->markets_save($market_id, $order);
            $this->responseSetParam('time', $this->User->updates_get('markets'));
        }

        //Request to set a market configuration as default
        //-> (void)
        private function market_select()
        {
            //Load and store parameters
            $market_id = (int)$this->Request->get('market_id');
            $this->responseSetParam('market_id', $market_id);

            //Select market configuration and store new timestamp
            $time = $this->User->markets_select($market_id);
            $this->responseSetParam('time', $this->User->updates_get('markets'));
        }

        /**********************************************************
        SEND
        ***********************************************************/

        //Request to send shopping list by email
        //-> (void)
        private function send()
        {
            //Load and store parameters
            $shopping = array_values(json_decode($this->Request->get('shopping'), true));
            $email    = $this->Request->get('email');
            $this->responseSetParam('email', $email);

            //Prepare shopping list text
            $list = '';
            $groups_names = $this->Lang->get('INGREDIENTS_GROUPS_NAMES');
            foreach($shopping as $group)
            {
                //Category title
                $group_id   = (int)$group['group'];
                $group_name = $groups_names[$group_id];
                $list .= "<strong>$group_name</strong><br/>";

                //Loop through ingredients of this category
                foreach($group['ingredients'] as $ingredient)
                {
                    $list .= "$ingredient<br/>";
                }

                //Loop through items of this category
                foreach($group['items'] as $item)
                {
                    $list .= "$item<br/>";
                }
            }

            //Send email
            $EmailHandler = new EmailHandler($this->DB);
            $EmailHandler->pattern(EmailHandler::TYPE_SHOPPING, array(
                'content'   => 'html',
                'recipient' => $email,
                'name'      => $this->User->getName(),
                'list'      => $list
            ));
        }
    }
?>