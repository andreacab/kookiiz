<?php
    /**********************************************************
    Title: Recipes tags popup
    Authors: Kookiiz Team
    Purpose: HTML content of the recipes tagging popup
	***********************************************************/
	
	/**********************************************************
	SET UP
	***********************************************************/
	
	//Dependencies
    require_once '../class/dblink.php';
    require_once '../class/globals.php';
    require_once '../class/lang_db.php';
    require_once '../class/recipes_lib.php';
    require_once '../class/request.php';
    require_once '../class/session.php';
    require_once '../class/user.php';

    //Start session
    Session::start();

    //Init handlers
	$DB      = new DBLink('kookiiz');
    $Lang    = LangDB::getHandler(Session::getLang());
    $Request = new RequestHandler();
    $User    = new User($DB);
	
	//Load parameters
    $action    = $Request->get('action');
	$recipe_id = (int)$Request->get('recipe_id');
	
	/**********************************************************
	SCRIPT
	***********************************************************/
	
	//Only admins can edit tags
	if(!$User->isAdmin()) die();

    //Take appropriate action
    $RecipesLib = new RecipesLib($DB, $User);
    switch($action)
    {
      case 'delete':
          $tag_id = (int)$Request->get('tag_id');
          $RecipesLib->tags_delete($recipe_id, $tag_id);
          break;
      
      case 'save':
          $tag_id = (int)$Request->get('tag_id');
          $RecipesLib->tags_save($recipe_id, $tag_id);
          break;
    }
	
	//Load current list of tags
	$tags = $RecipesLib->tags_load($recipe_id);
    $tagsNames = $Lang->get('RECIPES_TAGS_NAMES');
    asort($tagsNames);
?>
<div id="recipes_tags_popup">
	<p class="center">
      <span class="bold">New tag</span>
		<select id="recipe_tag_select">
		<?php
        foreach($tagsNames as $id => $name)
        {
            if(!in_array($id, $tags))
               echo "<option value='$id'>$name</option>";
        }
		?>
		</select>
		<button type="button" class="button_80" id="recipe_tags_add"><?php $Lang->p('ACTIONS', 14); ?></button>
        <span id="recipe_notag_caption">No tag available</span>
	</p>
	<p class="bold">Current tags</p>
	<p class="center">
	<?php
		if(count($tags))
		{ 
			echo '<ul class="tags_list">';
			foreach($tags as $tag_id)
			{
				$name = $tagsNames[$tag_id];
				echo    "<li id='tag_item_$tag_id' class='tag_item'>",
                            "<span>$name</span>",
                            '<img class="button15 cancel" src="', C::ICON_URL, '" alt="', $Lang->get('ACTIONS', 23), '" />',
                        '</li>';
			}
			echo '</ul>';
		}
		else 
            echo 'No tag added';
	?>
	</p>
</div>