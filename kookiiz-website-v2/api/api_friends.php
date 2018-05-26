<?php
    /*******************************************************
    Title: API friends
    Authors: Kookiiz Team
    Purpose: API module for friends-related actions
    ********************************************************/

    //Dependencies
    require_once '../api/api.php';
    require_once '../class/email.php';
    require_once '../class/events_lib.php';
    require_once '../class/recipes_lib.php';
    require_once '../class/social.php';
    require_once '../class/users_lib.php';

    //Represents an API handler for friends-related actions
    class FriendsAPI extends KookiizAPIHandler
    {
        /**********************************************************
        PROPERTIES
        ***********************************************************/

        const MODULE = 'friends';

        /**********************************************************
        CONSTRUCTOR
        ***********************************************************/

        /**
         * Class constructor
         * @global Array $API_AUTHORIZATIONS auth. level for each action
         */
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

        /**
         * Class destructor
         */
        public function __destruct()
        {
            parent::__destruct();
        }

        /**********************************************************
        ACTION
        ***********************************************************/

        /**
         * Take appropriate action
         */
        protected function action()
        {
            switch($this->action)
            {
                case 'add':
                    $this->add();
                    break;
                case 'block':
                    $this->block();
                    break;
                case 'deny':
                    $this->deny();
                    break;
                case 'remove':
                    $this->remove();
                    break;
                case 'share':
                    $this->share();
                    break;
                case 'unshare':
                    $this->unshare();
                    break;
            }
        }

        /**********************************************************
        ADD
        ***********************************************************/

        /**
         * Request to create a friendship link
         */
        private function add()
        {
            //Load and store parameters
            $friend_id = (int)$this->Request->get('friend_id');
            $this->responseSetParam('friend_id', $friend_id);

            //Create friendship link
            $status = $this->User->friends_add($friend_id);
            $this->responseSetParam('status', $status);

            //Send email request to target user
            if($status == C::FRIEND_STATUS_PENDING)
            {
                $UsersLib   = new UsersLib($this->DB, $this->User);
                $recipients = $UsersLib->getRecipients(array($friend_id), EmailHandler::TYPE_FRIENDREQUEST);
                if(count($recipients) && isset($recipients[0]))
                {
                    //Send email
                    $EmailHandler = new EmailHandler($this->DB);
                    $EmailHandler->pattern(EmailHandler::TYPE_FRIENDREQUEST, array(
                        'content'       => 'text',
                        'name'          => $this->User->getName(),
                        'recipient'     => $recipients[0]['email'],
                        'friend_name'   => $recipients[0]['firstname']
                    ));
                }
            }
        }

        /**********************************************************
        BLOCK
        ***********************************************************/

        /**
         * Request to block a friend
         */
        private function block()
        {
            //Load and store parameters
            $friend_id = (int)$this->Request->get('friend_id');
            $this->responseSetParam('friend_id', $friend_id);

            //Block user
            $this->User->friends_block($friend_id);

            //Reload friends list
            $this->responseSetContent($this->User->friends_export());
        }

        /**********************************************************
        DENY
        ***********************************************************/

        /**
         * Request to deny a friendship request
         */
        private function deny()
        {
            //Load and store parameters
            $friend_id = (int)$this->Request->get('friend_id');
            $this->responseSetParam('friend_id', $friend_id);

            //Block user
            $this->User->friends_deny($friend_id);
            
            //Reload friends list
            $this->responseSetContent($this->User->friends_export());
        }

        /**********************************************************
        REMOVE
        ***********************************************************/

        /**
         * Request to remove a user from friends
         */
        private function remove()
        {
            //Load and store parameters
            $friend_id = (int)$this->Request->get('friend_id');
            $this->responseSetParam('friend_id', $friend_id);

            //Remove user from friends
            $this->User->friends_remove($friend_id);
            //Delete shared content
            $SocialHandler = new SocialHandler($this->DB, $this->User);
            $SocialHandler->dismiss($friend_id);
        }

        /**********************************************************
        SHARE
        ***********************************************************/

        /**
         * Request to share some content with a friend
         */
        private function share()
        {
            //Load and store parameters
            $friend_id    = (int)$this->Request->get('friend_id');
            $content_type = $this->Request->get('content_type');
            $this->responseSetParam('friend_id', $friend_id);
            $this->responseSetParam('content_type', $content_type);

            //Retrieve current user friends and check that recipient is among them
            $friends_ids = $this->User->friends_ids_get();
            if($friend_id && !in_array($friend_id, $friends_ids))
                throw new KookiizException('friends', 9);

            //Take appropriate action depending on content type
            $SocialHandler = new SocialHandler($this->DB, $this->User);
            switch($content_type)
            {
                case 'recipe':
                    $event_type = EventsLib::TYPE_SHARERECIPE;
                    $recipe_id  = (int)$this->Request->get('recipe_id');
                    $this->responseSetParam('recipe_id', $recipe_id);

                    //Share recipe
                    $share_id = $SocialHandler->share_recipe($friend_id, $recipe_id);
                    break;

                case 'shopping':
                    break;

                case 'status':
                    $event_type  = EventsLib::TYPE_SHARESTATUS;
                    $status_type = (int)$this->Request->get('status_type');
                    $content_id  = (int)$this->Request->get('content_id');
                    $comment     = $this->Request->get('comment');
                    $summary     = $this->Request->get('summary');
                    $networks    = json_decode($this->Request->get('networks'), true);

                    //Publish on Kookiiz
                    $share_id = $SocialHandler->share_status($status_type, $content_id, $comment);

                    //Publish on social networks
                    $tabs = $this->Lang->get('URL_HASH_TABS');
                    $url = 'http://www.kookiiz.com' . ($content_id ? "/{$tabs[4]}-$content_id" : '');
                    $text = ($summary ? $summary . '. ' : '') . $comment;
                    foreach($networks as $network => $enabled)
                    {
                        if($enabled)
                        {
                            switch($network)
                            {
                                case 'facebook':
                                    if($content_id)
                                    {
                                        $RecipesLib = new RecipesLib($this->DB, $this->User);
                                        $data = $RecipesLib->load(array($content_id));
                                        if(count($data))
                                        {
                                            $recipe = $data[0];
                                            $desc = $recipe['desc'];
                                            if(strlen($desc) > 200)
                                                $desc = substr($desc, 0, 200) . '...';
                                            $picID = $recipe['pic'];
                                            $pic = $picID ? "http://www.kookiiz.com/pics/recipes-$picID-tb" : '';
                                        }
                                        else
                                        {
                                            $url = 'http://www.kookiiz.com';
                                            $desc = ''; $pic = '';
                                        }
                                        $SocialHandler->facebookPost($text, $url, $cap = 'www.kookiiz.com', $desc, $pic);
                                    }
                                    else
                                        $SocialHandler->facebookPost($text, $url);
                                    break;
                                
                                case 'twitter':
                                    $short_url = $SocialHandler->bitly_shorten($url);
                                    $SocialHandler->twitter_tweet($text, $short_url);
                                    break;
                            }
                        }
                    }
                    break;
            }

            //Register event
            $EventsLib = new EventsLib($this->DB, $this->User);
            $EventsLib->register($this->User->getID(), $event_type, $public = false, $share_id);
        }

        /**
         * Request to accept a sharing proposal
         */
        private function share_accept()
        {
            //Load and store parameters
            $friend_id    = (int)$this->Request->get('friend_id');
            $content_type = $this->Request->get('content_type');
            $this->responseSetParam('friend_id', $friend_id);
            $this->responseSetParam('content_type', $content_type);

            //Take appropriate action depending on content type
            switch($content_type)
            {
                case 'shopping':
                    break;
            }
        }

        /**
         * Request to deny a sharing proposal
         */
        private function share_deny()
        {
            //Load and store parameters
            $friend_id    = (int)$this->Request->get('friend_id');
            $content_type = $this->Request->get('content_type');
            $this->responseSetParam('friend_id', $friend_id);
            $this->responseSetParam('content_type', $content_type);

            //Take appropriate action depending on content type
            switch($content_type)
            {
                case 'shopping':
                    break;
            }
        }

        /**********************************************************
        UNSHARE
        ***********************************************************/

        /**
         * Request to cancel sharing action
         */
        private function unshare()
        {
            //Load and store parameters
            $friend_id    = (int)$this->Request->get('friend_id');
            $canceled_by  = $this->Request->get('canceled_by');
            $content_type = $this->Request->get('content_type');
            $this->responseSetParam('friend_id', $friend_id);
            $this->responseSetParam('canceled_by', $canceled_by);
            $this->responseSetParam('content_type', $content_type);
            
            //Take appropriate action depending on content type
            $SocialHandler = new SocialHandler($this->DB, $this->User);
            switch($content_type)
            {
                case 'recipe':
                    $recipe_id = (int)$this->Request->get('recipe_id');
                    $this->responseSetParam('recipe_id', $recipe_id);

                    //Cancel recipe sharing
                    $SocialHandler->unshare_recipe($friend_id, $recipe_id, $canceled_by);
                    break;
                case 'shopping':
                    break;
            }
        }
    }
?>