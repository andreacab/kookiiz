<?php
    /*******************************************************
    Title: Social
    Authors: Kookiiz Team
    Purpose: Provide sharing functionalities between users
    ********************************************************/

    //Dependencies
    require_once '../class/curl.php';
    require_once '../class/dblink.php';
    require_once '../class/facebook.php';
    require_once '../class/globals.php';
    require_once '../class/twitter_oauth.php';
    require_once '../class/user.php';
    require_once '../secure/bitly.php';
    require_once '../secure/facebook.php';
    require_once '../secure/twitter.php';

    //Represents a handler for social interactions
    class SocialHandler
    {
        /**********************************************************
        CONSTANTS
        ***********************************************************/

        const TWITTER_MAX   = 140;              //Max chars for tweets
        const TWITTER_VIA   = 'KookiizRecipes'; //Reference account

        /**********************************************************
        PROPERTIES
        ***********************************************************/

        private $DB;
        private $User;

        /**********************************************************
        CONSTRUCTOR
        ***********************************************************/

        /**
         * Class constructor
         * @param DBLink $DB open database connection
         * @param User $User current user
         */
        public function __construct(DBLink &$DB, User &$User)
        {
            $this->DB   = $DB;
            $this->User = $User;
        }

        /**********************************************************
        BITLY
        ***********************************************************/

        /**
         * Shorten URL using Bit.ly service
         * @param String $url original URL to shorten
         * @return String shortened URL (or original URL in case of failure)
         */
        public function bitly_shorten($url)
        {
            $cURL = new cURLHandler();
            
            //Prepare request
            $url = urlencode($url);
            $request = 'http://api.bit.ly/v3/shorten?login=' . BITLY_LOGIN
                     . '&apiKey=' . BITLY_KEY . '&longUrl=' . $url . '&format=json';          
            //Read response
            $response = json_decode($cURL->read($request), true);
            $status   = (int)$response['status_code'];            
            //Return shortened or original URL
            if($status == C::HTTP_STATUS_OK)  
                return $response['data']['url'];
            else
                return $url;
        }

        /**********************************************************
        DISMISS
        ***********************************************************/

        /**
         * Remove all social interactions between current user and provided friend
         * @param Int $friend_id unique ID of the friend to dismiss
         */
        public function dismiss($friend_id)
        {
            $user_id = $this->User->getID();

            //Delete shared recipes
            $request = 'DELETE FROM shared_recipes'
                    . ' WHERE (user_1 = ? AND user_2 = ?)'
                        . ' OR (user_1 = ? AND user_2 = ?)';
            $params = array($user_id, $friend_id, $friend_id, $user_id);
            $this->DB->query($request, $params);

            //Remove from mutual invitations
            $request = 'SELECT invitation_id FROM invitations WHERE user_id = ?';
            $stmt = $this->DB->query($request, array($user_id));
            $invits = array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN, 0));
            if(count($invits))
            {
                $request = 'DELETE FROM invitations_guests'
                        . ' WHERE invitation_id IN (' . implode(', ', $invits) . ')'
                            . ' AND guest_id = ?';
                $this->DB->query($request, array($friend_id));
            }
            $request = 'SELECT invitation_id FROM invitations WHERE user_id = ?';
            $stmt = $this->DB->query($request, array($friend_id));
            $invits = array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN, 0));
            if(count($invits))
            {
                $request = 'DELETE FROM invitations_guests'
                        . ' WHERE invitation_id IN (' . implode(', ', $invits) . ')'
                            . ' AND guest_id = ?';
                $this->DB->query($request, array($user_id));
            }

            //Retrieve users menu IDs
            $user_menu = 0; $friend_menu = 0;
            $request = 'SELECT user_id, menu_id FROM users_menus'
                    . ' WHERE user_id IN (?, ?)';
            $stmt = $this->DB->query($request, array($user_id, $friend_id));
            while($menu = $stmt->fetch())
            {
                if($menu['user_id'] == $user_id)        
                    $user_menu = (int)$menu['menu_id'];
                else if($menu['user_id'] == $friend_id) 
                    $friend_menu = (int)$menu['menu_id'];
            }

            //Delete shared shopping lists
            $request = 'DELETE FROM shared_shopping'
                    . ' WHERE (menu_id = ? AND friend_id = ?)'
                        . ' OR (menu_id = ? AND friend_id = ?)';
            $this->DB->query($request, array($user_menu, $friend_id, $friend_menu, $user_id));
        }

        /**********************************************************
        FACEBOOK
        ***********************************************************/

        /**
         * Check if provided Facebook ID is already tied to a Kookiiz account
         * @param Int $fb_id 
         * @return Int ID of the user this FB ID is tied to, or 0
         */
        public function facebook_check($fb_id)
        {
            $request = 'SELECT user_id FROM users WHERE facebook_id = ?';
            $stmt = $this->DB->query($request, array($fb_id));
            $data = $stmt->fetch();
            return $data ? (int)$data['user_id'] : 0;
        }

        /**
         * Connect current user with provided Facebook ID
         * @param Int $fb_id unique Facebook ID
         */
        public function facebook_connect($fb_id)
        {
            $user_id = $this->User->getID();
            if($user_id)
            {
                //Remove any previous connection to this Facebook ID
                $this->facebook_disconnect($fb_id);

                //Bind Kookiiz account to Facebook credentials
                $request = 'UPDATE users SET facebook_id = ? WHERE user_id = ?';
                $stmt = $this->DB->query($request, array($fb_id, $user_id));
            }
            else
                throw new KookiizException('social', 1);
        }

        /**
         * Remove Facebook ID from database
         * @param Int $fb_id unique Facebook ID
         */
        public function facebook_disconnect($fb_id)
        {
            $request = 'UPDATE users SET facebook_id = 0'
                    . ' WHERE facebook_id = ?';
            $this->DB->query($request, array($fb_id));
        }

        /**
         * Find Facebook friends on Kookiiz
         * @param Array $fb_friends list of Facebook friends objects
         * @return Array list of fb_id/user_id pairs
         */
        public function facebook_friends_find(array $fb_friends)
        {
            $users  = array();
            $fb_ids = array();
            foreach($fb_friends as $friend)
            {
                $fb_ids[] = $friend['id'];
            }
            if(count($fb_ids))
            {
                $request = 'SELECT user_id, facebook_id AS fb_id FROM users'
                        . ' WHERE facebook_id AND facebook_id IN (' . implode(', ', $fb_ids) . ')';
                $stmt = $this->DB->query($request);
                $users = $stmt->fetchAll();
            }
            return $users;
        }
        
        /**
         * Post on Facebook Wall
         * @param String $text message to post on user's wall
         * @param String $url related URL
         * @param String $caption link short caption
         * @param String $desc link description
         * @param String $pic picture URL
         */
        public function facebookPost($text, $url, $caption = 'Kookiiz', $desc = '', $pic = '')
        {
            $Facebook = new Facebook(array(
                'appId'     => C::FACEBOOK_APP_ID,
                'secret'    => FACEBOOK_SECRET,
                'cookie'    => true
            ));
            try
            {
                $params = array(
                    'access_token'  => $Facebook->getAccessToken(),
                    'caption'       => $caption,
                    'message'       => $text,
                    'link'          => $url
                );
                if($desc) 
                    $params['description'] = $desc;
                if($pic)
                    $params['picture'] = $pic;
                
                $Facebook->api('/me/feed', 'POST', $params);
            }
            catch(FacebookApiException $e)
            {
            }
        }
        
        /**********************************************************
        SHARE
        ***********************************************************/

        /**
         * Share a recipe with a friend
         * @param Int $friend_id ID of the user with whom to share the recipe
         * @param Int $recipe_id ID of the recipe to share
         * @return Int unique ID of the sharing action
         */
        public function share_recipe($friend_id, $recipe_id)
        {
            //Check if this recipe is not already shared
            $request = 'SELECT recipe_id'
                    . ' FROM shared_recipes'
                    . ' WHERE user_1 = :user_id'
                        . ' AND user_2 = :friend_id'
                        . ' AND recipe_id = :recipe_id';
            $stmt = $this->DB->query($request, array(
                ':user_id'      => $this->User->getID(),
                ':friend_id'    => $friend_id,
                ':recipe_id'    => $recipe_id
            ));
            $data = $stmt->fetch();
            if($data) 
                throw new KookiizException('friends', 3);
            else
            {
                //Try to find recipe information
                $request = 'SELECT public, author_id'
                        . ' FROM recipes'
                        . ' WHERE recipe_id = ?';
                $stmt = $this->DB->query($request, array($recipe_id));
                $data = $stmt->fetch();
                if($data)
                {
                    //Check if recipe is public or user is the owner
                    $public = (int)$data['public'];
                    $author = (int)$data['author_id'];
                    if($public || $author == $this->User->getID())
                    {
                        if(!$public)
                        {
                            //Authorize friend to see this recipe
                            $request = 'INSERT IGNORE INTO recipes_authorized (recipe_id, user_id)'
                                        . ' VALUES (?, ?)';
                            $this->DB->query($request, array($recipe_id, $friend_id));
                        }

                        //Register this shared recipe
                        $request = 'INSERT INTO shared_recipes (user_1, user_2, recipe_id)'
                                    . ' VALUES(?, ?, ?)';
                        $this->DB->query($request, array($this->User->getID(), $friend_id, $recipe_id));

                        //Return sharing ID
                        return $this->DB->insertID();
                    }
                    else 
                        //Only owner can share this private recipe
                        throw new KookiizException('friends', 8);
                }
                else 
                    //Recipe was not found
                    throw new KookiizException('friends', 7);
            }
        }

        /**
         * Share a shopping list with a friend
         * @param Int $friend_id ID of the user with whom to share the shopping list
         * @param String $shopping_date date of the shopping list as "YYYY-MM-DD"
         */
        public function share_shopping($friend_id, $shopping_date)
        {
        
        }

        /**
         * Accept shopping list sharing proposal
         * @param Int $share_id ID of the sharing action
         */
        public function share_shopping_accept($share_id)
        {
            
        }

        /**
         * Deny shopping list sharing proposal
         * @param Int $share_id ID of the sharing action
         */
        public function share_shopping_deny($share_id)
        {
            
        }

        /**
         * Share current status with friends
         * @param Int $status_type type identifier
         * @param Int $content_id ID of a related content (0 if none)
         * @param String $comment text added to the status
         * @return Int unique ID of the sharing action
         */
        public function share_status($status_type, $content_id, $comment)
        {
            //Check if a content is required and provided
            if(C::get('STATUS_REQUIRE_CONTENT', $status_type) && !$content_id)
                throw new KookiizException('social', 2);
            else if(!C::get('STATUS_HAS_CONTENT', $status_type))
                $content_id = 0;

            //Find content title and picture (if there is a content ID)
            $content_title = ''; $content_pic = 0;
            if($content_id)
            {
                $request = 'SELECT name, pic_id'
                        . ' FROM recipes'
                        . ' WHERE recipe_id = ?';
                $stmt = $this->DB->query($request, array($content_id));
                $data = $stmt->fetch();
                if($data)
                {
                    $content_title = $data['name'];
                    $content_pic   = (int)$data['pic_id'];
                }
                else 
                    $content_id = 0;
            }

            //Save shared status
            $request = 'INSERT INTO shared_status (user_id, status_type, status_comment, content_id, content_title, content_pic)'
                        . ' VALUES(:user_id, :status, :comment, :content_id, :content_title, :content_pic)';
            $this->DB->query($request, array(
                ':user_id'          => $this->User->getID(),
                ':status'           => $status_type,
                ':comment'          => $comment,
                ':content_id'       => $content_id,
                ':content_title'    => $content_title,
                ':content_pic'      => $content_pic
            ));

            //Return sharing ID
            return $this->DB->insertID();
        }

        /**
         * Load recipes shared with current user
         * @return Array list of shared recipes data
         */
        public function shared_recipes_load()
        {
            $shared_recipes = array();
            $request = 'SELECT recipes.recipe_id, recipes.name AS recipe_name, recipes.pic_id,'
                        . ' users.user_id AS author_id, users.firstname AS author_name,'
                        . ' UNIX_TIMESTAMP(shared_recipes.share_date) AS date, shared_recipes.viewed'
                    . ' FROM shared_recipes'
                        . ' NATURAL JOIN recipes'
                        . ' LEFT JOIN users ON shared_recipes.user_1 = users.user_id'
                    . ' WHERE shared_recipes.user_2 = ?'
                    . ' ORDER BY date DESC';
            $stmt = $this->DB->query($request, array($this->User->getID()));
            while($recipe = $stmt->fetch())
            {
                $shared_recipes[] = array(
                    'recipe_id'     => (int)$recipe['recipe_id'],
                    'recipe_name'   => htmlspecialchars($recipe['recipe_name'], ENT_COMPAT, 'UTF-8'),
                    'recipe_pic'    => (int)$recipe['pic_id'],
                    'author_id'     => (int)$recipe['author_id'],
                    'author_name'   => htmlspecialchars($recipe['author_name'], ENT_COMPAT, 'UTF-8'),
                    'date'          => date('d.m.Y', (int)$recipe['date']),
                    'time'          => date('H:i', (int)$recipe['date']),
                    'viewed'        => (int)$recipe['viewed']
                );
            }

            //Set viewed as true for shared recipes
            $request = 'UPDATE shared_recipes SET viewed = 1 WHERE user_2 = ?';
            $this->DB->query($request, array($this->User->getID()));

            //Return shared recipes data
            return $shared_recipes;
        }

        /**
         * Load shopping lists shared with current user
         * @param Int $share_id ID of the sharing action (optional, defaults to all)
         * @return Array list of shared shopping data
         */
        public function shared_shopping_load($share_id = 0)
        {
            $shopping_shared = array();

            //Retrieve menu ID and shopping date
            $request = 'SELECT share_id, users_menus.menu_id, menus_shopping.menu_day'
                    . ' FROM shared_shopping'
                    . '     JOIN users_menus USING(menu_id)'
                    . '     JOIN menus_shopping USING(menu_id)'
                    . ' WHERE friend_id = ?'
                        . ' AND menu_day = DATEDIFF(shopping_date, menu_date) + ?'
                        . ' AND shared_shopping.accepted = 1';
            $params = array($this->User->getID(), C::MENU_DAYS_PAST);
            if($share_id)
            {
                $request .= ' AND share_id = ?';
                $params[] = $share_id;
            }
            $stmt = $this->DB->query($request, $params);
            while($shopping = $stmt->fetch())
            {
            }

            //Return shared shopping data
            return $shopping_shared;
        }

        /**********************************************************
        TWITTER
        ***********************************************************/

        /**
         * Check if provided Twitter ID is already tied to a Kookiiz account
         * @param Int $tw_id unique Twitter ID
         * @return Int ID of the user this TW ID is tied to, or 0
         */
        public function twitter_check($tw_id)
        {
            $request = 'SELECT user_id FROM users_twitter WHERE twitter_id = ?';
            $stmt = $this->DB->query($request, array($tw_id));
            $data = $stmt->fetch();
            return $data ? (int)$data['user_id'] : 0;
        }

        /**
         * Connect current user with provided Twitter credentials
         * @param Int $tw_id Twitter ID
         * @param String $token request token
         * @param String $secret request secret
         */
        public function twitter_connect($tw_id, $token, $secret)
        {
            $user_id = $this->User->getID();
            if($user_id)
            {
                //Check if Twitter ID is already connected to a Kookiiz account
                if($this->twitter_check($tw_id))
                    throw new KookiizException('social', 4);

                //Bind Kookiiz account to Twitter credentials
                $request = 'INSERT INTO users_twitter (user_id, twitter_id, token, secret)'
                            . ' VALUES (:user_id, :tw_id, :token, :secret)';
                $this->DB->query($request, array(
                    ':user_id'  => $user_id,
                    ':tw_id'    => $tw_id,
                    ':token'    => $token,
                    ':secret'   => $secret
                ));
            }
            else 
                throw new KookiizException('social', 1);
        }

        /**
         * Tweet provided data
         * @param String $text tweet content (properly encoded)
         * @param String $url related URL (usually shortened)
         * @return Bool true if tweet was successfully posted
         */
        public function twitter_tweet($text, $url)
        {
            $request = 'SELECT token, secret FROM users_twitter WHERE user_id = ?';
            $stmt = $this->DB->query($request, array($this->User->getID()));
            $data = $stmt->fetch();
            if($data)
            {
                //Prepare fields
                $text = stripslashes($text) . ' ';
                $url  = urldecode($url);
                $via  = ' @' . self::TWITTER_VIA;
                //Limit chars
                $chars_available = self::TWITTER_MAX - strlen($url) - strlen($via);
                if(strlen($text) > $chars_available)
                    $text = substr($text, 0, $chars_available - 3) . '...';
                //Build tweet string
                $tweet = $text . $url . $via;

                //Publish tweet
                $Twitter = new TwitterOAuth(TWITTER_CLIENT, TWITTER_SECRET, $data['token'], $data['secret']);
                $Twitter->post('statuses/update', array('status' => $tweet));

                //Tweet was successfull
                return true;
            }
            //Could not retrieve Twitter data for current user
            else return false;
        }
        
        /**********************************************************
        UNSHARE
        ***********************************************************/

        /**
         * Cancel recipe sharing
         * @param Int $friend_id ID of the friend
         * @param Int $recipe_id ID of the recipe
         * @param String $canceled_by was action triggered by "user" (sharer) or "friend" (receiver)?
         */
        public function unshare_recipe($friend_id, $recipe_id, $canceled_by)
        {
            $request = 'DELETE FROM shared_recipes';
            if($canceled_by == 'user')  
                $request .= ' WHERE user_1 = :user_id AND user_2 = :friend_id';
            else                        
                $request .= ' WHERE user_1 = :friend_id AND user_2 = :user_id';
            $request .= ' AND recipe_id = :recipe_id';
            
            $params = array(
                ':user_id'      => $this->User->getID(),
                ':friend_id'    => $friend_id,
                ':recipe_id'    => $recipe_id
            );
        }

        /**
         * Cancel shopping list sharing
         * @param Int $friend_id ID of the friend
         * @param String $shopping_date date of the shopping list as "YYYY-MM-DD"
         * @param String $canceled_by was action triggered by "user" (sharer) or "friend" (receiver)?
         */
        public function unshare_shopping($friend_id, $shopping_date, $canceled_by)
        {
            
        }
    }
?>
